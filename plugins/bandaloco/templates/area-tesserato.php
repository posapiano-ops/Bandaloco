<?php if ( ! defined('ABSPATH') ) exit;

$current_url = get_permalink();
$settings    = BLT_Database::get_all_settings();
$iscr_url    = ! empty($settings['pagina_iscrizione']) ? $settings['pagina_iscrizione'] : '';

// ════════════════════════════════════════════════════════════════════════════
// NON LOGGATO
// ════════════════════════════════════════════════════════════════════════════
if ( ! is_user_logged_in() ) :

    $reset_key    = sanitize_text_field( $_GET['blt_reset_key']   ?? '' );
    $reset_login  = sanitize_text_field( $_GET['blt_reset_login']  ?? '' );
    $mostra_reset = $reset_key && $reset_login;
    $mostra_pwd   = ! $mostra_reset && isset($_GET['blt_forgot']);
    $pwd_changed  = isset($_GET['blt_pwd_changed']);

    if ( $pwd_changed ) : ?>
        <div class="blt-login-box">
            <div class="blt-notice blt-notice-success">✅ Password aggiornata. Ora puoi accedere.</div>
            <p style="text-align:center;margin-top:14px;font-size:13px;"><a href="<?= esc_url($current_url) ?>">← Vai al login</a></p>
        </div>

    <?php elseif ( $mostra_reset ) :
        $reset_msgs = array('short'=>'La password deve essere di almeno 8 caratteri.','mismatch'=>'Le due password non coincidono.','expired'=>'Il link è scaduto. Richiedi un nuovo link.');
        $reset_err  = $reset_msgs[ sanitize_key($_GET['blt_reset_error'] ?? '') ] ?? '';
    ?>
        <div class="blt-login-box">
            <h3>Imposta Nuova Password</h3>
            <?php if ( $reset_err ) : ?>
                <div class="blt-notice blt-notice-error">⚠️ <?= esc_html($reset_err) ?></div>
            <?php endif; ?>
            <form method="post" class="blt-public-form">
                <?php wp_nonce_field('blt_reset_password','blt_nonce'); ?>
                <input type="hidden" name="blt_action"      value="reset_password">
                <input type="hidden" name="blt_reset_key"   value="<?= esc_attr($reset_key) ?>">
                <input type="hidden" name="blt_reset_login" value="<?= esc_attr($reset_login) ?>">
                <label>Nuova Password <span style="font-weight:400;color:var(--blt-muted);font-size:12px;">(minimo 8 caratteri)</span>
                    <input type="password" name="password" required minlength="8" autocomplete="new-password" autofocus>
                </label>
                <label>Conferma Password
                    <input type="password" name="password2" required minlength="8" autocomplete="new-password">
                </label>
                <button type="submit" class="blt-btn-primary" style="width:100%;">Salva Nuova Password</button>
            </form>
        </div>

    <?php elseif ( $mostra_pwd ) : ?>
        <div class="blt-login-box">
            <h3>Recupera Password</h3>
            <p>Inserisci la tua email per ricevere il link di reset.</p>
            <?php if ( isset($_GET['blt_pwd_sent']) ) : ?>
                <div class="blt-notice blt-notice-success">
                    ✅ <strong>Email inviata!</strong><br>
                    Controlla la tua casella di posta (anche la cartella spam).<br>
                    Riceverai a breve un link per impostare una nuova password.
                </div>
                <p style="text-align:center;margin-top:14px;font-size:13px;"><a href="<?= esc_url($current_url) ?>">← Torna al login</a></p>
            <?php else : ?>
                <?php
                $pwd_error = sanitize_key($_GET['blt_pwd_error'] ?? '');
                $error_msgs = array(
                    'empty' => 'Inserisci la tua email o nome utente.',
                    'send'  => 'Impossibile inviare l\'email. Riprova o contatta la segreteria.',
                );
                if ( $pwd_error && isset($error_msgs[$pwd_error]) ) : ?>
                    <div class="blt-notice blt-notice-error">⚠️ <?= $error_msgs[$pwd_error] ?></div>
                <?php endif; ?>
                <form method="post" class="blt-public-form">
                    <?php wp_nonce_field('blt_recupera_password','blt_nonce'); ?>
                    <input type="hidden" name="blt_action" value="recupera_password">
                    <input type="hidden" name="blt_redirect" value="<?= esc_attr( add_query_arg('blt_forgot','1', $current_url) ) ?>">
                    <label>Email o Nome Utente
                        <input type="text" name="user_login" required autofocus value="<?= esc_attr($_GET['user_login'] ?? '') ?>">
                    </label>
                    <button type="submit" class="blt-btn-primary" style="width:100%;">Invia Link di Reset</button>
                </form>
                <p style="text-align:center;margin-top:14px;font-size:13px;"><a href="<?= esc_url($current_url) ?>">← Torna al login</a></p>
            <?php endif; ?>
        </div>

    <?php else : ?>
        <div class="blt-login-box">
            <h3>Area Riservata Tesserati</h3>
            <p>Accedi per visualizzare la tua tessera digitale.</p>

            <?php
            $login_error = sanitize_text_field($_GET['blt_login_error'] ?? '');
            $login_msgs  = array(
                'empty' => 'Inserisci email e password.',
                'wrong' => 'Email o password non corretti.',
            );
            if ( $login_error && isset($login_msgs[$login_error]) ) :
                echo '<div class="blt-notice blt-notice-error" style="margin-bottom:14px;">⚠️ ' . $login_msgs[$login_error] . '</div>';
            endif;
            if ( ! empty($_GET['blt_oauth_error']) ) :
                $msgs = array('state' => 'Sessione scaduta. Riprova.', 'no_code' => 'Accesso annullato.');
                $msg  = $msgs[ sanitize_text_field($_GET['blt_oauth_error']) ] ?? 'Errore durante il login sociale.';
                echo '<div class="blt-notice blt-notice-error" style="margin-bottom:14px;">⚠️ ' . esc_html($msg) . '</div>';
            endif;
            if ( ! empty($_GET['loggedout']) ) :
                echo '<div class="blt-notice blt-notice-success" style="margin-bottom:14px;">✅ Hai effettuato il logout.</div>';
            endif;
            ?>

            <form method="post" class="blt-public-form" novalidate>
                <?php wp_nonce_field('blt_login','blt_nonce'); ?>
                <input type="hidden" name="blt_action" value="login">
                <input type="hidden" name="blt_redirect" value="<?= esc_attr($current_url) ?>">

                <label>Email o Nome Utente
                    <input type="text" name="log"
                           value="<?= esc_attr($_GET['user_login'] ?? '') ?>"
                           required autocomplete="username" autofocus>
                </label>
                <label>Password
                    <input type="password" name="pwd" required autocomplete="current-password">
                </label>
                <label class="blt-checkbox-label" style="margin-bottom:12px;">
                    <input type="checkbox" name="rememberme" value="1"> Ricordami
                </label>

                <button type="submit" class="blt-btn-primary" style="width:100%;">Accedi</button>
            </form>

            <p style="text-align:center;margin-top:12px;font-size:13px;">
                <a href="<?= esc_url( add_query_arg('blt_forgot','1', $current_url) ) ?>">Password dimenticata?</a>
            </p>

            <?php BLT_OAuth::render_buttons( $current_url ); ?>

            <?php if ( $iscr_url ) : ?>
                <p class="blt-register-link">Non hai ancora un account? <a href="<?= esc_url($iscr_url) ?>">Registrati →</a></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php return; endif;

// ════════════════════════════════════════════════════════════════════════════
// LOGGATO — controlla se ha già la tessera
// ════════════════════════════════════════════════════════════════════════════
$user_id   = get_current_user_id();
$tesserato = BLT_Database::get_tesserato_by_user($user_id);

// ── Nessuna tessera: mostra form dati anagrafici ──────────────────────────────
if ( ! $tesserato ) :

    $settings    = BLT_Database::get_all_settings();
    $stripe_ok   = BLT_Payment::is_stripe_enabled();
    $paypal_ok   = BLT_Payment::is_paypal_enabled();
    $has_payment = $stripe_ok || $paypal_ok;
    $errore      = '';
    $step        = 1;
    $tk          = '';
    $wp_user     = wp_get_current_user();

    // Notifica di nuovo account
    if ( isset($_GET['blt_nuovo']) ) :?>
        <div class="blt-notice blt-notice-success" style="margin-bottom:20px;">
            👋 Benvenuto! Completa i tuoi dati per ottenere la tessera.
        </div>
    <?php endif;

    // Gestione submit dati
    if ( isset($_POST['blt_dati_tessera']) && wp_verify_nonce($_POST['blt_nonce'] ?? '', 'blt_dati_tessera') ) {

        $dati = array(
            'nome'           => sanitize_text_field( $_POST['nome']           ?? '' ),
            'cognome'        => sanitize_text_field( $_POST['cognome']        ?? '' ),
            'email'          => sanitize_email(      $_POST['email']          ?? $wp_user->user_email ),
            'telefono'       => sanitize_text_field( $_POST['telefono']       ?? '' ),
            'codice_fiscale' => strtoupper(sanitize_text_field( $_POST['codice_fiscale'] ?? '' )),
            'data_nascita'   => sanitize_text_field( $_POST['data_nascita']   ?? '' ),
            'luogo_nascita'  => sanitize_text_field( $_POST['luogo_nascita']  ?? '' ),
            'indirizzo'      => sanitize_text_field( $_POST['indirizzo']      ?? '' ),
            'cap'            => sanitize_text_field( $_POST['cap']            ?? '' ),
            'citta'          => sanitize_text_field( $_POST['citta']          ?? '' ),
            'provincia'      => strtoupper(sanitize_text_field( $_POST['provincia'] ?? '' )),
            'tipo_tessera'   => sanitize_key( $_POST['tipo_tessera'] ?? 'ordinario' ),
        );

        if ( empty($dati['nome']) || empty($dati['cognome']) ) {
            $errore = 'Nome e Cognome sono obbligatori.';
        } else {
            if ( ! $has_payment ) {
                $id = BLT_Public::crea_tesserato_da_form( $dati );
                if ( $id ) {
                    wp_safe_redirect( add_query_arg('blt_tessera_creata','1', $current_url) );
                    exit;
                }
                $errore = 'Errore nel salvataggio. Riprova o contatta la sede.';
            } else {
                $tk   = wp_generate_password(20, false);
                set_transient( 'blt_reg_' . $tk, $dati, 600 );
                $step = 2;
            }
        }
    }

    if ( isset($_GET['blt_tessera_creata']) ) {
        // Ricarica tesserato dopo redirect
        $tesserato = BLT_Database::get_tesserato_by_user($user_id);
    }

    if ( ! $tesserato ) : // ancora nessuna tessera, mostra form
    ?>

    <div class="blt-form-iscrizione">

        <?php if ( $errore ) : ?>
            <div class="blt-notice blt-notice-error">⚠️ <?= esc_html($errore) ?></div>
        <?php endif; ?>

        <?php if ( $step === 1 ) : ?>

            <?php if ( $has_payment ) : ?>
            <div class="blt-steps-indicator">
                <div class="blt-step blt-step-active"><span>1</span><em>Dati</em></div>
                <div class="blt-step-line"></div>
                <div class="blt-step"><span>2</span><em>Pagamento</em></div>
                <div class="blt-step-line"></div>
                <div class="blt-step"><span>3</span><em>Tessera attiva</em></div>
            </div>
            <?php endif; ?>

            <h3>Completa la tua iscrizione</h3>
            <p>Inserisci i tuoi dati anagrafici per generare la tessera.</p>

            <form method="post" class="blt-public-form" novalidate>
                <?php wp_nonce_field('blt_dati_tessera','blt_nonce'); ?>
                <input type="hidden" name="blt_dati_tessera" value="1">

                <div class="blt-form-row-2">
                    <label>Cognome *
                        <input type="text" name="cognome" value="<?= esc_attr($_POST['cognome']??$wp_user->last_name) ?>" required>
                    </label>
                    <label>Nome *
                        <input type="text" name="nome" value="<?= esc_attr($_POST['nome']??$wp_user->first_name) ?>" required>
                    </label>
                </div>
                <div class="blt-form-row-2">
                    <label>Email *
                        <input type="email" name="email" value="<?= esc_attr($_POST['email']??$wp_user->user_email) ?>" required>
                    </label>
                    <label>Telefono
                        <input type="tel" name="telefono" value="<?= esc_attr($_POST['telefono']??'') ?>">
                    </label>
                </div>
                <div class="blt-form-row-2">
                    <label>Codice Fiscale
                        <input type="text" name="codice_fiscale" value="<?= esc_attr($_POST['codice_fiscale']??'') ?>" maxlength="16" style="text-transform:uppercase">
                    </label>
                    <label>Data di Nascita
                        <input type="date" name="data_nascita" value="<?= esc_attr($_POST['data_nascita']??'') ?>">
                    </label>
                </div>
                <label>Luogo di Nascita
                    <input type="text" name="luogo_nascita" value="<?= esc_attr($_POST['luogo_nascita']??'') ?>">
                </label>
                <label>Indirizzo
                    <input type="text" name="indirizzo" value="<?= esc_attr($_POST['indirizzo']??'') ?>">
                </label>
                <div class="blt-form-row-3">
                    <label>CAP<input type="text" name="cap" value="<?= esc_attr($_POST['cap']??'') ?>" maxlength="10"></label>
                    <label>Città<input type="text" name="citta" value="<?= esc_attr($_POST['citta']??'') ?>"></label>
                    <label>Prov.<input type="text" name="provincia" value="<?= esc_attr($_POST['provincia']??'') ?>" maxlength="5"></label>
                </div>
                <label>Tipo Tessera
                    <select name="tipo_tessera">
                        <option value="ordinario"   <?= selected($_POST['tipo_tessera']??'','ordinario',false) ?>>Ordinario — € <?= esc_html($settings['quota_ordinario']??'20') ?></option>
                        <option value="familiare"   <?= selected($_POST['tipo_tessera']??'','familiare',false) ?>>Familiare — € <?= esc_html($settings['quota_familiare']??'30') ?></option>
                        <option value="sostenitore" <?= selected($_POST['tipo_tessera']??'','sostenitore',false) ?>>Sostenitore — € <?= esc_html($settings['quota_sostenitore']??'50') ?></option>
                        <option value="junior"      <?= selected($_POST['tipo_tessera']??'','junior',false) ?>>Junior (under 18) — € <?= esc_html($settings['quota_junior']??$settings['quota_ordinario']??'20') ?></option>
                    </select>
                </label>

                <button type="submit" class="blt-btn-primary" style="width:100%;">
                    <?= $has_payment ? 'Continua al Pagamento →' : 'Ottieni la Tessera' ?>
                </button>
            </form>

        <?php elseif ( $step === 2 && $tk ) :
            $dati_step2   = get_transient( 'blt_reg_' . $tk );
            $tipo_tessera = $dati_step2['tipo_tessera'] ?? 'ordinario';
            $tesserato_id = 0;
            $return_url   = $current_url;
        ?>
            <div class="blt-steps-indicator">
                <div class="blt-step blt-step-done"><span>✓</span><em>Dati</em></div>
                <div class="blt-step-line blt-step-line-done"></div>
                <div class="blt-step blt-step-active"><span>2</span><em>Pagamento</em></div>
                <div class="blt-step-line"></div>
                <div class="blt-step"><span>3</span><em>Tessera attiva</em></div>
            </div>
            <div class="blt-reg-summary">
                <strong><?= esc_html(($dati_step2['cognome']??'').' '.($dati_step2['nome']??'')) ?></strong>
                &nbsp;·&nbsp; Tessera <?= esc_html(ucfirst($tipo_tessera)) ?>
            </div>
            <div data-blt-reg-token="<?= esc_attr($tk) ?>">
                <?php include BLT_PLUGIN_DIR . 'templates/payment-block.php'; ?>
            </div>
        <?php endif; ?>

        <p style="text-align:center;margin-top:20px;font-size:13px;color:var(--blt-muted);">
            <a href="<?= esc_url(wp_logout_url($current_url)) ?>">Esci dall'account</a>
        </p>

    </div><!-- /.blt-form-iscrizione -->

    <?php endif; // fine !$tesserato dopo redirect
endif; // fine !$tesserato iniziale

// ════════════════════════════════════════════════════════════════════════════
// LOGGATO + TESSERA: area riservata completa
// ════════════════════════════════════════════════════════════════════════════
if ( $tesserato ) :

$quota_anno           = BLT_Database::get_quota($tesserato->id, date('Y'));
$scadenza             = $tesserato->data_scadenza ? date_i18n('d/m/Y', strtotime($tesserato->data_scadenza)) : '—';
$giorni_alla_scadenza = $tesserato->data_scadenza ? (int)ceil((strtotime($tesserato->data_scadenza) - time())/86400) : 0;
$tab = sanitize_key($_GET['blt_tab'] ?? ( isset($_GET['blt_tessera_creata']) ? 'quota' : 'tessera' ) );
?>

<div class="blt-area-tesserato">

    <?php if ( isset($_GET['blt_tessera_creata']) ) : ?>
        <div class="blt-notice blt-notice-success">🎉 Tessera creata! Paga subito la quota annuale oppure puoi farlo in seguito dal tab Quota.</div>
    <?php endif; ?>
    <?php if ($tesserato->stato === 'scaduto') : ?>
        <div class="blt-notice blt-notice-error">⚠️ La tua tessera è <strong>scaduta</strong>. Rinnova dal tab Quota.</div>
    <?php elseif ($giorni_alla_scadenza > 0 && $giorni_alla_scadenza <= 30) : ?>
        <div class="blt-notice blt-notice-warning">⏰ La tua tessera scade tra <strong><?= $giorni_alla_scadenza ?> giorni</strong> (<?= $scadenza ?>).</div>
    <?php endif; ?>
    <?php if ( isset($_GET['blt_profile_saved']) ) : ?>
        <div class="blt-notice blt-notice-success">✅ Profilo aggiornato.</div>
    <?php endif; ?>

    <nav class="blt-tab-nav">
        <a href="<?= esc_url(add_query_arg('blt_tab','tessera',$current_url)) ?>"
           class="blt-tab-link <?= $tab==='tessera'?'blt-tab-active':'' ?>">🪪 Tessera</a>
        <a href="<?= esc_url(add_query_arg('blt_tab','quota',$current_url)) ?>"
           class="blt-tab-link <?= $tab==='quota'?'blt-tab-active':'' ?>">💳 Quota <?= date('Y') ?></a>
        <a href="<?= esc_url(add_query_arg('blt_tab','profilo',$current_url)) ?>"
           class="blt-tab-link <?= $tab==='profilo'?'blt-tab-active':'' ?>">👤 I Miei Dati</a>
        <a href="<?= esc_url(wp_logout_url($current_url)) ?>" class="blt-tab-link blt-tab-link-logout">Esci</a>
    </nav>

    <?php if ($tab === 'tessera') : ?>
    <div class="blt-area-grid">
        <div class="blt-area-block">
            <h3>La Tua Tessera Digitale</h3>
            <?php BLT_QRCode::render_tessera($tesserato); ?>
            <p class="blt-tessera-note">
                Mostra il QR code per la verifica oppure
                <a href="<?= BLT_QRCode::get_verify_url($tesserato->qr_token) ?>" target="_blank">apri il link di verifica</a>.
            </p>
        </div>
        <div class="blt-area-block">
            <h3>Riepilogo</h3>
            <table class="blt-info-table">
                <tr><th>Nome Completo</th><td><?= esc_html($tesserato->nome . ' ' . $tesserato->cognome) ?></td></tr>
                <tr><th>N° Tessera</th><td><?= esc_html($tesserato->numero_tessera) ?></td></tr>
                <tr><th>Tipo</th><td><?= esc_html(ucfirst($tesserato->tipo_tessera)) ?></td></tr>
                <tr><th>Stato</th><td><span class="blt-badge blt-badge-<?= $tesserato->stato ?>"><?= ucfirst($tesserato->stato) ?></span></td></tr>
                <tr><th>Iscrizione</th><td><?= $tesserato->data_iscrizione ? date_i18n('d/m/Y', strtotime($tesserato->data_iscrizione)) : '—' ?></td></tr>
                <tr><th>Scadenza</th><td><?= $scadenza ?></td></tr>
            </table>
        </div>
    </div>

    <?php elseif ($tab === 'quota') : ?>
    <div class="blt-area-block" style="max-width:560px;">
        <h3>Quota <?= date('Y') ?></h3>
        <?php if ($quota_anno && $quota_anno->stato_pagamento === 'pagato') : ?>
            <div class="blt-quota-status blt-quota-pagato">
                ✅ <strong>Quota pagata</strong> — € <?= number_format($quota_anno->importo, 2, ',', '.') ?>
                <?php if ($quota_anno->data_pagamento) : ?>
                    <br><small>Pagata il <?= date_i18n('d/m/Y', strtotime($quota_anno->data_pagamento)) ?></small>
                <?php endif; ?>
                <?php if ($quota_anno->ricevuta_numero) : ?>
                    <br><small>Ricevuta n. <?= esc_html($quota_anno->ricevuta_numero) ?></small>
                <?php endif; ?>
            </div>
        <?php else :
            $tipo_tessera = $tesserato->tipo_tessera ?: 'ordinario';
            $tesserato_id = (int)$tesserato->id;
            $return_url   = add_query_arg('blt_tab','quota', $current_url);
            include BLT_PLUGIN_DIR . 'templates/payment-block.php';
        endif; ?>
    </div>

    <?php elseif ($tab === 'profilo') : ?>
    <div class="blt-area-block" style="max-width:600px;">
        <h3>I Miei Dati</h3>
        <table class="blt-info-table" style="margin-bottom:20px;">
            <tr><th>Nome Completo</th><td><?= esc_html($tesserato->nome . ' ' . $tesserato->cognome) ?></td></tr>
            <tr><th>Codice Fiscale</th><td><?= esc_html($tesserato->codice_fiscale ?: '—') ?></td></tr>
            <tr><th>Data di Nascita</th><td><?= $tesserato->data_nascita ? date_i18n('d/m/Y', strtotime($tesserato->data_nascita)) : '—' ?></td></tr>
            <tr><th>Luogo di Nascita</th><td><?= esc_html($tesserato->luogo_nascita ?: '—') ?></td></tr>
        </table>
        <p style="font-size:13px;color:var(--blt-muted);margin-bottom:16px;">
            Nome, codice fiscale, data e luogo di nascita sono modificabili solo dalla segreteria.
        </p>
        <form method="post" class="blt-public-form">
            <?php wp_nonce_field('blt_aggiorna_profilo','blt_nonce'); ?>
            <input type="hidden" name="blt_action" value="aggiorna_profilo">
            <label>Email
                <input type="email" name="email" value="<?= esc_attr($tesserato->email) ?>" required>
            </label>
            <label>Telefono
                <input type="tel" name="telefono" value="<?= esc_attr($tesserato->telefono) ?>">
            </label>
            <label>Indirizzo
                <input type="text" name="indirizzo" value="<?= esc_attr($tesserato->indirizzo) ?>">
            </label>
            <div class="blt-form-row-3">
                <label>CAP<input type="text" name="cap" value="<?= esc_attr($tesserato->cap) ?>" maxlength="10"></label>
                <label>Città<input type="text" name="citta" value="<?= esc_attr($tesserato->citta) ?>"></label>
                <label>Prov.<input type="text" name="provincia" value="<?= esc_attr($tesserato->provincia) ?>" maxlength="5"></label>
            </div>
            <button type="submit" class="blt-btn-primary">Salva Modifiche</button>
        </form>
        <hr style="margin:20px 0;border:none;border-top:1px solid var(--blt-border);">
        <p style="font-size:13px;">
            <a href="<?= esc_url(add_query_arg('blt_forgot','1',$current_url)) ?>">Cambia password</a>
        </p>
    </div>
    <?php endif; ?>

</div><!-- /.blt-area-tesserato -->

<?php endif; // fine $tesserato ?>
