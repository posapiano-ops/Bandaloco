<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BLT_Email {

    public static function invia_benvenuto( $tesserato ) {
        if ( empty( $tesserato->email ) ) return;
        $settings  = BLT_Database::get_all_settings();
        $proloco   = $settings['nome_proloco'] ?? 'Bandaloco';
        $subject   = "Benvenuto in " . $proloco . " – Tessera N° " . $tesserato->numero_tessera;
        $body      = self::template( 'benvenuto', $tesserato, $settings );
        self::send( $tesserato->email, $subject, $body );
    }

    public static function invia_avviso_scadenza( $tesserato ) {
        if ( empty( $tesserato->email ) ) return;
        $settings = BLT_Database::get_all_settings();
        $proloco  = $settings['nome_proloco'] ?? 'Bandaloco';
        $scadenza = date_i18n( 'd/m/Y', strtotime( $tesserato->data_scadenza ) );
        $subject  = "La tua tessera " . $proloco . " scade il " . $scadenza;
        $body     = self::template( 'scadenza', $tesserato, $settings );
        self::send( $tesserato->email, $subject, $body );
    }

    // ── Notifiche admin ──────────────────────────────────────────────────────

    public static function notifica_admin_nuova_iscrizione( $tesserato ) {
        $settings    = BLT_Database::get_all_settings();
        $proloco     = $settings['nome_proloco'] ?? 'Bandaloco';
        $admin_email = $settings['email_notifiche'] ?: get_option('admin_email');
        $admin_url   = admin_url('admin.php?page=blt-tesserati&action=view&id=' . $tesserato->id);

        $subject = '🆕 Nuova iscrizione – ' . $tesserato->nome . ' ' . $tesserato->cognome;
        $content = "<p>È arrivata una <strong>nuova iscrizione</strong> su <strong>$proloco</strong>.</p>
            <table style='border:1px solid #ddd;padding:10px;border-radius:6px;width:100%;'>
                <tr><td><strong>Nome:</strong></td><td>" . esc_html($tesserato->nome . ' ' . $tesserato->cognome) . "</td></tr>
                <tr><td><strong>Email:</strong></td><td>" . esc_html($tesserato->email) . "</td></tr>
                <tr><td><strong>Tessera N°:</strong></td><td>" . esc_html($tesserato->numero_tessera) . "</td></tr>
                <tr><td><strong>Tipo:</strong></td><td>" . esc_html(ucfirst($tesserato->tipo_tessera)) . "</td></tr>
                <tr><td><strong>Data:</strong></td><td>" . date_i18n('d/m/Y H:i') . "</td></tr>
            </table>
            <p style='margin-top:16px;'>
                <a href='" . esc_url($admin_url) . "' style='background:#2c5282;color:#fff;padding:10px 20px;border-radius:4px;text-decoration:none;'>
                    Visualizza in Admin →
                </a>
            </p>";
        self::send( $admin_email, $subject, self::wrap($content, $proloco) );

        // Notifica WP interna (campanellino admin)
        self::crea_notifica_wp( $tesserato, 'iscrizione' );
    }

    public static function notifica_admin_bonifico( $tesserato, $quota ) {
        $settings    = BLT_Database::get_all_settings();
        $proloco     = $settings['nome_proloco'] ?? 'Bandaloco';
        $admin_email = $settings['email_notifiche'] ?: get_option('admin_email');
        $admin_url   = admin_url('admin.php?page=blt-quote');
        $iban        = $settings['bonifico_iban'] ?? '—';

        $subject = '🏦 Richiesta bonifico – ' . $tesserato->nome . ' ' . $tesserato->cognome;
        $content = "<p><strong>" . esc_html($tesserato->nome . ' ' . $tesserato->cognome) . "</strong> ha scelto il <strong>pagamento tramite bonifico</strong> per la quota " . esc_html($quota->anno ?? date('Y')) . ".</p>
            <table style='border:1px solid #ddd;padding:10px;border-radius:6px;width:100%;'>
                <tr><td><strong>Tessera N°:</strong></td><td>" . esc_html($tesserato->numero_tessera) . "</td></tr>
                <tr><td><strong>Tipo:</strong></td><td>" . esc_html(ucfirst($tesserato->tipo_tessera)) . "</td></tr>
                <tr><td><strong>Importo:</strong></td><td>€ " . number_format((float)($quota->importo ?? 0), 2, ',', '.') . "</td></tr>
                <tr><td><strong>IBAN:</strong></td><td>" . esc_html($iban) . "</td></tr>
            </table>
            <p>Quando ricevi il bonifico, <a href='" . esc_url($admin_url) . "'>marca la quota come pagata</a> nel pannello admin.</p>";
        self::send( $admin_email, $subject, self::wrap($content, $proloco) );

        // Notifica WP interna
        self::crea_notifica_wp( $tesserato, 'bonifico' );
    }

    /**
     * Crea una notifica admin WordPress (visibile nel menu Tessere Pro Loco).
     * Usa l'opzione 'blt_notifiche_admin' come array di messaggi non letti.
     */
    private static function crea_notifica_wp( $tesserato, string $tipo ): void {
        $notifiche = get_option('blt_notifiche_admin', array());
        $notifiche[] = array(
            'tipo'    => $tipo,
            'id'      => $tesserato->id,
            'nome'    => $tesserato->nome . ' ' . $tesserato->cognome,
            'tessera' => $tesserato->numero_tessera,
            'data'    => date('Y-m-d H:i:s'),
            'letta'   => false,
        );
        // Tieni solo le ultime 50
        if ( count($notifiche) > 50 ) $notifiche = array_slice($notifiche, -50);
        update_option('blt_notifiche_admin', $notifiche);
    }

    public static function conta_notifiche_non_lette(): int {
        $notifiche = get_option('blt_notifiche_admin', array());
        return count( array_filter($notifiche, fn($n) => ! $n['letta']) );
    }

    public static function segna_tutte_lette(): void {
        $notifiche = get_option('blt_notifiche_admin', array());
        foreach ( $notifiche as &$n ) $n['letta'] = true;
        update_option('blt_notifiche_admin', $notifiche);
    }

    // ── Email tesserato ───────────────────────────────────────────────────────

    public static function invia_conferma_pagamento( $tesserato, $quota ) {
        if ( empty( $tesserato->email ) ) return;
        $settings = BLT_Database::get_all_settings();
        $proloco  = $settings['nome_proloco'] ?? 'Bandaloco';
        $subject  = "Conferma pagamento quota " . $quota->anno . " – " . $proloco;
        $body     = self::template( 'pagamento', $tesserato, $settings, $quota );
        self::send( $tesserato->email, $subject, $body );
    }

    private static function template( $tipo, $tesserato, $settings, $extra = null ) {
        $proloco = esc_html( $settings['nome_proloco'] ?? 'Bandaloco' );
        $nome    = esc_html( $tesserato->nome );

        $content = '';
        switch ( $tipo ) {
            case 'benvenuto':
                $scadenza = date_i18n( 'd/m/Y', strtotime( $tesserato->data_scadenza ) );
                $content = "<p>Ciao <strong>$nome</strong>,</p>
                <p>la tua iscrizione a <strong>$proloco</strong> è stata registrata con successo!</p>
                <table style='border:1px solid #ddd;padding:10px;border-radius:6px;'>
                    <tr><td><strong>N° Tessera:</strong></td><td>" . esc_html($tesserato->numero_tessera) . "</td></tr>
                    <tr><td><strong>Tipo:</strong></td><td>" . esc_html(ucfirst($tesserato->tipo_tessera)) . "</td></tr>
                    <tr><td><strong>Valida fino al:</strong></td><td>$scadenza</td></tr>
                </table>
                <p>Puoi accedere alla tua area riservata per visualizzare la tua tessera digitale.</p>";
                break;

            case 'scadenza':
                $scadenza = date_i18n( 'd/m/Y', strtotime( $tesserato->data_scadenza ) );
                $giorni   = (int) ceil( ( strtotime($tesserato->data_scadenza) - time() ) / 86400 );
                $content  = "<p>Ciao <strong>$nome</strong>,</p>
                <p>ti ricordiamo che la tua tessera <strong>$proloco</strong> (N° " . esc_html($tesserato->numero_tessera) . ") <strong>scade il $scadenza</strong> (tra $giorni giorni).</p>
                <p>Per rinnovarla contatta la sede o accedi all'area riservata.</p>";
                break;

            case 'pagamento':
                $content = "<p>Ciao <strong>$nome</strong>,</p>
                <p>confermiamo la ricezione del pagamento della quota associativa " . esc_html($extra->anno) . ".</p>
                <table style='border:1px solid #ddd;padding:10px;border-radius:6px;'>
                    <tr><td><strong>Importo:</strong></td><td>€ " . number_format($extra->importo, 2, ',', '.') . "</td></tr>
                    <tr><td><strong>Data pagamento:</strong></td><td>" . date_i18n('d/m/Y', strtotime($extra->data_pagamento)) . "</td></tr>
                    <tr><td><strong>N° Ricevuta:</strong></td><td>" . esc_html($extra->ricevuta_numero) . "</td></tr>
                </table>";
                break;
        }

        return self::wrap( $content, $proloco );
    }

    private static function wrap( $content, $proloco ) {
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;color:#333;">
            <div style="background:#2c5282;color:#fff;padding:20px;border-radius:8px 8px 0 0;">
                <h2 style="margin:0;">' . $proloco . '</h2>
            </div>
            <div style="border:1px solid #ddd;border-top:none;padding:24px;border-radius:0 0 8px 8px;">
                ' . $content . '
                <hr style="border:none;border-top:1px solid #eee;margin:20px 0;">
                <p style="font-size:12px;color:#777;">Questa email è stata inviata automaticamente da ' . $proloco . '. Non rispondere a questa email.</p>
            </div>
        </body></html>';
    }

    private static function send( $to, $subject, $body ) {
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
        wp_mail( $to, $subject, $body, $headers );
    }
}

// Cron job giornaliero per avvisi scadenza
add_action( 'blt_daily_check_scadenze', 'blt_check_scadenze_cron' );
function blt_check_scadenze_cron() {
    global $wpdb;
    $giorni  = (int) BLT_Database::get_setting( 'giorni_avviso', 30 );
    $results = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}blt_tesserati
         WHERE stato = 'attivo'
         AND data_scadenza BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL %d DAY)",
        $giorni
    ) );
    foreach ( $results as $t ) {
        BLT_Email::invia_avviso_scadenza( $t );
    }

    // Aggiorna stato tesserati scaduti
    $wpdb->query( "UPDATE {$wpdb->prefix}blt_tesserati SET stato='scaduto' WHERE stato='attivo' AND data_scadenza < CURDATE()" );
}
