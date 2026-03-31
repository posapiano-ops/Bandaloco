<?php
/**
 * Template: Direttivo BAndaloco
 * Shortcode: [bandaloco_direttivo]
 * Mostra la lista pubblica dei membri del direttivo con ruolo, foto e bio.
 */
if ( ! defined('ABSPATH') ) exit;

$membri   = BLT_Database::get_direttivo( true ); // solo pubblicati
$titolo   = BLT_Database::get_setting( 'direttivo_titolo', 'Il Nostro Direttivo' );
$descr    = BLT_Database::get_setting( 'direttivo_descrizione', '' );
$proloco  = BLT_Database::get_setting( 'nome_proloco', 'Bandaloco' );

// Ordine priorità ruoli per badge colore
$ruoli_prioritari = [ 'Presidente', 'Vicepresidente', 'Segretario', 'Tesoriere' ];
?>

<div class="blt-direttivo-wrap">

    <div class="blt-direttivo-header">
        <h2 class="blt-direttivo-title"><?= esc_html( $titolo ) ?></h2>
        <?php if ( $descr ) : ?>
            <p class="blt-direttivo-descr"><?= esc_html( $descr ) ?></p>
        <?php endif; ?>
    </div>

    <?php if ( empty($membri) ) : ?>
        <p class="blt-direttivo-empty">Nessun membro del direttivo disponibile al momento.</p>
    <?php else : ?>

    <div class="blt-direttivo-grid">
        <?php foreach ( $membri as $m ) :
            $ruolo        = $m->ruolo_direttivo ?: 'Membro';
            $is_prioritario = in_array( $ruolo, $ruoli_prioritari, true );
            $foto_url     = ! empty($m->foto) ? esc_url($m->foto) : '';
            $iniziali     = strtoupper( mb_substr($m->nome,0,1) . mb_substr($m->cognome,0,1) );
        ?>
        <div class="blt-direttivo-card <?= $is_prioritario ? 'blt-card-highlight' : '' ?>">

            <div class="blt-card-foto">
                <?php if ( $foto_url ) : ?>
                    <img src="<?= $foto_url ?>" alt="<?= esc_attr($m->nome.' '.$m->cognome) ?>" loading="lazy">
                <?php else : ?>
                    <div class="blt-card-iniziali"><?= esc_html($iniziali) ?></div>
                <?php endif; ?>
                <?php if ( $is_prioritario ) : ?>
                    <div class="blt-card-corona">⭐</div>
                <?php endif; ?>
            </div>

            <div class="blt-card-body">
                <div class="blt-card-ruolo"><?= esc_html( $ruolo ) ?></div>
                <h3 class="blt-card-nome"><?= esc_html( $m->nome . ' ' . $m->cognome ) ?></h3>
                <?php if ( ! empty($m->bio_direttivo) ) : ?>
                    <p class="blt-card-bio"><?= nl2br( esc_html( $m->bio_direttivo ) ) ?></p>
                <?php endif; ?>
            </div>

        </div>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</div>
