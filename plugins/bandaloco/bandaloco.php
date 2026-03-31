<?php
/**
 * Plugin Name: Bandaloco
 * Plugin URI:  https://github.com/posapiano-ops/Bandaloco
 * Description: Gestione completa dei tesserati per Pro Loco: anagrafica, quote, tessere digitali con QR code, rinnovi, area riservata, login sociale (Google, Facebook) e pagamento online (Stripe, PayPal).
 * Version:     1.3.1
 * Author:      Posapiano
 * License:     GPL-2.0+
 * Text Domain: bandaloco
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'BLT_VERSION',    '1.3.1' );
define( 'BLT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BLT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BLT_PLUGIN_FILE', __FILE__ );

// ── Autoload ──────────────────────────────────────────────────────────────────
require_once BLT_PLUGIN_DIR . 'includes/class-blt-activator.php';
require_once BLT_PLUGIN_DIR . 'includes/class-blt-database.php';
require_once BLT_PLUGIN_DIR . 'includes/class-blt-tesserato.php';
require_once BLT_PLUGIN_DIR . 'includes/class-blt-quota.php';
require_once BLT_PLUGIN_DIR . 'includes/class-blt-qrcode.php';
require_once BLT_PLUGIN_DIR . 'includes/class-blt-export.php';
require_once BLT_PLUGIN_DIR . 'includes/class-blt-email.php';
require_once BLT_PLUGIN_DIR . 'includes/class-blt-oauth.php';
require_once BLT_PLUGIN_DIR . 'includes/class-blt-payment.php';
require_once BLT_PLUGIN_DIR . 'admin/class-blt-admin.php';
require_once BLT_PLUGIN_DIR . 'public/class-blt-public.php';

// ── Activation / Deactivation ─────────────────────────────────────────────────
register_activation_hook(   __FILE__, array( 'BLT_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'BLT_Activator', 'deactivate' ) );

// ── Bootstrap ─────────────────────────────────────────────────────────────────
function BLT_init() {
    global $wpdb;

    // Controlla esistenza tabella con query diretta su information_schema
    $tabella = $wpdb->prefix . 'blt_tesserati';
    $esiste  = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM information_schema.tables
         WHERE table_schema = %s AND table_name = %s",
        DB_NAME, $tabella
    ) );

    if ( ! $esiste ) {
        BLT_Activator::activate();
    } else {
        BLT_Activator::maybe_upgrade();
    }

    new BLT_Admin();
    new BLT_Public();
    new BLT_OAuth();
    new BLT_Payment();
}
add_action( 'plugins_loaded', 'blt_init' );

// ── Shortcodes ────────────────────────────────────────────────────────────────
add_shortcode( 'proloco_area_tesserato', 'blt_shortcode_area_tesserato' );
add_shortcode( 'proloco_iscrizione',     'blt_shortcode_iscrizione' );
add_shortcode( 'proloco_direttivo',      'blt_shortcode_direttivo' );

function blt_shortcode_area_tesserato( $atts ) {
    ob_start();
    include BLT_PLUGIN_DIR . 'templates/area-tesserato.php';
    return ob_get_clean();
}

function blt_shortcode_iscrizione( $atts ) {
    ob_start();
    include BLT_PLUGIN_DIR . 'templates/form-iscrizione.php';
    return ob_get_clean();
}

function blt_shortcode_direttivo( $atts ) {
    ob_start();
    include BLT_PLUGIN_DIR . 'templates/direttivo.php';
    return ob_get_clean();
}
