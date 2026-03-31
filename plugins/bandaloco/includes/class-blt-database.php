<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BLT_Database {

    public static function get_setting( $chiave, $default = '' ) {
        global $wpdb;
        $row = $wpdb->get_var( $wpdb->prepare(
            "SELECT valore FROM {$wpdb->prefix}blt_impostazioni WHERE chiave = %s", $chiave
        ) );
        return $row !== null ? $row : $default;
    }

    public static function set_setting( $chiave, $valore ) {
        global $wpdb;
        return $wpdb->replace( $wpdb->prefix . 'blt_impostazioni', array(
            'chiave' => $chiave,
            'valore' => $valore,
        ) );
    }

    public static function get_all_settings() {
        global $wpdb;
        $rows = $wpdb->get_results( "SELECT chiave, valore FROM {$wpdb->prefix}blt_impostazioni", ARRAY_A );
        $out  = array();
        foreach ( $rows as $r ) $out[ $r['chiave'] ] = $r['valore'];
        return $out;
    }

    // ── Tesserati ──────────────────────────────────────────────────────────────

    public static function get_tesserati( $args = array() ) {
        global $wpdb;
        $defaults = array(
            'stato'      => '',
            'search'     => '',
            'orderby'    => 'cognome',
            'order'      => 'ASC',
            'per_page'   => 20,
            'paged'      => 1,
        );
        $args   = wp_parse_args( $args, $defaults );
        $where  = array( '1=1' );
        $params = array();

        if ( $args['stato'] ) {
            $where[]  = 't.stato = %s';
            $params[] = $args['stato'];
        }
        if ( ! empty($args['direttivo']) ) {
            if ( $args['direttivo'] === 'pub' ) {
                $where[] = 't.nel_direttivo = 1 AND t.pubblica_direttivo = 1';
            } else {
                $where[] = 't.nel_direttivo = 1';
            }
        }
        if ( $args['search'] ) {
            $where[]  = '(t.nome LIKE %s OR t.cognome LIKE %s OR t.email LIKE %s OR t.numero_tessera LIKE %s)';
            $like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $params   = array_merge( $params, array( $like, $like, $like, $like ) );
        }

        $where_sql = implode( ' AND ', $where );
        $order_sql = sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] ) ?: 'cognome ASC';
        $offset    = ( absint( $args['paged'] ) - 1 ) * absint( $args['per_page'] );

        $sql = "SELECT t.*, q.stato_pagamento, q.importo as quota_anno
                FROM {$wpdb->prefix}blt_tesserati t
                LEFT JOIN {$wpdb->prefix}blt_quote q
                    ON q.tesserato_id = t.id AND q.anno = YEAR(CURDATE())
                WHERE $where_sql
                ORDER BY $order_sql
                LIMIT %d OFFSET %d";

        $params[] = absint( $args['per_page'] );
        $params[] = $offset;

        return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
    }

    public static function count_tesserati( $args = array() ) {
        global $wpdb;
        $defaults = array( 'stato' => '', 'search' => '', 'direttivo' => '' );
        $args     = wp_parse_args( $args, $defaults );
        $where    = array( '1=1' );
        $params   = array();

        if ( $args['stato'] ) { $where[] = 'stato = %s'; $params[] = $args['stato']; }
        if ( ! empty($args['direttivo']) ) {
            $where[] = $args['direttivo'] === 'pub' ? 'nel_direttivo = 1 AND pubblica_direttivo = 1' : 'nel_direttivo = 1';
        }
        if ( $args['search'] ) {
            $where[]  = '(nome LIKE %s OR cognome LIKE %s OR email LIKE %s OR numero_tessera LIKE %s)';
            $like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $params   = array_merge( $params, array( $like, $like, $like, $like ) );
        }
        $where_sql = implode( ' AND ', $where );
        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}blt_tesserati WHERE $where_sql";
        return (int) ( $params ? $wpdb->get_var( $wpdb->prepare( $sql, $params ) ) : $wpdb->get_var( $sql ) );
    }

    public static function get_tesserato( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}BLT_tesserati WHERE id = %d", absint( $id )
        ) );
    }

    public static function get_tesserato_by_token( $token ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}blt_tesserati WHERE qr_token = %s", sanitize_text_field( $token )
        ) );
    }

    public static function get_tesserato_by_user( $user_id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}blt_tesserati WHERE user_id = %d", absint( $user_id )
        ) );
    }

    public static function save_tesserato( $data, $id = 0 ) {
        global $wpdb;
        $table  = $wpdb->prefix . 'blt_tesserati';

        $fields = array(
            'nome','cognome','codice_fiscale','data_nascita','luogo_nascita',
            'indirizzo','cap','citta','provincia','email','telefono',
            'tipo_tessera','stato','data_iscrizione','data_scadenza',
            'ruolo_direttivo','ordine_direttivo','foto',
        );
        $row = array();
        foreach ( $fields as $f ) {
            if ( isset( $data[ $f ] ) ) {
                $row[ $f ] = sanitize_text_field( $data[ $f ] );
            }
        }

        // Campi testo lungo
        if ( isset( $data['note'] ) )         $row['note']          = sanitize_textarea_field( $data['note'] );
        if ( isset( $data['bio_direttivo'] ) ) $row['bio_direttivo'] = sanitize_textarea_field( $data['bio_direttivo'] );

        // Checkbox -> 0/1  (solo se il campo è presente nell'input)
        if ( array_key_exists('nel_direttivo', $data) )
            $row['nel_direttivo']      = ! empty( $data['nel_direttivo'] ) ? 1 : 0;
        if ( array_key_exists('pubblica_direttivo', $data) )
            $row['pubblica_direttivo'] = ! empty( $data['pubblica_direttivo'] ) ? 1 : 0;

        if ( $id ) {
            // UPDATE: applica solo i campi passati, senza toccare il resto
            $result = $wpdb->update( $table, $row, array( 'id' => $id ) );
            if ( $result === false ) {
                error_log( 'BLT save_tesserato UPDATE error: ' . $wpdb->last_error );
            }
            return $id;
        } else {
            // INSERT: garantisce valori obbligatori non nulli
            if ( ! isset( $row['nel_direttivo'] ) )      $row['nel_direttivo']      = 0;
            if ( ! isset( $row['pubblica_direttivo'] ) ) $row['pubblica_direttivo'] = 0;
            if ( empty( $row['stato'] ) )                $row['stato']              = 'nuovo';
            if ( empty( $row['tipo_tessera'] ) )         $row['tipo_tessera']       = 'ordinario';
            if ( empty( $row['data_iscrizione'] ) )      $row['data_iscrizione']    = current_time('Y-m-d');
            if ( empty( $row['data_scadenza'] ) )        $row['data_scadenza']      = date('Y') . '-12-31';
            if ( empty( $row['nome'] ) )                 $row['nome']               = '—';
            if ( empty( $row['cognome'] ) )              $row['cognome']            = '—';
            $row['numero_tessera'] = self::genera_numero_tessera();
            $row['qr_token']       = wp_generate_password( 32, false );

            $result = $wpdb->insert( $table, $row );
            if ( $result === false ) {
                error_log( 'BLT save_tesserato INSERT error: ' . $wpdb->last_error );
                return 0;
            }
            return (int) $wpdb->insert_id;
        }
    }

    public static function delete_tesserato( $id ) {
        global $wpdb;
        return $wpdb->delete( $wpdb->prefix . 'blt_tesserati', array( 'id' => absint( $id ) ) );
    }


    public static function get_direttivo( bool $solo_pubblicati = true ): array {
        global $wpdb;
        $where = $solo_pubblicati
            ? "WHERE nel_direttivo = 1 AND pubblica_direttivo = 1 AND stato = 'attivo'"
            : "WHERE nel_direttivo = 1";
        return $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}blt_tesserati $where ORDER BY ordine_direttivo ASC, cognome ASC"
        ) ?: [];
    }

        public static function genera_numero_tessera() {
        global $wpdb;
        $prefisso = BLT_Database::get_setting( 'prefisso_tessera', 'PL' );
        $anno     = date( 'Y' );
        $last     = $wpdb->get_var( $wpdb->prepare(
            "SELECT numero_tessera FROM {$wpdb->prefix}blt_tesserati
             WHERE numero_tessera LIKE %s ORDER BY id DESC LIMIT 1",
            $prefisso . $anno . '%'
        ) );
        $prog = $last ? ( (int) substr( $last, -4 ) + 1 ) : 1;
        return $prefisso . $anno . str_pad( $prog, 4, '0', STR_PAD_LEFT );
    }

    // ── Quote ──────────────────────────────────────────────────────────────────

    public static function get_quote_tesserato( $tesserato_id ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}blt_quote WHERE tesserato_id = %d ORDER BY anno DESC",
            absint( $tesserato_id )
        ) );
    }

    public static function get_quota( $tesserato_id, $anno ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}blt_quote WHERE tesserato_id = %d AND anno = %d",
            absint( $tesserato_id ), absint( $anno )
        ) );
    }

    public static function save_quota( $data, $id = 0 ) {
        global $wpdb;
        $row = array(
            'tesserato_id'    => absint( $data['tesserato_id'] ),
            'anno'            => absint( $data['anno'] ),
            'importo'         => floatval( $data['importo'] ),
            'stato_pagamento' => sanitize_text_field( $data['stato_pagamento'] ),
            'metodo_pagamento'=> sanitize_text_field( $data['metodo_pagamento'] ?? '' ),
            'data_pagamento'  => sanitize_text_field( $data['data_pagamento'] ?? '' ) ?: null,
            'ricevuta_numero' => sanitize_text_field( $data['ricevuta_numero'] ?? '' ),
            'note'            => sanitize_textarea_field( $data['note'] ?? '' ),
        );
        if ( $id ) {
            $wpdb->update( $wpdb->prefix . 'blt_quote', $row, array( 'id' => $id ) );
            return $id;
        } else {
            $wpdb->insert( $wpdb->prefix . 'blt_quote', $row );
            return $wpdb->insert_id;
        }
    }

    // ── Statistiche ───────────────────────────────────────────────────────────

    public static function get_statistiche() {
        global $wpdb;
        $anno = date('Y');
        return array(
            'totale'         => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}blt_tesserati" ),
            'attivi'         => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}blt_tesserati WHERE stato='attivo'" ),
            'scaduti'        => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}blt_tesserati WHERE stato='scaduto'" ),
            'nuovi'          => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}blt_tesserati WHERE stato='nuovo'" ),
            'pagato_anno'    => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}blt_quote WHERE anno=%d AND stato_pagamento='pagato'", $anno ) ),
            'incasso_anno'   => (float) $wpdb->get_var( $wpdb->prepare( "SELECT SUM(importo) FROM {$wpdb->prefix}blt_quote WHERE anno=%d AND stato_pagamento='pagato'", $anno ) ),
            'in_scadenza'    => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}blt_tesserati WHERE stato='attivo' AND data_scadenza BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)" ),
        );
    }
}
