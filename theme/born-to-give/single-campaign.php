<?php
get_header();
global $borntogive_allowed_tags;
$borntogive_options = get_option('borntogive_options');
borntogive_sidebar_position_module();
$pageSidebarGet = get_post_meta(get_the_ID(), 'borntogive_select_sidebar_from_list', true);
$pageSidebarStrictNo = get_post_meta(get_the_ID(), 'borntogive_strict_no_sidebar', true);
$pageSidebarOpt = (isset($borntogive_options['campaign_sidebar'])) ? $borntogive_options['campaign_sidebar'] : '';
if ($pageSidebarGet != '') {
	$pageSidebar = $pageSidebarGet;
} elseif ($pageSidebarOpt != '') {
	$pageSidebar = $pageSidebarOpt;
} else {
	$pageSidebar = '';
}
if ($pageSidebarStrictNo == 1) {
	$pageSidebar = '';
}
$sidebar_column = get_post_meta(get_the_ID(), 'borntogive_sidebar_columns_layout', true);
$sidebar_column = ($sidebar_column == '') ? 4 : $sidebar_column;
if (!empty($pageSidebar) && is_active_sidebar($pageSidebar)) {
	$left_col = 12 - intval($sidebar_column);
	$class = $left_col;
} else {
	$class = 12;
}
$page_header = get_post_meta(get_the_ID(), 'borntogive_pages_Choose_slider_display', true);
if ($page_header == 3 || $page_header == 4) {
	get_template_part('pages', 'flex');
} elseif ($page_header == 5) {
	get_template_part('pages', 'revolution');
} else {
	get_template_part('pages', 'banner');
}

if (have_posts()) : while (have_posts()) : the_post();
		$campaign = charitable_get_current_campaign();
		$donated = $campaign->get_percent_donated_raw();
		$time_left = $campaign->get_time_left();
		$raw_end_date = $campaign->get_end_time();
		$end_date = borntogive_date_localization(get_option('date_format'), $raw_end_date);
		$goal = $campaign->get_monetary_goal();
		$donation_achieved = charitable_format_money($campaign->get_donated_amount());
		$currency = '';
		$donors = $campaign->get_donor_count();
		$campaign_desc = get_post_meta(get_the_ID(), '_campaign_description', true);
		$single_cam_show_description = (isset($options['single_cam_show_description'])) ? $options['single_cam_show_description'] : 1;
		$single_cam_show_stdt = (isset($options['single_cam_show_stdt'])) ? $options['single_cam_show_stdt'] : 1;
		$single_cam_show_endt = (isset($options['single_cam_show_endt'])) ? $options['single_cam_show_endt'] : 1;
		$single_cam_show_donors = (isset($options['single_cam_show_donors'])) ? $options['single_cam_show_donors'] : 1;
		$campaign_progress_sh = get_post_meta(get_the_ID(), 'borntogive_campaign_progress_sh', true);
		?>
		<!-- Main Content -->
		<div id="main-container">
			<div class="content">
				<div class="container">
					<div class="row">
						<div class="col-md-<?php echo esc_attr($class); ?>" id="content-col">
							<h3><?php echo get_the_title(); ?></h3>
							<div class="post-media">
								<?php echo get_the_post_thumbnail(get_the_ID()); ?>
							</div>
							<?php if($campaign->has_goal()){ ?>
								<div class="cause-progress-and-info">
									<?php if ($campaign_progress_sh == 1 || $campaign_progress_sh == '') { ?>
										<div class="campaign-progress-wrap">
											<span class="label label-default"><?php esc_html_e('Cause progress', 'borntogive'); ?></span>

											<div class="progress">
												<div class="progress-bar progress-bar-primary" data-appear-progress-animation="<?php echo round($donated, 0, PHP_ROUND_HALF_EVEN) . '%'; ?>" data-appear-animation-delay="100"><?php if ($donated != '') { ?><span class="progress-bar-tooltip"><?php echo round($donated, 0, PHP_ROUND_HALF_EVEN) . '%'; ?></span><?php } ?></div>
											</div>

											<div class="pull-left"><?php esc_html_e('Raised', 'borntogive'); ?> <strong><?php echo '' . $currency . $donation_achieved; ?></strong></div>
											<div class="pull-right"><?php esc_html_e('Goal', 'borntogive'); ?> <strong class="accent-color"><?php echo esc_attr($goal); ?></strong></div>
											<div class="spacer-20"></div>
										</div>
									<?php } ?>
									<div class="row">
										<?php if (($single_cam_show_stdt == 1 || $single_cam_show_stdt == '') || ($single_cam_show_endt == 1 || $single_cam_show_endt == '') || ($single_cam_show_donors == 1 || $single_cam_show_donors == '')) {
											$colc1 = 'col-md-5 col-sm-5';
											$colc2 = 'col-md-7 col-sm-7';
										} else {
											$colc1 = 'col-md-12 col-sm-12';
											$colc2 = '';
										} ?>


										<?php if ($single_cam_show_description == 1 || $single_cam_show_description == '') { ?>

											<div class="<?php echo esc_attr($colc1); ?>">
												<p class="lead"><?php echo wp_kses($campaign_desc, $borntogive_allowed_tags); ?></p>
											</div>

										<?php } ?>

										<?php if (($single_cam_show_stdt == 1 || $single_cam_show_stdt == '') || ($single_cam_show_endt == 1 || $single_cam_show_endt == '') || ($single_cam_show_donors == 1 || $single_cam_show_donors == '')) { ?>
											<div class="<?php echo esc_attr($colc2); ?>">
												<ul class="list-group">
													<?php if ($single_cam_show_donors == 1 || $single_cam_show_donors == '') { ?>
														<li class="list-group-item"><?php esc_html_e('Total Donors', 'borntogive'); ?><span class="badge"><?php echo esc_attr($donors); ?></span></li>
													<?php } ?>
													<?php if (($single_cam_show_endt == 1 || $single_cam_show_endt == '') && $campaign->get_time_left()) { ?>
														<li class="list-group-item"><?php esc_html_e('End Date', 'borntogive'); ?><span class="badge"><?php echo '' . $end_date; ?></span></li>
													<?php } ?>
													<?php if (($single_cam_show_stdt == 1 || $single_cam_show_stdt == '') && $campaign->get_time_left()) { ?>
														<li class="list-group-item"><?php esc_html_e('Remaining Time', 'borntogive'); ?><span class="badge"><?php echo '' . $time_left; ?></span></li>
													<?php } ?>
												</ul>
												<?php $campaign->donate_button_template();
												
												?>
											</div>
										<?php } ?>
									</div>
								</div>
							<?php } else { ?>
								<p class="lead"><?php echo wp_kses($campaign_desc, $borntogive_allowed_tags); ?></p>						
							<?php } ?>
							<div class="post-content">
								<?php the_content(); ?>
							</div>
							<?php if (isset($borntogive_options['switch_sharing']) && $borntogive_options['switch_sharing'] == 1 && $borntogive_options['share_post_types']['4'] == '1') { ?>
								<?php borntogive_share_buttons(); ?>
							<?php } ?>
						</div>

						<!-- Sidebar -->
						<?php if (is_active_sidebar($pageSidebar)) { ?>
							<!-- Sidebar -->
							<div class="col-md-<?php echo esc_attr($sidebar_column); ?>" id="sidebar-col">
								<?php dynamic_sidebar($pageSidebar); ?>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	<?php endwhile;
endif;
get_footer(); ?>