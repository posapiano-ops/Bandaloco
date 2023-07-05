<?php
/**
 * Charitable Core Admin Functions
 *
 * General core functions available only within the admin area.
 *
 * @package 	Charitable/Functions/Admin
 * @version     1.0.0
 * @author 		David Bisset
 * @copyright 	Copyright (c) 2022, WP Charitable LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load a view from the admin/views folder.
 *
 * If the view is not found, an Exception will be thrown.
 *
 * Example usage: charitable_admin_view('metaboxes/campaign-title');
 *
 * @since  1.0.0
 *
 * @param  string $view      The view to display.
 * @param  array  $view_args Optional. Arguments to pass through to the view itself.
 * @return boolean True if the view exists and was rendered. False otherwise.
 */
function charitable_admin_view( $view, $view_args = array(), $return_html = false ) {
	$base_path = array_key_exists( 'base_path', $view_args ) ? $view_args['base_path'] : charitable()->get_path( 'admin' ) . 'views/';

	/**
	 * Filter the path to the view.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path      The default path.
	 * @param string $view      The view.
	 * @param array  $view_args View args.
	 */
	$filename = apply_filters( 'charitable_admin_view_path', $base_path . $view . '.php', $view, $view_args );

	if ( ! is_readable( $filename ) ) {
		charitable_get_deprecated()->doing_it_wrong(
			__FUNCTION__,
			sprintf(
				/* translators: %s: Filename of passed view */
				__( 'Passed view (%s) not found or is not readable.', 'charitable' ),
				$filename
			),
			'1.0.0'
		);

		return false;
	}

	ob_start();

	include( $filename );

	if ( $return_html ) {
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	ob_end_flush();

	return true;
}

/**
 * Returns the Charitable_Settings helper.
 *
 * @since  1.0.0
 *
 * @return Charitable_Settings
 */
function charitable_get_admin_settings() {
	return Charitable_Settings::get_instance();
}

/**
 * Returns the Charitable_Admin_Notices helper.
 *
 * @since  1.4.6
 *
 * @return Charitable_Admin_Notices
 */
function charitable_get_admin_notices() {
	return charitable()->registry()->get( 'admin_notices' );
}

/**
 * Returns whether we are currently viewing the Charitable settings area.
 *
 * @since  1.2.0
 *
 * @param  string $tab Optional. If passed, the function will also check that we are on the given tab.
 * @return boolean
 */
function charitable_is_settings_view( $tab = '' ) {
	if ( ! empty( $_POST ) ) {
		$is_settings = array_key_exists( 'option_page', $_POST ) && 'charitable_settings' === $_POST['option_page'];

		if ( ! $is_settings || empty( $tab ) ) {
			return $is_settings;
		}

		return array_key_exists( 'charitable_settings', $_POST ) && array_key_exists( $tab, $_POST['charitable_settings'] );
	}

	$is_settings = isset( $_GET['page'] ) && 'charitable-settings' == $_GET['page'];

	if ( ! $is_settings || empty( $tab ) ) {
		return $is_settings;
	}

	/* The general tab can be loaded when tab is not set. */
	if ( 'general' == $tab ) {
		return ! isset( $_GET['tab'] ) || 'general' == $_GET['tab'];
	}

	return isset( $_GET['tab'] ) && $tab == $_GET['tab'];
}

/**
 * Print out the settings fields for a particular settings section.
 *
 * This is based on WordPress' do_settings_fields but allows the possibility
 * of leaving out a field lable/title, for fullwidth fields.
 *
 * @see    do_settings_fields
 *
 * @since  1.0.0
 *
 * @global $wp_settings_fields Storage array of settings fields and their pages/sections
 *
 * @param  string  $page       Slug title of the admin page who's settings fields you want to show.
 * @param  string  $section    Slug title of the settings section who's fields you want to show.
 * @return string
 */
function charitable_do_settings_fields( $page, $section ) {
	global $wp_settings_fields;

	if ( ! isset( $wp_settings_fields[ $page ][ $section ] ) ) {
		return;
	}

	foreach ( (array) $wp_settings_fields[ $page ][ $section ] as $field ) {
		$class = '';

		if ( ! empty( $field['args']['class'] ) ) {
			$class = ' class="' . esc_attr( $field['args']['class'] ) . '"';
		}

		echo "<tr{$class}>";

		if ( ! empty( $field['args']['label_for'] ) ) {
			echo '<th scope="row"><label for="' . esc_attr( $field['args']['label_for'] ) . '">' . $field['title'] . '</label></th>';
			echo '<td>';
			call_user_func( $field['callback'], $field['args'] );
			echo '</td>';
		} elseif ( ! empty( $field['title'] ) ) {
			if ( $field['args']['type'] === 'heading' && isset( $field['args']['help'] ) ) { // if this is a heading display a "help" as a subheading
				echo '<th scope="row" colspan="2"><h4>' . $field['title'] . '</h4>';
				echo '<p>' . $field['args']['help'] . '</p>';
			} else {
				echo '<th scope="row"><h4>' . $field['title'] . '</h4>';
			}
			echo '</th>';
			if ( $field['args']['type'] !== 'heading' ) {
				echo '<td>';
				call_user_func( $field['callback'], $field['args'] );
				echo '</td>';
			}
		} else {
			echo '<td colspan="2" class="charitable-fullwidth">';
			call_user_func( $field['callback'], $field['args'] );
			echo '</td>';
		}

		echo '</tr>';
	}
}

/**
 * Add new tab to the Charitable settings area.
 *
 * @since  1.3.0
 *
 * @param  string[] $tabs
 * @param  string $key
 * @param  string $name
 * @param  mixed[] $args
 * @return string[]
 */
function charitable_add_settings_tab( $tabs, $key, $name, $args = array() ) {
	$defaults = array(
		'index' => 3,
	);

	$args   = wp_parse_args( $args, $defaults );
	$keys   = array_keys( $tabs );
	$values = array_values( $tabs );

	array_splice( $keys, $args['index'], 0, $key );
	array_splice( $values, $args['index'], 0, $name );

	return array_combine( $keys, $values );
}

/**
 * Return the donation actions class.
 *
 * @since  1.5.0
 *
 * @return Charitable_Donation_Admin_Actions
 */
function charitable_get_donation_actions() {
    return Charitable_Admin::get_instance()->get_donation_actions();
}


/**
 * Adds the "Upgrade to Pro" menu item to the very end of the submenu.
 *
 * @since 1.7.0
 */
function charitable_add_upgrade_item() {
	global $submenu;

	if ( charitable_is_pro() ) {
		return;
	}

	$submenu['charitable'][99] = array(
		__( 'Upgrade to Pro', 'chariable' ),
		'manage_options',
		charitable_ga_url(
			'https://wpcharitable.com/lite-vs-pro/',
			urlencode( 'Admin Menu Link'),
			urlencode( 'Upgrade to Pro' )
		)
	);
}
add_action('admin_menu', 'charitable_add_upgrade_item');


/**
 * Outputs "please rate" text.
 *
 * @since 1.7.0
 *
 * @param string $footer_text Footer text.
 * @return string
 */
function charitable_add_footer_text( $footer_text ) {
	if ( ! charitable_is_admin_screen() ) {
		return $footer_text;
	}

	return sprintf(
		/* translators: %1$s Opening strong tag, do not translate. %2$s Closing strong tag, do not translate. %3$s Opening anchor tag, do not translate. %4$s Closing anchor tag, do not translate. */
		__( 'Please rate %1$sWP Charitable%2$s %3$s★★★★★%4$s on %3$sWordPress.org%4$s to help us spread the word. Thank you from the WP Charitable team!', 'stripe' ),
		'<strong>',
		'</strong>',
		'<a href="https://wordpress.org/support/plugin/charitable/reviews/?filter=5#new-post" rel="noopener noreferrer" target="_blank">',
		'</a>'
	);
}
add_filter( 'admin_footer_text', 'charitable_add_footer_text' );

/**
 * Check if a screen is a plugin admin view.
 * Returns the screen id if true, false (bool) if not.
 *
 * @since 1.7.0
 *
 * @return string|bool
 */
function charitable_is_admin_screen() {
	$screen = \get_current_screen();

	if (
		'charitable' === $screen->post_type ||
		'campaign'   === $screen->post_type ||
		'charitable' === $screen->parent_file
	) {
		return 'charitable';
	}

	if ( isset( $_GET['page'] ) ) {
		if ( 'charitable' == $_GET['page'] ) {
			return 'charitable';
		}

		// if ( 'charitable_settings' == $_GET['page'] ) {
		// 	return 'charitable_settings';
		// }

		// if ( 'charitable_system_status' == $_GET['page'] ) {
		// 	return 'charitable_system_status';
		// }
	}

	return false;
}

/**
 * Appends UTM parameters to a given URL.
 *
 * @since 1.7.0
 *
 * @param string $base_url Base URL.
 * @param string $utm_medium utm_medium parameter.
 * @param string $utm_content Optional. utm_content parameter.
 * @return string $url Full Google Analytics campaign URL.
 */
function charitable_ga_url( $base_url, $utm_medium, $utm_content = false ) {
	/**
	 * Filters the UTM campaign for generated links.
	 *
	 * @since 3.0.0
	 *
	 * @param string $utm_campaign
	 */
	$utm_campaign = apply_filters( 'charitable_utm_campaign', 'WP+Charitable' );

	$args =  array(
		'utm_source'   => 'WordPress',
		'utm_campaign' => $utm_campaign,
		'utm_medium'   => $utm_medium,
	);

	if ( ! empty( $utm_content ) ) {
		$args['utm_content'] = $utm_content;
	}

	return esc_url( add_query_arg( $args, $base_url ) );
}

/**
 * URL for upgrading to Pro (or another Pro licecnse).
 *
 * @since 1.7.0
 *
 * @param string $utm_medium utm_medium parameter.
 * @param string $utm_content Optional. utm_content parameter.
 * @return string
 */
function charitable_pro_upgrade_url( $utm_medium, $utm_content = '' ) {
	return apply_filters(
		'charitable_upgrade_link',
		charitable_ga_url(
			'https://wpcharitable.com/lite-vs-pro/',
			urlencode( $utm_medium ),
			urlencode( $utm_content )
		),
		$utm_medium,
		$utm_content
	);
}

/**
 * Get the current installation license type (always lowercase).
 *
 * @since 1.7.0.3
 *
 * @return string|false
 */
function charitable_get_license_type() {

	$type = charitable_setting( 'type', '', 'charitable_license' );

	if ( empty( $type ) || ! charitable()->is_pro() ) {
		return false;
	}

	return strtolower( $type );
}

/**
 * Get when WPCharitable was first installed.
 *
 * @since 1.6.0
 *
 * @param string $type Specific install type to check for.
 *
 * @return int|false Unix timestamp. False on failure.
 */
function charitable_get_activated_timestamp( $type = '' ) {

	$activated = (array) get_option( 'charitable_activated', [] );

	if ( empty( $activated ) ) {
		return false;
	}

	// When a passed install type is empty, then get it from a DB.
	// If it is installed/activated first, it is saved first.
	$type = empty( $type ) ? (string) array_keys( $activated )[0] : $type;

	if ( ! empty( $activated[ $type ] ) ) {
		return absint( $activated[ $type ] );
	}

	// Fallback.
	$types = array_diff( [ 'lite', 'pro' ], [ $type ] );

	foreach ( $types as $_type ) {
		if ( ! empty( $activated[ $_type ] ) ) {
			return absint( $activated[ $_type ] );
		}
	}

	return false;
}

/**
 * Check permissions for currently logged in user, taken from Charitable.
 * Both short (e.g. 'view_own_forms') or long (e.g. 'charitable_view_own_forms') capability name can be used.
 * Only Charitable capabilities get processed.
 *
 * @since 1.7.0.3
 *
 * @param array|string $caps Capability name(s).
 * @param int          $id   ID of the specific object to check against if capability is a "meta" cap. "Meta"
 *                           capabilities, e.g. 'edit_post', 'edit_user', etc., are capabilities used by
 *                           map_meta_cap() to map to other "primitive" capabilities, e.g. 'edit_posts',
 *                           edit_others_posts', etc. Accessed via func_get_args() and passed to
 *                           WP_User::has_cap(), then map_meta_cap().
 *
 * @return bool
 */
function charitable_current_user_can( $caps = [], $id = 0 ) {

	$user_can = current_user_can( $caps , $id );

	return apply_filters( 'charitable_current_user_can', $user_can, $caps, $id );
}

/**
 * Determines whether the current request is a WP CLI request.
 *
 * @since 1.7.0.3
 *
 * @return bool
 */
function charitable_doing_wp_cli() {

	return defined( 'WP_CLI' ) && WP_CLI;
}

/**
 * Modify the default USer-Agent generated by wp_remote_*() to include additional information.
 *
 * @since 1.7.0.3
 *
 * @return string
 */
function charitable_get_default_user_agent() {

	$wpcharitable_type = function_exists( 'charitable_is_pro' ) && charitable_is_pro() ? 'Paid' : 'Lite';

	return 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ) . '; WPCharitable/' . $wpcharitable_type;
}


function charitable_theme_template_paths() {

	$template_dir = 'charitable';

	$file_paths = array(
		1   => trailingslashit( get_stylesheet_directory() ) . $template_dir,
		10  => trailingslashit( get_template_directory() ) . $template_dir,
		100 => trailingslashit( CHARTIABLE_DIRECTORY_PATH ) . 'includes',
	);

	$file_paths = apply_filters( 'charitable_helpers_templates_get_theme_template_paths', $file_paths );

	// Sort the file paths based on priority.
	ksort( $file_paths, SORT_NUMERIC );

	return array_map( 'trailingslashit', $file_paths );
}

/**
 * Locate a template and return the path for inclusion.
 *
 * @since 1.7.0.3
 *
 * @param string $template_name Template name.
 *
 * @return string
 */
function charitable_locate_template( $template_name ) {

	// Trim off any slashes from the template name.
	$template_name = ltrim( $template_name, '/' );

	if ( empty( $template_name ) ) {
		return apply_filters( 'charitable_helpers_templates_locate', '', $template_name );
	}

	$located = '';

	// Try locating this template file by looping through the template paths.
	foreach ( charitable_theme_template_paths() as $template_path ) {
		if ( file_exists( $template_path . $template_name ) ) {
			$located = $template_path . $template_name;
			break;
		}
	}

	return apply_filters( 'charitable_helpers_templates_locate', $located, $template_name );
}

/**
 * Include a template.
 * Use 'require' if $args are passed or 'load_template' if not.
 *
 * @since 1.7.0.3
 *
 * @param string $template_name Template name.
 * @param array  $args          Arguments.
 * @param bool   $extract       Extract arguments.
 *
 * @throws \RuntimeException If extract() tries to modify the scope.
 */
function charitable_admin_include_html( $template_name, $args = array(), $extract = false ) {

	$template_name .= '.php';

	// Allow 3rd party plugins to filter template file from their plugin.
	$located = apply_filters( 'charitable_helpers_templates_include_html_located', charitable_locate_template( $template_name ), $template_name, $args, $extract );
	$args    = apply_filters( 'charitable_helpers_templates_include_html_args', $args, $template_name, $extract );

	if ( empty( $located ) || ! \is_readable( $located ) ) {
		return;
	}

	// Load template WP way if no arguments were passed.
	if ( empty( $args ) ) {
		load_template( $located, false );
		return;
	}

	$extract = apply_filters( 'charitable_helpers_templates_include_html_extract_args', $extract, $template_name, $args );

	if ( $extract && is_array( $args ) ) {

		$created_vars_count = extract( $args, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract

		// Protecting existing scope from modification.
		if ( count( $args ) !== $created_vars_count ) {
			throw new \RuntimeException( 'Extraction failed: variable names are clashing with the existing ones.' );
		}
	}

	require $located;
}

/**
 * Like self::include_html, but returns the HTML instead of including.
 *
 * @since 1.7.0.3
 *
 * @param string $template_name Template name.
 * @param array  $args          Arguments.
 * @param bool   $extract       Extract arguments.
 *
 * @return string
 */
function charitable_admin_get_html( $template_name, $args = array(), $extract = false ) {
	ob_start();
	charitable_admin_include_html( $template_name, $args, $extract );
	return ob_get_clean();
}

/**
 * Include a template - alias to \charitable\Helpers\Template::get_html.
 * Use 'require' if $args are passed or 'load_template' if not.
 *
 * @since 1.7.0.3
 *
 * @param string $template_name Template name.
 * @param array  $args          Arguments.
 * @param bool   $extract       Extract arguments.
 *
 * @throws \RuntimeException If extract() tries to modify the scope.
 *
 * @return string Compiled HTML.
 */
function charitable_render( $template_name, $args = array(), $extract = false ) {
	return charitable_admin_get_html( $template_name, $args, $extract );
}

/**
 * Determine if the plugin/addon installations are allowed.
 *
 * @since 1.6.2.3
 *
 * @param string $type Should be `plugin` or `addon`.
 *
 * @return bool
 */
function charitable_can_install( $type ) {

	return charitable_can_do( 'install', $type );
}

/**
 * Determine if the plugin/addon activations are allowed.
 *
 * @since 1.7.3
 *
 * @param string $type Should be `plugin` or `addon`.
 *
 * @return bool
 */
function charitable_can_activate( $type ) {

	return charitable_can_do( 'activate', $type );
}

/**
 * Determine if the plugin/addon installations/activations are allowed.
 *
 * @since 1.7.3
 *
 * @internal Use charitable_can_activate() or charitable_can_install() instead.
 *
 * @param string $what Should be 'activate' or 'install'.
 * @param string $type Should be `plugin` or `addon`.
 *
 * @return bool
 */
function charitable_can_do( $what, $type ) {

	if ( ! in_array( $what, [ 'install', 'activate' ], true ) ) {
		return false;
	}

	if ( ! in_array( $type, [ 'plugin', 'addon' ], true ) ) {
		return false;
	}

	$capability = $what . '_plugins';

	if ( ! current_user_can( $capability ) ) {
		return false;
	}

	// Determine whether file modifications are allowed and it is activation permissions checking.
	if ( $what === 'install' && ! wp_is_file_mod_allowed( 'charitable_can_install' ) ) {
		return false;
	}

	// All plugin checks are done.
	if ( $type === 'plugin' ) {
		return true;
	}

	// Addons require additional license checks.
	// $license = get_option( 'charitable_license', [] );

	// // Allow addons installation if license is not expired, enabled and valid.
	// return empty( $license['is_expired'] ) && empty( $license['is_disabled'] ) && empty( $license['is_invalid'] );

	return true;
}

/**
 * Perform test connection to verify that the current web host can successfully
 * make outbound SSL connections.
 *
 * @since 1.4.5
 */
function charitable_verify_ssl() {

	// Run a security check.
	check_ajax_referer( 'charitable-admin', 'nonce' );

	// Check for permissions.
	if ( ! charitable_current_user_can() ) {
		wp_send_json_error(
			array(
				'msg' => esc_html__( 'You do not have permission to perform this operation.', 'charitable' ),
			)
		);
	}

	$response = wp_remote_post( 'https://wpcharitable.com/connection-test.php' );

	if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
		wp_send_json_success(
			array(
				'msg' => esc_html__( 'Success! Your server can make SSL connections.', 'charitable' ),
			)
		);
	}

	wp_send_json_error(
		array(
			'msg'   => esc_html__( 'There was an error and the connection failed. Please contact your web host with the technical details below.', 'charitable' ),
			'debug' => '<pre>' . print_r( map_deep( $response, 'wp_strip_all_tags' ), true ) . '</pre>',
		)
	);
}
add_action( 'wp_ajax_charitable_verify_ssl', 'charitable_verify_ssl' );

/**
 * Deactivate addon.
 *
 * @since 1.0.0
 * @since 1.6.2.3 Updated the permissions checking.
 */
function charitable_deactivate_addon() {

	// Run a security check.
	check_ajax_referer( 'charitable-admin', 'nonce' );

	// Check for permissions.
	if ( ! current_user_can( 'deactivate_plugins' ) ) {
		wp_send_json_error( esc_html__( 'Plugin deactivation is disabled for you on this site.', 'charitable' ) );
	}

	$type = empty( $_POST['type'] ) ? 'addon' : sanitize_key( $_POST['type'] );

	if ( isset( $_POST['plugin'] ) ) {
		$plugin = sanitize_text_field( wp_unslash( $_POST['plugin'] ) );

		if ( defined( 'CHARITABLE_DEBUG' ) && CHARITABLE_DEBUG ) {
			error_log( 'charitable_deactivate_addon' );
			error_log( print_r( $plugin, true ) );
			error_log( print_r( $_POST, true ) );
			error_log( print_r( $type, true ) );
		}

		deactivate_plugins( $plugin );

		do_action( 'charitable_plugin_deactivated', $plugin );

		if ( $type === 'plugin' ) {
			wp_send_json_success( esc_html__( 'Plugin deactivated.', 'charitable' ) );
		} else {
			wp_send_json_success( esc_html__( 'Addon deactivated.', 'charitable' ) );
		}
	}

	wp_send_json_error( esc_html__( 'Could not deactivate the addon. Please deactivate from the Plugins page.', 'charitable' ) );
}
add_action( 'wp_ajax_charitable_deactivate_addon', 'charitable_deactivate_addon' );

/**
 * Activate addon.
 *
 * @since 1.0.0
 * @since 1.6.2.3 Updated the permissions checking.
 */
function charitable_ajax_activate_addon() {

	// Run a security check.
	check_ajax_referer( 'charitable-admin', 'nonce' );

	// Check for permissions.
	if ( ! current_user_can( 'activate_plugins' ) ) {
		wp_send_json_error( esc_html__( 'Plugin activation is disabled for you on this site.', 'charitable' ) );
	}

	$type = 'addon';

	if ( isset( $_POST['plugin'] ) ) {

		if ( ! empty( $_POST['type'] ) ) {
			$type = sanitize_key( $_POST['type'] );
		}

		$plugin   = sanitize_text_field( wp_unslash( $_POST['plugin'] ) );

		$activate = activate_plugins( $plugin );

		/**
		 * Fire after plugin activating via the Charitable installer.
		 *
		 * @since 1.6.3.1
		 *
		 * @param string $plugin Path to the plugin file relative to the plugins directory.
		 */
		do_action( 'charitable_plugin_activated', $plugin );

		if ( ! is_wp_error( $activate ) ) {
			if ( $type === 'plugin' ) {
				wp_send_json_success( esc_html__( 'Plugin activated.', 'charitable' ) );
			} else {
				wp_send_json_success( esc_html__( 'Addon activated.', 'charitable' ) );
			}
		}
	}

	if ( $type === 'plugin' ) {
		wp_send_json_error( esc_html__( 'Could not activate the plugin. Please activate it on the Plugins page.', 'charitable' ) );
	}

	wp_send_json_error( esc_html__( 'Could not activate the addon. Please activate it on the Plugins page.', 'charitable' ) );
}
add_action( 'wp_ajax_charitable_activate_addon', 'charitable_ajax_activate_addon' );


/**
 * Installs an Charitable addon.
 *
 * @since 1.0.0
 */
function charitable_ajax_install_addon() {

	// Run a security check first.
	check_admin_referer( 'charitable-admin', 'nonce' );

	// Permission check for installing plugins.
	if ( ! current_user_can( 'install_plugins' ) ) {
		wp_send_json_error( esc_html__( 'Plugin install is disabled for you on this site.', 'charitable' ) );
	}

	// Install the addon.
	if ( isset( $_POST['plugin'] ) ) {
		$download_url = esc_url_raw( wp_unslash( $_POST['plugin'] ) );
		global $hook_suffix;

		if ( defined( 'CHARITABLE_DEBUG' ) && CHARITABLE_DEBUG ) {
			error_log( 'charitable_ajax_install_addon' );
			error_log( print_r( $_POST, true ) );
			error_log( print_r( $download_url, true ) );
		}

		// Set the current screen to avoid undefined notices.
		set_current_screen();

		// Prepare variables.
		$method = '';
		$url    = add_query_arg(
			array(
				'page' => 'charitable-settings',
			),
			admin_url( 'admin.php' )
		);
		$url    = esc_url( $url );

		// Start output bufferring to catch the filesystem form if credentials are needed.
		ob_start();
		$creds = request_filesystem_credentials( $url, $method, false, false, null );
		if ( false === $creds ) {
			$form = ob_get_clean();
			echo wp_json_encode( array( 'form' => $form ) );
			die;
		}

		if ( defined( 'CHARITABLE_DEBUG' ) && CHARITABLE_DEBUG ) {
			error_log( 'charitable_ajax_install_addon creds' );
			error_log( print_r( $creds, true ) );
		}

		// If we are not authenticated, make it happen now.
		if ( ! WP_Filesystem( $creds ) ) {
			ob_start();
			request_filesystem_credentials( $url, $method, true, false, null );
			$form = ob_get_clean();
			echo wp_json_encode( array( 'form' => $form ) );
			die;
		}

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', [ 'Language_Pack_Upgrader', 'async_upgrade' ], 20 );

		// We do not need any extra credentials if we have gotten this far, so let's install the plugin.
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once plugin_dir_path( CHARTIABLE_DIRECTORY_PATH ) . 'charitable/includes/utilities/Skin.php';

		// Create the plugin upgrader with our custom skin.
		$skin      = new Charitable_Skin();
		$installer = new Plugin_Upgrader( $skin );
		$installer->install( $download_url );

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		if ( $installer->plugin_info() ) {
			$plugin_basename = $installer->plugin_info();
			$plugin          = sanitize_text_field( wp_unslash( $_POST['plugin'] ) );
			// attempt to activate the installed addon, save the user a step.
			$activate = activate_plugins( $plugin_basename );
			if ( ! is_wp_error( $activate ) ) {
				wp_send_json_success( array( 'basename' => $plugin_basename, 'is_activated' => true, 'msg' => esc_html__( 'Addon installed and activated.', 'charitable' ) ) );
			} else {
				wp_send_json_success( array( 'basename' => $plugin_basename, 'msg' => esc_html__( 'Addon installed.', 'charitable' ) ) );
			}
			die;
		}
	}

	// Send back a response.
	echo wp_json_encode( true );
	die;

}
add_action( 'wp_ajax_charitable_install_addon', 'charitable_ajax_install_addon' );

/**
 * Keep an option updated to know what/when a warning went out for a third party plugin, theme, etc.
 *
 * @since  1.7.0.8
 */
function charitable_update_third_party_warning_option() {

	$third_party_warnings = get_option( 'charitable_third_party_warnings' );
	$updated              = false;

	// If the option is empty or wiped (which is possible), initialize it with an empty array.
	$third_party_warnings = ( false === $third_party_warnings ) ? array( 'plugins' => array() ) : $third_party_warnings = json_decode( $third_party_warnings, ARRAY_A );

	if ( is_plugin_active( 'code-snippets/code-snippets.php' ) && ! array_key_exists( 'code-snippets/code-snippets.php', $third_party_warnings['plugins'] ) ) {
		$third_party_warnings['plugins']['code-snippets/code-snippets.php'] = 'noted';
		$updated = true;
	} else if ( ! is_plugin_active( 'code-snippets/code-snippets.php' ) && array_key_exists( 'code-snippets/code-snippets.php', $third_party_warnings['plugins'] ) && $third_party_warnings['plugins']['code-snippets/code-snippets.php'] === 'noted' ) {
		unset( $third_party_warnings['plugins']['code-snippets/code-snippets.php'] );
		$updated = true;
	}

	$third_party_warnings = apply_filters( 'charitable_update_third_party_warning_option', $third_party_warnings );

	if ( $updated ) {
		$result = update_option( 'charitable_third_party_warnings', json_encode( $third_party_warnings ) );
	}

}
add_action( 'admin_init', 'charitable_update_third_party_warning_option' );

/**
 * Get an option related to third party warning ( null, noted, dismissed )
 *
 * @since  1.7.0.8
 */
function charitable_get_third_party_warning_option( $plugin_path = false, $category = 'plugins' ) {

	if ( false === $plugin_path ) {
		return false;
	}

	$third_party_warnings = get_option( 'charitable_third_party_warnings' );

	if ( false === $third_party_warnings ) {
		return false;
	}

	$third_party_warnings = json_decode( $third_party_warnings, ARRAY_A );

	if ( ! isset( $third_party_warnings[ $category ][ $plugin_path ] ) ) {
		return false;
	}

	return esc_html( $third_party_warnings[ $category ][ $plugin_path ] );

}

/**
 * Get an option related to third party warning ( null, noted, dismissed )
 *
 * @since  1.7.0.8
 */
function charitable_set_third_party_warning_option( $plugin_path = false, $value = false, $category = 'plugins' ) {

	if ( false === $plugin_path ) {
		return;
	}

	$third_party_warnings = get_option( 'charitable_third_party_warnings' );

	if ( false === $third_party_warnings ) {
		return;
	}

	$third_party_warnings = json_decode( $third_party_warnings, ARRAY_A );

	$third_party_warnings[ $category ][ $plugin_path ] = $value;

	$result = update_option( 'charitable_third_party_warnings', json_encode( $third_party_warnings ) );

	return $result;

}