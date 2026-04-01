<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BLT_Public {

    public function __construct() {
        add_action( 'wp_enqueue_scripts',  array( $this, 'enqueue_scripts' ) );
        add_action( 'init',                array( $this, 'handle_qr_verify' ) );
        add_action( 'init',                array( $this, 'handle_public_forms' ) );

        // Nasconde la barra admin WP per i tesserati
        add_filter( 'show_admin_bar',      array( $this, 'hide_admin_bar' ),            10, 1 );
        add_action( 'after_setup_theme',   array( $this, 'hide_admin_bar_theme' ) );

        // Porta login/logout/password dimenticata sul frontend
        add_filter( 'login_url',           array( $this, 'filter_login_url' ),         10, 3 );
        add_filter( 'logout_url',          array( $this, 'filter_logout_url' ),         10, 2 );
        add_filter( 'lostpassword_url',    array( $this, 'filter_lostpassword_url' ),   10, 2 );
        add_filter( 'login_redirect',      array( $this, 'filter_login_redirect' ),     10, 3 );

        // Blocca /wp-login.php per i non-admin → rimanda al frontend
        add_action( 'login_init',          array( $this, 'redirect_wplogin' ) );

        // Intercetta reset password da email → rimanda al frontend
        add_action( 'login_form_rp',       array( $this, 'intercept_password_reset' ) );
        add_action( 'login_form_resetpass', array( $this, 'intercept_password_reset' ) );
    }

    public function hide_admin_bar( $show ) {
        if ( ! is_user_logged_in() ) return $show;
        $user = wp_get_current_user();
        if ( in_array('tesserato_bandaloco', (array)$user->roles, true) ) return false;
        return $show;
    }

    public function hide_admin_bar_theme() {
        if ( ! is_user_logged_in() ) return;
        $user = wp_get_current_user();
        if ( in_array('tesserato_bandaloco', (array)$user->roles, true) ) {
            show_admin_bar(false);
        }
    }

    /** Sostituisce login_url() con la pagina area riservata */
    public function filter_login_url( $login_url, $redirect, $force_reauth ) {
        $area = BLT_Database::get_setting('pagina_area');
        if ( ! $area ) return $login_url;
        return $redirect ? add_query_arg('redirect_to', urlencode($redirect), $area) : $area;
    }

    /** logout_url() rimane funzionale ma reindirizza al frontend dopo il logout */
    public function filter_logout_url( $logout_url, $redirect ) {
        $area = BLT_Database::get_setting('pagina_area');
        if ( ! $area ) return $logout_url;
        $after = $redirect ?: add_query_arg('loggedout','true', $area);
        // Mantieni il nonce WP, cambia solo redirect_to
        return add_query_arg( 'redirect_to', urlencode($after), $logout_url );
    }

    /** lostpassword_url() → link "Password dimenticata?" dell'area riservata */
    public function filter_lostpassword_url( $lostpassword_url, $redirect ) {
        $area = BLT_Database::get_setting('pagina_area');
        if ( ! $area ) return $lostpassword_url;
        return add_query_arg('blt_forgot','1', $area);
    }

    /** Dopo login WP nativo (es. OAuth) i tesserati vanno all'area, non a /wp-admin */
    public function filter_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
        if ( is_wp_error($user) ) return $redirect_to;
        if ( $user->has_cap('edit_posts') ) return $redirect_to; // admin → wp-admin
        $area = BLT_Database::get_setting('pagina_area');
        return $area ?: $redirect_to;
    }

    /** Reindirizza /wp-login.php al frontend per i non-admin */
    public function redirect_wplogin() {
        $action = sanitize_key( $_REQUEST['action'] ?? 'login' );
        // Lascia passare logout, reset password, 2FA
        if ( in_array($action, array('logout','resetpass','rp','validate_2fa','postpass'), true) ) return;
        // Lascia passare gli admin già loggati
        if ( is_user_logged_in() && current_user_can('edit_posts') ) return;
        $area = BLT_Database::get_setting('pagina_area');
        if ( $area ) {
            wp_safe_redirect( $area );
            exit;
        }
    }

    /**
     * Intercetta il link di reset password che WP invia via email.
     * WP manda: /wp-login.php?action=rp&key=XXX&login=YYY
     * Noi lo gestiamo in una pagina frontend con ?BLT_reset_key=XXX&BLT_reset_login=YYY
     */
    public function intercept_password_reset() {
        $area_url = BLT_Database::get_setting('pagina_area');
        if ( ! $area_url ) return;

        $key   = sanitize_text_field( $_GET['key']   ?? '' );
        $login = sanitize_text_field( $_GET['login'] ?? '' );
        if ( ! $key || ! $login ) return;

        wp_safe_redirect( add_query_arg( array(
            'blt_reset_key'   => $key,
            'blt_reset_login' => rawurlencode($login),
        ), $area_url ) );
        exit;
    }

    public function enqueue_scripts() {
        if ( is_page() ) {
            wp_enqueue_style(  'blt-public', BLT_PLUGIN_URL . 'assets/css/public.css', array(), BLT_VERSION );
            wp_enqueue_script( 'blt-public', BLT_PLUGIN_URL . 'assets/js/public.js',  array('jquery'), BLT_VERSION, true );
            wp_localize_script( 'blt-public', 'bltAjax', array(
                'url'   => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('blt_payment_nonce'),
            ) );
        }
    }

    public function handle_qr_verify() {
        if ( ! isset( $_GET['blt_verify'] ) || ! isset( $_GET['token'] ) ) return;
        $token     = sanitize_text_field( $_GET['token'] );
        $tesserato = BLT_Database::get_tesserato_by_token( $token );
        include BLT_PLUGIN_DIR . 'templates/verify-tessera.php';
        exit;
    }

    public function handle_public_forms() {
        if ( ! isset( $_POST['blt_action'] ) ) return;

        switch ( $_POST['blt_action'] ) {
            case 'login':
                $this->process_login();
                break;
            case 'aggiorna_profilo':
                $this->process_aggiorna_profilo();
                break;
            case 'recupera_password':
                $this->process_recupera_password();
                break;
            case 'reset_password':
                $this->process_reset_password();
                break;
        }
    }

    /**
     * Recupero password frontend — invia email reset senza passare per /wp-login.php
     */
    private function process_login() {
        if ( ! wp_verify_nonce( $_POST['blt_nonce'] ?? '', 'blt_login' ) ) {
            wp_die('Sessione scaduta. Ricaricare la pagina.');
        }

        $redirect = sanitize_url( $_POST['blt_redirect'] ?? '' );
        if ( ! $redirect || ! wp_validate_redirect($redirect) ) {
            $redirect = home_url('/');
        }

        $login    = sanitize_text_field( $_POST['log'] ?? '' );
        $password = $_POST['pwd'] ?? '';

        if ( empty($login) || empty($password) ) {
            wp_safe_redirect( add_query_arg('blt_login_error', 'empty', $redirect) );
            exit;
        }

        $user = wp_authenticate( $login, $password );

        if ( is_wp_error($user) ) {
            wp_safe_redirect( add_query_arg(array(
                'blt_login_error' => 'wrong',
                'user_login'      => urlencode($login),
            ), $redirect) );
            exit;
        }

        wp_set_auth_cookie( $user->ID, ! empty($_POST['rememberme']) );
        wp_safe_redirect( $redirect );
        exit;
    }

    private function process_recupera_password() {
        if ( ! wp_verify_nonce( $_POST['blt_nonce'] ?? '', 'blt_recupera_password' ) ) {
            wp_die('Sessione scaduta.');
        }

        $login    = sanitize_text_field( $_POST['user_login'] ?? '' );
        $redirect = sanitize_url( $_POST['blt_redirect'] ?? '' );

        // Fallback sicuro: pagina area riservata o home
        if ( ! $redirect || ! wp_validate_redirect($redirect) ) {
            $area     = BLT_Database::get_setting('pagina_area');
            $redirect = $area ? add_query_arg('blt_forgot','1',$area) : home_url('/');
        }

        if ( empty($login) ) {
            wp_safe_redirect( add_query_arg('blt_pwd_error', 'empty', $redirect) );
            exit;
        }

        $user = is_email($login)
            ? get_user_by('email', $login)
            : get_user_by('login', $login);

        if ( ! $user ) {
            // Non riveliamo se l'account esiste o meno per sicurezza
            wp_safe_redirect( add_query_arg('blt_pwd_sent', '1', $redirect) );
            exit;
        }

        $result = retrieve_password( $user->user_login );
        if ( is_wp_error($result) ) {
            wp_safe_redirect( add_query_arg('blt_pwd_error', 'send', $redirect) );
            exit;
        }

        wp_safe_redirect( add_query_arg('blt_pwd_sent', '1', $redirect) );
        exit;
    }

    /**
     * Aggiornamento dati profilo dall'area riservata frontend.
     */
    /**
     * Applica la nuova password dopo il click sul link ricevuto via email.
     */
    private function process_reset_password() {
        if ( ! wp_verify_nonce( $_POST['blt_nonce'] ?? '', 'blt_reset_password' ) ) {
            wp_die('Sessione scaduta.');
        }

        $key      = sanitize_text_field( $_POST['blt_reset_key']   ?? '' );
        $login    = sanitize_text_field( $_POST['blt_reset_login']  ?? '' );
        $password = $_POST['password']  ?? '';
        $password2= $_POST['password2'] ?? '';
        $area_url = BLT_Database::get_setting('pagina_area') ?: home_url('/');
        $referer  = wp_get_referer() ?: $area_url;

        if ( strlen($password) < 8 ) {
            wp_safe_redirect( add_query_arg( array('blt_reset_error'=>'short','blt_reset_key'=>$key,'blt_reset_login'=>rawurlencode($login)), $referer ) );
            exit;
        }
        if ( $password !== $password2 ) {
            wp_safe_redirect( add_query_arg( array('blt_reset_error'=>'mismatch','blt_reset_key'=>$key,'blt_reset_login'=>rawurlencode($login)), $referer ) );
            exit;
        }

        $user = check_password_reset_key( $key, $login );
        if ( is_wp_error($user) ) {
            wp_safe_redirect( add_query_arg('blt_reset_error','expired', $area_url) );
            exit;
        }

        reset_password( $user, $password );
        wp_set_auth_cookie( $user->ID, true );
        wp_safe_redirect( add_query_arg('blt_pwd_changed','1', $area_url) );
        exit;
    }

    private function process_aggiorna_profilo() {
        if ( ! is_user_logged_in() ) wp_die('Accesso negato.');
        if ( ! wp_verify_nonce( $_POST['blt_nonce'] ?? '', 'blt_aggiorna_profilo' ) ) {
            wp_die('Sessione scaduta. Ricaricare la pagina.');
        }

        $user_id   = get_current_user_id();
        $tesserato = BLT_Database::get_tesserato_by_user( $user_id );
        if ( ! $tesserato ) wp_die('Accesso negato.');

        $campi_aggiornabili = array(
            'telefono','indirizzo','cap','citta','provincia',
        );
        $data = array();
        foreach ( $campi_aggiornabili as $c ) {
            $data[ $c ] = sanitize_text_field( $_POST[ $c ] ?? '' );
        }

        // Aggiorna email anche su WP se cambiata
        $nuova_email = sanitize_email( $_POST['email'] ?? '' );
        if ( is_email($nuova_email) && $nuova_email !== $tesserato->email ) {
            if ( ! email_exists($nuova_email) || email_exists($nuova_email) === $user_id ) {
                $data['email'] = $nuova_email;
                wp_update_user( array( 'ID' => $user_id, 'user_email' => $nuova_email ) );
            }
        }

        BLT_Database::save_tesserato( $data, $tesserato->id );

        $area = BLT_Database::get_setting('pagina_area') ?: home_url('/');
        $redirect = add_query_arg( array('blt_tab' => 'profilo', 'blt_profile_saved' => '1'), $area );
        wp_safe_redirect( $redirect );
        exit;
    }

    /**
     * Crea tesserato + utente WP da dati form iscrizione frontend.
     * Chiamato dal template form-iscrizione.php quando non ci sono gateway configurati.
     */
    public static function crea_tesserato_da_form( array $dati ): int {
        global $wpdb;

        $email = sanitize_email( $dati['email'] ?? '' );
        if ( ! is_email($email) ) return 0;

        // Se l'utente è loggato usa la sua email come priorità
        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            if ( empty($email) ) $email = $current_user->user_email;
        }

        // Se esiste già un tesserato con questa email non ne creiamo un secondo
        $esistente = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}blt_tesserati WHERE email = %s LIMIT 1", $email
        ) );
        if ( $esistente ) return (int) $esistente;

        $payload = array_merge( $dati, array(
            'email'           => $email,
            'stato'           => 'nuovo',
            'tipo_tessera'    => $dati['tipo_tessera'] ?: 'ordinario',
            'data_iscrizione' => date('Y-m-d'),
            'data_scadenza'   => date('Y') . '-12-31',
        ) );

        $id = BLT_Database::save_tesserato( $payload );
        if ( ! $id ) return 0;

        // Collega all'utente WP loggato oppure crea nuovo utente
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            wp_update_user( array(
                'ID'           => $user_id,
                'display_name' => trim(($dati['nome']??'') . ' ' . ($dati['cognome']??'')),
                'first_name'   => $dati['nome']    ?? '',
                'last_name'    => $dati['cognome']  ?? '',
            ) );
            $wpdb->update(
                $wpdb->prefix . 'blt_tesserati',
                array( 'user_id' => $user_id ),
                array( 'id'      => $id )
            );
        } elseif ( ! email_exists($email) ) {
            // Crea nuovo utente WP (flusso senza step 1)
            $password = wp_generate_password(10, false);
            $user_id  = wp_create_user( $email, $password, $email );
            if ( ! is_wp_error($user_id) ) {
                $user = new WP_User($user_id);
                $user->set_role('tesserato_bandaloco');
                wp_update_user( array(
                    'ID'           => $user_id,
                    'display_name' => trim(($dati['nome']??'') . ' ' . ($dati['cognome']??'')),
                    'first_name'   => $dati['nome']    ?? '',
                    'last_name'    => $dati['cognome']  ?? '',
                ) );
                $wpdb->update(
                    $wpdb->prefix . 'blt_tesserati',
                    array( 'user_id' => $user_id ),
                    array( 'id'      => $id )
                );
                wp_new_user_notification( $user_id, null, 'user' );
            }
        } else {
            $existing_user_id = email_exists($email);
            $wpdb->update(
                $wpdb->prefix . 'blt_tesserati',
                array( 'user_id' => $existing_user_id ),
                array( 'id'      => $id )
            );
        }

        $tesserato = BLT_Database::get_tesserato($id);
        if ( $tesserato ) {
            BLT_Email::invia_benvenuto( $tesserato );
            BLT_Email::notifica_admin_nuova_iscrizione( $tesserato );
        }

        return $id;
    }
}
