<?php
/**
 * BLT_Payment – Gestione pagamenti Stripe e PayPal.
 *
 * Flusso acquisto:
 *  1. Utente seleziona gateway e clicca "Paga"
 *  2. create_session() crea la sessione sul provider e redirige
 *  3. Provider rimanda a success_url o cancel_url
 *  4. Webhook del provider (IPN per PayPal) conferma il pagamento lato server
 *  5. on_payment_confirmed() attiva tessera + quota + email
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BLT_Payment {

    // Slug per le URL di ritorno
    const PARAM_SESSION  = 'blt_pay_session';
    const PARAM_GATEWAY  = 'blt_pay_gw';
    const PARAM_RESULT   = 'blt_pay_result';   // success | cancel | error
    const WEBHOOK_STRIPE = 'blt-webhook-stripe';
    const WEBHOOK_PAYPAL = 'blt-webhook-paypal';

    public function __construct() {
        add_action( 'init',                        array( $this, 'handle_return' ) );
        add_action( 'init',                        array( $this, 'handle_webhook' ) );
        add_action( 'wp_ajax_BLT_create_payment',        array( $this, 'ajax_create_payment' ) );
        add_action( 'wp_ajax_nopriv_BLT_create_payment', array( $this, 'ajax_create_payment' ) );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AJAX: crea sessione pagamento
    // ─────────────────────────────────────────────────────────────────────────

    public function ajax_create_payment() {
        check_ajax_referer( 'BLT_payment_nonce', 'nonce' );

        $gateway      = sanitize_key( $_POST['gateway'] ?? '' );
        $tipo_tessera = sanitize_key( $_POST['tipo_tessera'] ?? 'ordinario' );
        $tesserato_id = absint( $_POST['tesserato_id'] ?? 0 ); // 0 = nuova iscrizione
        $return_url   = sanitize_url( $_POST['return_url'] ?? home_url('/') );
        $reg_token    = sanitize_text_field( $_POST['reg_token'] ?? '' ); // nuova iscrizione

        // Bonifico bancario: gestito lato server senza redirect
        if ( $gateway === 'bonifico' ) {
            $this->handle_bonifico( $tipo_tessera, $tesserato_id, $return_url, $reg_token );
            return;
        }

        if ( ! in_array( $gateway, ['stripe','paypal'], true ) ) {
            wp_send_json_error('Gateway non valido.');
        }

        $importo = $this->get_importo( $tipo_tessera );
        if ( $importo <= 0 ) wp_send_json_error('Importo non valido.');

        // Recupera dati iscrizione se nuova
        $dati_iscrizione = array();
        if ( ! $tesserato_id && $reg_token ) {
            $dati_iscrizione = get_transient( 'blt_reg_' . $reg_token ) ?: array();
        }

        // Salva sessione temporanea
        $token = wp_generate_password( 32, false );
        $this->save_session( $token, array(
            'gateway'          => $gateway,
            'tipo_tessera'     => $tipo_tessera,
            'tesserato_id'     => $tesserato_id,
            'importo'          => $importo,
            'user_id'          => get_current_user_id(),
            'return_url'       => $return_url,
            'status'           => 'pending',
            'created'          => time(),
            'dati_iscrizione'  => $dati_iscrizione,
        ) );

        try {
            $redirect = $gateway === 'stripe'
                ? $this->stripe_create_session( $token, $importo, $tipo_tessera, $return_url )
                : $this->paypal_create_order( $token, $importo, $tipo_tessera, $return_url );

            wp_send_json_success( array( 'redirect' => $redirect ) );
        } catch ( Exception $e ) {
            wp_send_json_error( $e->getMessage() );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STRIPE
    // ─────────────────────────────────────────────────────────────────────────

    private function stripe_create_session( string $token, float $importo, string $tipo, string $return_url ): string {
        $secret_key = BLT_Database::get_setting('stripe_secret_key');
        if ( ! $secret_key ) throw new Exception('Stripe non configurato. Inserisci la Secret Key nelle impostazioni.');

        $proloco     = BLT_Database::get_setting('nome_proloco', 'Pro Loco');
        $success_url = add_query_arg( array(
            self::PARAM_RESULT  => 'success',
            self::PARAM_GATEWAY => 'stripe',
            self::PARAM_SESSION => $token,
        ), $return_url );
        $cancel_url = add_query_arg( array(
            self::PARAM_RESULT  => 'cancel',
            self::PARAM_GATEWAY => 'stripe',
        ), $return_url );

        $response = wp_remote_post( 'https://api.stripe.com/v1/checkout/sessions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $secret_key,
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ),
            'body' => array(
                'payment_method_types[]'              => 'card',
                'mode'                                => 'payment',
                'success_url'                         => $success_url,
                'cancel_url'                          => $cancel_url,
                'metadata[BLT_token]'                 => $token,
                'metadata[tipo_tessera]'              => $tipo,
                'line_items[0][quantity]'             => '1',
                'line_items[0][price_data][currency]' => 'eur',
                'line_items[0][price_data][unit_amount]' => (string) round( $importo * 100 ),
                'line_items[0][price_data][product_data][name]' =>
                    'Tessera ' . ucfirst($tipo) . ' – ' . $proloco . ' ' . date('Y'),
                'line_items[0][price_data][product_data][description]' =>
                    'Quota associativa annuale ' . date('Y'),
            ),
            'timeout' => 20,
        ) );

        $this->check_http( $response, 'Stripe' );
        $data = json_decode( wp_remote_retrieve_body($response), true );
        if ( empty($data['url']) ) throw new Exception('Stripe non ha restituito un URL di pagamento.');
        return $data['url'];
    }

    // ── Webhook Stripe ────────────────────────────────────────────────────────

    private function stripe_handle_webhook(): void {
        $payload   = file_get_contents('php://input');
        $sig       = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $secret    = BLT_Database::get_setting('stripe_webhook_secret');

        // Verifica firma se il webhook secret è configurato
        if ( $secret ) {
            $this->stripe_verify_signature( $payload, $sig, $secret );
        }

        $event = json_decode( $payload, true );
        if ( ! $event ) { status_header(400); exit('Bad JSON'); }

        if ( $event['type'] === 'checkout.session.completed' ) {
            $session = $event['data']['object'];
            if ( $session['payment_status'] === 'paid' ) {
                $token = $session['metadata']['BLT_token'] ?? '';
                if ( $token ) {
                    $this->on_payment_confirmed( $token, 'stripe', $session['id'] );
                }
            }
        }
        status_header(200); echo 'OK'; exit;
    }

    private function stripe_verify_signature( string $payload, string $sig_header, string $secret ): void {
        $parts     = array();
        foreach ( explode(',', $sig_header) as $part ) {
            list($k,$v) = explode('=', $part, 2);
            $parts[$k]  = $v;
        }
        $timestamp = $parts['t'] ?? 0;
        $tolerance = 300; // 5 min
        if ( abs(time() - $timestamp) > $tolerance ) {
            throw new Exception('Webhook Stripe scaduto.');
        }
        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
        if ( ! hash_equals($expected, $parts['v1'] ?? '') ) {
            throw new Exception('Firma Stripe non valida.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PAYPAL
    // ─────────────────────────────────────────────────────────────────────────

    private function paypal_api_base(): string {
        return BLT_Database::get_setting('paypal_sandbox') === '1'
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    private function paypal_get_token(): string {
        $client_id = BLT_Database::get_setting('paypal_client_id');
        $secret    = BLT_Database::get_setting('paypal_secret');
        if ( ! $client_id || ! $secret ) throw new Exception('PayPal non configurato. Inserisci Client ID e Secret nelle impostazioni.');

        $response = wp_remote_post( $this->paypal_api_base() . '/v1/oauth2/token', array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $secret ),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ),
            'body'    => 'grant_type=client_credentials',
            'timeout' => 20,
        ) );
        $this->check_http( $response, 'PayPal token' );
        $data = json_decode( wp_remote_retrieve_body($response), true );
        if ( empty($data['access_token']) ) throw new Exception('PayPal: impossibile ottenere il token.');
        return $data['access_token'];
    }

    private function paypal_create_order( string $token, float $importo, string $tipo, string $return_url ): string {
        $access_token = $this->paypal_get_token();
        $proloco      = BLT_Database::get_setting('nome_proloco', 'Bandaloco');

        $success_url = add_query_arg( array(
            self::PARAM_RESULT  => 'success',
            self::PARAM_GATEWAY => 'paypal',
            self::PARAM_SESSION => $token,
            'token'             => '{token}', // PayPal sostituisce questo
        ), $return_url );
        $cancel_url = add_query_arg( array(
            self::PARAM_RESULT  => 'cancel',
            self::PARAM_GATEWAY => 'paypal',
        ), $return_url );

        $body = json_encode( array(
            'intent' => 'CAPTURE',
            'purchase_units' => array( array(
                'reference_id' => $token,
                'description'  => 'Tessera ' . ucfirst($tipo) . ' ' . date('Y') . ' – ' . $proloco,
                'amount'       => array(
                    'currency_code' => 'EUR',
                    'value'         => number_format( $importo, 2, '.', '' ),
                ),
                'custom_id' => $token,
            ) ),
            'application_context' => array(
                'return_url'         => $success_url,
                'cancel_url'         => $cancel_url,
                'brand_name'         => $proloco,
                'landing_page'       => 'NO_PREFERENCE',
                'user_action'        => 'PAY_NOW',
            ),
        ) );

        $response = wp_remote_post( $this->paypal_api_base() . '/v2/checkout/orders', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ),
            'body'    => $body,
            'timeout' => 20,
        ) );
        $this->check_http( $response, 'PayPal create order' );
        $data = json_decode( wp_remote_retrieve_body($response), true );

        // Trova il link di approvazione
        foreach ( $data['links'] ?? array() as $link ) {
            if ( $link['rel'] === 'approve' ) return $link['href'];
        }
        throw new Exception('PayPal non ha restituito il link di pagamento.');
    }

    // ── Cattura pagamento PayPal al ritorno ───────────────────────────────────

    private function paypal_capture_order( string $paypal_order_id ): bool {
        $access_token = $this->paypal_get_token();

        $response = wp_remote_post(
            $this->paypal_api_base() . '/v2/checkout/orders/' . $paypal_order_id . '/capture',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type'  => 'application/json',
                ),
                'body'    => '{}',
                'timeout' => 20,
            )
        );
        $this->check_http( $response, 'PayPal capture' );
        $data = json_decode( wp_remote_retrieve_body($response), true );
        return ( $data['status'] ?? '' ) === 'COMPLETED';
    }

    // ── Webhook PayPal (IPN v2) ───────────────────────────────────────────────

    private function paypal_handle_webhook(): void {
        $payload = file_get_contents('php://input');
        $event   = json_decode( $payload, true );
        if ( ! $event ) { status_header(400); exit; }

        if ( $event['event_type'] === 'CHECKOUT.ORDER.APPROVED'
          || $event['event_type'] === 'PAYMENT.CAPTURE.COMPLETED' ) {
            $resource = $event['resource'];
            // Recupera custom_id / reference_id
            $token = $resource['custom_id']
                  ?? $resource['purchase_units'][0]['reference_id']
                  ?? $resource['purchase_units'][0]['custom_id']
                  ?? '';
            if ( $token ) {
                $this->on_payment_confirmed( $token, 'paypal', $resource['id'] ?? '' );
            }
        }
        status_header(200); echo 'OK'; exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RITORNO DALL'ACQUISTO (redirect da browser)
    // ─────────────────────────────────────────────────────────────────────────

    public function handle_return(): void {
        if ( ! isset( $_GET[ self::PARAM_RESULT ] ) ) return;

        $result  = sanitize_key( $_GET[ self::PARAM_RESULT ] );
        $gateway = sanitize_key( $_GET[ self::PARAM_GATEWAY ] ?? '' );
        $token   = sanitize_text_field( $_GET[ self::PARAM_SESSION ] ?? '' );

        if ( $result === 'success' && $token ) {
            $session = $this->get_session( $token );
            if ( ! $session || $session['status'] === 'completed' ) return;

            // PayPal richiede capture esplicito al ritorno
            if ( $gateway === 'paypal' ) {
                $paypal_order_id = sanitize_text_field( $_GET['token'] ?? '' );
                try {
                    if ( $paypal_order_id && $this->paypal_capture_order( $paypal_order_id ) ) {
                        $this->on_payment_confirmed( $token, 'paypal', $paypal_order_id );
                    }
                } catch ( Exception $e ) {
                    // Webhook lo gestirà comunque
                    error_log( 'blt PayPal capture error: ' . $e->getMessage() );
                }
            }
            // Stripe: il webhook fa tutto, ma possiamo già marcare completato se session esiste
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // WEBHOOK ENTRY POINT
    // ─────────────────────────────────────────────────────────────────────────

    public function handle_webhook(): void {
        if ( isset($_GET[ self::WEBHOOK_STRIPE ]) ) {
            $this->stripe_handle_webhook();
        }
        if ( isset($_GET[ self::WEBHOOK_PAYPAL ]) ) {
            $this->paypal_handle_webhook();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONFERMA PAGAMENTO → attiva tessera
    // ─────────────────────────────────────────────────────────────────────────

    public function on_payment_confirmed( string $token, string $gateway, string $transaction_id ): void {
        global $wpdb;

        $session = $this->get_session( $token );
        if ( ! $session || $session['status'] === 'completed' ) return;

        // Marca sessione come completata (idempotenza)
        $this->update_session( $token, array( 'status' => 'completed', 'transaction_id' => $transaction_id ) );

        $tipo         = $session['tipo_tessera'];
        $importo      = (float) $session['importo'];
        $tesserato_id = (int) $session['tesserato_id'];
        $user_id      = (int) $session['user_id'];
        $anno         = date('Y');

        // ── Caso 1: Nuova iscrizione (tesserato_id = 0) ───────────────────────
        if ( ! $tesserato_id ) {
            // Recupera dati pendenti dalla sessione (salvati al momento del form)
            $dati = $session['dati_iscrizione'] ?? array();
            if ( empty($dati) && $user_id ) {
                // Fallback: prova da user meta
                $dati = get_user_meta( $user_id, 'blt_pending_registration', true ) ?: array();
            }
            if ( empty($dati) ) {
                error_log('BLT: nessun dato iscrizione per token ' . $token);
                return;
            }
            $dati = array_merge( $dati, array(
                'tipo_tessera'    => $tipo,
                'stato'           => 'nuovo',   // attivato sotto dopo verifica importo
                'data_iscrizione' => date('Y-m-d'),
                'data_scadenza'   => $anno . '-12-31',
            ) );
            $tesserato_id = BLT_Database::save_tesserato( $dati );
            if ( $user_id ) {
                $wpdb->update( $wpdb->prefix . 'blt_tesserati', array('user_id' => $user_id), array('id' => $tesserato_id) );
                delete_user_meta( $user_id, 'blt_pending_registration' );
            }
        } else {
            // ── Caso 2: Rinnovo tessera esistente ────────────────────────────
            // L'attivazione avviene solo se importo >= quota dovuta (vedi sotto)
        }

        // ── Registra / aggiorna quota ────────────────────────────────────────
        $quota_esistente = BLT_Database::get_quota( $tesserato_id, $anno );
        BLT_Database::save_quota( array(
            'tesserato_id'     => $tesserato_id,
            'anno'             => $anno,
            'importo'          => $importo,
            'stato_pagamento'  => 'pagato',
            'metodo_pagamento' => $gateway,
            'data_pagamento'   => date('Y-m-d'),
            'ricevuta_numero'  => strtoupper( substr($gateway,0,3) ) . '-' . strtoupper( substr($transaction_id,0,16) ),
            'note'             => 'Pagamento online – ' . ucfirst($gateway) . ' ' . $transaction_id,
        ), $quota_esistente ? $quota_esistente->id : 0 );

        // ── Attiva tessera se importo >= quota dovuta ─────────────────────────
        self::attiva_se_quota_sufficiente( $tesserato_id, $tipo, $importo, $anno );

        // ── Email di conferma ─────────────────────────────────────────────────
        $tesserato = BLT_Database::get_tesserato( $tesserato_id );
        $quota     = BLT_Database::get_quota( $tesserato_id, $anno );
        if ( $tesserato && $quota ) {
            BLT_Email::invia_conferma_pagamento( $tesserato, $quota );
            if ( (int) $session['tesserato_id'] === 0 ) {
                BLT_Email::invia_benvenuto( $tesserato );
                BLT_Email::notifica_admin_nuova_iscrizione( $tesserato );
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SESSIONI (tabella blt_impostazioni usata come KV store temporaneo)
    // ─────────────────────────────────────────────────────────────────────────

    private function save_session( string $token, array $data ): void {
        global $wpdb;
        $wpdb->replace( $wpdb->prefix . 'blt_impostazioni', array(
            'chiave' => 'pay_session_' . $token,
            'valore' => wp_json_encode( $data ),
        ) );
    }

    private function get_session( string $token ): ?array {
        $raw = BLT_Database::get_setting( 'pay_session_' . $token );
        if ( ! $raw ) return null;
        return json_decode( $raw, true ) ?: null;
    }

    private function update_session( string $token, array $merge ): void {
        $session = $this->get_session( $token );
        if ( ! $session ) return;
        $this->save_session( $token, array_merge( $session, $merge ) );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    // ── Bonifico bancario ────────────────────────────────────────────────────

    private function handle_bonifico( string $tipo, int $tesserato_id, string $return_url, string $reg_token ): void {
        global $wpdb;

        $importo     = $this->get_importo( $tipo );
        $anno        = date('Y');
        $settings    = BLT_Database::get_all_settings();
        $iban        = $settings['bonifico_iban']    ?? '';
        $intestato   = $settings['bonifico_intestato'] ?? ($settings['nome_proloco'] ?? 'Bandaloco');
        $causale_pfx = $settings['bonifico_causale'] ?? 'Quota associativa';

        // Nuova iscrizione: crea tesserato dai dati salvati nel transient
        if ( ! $tesserato_id && $reg_token ) {
            $dati = get_transient( 'blt_reg_' . $reg_token );
            if ( $dati ) {
                $dati = array_merge($dati, array(
                    'tipo_tessera'    => $tipo,
                    'stato'           => 'nuovo',
                    'data_iscrizione' => date('Y-m-d'),
                    'data_scadenza'   => $anno . '-12-31',
                ));
                $tesserato_id = BLT_Database::save_tesserato( $dati );
                if ( $tesserato_id && is_user_logged_in() ) {
                    $wpdb->update($wpdb->prefix.'blt_tesserati', array('user_id'=>get_current_user_id()), array('id'=>$tesserato_id));
                }
                delete_transient( 'blt_reg_' . $reg_token );
                if ( $tesserato_id ) {
                    $tesserato = BLT_Database::get_tesserato($tesserato_id);
                    if ($tesserato) BLT_Email::invia_benvenuto($tesserato);
                }
            }
        }

        if ( ! $tesserato_id ) {
            wp_send_json_error('Impossibile creare il tesserato. Riprova.');
            return;
        }

        $tesserato = BLT_Database::get_tesserato($tesserato_id);
        if ( ! $tesserato ) {
            wp_send_json_error('Tesserato non trovato.');
            return;
        }

        // Crea riga quota in attesa
        $causale = $causale_pfx . ' ' . $anno . ' – ' . $tesserato->nome . ' ' . $tesserato->cognome . ' (N°' . $tesserato->numero_tessera . ')';
        $quota_esistente = BLT_Database::get_quota($tesserato_id, $anno);
        BLT_Database::save_quota( array(
            'tesserato_id'     => $tesserato_id,
            'anno'             => $anno,
            'importo'          => $importo,
            'stato_pagamento'  => 'in_attesa',
            'metodo_pagamento' => 'bonifico',
            'data_pagamento'   => null,
            'note'             => 'In attesa di bonifico – causale: ' . $causale,
        ), $quota_esistente ? $quota_esistente->id : 0 );

        $quota = BLT_Database::get_quota($tesserato_id, $anno);

        // Notifica admin
        BLT_Email::notifica_admin_bonifico($tesserato, $quota);

        // Risposta JSON con dati per mostrare le istruzioni all'utente
        wp_send_json_success(array(
            'gateway'    => 'bonifico',
            'iban'       => $iban,
            'intestato'  => $intestato,
            'causale'    => $causale,
            'importo'    => number_format($importo, 2, ',', '.'),
            'redirect'   => add_query_arg(array(BLT_Payment::PARAM_RESULT => 'bonifico_pending'), $return_url),
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ATTIVAZIONE AUTOMATICA PER IMPORTO
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Attiva la tessera e aggiorna data_scadenza se importo >= quota dovuta.
     * Chiamato dopo ogni pagamento confermato (online e bonifico lato admin).
     *
     * @param int    $tesserato_id
     * @param string $tipo_tessera
     * @param float  $importo_pagato  importo effettivamente ricevuto
     * @param string $anno            anno di competenza (default anno corrente)
     * @return bool  true se attivato
     */
    public static function attiva_se_quota_sufficiente(
        int    $tesserato_id,
        string $tipo_tessera,
        float  $importo_pagato,
        string $anno = ''
    ): bool {
        global $wpdb;

        if ( ! $anno ) $anno = date('Y');

        $quota_dovuta = self::get_importo( $tipo_tessera );

        if ( $importo_pagato < $quota_dovuta ) {
            // Importo insufficiente — lascia in stato attuale, logga
            error_log( sprintf(
                'blt: tesserato %d – importo pagato %.2f < quota dovuta %.2f (%s). Tessera NON attivata.',
                $tesserato_id, $importo_pagato, $quota_dovuta, $tipo_tessera
            ) );
            return false;
        }

        $wpdb->update(
            $wpdb->prefix . 'blt_tesserati',
            array(
                'stato'         => 'attivo',
                'data_scadenza' => $anno . '-12-31',
            ),
            array( 'id' => $tesserato_id ),
            array( '%s', '%s' ),
            array( '%d' )
        );

        return true;
    }

    public static function get_importo( string $tipo ): float {
        $map = array(
            'ordinario'   => 'quota_ordinario',
            'sostenitore' => 'quota_sostenitore',
            'familiare'   => 'quota_familiare',
            'onorario'    => 'quota_onorario',
            'junior'      => 'quota_junior',
        );
        $key = $map[ $tipo ] ?? 'quota_ordinario';
        return (float) BLT_Database::get_setting( $key, '20.00' );
    }

    public static function is_stripe_enabled(): bool {
        return BLT_Database::get_setting('stripe_enabled') === '1'
            && ! empty( BLT_Database::get_setting('stripe_publishable_key') )
            && ! empty( BLT_Database::get_setting('stripe_secret_key') );
    }

    public static function is_bonifico_enabled(): bool {
        return BLT_Database::get_setting('bonifico_enabled') === '1'
            && ! empty( BLT_Database::get_setting('bonifico_iban') );
    }

    public static function is_paypal_enabled(): bool {
        return BLT_Database::get_setting('paypal_enabled') === '1'
            && ! empty( BLT_Database::get_setting('paypal_client_id') )
            && ! empty( BLT_Database::get_setting('paypal_secret') );
    }

    public static function webhook_url( string $gateway ): string {
        return add_query_arg( 'blt-webhook-' . $gateway, '1', home_url('/') );
    }

    private function check_http( $response, string $label ): void {
        if ( is_wp_error($response) ) throw new Exception( $label . ': ' . $response->get_error_message() );
        $code = wp_remote_retrieve_response_code($response);
        if ( $code < 200 || $code >= 300 ) {
            $body = wp_remote_retrieve_body($response);
            $err  = json_decode($body, true);
            $msg  = $err['error']['message'] ?? $err['message'] ?? 'Errore HTTP ' . $code;
            throw new Exception( $label . ': ' . $msg );
        }
    }
}
