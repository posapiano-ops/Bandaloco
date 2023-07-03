<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

do_action('asd');

global $borntogive_allowed_tags;
?>

<div class="wrap about-wrap imi-admin-wrap">

	<?php imi_get_admin_tabs('demo-importer'); ?>

	<?php if (class_exists('ReduxFramework_extension_wbc_importer')) :
		$execution_time = ini_get('max_execution_time');
		if ($execution_time < 60) { ?>
			<div class="imi-row">
				<div class="imi-col-sm-12">
					<div class="imi-box imi-box-importer">
						<div class="imi-box-head">
							<?php esc_html_e('Warning', 'borntogive-core'); ?>
						</div>
						<div class="imi-box-content">
							<?php echo wp_kses('To install several plugins correctly including Revolution Slider, maximum execution time must be at least 60 seconds. At this moment, this value is less than 60 seconds in your host. To fix it there is System Status page which provides essential guidance, <a href="' . esc_url(self_admin_url('admin.php?page=imi-admin-system-status')) . '"><strong>click here</strong></a> to go to Sysytem Status Page.', $borntogive_allowed_tags); ?>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>

		<div class="wbc_importer imi-theme-browser-wrap">
			<div class="theme-browser rendered wp-clearfix">
				<div class="themes wp-clearfix">

					<?php

					$wbc_importer		= ReduxFramework_extension_wbc_importer::get_instance();
					$tgmpa_list_table	= new TGMPA_List_Table;
					$plugins			= TGM_Plugin_Activation::$instance->plugins;
					$demo_screen_url 	= BTG__PLUGIN_URL . '/imi-admin/theme-options/screen-images/';

					if (!empty($wbc_importer->demo_data_dir)) {
						$demo_data_dir = $wbc_importer->demo_data_dir;
						$demo_data_url = 'https://data.imithemes.com/demo-data/borntogive/';
					}

					$nonce				= wp_create_nonce('redux_borntogive_options_wbc_importer');
					$imported			= false;
					$wbc_demo_imports	= $wbc_importer->wbc_import_files;
					$output				= '';

					if (!empty($wbc_demo_imports)) {

						$i = '0';

						foreach ($wbc_demo_imports as $section => $imports) :

							$i++;

							if (empty($imports)) {
								continue;
							}

							if (!array_key_exists('imported', $imports)) {

								$imported		= false;
								$extra_class	= 'not-imported';
								$import_message	= esc_html__('Import Demo', 'borntogive-core');
							} else {

								$imported = true;
								$extra_class = 'active imported';
								$import_message = esc_html__('Demo Imported', 'borntogive-core');
							}

							$output .= '
								<div class="wrap-importer theme ' . $extra_class . '" data-demo-id="' . esc_attr($section) . '"  data-nonce="' . $nonce . '">
									<div class="theme-screenshot">';

							if (isset($imports['image'])) {
								$output .= '<img class="wbc_image" src="' . esc_attr(esc_url($demo_screen_url . $imports['directory'] . '/' . $imports['image'])) . '">';
							}

							$output .= '
								</div>  <!-- end theme-screenshot -->
								<a class="more-details" href="' . $imports['preview'] . '" target="_blank">' . esc_html__('Preview', 'borntogive-core') . '</a>
								<h3 class="theme-name">' . esc_html(apply_filters('wbc_importer_directory_title', $imports['display'])) . '</h3>
								<div class="theme-actions">';

							if ($imported == false) {

								$output .= '
									<div class="wbc-importer-buttons">
										<span class="spinner">' . esc_html__('Please Wait...', 'borntogive-core') . '</span>
										<span class="button button-primary importer-button import-demo-data">' . esc_html__('Import', 'borntogive-core') . '</span>
									</div>';
							} else {

								$output .= '
									<div class="wbc-importer-buttons button-secondary importer-button">' . esc_html__('Imported', 'borntogive-core') . '</div>
									<span class="spinner">' . esc_html__('Please Wait...', 'borntogive-core') . '</span>
									<div id="wbc-importer-reimport" class="wbc-importer-buttons button-primary import-demo-data importer-button">' . esc_html__('Re-Import', 'borntogive-core') . '</div>';
							}

							$output .= '
									</div> <!-- end theme-actions -->
								</div>';

							// lightbox
							$output .= '
							<div class="imi-lightbox-wrap wp-clearfix">
								<div class="imi-lightbox-contents">

									<i class="dashicons dashicons-no-alt"></i>

									<div class="imi-lightbox" data-demo-id="' . esc_attr($section) . '">
										<h2>' . esc_html(apply_filters('wbc_importer_directory_title', $imports['directory'])) . '</h2>
										<div class="imi-lb-content imi-settings">

											<div class="imi-row-import">
												<div class="imi-install-plugins-wrap">
													<h3>' . esc_html__('These below plugins are required', 'borntogive-core') . '</h3>
													<a href="#" class="imi-admin-btn imi-install-plugins">Activate all plugins</a>
												</div>
												<div class="imi-plugins-wrap imi-plugins">';
							$req_plugins = array();
							$keys		 = imi_demo_plugins($section);

							foreach ($keys as $key) :
								if (array_key_exists($key, $plugins)) {
									$req_plugins[$key] = $plugins[$key];
								}
							endforeach;

							foreach ($req_plugins as $plugin) :

								$plugin['type']				= isset($plugin['type']) ? $plugin['type'] : 'recommended';
								$plugin['sanitized_plugin']	= $plugin['name'];

								$plugin_action = $tgmpa_list_table->actions_plugin($plugin);

								if (is_plugin_active($plugin['file_path'])) {
									$plugin_action = '
																<div class="row-actions visible active">
																	<span class="activate">
																		<a class="button imi-admin-btn">' . esc_html__('Activated', 'borntogive-core') . '</a>
																	</span>
																</div>
															';
								}

								$output .= '<div class="imi-plugin wp-clearfix" data-plugin-name="' . esc_html($plugin['name']) . '">';
								$output .= '<h4>' . esc_html($plugin['name']) . '</h4>';
								$output .= '<span class="imi-plugin-line"></span>';
								$output .= $plugin_action;
								$output .= '</div>';

							endforeach;

							$output .= '
												
												<p>' . esc_html__('If the process for any plugin activation keeps loading for more than 5 minutes then refresh this page, click import button again and activate the plugin again.', 'borntogive-core') . '</p>
												
												</div> <!-- end imi-plugins-wrap -->
											</div>

											<div class="imi-row-import">
												<!-- <h3>' . esc_html__('Import content', 'borntogive-core') . ' <span>' . esc_html__('(menus only import by selecting "All")', 'borntogive-core') . '</span></h3> -->
												<h3>' . esc_html__('Import content', 'borntogive-core') . '</h3>
												<div class="imi-import-content-wrap wp-clearfix">
													<div class="imi-checkbox-wrap imi-all-contents">
														<input type="checkbox" class="imi-checkbox-input" checked id="all' . esc_attr($i) . '" name="importcontent" value="all">
														<label for="all' . esc_attr($i) . '" class="imi-checkbox-label"></label>
														<span>' . esc_html__('All', 'borntogive-core') . '</span>
													</div>
													<div class="imi-checkbox-wrap">
														<input type="checkbox" class="imi-checkbox-input" checked id="contents' . esc_attr($i) . '" name="importcontent" value="contents">
														<label for="contents' . esc_attr($i) . '" class="imi-checkbox-label"></label>
														<span>' . esc_html__('Contents', 'borntogive-core') . '</span>
													</div>
													<div class="imi-checkbox-wrap">
														<input type="checkbox" class="imi-checkbox-input" checked id="images' . esc_attr($i) . '" name="importcontent" value="images">
														<label for="images' . esc_attr($i) . '" class="imi-checkbox-label"></label>
														<span>' . esc_html__('Images', 'borntogive-core') . '</span>
													</div>
													<div class="imi-checkbox-wrap">
														<input type="checkbox" class="imi-checkbox-input" checked id="widgets' . esc_attr($i) . '" name="importcontent" value="widgets">
														<label for="widgets' . esc_attr($i) . '" class="imi-checkbox-label"></label>
														<span>' . esc_html__('Widgets', 'borntogive-core') . '</span>
													</div>
													<div class="imi-checkbox-wrap">
														<input type="checkbox" class="imi-checkbox-input" checked id="themeoptions' . esc_attr($i) . '" name="importcontent" value="themeoptions">
														<label for="themeoptions' . esc_attr($i) . '" class="imi-checkbox-label"></label>
														<span>' . esc_html__('Theme options', 'borntogive-core') . '</span>
													</div>
													<div class="imi-checkbox-wrap">
														<input type="checkbox" class="imi-checkbox-input" checked id="sliders' . esc_attr($i) . '" name="importcontent" value="sliders">
														<label for="sliders' . esc_attr($i) . '" class="imi-checkbox-label"></label>
														<span>' . esc_html__('Sliders', 'borntogive-core') . '</span>
													</div>
												</div>
											</div>

											<div class="imi-row-import">
												<a href="#" class="imi-import-demo-btn">' . esc_html__('Import', 'borntogive-core') . '</a>
											</div>
										</div>

										<div class="imi-suc-imp-title"><strong>' . esc_html(apply_filters('wbc_importer_directory_title', $imports['directory'])) . '</strong></div>
										<div class="imi-lb-content imi-suc-imp-content-wrap">
											<div class="imi-suc-imp-content">
												<a href="' . esc_url(home_url('/')) . '" target="_blank" title="' . esc_html__('Visit Site', 'borntogive-core') . '">
													<img class="wbc_image" src="' . esc_attr(esc_url($demo_screen_url . $imports['directory'] . '/' . $imports['image'])) . '">
												</a>
												<div class="imi-suc-imp-t100"><strong>100%</strong>' . esc_html__('Demo is Successfully Imported', 'borntogive-core') . '</div>
												<div class="imi-suc-imp-links">
													<a class="imi-suc-imp-links-d" href="' . esc_url(self_admin_url('admin.php?page=imi-admin-welcome')) . '">' . esc_html__('Born To Give Dashboard', 'borntogive-core') . '</a>
													<a class="imi-suc-imp-links-t" href="' . esc_url(self_admin_url('admin.php?page=_options')) . '">' . esc_html__('Theme Options', 'borntogive-core') . '</a>
													<a class="imi-suc-imp-links-v" href="' . esc_url(home_url('/')) . '" target="_blank">' . esc_html__('Visit Site', 'borntogive-core') . '</a>
												</div>
												<div class="imi-start">
													<span class="imi-start-message">' . esc_html__('Please don’t refresh the page until import is done completely, import time is related to your host configuration and it may take upto 15 minutes.', 'borntogive-core') . '</span>
												</div>
											</div>
										</div>
										
									</div>
								</div>
							</div>';

						endforeach;

						echo '' . $output;
					} else {
						echo '<h5>' . esc_html__('No Demo Data Provided', 'borntogive-core') . '</h5>';
					}

					?>

				</div>
			</div>
		</div>

	<?php endif; ?>

</div> <!-- end wrap -->