<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}
if (!function_exists('get_plugins')) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
$plugin_data = get_plugins();
$btg_core_data = (isset($plugin_data['btg-core/btg.php'])) ? $plugin_data['btg-core/btg.php'] : '';
if ($btg_core_data) {
	if (isset($btg_core_data['Version']) && $btg_core_data['Version'] > 1.6) {
		return;
	}
}
if (class_exists('IMI_Admin')) {
	//return;
}
//if (!class_exists('IMI_Admin')) {
add_action('admin_menu', 'btg_welcome_page_menu');
add_action('admin_notices', 'btg_admin_notice');

function btg_admin_notice()
{
	global $pagenow;
	echo '<div class="error notice" style="height:100px;"><div style="height:40px;"><div id="btg-admin-notices"><h1>' . esc_html__('Born To Give Theme Urgent Message!', 'borntogive') . '</h1></div></div><p>' . esc_html__('Please install/update the Born To Give core plugin now, its mandatory to keep this plugin always activated to use all features of this theme.', 'borntogive') . '</p><a href="' . esc_url(site_url('/wp-admin/themes.php?page=btg')) . '">' . esc_html__('Install/Update plugin here', 'borntogive') . '</a></div>';
}
//}

function btg_welcome_page_menu()
{
	add_theme_page(
		'Born to give',
		'Born to give',
		'manage_options',
		'btg',
		'btg_welcome_page'
	);
}

function btg_welcome_page()
{
	?>
	<div class="wrap about-wrap imi-admin-wrap">

		<h1><?php echo esc_html__('Thanks for using Born to give Theme', 'borntogive'); ?></h1>
		<div class="about-text"><?php echo esc_html__('Born To Give', 'borntogive') . esc_html__(' is now installed and ready to use! Please install/update and activate the core plugin from below to get all the features of this theme.', 'borntogive'); ?></div>
		<div class="wp-badge"></div>

		<div id="imi-dashboard" class="wrap about-wrap">
			<div class="welcome-content imi-clearfix">
				<div id="imi-dashboard" class="wrap about-wrap">
					<div class="welcome-content imi-clearfix extra">
						<div class="imi-row">
							<div class="imi-col-sm-12">

								<div class="imi-plugins imi-theme-browser-wrap">
									<div class="theme-browser rendered">
										<div class="themes">
											<div class="theme ">

												<div class="plugin-requirement plugin-required"><?php esc_html_e('REQUIRED', 'borntogive'); ?></div>

												<div class="theme-screenshot">
													<img src="<?php echo esc_url(get_template_directory_uri() . '/framework/tgm/images/plugin-core.png'); ?>" alt="Screen">
												</div>

												<h3 class="theme-name"><?php esc_html_e('Add core features of theme', 'borntogive'); ?></h3>
												<?php
												if (!function_exists('is_plugin_inactive')) {
													require_once(ABSPATH . '/wp-admin/includes/plugin.php');
												}

												if (is_plugin_inactive('btg-core/btg.php')) {
													//plugin is not activated
													$btg_core_plugin = WP_PLUGIN_DIR . '/btg-core';
													if (is_file($btg_core_plugin . '/btg.php')) {
														if (empty($wp_filesystem)) {
															require_once ABSPATH . '/wp-admin/includes/file.php';
															WP_Filesystem();
														}
														global $wp_filesystem;
														if ($wp_filesystem) {
															$wp_filesystem->delete($btg_core_plugin, true);
														}
													}
												}

												?>
												<div class="theme-actions">
													<div class="row-actions visible">
														<span class="install">
															<?php
															if (is_plugin_inactive('btg-core/btg.php')) {
																?>
																<a href="<?php echo esc_url(home_url()); ?>/wp-admin/themes.php?page=tgmpa-install-plugins&amp;plugin=btg-core&amp;tgmpa-install=install-plugin&amp;tgmpa-nonce=<?php echo esc_attr(wp_create_nonce('tgmpa-install')); ?>" data-plugin-action="install" class="button imi-admin-btn">Activate <span class="screen-reader-text"><?php esc_html_e('Add core features of theme', 'borntogive'); ?></span>
																</a>
															<?php
														} else {
															?>
																<a href="<?php echo esc_url(home_url()); ?>/wp-admin/themes.php?page=tgmpa-install-plugins&amp;plugin=btg-core&amp;tgmpa-update=update-plugin&amp;tgmpa-nonce=<?php echo esc_attr(wp_create_nonce('tgmpa-update')); ?>" data-plugin-action="update" class="button imi-admin-btn">Update <span class="screen-reader-text"><?php esc_html_e('Add core features of theme', 'borntogive'); ?></span>
																</a>
															<?php
														}
														?>
														</span>
													</div>
													<button type="button" class="toggle-row">
														<span class="screen-reader-text">Show more details</span>
													</button>
												</div>

											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div> <!-- end wrap -->
<?php
}

class IMI_Admin_Default
{

	public function __construct()
	{


		add_filter('tgmpa_load', array($this, 'tgmpa_load'), 10);
		add_action('wp_ajax_imi_install_plugin', array($this, 'install_plugin'));
		add_action('wp_ajax_imi_activate_plugin', array($this, 'activate_plugin'));
		add_action('wp_ajax_imi_deactivate_plugin', array($this, 'deactivate_plugin'));
		add_action('wp_ajax_imi_update_plugin', array($this, 'update_plugin'));
		add_action('after_switch_theme', array($this, 'after_switch_theme'));
	}

	public function tgmpa_load($load)
	{
		return true;
	}

	public function install_plugin()
	{

		if (current_user_can('manage_options')) {

			check_admin_referer('tgmpa-install', 'tgmpa-nonce');

			global $tgmpa;

			$tgmpa->install_plugins_page();

			$url = wp_nonce_url(
				add_query_arg(
					array(
						'plugin'			=> urlencode($_GET['plugin']),
						'tgmpa-deactivate'	=> 'deactivate-plugin',
					),
					$tgmpa->get_tgmpa_url()
				),
				'tgmpa-deactivate',
				'tgmpa-nonce'
			);

			echo 'imi';
			echo wp_specialchars_decode($url);
		}

		// this is required to terminate immediately and return a proper response
		wp_die();
	}

	public function after_switch_theme()
	{
		if (is_admin() && current_user_can('manage_options') && !class_exists('IMI_Admin')) {
			wp_redirect(admin_url('themes.php?page=btg'));
		}
	}

	public function activate_plugin()
	{

		if (current_user_can('edit_theme_options')) {

			check_admin_referer('tgmpa-activate', 'tgmpa-nonce');

			global $tgmpa;

			$plugins = $tgmpa->plugins;

			foreach ($plugins as $plugin) {

				if (isset($_GET['plugin']) && $plugin['slug'] === $_GET['plugin']) {

					activate_plugin($plugin['file_path']);

					$url = wp_nonce_url(
						add_query_arg(
							array(
								'plugin'			=> urlencode($_GET['plugin']),
								'tgmpa-deactivate'	=> 'deactivate-plugin',
							),
							$tgmpa->get_tgmpa_url()
						),
						'tgmpa-deactivate',
						'tgmpa-nonce'
					);

					echo wp_specialchars_decode($url);
				}
			} // foreach

		}

		// this is required to terminate immediately and return a proper response
		wp_die();
	}

	public function deactivate_plugin()
	{

		if (current_user_can('edit_theme_options')) {

			check_admin_referer('tgmpa-deactivate', 'tgmpa-nonce');

			global $tgmpa;

			$plugins = $tgmpa->plugins;

			foreach ($plugins as $plugin) {

				if (isset($_GET['plugin']) && $plugin['slug'] === $_GET['plugin']) {

					deactivate_plugins($plugin['file_path']);

					$url = wp_nonce_url(
						add_query_arg(
							array(
								'plugin'			=> urlencode($_GET['plugin']),
								'tgmpa-activate'	=> 'activate-plugin',
							),
							$tgmpa->get_tgmpa_url()
						),
						'tgmpa-activate',
						'tgmpa-nonce'
					);

					echo wp_specialchars_decode($url);
				}
			} // foreach

		}

		// this is required to terminate immediately and return a proper response
		wp_die();
	}

	public function update_plugin()
	{
		if (current_user_can('manage_options')) {
			check_admin_referer('tgmpa-update', 'tgmpa-nonce');
			global $tgmpa;
			$tgmpa->install_plugins_page();

			$url = wp_nonce_url(
				add_query_arg(
					array(
						'plugin'			=> urlencode($_GET['plugin']),
						'tgmpa-deactivate'	=> 'deactivate-plugin',
					),
					$tgmpa->get_tgmpa_url()
				),
				'tgmpa-deactivate',
				'tgmpa-nonce'
			);

			echo 'imi';
			echo wp_specialchars_decode($url);
		}

		// this is required to terminate immediately and return a proper response
		wp_die();
	}
}

new IMI_Admin_Default();
