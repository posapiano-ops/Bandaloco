<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BLT_Admin {

    public function __construct() {
        add_action( 'admin_menu',            array( $this, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_post_blt_save_tesserato',   array( $this, 'handle_save_tesserato' ) );
        add_action( 'admin_post_blt_delete_tesserato', array( $this, 'handle_delete_tesserato' ) );
        add_action( 'admin_post_blt_save_quota',       array( $this, 'handle_save_quota' ) );
        add_action( 'admin_post_blt_conferma_bonifico', array( $this, 'handle_conferma_bonifico' ) );
        add_action( 'admin_post_blt_export_csv',       array( $this, 'handle_export_csv' ) );
        add_action( 'admin_post_blt_export_pdf',       array( $this, 'handle_export_pdf' ) );
        add_action( 'admin_post_blt_save_settings',    array( $this, 'handle_save_settings' ) );
    }

    public function register_menu() {
        $badge      = BLT_Email::conta_notifiche_non_lette();
        $menu_label = 'Bandaloco';
        if ( $badge > 0 ) {
            $menu_label .= ' <span class="update-plugins count-' . $badge . '"><span class="plugin-count">' . $badge . '</span></span>';
        }
        add_menu_page(
            'ProLoco Tessere', $menu_label,
            'manage_options', 'blt-dashboard',
            array( $this, 'page_dashboard' ),
            'dashicons-id-alt', 30
        );
        add_submenu_page( 'blt-dashboard', 'Dashboard',    'Dashboard',   'manage_options', 'blt-dashboard',    array( $this, 'page_dashboard' ) );
        add_submenu_page( 'blt-dashboard', 'Tesserati',    'Tesserati',   'manage_options', 'blt-tesserati',    array( $this, 'page_tesserati' ) );
        add_submenu_page( 'blt-dashboard', 'Quote',        'Quote',       'manage_options', 'blt-quote',        array( $this, 'page_quote' ) );
        add_submenu_page( 'blt-dashboard', 'Impostazioni', 'Impostazioni','manage_options', 'blt-impostazioni', array( $this, 'page_impostazioni' ) );
        add_submenu_page( 'blt-dashboard', 'Diagnostica',  '🔧 Diagnostica', 'manage_options', 'blt-diagnostica',  array( $this, 'page_diagnostica' ) );
    }

    public function enqueue_scripts( $hook ) {
        if ( strpos( $hook, 'blt-' ) === false ) return;
        wp_enqueue_style(  'blt-admin', BLT_PLUGIN_URL . 'assets/css/admin.css', array(), BLT_VERSION );
        wp_enqueue_script( 'blt-admin', BLT_PLUGIN_URL . 'assets/js/admin.js',  array('jquery'), BLT_VERSION, true );
    }

    // ── Pages ──────────────────────────────────────────────────────────────────

    public function page_dashboard() {
        $stats = BLT_Database::get_statistiche();
        include BLT_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    public function page_tesserati() {
        $action = $_GET['action'] ?? 'list';
        if ( $action === 'edit' || $action === 'new' ) {
            $id         = isset($_GET['id']) ? absint($_GET['id']) : 0;
            $tesserato  = $id ? BLT_Database::get_tesserato($id) : null;
            $settings   = BLT_Database::get_all_settings();
            include BLT_PLUGIN_DIR . 'admin/views/tesserato-form.php';
        } elseif ( $action === 'view' ) {
            $id        = absint( $_GET['id'] ?? 0 );
            $tesserato = BLT_Database::get_tesserato( $id );
            $quote     = BLT_Database::get_quote_tesserato( $id );
            include BLT_PLUGIN_DIR . 'admin/views/tesserato-detail.php';
        } else {
            $search     = sanitize_text_field( $_GET['s'] ?? '' );
            $stato      = sanitize_text_field( $_GET['stato'] ?? '' );
            $direttivo  = sanitize_text_field( $_GET['direttivo'] ?? '' );
            $paged      = absint( $_GET['paged'] ?? 1 );
            $args       = array( 'search' => $search, 'stato' => $stato, 'direttivo' => $direttivo, 'paged' => $paged, 'per_page' => 20 );
            $tesserati  = BLT_Database::get_tesserati( $args );
            $totale     = BLT_Database::count_tesserati( $args );
            include BLT_PLUGIN_DIR . 'admin/views/tesserati-list.php';
        }
    }

    public function page_quote() {
        $search    = sanitize_text_field( $_GET['s'] ?? '' );
        $anno      = absint( $_GET['anno'] ?? date('Y') );
        $paged     = absint( $_GET['paged'] ?? 1 );
        global $wpdb;
        $like      = '%' . $wpdb->esc_like( $search ) . '%';
        $quote     = $wpdb->get_results( $wpdb->prepare(
            "SELECT q.*, t.nome, t.cognome, t.numero_tessera
             FROM {$wpdb->prefix}blt_quote q
             JOIN {$wpdb->prefix}blt_tesserati t ON t.id = q.tesserato_id
             WHERE q.anno = %d AND (t.cognome LIKE %s OR t.nome LIKE %s OR t.numero_tessera LIKE %s)
             ORDER BY t.cognome ASC LIMIT 20 OFFSET %d",
            $anno, $like, $like, $like, ($paged-1)*20
        ) );
        $totale_incasso = $wpdb->get_var( $wpdb->prepare(
            "SELECT SUM(importo) FROM {$wpdb->prefix}blt_quote WHERE anno=%d AND stato_pagamento='pagato'", $anno
        ) );
        include BLT_PLUGIN_DIR . 'admin/views/quote-list.php';
    }

    public function page_diagnostica() {
        include BLT_PLUGIN_DIR . 'admin/views/diagnostica.php';
    }

    public function page_impostazioni() {
        $settings = BLT_Database::get_all_settings();
        include BLT_PLUGIN_DIR . 'admin/views/impostazioni.php';
    }

    // ── Handlers ───────────────────────────────────────────────────────────────

    public function handle_save_tesserato() {
        check_admin_referer( 'blt_save_tesserato' );
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Accesso negato.' );

        $id_originale = absint( $_POST['tesserato_id'] ?? 0 );
        $nuovo        = ! $id_originale;
        $id           = BLT_Database::save_tesserato( $_POST, $id_originale );

        // Se l'insert è fallito $id sarà 0
        if ( ! $id ) {
            global $wpdb;
            $msg = urlencode( 'Errore nel salvataggio: ' . $wpdb->last_error );
            wp_redirect( admin_url( 'admin.php?page=blt-tesserati&action=' . ( $nuovo ? 'new' : 'edit&id=' . $id_originale ) . '&blt_error=' . $msg ) );
            exit;
        }

        // Crea utente WordPress se email fornita e non esiste
        if ( $nuovo && ! empty( $_POST['email'] ) && ! email_exists( sanitize_email( $_POST['email'] ) ) ) {
            $user_id = wp_create_user(
                sanitize_user( $_POST['email'] ),
                wp_generate_password(),
                sanitize_email( $_POST['email'] )
            );
            if ( ! is_wp_error( $user_id ) ) {
                $user = new WP_User( $user_id );
                $user->set_role( 'tesserato_bandaloco' );
                global $wpdb;
                $wpdb->update( $wpdb->prefix . 'blt_tesserati', array( 'user_id' => $user_id ), array( 'id' => $id ) );
                wp_new_user_notification( $user_id, null, 'user' );
            }
        }

        if ( $nuovo ) {
            $tesserato = BLT_Database::get_tesserato( $id );
            if ( $tesserato ) BLT_Email::invia_benvenuto( $tesserato );
        }

        wp_redirect( admin_url( 'admin.php?page=blt-tesserati&action=view&id=' . $id . '&saved=1' ) );
        exit;
    }

    public function handle_delete_tesserato() {
        check_admin_referer( 'blt_delete_tesserato' );
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Accesso negato.' );
        BLT_Database::delete_tesserato( absint( $_POST['tesserato_id'] ) );
        wp_redirect( admin_url( 'admin.php?page=blt-tesserati&deleted=1' ) );
        exit;
    }

    public function handle_save_quota() {
        check_admin_referer( 'blt_save_quota' );
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Accesso negato.' );
        $id    = absint( $_POST['quota_id'] ?? 0 );
        $qid   = BLT_Database::save_quota( $_POST, $id );

        // Se marcata come pagata: verifica importo e attiva tessera se sufficiente
        if ( isset($_POST['stato_pagamento']) && $_POST['stato_pagamento'] === 'pagato'
             && ! empty( $_POST['tesserato_id'] ) ) {

            $tesserato_id = absint( $_POST['tesserato_id'] );
            $tesserato    = BLT_Database::get_tesserato( $tesserato_id );
            $importo      = (float) ($_POST['importo'] ?? 0);
            $anno         = sanitize_text_field( $_POST['anno'] ?? date('Y') );

            if ( $tesserato ) {
                $attivato = BLT_Payment::attiva_se_quota_sufficiente(
                    $tesserato_id,
                    $tesserato->tipo_tessera,
                    $importo,
                    $anno
                );

                $quota = BLT_Database::get_quota( $tesserato_id, (int)$anno );
                if ( $quota ) BLT_Email::invia_conferma_pagamento( $tesserato, $quota );

                // Avvisa l'admin se importo insufficiente
                if ( ! $attivato ) {
                    $back = admin_url( 'admin.php?page=blt-tesserati&action=view&id=' . $tesserato_id . '&quota_saved=1&blt_warn=importo_basso' );
                    wp_redirect( $back );
                    exit;
                }
            }
        }

        $back = admin_url( 'admin.php?page=blt-tesserati&action=view&id=' . absint($_POST['tesserato_id']) . '&quota_saved=1' );
        wp_redirect( $back );
        exit;
    }

    public function handle_conferma_bonifico() {
        $quota_id     = absint( $_GET['quota_id']     ?? 0 );
        $tesserato_id = absint( $_GET['tesserato_id'] ?? 0 );
        check_admin_referer( 'blt_conferma_bonifico_' . $quota_id );
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Accesso negato.' );

        global $wpdb;
        $quota = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}blt_quote WHERE id = %d", $quota_id
        ) );

        if ( ! $quota ) wp_die( 'Quota non trovata.' );

        $tesserato = BLT_Database::get_tesserato( $tesserato_id );
        if ( ! $tesserato ) wp_die( 'Tesserato non trovato.' );

        // Aggiorna quota a pagato
        $wpdb->update(
            $wpdb->prefix . 'blt_quote',
            array(
                'stato_pagamento' => 'pagato',
                'data_pagamento'  => date('Y-m-d'),
                'ricevuta_numero' => 'BON-' . date('Ymd') . '-' . str_pad($quota_id, 4, '0', STR_PAD_LEFT),
            ),
            array( 'id' => $quota_id )
        );

        // Attiva tessera se importo sufficiente
        $attivato = BLT_Payment::attiva_se_quota_sufficiente(
            $tesserato_id,
            $tesserato->tipo_tessera,
            (float) $quota->importo,
            (string) $quota->anno
        );

        // Email conferma al tesserato
        $quota_aggiornata = BLT_Database::get_quota( $tesserato_id, (int)$quota->anno );
        if ( $quota_aggiornata ) {
            BLT_Email::invia_conferma_pagamento( $tesserato, $quota_aggiornata );
        }

        $warn = $attivato ? '' : '&blt_warn=importo_basso';
        wp_redirect( admin_url( 'admin.php?page=blt-quote&bonifico_confermato=1' . $warn ) );
        exit;
    }

    public function handle_export_csv() {
        check_admin_referer( 'blt_export' );
        if ( ! current_user_can( 'manage_options' ) ) wp_die();
        BLT_Export::export_csv( array( 'stato' => sanitize_text_field($_GET['stato'] ?? '') ) );
    }

    public function handle_export_pdf() {
        check_admin_referer( 'blt_export' );
        if ( ! current_user_can( 'manage_options' ) ) wp_die();
        BLT_Export::export_pdf( array( 'stato' => sanitize_text_field($_GET['stato'] ?? '') ) );
    }

    public function handle_save_settings() {
        check_admin_referer( 'blt_save_settings' );
        if ( ! current_user_can( 'manage_options' ) ) wp_die();
        $fields = array('nome_proloco','quota_ordinario','quota_sostenitore','quota_familiare','quota_junior',
                        'giorni_avviso','prefisso_tessera','email_notifiche','testo_tessera',
                        'google_client_id','google_client_secret',
                        'facebook_app_id','facebook_app_secret',
                        'stripe_publishable_key','stripe_secret_key','stripe_webhook_secret',
                        'paypal_client_id','paypal_secret',
                        'direttivo_titolo','direttivo_descrizione');
        // URL fields – sanitize as URL not as text
        // Toggle bonifico
        BLT_Database::set_setting( 'bonifico_enabled', isset($_POST['bonifico_enabled']) ? '1' : '0' );
        // Bonifico text fields
        foreach ( array('bonifico_iban','bonifico_intestato','bonifico_causale') as $bf ) {
            if ( isset($_POST[$bf]) ) BLT_Database::set_setting( $bf, sanitize_text_field($_POST[$bf]) );
        }
        if ( isset($_POST['bonifico_note']) ) BLT_Database::set_setting( 'bonifico_note', sanitize_textarea_field($_POST['bonifico_note']) );
        foreach ( array('pagina_iscrizione','pagina_area','logo_tessera') as $url_field ) {
            if ( isset($_POST[$url_field]) ) BLT_Database::set_setting( $url_field, esc_url_raw($_POST[$url_field]) );
        }
        unset($fields[array_search('logo_tessera',$fields)]);
        foreach ( $fields as $f ) {
            if ( isset( $_POST[$f] ) ) BLT_Database::set_setting( $f, sanitize_text_field($_POST[$f]) );
        }
        // Toggle social login (checkbox – assente se non spuntato)
        BLT_Database::set_setting( 'google_login_enabled',   isset($_POST['google_login_enabled'])   ? '1' : '0' );
        BLT_Database::set_setting( 'facebook_login_enabled', isset($_POST['facebook_login_enabled']) ? '1' : '0' );
        // Toggle gateway pagamento
        BLT_Database::set_setting( 'stripe_enabled',  isset($_POST['stripe_enabled'])  ? '1' : '0' );
        BLT_Database::set_setting( 'stripe_sandbox',  isset($_POST['stripe_sandbox'])  ? '1' : '0' );
        BLT_Database::set_setting( 'paypal_enabled',  isset($_POST['paypal_enabled'])  ? '1' : '0' );
        BLT_Database::set_setting( 'paypal_sandbox',  isset($_POST['paypal_sandbox'])  ? '1' : '0' );
        wp_redirect( admin_url('admin.php?page=blt-impostazioni&saved=1') );
        exit;
    }
}
