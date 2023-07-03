<?php
/**
 * Charitable Admin Hooks.
 *
 * @package   Charitable/Functions/Admin
 * @author    David Bisset
 * @copyright Copyright (c) 2022, WP Charitable LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.3.0
 * @version   1.6.40
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue Charitable's admin-area scripts & styles.
 *
 * @see Charitable_Admin::admin_enqueue_scripts()
 */
add_action( 'admin_enqueue_scripts', array( Charitable_Admin::get_instance(), 'admin_enqueue_scripts' ) );

/**
 * Set custom admin body classes.
 *
 * @see Charitable_Admin::set_body_class()
 */
add_filter( 'admin_body_class', array( Charitable_Admin::get_instance(), 'set_body_class' ) );

/**
 * Do an admin action.
 *
 * @see Charitable_Admin::maybe_do_admin_action()
 */
add_action( 'admin_init', array( Charitable_Admin::get_instance(), 'maybe_do_admin_action' ), 999 );

/**
 * Check if there are any notices to be displayed in the admin.
 *
 * @see Charitable_Admin::add_notices()
 */
add_action( 'admin_notices', array( Charitable_Admin::get_instance(), 'add_notices' ) );

/**
 * Dismiss a notice.
 *
 * @see Charitable_Admin::dismiss_notice()
 */
add_action( 'wp_ajax_charitable_dismiss_notice', array( Charitable_Admin::get_instance(), 'dismiss_notice' ) );

/**
 * Dismiss a banner.
 *
 * @see Charitable_Admin::dismiss_banner()
 */
add_action( 'wp_ajax_charitable_dismiss_admin_banner', array( Charitable_Admin::get_instance(), 'dismiss_banner' ) );

/**
 * Dismiss a five star rating request.
 *
 * @see Charitable_Admin::dismiss_five_star_rating()
 */
add_action( 'wp_ajax_charitable_dismiss_admin_five_star_rating', array( Charitable_Admin::get_instance(), 'dismiss_five_star_rating' ) );

/**
 * Add a generic body class to donations page
 *
 * @see Charitable_Admin::add_admin_body_class()
 */
add_filter( 'admin_body_class', array( Charitable_Admin::get_instance(), 'add_admin_body_class' ) );

/**
 * Remove jQuery UI styles added by Ninja Forms.
 *
 * @see Charitable_Admin::remove_jquery_ui_styles_nf()
 */
// add_filter( 'media_buttons_context', array( Charitable_Admin::get_instance(), 'remove_jquery_ui_styles_nf' ), 20 );

/**
 * Add action links to the Charitable plugin block.
 *
 * @see Charitable_Admin::add_plugin_action_links()
 */
add_filter( 'plugin_action_links_' . plugin_basename( charitable()->get_path() ), array( Charitable_Admin::get_instance(), 'add_plugin_action_links' ) );

/**
 * Add a link to the settings page from the Charitable plugin block.
 *
 * @see Charitable_Admin::add_plugin_row_meta()
 */
add_filter( 'plugin_row_meta', array( Charitable_Admin::get_instance(), 'add_plugin_row_meta' ), 10, 2 );

/**
 * Export handlers.
 *
 * @see Charitable_Admin::export_donations()
 * @see Charitable_Admin::export_campaigns()
 */
add_action( 'charitable_export_donations', array( Charitable_Admin::get_instance(), 'export_donations' ) );
add_action( 'charitable_export_campaigns', array( Charitable_Admin::get_instance(), 'export_campaigns' ) );

/**
 * Add Charitable menu.
 *
 * @see Charitable_Admin_Pages::add_menu()
 */
add_action( 'admin_menu', array( Charitable_Admin_Pages::get_instance(), 'add_menu' ), 5 );
add_action( 'parent_file', array( Charitable_Admin_Pages::get_instance(), 'menu_highlight' ), 10 );

/**
 * Redirect to welcome page after install.
 *
 * @see Charitable_Admin_Pages::redirect_to_welcome()
 */
add_action( 'charitable_install', array( Charitable_Admin_Pages::get_instance(), 'setup_welcome_redirect' ), 100 );

/**
 * Stash any notices that haven't been displayed.
 *
 * @see Charitable_Admin_Notices::shutdown()
 */
add_action( 'shutdown', array( charitable_get_admin_notices(), 'shutdown' ) );

/**
 * Stash any notices that haven't been displayed.
 *
 * @see Charitable_Admin_Support::maybe_do_charitable_support_query()
 */
add_action( 'admin_init', array( Charitable_Admin_Support::get_instance(), 'maybe_do_charitable_support_query' ), 100 );

