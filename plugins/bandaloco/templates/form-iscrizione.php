<?php
/**
 * [proloco_iscrizione] – Registrazione account WordPress.
 * Solo crea l'account. Il completamento della tessera avviene nell'area riservata.
 */
if ( ! defined('ABSPATH') ) exit;

$settings    = BLT_Database::get_all_settings();
$area_url    = ! empty($settings['pagina_area']) ? $settings['pagina_area'] : home_url('/');
$current_url = get_permalink();

// Già loggato
if ( is_user_logged_in() ) {
    echo '<div class="blt-notice blt-notice-success">✅ Sei già registrato. '
        . '<a href="' . esc_url($area_url) . '">Vai alla tua area riservata →</a></div>';
    return;
}

// Registrazione completata
if ( isset($_GET['blt_registrato']) ) {
    echo '<div class="blt-notice blt-notice-success">'
        . '✅ <strong>Account creato!</strong> Controlla la tua email e '
        . '<a href="' . esc_url($area_url) . '">accedi alla tua area riservata</a> per completare l\'iscrizione.'
        . '</div>';
    return;
}

$errore = '';
$email  = '';

if ( isset($_POST['blt_registrazione']) && wp_verify_nonce($_POST['blt_nonce'] ?? '', 'blt_registrazione') ) {

    $email     = sanitize_email( $_POST['email'] ?? '' );
    $password  = $_POST['password']  ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ( ! is_email($email) ) {
        $errore = 'Inserisci un indirizzo email valido.';
    } elseif ( strlen($password) < 8 ) {
        $errore = 'La password deve essere di almeno 8 caratteri.';
    } elseif ( $password !== $password2 ) {
        $errore = 'Le due password non coincidono.';
    } elseif ( email_exists($email) ) {
        $errore = 'Questa email è già registrata. '
            . '<a href="' . esc_url( add_query_arg('BLT_forgot','1',$area_url) ) . '">Password dimenticata?</a>';
    } elseif ( empty($_POST['privacy']) ) {
        $errore = 'Devi accettare il trattamento dei dati personali.';
    } else {
        $user_id = wp_create_user( $email, $password, $email );
        if ( is_wp_error($user_id) ) {
            $errore = $user_id->get_error_message();
        } else {
            $user = new WP_User($user_id);
            $user->set_role('tesserato_bandaloco');
            wp_set_auth_cookie( $user_id, true );
            wp_safe_redirect( add_query_arg('blt_nuovo','1', $area_url) );
            exit;
        }
    }
}
?>

<div class="blt-form-iscrizione" id="blt-iscrizione">

    <h3>Crea il tuo account</h3>
    <p>Registrati con email e password. Dopo potrai completare i dati per ottenere la tua tessera.</p>

    <?php if ( $errore ) : ?>
        <div class="blt-notice blt-notice-error">⚠️ <?= $errore ?></div>
    <?php endif; ?>

    <form method="post" class="blt-public-form" novalidate>
        <?php wp_nonce_field('blt_registrazione','blt_nonce'); ?>
        <input type="hidden" name="blt_registrazione" value="1">

        <label>Email *
            <input type="email" name="email" value="<?= esc_attr($email) ?>" required autofocus autocomplete="email">
        </label>
        <label>Password * <span style="font-weight:400;color:var(--blt-muted);font-size:12px;">(minimo 8 caratteri)</span>
            <input type="password" name="password" required minlength="8" autocomplete="new-password">
        </label>
        <label>Conferma Password *
            <input type="password" name="password2" required minlength="8" autocomplete="new-password">
        </label>
        <label class="blt-checkbox-label">
            <input type="checkbox" name="privacy" required>
            Acconsento al trattamento dei dati personali ai sensi del GDPR *
        </label>

        <button type="submit" class="blt-btn-primary" style="width:100%;">Crea Account</button>
    </form>

    <?php BLT_OAuth::render_buttons( $area_url ); ?>

    <p style="text-align:center;margin-top:16px;font-size:13px;color:var(--blt-muted);">
        Hai già un account? <a href="<?= esc_url($area_url) ?>">Accedi →</a>
    </p>

</div>
