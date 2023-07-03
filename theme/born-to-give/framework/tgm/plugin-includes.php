<?php
require_once get_template_directory() . '/framework/tgm/class-tgm-plugin-activation.php';
add_action('tgmpa_register', 'borntogive_register_required_plugins');

function borntogive_register_required_plugins()
{
	$plugins_path = get_template_directory() . '/framework/tgm/plugins/';
	$plugins = array(
		array(
			'name' 					=> esc_html__('Breadcrumb NavXT', 'borntogive'),
			'slug' 					=> 'breadcrumb-navxt',
			'required' 				=> true,
			'type'					=> 'Required',
			'image_src'				=> get_template_directory_uri() . '/framework/tgm/images/plugin-navxt.png',
		),
		array(
			'name' 					=> esc_html__('Pojo Sidebars', 'borntogive'),
			'slug' 					=> 'pojo-sidebars',
			'required' 				=> false,
			'type'					=> 'Required',
			'image_src'				=> get_template_directory_uri() . '/framework/tgm/images/plugin-pojo.png',
		),
		array(
			'name' 					=> esc_html__('The GDPR Framework', 'borntogive'),
			'slug' 					=> 'gdpr-framework',
			'required' 				=> false,
			'image_src'				=> get_template_directory_uri() . '/framework/tgm/images/plugin-gdpr.png',
		),
		array(
			'name' 					=> esc_html__('Loco Translate', 'borntogive'),
			'slug' 					=> 'loco-translate',
			'required' 				=> false,
			'image_src'				=> get_template_directory_uri() . '/framework/tgm/images/plugin-loco.png',
		),
		array(
			'name' 					=> esc_html__('WooCommerce', 'borntogive'),
			'slug' 					=> 'woocommerce',
			'required' 				=> false,
			'image_src'				=> get_template_directory_uri() . '/framework/tgm/images/plugin-woo.png',
		),
		array(
			'name' 					=> esc_html__('Contact Form 7', 'borntogive'),
			'slug' 					=> 'contact-form-7',
			'required' 				=> false,
			'image_src'				=> get_template_directory_uri() . '/framework/tgm/images/plugin-cf7.png',
		),
		array(
			'name' 					=> esc_html__('Give - WordPress Donation Plugin', 'borntogive'),
			'slug' 					=> 'give',
			'required' 				=> false,
			'image_src'				=> get_template_directory_uri() . '/framework/tgm/images/plugin-give.png',
		),
		array(
			'name' 					=> esc_html__('Charitable', 'borntogive'),
			'slug' 					=> 'charitable',
			'required' 				=> true,
			'type'					=> 'Required',
			'image_src'				=> get_template_directory_uri() . '/framework/tgm/images/plugin-charitable.png',
		),
		array(
			'name' 					=> esc_html__('TinyMCE Advanced', 'borntogive'),
			'slug' 					=> 'tinymce-advanced',
			'required' 				=> true,
			'image_src'				=> get_template_directory_uri() . '/framework/tgm/images/plugin-tinymce.png',
		),
		array(
			'name' 					=> esc_html__('Regenerate Thumbnails', 'borntogive'),
			'slug' 					=> 'regenerate-thumbnails',
			'required' 				=> false,
			'image_src'				=> get_template_directory_uri() . '/framework/tgm/images/plugin-regen.png',
		),
		array(
			'name' 					=> esc_html__('Meta Box', 'borntogive'),
			'slug' 					=> 'meta-box',
			'required' 				=> true,
			'type'					=> 'Required',
			'image_src'				=> get_template_directory_uri() . '/framework/tgm/images/plugin-metabox.png',
		),
        array(
			'name' 					=> esc_html__('Best Contact Forms', 'borntogive'),
			'slug' 					=> 'wpforms-lite',
			'required' 				=> false,
			'image_src'				=> get_template_directory_uri() . '/framework/tgm/images/plugin-wpforms.png',
		),
		array(
			'name'               	=> esc_html__('Revolution Slider', 'borntogive'),
			'slug'               	=> 'revslider',
			'source'             	=> $plugins_path . 'revslider.zip',
			'required'           	=> true,
			'version' 			 	=> '6.5.6',
			'force_activation'   	=> false,
			'force_deactivation' 	=> false,
			'external_url'       	=> '',
			'type'					=> 'Required',
			'image_src'				=> get_template_directory_uri() . '/framework/tgm/images/plugin-revslider.png',
		),
		array(
			'name'               	=> esc_html__('A Core Plugin', 'borntogive'),
			'slug'               	=> 'btg-core',
			'source'             	=> $plugins_path . 'btg-core.zip',
			'required'           	=> true,
			'version'            	=> '2.2',
			'force_activation'   	=> false,
			'force_deactivation' 	=> false,
			'external_url'       	=> '',
			'type'					=> 'Required',
			'image_src'				=> get_template_directory_uri() . '/framework/tgm/images/plugin-core.png',
		),
		array(
			'name'               	=> esc_html__('BornToGive VC Elements', 'borntogive'),
			'slug'               	=> 'borntogive-vc-elements',
			'source'             	=> $plugins_path . 'borntogive-vc-elements.zip',
			'version' 			 	=> '2.2',
			'required'           	=> true,
			'force_activation'   	=> false,
			'force_deactivation' 	=> false,
			'type'					=> 'Required',
			'image_src'				=> get_template_directory_uri() . '/framework/tgm/images/plugin-btgvc.png',
		),
		array(
			'name'               	=> esc_html__('WP Bakery Page Builder', 'borntogive'),
			'slug'               	=> 'js_composer',
			'source'             	=> $plugins_path . 'js_composer.zip',
			'version' 			 	=> '6.7.0',
			'required'           	=> true,
			'force_activation'   	=> false,
			'force_deactivation' 	=> false,
			'type'					=> 'Required',
			'image_src'				=> get_template_directory_uri() . '/framework/tgm/images/plugin-vc.png',
		),
		array(
			'name'               	=> esc_html__('IMIC Shortcodes', 'borntogive'),
			'slug'               	=> 'imic-shortcodes',
			'source'             	=> $plugins_path . 'imic-shortcodes.zip',
			'version' 			 	=> '1.4',
			'required'           	=> true,
			'force_activation'   	=> false,
			'force_deactivation' 	=> false,
			'type'					=> 'Required',
			'image_src'				=> get_template_directory_uri() . '/framework/tgm/images/plugin-imic.png',
		),

	);

	$config = array(
		'id'			=> 'tgmpa',
		'default_path'	=> '',
		'menu'			=> 'tgmpa-install-plugins',
		'parent_slug'	=> 'themes.php',
		'capability'	=> 'edit_theme_options',
		'has_notices'	=> false,
		'dismissable'	=> true,
		'dismiss_msg'	=> '',
		'is_automatic'	=> true,
		'message'		=> '',
	);

	tgmpa($plugins, $config);
}

