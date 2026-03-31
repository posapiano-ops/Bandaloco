<?php
/**
 * BLT_Tesserato – Classe wrapper per la gestione dei tesserati.
 *
 * Fornisce metodi statici semantici che delegano a BLT_Database,
 * e può essere estesa per logica di dominio specifica.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BLT_Tesserato {

    // ── Lettura ───────────────────────────────────────────────────────────────

    /**
     * Restituisce un array di tesserati con filtri e paginazione.
     *
     * @param array $args {
     *   @type string $stato    Filtra per stato: attivo|scaduto|sospeso|nuovo
     *   @type string $search   Ricerca libera su nome/cognome/email/numero_tessera
     *   @type string $orderby  Campo di ordinamento (default: cognome)
     *   @type string $order    ASC|DESC
     *   @type int    $per_page Risultati per pagina (default: 20)
     *   @type int    $paged    Pagina corrente (default: 1)
     * }
     * @return object[]
     */
    public static function get_list( array $args = array() ): array {
        return BLT_Database::get_tesserati( $args );
    }

    /**
     * Conta i tesserati con gli stessi filtri di get_list().
     */
    public static function count( array $args = array() ): int {
        return BLT_Database::count_tesserati( $args );
    }

    /**
     * Restituisce un singolo tesserato per ID.
     *
     * @param int $id
     * @return object|null
     */
    public static function get( int $id ): ?object {
        return BLT_Database::get_tesserato( $id ) ?: null;
    }

    /**
     * Restituisce il tesserato associato a un utente WordPress.
     *
     * @param int $user_id
     * @return object|null
     */
    public static function get_by_user( int $user_id ): ?object {
        return BLT_Database::get_tesserato_by_user( $user_id ) ?: null;
    }

    /**
     * Restituisce il tesserato associato a un QR token.
     *
     * @param string $token
     * @return object|null
     */
    public static function get_by_token( string $token ): ?object {
        return BLT_Database::get_tesserato_by_token( $token ) ?: null;
    }

    // ── Scrittura ─────────────────────────────────────────────────────────────

    /**
     * Crea o aggiorna un tesserato.
     * Se $id è 0 o omesso, crea un nuovo record.
     *
     * @param array $data  Campi del tesserato.
     * @param int   $id    ID del record da aggiornare (0 = nuovo).
     * @return int         ID del tesserato creato o aggiornato.
     */
    public static function save( array $data, int $id = 0 ): int {
        return BLT_Database::save_tesserato( $data, $id );
    }

    /**
     * Elimina un tesserato e le sue quote (CASCADE).
     *
     * @param int $id
     * @return bool
     */
    public static function delete( int $id ): bool {
        return (bool) BLT_Database::delete_tesserato( $id );
    }

    // ── Stato ─────────────────────────────────────────────────────────────────

    /**
     * Attiva la tessera impostando stato = 'attivo' e aggiorna la scadenza.
     *
     * @param int    $id           ID del tesserato.
     * @param string $data_scadenza Data di scadenza (Y-m-d). Default: 31/12 anno corrente.
     * @return bool
     */
    public static function attiva( int $id, string $data_scadenza = '' ): bool {
        global $wpdb;
        if ( ! $data_scadenza ) $data_scadenza = date('Y') . '-12-31';
        return (bool) $wpdb->update(
            $wpdb->prefix . 'blt_tesserati',
            array( 'stato' => 'attivo', 'data_scadenza' => $data_scadenza ),
            array( 'id' => $id )
        );
    }

    /**
     * Sospende la tessera impostando stato = 'sospeso'.
     *
     * @param int $id
     * @return bool
     */
    public static function sospendi( int $id ): bool {
        global $wpdb;
        return (bool) $wpdb->update(
            $wpdb->prefix . 'blt_tesserati',
            array( 'stato' => 'sospeso' ),
            array( 'id' => $id )
        );
    }

    /**
     * Rinnova la tessera: aggiorna scadenza e stato, registra la quota.
     *
     * @param int   $id      ID del tesserato.
     * @param float $importo Importo della quota.
     * @param string $metodo Metodo di pagamento (es. 'stripe', 'paypal', 'contante').
     * @return bool
     */
    public static function rinnova( int $id, float $importo, string $metodo = '' ): bool {
        $attivato = self::attiva( $id );
        if ( ! $attivato ) return false;

        BLT_Quota::registra_pagamento( $id, array(
            'anno'             => (int) date('Y'),
            'importo'          => $importo,
            'stato_pagamento'  => 'pagato',
            'metodo_pagamento' => $metodo,
            'data_pagamento'   => date('Y-m-d'),
        ) );

        return true;
    }

    // ── Utility ───────────────────────────────────────────────────────────────

    /**
     * Restituisce i tesserati in scadenza entro N giorni.
     *
     * @param int $giorni
     * @return object[]
     */
    public static function get_in_scadenza( int $giorni = 30 ): array {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}blt_tesserati
             WHERE stato = 'attivo'
             AND data_scadenza BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL %d DAY)
             ORDER BY data_scadenza ASC",
            $giorni
        ) ) ?: array();
    }

    /**
     * Restituisce i tesserati scaduti (scadenza passata e stato ancora 'attivo').
     *
     * @return object[]
     */
    public static function get_scaduti_da_aggiornare(): array {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}blt_tesserati
             WHERE stato = 'attivo' AND data_scadenza < CURDATE()"
        ) ?: array();
    }

    /**
     * Aggiorna in blocco lo stato dei tesserati scaduti.
     *
     * @return int Numero di record aggiornati.
     */
    public static function segna_scaduti(): int {
        global $wpdb;
        return (int) $wpdb->query(
            "UPDATE {$wpdb->prefix}blt_tesserati
             SET stato = 'scaduto'
             WHERE stato = 'attivo' AND data_scadenza < CURDATE()"
        );
    }

    /**
     * Genera il prossimo numero di tessera progressivo.
     *
     * @return string Es. PL20240042
     */
    public static function genera_numero(): string {
        return BLT_Database::genera_numero_tessera();
    }

    /**
     * Collega un utente WordPress a un tesserato.
     *
     * @param int $tesserato_id
     * @param int $user_id
     * @return bool
     */
    public static function collega_utente( int $tesserato_id, int $user_id ): bool {
        global $wpdb;
        return (bool) $wpdb->update(
            $wpdb->prefix . 'blt_tesserati',
            array( 'user_id' => $user_id ),
            array( 'id' => $tesserato_id )
        );
    }
}
