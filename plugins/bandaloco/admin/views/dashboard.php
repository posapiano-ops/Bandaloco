<?php if ( ! defined('ABSPATH') ) exit; ?>
<div class="wrap blt-wrap">
    <h1 class="blt-page-title">
        <span class="dashicons dashicons-id-alt"></span>
       Bandaloco &mdash; Dashboard
    </h1>

    <?php if ( isset($_GET['saved']) ) : ?>
        <div class="notice notice-success is-dismissible"><p>✅ Operazione completata con successo.</p></div>
    <?php endif; ?>

    <div class="blt-stats-grid">
        <div class="blt-stat-card blt-stat-blue">
            <div class="blt-stat-icon">👥</div>
            <div class="blt-stat-value"><?= number_format($stats['totale']) ?></div>
            <div class="blt-stat-label">Tesserati Totali</div>
        </div>
        <div class="blt-stat-card blt-stat-green">
            <div class="blt-stat-icon">✅</div>
            <div class="blt-stat-value"><?= number_format($stats['attivi']) ?></div>
            <div class="blt-stat-label">Tessere Attive</div>
        </div>
        <div class="blt-stat-card blt-stat-red">
            <div class="blt-stat-icon">⚠️</div>
            <div class="blt-stat-value"><?= number_format($stats['scaduti']) ?></div>
            <div class="blt-stat-label">Tessere Scadute</div>
        </div>
        <div class="blt-stat-card blt-stat-orange">
            <div class="blt-stat-icon">⏰</div>
            <div class="blt-stat-value"><?= number_format($stats['in_scadenza']) ?></div>
            <div class="blt-stat-label">In Scadenza (30gg)</div>
        </div>
        <div class="blt-stat-card blt-stat-purple">
            <div class="blt-stat-icon">💰</div>
            <div class="blt-stat-value">€ <?= number_format($stats['incasso_anno'], 2, ',', '.') ?></div>
            <div class="blt-stat-label">Incasso <?= date('Y') ?></div>
        </div>
        <div class="blt-stat-card blt-stat-teal">
            <div class="blt-stat-icon">🆕</div>
            <div class="blt-stat-value"><?= number_format($stats['nuovi']) ?></div>
            <div class="blt-stat-label">Nuove Iscrizioni</div>
        </div>
    </div>

    <?php
    // Mostra notifiche non lette e le segna come lette
    $notifiche = get_option('blt_notifiche_admin', array());
    $non_lette = array_filter($notifiche, fn($n) => ! $n['letta']);
    if ( $non_lette ) :
        BLT_Email::segna_tutte_lette();
    ?>
    <div class="blt-notifiche-box" style="margin-bottom:24px;">
        <h3 style="margin:0 0 12px;font-size:14px;font-weight:600;">
            🔔 Notifiche recenti
            <?php if ($non_lette) : ?>
                <span style="background:#ef4444;color:#fff;border-radius:10px;padding:2px 8px;font-size:11px;margin-left:6px;">
                    <?= count($non_lette) ?> nuove
                </span>
            <?php endif; ?>
        </h3>
        <table class="widefat striped" style="font-size:13px;">
            <thead>
                <tr>
                    <th>Tipo</th><th>Tesserato</th><th>Tessera N°</th><th>Data</th><th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( array_reverse($non_lette) as $n ) :
                $tipo_label = array('iscrizione'=>'🆕 Nuova iscrizione','bonifico'=>'🏦 Bonifico richiesto');
                $label = $tipo_label[$n['tipo']] ?? ucfirst($n['tipo']);
                $url   = admin_url('admin.php?page=blt-tesserati&action=view&id=' . (int)$n['id']);
            ?>
                <tr>
                    <td><?= $label ?></td>
                    <td><?= esc_html($n['nome']) ?></td>
                    <td><?= esc_html($n['tessera']) ?></td>
                    <td><?= esc_html( date_i18n('d/m/Y H:i', strtotime($n['data'])) ) ?></td>
                    <td><a href="<?= esc_url($url) ?>" class="button button-small">Visualizza</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="blt-actions-row">
        <a href="<?= admin_url('admin.php?page=blt-tesserati&action=new') ?>" class="button button-primary button-large">
            ＋ Nuovo Tesserato
        </a>
        <a href="<?= admin_url('admin.php?page=blt-tesserati') ?>" class="button button-large">
            📋 Elenco Tesserati
        </a>
        <a href="<?= admin_url('admin.php?page=blt-quote') ?>" class="button button-large">
            💳 Gestione Quote
        </a>
        <a href="<?= wp_nonce_url(admin_url('admin-post.php?action=blt_export_csv'), 'blt_export') ?>" class="button button-large">
            📥 Esporta CSV
        </a>
        <a href="<?= wp_nonce_url(admin_url('admin-post.php?action=blt_export_pdf'), 'blt_export') ?>" class="button button-large" target="_blank">
            📄 Esporta PDF
        </a>
    </div>

    <div class="blt-shortcodes-box">
        <h3>Shortcode per le pagine del sito</h3>
        <p>Inserisci questi shortcode nelle pagine WordPress:</p>
        <div class="blt-shortcode-item">
            <code>[proloco_area_tesserato]</code>
            <span>Area riservata del tesserato (tessera digitale, stato, quota)</span>
        </div>
        <div class="blt-shortcode-item">
            <code>[proloco_iscrizione]</code>
            <span>Modulo di iscrizione online per nuovi tesserati</span>
        </div>
         <div class="blt-shortcode-item">
            <code>[proloco_direttivo]</code>
            <span>Pagina Pubblica Direttivo</span>
        </div>
    </div>
</div>
