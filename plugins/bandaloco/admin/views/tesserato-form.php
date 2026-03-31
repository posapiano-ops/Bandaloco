<?php if ( ! defined('ABSPATH') ) exit;
$is_new = ! $tesserato;
$title  = $is_new ? 'Nuovo Tesserato' : 'Modifica Tesserato';
$val = function($k, $default='') use ($tesserato) {
    return $tesserato ? esc_attr($tesserato->$k ?? $default) : $default;
};

// Ruoli direttivo predefiniti
$ruoli_direttivo = [
    ''                    => '— Seleziona ruolo —',
    'Presidente'          => 'Presidente',
    'Vicepresidente'      => 'Vicepresidente',
    'Segretario'          => 'Segretario',
    'Tesoriere'           => 'Tesoriere',
    'Consigliere'         => 'Consigliere',
    'Revisore dei Conti'  => 'Revisore dei Conti',
    'Responsabile Eventi' => 'Responsabile Eventi',
    'Responsabile Social' => 'Responsabile Social',
    'Altro'               => 'Altro (specificare)',
];
?>
<div class="wrap blt-wrap">
    <h1 class="blt-page-title">
        <span class="dashicons dashicons-id-alt"></span>
        <?= $title ?>
        <?php if ( $tesserato && $tesserato->nel_direttivo ) : ?>
            <span class="blt-badge-direttivo-header">🏛️ Direttivo</span>
        <?php endif; ?>
    </h1>
    <a href="<?= admin_url('admin.php?page=blt-tesserati') ?>" class="blt-back-link">← Torna all'elenco</a>

    <?php if ( ! empty($_GET['blt_error']) ) : ?>
        <div class="notice notice-error is-dismissible">
            <p>❌ <?= esc_html( urldecode($_GET['blt_error']) ) ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= admin_url('admin-post.php') ?>" class="blt-form">
        <?php wp_nonce_field('blt_save_tesserato'); ?>
        <input type="hidden" name="action" value="BLT_save_tesserato">
        <input type="hidden" name="tesserato_id" value="<?= $val('id', '0') ?>">

        <div class="blt-form-grid">

            <!-- Dati anagrafici -->
            <div class="blt-form-section">
                <h2>📋 Dati Anagrafici</h2>
                <div class="blt-form-row-2">
                    <label>Cognome *<input type="text" name="cognome" value="<?= $val('cognome') ?>" required class="regular-text"></label>
                    <label>Nome *<input type="text" name="nome" value="<?= $val('nome') ?>" required class="regular-text"></label>
                </div>
                <div class="blt-form-row-2">
                    <label>Codice Fiscale<input type="text" name="codice_fiscale" value="<?= $val('codice_fiscale') ?>" maxlength="16" style="text-transform:uppercase" class="regular-text"></label>
                    <label>Data di Nascita<input type="date" name="data_nascita" value="<?= $val('data_nascita') ?>"></label>
                </div>
                <label>Luogo di Nascita<input type="text" name="luogo_nascita" value="<?= $val('luogo_nascita') ?>" class="regular-text"></label>
            </div>

            <!-- Contatti -->
            <div class="blt-form-section">
                <h2>📞 Contatti</h2>
                <label>Email<input type="email" name="email" value="<?= $val('email') ?>" class="regular-text"></label>
                <label>Telefono<input type="tel" name="telefono" value="<?= $val('telefono') ?>" class="regular-text"></label>
                <label>Indirizzo<input type="text" name="indirizzo" value="<?= $val('indirizzo') ?>" class="regular-text"></label>
                <div class="blt-form-row-3">
                    <label>CAP<input type="text" name="cap" value="<?= $val('cap') ?>" maxlength="10"></label>
                    <label>Città<input type="text" name="citta" value="<?= $val('citta') ?>" class="regular-text"></label>
                    <label>Prov.<input type="text" name="provincia" value="<?= $val('provincia') ?>" maxlength="5" style="text-transform:uppercase"></label>
                </div>
            </div>

            <!-- Tessera -->
            <div class="blt-form-section">
                <h2>🪪 Tipo Tessera</h2>
                <div class="blt-form-row-2">
                    <label>Tipo Tessera
                        <select name="tipo_tessera">
                            <?php
                            $tipi = ['ordinario'=>'Ordinario','sostenitore'=>'Sostenitore','familiare'=>'Familiare','onorario'=>'Onorario','junior'=>'Junior (under 18)'];
                            foreach ($tipi as $v=>$l):
                            ?>
                            <option value="<?= $v ?>" <?= selected($val('tipo_tessera','ordinario'),$v,false) ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Stato
                        <select name="stato">
                            <?php foreach(['nuovo'=>'Nuovo','attivo'=>'Attivo','scaduto'=>'Scaduto','sospeso'=>'Sospeso'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= selected($val('stato','nuovo'),$v,false) ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div class="blt-form-row-2">
                    <label>Data Iscrizione *
                        <input type="date" name="data_iscrizione" value="<?= $val('data_iscrizione', date('Y-m-d')) ?>" required>
                    </label>
                    <label>Data Scadenza *
                        <input type="date" name="data_scadenza" value="<?= $val('data_scadenza', date('Y-12-31')) ?>" required>
                    </label>
                </div>
            </div>

            <!-- Direttivo -->
            <div class="blt-form-section blt-direttivo-section">
                <h2>🏛️ Direttivo Pro Loco</h2>

                <label class="blt-toggle-row">
                    <input type="checkbox" name="nel_direttivo" value="1"
                        id="chk_direttivo"
                        <?= checked( $val('nel_direttivo','0'), '1', false ) ?>
                        onchange="document.getElementById('direttivo-fields').style.display=this.checked?'block':'none'">
                    <strong>Fa parte del Direttivo</strong>
                </label>

                <div id="direttivo-fields" style="display:<?= $val('nel_direttivo') ? 'block' : 'none' ?>; margin-top:14px;">

                    <div class="blt-form-row-2">
                        <label>Ruolo nel Direttivo
                            <select name="ruolo_direttivo">
                                <?php foreach ( $ruoli_direttivo as $v => $l ) : ?>
                                <option value="<?= esc_attr($v) ?>" <?= selected($val('ruolo_direttivo'), $v, false) ?>><?= esc_html($l) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Ordine visualizzazione
                            <input type="number" name="ordine_direttivo" value="<?= $val('ordine_direttivo','0') ?>" min="0" max="999" class="small-text">
                            <p class="description">0 = automatico per cognome</p>
                        </label>
                    </div>

                    <label>Foto (URL)
                        <input type="url" name="foto" value="<?= $val('foto') ?>" class="large-text" placeholder="https://…/foto.jpg">
                        <p class="description">URL immagine mostrata nella scheda pubblica del direttivo</p>
                    </label>

                    <label>Breve biografia / presentazione
                        <textarea name="bio_direttivo" rows="3" class="large-text" placeholder="Breve testo di presentazione mostrato sul sito..."><?= $tesserato ? esc_textarea($tesserato->bio_direttivo ?? '') : '' ?></textarea>
                    </label>

                    <label class="blt-toggle-row blt-toggle-pubblica">
                        <input type="checkbox" name="pubblica_direttivo" value="1"
                            <?= checked( $val('pubblica_direttivo','0'), '1', false ) ?>>
                        <strong>Pubblica sul sito</strong>
                        <span class="blt-toggle-desc">— Questo membro apparirà nella pagina pubblica del direttivo (<code>[proloco_direttivo]</code>)</span>
                    </label>

                </div><!-- /#direttivo-fields -->
            </div>

            <!-- Note -->
            <div class="blt-form-section">
                <h2>📝 Note Interne</h2>
                <label>Note
                    <textarea name="note" rows="4" class="large-text"><?= $tesserato ? esc_textarea($tesserato->note) : '' ?></textarea>
                </label>
            </div>

        </div><!-- /.blt-form-grid -->

        <div class="blt-form-submit">
            <button type="submit" class="button button-primary button-large">
                <?= $is_new ? '✅ Crea Tesserato' : '💾 Salva Modifiche' ?>
            </button>
            <a href="<?= admin_url('admin.php?page=blt-tesserati') ?>" class="button button-large">Annulla</a>
        </div>
    </form>
</div>
