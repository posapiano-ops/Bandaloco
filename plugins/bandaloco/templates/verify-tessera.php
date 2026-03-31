<?php if ( ! defined('ABSPATH') ) exit;
$settings = BLT_Database::get_all_settings();
$proloco  = esc_html($settings['nome_proloco'] ?? 'Bandaloco');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifica Tessera – <?= $proloco ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f4f8; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 8px 40px rgba(0,0,0,.12); max-width: 420px; width: 100%; overflow: hidden; }
        .card-header { padding: 24px; text-align: center; }
        .card-header.valida  { background: linear-gradient(135deg, #276749, #38a169); color: #fff; }
        .card-header.scaduta { background: linear-gradient(135deg, #c53030, #e53e3e); color: #fff; }
        .card-header.non_trovata { background: linear-gradient(135deg, #2d3748, #4a5568); color: #fff; }
        .card-header h1 { font-size: 28px; margin-bottom: 4px; }
        .card-header p  { opacity: .85; font-size: 14px; }
        .card-body { padding: 24px; }
        .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #edf2f7; font-size: 15px; }
        .info-row:last-child { border: none; }
        .info-label { color: #718096; }
        .info-value { font-weight: 600; color: #2d3748; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .badge-attivo  { background: #c6f6d5; color: #276749; }
        .badge-scaduto { background: #fed7d7; color: #c53030; }
        .badge-sospeso { background: #fefcbf; color: #975a16; }
        .badge-nuovo   { background: #bee3f8; color: #2b6cb0; }
        .footer { text-align: center; padding: 16px; font-size: 12px; color: #a0aec0; border-top: 1px solid #edf2f7; }
        .icon-big { font-size: 48px; margin-bottom: 8px; display: block; }
    </style>
</head>
<body>
<?php if ( ! $tesserato ) : ?>
    <div class="card">
        <div class="card-header non_trovata">
            <span class="icon-big">❓</span>
            <h1>Tessera non trovata</h1>
            <p>Il codice QR non è valido o la tessera non esiste.</p>
        </div>
        <div class="footer"><?= $proloco ?></div>
    </div>
<?php else:
    $valida = $tesserato->stato === 'attivo';
    $classe = $valida ? 'valida' : 'scaduta';
    $icon   = $valida ? '✅' : '❌';
    $titolo = $valida ? 'Tessera Valida' : 'Tessera Non Valida';
    $scad   = $tesserato->data_scadenza ? date_i18n('d/m/Y', strtotime($tesserato->data_scadenza)) : '—';
?>
    <div class="card">
        <div class="card-header <?= $classe ?>">
            <span class="icon-big"><?= $icon ?></span>
            <h1><?= $titolo ?></h1>
            <p><?= $proloco ?></p>
        </div>
        <div class="card-body">
            <div class="info-row">
                <span class="info-label">Nome</span>
                <span class="info-value"><?= esc_html($tesserato->nome . ' ' . $tesserato->cognome) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">N° Tessera</span>
                <span class="info-value"><?= esc_html($tesserato->numero_tessera) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Tipo</span>
                <span class="info-value"><?= esc_html(ucfirst($tesserato->tipo_tessera)) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Stato</span>
                <span class="info-value"><span class="badge badge-<?= $tesserato->stato ?>"><?= ucfirst($tesserato->stato) ?></span></span>
            </div>
            <div class="info-row">
                <span class="info-label">Valida fino al</span>
                <span class="info-value"><?= $scad ?></span>
            </div>
        </div>
        <div class="footer">
            Verifica effettuata il <?= date_i18n('d/m/Y H:i') ?> &mdash; <?= $proloco ?>
        </div>
    </div>
<?php endif; ?>
</body>
</html>
