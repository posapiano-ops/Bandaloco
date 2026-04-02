<?php if ( ! defined('ABSPATH') ) exit; ?>
<div class="wrap blt-wrap">
    <h1 class="blt-page-title">
        <span class="dashicons dashicons-money-alt"></span>
        Bandaloco &mdash; Quote Associative
    </h1>

    <?php if ( isset($_GET['bonifico_confermato']) ) : ?>
        <div class="notice notice-success is-dismissible">
            <p>✅ Bonifico confermato<?= isset($_GET['blt_warn']) ? '' : ' — tessera attivata' ?>.</p>
        </div>
    <?php endif; ?>
    <?php if ( isset($_GET['blt_warn']) && $_GET['blt_warn'] === 'importo_basso' ) : ?>
        <div class="notice notice-warning is-dismissible">
            <p>⚠️ <strong>Attenzione:</strong> la quota è stata marcata come pagata ma l'importo è inferiore alla quota dovuta — la tessera <strong>non è stata attivata automaticamente</strong>. Verifica e aggiorna manualmente se necessario.</p>
        </div>
    <?php endif; ?>

    <form method="get" class="blt-filter-form">
        <input type="hidden" name="page" value="blt-quote">
        <div class="blt-filter-row">
            <input type="search" name="s" value="<?= esc_attr($search ?? '') ?>" placeholder="Cerca tesserato…" class="blt-search-input">
            <select name="anno">
                <?php for($y=date('Y'); $y>=date('Y')-5; $y--): ?>
                <option value="<?= $y ?>" <?= selected($anno,$y,false) ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="button">🔍 Filtra</button>
        </div>
    </form>

    <div class="blt-summary-bar">
        Incasso <strong><?= $anno ?></strong>: 
        <strong style="color:#276749;">€ <?= number_format($totale_incasso ?: 0, 2, ',', '.') ?></strong>
    </div>

    <table class="wp-list-table widefat fixed striped blt-table">
        <thead>
            <tr>
                <th>N° Tessera</th>
                <th>Nominativo</th>
                <th>Anno</th>
                <th>Importo</th>
                <th>Stato Pagamento</th>
                <th>Data Pagamento</th>
                <th>Metodo</th>
                <th>Ricevuta</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
        <?php if ( empty($quote) ): ?>
            <tr><td colspan="9" style="text-align:center;padding:20px;color:#888;">Nessuna quota trovata per <?= $anno ?></td></tr>
        <?php else: foreach($quote as $q): ?>
        <tr>
            <td><?= esc_html($q->numero_tessera) ?></td>
            <td><a href="<?= admin_url('admin.php?page=blt-tesserati&action=view&id='.$q->tesserato_id) ?>"><?= esc_html($q->cognome.' '.$q->nome) ?></a></td>
            <td><?= $q->anno ?></td>
            <td>€ <?= number_format($q->importo,2,',','.') ?></td>
            <td><span class="blt-badge blt-badge-<?= $q->stato_pagamento ?>"><?= ucfirst(str_replace('_',' ',$q->stato_pagamento)) ?></span></td>
            <td><?= $q->data_pagamento ? date_i18n('d/m/Y', strtotime($q->data_pagamento)) : '—' ?></td>
            <td><?= esc_html($q->metodo_pagamento ?: '—') ?></td>
            <td><?= esc_html($q->ricevuta_numero ?: '—') ?></td>
            <td style="white-space:nowrap;">
                <a href="<?= admin_url('admin.php?page=blt-tesserati&action=view&id='.$q->tesserato_id) ?>" class="button button-small">👁 Vedi</a>
                <?php if ( $q->stato_pagamento === 'in_attesa' ) :
                    $confirm_url = wp_nonce_url(
                        admin_url('admin-post.php?action=blt_conferma_bonifico&quota_id='.$q->id.'&tesserato_id='.$q->tesserato_id),
                        'blt_conferma_bonifico_'.$q->id
                    ); ?>
                    <a href="<?= esc_url($confirm_url) ?>"
                       class="button button-small button-primary"
                       onclick="return confirm('Confermi la ricezione del bonifico e attivi la tessera?')">
                        ✅ Conferma bonifico
                    </a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
