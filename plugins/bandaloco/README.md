# Bandaloco – Plugin WordPress

Gestione completa dei tesserati per associazioni Pro Loco.

## Funzionalità

- ✅ **Anagrafica tesserati** — dati personali completi, foto, tipo tessera
- 💳 **Gestione quote** — pagamenti annuali, storico, ricevute, stati (pagato / in attesa / non pagato)
- 🪪 **Tessera digitale con QR code** — ogni tesserato ha una tessera visuale con QR code di verifica
- ⏰ **Rinnovi e scadenze** — avvisi automatici via email, aggiornamento stato automatico
- 🔒 **Area riservata** — i tesserati accedono con login WordPress e vedono la propria tessera
- 📥 **Export CSV e PDF** — esportazione elenco tesserati filtrato
- 📧 **Email automatiche** — benvenuto, avviso scadenza, conferma pagamento

---

## Installazione

1. Copia la cartella `bandaloco` in `/wp-content/plugins/`
2. Attiva il plugin da **Plugins → Plugin installati**
3. Vai su **Bandaloco → Impostazioni** e configura nome, quote e prefisso tessera

---

## Utilizzo

### Pannello di amministrazione

Vai su **Bandaloco** nella barra laterale di WordPress admin:

| Sezione | Cosa puoi fare |
|---|---|
| **Dashboard** | Statistiche, accesso rapido alle funzioni |
| **Tesserati** | Aggiungi, modifica, cerca, filtra, visualizza scheda completa |
| **Quote** | Visualizza e gestisci i pagamenti per anno |
| **Impostazioni** | Nome Pro Loco, quote, prefisso tessera, avvisi |

### Shortcode per il sito

Inserisci questi shortcode nelle pagine che vuoi:

```
[proloco_area_tesserato]
```
Mostra l'area riservata: il tesserato fa login e vede la sua tessera digitale con QR code, stato e quota.

```
[proloco_iscrizione]
```
Modulo di iscrizione per i nuovi tesserati (crea automaticamente utente WordPress e manda email di benvenuto).

```
[proloco_direttivo]
```
Modulo che mostra le figure presenti nel direttivo

### Verifica QR code

Quando si scansiona il QR code della tessera, viene mostrata una pagina di verifica con:
- ✅ Tessera Valida (attivo) o ❌ Tessera Non Valida (scaduto/sospeso)
- Nome tesserato, numero tessera, tipo, scadenza

---

## Struttura file

```
proloco-tessere/
├── proloco-tessere.php          ← File principale plugin
├── includes/
│   ├── class-blt-activator.php  ← Creazione tabelle DB, ruoli, cron
│   ├── class-blt-database.php   ← Tutte le query al database
│   ├── class-blt-qrcode.php     ← Generazione QR e tessera digitale
│   ├── class-blt-export.php     ← Export CSV e PDF
│   └── class-blt-email.php      ← Email automatiche + cron scadenze
├── admin/
│   ├── class-blt-admin.php      ← Menu, pagine, handler form admin
│   └── views/
│       ├── dashboard.php
│       ├── tesserati-list.php
│       ├── tesserato-form.php
│       ├── tesserato-detail.php
│       ├── quote-list.php
│       └── impostazioni.php
├── public/
│   └── class-blt-public.php     ← Frontend shortcodes, QR verify
├── templates/
│   ├── area-tesserato.php       ← Shortcode area riservata
│   ├── form-iscrizione.php      ← Shortcode modulo iscrizione
│   └── verify-tessera.php       ← Pagina verifica QR
└── assets/
    ├── css/admin.css
    ├── css/public.css
    ├── js/admin.js
    └── js/public.js
```

---

## Tabelle database

| Tabella | Contenuto |
|---|---|
| `wp_BLT_tesserati` | Anagrafica completa + numero tessera + QR token |
| `wp_BLT_quote` | Quote annuali con stato pagamento |
| `wp_BLT_impostazioni` | Configurazione plugin chiave-valore |

---

## Personalizzazione

### Tipi di tessera
Modifica i tipi in `admin/views/tesserato-form.php` nell'array `$tipi`.

### Logo sulla tessera
Vai in **Impostazioni** e inserisci l'URL del logo. Viene mostrato nell'intestazione della tessera digitale.

### QR code con libreria locale
Per produzione si consiglia di sostituire il servizio esterno in `class-blt-qrcode.php` con [phpqrcode](https://phpqrcode.sourceforge.net/) o `endroid/qr-code` via Composer per non dipendere da servizi esterni.

---

## Requisiti

- WordPress 5.8+
- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.3+

---

## Note GDPR

Il plugin raccoglie dati personali (nome, cognome, CF, email, ecc.). È responsabilità dell'amministratore:
- Aggiornare la Privacy Policy del sito
- Ottenere il consenso degli iscritti (il form di iscrizione include la checkbox privacy)
- Gestire le richieste di cancellazione dati
