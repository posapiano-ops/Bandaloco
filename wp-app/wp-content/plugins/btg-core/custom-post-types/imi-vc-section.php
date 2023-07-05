<?php
/* ==================================================
  Testimonial Post Type Functions
  ================================================== */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
add_action('init', 'imi_vcsection_register', 0);
function imi_vcsection_register() {
    $labels = array(
        'name' => __('VC Section', 'borntogive-core'),
        'singular_name' => __('VC Section', 'borntogive-core'),
        'add_new' => __('Add New', 'borntogive-core'),
		'all_items'=> __('VC Sections', 'borntogive-core'),
        'add_new_item' => __('Add New', 'borntogive-core'),
        'edit_item' => __('Edit', 'borntogive-core'),
        'new_item' => __('New', 'borntogive-core'),
        'view_item' => __('View', 'borntogive-core'),
        'search_items' => __('Search', 'borntogive-core'),
        'not_found' => __('Nothing found', 'borntogive-core'),
        'not_found_in_trash' => __('Nothing found in Trash', 'borntogive-core'),
        'parent_item_colon' => '',
    );
    $args = array(
        'labels' => $labels,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => false,
        'hierarchical' => false,
		'rewrite' => true,
        'supports' => array('title', 'editor'),
        'has_archive' => false,
		'menu_icon' => 'dashicons-align-left',
	
    );
    register_post_type('imi_vc_section', $args);
}
?>