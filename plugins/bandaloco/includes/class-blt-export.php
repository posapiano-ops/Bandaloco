<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BLT_Export {

    /**
     * Esporta tesserati in CSV e invia al browser.
     */
    public static function export_csv( $args = array() ) {
        $tesserati = BLT_Database::get_tesserati( array_merge( $args, array( 'per_page' => 9999 ) ) );

        $filename = 'tesserati-' . date('Y-m-d') . '.csv';
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Pragma: no-cache' );

        $output = fopen( 'php://output', 'w' );
        fprintf( $output, chr(0xEF) . chr(0xBB) . chr(0xBF) ); // BOM UTF-8

        fputcsv( $output, array(
            'N° Tessera','Cognome','Nome','Codice Fiscale','Data Nascita',
            'Email','Telefono','Indirizzo','CAP','Città','Provincia',
            'Tipo Tessera','Stato','Data Iscrizione','Data Scadenza','Note'
        ), ';' );

        foreach ( $tesserati as $t ) {
            fputcsv( $output, array(
                $t->numero_tessera,
                $t->cognome,
                $t->nome,
                $t->codice_fiscale,
                $t->data_nascita,
                $t->email,
                $t->telefono,
                $t->indirizzo,
                $t->cap,
                $t->citta,
                $t->provincia,
                $t->tipo_tessera,
                $t->stato,
                $t->data_iscrizione,
                $t->data_scadenza,
                $t->note,
            ), ';' );
        }

        fclose( $output );
        exit;
    }

    /**
     * Esporta tesserati in PDF usando HTML + CSS (stampa).
     * Per PDF vero usare mPDF o TCPDF.
     */
    public static function export_pdf( $args = array() ) {
        $tesserati = BLT_Database::get_tesserati( array_merge( $args, array( 'per_page' => 9999 ) ) );
        $settings  = BLT_Database::get_all_settings();
        $proloco   = esc_html( $settings['nome_proloco'] ?? 'Bandaloco' );

        $filename = 'tesserati-' . date('Y-m-d') . '.html';
        header( 'Content-Type: text/html; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

        echo '<!DOCTYPE html><html lang="it"><head><meta charset="UTF-8">
        <title>Elenco Tesserati - ' . $proloco . '</title>
        <style>
            body { font-family: Arial, sans-serif; font-size: 11px; }
            h1   { font-size: 16px; text-align: center; margin-bottom: 4px; }
            p.sub{ text-align:center; color:#555; margin:0 0 12px; }
            table{ width:100%; border-collapse:collapse; }
            th   { background:#2c5282; color:#fff; padding:5px 4px; text-align:left; }
            td   { padding:4px; border-bottom:1px solid #ddd; }
            tr:nth-child(even){ background:#f7f7f7; }
            .badge-attivo   { color:#276749; font-weight:bold; }
            .badge-scaduto  { color:#c53030; }
            .badge-sospeso  { color:#975a16; }
            @media print { @page { size: A4 landscape; margin: 10mm; } }
        </style></head><body>';

        echo '<h1>' . $proloco . ' – Elenco Tesserati</h1>';
        echo '<p class="sub">Generato il ' . date_i18n('d/m/Y H:i') . ' &mdash; Totale: ' . count($tesserati) . ' tesserati</p>';
        echo '<table><thead><tr>
            <th>N° Tessera</th><th>Cognome e Nome</th><th>Email</th>
            <th>Telefono</th><th>Tipo</th><th>Stato</th><th>Scadenza</th>
        </tr></thead><tbody>';

        foreach ( $tesserati as $t ) {
            $badge = 'badge-' . esc_attr($t->stato);
            echo '<tr>
                <td>' . esc_html($t->numero_tessera) . '</td>
                <td>' . esc_html($t->cognome . ' ' . $t->nome) . '</td>
                <td>' . esc_html($t->email) . '</td>
                <td>' . esc_html($t->telefono) . '</td>
                <td>' . esc_html(ucfirst($t->tipo_tessera)) . '</td>
                <td class="' . $badge . '">' . esc_html(ucfirst($t->stato)) . '</td>
                <td>' . ($t->data_scadenza ? date_i18n('d/m/Y', strtotime($t->data_scadenza)) : '—') . '</td>
            </tr>';
        }

        echo '</tbody></table><script>window.onload=function(){window.print();}</script></body></html>';
        exit;
    }
}
