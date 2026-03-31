/*Bandaloco Tessere – Admin JS */
jQuery(function($){
    // Auto-uppercase CF e provincia
    $('input[name="codice_fiscale"], input[name="provincia"]').on('input', function(){
        this.value = this.value.toUpperCase();
    });

    // Conferma eliminazione
    $('form.blt-delete-form').on('submit', function(){
        return confirm('Sei sicuro di voler eliminare questo tesserato? L\'operazione è irreversibile.');
    });

    // Imposta quota automaticamente dal tipo tessera
    var quote = pltAjax && pltAjax.quote ? pltAjax.quote : {};
    $('select[name="tipo_tessera"]').on('change', function(){
        var tipo = $(this).val();
        if (quote[tipo]) $('input[name="importo"]').val(quote[tipo]);
    });
});
