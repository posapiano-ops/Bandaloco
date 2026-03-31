<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BLT_Activator {

    public static function activate() {
        self::create_tables();
        self::maybe_upgrade();
        self::create_roles();
        self::schedule_events();
        flush_rewrite_rules();
    }

    public static function deactivate() {
        wp_clear_scheduled_hook( 'blt_daily_check_scadenze' );
        flush_rewrite_rules();
    }

    public static function create_tables() {
        global $wpdb;

        $charset = $wpdb->get_charset_collate();

        // ── Tabella tesserati ────────────────────────────────────────────────────
        $wpdb->query( "
            CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}blt_tesserati` (
                `id`                 INT UNSIGNED    NOT NULL AUTO_INCREMENT,
                `user_id`            BIGINT UNSIGNED NULL DEFAULT NULL,
                `numero_tessera`     VARCHAR(20)     NOT NULL DEFAULT '',
                `nome`               VARCHAR(100)    NOT NULL DEFAULT '',
                `cognome`            VARCHAR(100)    NOT NULL DEFAULT '',
                `codice_fiscale`     VARCHAR(16)     NOT NULL DEFAULT '',
                `data_nascita`       DATE            NULL     DEFAULT NULL,
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
                PRIMARY KEY (`id`),
                UNIQUE KEY `numero_tessera` (`numero_tessera`),
                UNIQUE KEY `qr_token` (`qr_token`),
                KEY `idx_stato`      (`stato`),
                KEY `idx_scadenza`   (`data_scadenza`),
                KEY `idx_cognome`    (`cognome`),
                KEY `idx_direttivo`  (`nel_direttivo`, `ordine_direttivo`)
            ) {$charset}
        " );

        if ( $wpdb->last_error ) {
            error_log( 'BLT CREATE blt_tesserati ERROR: ' . $wpdb->last_error );
        }

        // ── Tabella quote ────────────────────────────────────────────────────────
        $wpdb->query( "
            CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}blt_quote` (
                `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
                `tesserato_id`     INT UNSIGNED    NOT NULL,
                `anno`             SMALLINT        NOT NULL DEFAULT 2024,
                `importo`          DECIMAL(8,2)    NOT NULL DEFAULT 0.00,
                `stato_pagamento`  VARCHAR(20)     NOT NULL DEFAULT 'non_pagato',
                `metodo_pagamento` VARCHAR(50)     NOT NULL DEFAULT '',
                `data_pagamento`   DATE            NULL     DEFAULT NULL,
                `ricevuta_numero`  VARCHAR(50)     NOT NULL DEFAULT '',
                `note`             TEXT            NULL,
                `created_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_anno_tesserato` (`tesserato_id`, `anno`),
                KEY `idx_tesserato` (`tesserato_id`)
            ) {$charset}
        " );

        if ( $wpdb->last_error ) {
            error_log( 'BLT CREATE blt_quote ERROR: ' . $wpdb->last_error );
        }

        // ── Tabella impostazioni ─────────────────────────────────────────────────
        $wpdb->query( "
            CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}blt_impostazioni` (
                `chiave`     VARCHAR(150) NOT NULL DEFAULT '',
                `valore`     LONGTEXT     NULL,
                `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`chiave`)
            ) {$charset}
        " );

        if ( $wpdb->last_error ) {
            error_log( 'BLT CREATE blt_impostazioni ERROR: ' . $wpdb->last_error );
        }

        // ── Impostazioni di default ──────────────────────────────────────────────
        self::insert_defaults();
    }

    private static function insert_defaults() {
        global $wpdb;
        $t = $wpdb->prefix . 'blt_impostazioni';

        $defaults = array(
            'nome_proloco'           => get_bloginfo('name'),
            'quota_ordinario'        => '20.00',
            'quota_sostenitore'      => '50.00',
            'quota_familiare'        => '30.00',
            'quota_junior'           => '10.00',
            'giorni_avviso'          => '30',
            'prefisso_tessera'       => 'PL',
            'anno_corrente'          => date('Y'),
            'email_notifiche'        => get_option('admin_email'),
            'testo_tessera'          => 'Bandaloco',
            'logo_tessera'           => '',
            'google_login_enabled'   => '0',
            'google_client_id'       => '',
            'google_client_secret'   => '',
            'facebook_login_enabled' => '0',
            'facebook_app_id'        => '',
            'facebook_app_secret'    => '',
            'stripe_enabled'         => '0',
            'stripe_publishable_key' => '',
            'stripe_secret_key'      => '',
            'stripe_webhook_secret'  => '',
            'stripe_sandbox'         => '0',
            'paypal_enabled'         => '0',
            'paypal_client_id'       => '',
            'paypal_secret'          => '',
            'paypal_sandbox'         => '1',
            'direttivo_titolo'       => 'Il Nostro Direttivo',
            'direttivo_descrizione'  => '',
            'pagina_iscrizione'      => '',
            'pagina_area'            => '',
            'bonifico_enabled'       => '0',
            'bonifico_iban'          => '',
            'bonifico_intestato'     => '',
            'bonifico_causale'       => 'Quota associativa',
            'bonifico_note'          => '',
        );

        foreach ( $defaults as $k => $v ) {
            $wpdb->query( $wpdb->prepare(
                "INSERT IGNORE INTO `{$t}` (`chiave`, `valore`) VALUES (%s, %s)",
                $k, $v
            ) );
        }
    }

    /**
     * Aggiunge colonne mancanti su installazioni già esistenti.
     */
    public static function maybe_upgrade() {
        global $wpdb;
        $t = $wpdb->prefix . 'blt_tesserati';

        // Verifica esistenza tabella prima di fare ALTER
        $exists = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = %s AND table_name = %s",
            DB_NAME, $t
        ) );
        if ( ! $exists ) return;

        // Aggiunge colonne direttivo se mancanti
        $col = $wpdb->get_var( "SHOW COLUMNS FROM `{$t}` LIKE 'nel_direttivo'" );
        if ( ! $col ) {
            $wpdb->query( "ALTER TABLE `{$t}`
                ADD COLUMN `nel_direttivo`      TINYINT(1)   NOT NULL DEFAULT 0     AFTER `qr_token`,
                ADD COLUMN `ruolo_direttivo`    VARCHAR(100) NOT NULL DEFAULT ''    AFTER `nel_direttivo`,
                ADD COLUMN `ordine_direttivo`   SMALLINT     NOT NULL DEFAULT 0     AFTER `ruolo_direttivo`,
                ADD COLUMN `pubblica_direttivo` TINYINT(1)   NOT NULL DEFAULT 0     AFTER `ordine_direttivo`,
                ADD COLUMN `bio_direttivo`      TEXT         NULL                   AFTER `pubblica_direttivo`
            " );
            if ( $wpdb->last_error ) {
                error_log( 'BLT maybe_upgrade ALTER ERROR: ' . $wpdb->last_error );
            }
        }

        // Aggiunge impostazioni mancanti
        self::insert_defaults();
    }

    private static function create_roles() {
        if ( ! get_role( 'tesserato_bandaloco' ) ) {
            add_role( 'tesserato_bandaloco', 'Tesserato Bandaloco', array( 'read' => true ) );
        }
    }

    private static function schedule_events() {
        if ( ! wp_next_scheduled( 'blt_daily_check_scadenze' ) ) {
            wp_schedule_event( strtotime( 'tomorrow 08:00:00' ), 'daily', 'blt_daily_check_scadenze' );
        }
    }
}
