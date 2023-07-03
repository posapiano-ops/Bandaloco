<?php
get_header();
$borntogive_options = get_option('borntogive_options');
borntogive_sidebar_position_module();
$pageSidebarGet = get_post_meta(get_the_ID(), 'borntogive_select_sidebar_from_list', true);
$pageSidebarStrictNo = get_post_meta(get_the_ID(), 'borntogive_strict_no_sidebar', true);
$pageSidebarOpt = (isset($borntogive_options['page_sidebar'])) ? $borntogive_options['page_sidebar'] : '';
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
?>
<!-- Start Body Content -->
<div id="main-container">
	<div class="content">
		<div class="container">
			<div class="row">
				<div class="col-md-<?php echo esc_attr($class); ?>" id="content-col">
					<?php if (have_posts()) : while (have_posts()) : the_post();
							echo '<div class="post-content">';
							the_content();
							echo '</div>';
						endwhile;
					endif; ?>
					<?php if (isset($borntogive_options['switch_sharing']) && $borntogive_options['switch_sharing'] == 1 && $borntogive_options['share_post_types']['2'] == '1') { ?>
						<?php borntogive_share_buttons(); ?>
					<?php } ?>
					<?php if (comments_open()) {
						comments_template('', true);
					} ?>
				</div>
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
<?php get_footer(); ?>