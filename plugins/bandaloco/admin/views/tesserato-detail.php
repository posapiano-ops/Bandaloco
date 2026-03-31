<?php if ( ! defined('ABSPATH') ) exit;
if ( ! $tesserato ) { echo '<div class="wrap"><p>Tesserato non trovato.</p></div>'; return; }
$scadenza = $tesserato->data_scadenza ? date_i18n('d/m/Y', strtotime($tesserato->data_scadenza)) : '—';
$quota_anno = BLT_Database::get_quota($tesserato->id, date('Y'));
?>
<div class="wrap blt-wrap">
    <h1 class="blt-page-title">
        <span class="dashicons dashicons-id-alt"></span>
        <?= esc_html($tesserato->cognome . ' ' . $tesserato->nome) ?>
        <span class="blt-badge blt-badge-<?= $tesserato->stato ?>"><?= ucfirst($tesserato->stato) ?></span>
    </h1>
    <a href="<?= admin_url('admin.php?page=blt-tesserati') ?>" class="blt-back-link">← Torna all'elenco</a>
    <a href="<?= admin_url('admin.php?page=blt-tesserati&action=edit&id='.$tesserato->id) ?>" class="button" style="margin-left:8px;">✏️ Modifica</a>

    <?php if(isset($_GET['saved']))       : ?><div class="notice notice-success is-dismissible"><p>✅ Salvato.</p></div><?php endif; ?>
    <?php if(isset($_GET['quota_saved'])) : ?>
        <div class="notice notice-success is-dismissible"><p>💳 Quota aggiornata.</p></div>
    <?php endif; ?>
    <?php if(isset($_GET['BLT_warn']) && $_GET['BLT_warn']==='importo_basso') : ?>
        <div class="notice notice-warning is-dismissible">
            <p>⚠️ <strong>Importo insufficiente:</strong> la quota è stata salvata ma la tessera <strong>non è stata attivata</strong> perché l'importo pagato è inferiore alla quota dovuta per questo tipo di tessera. Modifica l'importo o cambia manualmente lo stato della tessera.</p>
        </div>
    <?php endif; ?>

    <div class="blt-detail-grid">

        <!-- Tessera digitale -->
        <div class="blt-detail-card">
            <h2>🪪 Tessera Digitale</h2>
            <?php BLT_QRCode::render_tessera($tesserato); ?>
            <div style="margin-top:12px;text-align:center;">
                <a href="<?= BLT_QRCode::get_verify_url($tesserato->qr_token) ?>" target="_blank" class="button button-small">🔗 Link di verifica</a>
                <button onclick="window.print()" class="button button-small">🖨️ Stampa</button>
            </div>
        </div>

        <!-- Dati anagrafici -->
        <div class="blt-detail-card">
            <h2>📋 Dati Personali</h2>
            <table class="blt-info-table">
                <tr><th>Tessera N°</th><td><?= esc_html($tesserato->numero_tessera) ?></td></tr>
                <tr><th>Codice Fiscale</th><td><?= esc_html($tesserato->codice_fiscale ?: '—') ?></td></tr>
                <tr><th>Data di Nascita</th><td><?= $tesserato->data_nascita ? date_i18n('d/m/Y', strtotime($tesserato->data_nascita)) : '—' ?></td></tr>
                <tr><th>Luogo di Nascita</th><td><?= esc_html($tesserato->luogo_nascita ?: '—') ?></td></tr>
                <tr><th>Indirizzo</th><td><?= esc_html(trim($tesserato->indirizzo . ' ' . $tesserato->cap . ' ' . $tesserato->citta . ' ' . $tesserato->provincia) ?: '—') ?></td></tr>
                <tr><th>Email</th><td><?= $tesserato->email ? '<a href="mailto:'.esc_attr($tesserato->email).'">'.esc_html($tesserato->email).'</a>' : '—' ?></td></tr>
                <tr><th>Telefono</th><td><?= esc_html($tesserato->telefono ?: '—') ?></td></tr>
                <tr><th>Tipo Tessera</th><td><?= esc_html(ucfirst($tesserato->tipo_tessera)) ?></td></tr>
                <tr><th>Iscrizione</th><td><?= $tesserato->data_iscrizione ? date_i18n('d/m/Y', strtotime($tesserato->data_iscrizione)) : '—' ?></td></tr>
                <tr><th>Scadenza</th><td><?= $scadenza ?></td></tr>
            </table>
            <?php if($tesserato->note): ?>
            <div class="blt-note-box"><strong>Note:</strong> <?= esc_html($tesserato->note) ?></div>
            <?php endif; ?>
        </div>

        <!-- Quota anno corrente -->
        <div class="blt-detail-card">
            <h2>💳 Quota <?= date('Y') ?></h2>
            <form method="post" action="<?= admin_url('admin-post.php') ?>">
                <?php wp_nonce_field('blt_save_quota'); ?>
                <input type="hidden" name="action" value="BLT_save_quota">
                <input type="hidden" name="tesserato_id" value="<?= $tesserato->id ?>">
                <input type="hidden" name="quota_id" value="<?= $quota_anno ? $quota_anno->id : '0' ?>">
                <input type="hidden" name="anno" value="<?= date('Y') ?>">
                <table class="form-table">
                    <tr><th>Importo (€)</th><td><input type="number" name="importo" step="0.01" min="0" value="<?= $quota_anno ? $quota_anno->importo : '' ?>" class="small-text" required></td></tr>
                    <tr><th>Stato Pagamento</th><td>
                        <select name="stato_pagamento">
                            <?php foreach(['non_pagato'=>'❌ Non Pagato','in_attesa'=>'⏳ In Attesa','pagato'=>'✅ Pagato'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= selected($quota_anno->stato_pagamento??'non_pagato', $v, false) ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td></tr>
                    <tr><th>Metodo Pagamento</th><td>
                        <select name="metodo_pagamento">
                            <?php foreach([''=>'—','contante'=>'Contante','bonifico'=>'Bonifico','pos'=>'POS/Carta','assegno'=>'Assegno'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= selected($quota_anno->metodo_pagamento??'', $v, false) ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td></tr>
                    <tr><th>Data Pagamento</th><td><input type="date" name="data_pagamento" value="<?= $quota_anno->data_pagamento??'' ?>"></td></tr>
                    <tr><th>N° Ricevuta</th><td><input type="text" name="ricevuta_numero" value="<?= esc_attr($quota_anno->ricevuta_numero??'') ?>" class="regular-text"></td></tr>
                    <tr><th>Note</th><td><textarea name="note" rows="2" class="large-text"><?= esc_textarea($quota_anno->note??'') ?></textarea></td></tr>
                </table>
                <button type="submit" class="button button-primary">💾 Salva Quota</button>
            </form>
        </div>

        <!-- Storico quote -->
        <?php if(!empty($quote)): ?>
        <div class="blt-detail-card">
            <h2>📊 Storico Quote</h2>
            <table class="wp-list-table widefat fixed blt-table">
                <thead><tr><th>Anno</th><th>Importo</th><th>Stato</th><th>Data Pag.</th><th>Ricevuta</th></tr></thead>
                <tbody>
                <?php foreach($quote as $q): ?>
                <tr>
                    <td><?= $q->anno ?></td>
                    <td>€ <?= number_format($q->importo, 2, ',', '.') ?></td>
                    <td><span class="blt-badge blt-badge-<?= $q->stato_pagamento ?>"><?= ucfirst(str_replace('_',' ',$q->stato_pagamento)) ?></span></td>
                    <td><?= $q->data_pagamento ? date_i18n('d/m/Y', strtotime($q->data_pagamento)) : '—' ?></td>
                    <td><?= esc_html($q->ricevuta_numero ?: '—') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>

    <!-- Elimina -->
    <div class="blt-danger-zone">
        <form method="post" action="<?= admin_url('admin-post.php') ?>" onsubmit="return confirm('Eliminare definitivamente questo tesserato?')">
            <?php wp_nonce_field('blt_delete_tesserato'); ?>
            <input type="hidden" name="action" value="BLT_delete_tesserato">
            <input type="hidden" name="tesserato_id" value="<?= $tesserato->id ?>">
            <button type="submit" class="button" style="color:#c00;border-color:#c00;">🗑️ Elimina Tesserato</button>
        </form>
    </div>
</div>
