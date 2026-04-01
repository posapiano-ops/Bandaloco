/*Bandaloco – Public JS (v1.2.0) */
jQuery(function ($) {

    // Auto-uppercase CF
    $(document).on('input', 'input[name="codice_fiscale"]', function () {
        this.value = this.value.toUpperCase();
    });

    // Smooth scroll ai messaggi
    var $msg = $('.blt-notice-success, .blt-notice-error').first();
    if ($msg.length) {
        $('html,body').animate({ scrollTop: $msg.offset().top - 80 }, 400);
    }

    // ── Pagamento ──────────────────────────────────────────────────────────
    $(document).on('click', '.blt-pay-btn', function (e) {
        e.preventDefault();
        var $btn     = $(this);
        var gateway  = $btn.data('gateway');
        var tipo     = $btn.data('tipo');
        var tid      = $btn.data('tid');
        var returnUrl= $btn.data('return');
        var nonce    = $btn.data('nonce');

        // Trova eventuale reg_token per nuova iscrizione
        var regToken = $btn.closest('[data-blt-reg-token]').data('blt-reg-token') || '';

        // Mostra spinner, disabilita pulsanti
        var $block   = $btn.closest('.blt-payment-block');
        var $spinner = $block.find('.blt-payment-spinner');
        $block.find('.blt-pay-btn').prop('disabled', true).css('opacity', '.6');
        $spinner.show();

        $.ajax({
            url:    bltAjax.url,
            method: 'POST',
            data: {
                action:       'blts_create_payment',
                nonce:        nonce,
                gateway:      gateway,
                tipo_tessera: tipo,
                tesserato_id: tid,
                return_url:   returnUrl,
                reg_token:    regToken,
            },
            success: function (res) {
                if (res.success && res.data.gateway === 'bonifico') {
                    // Mostra istruzioni bonifico inline
                    var d = res.data;
                    var $info = $block.find('.blt-bonifico-info');
                    $info.find('.blt-bonifico-causale').text(d.causale || '');
                    $info.find('.blt-iban').text(d.iban || '');
                    $spinner.hide();
                    $block.find('.blt-pay-btn').hide();
                    $block.find('.blt-payment-secure').hide();
                    $info.slideDown(250);
                    // Redirect dopo 2s per aggiornare lo stato
                    setTimeout(function(){ window.location.href = d.redirect; }, 3500);
                } else if (res.success && res.data.redirect) {
                    window.location.href = res.data.redirect;
                } else {
                    var msg = (res.data && typeof res.data === 'string') ? res.data : 'Errore sconosciuto.';
                    $spinner.hide();
                    $block.find('.blt-pay-btn').prop('disabled', false).css('opacity', '1');
                    alert('❌ ' + msg);
                }
            },
            error: function () {
                $spinner.hide();
                $block.find('.blt-pay-btn').prop('disabled', false).css('opacity', '1');
                alert('❌ Errore di connessione. Riprova.');
            }
        });
    });
});
