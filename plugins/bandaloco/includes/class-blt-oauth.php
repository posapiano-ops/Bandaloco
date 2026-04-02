<?php
/**
 * BLT_OAuth – Login sociale con Google e Facebook (OAuth 2.0 nativo, senza librerie esterne).
 *
 * Flusso:
 *  1. Utente clicca "Accedi con Google/Facebook"
 *  2. Redirect all'endpoint OAuth del provider
 *  3. Provider rimanda a ?blt_oauth_callback=google|facebook&code=XXX&state=YYY
 *  4. Scambiamo il code per un access token, recuperiamo il profilo
 *  5. Login o registrazione automatica dell'utente WordPress
 *  6. Redirect alla pagina originale
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BLT_OAuth {

    // ── Costanti provider ─────────────────────────────────────────────────────

    const GOOGLE_AUTH_URL  = 'https://accounts.google.com/o/oauth2/v2/auth';
    const GOOGLE_TOKEN_URL = 'https://oauth2.googleapis.com/token';
    const GOOGLE_USER_URL  = 'https://www.googleapis.com/oauth2/v3/userinfo';

    const FB_AUTH_URL      = 'https://www.facebook.com/v19.0/dialog/oauth';
    const FB_TOKEN_URL     = 'https://graph.facebook.com/v19.0/oauth/access_token';
    const FB_USER_URL      = 'https://graph.facebook.com/me';

    // ── Init ──────────────────────────────────────────────────────────────────

    public function __construct() {
        add_action( 'init', array( $this, 'handle_callback' ), 5 );
    }

    // ── URL helper ────────────────────────────────────────────────────────────

    public static function callback_url( $provider ) {
        return add_query_arg( 'blt_oauth_callback', $provider, home_url( '/' ) );
    }

    /**
     * Genera l'URL di autorizzazione e salva lo state in sessione.
     */
    public static function get_auth_url( $provider, $redirect_to = '' ) {
        $state = wp_generate_password( 16, false );
        set_transient( 'blt_oauth_state_'    . $state, $state,                   600 );
        set_transient( 'blt_oauth_redirect_' . $state, $redirect_to ?: home_url('/'), 600 );

        switch ( $provider ) {
            case 'google':
                $client_id = BLT_Database::get_setting('google_client_id');
                if ( ! $client_id ) return '';
                return self::GOOGLE_AUTH_URL . '?' . http_build_query([
                    'client_id'     => $client_id,
                    'redirect_uri'  => self::callback_url('google'),
                    'response_type' => 'code',
                    'scope'         => 'openid email profile',
                    'state'         => $state,
                    'prompt'        => 'select_account',
                ]);

            case 'facebook':
                $app_id = BLT_Database::get_setting('facebook_app_id');
                if ( ! $app_id ) return '';
                return self::FB_AUTH_URL . '?' . http_build_query([
                    'client_id'     => $app_id,
                    'redirect_uri'  => self::callback_url('facebook'),
                    'response_type' => 'code',
                    'scope'         => 'email,public_profile',
                    'state'         => $state,
                ]);
        }
        return '';
    }

    // ── Callback handler ──────────────────────────────────────────────────────

    public function handle_callback() {
        $provider = sanitize_key( $_GET['blt_oauth_callback'] ?? '' );
        if ( ! in_array( $provider, ['google', 'facebook'], true ) ) return;

        // Verifica state CSRF tramite transient (no session, funziona su tutti gli hosting)
        $state         = sanitize_text_field( $_GET['state'] ?? '' );
        $saved_state   = $state ? get_transient( 'blt_oauth_state_'    . $state ) : '';
        $redirect_back = $state ? get_transient( 'blt_oauth_redirect_' . $state ) : home_url('/');
        $redirect_back = $redirect_back ?: home_url('/');

        if ( ! $state || $state !== $saved_state ) {
            wp_safe_redirect( add_query_arg('blt_oauth_error', 'state', $redirect_back) );
            exit;
        }

        $code = sanitize_text_field( $_GET['code'] ?? '' );
        if ( ! $code ) {
            wp_safe_redirect( add_query_arg('blt_oauth_error', 'no_code', $redirect_back) );
            exit;
        }

        delete_transient( 'blt_oauth_state_'    . $state );
        delete_transient( 'blt_oauth_redirect_' . $state );

        try {
            $profile = $provider === 'google'
                ? $this->google_get_profile( $code )
                : $this->facebook_get_profile( $code );

            if ( ! $profile || empty( $profile['email'] ) ) {
                throw new Exception('Impossibile recuperare email dal provider.');
            }

            $user_id = $this->login_or_register( $profile, $provider );
            wp_set_auth_cookie( $user_id, true );
            wp_safe_redirect( $redirect_back );
            exit;

        } catch ( Exception $e ) {
            wp_safe_redirect( add_query_arg('blt_oauth_error', urlencode($e->getMessage()), $redirect_back) );
            exit;
        }
    }

    // ── Google ────────────────────────────────────────────────────────────────

    private function google_get_profile( $code ) {
        $client_id     = BLT_Database::get_setting('google_client_id');
        $client_secret = BLT_Database::get_setting('google_client_secret');

        // Scambia code per token
        $token_resp = wp_remote_post( self::GOOGLE_TOKEN_URL, [
            'body' => [
                'code'          => $code,
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri'  => self::callback_url('google'),
                'grant_type'    => 'authorization_code',
            ],
        ]);
        $this->check_http_response( $token_resp, 'Google token' );
        $token_data   = json_decode( wp_remote_retrieve_body($token_resp), true );
        $access_token = $token_data['access_token'] ?? '';
        if ( ! $access_token ) throw new Exception('Token Google non ricevuto.');

        // Recupera profilo
        $user_resp = wp_remote_get( self::GOOGLE_USER_URL, [
            'headers' => [ 'Authorization' => 'Bearer ' . $access_token ],
        ]);
        $this->check_http_response( $user_resp, 'Google userinfo' );
        $data = json_decode( wp_remote_retrieve_body($user_resp), true );

        return [
            'provider'   => 'google',
            'provider_id'=> $data['sub'] ?? '',
            'email'      => $data['email'] ?? '',
            'nome'       => $data['given_name'] ?? '',
            'cognome'    => $data['family_name'] ?? '',
            'foto'       => $data['picture'] ?? '',
            'display'    => $data['name'] ?? '',
        ];
    }

    // ── Facebook ──────────────────────────────────────────────────────────────

    private function facebook_get_profile( $code ) {
        $app_id     = BLT_Database::get_setting('facebook_app_id');
        $app_secret = BLT_Database::get_setting('facebook_app_secret');

        // Scambia code per token
        $token_resp = wp_remote_get( self::FB_TOKEN_URL . '?' . http_build_query([
            'client_id'     => $app_id,
            'client_secret' => $app_secret,
            'redirect_uri'  => self::callback_url('facebook'),
            'code'          => $code,
        ]));
        $this->check_http_response( $token_resp, 'Facebook token' );
        $token_data   = json_decode( wp_remote_retrieve_body($token_resp), true );
        $access_token = $token_data['access_token'] ?? '';
        if ( ! $access_token ) throw new Exception('Token Facebook non ricevuto.');

        // Recupera profilo (id, name, email, first_name, last_name, picture)
        $user_resp = wp_remote_get( self::FB_USER_URL . '?' . http_build_query([
            'fields'       => 'id,name,email,first_name,last_name,picture.type(large)',
            'access_token' => $access_token,
        ]));
        $this->check_http_response( $user_resp, 'Facebook me' );
        $data = json_decode( wp_remote_retrieve_body($user_resp), true );

        if ( empty($data['email']) ) {
            throw new Exception('Facebook non ha condiviso l\'indirizzo email. Verifica che l\'account abbia un\'email confermata.');
        }

        return [
            'provider'   => 'facebook',
            'provider_id'=> $data['id'] ?? '',
            'email'      => $data['email'] ?? '',
            'nome'       => $data['first_name'] ?? '',
            'cognome'    => $data['last_name'] ?? '',
            'foto'       => $data['picture']['data']['url'] ?? '',
            'display'    => $data['name'] ?? '',
        ];
    }

    // ── Login / Registrazione ─────────────────────────────────────────────────

    private function login_or_register( array $profile, string $provider ): int {
        $email    = sanitize_email( $profile['email'] );
        $meta_key = 'blt_' . $provider . '_id';

        // 1. Cerca per provider ID (login ripetuto)
        $users = get_users([
            'meta_key'   => $meta_key,
            'meta_value' => sanitize_text_field($profile['provider_id']),
            'number'     => 1,
        ]);
        if ( ! empty($users) ) {
            return $users[0]->ID;
        }

        // 2. Cerca per email (account già esistente)
        $user = get_user_by( 'email', $email );
        if ( $user ) {
            // Collega il provider all'account esistente
            update_user_meta( $user->ID, $meta_key, $profile['provider_id'] );
            if ( ! empty($profile['foto']) ) update_user_meta( $user->ID, 'blt_avatar_url', esc_url_raw($profile['foto']) );
            return $user->ID;
        }

        // 3. Nuovo utente
        $username = $this->genera_username( $profile );
        $user_id  = wp_insert_user([
            'user_login'   => $username,
            'user_email'   => $email,
            'first_name'   => sanitize_text_field($profile['nome']),
            'last_name'    => sanitize_text_field($profile['cognome']),
            'display_name' => sanitize_text_field($profile['display']),
            'user_pass'    => wp_generate_password(20),
            'role'         => 'tesserato_bandaloco',
        ]);

        if ( is_wp_error($user_id) ) {
            throw new Exception( $user_id->get_error_message() );
        }

        update_user_meta( $user_id, $meta_key, $profile['provider_id'] );
        if ( ! empty($profile['foto']) ) update_user_meta( $user_id, 'blt_avatar_url', esc_url_raw($profile['foto']) );

        // Notifica admin di nuovo iscritto social
        $admin_email = BLT_Database::get_setting('email_notifiche', get_option('admin_email'));
        wp_mail(
            $admin_email,
            'Nuovo accesso social – ' . ucfirst($provider),
            sprintf(
                "Un nuovo utente ha effettuato l'accesso tramite %s:\n\nNome: %s\nEmail: %s\n\nVerifica in WP Admin > Bandaloco se associare manualmente la tessera.",
                ucfirst($provider), $profile['display'], $email
            )
        );

        return $user_id;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function genera_username( array $profile ): string {
        $base = sanitize_user( strtolower( $profile['nome'] . '.' . $profile['cognome'] ) );
        $base = $base ?: sanitize_user( explode('@', $profile['email'])[0] );
        $user = $base;
        $i    = 1;
        while ( username_exists($user) ) $user = $base . $i++;
        return $user;
    }

    private function check_http_response( $response, string $label ): void {
        if ( is_wp_error($response) ) {
            throw new Exception( $label . ' – errore di rete: ' . $response->get_error_message() );
        }
        $code = wp_remote_retrieve_response_code($response);
        if ( $code < 200 || $code >= 300 ) {
            throw new Exception( $label . ' – risposta HTTP ' . $code );
        }
    }

    // ── Utility pubblica ──────────────────────────────────────────────────────

    /**
     * Restituisce true se il provider è configurato e abilitato.
     */
    public static function is_enabled( string $provider ): bool {
        switch ( $provider ) {
            case 'google':
                return (bool) BLT_Database::get_setting('google_client_id')
                    && (bool) BLT_Database::get_setting('google_client_secret')
                    && BLT_Database::get_setting('google_login_enabled') === '1';
            case 'facebook':
                return (bool) BLT_Database::get_setting('facebook_app_id')
                    && (bool) BLT_Database::get_setting('facebook_app_secret')
                    && BLT_Database::get_setting('facebook_login_enabled') === '1';
        }
        return false;
    }

    /**
     * Renderizza i pulsanti social login (HTML pronto).
     */
    public static function render_buttons( string $redirect_to = '', bool $echo = true ): string {
        $google_url   = self::is_enabled('google')   ? self::get_auth_url('google',   $redirect_to) : '';
        $facebook_url = self::is_enabled('facebook') ? self::get_auth_url('facebook', $redirect_to) : '';

        if ( ! $google_url && ! $facebook_url ) return '';

        $html  = '<div class="blt-social-login">';
        $html .= '<div class="blt-social-divider"><span>oppure accedi con</span></div>';
        $html .= '<div class="blt-social-buttons">';

        if ( $google_url ) {
            $html .= '<a href="' . esc_url($google_url) . '" class="blt-social-btn blt-btn-google">'
                   . '<svg viewBox="0 0 24 24" width="20" height="20"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>'
                   . ' Accedi con Google</a>';
        }

        if ( $facebook_url ) {
            $html .= '<a href="' . esc_url($facebook_url) . '" class="blt-social-btn blt-btn-facebook">'
                   . '<svg viewBox="0 0 24 24" width="20" height="20" fill="#fff"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>'
                   . ' Accedi con Facebook</a>';
        }

        $html .= '</div></div>';

        if ( $echo ) { echo $html; return ''; }
        return $html;
    }
}
