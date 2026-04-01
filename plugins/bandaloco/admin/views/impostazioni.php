<?php if ( ! defined('ABSPATH') ) exit; ?>
<div class="wrap blt-wrap">
    <h1 class="blt-page-title">
        <span class="dashicons dashicons-admin-settings"></span>
         Bandaloco &mdash; Impostazioni
    </h1>

    <?php if(isset($_GET['saved'])): ?>
        <div class="notice notice-success is-dismissible"><p>✅ Impostazioni salvate.</p></div>
    <?php endif; ?>

    <form method="post" action="<?= admin_url('admin-post.php') ?>">
        <?php wp_nonce_field('blt_save_settings'); ?>
        <input type="hidden" name="action" value="blt_save_settings">

        <div class="blt-settings-grid">

            <div class="blt-form-section">
                <h2>🏛️ Dati dell'Associazione</h2>
                <table class="form-table">
                    <tr>
                        <th><label for="nome_proloco">Nome Pro Loco</label></th>
                        <td><input id="nome_proloco" type="text" name="nome_proloco" value="<?= esc_attr($settings['nome_proloco']??'') ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="email_notifiche">Email Notifiche</label></th>
                        <td>
                            <input id="email_notifiche" type="email" name="email_notifiche" value="<?= esc_attr($settings['email_notifiche']??'') ?>" class="regular-text">
                            <p class="description">Email che riceve le notifiche di sistema</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="logo_tessera">Logo (URL)</label></th>
                        <td>
                            <input id="logo_tessera" type="url" name="logo_tessera" value="<?= esc_attr($settings['logo_tessera']??'') ?>" class="large-text">
                            <p class="description">URL del logo mostrato sulla tessera digitale</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="testo_tessera">Testo Tessera</label></th>
                        <td><input id="testo_tessera" type="text" name="testo_tessera" value="<?= esc_attr($settings['testo_tessera']??'Pro Loco') ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="pagina_iscrizione">URL Pagina Iscrizione</label></th>
                        <td>
                            <input id="pagina_iscrizione" type="url" name="pagina_iscrizione" value="<?= esc_attr($settings['pagina_iscrizione']??'') ?>" class="large-text" placeholder="https://...">
                            <p class="description">URL della pagina con lo shortcode <code>[proloco_iscrizione]</code>.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="pagina_area">URL Area Riservata</label></th>
                        <td>
                            <input id="pagina_area" type="url" name="pagina_area" value="<?= esc_attr($settings['pagina_area']??'') ?>" class="large-text" placeholder="https://...">
                            <p class="description">URL della pagina con lo shortcode <code>[proloco_area_tesserato]</code>. Usato per i redirect dopo login e iscrizione.</p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="blt-form-section">
                <h2>💳 Quote Associative</h2>
                <table class="form-table">
                    <tr>
                        <th><label for="quota_ordinario">Quota Ordinario (€)</label></th>
                        <td><input id="quota_ordinario" type="number" step="0.01" name="quota_ordinario" value="<?= esc_attr($settings['quota_ordinario']??'20') ?>" class="small-text"> €</td>
                    </tr>
                    <tr>
                        <th><label for="quota_sostenitore">Quota Sostenitore (€)</label></th>
                        <td><input id="quota_sostenitore" type="number" step="0.01" name="quota_sostenitore" value="<?= esc_attr($settings['quota_sostenitore']??'50') ?>" class="small-text"> €</td>
                    </tr>
                    <tr>
                        <th><label for="quota_familiare">Quota Familiare (€)</label></th>
                        <td><input id="quota_familiare" type="number" step="0.01" name="quota_familiare" value="<?= esc_attr($settings['quota_familiare']??'30') ?>" class="small-text"> €</td>
                    </tr>
                </table>
            </div>

            <div class="blt-form-section">
                <h2>🔧 Configurazione Tessere</h2>
                <table class="form-table">
                    <tr>
                        <th><label for="prefisso_tessera">Prefisso Numero Tessera</label></th>
                        <td>
                            <input id="prefisso_tessera" type="text" name="prefisso_tessera" value="<?= esc_attr($settings['prefisso_tessera']??'BL') ?>" class="small-text" maxlength="5">
                            <p class="description">Es: BL → BL20240001</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="giorni_avviso">Giorni di preavviso scadenza</label></th>
                        <td>
                            <input id="giorni_avviso" type="number" min="1" max="365" name="giorni_avviso" value="<?= esc_attr($settings['giorni_avviso']??'30') ?>" class="small-text"> giorni
                            <p class="description">Quanti giorni prima della scadenza inviare l'avviso email</p>
                        </td>
                    </tr>
                </table>
            </div>

        </div><!-- /.blt-settings-grid -->

        <!-- ── Social Login ─────────────────────────────────────────────────── -->
        <div class="blt-form-section blt-social-settings" style="margin-bottom:24px;">
            <h2>🔑 Login Sociale (Google &amp; Facebook)</h2>
            <p style="color:#718096;font-size:13px;margin-bottom:16px;">
                Permette ai tesserati di accedere all'area riservata con il loro account Google o Facebook, senza dover ricordare una password separata.
            </p>

            <!-- Google -->
            <div class="blt-oauth-provider-box">
                <div class="blt-oauth-provider-header">
                    <svg viewBox="0 0 24 24" width="24" height="24" style="flex-shrink:0"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    <strong>Google Login</strong>
                    <label class="blt-toggle" style="margin-left:auto;">
                        <input type="checkbox" name="google_login_enabled" value="1" <?= checked($settings['google_login_enabled']??'0','1',false) ?>>
                        <span class="blt-toggle-slider"></span>
                        Abilitato
                    </label>
                </div>
                <table class="form-table" style="margin-top:0;">
                    <tr>
                        <th style="width:200px;"><label for="google_client_id">Client ID</label></th>
                        <td><input id="google_client_id" type="text" name="google_client_id" value="<?= esc_attr($settings['google_client_id']??'') ?>" class="large-text" placeholder="123456789-xxxxxxxx.apps.googleusercontent.com"></td>
                    </tr>
                    <tr>
                        <th><label for="google_client_secret">Client Secret</label></th>
                        <td>
                            <input id="google_client_secret" type="password" name="google_client_secret" value="<?= esc_attr($settings['google_client_secret']??'') ?>" class="regular-text" autocomplete="new-password">
                            <p class="description">
                                Ottieni le credenziali su <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a>.<br>
                                URI di reindirizzamento autorizzato: <code><?= esc_html(BLT_OAuth::callback_url('google')) ?></code>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Facebook -->
            <div class="blt-oauth-provider-box" style="margin-top:16px;">
                <div class="blt-oauth-provider-header">
                    <svg viewBox="0 0 24 24" width="24" height="24" style="flex-shrink:0"><path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    <strong>Facebook Login</strong>
                    <label class="blt-toggle" style="margin-left:auto;">
                        <input type="checkbox" name="facebook_login_enabled" value="1" <?= checked($settings['facebook_login_enabled']??'0','1',false) ?>>
                        <span class="blt-toggle-slider"></span>
                        Abilitato
                    </label>
                </div>
                <table class="form-table" style="margin-top:0;">
                    <tr>
                        <th style="width:200px;"><label for="facebook_app_id">App ID</label></th>
                        <td><input id="facebook_app_id" type="text" name="facebook_app_id" value="<?= esc_attr($settings['facebook_app_id']??'') ?>" class="regular-text" placeholder="1234567890123456"></td>
                    </tr>
                    <tr>
                        <th><label for="facebook_app_secret">App Secret</label></th>
                        <td>
                            <input id="facebook_app_secret" type="password" name="facebook_app_secret" value="<?= esc_attr($settings['facebook_app_secret']??'') ?>" class="regular-text" autocomplete="new-password">
                            <p class="description">
                                Ottieni le credenziali su <a href="https://developers.facebook.com/apps/" target="_blank">Meta for Developers</a>.<br>
                                URI di reindirizzamento OAuth: <code><?= esc_html(BLT_OAuth::callback_url('facebook')) ?></code><br>
                                Permessi necessari: <code>email</code>, <code>public_profile</code>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
        </div><!-- /.blt-social-settings -->

        <!-- ── Gateway Pagamento ────────────────────────────────────────────── -->
        <div class="blt-form-section blt-payment-settings" style="margin-bottom:24px;">
            <h2>💳 Gateway di Pagamento Online</h2>
            <p style="color:#718096;font-size:13px;margin-bottom:16px;">
                Permette ai tesserati di pagare la quota e rinnovare la tessera direttamente online con carta di credito o PayPal. La tessera si attiva automaticamente dopo il pagamento.
            </p>

            <!-- Stripe -->
            <div class="blt-oauth-provider-box">
                <div class="blt-oauth-provider-header">
                    <svg viewBox="0 0 60 25" height="20" fill="none"><path d="M59.6 13.4c0-3.9-1.9-7-5.5-7-3.6 0-5.8 3.1-5.8 6.9 0 4.6 2.6 6.9 6.3 6.9 1.8 0 3.2-.4 4.2-1v-3c-1 .5-2.2.8-3.7.8-1.5 0-2.8-.5-2.9-2.3h7.3c0-.2.1-.9.1-1.3zm-7.4-1.4c0-1.7 1-2.4 2-2.4 1 0 1.9.7 1.9 2.4h-3.9zM40.1 6.4c-1.5 0-2.5.7-3 1.2l-.2-1H33v18.6l3.8-.8V18c.5.4 1.3.8 2.5.8 2.5 0 4.8-2 4.8-6.4-.1-4-2.4-6-4-6zm-.7 9.8c-.8 0-1.3-.3-1.7-.7V10c.4-.4.9-.7 1.7-.7 1.3 0 2.2 1.4 2.2 3.4 0 2.1-.9 3.5-2.2 3.5zM28.2 5.4l3.8-.8V1.2l-3.8.8v3.4zM28.2 6.6h3.8v13.2h-3.8V6.6zM22.7 7.7l-.2-1.1h-3.3v13.2h3.8v-8.9c.9-1.2 2.4-1 2.8-.8V6.6c-.5-.2-2.2-.5-3.1 1.1zM15.1 3.9l-3.7.8-.1 12.3c0 2.3 1.7 3.9 4 3.9 1.3 0 2.2-.2 2.7-.5v-3.1c-.5.2-2.8.9-2.8-1.4V9.8h2.8V6.6h-2.8l-.1-2.7zM4 10.3c0-.6.5-.8 1.3-.8 1.2 0 2.6.4 3.8 1V7c-1.3-.5-2.5-.7-3.8-.7C2.3 6.3 0 7.9 0 11c0 4.8 6.6 4 6.6 6.1 0 .7-.6 1-1.5 1-1.3 0-2.9-.5-4.2-1.3v3.5c1.4.6 2.9 1 4.2 1 3.2 0 5.4-1.6 5.4-4.8C10.5 11.4 4 12.3 4 10.3z" fill="#635bff"/></svg>
                    <strong>Stripe (Carta di Credito / Debito)</strong>
                    <label class="blt-toggle" style="margin-left:auto;">
                        <input type="checkbox" name="stripe_enabled" value="1" <?= checked($settings['stripe_enabled']??'0','1',false) ?>>
                        <span class="blt-toggle-slider"></span>
                        Abilitato
                    </label>
                </div>
                <table class="form-table" style="margin-top:0;">
                    <tr>
                        <th style="width:220px;"><label for="stripe_publishable_key">Publishable Key</label></th>
                        <td><input id="stripe_publishable_key" type="text" name="stripe_publishable_key" value="<?= esc_attr($settings['stripe_publishable_key']??'') ?>" class="large-text" placeholder="pk_live_…"></td>
                    </tr>
                    <tr>
                        <th><label for="stripe_secret_key">Secret Key</label></th>
                        <td>
                            <input id="stripe_secret_key" type="password" name="stripe_secret_key" value="<?= esc_attr($settings['stripe_secret_key']??'') ?>" class="regular-text" autocomplete="new-password" placeholder="sk_live_…">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="stripe_webhook_secret">Webhook Secret</label></th>
                        <td>
                            <input id="stripe_webhook_secret" type="password" name="stripe_webhook_secret" value="<?= esc_attr($settings['stripe_webhook_secret']??'') ?>" class="regular-text" placeholder="whsec_…">
                            <p class="description">
                                Ottieni le chiavi su <a href="https://dashboard.stripe.com/apikeys" target="_blank">dashboard.stripe.com</a> → API Keys.<br>
                                URL Webhook da registrare: <code><?= esc_html(BLT_Payment::webhook_url('stripe')) ?></code><br>
                                Evento da abilitare: <code>checkout.session.completed</code>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th>Modalità</th>
                        <td>
                            <label style="flex-direction:row;gap:8px;font-weight:normal;">
                                <input type="checkbox" name="stripe_sandbox" value="1" <?= checked($settings['stripe_sandbox']??'0','1',false) ?>>
                                Usa modalità Test (sandbox)
                            </label>
                            <p class="description">In test usa chiavi <code>pk_test_…</code> / <code>sk_test_…</code></p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- PayPal -->
            <div class="blt-oauth-provider-box" style="margin-top:16px;">
                <div class="blt-oauth-provider-header">
                    <svg viewBox="0 0 24 24" width="24" height="24"><path fill="#003087" d="M7.076 21.337H2.47a.641.641 0 01-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106zm14.146-14.42a3.35 3.35 0 00-.607-.541c-.013.076-.026.175-.041.254-.93 4.778-4.005 7.201-9.138 7.201h-2.19a.563.563 0 00-.556.479l-1.187 7.527h-.506l-.24 1.516a.56.56 0 00.554.647h3.882c.46 0 .85-.334.922-.788.06-.26.76-4.852.816-5.09a.932.932 0 01.923-.788h.58c3.76 0 6.705-1.528 7.565-5.946.36-1.847.174-3.388-.777-4.471z"/></svg>
                    <strong>PayPal</strong>
                    <label class="blt-toggle" style="margin-left:auto;">
                        <input type="checkbox" name="paypal_enabled" value="1" <?= checked($settings['paypal_enabled']??'0','1',false) ?>>
                        <span class="blt-toggle-slider"></span>
                        Abilitato
                    </label>
                </div>
                <table class="form-table" style="margin-top:0;">
                    <tr>
                        <th style="width:220px;"><label for="paypal_client_id">Client ID</label></th>
                        <td><input id="paypal_client_id" type="text" name="paypal_client_id" value="<?= esc_attr($settings['paypal_client_id']??'') ?>" class="large-text" placeholder="AaBb…"></td>
                    </tr>
                    <tr>
                        <th><label for="paypal_secret">Secret</label></th>
                        <td>
                            <input id="paypal_secret" type="password" name="paypal_secret" value="<?= esc_attr($settings['paypal_secret']??'') ?>" class="regular-text" autocomplete="new-password">
                            <p class="description">
                                Crea l'app su <a href="https://developer.paypal.com/dashboard/applications/live" target="_blank">developer.paypal.com</a>.<br>
                                URL Webhook da registrare: <code><?= esc_html(BLT_Payment::webhook_url('paypal')) ?></code><br>
                                Evento: <code>CHECKOUT.ORDER.APPROVED</code> e <code>PAYMENT.CAPTURE.COMPLETED</code>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th>Modalità</th>
                        <td>
                            <label style="flex-direction:row;gap:8px;font-weight:normal;">
                                <input type="checkbox" name="paypal_sandbox" value="1" <?= checked($settings['paypal_sandbox']??'0','1',false) ?>>
                                Usa Sandbox (test)
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
        </div><!-- /.blt-payment-settings -->

        <!-- ── Bonifico ───────────────────────────────────────────────────────── -->
        <div class="blt-form-section" style="margin-bottom:24px;">
            <h2>🏦 Bonifico Bancario</h2>
            <table class="form-table">
                <tr>
                    <th></th>
                    <td><label>
                        <input type="checkbox" name="bonifico_enabled" value="1" <?= checked($settings['bonifico_enabled']??'','1') ?>>
                        Abilita pagamento tramite bonifico
                    </label></td>
                </tr>
                <tr>
                    <th><label for="bonifico_iban">IBAN</label></th>
                    <td><input id="bonifico_iban" type="text" name="bonifico_iban"
                               value="<?= esc_attr($settings['bonifico_iban']??'') ?>"
                               class="regular-text" placeholder="IT60 X054 2811 1010 0000 0123 456"></td>
                </tr>
                <tr>
                    <th><label for="bonifico_intestato">Intestato a</label></th>
                    <td>
                        <input id="bonifico_intestato" type="text" name="bonifico_intestato"
                               value="<?= esc_attr($settings['bonifico_intestato']??'') ?>" class="regular-text">
                        <p class="description">Lascia vuoto per usare il nome Pro Loco</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="bonifico_causale">Prefisso causale</label></th>
                    <td>
                        <input id="bonifico_causale" type="text" name="bonifico_causale"
                               value="<?= esc_attr($settings['bonifico_causale']??'Quota associativa') ?>" class="regular-text">
                        <p class="description">Il sistema aggiunge automaticamente anno, nome e numero tessera</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="bonifico_note">Note aggiuntive</label></th>
                    <td>
                        <textarea id="bonifico_note" name="bonifico_note" rows="2" class="large-text"><?= esc_textarea($settings['bonifico_note']??'') ?></textarea>
                        <p class="description">Testo extra mostrato all'utente (es. BIC, filiale)</p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- ── Direttivo ─────────────────────────────────────────────────────── -->
        <div class="blt-form-section" style="margin-bottom:24px;">
            <h2>🏛️ Pagina Pubblica Direttivo</h2>
            <p style="color:#718096;font-size:13px;margin-bottom:16px;">
                Configura il testo mostrato nella pagina pubblica del direttivo, generata con lo shortcode <code>[proloco_direttivo]</code>.
            </p>
            <table class="form-table">
                <tr>
                    <th><label for="direttivo_titolo">Titolo sezione</label></th>
                    <td><input id="direttivo_titolo" type="text" name="direttivo_titolo" value="<?= esc_attr($settings['direttivo_titolo']??'Il Nostro Direttivo') ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="direttivo_descrizione">Testo introduttivo</label></th>
                    <td>
                        <textarea id="direttivo_descrizione" name="direttivo_descrizione" rows="3" class="large-text"><?= esc_textarea($settings['direttivo_descrizione']??'') ?></textarea>
                        <p class="description">Breve testo di presentazione mostrato sopra le schede dei membri.</p>
                    </td>
                </tr>
            </table>
            <p class="description" style="margin-top:12px;">
                💡 Shortcode da inserire nella pagina: <code>[proloco_direttivo]</code><br>
                Per aggiungere un membro al direttivo vai su <strong>Tesserati → modifica tesserato → sezione Direttivo</strong>.
            </p>
        </div>

        <p><button type="submit" class="button button-primary button-large">💾 Salva Impostazioni</button></p>
    </form>
</div>
