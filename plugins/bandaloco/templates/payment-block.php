<?php
/**
 * Template: blocco pagamento (Stripe, PayPal, Bonifico).
 * Variabili attese:
 *   $tipo_tessera  – string  (ordinario|sostenitore|familiare|junior)
 *   $tesserato_id  – int     (0 = nuova iscrizione)
 *   $return_url    – string
 */
if ( ! defined('ABSPATH') ) exit;

$importo     = BLT_Payment::get_importo( $tipo_tessera );
$stripe_ok   = BLT_Payment::is_stripe_enabled();
$paypal_ok   = BLT_Payment::is_paypal_enabled();
$bonifico_ok = BLT_Payment::is_bonifico_enabled();
$importo_fmt = '€ ' . number_format($importo, 2, ',', '.');
$proloco     = BLT_Database::get_setting('nome_proloco','Bandaloco');
$nonce       = wp_create_nonce('blt_payment_nonce');
$iban        = BLT_Database::get_setting('bonifico_iban','');
$intestato   = BLT_Database::get_setting('bonifico_intestato', $proloco);

if ( ! $stripe_ok && ! $paypal_ok && ! $bonifico_ok ) return;

// Stato di ritorno
$param_result = sanitize_key( $_GET[ BLT_Payment::PARAM_RESULT ] ?? '' );
?>

<div class="blt-payment-block" id="blt-payment-<?= esc_attr($tipo_tessera) ?>-<?= (int)$tesserato_id ?>">

    <?php if ( $param_result === 'success' ) : ?>
        <div class="blt-notice blt-notice-success">
            🎉 <strong>Pagamento ricevuto!</strong> La tua tessera è ora attiva.
        </div>
        <?php return; ?>

    <?php elseif ( $param_result === 'bonifico_pending' ) : ?>
        <div class="blt-notice blt-notice-info" style="background:#eff6ff;border-color:#3b82f6;color:#1e3a5f;">
            🏦 <strong>Richiesta di bonifico registrata!</strong><br>
            Riceverai una email con le istruzioni per completare il pagamento.
            La tua tessera sarà attivata non appena il bonifico sarà verificato dalla segreteria.
        </div>
        <?php return; ?>

    <?php elseif ( $param_result === 'cancel' ) : ?>
        <div class="blt-notice blt-notice-warning">ℹ️ Pagamento annullato. Puoi riprovare quando vuoi.</div>

    <?php elseif ( $param_result === 'error' ) : ?>
        <div class="blt-notice blt-notice-error">❌ Si è verificato un errore. Riprova o contatta la sede.</div>
    <?php endif; ?>

    <div class="blt-payment-summary">
        <div class="blt-payment-summary-row">
            <span>Quota <?= esc_html(ucfirst($tipo_tessera)) ?> <?= date('Y') ?></span>
            <strong><?= esc_html($importo_fmt) ?></strong>
        </div>
        <div class="blt-payment-summary-row blt-payment-summary-total">
            <span>Totale</span>
            <strong><?= esc_html($importo_fmt) ?></strong>
        </div>
    </div>

    <!-- Istruzioni bonifico (hidden, mostrate via JS) -->
    <div class="blt-bonifico-info" id="blt-bonifico-info-<?= (int)$tesserato_id ?>" style="display:none;
         background:#eff6ff;border:1px solid #93c5fd;border-radius:6px;padding:16px;margin-bottom:16px;">
        <h4 style="margin:0 0 10px;color:#1e3a5f;">🏦 Istruzioni per il bonifico</h4>
        <table style="width:100%;font-size:14px;">
            <tr><td style="padding:4px 0;color:#6b7280;">Intestato a</td><td><strong><?= esc_html($intestato) ?></strong></td></tr>
            <tr><td style="padding:4px 0;color:#6b7280;">IBAN</td><td><strong class="blt-iban"><?= esc_html($iban) ?></strong></td></tr>
            <tr><td style="padding:4px 0;color:#6b7280;">Importo</td><td><strong><?= esc_html($importo_fmt) ?></strong></td></tr>
            <tr><td style="padding:4px 0;color:#6b7280;">Causale</td><td class="blt-bonifico-causale"><em>caricamento…</em></td></tr>
        </table>
        <p style="font-size:12px;color:#6b7280;margin:10px 0 0;">
            La segreteria verificherà il bonifico e attiverà la tua tessera entro pochi giorni lavorativi.
        </p>
    </div>

    <div class="blt-gateway-selector">

        <?php if ($stripe_ok): ?>
        <button type="button" class="blt-pay-btn blt-pay-stripe"
                data-gateway="stripe" data-tipo="<?= esc_attr($tipo_tessera) ?>"
                data-tid="<?= (int)$tesserato_id ?>" data-return="<?= esc_url($return_url) ?>"
                data-nonce="<?= esc_attr($nonce) ?>">
            <svg viewBox="0 0 60 25" height="20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M59.6 13.4c0-3.9-1.9-7-5.5-7-3.6 0-5.8 3.1-5.8 6.9 0 4.6 2.6 6.9 6.3 6.9 1.8 0 3.2-.4 4.2-1v-3c-1 .5-2.2.8-3.7.8-1.5 0-2.8-.5-2.9-2.3h7.3c0-.2.1-.9.1-1.3zm-7.4-1.4c0-1.7 1-2.4 2-2.4 1 0 1.9.7 1.9 2.4h-3.9zM40.1 6.4c-1.5 0-2.5.7-3 1.2l-.2-1H33v18.6l3.8-.8V18c.5.4 1.3.8 2.5.8 2.5 0 4.8-2 4.8-6.4-.1-4-2.4-6-4-6zm-.7 9.8c-.8 0-1.3-.3-1.7-.7V10c.4-.4.9-.7 1.7-.7 1.3 0 2.2 1.4 2.2 3.4 0 2.1-.9 3.5-2.2 3.5zM28.2 5.4l3.8-.8V1.2l-3.8.8v3.4zM28.2 6.6h3.8v13.2h-3.8V6.6zM22.7 7.7l-.2-1.1h-3.3v13.2h3.8v-8.9c.9-1.2 2.4-1 2.8-.8V6.6c-.5-.2-2.2-.5-3.1 1.1zM15.1 3.9l-3.7.8-.1 12.3c0 2.3 1.7 3.9 4 3.9 1.3 0 2.2-.2 2.7-.5v-3.1c-.5.2-2.8.9-2.8-1.4V9.8h2.8V6.6h-2.8l-.1-2.7zM4 10.3c0-.6.5-.8 1.3-.8 1.2 0 2.6.4 3.8 1V7c-1.3-.5-2.5-.7-3.8-.7C2.3 6.3 0 7.9 0 11c0 4.8 6.6 4 6.6 6.1 0 .7-.6 1-1.5 1-1.3 0-2.9-.5-4.2-1.3v3.5c1.4.6 2.9 1 4.2 1 3.2 0 5.4-1.6 5.4-4.8C10.5 11.4 4 12.3 4 10.3z" fill="white"/>
            </svg>
            Paga con carta di credito
        </button>
        <?php endif; ?>

        <?php if ($paypal_ok): ?>
        <button type="button" class="blt-pay-btn blt-pay-paypal"
                data-gateway="paypal" data-tipo="<?= esc_attr($tipo_tessera) ?>"
                data-tid="<?= (int)$tesserato_id ?>" data-return="<?= esc_url($return_url) ?>"
                data-nonce="<?= esc_attr($nonce) ?>">
            <svg viewBox="0 0 101 32" height="18" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12.237 2.824H5.437A1 1 0 004.45 3.68L1.77 20.37a.6.6 0 00.593.694h3.236a1 1 0 00.988-.843l.727-4.609a1 1 0 01.988-.843h2.137c4.451 0 7.02-2.153 7.693-6.42.302-1.865.012-3.33-.863-4.358-.962-1.132-2.668-1.732-4.933-1.732z" fill="white"/>
                <path d="M37.737 2.824h-6.8a1 1 0 00-.988.856L27.27 20.37a.6.6 0 00.593.694h3.448a.7.7 0 00.692-.59l.764-4.862a1 1 0 01.988-.843h2.137c4.451 0 7.02-2.153 7.693-6.42.302-1.865.012-3.33-.863-4.358-.962-1.132-2.668-1.732-4.985-1.732v-.435z" fill="white" opacity=".7"/>
            </svg>
            Paga con PayPal
        </button>
        <?php endif; ?>

        <?php if ($bonifico_ok): ?>
        <button type="button" class="blt-pay-btn blt-pay-bonifico"
                data-gateway="bonifico" data-tipo="<?= esc_attr($tipo_tessera) ?>"
                data-tid="<?= (int)$tesserato_id ?>" data-return="<?= esc_url($return_url) ?>"
                data-nonce="<?= esc_attr($nonce) ?>">
            🏦 Paga con Bonifico Bancario
        </button>
        <?php endif; ?>

    </div>

    <div class="blt-payment-spinner" id="blt-spinner-<?= (int)$tesserato_id ?>" style="display:none;">
        <div class="blt-spinner"></div>
        <span>Elaborazione in corso…</span>
    </div>

    <p class="blt-payment-secure">
        🔒 I tuoi dati di pagamento non vengono mai memorizzati su questo sito.
    </p>
</div>
