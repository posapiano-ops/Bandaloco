<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Genera QR code usando l'API gratuita di QR Server o una libreria locale.
 * Per produzione si consiglia phpqrcode o endroid/qr-code via Composer.
 */
class BLT_QRCode {

    /**
     * Restituisce URL immagine QR code tramite servizio esterno (fallback).
     */
    public static function get_qr_url( $data, $size = 200 ) {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . rawurlencode( $data );
    }

    /**
     * Restituisce l'URL di verifica tessera per il QR code.
     */
    public static function get_verify_url( $qr_token ) {
        return add_query_arg( array(
            'blt_verify' => '1',
            'token'      => $qr_token,
        ), home_url( '/' ) );
    }

    /**
     * Genera HTML della tessera digitale (fronte).
     */
    public static function render_tessera( $tesserato, $echo = true ) {
        $settings    = BLT_Database::get_all_settings();
        $verify_url  = self::get_verify_url( $tesserato->qr_token );
        $qr_img      = self::get_qr_url( $verify_url, 120 );
        $nome_completo = esc_html( $tesserato->nome . ' ' . $tesserato->cognome );
        $numero      = esc_html( $tesserato->numero_tessera );
        $tipo        = esc_html( ucfirst( $tesserato->tipo_tessera ) );
        $scadenza    = $tesserato->data_scadenza ? date_i18n( 'd/m/Y', strtotime( $tesserato->data_scadenza ) ) : '—';
        $proloco     = esc_html( $settings['nome_proloco'] ?? 'Bandaloco' );
        $stato_class = $tesserato->stato === 'attivo' ? 'blt-tessera-attiva' : 'blt-tessera-scaduta';

        $logo_html = '';
        if ( ! empty( $settings['logo_tessera'] ) ) {
            $logo_html = '<img src="' . esc_url( $settings['logo_tessera'] ) . '" alt="Logo" class="blt-tessera-logo">';
        }

        $foto_html = '';
        if ( ! empty( $tesserato->foto ) ) {
            $foto_html = '<img src="' . esc_url( $tesserato->foto ) . '" alt="Foto" class="blt-tessera-foto">';
        } else {
            $initials  = strtoupper( substr( $tesserato->nome, 0, 1 ) . substr( $tesserato->cognome, 0, 1 ) );
            $foto_html = '<div class="blt-tessera-initials">' . esc_html( $initials ) . '</div>';
        }

        $html = '
        <div class="blt-tessera-card ' . $stato_class . '">
            <div class="blt-tessera-header">
                ' . $logo_html . '
                <div class="blt-tessera-org">
                    <span class="blt-tessera-org-name">' . $proloco . '</span>
                    <span class="blt-tessera-tipo">' . $tipo . '</span>
                </div>
            </div>
            <div class="blt-tessera-body">
                <div class="blt-tessera-avatar">' . $foto_html . '</div>
                <div class="blt-tessera-info">
                    <div class="blt-tessera-nome">' . $nome_completo . '</div>
                    <div class="blt-tessera-numero">N° ' . $numero . '</div>
                    <div class="blt-tessera-scadenza">Valida fino al: <strong>' . $scadenza . '</strong></div>
                    <div class="blt-tessera-stato-badge">' . ucfirst( $tesserato->stato ) . '</div>
                </div>
                <div class="blt-tessera-qr">
                    <img src="' . esc_url( $qr_img ) . '" alt="QR Code tessera" width="100" height="100">
                </div>
            </div>
        </div>';

        if ( $echo ) echo $html;
        else return $html;
    }
}
