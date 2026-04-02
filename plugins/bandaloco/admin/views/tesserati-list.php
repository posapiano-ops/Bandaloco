<?php if ( ! defined('ABSPATH') ) exit; ?>
<div class="wrap blt-wrap">
    <h1 class="blt-page-title">
        <span class="dashicons dashicons-groups"></span>
        Bandaloco &mdash; Tesserati
        <a href="<?= admin_url('admin.php?page=blt-tesserati&action=new') ?>" class="page-title-action">＋ Nuovo</a>
    </h1>

    <?php if ( isset($_GET['saved'])   ) : ?><div class="notice notice-success is-dismissible"><p>✅ Tesserato salvato.</p></div><?php endif; ?>
    <?php if ( isset($_GET['deleted']) ) : ?><div class="notice notice-success is-dismissible"><p>🗑️ Tesserato eliminato.</p></div><?php endif; ?>

    <!-- Filtri -->
    <form method="get" class="blt-filter-form">
        <input type="hidden" name="page" value="blt-tesserati">
        <div class="blt-filter-row">
            <input type="search" name="s" value="<?= esc_attr($search) ?>" placeholder="Cerca nome, cognome, email, tessera…" class="blt-search-input">
            <select name="stato">
                <option value="">Tutti gli stati</option>
                <?php foreach (['attivo'=>'Attivo','scaduto'=>'Scaduto','sospeso'=>'Sospeso','nuovo'=>'Nuovo'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= selected($stato,$v,false) ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
            <select name="direttivo">
                <option value="">Tutti i tesserati</option>
                <option value="1" <?= selected($direttivo??'','1',false) ?>>🏛️ Solo Direttivo</option>
                <option value="pub" <?= selected($direttivo??'','pub',false) ?>>🌐 Direttivo pubblicato</option>
            </select>
            <button type="submit" class="button">🔍 Filtra</button>
            <a href="<?= wp_nonce_url(admin_url('admin-post.php?action=blt_export_csv&stato='.urlencode($stato)), 'blt_export') ?>" class="button">📥 CSV</a>
            <a href="<?= wp_nonce_url(admin_url('admin-post.php?action=blt_export_pdf&stato='.urlencode($stato)), 'blt_export') ?>" class="button" target="_blank">📄 PDF</a>
        </div>
    </form>

    <p class="blt-count">Trovati: <strong><?= $totale ?></strong> tesserati</p>

    <?php if ( empty($tesserati) ) : ?>
        <div class="blt-empty-state">
            <span class="dashicons dashicons-id-alt" style="font-size:48px;color:#ccc;"></span>
            <p>Nessun tesserato trovato.</p>
            <a href="<?= admin_url('admin.php?page=blt-tesserati&action=new') ?>" class="button button-primary">Aggiungi il primo tesserato</a>
        </div>
    <?php else: ?>
    <table class="wp-list-table widefat fixed striped blt-table">
        <thead>
            <tr>
                <th style="width:120px">N° Tessera</th>
                <th>Nominativo</th>
                <th>Email / Telefono</th>
                <th style="width:90px">Tipo</th>
                <th style="width:150px">🏛️ Direttivo</th>
                <th style="width:80px">Stato</th>
                <th style="width:90px">Scadenza</th>
                <th style="width:110px">Quota <?= date('Y') ?></th>
                <th style="width:130px">Azioni</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ( $tesserati as $t ) :
            $scadenza    = $t->data_scadenza ? date_i18n('d/m/Y', strtotime($t->data_scadenza)) : '—';
            $stato_class = 'blt-badge blt-badge-' . $t->stato;
            $quota_class = 'blt-badge blt-badge-' . ($t->stato_pagamento ?? 'non_pagato');
            $quota_label = match($t->stato_pagamento ?? '') {
                'pagato'    => '✅ Pagato',
                'in_attesa' => '⏳ Attesa',
                default     => '❌ Non pag.',
            };
        ?>
        <tr>
            <td><strong><?= esc_html($t->numero_tessera) ?></strong></td>
            <td>
                <a href="<?= admin_url('admin.php?page=blt-tesserati&action=view&id='.$t->id) ?>">
                    <?= esc_html($t->cognome . ' ' . $t->nome) ?>
                </a>
            </td>
            <td>
                <?= $t->email    ? '<div>'.esc_html($t->email).'</div>'    : '' ?>
                <?= $t->telefono ? '<div class="blt-text-muted">'.esc_html($t->telefono).'</div>' : '' ?>
            </td>
            <td><?= esc_html(ucfirst($t->tipo_tessera)) ?></td>
            <td>
                <?php if ( $t->nel_direttivo ) : ?>
                    <div class="blt-direttivo-cell">
                        <span class="blt-badge-direttivo" title="<?= esc_attr($t->ruolo_direttivo) ?>">
                            🏛️ <?= esc_html($t->ruolo_direttivo ?: 'Direttivo') ?>
                        </span>
                        <?php if ( $t->pubblica_direttivo ) : ?>
                            <span class="blt-pub-dot" title="Pubblicato sul sito">🌐</span>
                        <?php else: ?>
                            <span class="blt-pub-dot blt-pub-dot-no" title="Non pubblicato">🔒</span>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <span class="blt-text-muted">—</span>
                <?php endif; ?>
            </td>
            <td><span class="<?= $stato_class ?>"><?= ucfirst($t->stato) ?></span></td>
            <td><?= $scadenza ?></td>
            <td><span class="<?= $quota_class ?>"><?= $quota_label ?></span></td>
            <td class="blt-actions">
                <a href="<?= admin_url('admin.php?page=blt-tesserati&action=view&id='.$t->id) ?>" class="button button-small" title="Vedi">👁</a>
                <a href="<?= admin_url('admin.php?page=blt-tesserati&action=edit&id='.$t->id) ?>" class="button button-small" title="Modifica">✏️</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php
    $total_pages = ceil( $totale / 20 );
    if ( $total_pages > 1 ) {
        echo '<div class="blt-pagination">';
        for ( $i = 1; $i <= $total_pages; $i++ ) {
            $class = $i === $paged ? ' current' : '';
            $url   = add_query_arg( array('page'=>'blt-tesserati','s'=>$search,'stato'=>$stato,'direttivo'=>$direttivo??'','paged'=>$i), admin_url('admin.php') );
            echo "<a href='$url' class='button$class'>$i</a> ";
        }
        echo '</div>';
    }
    ?>
    <?php endif; ?>
</div>
