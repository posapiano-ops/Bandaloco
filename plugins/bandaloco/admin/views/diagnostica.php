<?php if ( ! defined('ABSPATH') ) exit;
if ( ! current_user_can('manage_options') ) wp_die('Accesso negato.');
global $wpdb;

$log = array();

// Forza installazione se richiesto
if ( isset($_POST['blt_force_install']) && check_admin_referer('blt_force_install') ) {

    $charset = $wpdb->get_charset_collate();
    $t       = $wpdb->prefix . 'blt_tesserati';

    $wpdb->show_errors();

    $sql = "CREATE TABLE IF NOT EXISTS `{$t}` (
        `id`                 INT UNSIGNED    NOT NULL AUTO_INCREMENT,
        `user_id`            BIGINT UNSIGNED NULL DEFAULT NULL,
        `numero_tessera`     VARCHAR(20)     NOT NULL DEFAULT '',
        `nome`               VARCHAR(100)    NOT NULL DEFAULT '',
        `cognome`            VARCHAR(100)    NOT NULL DEFAULT '',
        `codice_fiscale`     VARCHAR(16)     NOT NULL DEFAULT '',
        `data_nascita`       DATE            NULL DEFAULT NULL,
        `luogo_nascita`      VARCHAR(100)    NOT NULL DEFAULT '',
        `indirizzo`          VARCHAR(200)    NOT NULL DEFAULT '',
        `cap`                VARCHAR(10)     NOT NULL DEFAULT '',
        `citta`              VARCHAR(100)    NOT NULL DEFAULT '',
        `provincia`          VARCHAR(5)      NOT NULL DEFAULT '',
        `email`              VARCHAR(150)    NOT NULL DEFAULT '',
        `telefono`           VARCHAR(30)     NOT NULL DEFAULT '',
        `tipo_tessera`       VARCHAR(50)     NOT NULL DEFAULT 'ordinario',
        `stato`              VARCHAR(20)     NOT NULL DEFAULT 'nuovo',
        `data_iscrizione`    DATE            NOT NULL DEFAULT '2000-01-01',
        `data_scadenza`      DATE            NOT NULL DEFAULT '2099-12-31',
        `note`               TEXT            NULL,
        `foto`               VARCHAR(255)    NOT NULL DEFAULT '',
        `qr_token`           VARCHAR(64)     NOT NULL DEFAULT '',
        `nel_direttivo`      TINYINT(1)      NOT NULL DEFAULT 0,
        `ruolo_direttivo`    VARCHAR(100)    NOT NULL DEFAULT '',
        `ordine_direttivo`   SMALLINT        NOT NULL DEFAULT 0,
        `pubblica_direttivo` TINYINT(1)      NOT NULL DEFAULT 0,
        `bio_direttivo`      TEXT            NULL,
        `created_at`         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at`         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) {$charset}";

    $result = $wpdb->query( $sql );
    $log[] = array(
        'query'  => $sql,
        'result' => $result,
        'error'  => $wpdb->last_error,
    );

    // Quote
    $tq  = $wpdb->prefix . 'blt_quote';
    $sql2 = "CREATE TABLE IF NOT EXISTS `{$tq}` (
        `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `tesserato_id`     INT UNSIGNED NOT NULL,
        `anno`             SMALLINT     NOT NULL DEFAULT 2024,
        `importo`          DECIMAL(8,2) NOT NULL DEFAULT 0.00,
        `stato_pagamento`  VARCHAR(20)  NOT NULL DEFAULT 'non_pagato',
        `metodo_pagamento` VARCHAR(50)  NOT NULL DEFAULT '',
        `data_pagamento`   DATE         NULL DEFAULT NULL,
        `ricevuta_numero`  VARCHAR(50)  NOT NULL DEFAULT '',
        `note`             TEXT         NULL,
        `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) {$charset}";

    $result2 = $wpdb->query( $sql2 );
    $log[] = array(
        'query'  => $sql2,
        'result' => $result2,
        'error'  => $wpdb->last_error,
    );

    // Impostazioni
    $ti   = $wpdb->prefix . 'blt_impostazioni';
    $sql3 = "CREATE TABLE IF NOT EXISTS `{$ti}` (
        `chiave`     VARCHAR(150) NOT NULL DEFAULT '',
        `valore`     LONGTEXT     NULL,
        `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`chiave`)
    ) {$charset}";

    $result3 = $wpdb->query( $sql3 );
    $log[] = array(
        'query'  => $sql3,
        'result' => $result3,
        'error'  => $wpdb->last_error,
    );

    $wpdb->hide_errors();
}

// Stato attuale
$tabella     = $wpdb->prefix . 'blt_tesserati';
$esiste_info = (int) $wpdb->get_var( $wpdb->prepare(
    "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
    DB_NAME, $tabella
) );
$esiste_show = $wpdb->get_var( "SHOW TABLES LIKE '{$tabella}'" );
$tutte_blt   = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}blt_%'" );
$user_grants = $wpdb->get_results( "SHOW GRANTS FOR CURRENT_USER()" );
?>
<div class="wrap blt-wrap">
<h1 class="blt-page-title"><span class="dashicons dashicons-admin-tools"></span>Bandaloco &mdash; Diagnostica</h1>

<h2>Stato database</h2>
<table class="widefat" style="max-width:800px;margin-bottom:20px;">
<thead><tr><th>Verifica</th><th>Valore</th><th></th></tr></thead>
<tbody>
<tr><td>DB_NAME</td><td><code><?= esc_html(DB_NAME) ?></code></td><td>ℹ️</td></tr>
<tr><td>$wpdb->prefix</td><td><code><?= esc_html($wpdb->prefix) ?></code></td><td>ℹ️</td></tr>
<tr><td>Tabella attesa</td><td><code><?= esc_html($tabella) ?></code></td><td>ℹ️</td></tr>
<tr><td>Esiste (information_schema)</td><td><code><?= $esiste_info ? 'SÌ' : 'NO' ?></code></td><td><?= $esiste_info ? '✅' : '❌' ?></td></tr>
<tr><td>Esiste (SHOW TABLES)</td><td><code><?= esc_html($esiste_show ?: 'NULL') ?></code></td><td><?= $esiste_show ? '✅' : '❌' ?></td></tr>
<tr><td>Tabelle blt_* trovate</td><td><code><?= esc_html($tutte_blt ? implode(', ', $tutte_blt) : 'nessuna') ?></code></td><td><?= $tutte_blt ? '✅' : '❌' ?></td></tr>
<tr><td>Ultimo errore $wpdb</td><td><code><?= esc_html($wpdb->last_error ?: '—') ?></code></td><td>ℹ️</td></tr>
<tr><td>charset_collate</td><td><code><?= esc_html($wpdb->get_charset_collate()) ?></code></td><td>ℹ️</td></tr>
</tbody>
</table>

<h2>Permessi utente DB</h2>
<table class="widefat" style="max-width:800px;margin-bottom:20px;">
<tbody>
<?php foreach ( $user_grants as $g ) : $vals = array_values( (array)$g ); ?>
<tr><td><code><?= esc_html($vals[0]) ?></code></td></tr>
<?php endforeach; ?>
</tbody>
</table>

<?php if ( $log ) : ?>
<h2>Risultato ultimo tentativo creazione</h2>
<?php foreach ( $log as $i => $entry ) : ?>
<div style="background:<?= $entry['error'] ? '#fcebec' : '#edfaef' ?>;border:1px solid <?= $entry['error'] ? '#f5b8b8' : '#a8e6b8' ?>;border-radius:6px;padding:14px;margin-bottom:12px;max-width:800px;">
    <strong><?= $entry['error'] ? '❌ ERRORE tabella ' . ($i+1) : '✅ OK tabella ' . ($i+1) ?></strong><br>
    <?php if ( $entry['error'] ) : ?>
    <code style="display:block;margin-top:8px;color:#6e1a1a;"><?= esc_html($entry['error']) ?></code>
    <?php endif; ?>
    <details style="margin-top:8px;"><summary style="cursor:pointer;font-size:12px;color:#757575;">Query eseguita</summary>
    <pre style="font-size:11px;overflow:auto;background:#f6f7f7;padding:8px;border-radius:4px;margin-top:6px;"><?= esc_html($entry['query']) ?></pre></details>
</div>
<?php endforeach; ?>
<?php endif; ?>

<form method="post" style="margin-top:10px;">
    <?php wp_nonce_field('blt_force_install'); ?>
    <button type="submit" name="BLT_force_install" value="1" class="button button-primary button-large">
        🔧 Crea tabelle adesso (con log dettagliato)
    </button>
</form>

<p style="color:#757575;font-size:12px;margin-top:20px;">
    Se vedi "❌ ERRORE" copia il messaggio rosso e mandalo — è l'unico modo per capire cosa blocca MySQL.
</p>
</div>
