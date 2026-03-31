<?php
/**
 * BLT_Quota – Classe wrapper per la gestione delle quote associative.
 *
 * Fornisce metodi statici semantici che delegano a BLT_Database,
 * con logica aggiuntiva per la gestione dei pagamenti e degli stati.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BLT_Quota {

    // ── Lettura ───────────────────────────────────────────────────────────────

    /**
     * Restituisce tutte le quote di un tesserato, ordinate per anno decrescente.
     *
     * @param int $tesserato_id
     * @return object[]
     */
    public static function get_by_tesserato( int $tesserato_id ): array {
        return BLT_Database::get_quote_tesserato( $tesserato_id ) ?: array();
    }

    /**
     * Restituisce la quota di un tesserato per un anno specifico.
     *
     * @param int $tesserato_id
     * @param int $anno          Default: anno corrente.
     * @return object|null
     */
    public static function get( int $tesserato_id, int $anno = 0 ): ?object {
        if ( ! $anno ) $anno = (int) date('Y');
        return BLT_Database::get_quota( $tesserato_id, $anno ) ?: null;
    }

    /**
     * Restituisce la quota dell'anno corrente per un tesserato.
     *
     * @param int $tesserato_id
     * @return object|null
     */
    public static function get_corrente( int $tesserato_id ): ?object {
        return self::get( $tesserato_id, (int) date('Y') );
    }

    /**
     * Verifica se la quota dell'anno corrente è stata pagata.
     *
     * @param int $tesserato_id
     * @return bool
     */
    public static function is_pagata( int $tesserato_id ): bool {
        $quota = self::get_corrente( $tesserato_id );
        return $quota && $quota->stato_pagamento === 'pagato';
    }

    // ── Scrittura ─────────────────────────────────────────────────────────────

    /**
     * Crea o aggiorna una quota.
     *
     * @param array $data  Campi della quota (tesserato_id, anno, importo, ecc.).
     * @param int   $id    ID del record da aggiornare (0 = nuovo).
     * @return int         ID della quota creata o aggiornata.
     */
    public static function save( array $data, int $id = 0 ): int {
        return BLT_Database::save_quota( $data, $id );
    }

    /**
     * Registra un pagamento per un tesserato.
     * Crea la quota se non esiste, altrimenti la aggiorna.
     *
     * @param int   $tesserato_id
     * @param array $dati {
     *   @type int    $anno              Anno della quota (default: anno corrente)
     *   @type float  $importo           Importo pagato
     *   @type string $stato_pagamento   'pagato'|'in_attesa'|'non_pagato'
     *   @type string $metodo_pagamento  es. 'stripe', 'paypal', 'contante', 'bonifico'
     *   @type string $data_pagamento    Y-m-d (default: oggi)
     *   @type string $ricevuta_numero   Numero ricevuta / transaction ID
     *   @type string $note              Note libere
     * }
     * @return int ID della quota
     */
    public static function registra_pagamento( int $tesserato_id, array $dati ): int {
        $anno     = (int) ( $dati['anno'] ?? date('Y') );
        $esistente = self::get( $tesserato_id, $anno );

        $row = array_merge( array(
            'tesserato_id'     => $tesserato_id,
            'anno'             => $anno,
            'importo'          => 0.00,
            'stato_pagamento'  => 'non_pagato',
            'metodo_pagamento' => '',
            'data_pagamento'   => null,
            'ricevuta_numero'  => '',
            'note'             => '',
        ), $dati, array(
            'tesserato_id' => $tesserato_id,
            'anno'         => $anno,
        ) );

        if ( ! isset($row['data_pagamento']) || ! $row['data_pagamento'] ) {
            $row['data_pagamento'] = date('Y-m-d');
        }

        return BLT_Database::save_quota( $row, $esistente ? (int) $esistente->id : 0 );
    }

    /**
     * Segna una quota come pagata.
     *
     * @param int    $tesserato_id
     * @param float  $importo
     * @param string $metodo        es. 'stripe', 'paypal', 'contante'
     * @param string $ricevuta      Numero ricevuta o transaction ID
     * @param int    $anno          Default: anno corrente
     * @return int ID della quota
     */
    public static function segna_pagata( int $tesserato_id, float $importo, string $metodo = '', string $ricevuta = '', int $anno = 0 ): int {
        if ( ! $anno ) $anno = (int) date('Y');
        return self::registra_pagamento( $tesserato_id, array(
            'anno'             => $anno,
            'importo'          => $importo,
            'stato_pagamento'  => 'pagato',
            'metodo_pagamento' => $metodo,
            'data_pagamento'   => date('Y-m-d'),
            'ricevuta_numero'  => $ricevuta,
        ) );
    }

    /**
     * Segna una quota come "in attesa di conferma".
     *
     * @param int   $tesserato_id
     * @param float $importo
     * @param int   $anno
     * @return int
     */
    public static function segna_in_attesa( int $tesserato_id, float $importo, int $anno = 0 ): int {
        if ( ! $anno ) $anno = (int) date('Y');
        return self::registra_pagamento( $tesserato_id, array(
            'anno'            => $anno,
            'importo'         => $importo,
            'stato_pagamento' => 'in_attesa',
        ) );
    }

    // ── Statistiche ───────────────────────────────────────────────────────────

    /**
     * Restituisce il totale incassato per un anno.
     *
     * @param int $anno Default: anno corrente.
     * @return float
     */
    public static function totale_incassato( int $anno = 0 ): float {
        global $wpdb;
        if ( ! $anno ) $anno = (int) date('Y');
        return (float) $wpdb->get_var( $wpdb->prepare(
            "SELECT SUM(importo) FROM {$wpdb->prefix}blt_quote
             WHERE anno = %d AND stato_pagamento = 'pagato'",
            $anno
        ) );
    }

    /**
     * Restituisce il numero di quote pagate per un anno.
     *
     * @param int $anno
     * @return int
     */
    public static function count_pagate( int $anno = 0 ): int {
        global $wpdb;
        if ( ! $anno ) $anno = (int) date('Y');
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}blt_quote
             WHERE anno = %d AND stato_pagamento = 'pagato'",
            $anno
        ) );
    }

    /**
     * Restituisce i tesserati che non hanno ancora pagato la quota dell'anno corrente.
     *
     * @param int $anno Default: anno corrente.
     * @return object[]
     */
    public static function get_non_paganti( int $anno = 0 ): array {
        global $wpdb;
        if ( ! $anno ) $anno = (int) date('Y');
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT t.*
             FROM {$wpdb->prefix}blt_tesserati t
             LEFT JOIN {$wpdb->prefix}blt_quote q
                 ON q.tesserato_id = t.id AND q.anno = %d AND q.stato_pagamento = 'pagato'
             WHERE t.stato = 'attivo' AND q.id IS NULL
             ORDER BY t.cognome ASC",
            $anno
        ) ) ?: array();
    }

    /**
     * Genera il prossimo numero di ricevuta progressivo per l'anno.
     *
     * @param int $anno
     * @return string Es. RIC-2024-0042
     */
    public static function genera_numero_ricevuta( int $anno = 0 ): string {
        global $wpdb;
        if ( ! $anno ) $anno = (int) date('Y');
        $last = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}blt_quote
             WHERE anno = %d AND stato_pagamento = 'pagato'",
            $anno
        ) );
        return 'RIC-' . $anno . '-' . str_pad( $last + 1, 4, '0', STR_PAD_LEFT );
    }

    // ── Import / Batch ────────────────────────────────────────────────────────

    /**
     * Crea automaticamente le quote mancanti per tutti i tesserati attivi.
     * Utile all'inizio dell'anno per pre-popolare le quote in stato 'non_pagato'.
     *
     * @param int   $anno
     * @param float $importo_default Importo da assegnare (0 = usa quota tipo dal setting).
     * @return int  Numero di quote create.
     */
    public static function genera_quote_annuali( int $anno = 0, float $importo_default = 0 ): int {
        global $wpdb;
        if ( ! $anno ) $anno = (int) date('Y');

        $tesserati = $wpdb->get_results(
            "SELECT id, tipo_tessera FROM {$wpdb->prefix}blt_tesserati WHERE stato = 'attivo'"
        );
        $creati = 0;
        foreach ( $tesserati as $t ) {
            $esistente = self::get( (int) $t->id, $anno );
            if ( $esistente ) continue;

            $importo = $importo_default > 0
                ? $importo_default
                : BLT_Payment::get_importo( $t->tipo_tessera );

            BLT_Database::save_quota( array(
                'tesserato_id'    => (int) $t->id,
                'anno'            => $anno,
                'importo'         => $importo,
                'stato_pagamento' => 'non_pagato',
            ), 0 );
            $creati++;
        }
        return $creati;
    }
}
