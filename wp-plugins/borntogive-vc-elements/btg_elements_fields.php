<?php
add_action( 'vc_after_init', 'vc_after_init_imi_actions' );
function vc_after_init_imi_actions() {
	// Enable VC by default on a list of Post Types
	if( function_exists('vc_set_default_editor_post_types') ){
		$list = array(
			'page',
			'post',
			'imi_vc_section',
		);
		vc_set_default_editor_post_types( $list );
	}
}

/*Code to create VC Section element in visual composer
==================================================*/
add_action( 'vc_before_init', 'borntogive_vcsection_element' );
	function borntogive_vcsection_element() 
	{
		$vc_sections_array = get_posts(array( 'post_type' => 'imi_vc_section', 'posts_per_page' => - 1));
		$vc_sections = array( esc_html__('Select', 'borntogive-vc' ) => 0);
		if ( $vc_sections_array && ! is_wp_error( $vc_sections_array ) ) {
			foreach ( $vc_sections_array as $vc_section ) {
				$vc_sections[ get_the_title( $vc_section ) ] = $vc_section->post_name;
			}
		}
		
		vc_map( array(
			"name" => esc_html__( "VC Section", "borntogive-vc" ),
			"base" => "imi_vc_section_element",
			"category" => array("Born To Give"),
			"class" => "",
			"icon" => 'icon-wpb-vc_icon',
			"params" => array(
				array(
					'type'       => 'dropdown',
					'heading'    => esc_html__( 'VC Section', 'borntogive-vc' ),
					'param_name' => 'vc_section_custom',
					'admin_label' => true,
					'value'      => $vc_sections
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Extra class name', 'borntogive-vc' ),
					'param_name' => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'borntogive-vc' ),
				),
				array(
					'type'       => 'css_editor',
					'heading'    => esc_html__( 'Css', 'borntogive-vc' ),
					'param_name' => 'css',
					'group'      => esc_html__( 'Design options', 'borntogive-vc' )
				)
			)
		));
	}

/*Code to create causes element in visual composer
==================================================*/
add_action( 'vc_before_init', 'borntogive_causes_element' );
	function borntogive_causes_element() 
	{
		$terms = array();
		$campaign_cats = get_terms('campaign_category');
		if(!is_wp_error($campaign_cats))
		{
			foreach($campaign_cats as $cat)
			{ 
				$terms[] = array('value'=>$cat->slug, 'label'=>$cat->name); 
			}
		}
		vc_map( array(
			"name" => __( "BORNTOGIVE Causes", "borntogive-vc" ),
			"base" => "borntogive_causes",
			'icon' => 'icon-wpb-vc_icon',
			"category" => __( "Born To Give", "borntogive-vc"),
			"class" => "",
			"params" => array(
				array(
					'type' => 'dropdown',
					'heading' => __( 'Causes/Campaigns View', 'borntogive-vc' ),
					'param_name' => 'causes_view',
					'value' => array( __( 'List', 'borntogive-vc' ) => 'list', __( 'Grid', 'borntogive-vc' ) => 'grid', __( 'Carousel', 'borntogive-vc' ) => 'carousel' ) ,
					'description' => __( 'Select causes view.', 'borntogive-vc' ),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Grid Column', 'borntogive-vc' ),
					'param_name' => 'causes_grid_column',
					'value' => array( __( 'One Column', 'borntogive-vc' ) => 12, __( 'Two Columns', 'borntogive-vc' ) => 6, __( 'Three Columns', 'borntogive-vc' ) => 4, __( 'Four Columns', 'borntogive-vc' ) => 3 ) ,
					'description' => __( 'Select columns of grid/carousel.', 'borntogive-vc' ),
					'param_holder_class' => 'vc_colored-dropdown',
					'dependency' => array(
						'element' => 'causes_view',
						'value' => array( 'grid', 'carousel' ),
					),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Show Excerpt?', 'borntogive-vc' ),
					'param_name' => 'show_causes_excerpt',
					'value' => array( __( 'Yes', 'borntogive-vc' ) => '1', __( 'No', 'borntogive-vc' ) => '0') ,
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Show Pagination?', 'borntogive-vc' ),
					'param_name' => 'show_causes_pagination',
					'value' => array( __( 'Yes', 'borntogive-vc' ) => 1, __( 'No', 'borntogive-vc' ) => 0) ,
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'checkbox',
					'heading' => __( 'Show Filters?', 'borntogive-vc' ),
					'param_name' => 'causes_filters',
					'description' => __( 'Show Filters for Causes/Campaigns.', 'borntogive-vc' ),
					'value' => array( __( 'Yes', 'borntogive-vc' ) => true ),
				),
				array(
					'type' 			=> 'autocomplete',
					'class' 		=> '',
					'heading' 		=> esc_html__( 'Causes/Campaign Categories', 'borntogive-vc' ),
					'description' => __( 'Show campaigns by specific categories. Search and enter by typing category names.', 'borntogive-vc' ),
					'param_name' 	=> 'causes_terms',
					'settings'		=> array( 'values' => $terms,'multiple' => false,
					'min_length' => 1,
					'groups' => true,
					// In UI show results grouped by groups, default false
					'unique_values' => true,
					// In UI show results except selected. NB! You should manually check values in backend, default false
					'display_inline' => true,
					// In UI show results inline view, default false (each value in own line)
					'delay' => 500,
					// delay for search. default 500
					'auto_focus' => true, ),
				),
				array(
					'type' => 'textfield',
					'holder' => 'div',
					'class' => '',
					'heading' => __( 'Campaigns ID', 'borntogive-vc'),
					'param_name' => 'causes_ids',
					'description' => __( 'Show specific campaigns. You can provide a single number or multiple IDs as a comma separated list.', 'borntogive-vc' )
				),
				array(
					'type' => 'textfield',
					'holder' => 'div',
					'class' => '',
					'heading' => __( 'Campaigns creator', 'borntogive-vc'),
					'param_name' => 'causes_users',
					'description' => __( 'Only show campaigns created by a certain user. Enter the user\'s ID.', 'borntogive-vc' )
				),
				array(
					'type' => 'textfield',
					'holder' => 'div',
					'class' => '',
					'heading' => __( 'Campaigns Exclude', 'borntogive-vc'),
					'param_name' => 'causes_exclude',
					'description' => __( 'Exclude specific campaigns by their ID. Pass multiple IDs as a comma separated list to exclude more than one.', 'borntogive-vc' )
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Order campaigns by', 'borntogive-vc' ),
					'param_name' => 'causes_orderby',
					'value' => array( __( 'Post Date', 'borntogive-vc' ) => 'post_date', __( 'Popular', 'borntogive-vc' ) => 'popular', __( 'Ending', 'borntogive-vc' ) => 'ending', __( 'Title', 'borntogive-vc' ) => 'title' ) ,
					'description' => __( 'The order in which campaigns are displayed. Popular is to order campaigns by the amount of money they have raised. Ending is to order campaigns by how soon they are ending (soonest to end is shown first)', 'borntogive-vc' ),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Order campaigns', 'borntogive-vc' ),
					'param_name' => 'causes_order',
					'value' => array( __( 'Descending', 'borntogive-vc' ) => 'DESC', __( 'Ascending', 'borntogive-vc' ) => 'ASC') ,
					'description' => __( 'The order for the campaigns', 'borntogive-vc' ),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'textfield',
					'holder' => 'div',
					'class' => '',
					'heading' => __( 'Number of causes/campaigns', 'borntogive-vc'),
					'param_name' => 'causes_number',
					'value' => 5,
					'description' => __( 'Insert number of causes/campaigns to show per page.', 'borntogive-vc' )
				),
				array(
					'type' => 'checkbox',
					'heading' => __( 'Show inactive campaigns/causes?', 'borntogive-vc' ),
					'param_name' => 'causes_inactive',
					'description' => __( 'Show expired campaigns/causes?.', 'borntogive-vc' ),
					'value' => array( __( 'Yes', 'borntogive-vc' ) => true ),
				),
				array(
					'type' => 'textfield',
					'holder' => 'div',
					'group' => 'Ambassadors',
					'heading' => __( 'Parent Campaign ID', 'borntogive-vc'),
					'param_name' => 'parent',
					'description' => __( 'Show only the child campaigns of a specific parent campaign.', 'borntogive-vc' )
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Fundraiser Type', 'borntogive-vc' ),
					'group' => 'Ambassadors',
					'param_name' => 'fundraiser_type',
					'value' => array( __( 'Select', 'borntogive-vc' ) => '', __( 'Individual', 'borntogive-vc' ) => 'individual', __( 'Team', 'borntogive-vc' ) => 'team', __( 'Team Member', 'borntogive-vc' ) => 'team-member', __( 'Fundraiser', 'borntogive-vc' ) => 'fundraiser', __( 'Personal', 'borntogive-vc' ) => 'personal') ,
					'description' => __( 'Show only fundraiser campaigns of a specific type.', 'borntogive-vc' ),
					'param_holder_class' => 'vc_colored-dropdown',
				),
			)
		) 
	);
}
/*Code to create element of Events for visual composer
=====================================================*/
add_action( 'vc_before_init', 'borntogive_events_element' );
	function borntogive_events_element() {
		$terms = array();
		$team_cats = get_terms('event-category');
		if(!is_wp_error($team_cats))
		{
			foreach($team_cats as $cat)
			{ 
				$terms[] = array('value'=>$cat->term_id, 'label'=>$cat->name); 
			}
		}
		vc_map( array(
			"name" => __( "BORNTOGIVE Events", "borntogive-vc" ),
			"base" => "borntogive_events",
			'icon' => 'icon-wpb-vc_icon',
			"category" => __( "Born To Give", "borntogive-vc"),
			"class" => "",
			"params" => array(
				array(
					'type' => 'dropdown',
					'heading' => __( 'Event Type', 'borntogive-vc' ),
					'param_name' => 'event_type',
					'value' => array( __( 'Future Events', 'borntogive-vc' ) => 'future', __( 'Past Events', 'borntogive-vc' ) => 'past' ) ,
					'description' => __( 'Select event type.', 'borntogive-vc' ),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Event View', 'borntogive-vc' ),
					'param_name' => 'event_view',
					'value' => array( __( 'List', 'borntogive-vc' ) => 'list', __( 'Grid', 'borntogive-vc' ) => 'grid' ) ,
					'description' => __( 'Select event view.', 'borntogive-vc' ),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Grid column', 'borntogive-vc' ),
					'param_name' => 'event_grid_column',
					'value' => array( __( 'One Column', 'borntogive-vc' ) => 12, __( 'Two Columns', 'borntogive-vc' ) => 6, __( 'Three Columns', 'borntogive-vc' ) => 4, __( 'Four Columns', 'borntogive-vc' ) => 3 ) ,
					'description' => __( 'Select columns of grid.', 'borntogive-vc' ),
					'param_holder_class' => 'vc_colored-dropdown',
					'dependency' => array(
						'element' => 'event_view',
						'value' => array( 'grid' ),
					),
				),
				array(
					'type' => 'checkbox',
					'heading' => __( 'Show filters?', 'borntogive-vc' ),
					'param_name' => 'event_filters',
					'description' => __( 'Show filters for the events grid.', 'borntogive-vc' ),
					'value' => array( __( 'Yes', 'borntogive-vc' ) => true ),
					'dependency' => array(
						'element' => 'event_view',
						'value' => array( 'grid' ),
					),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Show event categories?', 'borntogive-vc' ),
					'param_name' => 'show_events_cats',
					'value' => array( __( 'No', 'borntogive-vc' ) => '0', __( 'Yes', 'borntogive-vc' ) => '1') ,
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Show event tags?', 'borntogive-vc' ),
					'param_name' => 'show_events_tags',
					'value' => array( __( 'No', 'borntogive-vc' ) => '0', __( 'Yes', 'borntogive-vc' ) => '1') ,
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Show excerpt?', 'borntogive-vc' ),
					'param_name' => 'show_events_excerpt',
					'value' => array( __( 'Yes', 'borntogive-vc' ) => '1', __( 'No', 'borntogive-vc' ) => '0') ,
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' 			=> 'autocomplete',
					'class' 		=> '',
					'heading' 		=> esc_html__( 'Category Filter', 'borntogive-vc' ),
					'description' => __( 'Enter/Select event categories from which you would like to show events', 'borntogive-vc' ),
					'description' => __( 'Show events by specific categories. Search and enter by typing category names.', 'borntogive-vc' ),
					'param_name' 	=> 'event_terms',
					'settings'		=> array( 'values' => $terms,'multiple' => true,
					'min_length' => 1,
					'groups' => true,
					// In UI show results grouped by groups, default false
					'unique_values' => true,
					// In UI show results except selected. NB! You should manually check values in backend, default false
					'display_inline' => true,
					// In UI show results inline view, default false (each value in own line)
					'delay' => 500,
					// delay for search. default 500
					'auto_focus' => true, ),
				),
				array(
					'type' => 'textfield',
					'holder' => 'div',
					'class' => '',
					'heading' => __( 'Number of events', 'borntogive-vc'),
					'param_name' => 'event_number',
					'value' => 5,
					'description' => __( 'Insert number of events to show per page.', 'borntogive-vc')
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Image size', 'borntogive-vc' ),
					'param_name' => 'img_size',
					'value' => 'thumbnail',
					'description' => __( 'Enter image size. Example: thumbnail, medium, large, full or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size.', 'borntogive-vc' ),
				),
				array(
					'type' => 'checkbox',
					'heading' => __( 'Show pagination?', 'borntogive-vc' ),
					'param_name' => 'event_pagination',
					'description' => __( 'Show pagination for the events list.', 'borntogive-vc' ),
					'value' => array( __( 'Yes', 'borntogive-vc' ) => true ),
				),
			)
		) 
	);
}
/*Code to create element of Gallery for visual composer
=====================================================*/
add_action( 'vc_before_init', 'borntogive_gallery_element' );
	function borntogive_gallery_element() {
		$terms = array();
		$team_cats = get_terms('gallery-category');
		if(!is_wp_error($team_cats))
		{
			foreach($team_cats as $cat)
			{ 
				$terms[] = array('value'=>$cat->term_id, 'label'=>$cat->name); 
			}
		}
		vc_map( array(
			"name" => __( "BORNTOGIVE Gallery", "borntogive-vc" ),
			"base" => "borntogive_gallery",
			'icon' => 'icon-wpb-vc_icon',
			"category" => __( "Born To Give", "borntogive-vc"),
			"class" => "",
			// 'admin_enqueue_js' => array(get_template_directory_uri().'/vc_extend/bartag.js'),
			// 'admin_enqueue_css' => array(get_template_directory_uri().'/vc_extend/bartag.css'),
			"params" => array(
				array(
					'type' => 'dropdown',
					'heading' => __( 'Gallery Type', 'borntogive-vc' ),
					'param_name' => 'gallery_type',
					'value' => array( __( 'Frame', 'borntogive-vc' ) => 'frame', __( 'Grid', 'borntogive-vc' ) => 'grid' ) ,
					'description' => __( 'Select gallery type.', 'borntogive-vc' ),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'textfield',
					'holder' => 'div',
					'class' => '',
					'heading' => __( 'Title', 'borntogive-vc'),
					'param_name' => 'gallery_title',
					'value' => '',
					'description' => __( 'Insert Gallery title.', 'borntogive-vc'),
					'dependency' => array(
						'element' => 'gallery_type',
						'value' => array( 'frame' ),
					),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Grid Column', 'borntogive-vc' ),
					'param_name' => 'gallery_grid_column',
					'value' => array( __( 'One Column', 'borntogive-vc' ) => 12, __( 'Two Columns', 'borntogive-vc' ) => 6, __( 'Three Column', 'borntogive-vc' ) => 4, __( 'Four Column', 'borntogive-vc' ) => 3 ) ,
					'description' => __( 'Select Columns of grid.', 'borntogive-vc' ),
					'param_holder_class' => 'vc_colored-dropdown',
					'dependency' => array(
						'element' => 'gallery_type',
						'value' => array( 'grid' ),
					),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Gallery Caption', 'borntogive-vc' ),
					'param_name' => 'gallery_caption',
					'value' => array( __( 'Yes', 'borntogive-vc' ) => '', __( 'No', 'borntogive-vc' ) => '0') ,
					'description' => __( 'Show gallery caption?', 'borntogive-vc' ),
					'param_holder_class' => 'vc_colored-dropdown',
					'dependency' => array(
						'element' => 'gallery_type',
						'value' => array( 'grid' ),
					),
				),
				array(
					'type' => 'checkbox',
					'heading' => __( 'Show Filters?', 'borntogive-vc' ),
					'param_name' => 'gallery_filters',
					'description' => __( 'Show filters for Gallery.', 'borntogive-vc' ),
					'value' => array( __( 'Yes', 'borntogive-vc' ) => true ),
				),
				array(
					'type' 			=> 'autocomplete',
					'class' 		=> '',
					'heading' 		=> esc_html__( 'Gallery Categories', 'borntogive-vc' ),
					'description' => __( 'Show gallery posts by specific categories. Search and enter by typing category names.', 'borntogive-vc' ),
					'param_name' 	=> 'gallery_terms',
					'settings'		=> array( 'values' => $terms,'multiple' => true,
					'min_length' => 1,
					'groups' => true,
					// In UI show results grouped by groups, default false
					'unique_values' => true,
					// In UI show results except selected. NB! You should manually check values in backend, default false
					'display_inline' => true,
					// In UI show results inline view, default false (each value in own line)
					'delay' => 500,
					// delay for search. default 500
					'auto_focus' => true, ),
				),
				array(
					'type' => 'textfield',
					'holder' => 'div',
					'class' => '',
					'heading' => __( 'Number of Gallery', 'borntogive-vc' ),
					'param_name' => 'gallery_number',
					'value' => 5,
					'description' => __( 'Insert number of gallery to show per page.', 'borntogive-vc' )
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Image size', 'borntogive-vc' ),
					'param_name' => 'img_size',
					'value' => 'thumbnail',
					'description' => __( 'Enter image size. Example: thumbnail, medium, large, full or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size.', 'borntogive-vc' ),
				),
				array(
					'type' => 'checkbox',
					'heading' => __( 'Show Pagination?', 'borntogive-vc' ),
					'param_name' => 'gallery_pagination',
					'description' => __( 'Show pagination for Posts.', 'borntogive-vc' ),
					'value' => array( __( 'Yes', 'borntogive-vc' ) => true ),
				),
			)
		) 
	);
}
/*Code to create element of Testimonial for visual composer
=====================================================*/
add_action( 'vc_before_init', 'borntogive_testimonial_element' );
	function borntogive_testimonial_element() {
		$terms = array();
		$testi_cats = get_terms('testimonial-category');
		if(!is_wp_error($testi_cats))
		{
			foreach($testi_cats as $cat)
			{ 
				$terms[] = array('value'=>$cat->term_id, 'label'=>$cat->name); 
			}
		}
		vc_map( array(
			"name" => __( "BORNTOGIVE Testimonial", "borntogive-vc" ),
			"base" => "borntogive_testimonial",
			'icon' => 'icon-wpb-vc_icon',
			"category" => __( "Born To Give", "borntogive-vc"),
			"class" => "",
			"params" => array(
				 array(
					'type' => 'dropdown',
					'heading' => __( 'Testimonial View', 'borntogive-vc' ),
					'param_name' => 'testimonial_view',
					'value' => array( __( 'Type 1', 'borntogive-vc' ) => '1', __( 'Type 2', 'borntogive-vc' ) => '2' ) ,
					'description' => __( 'Select testimonial view.', 'borntogive-vc' ),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Carousel Column', 'borntogive-vc' ),
					'param_name' => 'testimonial_carousel_column',
					'value' => array( __( 'One Column', 'borntogive-vc' ) => '1',__( 'Two Columns', 'borntogive-vc' ) => '2', __( 'Three Columns', 'borntogive-vc' ) => '3', __( 'Four Columns', 'borntogive-vc' ) => '4', __( 'Five Columns', 'borntogive-vc' ) => '5', __( 'Six Column', 'borntogive-vc' ) => '6' ) ,
					'description' => __( 'Select columns for the carousel.', 'borntogive-vc' ),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'checkbox',
					'heading' => esc_html__('Show carousel next/prev arrows?', 'borntogive-vc'),
					'param_name' => 'carousel_arrows',
					'value' => array( esc_html__( 'Yes', 'borntogive-vc' ) => true ),
					'std' => 1,
				),
				array(
					'type' => 'checkbox',
					'heading' => esc_html__('Show carousel pagination?', 'borntogive-vc'),
					'param_name' => 'carousel_pagi',
					'value' => array( esc_html__( 'Yes', 'borntogive-vc' ) => true ),
					'std' => 0,
				),
				array(
					'type' => 'checkbox',
					'heading' => esc_html__('Autoplay Carousel?', 'borntogive-vc'),
					'param_name' => 'carousel_autoplay',
					'value' => array( esc_html__( 'Yes', 'borntogive-vc' ) => true ),
					'std' => 0,
				),
				array(
					'type' 			=> 'autocomplete',
					'class' 		=> '',
					'heading' 		=> esc_html__( 'Testimonial Categories', 'borntogive-vc' ),
					'description' => __( 'Show testimonials by specific categories. Search and enter by typing category names.', 'borntogive-vc' ),
					'param_name' 	=> 'testimonial_terms',
					'settings'		=> array( 'values' => $terms,'multiple' => true,
					'min_length' => 1,
					'groups' => true,
					// In UI show results grouped by groups, default false
					'unique_values' => true,
					// In UI show results except selected. NB! You should manually check values in backend, default false
					'display_inline' => true,
					// In UI show results inline view, default false (each value in own line)
					'delay' => 500,
					// delay for search. default 500
					'auto_focus' => true, ),
				),
				array(
					 'type' => 'textfield',
					 'holder' => 'div',
					 'class' => '',
					 'heading' => __( 'Number of testimonials', 'borntogive-vc' ),
					 'param_name' => 'testimonial_number',
					 'value' => 5,
					 'description' => __( 'Insert number of testimonials to show per page.', 'borntogive-vc' )
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Image size', 'borntogive-vc' ),
					'param_name' => 'img_size',
					'value' => '',
					'description' => __( 'Enter image size. Example: thumbnail, medium, large, full or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size.', 'borntogive-vc' ),
				),
			)
		) 
	);
}
/*Code to create element of Posts for visual composer
=====================================================*/
add_action( 'vc_before_init', 'borntogive_post_element' );
	function borntogive_post_element() {
		$terms = array();
		$posts_cats = get_terms('category');
		if(!is_wp_error($posts_cats))
		{
			foreach($posts_cats as $cat)
			{ 
				$terms[] = array('value'=>$cat->term_id, 'label'=>$cat->name); 
			}
		}
		$authors=get_users();
		//get all users list
		foreach($authors as $author){
			$authors_list[] = array('value'=>$author->data->ID, 'label'=>$author->data->display_name); 
		}
		vc_map( array(
			"name" => __( "BORNTOGIVE Posts", "borntogive-vc" ),
			"base" => "borntogive_post",
			'icon' => 'icon-wpb-vc_icon',
			"category" => __( "Born To Give", "borntogive-vc"),
			"class" => "",
			"params" => array(
				array(
					'type' => 'dropdown',
					'heading' => __( 'Post View', 'borntogive-vc' ),
					'param_name' => 'post_view',
					'value' => array( __( 'List', 'borntogive-vc' ) => 'list', __( 'Grid', 'borntogive-vc' ) => 'grid', __( 'Grid with Carousel', 'borntogive-vc' ) => 'carousel' ) ,
					'description' => __( 'Select post view.', 'borntogive-vc' ),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Grid Column', 'borntogive-vc' ),
					'param_name' => 'post_grid_column',
					'value' => array( __( 'Two Columns', 'borntogive-vc' ) => 6, __( 'Three Columns', 'borntogive-vc' ) => 4, __( 'Four Columns', 'borntogive-vc' ) => 3) ,
					'description' => __( 'Select Columns of grid.', 'borntogive-vc' ),
					'param_holder_class' => 'vc_colored-dropdown',
					'dependency' => array(
						'element' => 'post_view',
						'value' => array( 'grid','carousel' ),
					),
				),
				array(
					'type' => 'checkbox',
					'heading' => esc_html__('Show carousel next/prev arrows?', 'borntogive-vc'),
					'param_name' => 'carousel_arrows',
					'value' => array( esc_html__( 'Yes', 'borntogive-vc' ) => true ),
					'std' => 0,
					'dependency' => array(
						'element' => 'post_view',
						'value' => array( 'carousel' ),
					),
				),
				array(
					'type' => 'checkbox',
					'heading' => esc_html__('Show carousel pagination?', 'borntogive-vc'),
					'param_name' => 'carousel_pagi',
					'value' => array( esc_html__( 'Yes', 'borntogive-vc' ) => true ),
					'std' => 1,
					'dependency' => array(
						'element' => 'post_view',
						'value' => array( 'carousel' ),
					),
				),
				array(
					'type' => 'checkbox',
					'heading' => esc_html__('Autoplay Carousel?', 'borntogive-vc'),
					'param_name' => 'carousel_autoplay',
					'value' => array( esc_html__( 'Yes', 'borntogive-vc' ) => true ),
					'std' => 0,
					'dependency' => array(
						'element' => 'post_view',
						'value' => array( 'carousel' ),
					),
				),
				array(
					'type' => 'checkbox',
					'heading' => __( 'Show Tags?', 'borntogive-vc' ),
					'param_name' => 'posts_tags',
					'description' => __( 'Show post tags in grid view.', 'borntogive-vc' ),
					'value' => array( __( 'Yes', 'borntogive-vc' ) => true ),
					'std' => 1,
					'dependency' => array(
						'element' => 'post_view',
						'value' => array( 'grid'),
					),
				),
				array(
					'type' => 'checkbox',
					'heading' => __( 'Show Post Date?', 'borntogive-vc' ),
					'param_name' => 'posts_post_date',
					'value' => array( __( 'Yes', 'borntogive-vc' ) => true ),
					'std' => 1,
				),
				array(
					'type' => 'checkbox',
					'heading' => __( 'Show Post Author?', 'borntogive-vc' ),
					'param_name' => 'posts_post_author',
					'value' => array( __( 'Yes', 'borntogive-vc' ) => true ),
					'std' => 1,
				),
				array(
					'type' => 'checkbox',
					'heading' => __( 'Show Post Categories?', 'borntogive-vc' ),
					'param_name' => 'posts_categories',
					'value' => array( __( 'Yes', 'borntogive-vc' ) => true ),
					'std' => 1,
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Content', 'borntogive-vc' ),
					'param_name' => 'post_content',
					'value' => array( __( 'Excerpt', 'borntogive-vc' ) => 2, __( 'Content', 'borntogive-vc' ) => 1) ,
					'description' => __( 'If content is chosen then the full content of posts will be shown until the MORE tag which can be inserted in the post content.', 'borntogive-vc' ),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'textfield',
					'class' => '',
					'heading' => __( 'Number of words to show as excerpt', 'borntogive-vc' ),
					'param_name' => 'post_excerpt_length',
					'value' => 30,
					'description' => __( 'This is the number of words that will be shown for the post excerpt. This text comes from the post content or excerpt field.', 'borntogive-vc' ),
					'dependency' => array(
						'element' => 'post_content',
						'value' => array('2'),
					),
				),
				array(
					'type' => 'textfield',
					'class' => '',
					'heading' => __( 'Excerpt closing text', 'borntogive-vc' ),
					'param_name' => 'post_closing_text',
					'value' => '...',
					'description' => __( 'Enter the text that you would like to show just after the limited content ends. Default is ...', 'borntogive-vc' ),
					'dependency' => array(
						'element' => 'post_content',
						'value' => array('2'),
					),
				),
				array(
					'type' => 'autocomplete',
					'class' => '',
					'heading' => __( 'Post Categories', 'borntogive-vc' ),
					'param_name' => 'post_terms',
					'description' => __( 'Show posts by specific categories. Search and enter by typing category names.', 'borntogive-vc' ),
					'settings'		=> array( 'values' => $terms,'multiple' => true,
					'min_length' => 1,
					'groups' => true,
					// In UI show results grouped by groups, default false
					'unique_values' => true,
					// In UI show results except selected. NB! You should manually check values in backend, default false
					'display_inline' => true,
					// In UI show results inline view, default false (each value in own line)
					'delay' => 500,
					// delay for search. default 500
					'auto_focus' => true, ),
				),
				array(
					'type' => 'autocomplete',
					'class' => '',
					'heading' => __( 'Post Authors', 'borntogive-vc' ),
					'param_name' => 'post_authors',
					'description' => __( 'Show posts by specific users/authors. Search and enter by typing their names.', 'borntogive-vc' ),
					'settings'		=> array( 'values' => $authors_list,'multiple' => true,
					'min_length' => 1,
					'groups' => true,
					// In UI show results grouped by groups, default false
					'unique_values' => true,
					// In UI show results except selected. NB! You should manually check values in backend, default false
					'display_inline' => true,
					// In UI show results inline view, default false (each value in own line)
					'delay' => 500,
					// delay for search. default 500
					'auto_focus' => true, ),
				),
				array(
					'type' => 'textfield',
					'class' => '',
					'heading' => __( 'Number of posts', 'borntogive-vc' ),
					'param_name' => 'post_number',
					'value' => 5,
					'description' => __( 'Insert number of posts to show per page.', 'borntogive-vc' )
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Image size', 'borntogive-vc' ),
					'param_name' => 'img_size',
					'value' => 'thumbnail',
					'description' => __( 'Enter image size. Example: thumbnail, medium, large, full or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size.', 'borntogive-vc' ),
				),
				array(
					'type' => 'checkbox',
					'heading' => __( 'Show pagination?', 'borntogive-vc' ),
					'param_name' => 'posts_pagination',
					'description' => __( 'Show pagination for posts.', 'borntogive-vc' ),
					'value' => array( __( 'Yes', 'borntogive-vc' ) => true ),
				),
			)
		) 
	);
}
/*Code to create element of Grid Container for visual composer
=====================================================*/
	function borntogive_gridcontainer_element() {
		vc_map( array(
			"name" => __( "BORNTOGIVE Grid Container", "borntogive-vc" ),
			"base" => "borntogive_gridcontainer",
			'icon' => 'icon-wpb-vc_icon',
			"category" => __( "Born To Give", "borntogive-vc"),
			"class" => "",
			// 'admin_enqueue_js' => array(get_template_directory_uri().'/vc_extend/bartag.js'),
			// 'admin_enqueue_css' => array(get_template_directory_uri().'/vc_extend/bartag.css'),
			"params" => array(
				array(
					'type' => 'attach_image',
					 'holder' => 'div',
					 'class' => 'page-header-container',
					 'heading' => __( 'Background Image', 'borntogive-vc' ),
					 'param_name' => 'grid_image',
					 'value' => '',
					 'description' => __( 'Choose a grid image', 'borntogive-vc' )
				),
				array(
					'type' => 'textarea',
					'holder' => 'div',
					'class' => '',
					'heading' => __( 'Grid Description', 'borntogive-vc' ),
					'param_name' => 'grid_description',
					'value' => __( '' ),
					'description' => __( 'Enter grid description.', 'borntogive-vc' )
				),
				array(
					'type' => 'textfield',
					'holder' => 'div',
					'class' => '',
					'heading' => __( 'Grid URL', 'borntogive-vc' ),
					'param_name' => 'grid_url',
					'value' => '',
					'description' => __( 'Enter grid URL.', 'borntogive-vc' )
				),
			)
		) 
	);
}

/*Code to create element of Team for visual composer
=====================================================*/
add_action( 'vc_before_init', 'borntogive_team_element' );
	function borntogive_team_element() {
		$terms = array();
		$team_cats = get_terms('team-category');
		if(!is_wp_error($team_cats))
		{
			foreach($team_cats as $cat)
			{ 
				$terms[] = array('value'=>$cat->term_id, 'label'=>$cat->name); 
			}
		}
		vc_map( array(
			"name" => __( "BORNTOGIVE Team", "borntogive-vc" ),
			"base" => "borntogive_team",
			'icon' => 'icon-wpb-vc_icon',
			"category" => __( "Born To Give", "borntogive-vc"),
			"class" => "",
			"params" => array(
				array(
					'type' => 'dropdown',
					'heading' => __( 'Team View', 'borntogive-vc' ),
					'param_name' => 'team_carousel_style',
					'value' => array( __( 'Grid', 'borntogive-vc' ) => 'grid', __( 'List', 'borntogive-vc' ) => 'list') ,
					'description' => __( 'Select view type for team.', 'borntogive-vc' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Column Style', 'borntogive-vc' ),
					'param_name' => 'team_carousel_column',
					'value' => array( __( 'One Column', 'borntogive-vc' ) => 1, __( 'Two Columns', 'borntogive-vc' ) => 2, __( 'Three Columns', 'borntogive-vc' ) => 3, __( 'Four Columns', 'borntogive-vc' ) => 4, __( 'Five Columns', 'borntogive-vc' ) => 5, __( 'Six Columns', 'borntogive-vc' ) => 6 ) ,
					'description' => __( 'Select Columns of carousel/grid.', 'borntogive-vc' ),
					'param_holder_class' => 'vc_colored-dropdown',
					'dependency' => array(
						'element' => 'team_carousel_style',
						'value' => array('grid'),
					),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Style', 'borntogive-vc' ),
					'param_name' => 'team_design_style',
					'value' => array( __( 'Standard', 'borntogive-vc' ) => '', __( 'Round Images', 'borntogive-vc' ) => 'team-rounded-style') ,
					'description' => __( 'Select style for team. If round images is chosen then enter the image size(option at the botton) to some square dimension like 400x400 for best results.', 'borntogive-vc' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Activate Carousel?', 'borntogive-vc' ),
					'param_name' => 'team_carousel',
					'description' => __( 'Activate carousel for team.', 'borntogive-vc' ),
					'value' => array( __( 'Yes', 'js_composer' ) => '1', __( 'No', 'borntogive-vc' ) => '0') ,
					'param_holder_class' => 'vc_colored-dropdown',
					'dependency' => array(
						'element' => 'team_carousel_style',
						'value' => array('grid'),
					),
				),
				array(
					'type' => 'checkbox',
					'heading' => esc_html__('Show carousel next/prev arrows?', 'borntogive-vc'),
					'param_name' => 'carousel_arrows',
					'value' => array( esc_html__( 'Yes', 'borntogive-vc' ) => true ),
					'std' => 1,
					'dependency' => array(
						'element' => 'team_carousel',
						'value' => array( '1' ),
					),
				),
				array(
					'type' => 'checkbox',
					'heading' => esc_html__('Show carousel pagination?', 'borntogive-vc'),
					'param_name' => 'carousel_pagi',
					'value' => array( esc_html__( 'Yes', 'borntogive-vc' ) => true ),
					'std' => 0,
					'dependency' => array(
						'element' => 'team_carousel',
						'value' => array( '1' ),
					),
				),
				array(
					'type' => 'checkbox',
					'heading' => esc_html__('Autoplay Carousel?', 'borntogive-vc'),
					'param_name' => 'carousel_autoplay',
					'value' => array( esc_html__( 'Yes', 'borntogive-vc' ) => true ),
					'std' => 0,
					'dependency' => array(
						'element' => 'team_carousel',
						'value' => array( '1' ),
					),
				),
				array(
					'type' => 'textfield',
					'holder' => 'div',
					'class' => '',
					'heading' => __( 'Number of team members', 'borntogive-vc' ),
					'param_name' => 'team_number',
					'value' => 3,
					'description' => __( 'Insert number of team members to show.', 'borntogive-vc' )
				),
				array(
					'type' => 'checkbox',
					'heading' => __( 'Link to details page?', 'borntogive-vc' ),
					'param_name' => 'team_linked',
					'description' => __( 'Check to enable link on thumbnail and title to open details page.', 'borntogive-vc' ),
					'value' => array( __( 'Yes', 'borntogive-vc' ) => true ),
					'std' => 1
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Show details?', 'borntogive-vc' ),
					'param_name' => 'team_details',
					'description' => __( 'Show team member details like social links.', 'borntogive-vc' ),
					'value' => array( __( 'Yes', 'borntogive-vc' ) => '1', __( 'No', 'borntogive-vc' ) => '0' ) ,
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Content', 'borntogive-vc' ),
					'param_name' => 'team_content',
					'value' => array( __( 'Excerpt', 'borntogive-vc' ) => 2, __( 'Content', 'borntogive-vc' ) => 1) ,
					'description' => __( 'If content is chosen then the full content of team posts will be shown until the MORE tag which can be inserted in the post content.', 'borntogive-vc' ),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'textfield',
					'holder' => 'div',
					'class' => '',
					'heading' => __( 'Number of words to show as excerpt', 'borntogive-vc' ),
					'param_name' => 'team_excerpt',
					'value' => 30,
					'description' => __( 'This is the number of words that will be shown for the team member content. This text comes from the team post content or excerpt field.', 'borntogive-vc' ),
					'dependency' => array(
						'element' => 'team_content',
						'value' => array('2'),
					),
				),
				array(
					'type' => 'textfield',
					'holder' => 'div',
					'class' => '',
					'heading' => __( 'Excerpt closing text', 'borntogive-vc' ),
					'param_name' => 'team_closing_text',
					'value' => '...',
					'description' => __( 'Enter the text that you would like to show just after the limited content ends. Default is ...', 'borntogive-vc' ),
					'dependency' => array(
						'element' => 'team_content',
						'value' => array('2'),
					),
				),
				array(
					'type' => 'textfield',
					'holder' => 'div',
					'class' => '',
					'heading' => __( 'Read more link text', 'borntogive-vc' ),
					'param_name' => 'team_more_text',
					'value' => '',
					'description' => __( 'Enter the link text which will be shown after the limited content/excerpt.', 'borntogive-vc' ),
					'dependency' => array(
						'element' => 'team_content',
						'value' => array('2'),
					),
				),
				array(
					'type' 			=> 'autocomplete',
					'class' 		=> '',
					'heading' 		=> esc_html__( 'Team Categories', 'borntogive-vc' ),
					'description' => __( 'Show team members by specific categories. Search and enter by typing category names.', 'borntogive-vc' ),
					'param_name' 	=> 'team_terms',
					'settings'		=> array( 'values' => $terms,'multiple' => true,
					'min_length' => 1,
					'groups' => true,
					// In UI show results grouped by groups, default false
					'unique_values' => true,
					// In UI show results except selected. NB! You should manually check values in backend, default false
					'display_inline' => true,
					// In UI show results inline view, default false (each value in own line)
					'delay' => 500,
					// delay for search. default 500
					'auto_focus' => true, ),
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Image size', 'borntogive-vc' ),
					'param_name' => 'img_size',
					'value' => 'full',
					'description' => __( 'Enter image size. Example: thumbnail, medium, large, full or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size.', 'borntogive-vc' ),
				),
			)
		) 
	);
}
/*Code to create element of Featured Link for visual composer
=====================================================*/
add_action( 'vc_before_init', 'borntogive_featured_link' );
	function borntogive_featured_link() {
		vc_map( array(
			"name" => __( "BORNTOGIVE Featured Link", "borntogive-vc" ),
			"base" => "borntogive_feat_link",
			'icon' => 'icon-wpb-vc_icon',
			"category" => __( "Born To Give", "borntogive-vc"),
			"class" => "",
			"params" => array(
				array(
					'type' => 'textfield',
					'holder' => 'div',
					'class' => '',
					'heading' => __( 'Title', 'borntogive-vc' ),
					'param_name' => 'feat_title',
					'value' => '',
					'description' => __( 'Insert title for the featured link.', 'borntogive-vc' )
				),
				array(
					'type' => 'textfield',
					'holder' => 'div',
					'class' => '',
					'heading' => __( 'Sub title', 'borntogive-vc' ),
					'param_name' => 'feat_head_line',
					'value' => __( '' ),
					'description' => __( 'Insert sub title for the featured link.', 'borntogive-vc' )
				),
				array(
					'type' => 'textfield',
					'holder' => 'div',
					'class' => '',
					'heading' => __( 'URL', 'borntogive-vc' ),
					'param_name' => 'feat_url',
					'value' => '',
					'description' => __( 'Insert URL for the featured link.', 'borntogive-vc' )
				),
				array(
					'type' => 'checkbox',
					'heading' => __( 'Open in new tab?', 'borntogive-vc' ),
					'param_name' => 'feat_url_target',
					'description' => __( 'Check to open featured link URL in a new tab or window.', 'borntogive-vc' ),
					'value' => array( __( 'Yes', 'borntogive-vc' ) => true ),
					'std' => 0,
				),
				array(
					'type' => 'colorpicker',
					'class' => '',
					'heading' => __( 'Background Color', 'borntogive-vc' ),
					'param_name' => 'feat_custom_bg',
					'description' => __( 'Select custom background color for the featured link block. By default it will be a darker shade of your selected theme color scheme/custom color at Theme Options.', 'borntogive-vc' )
				),
				array(
					'type' => 'colorpicker',
					'class' => '',
					'heading' => __( 'Text Color', 'borntogive-vc' ),
					'param_name' => 'feat_custom_text',
					'description' => __( 'Select custom text color for the featured link block. By default it is #ffffff.', 'borntogive-vc' )
				),
			)
		) 
	);
}
/*Code to create element of Featured Text for visual composer
=====================================================*/
add_action( 'vc_before_init', 'borntogive_featured_text' );
	function borntogive_featured_text() {
		vc_map( array(
			"name" => __( "BORNTOGIVE Featured Text", "borntogive-vc" ),
			"base" => "borntogive_feat_text",
			'icon' => 'icon-wpb-vc_icon',
			"category" => __( "Born To Give", "borntogive-vc"),
			"class" => "",
			"params" => array(
				array(
					'type' => 'textfield',
					'holder' => 'div',
					'class' => '',
					'heading' => __( 'Head Line', 'borntogive-vc' ),
					'param_name' => 'feat_head',
					'value' => __( '' ),
					'description' => __( 'Insert head line for featured text."," separated.', 'borntogive-vc' )
				),
				array(
					'type' => 'textfield',
					'holder' => 'div',
					'class' => '',
					'heading' => __( 'Content', 'borntogive-vc' ),
					'param_name' => 'feat_content',
					'value' => '',
					'description' => __( 'Insert content for featured text.', 'borntogive-vc' )
				 ),
			)
		) 
	);
}
/*Code to create element of Google Map for visual composer
=====================================================*/
add_action( 'vc_before_init', 'borntogive_google_maps' );
	function borntogive_google_maps() {
		vc_map( array(
			"name" => esc_html__( "BORNTOGIVE Google Map", "borntogive-vc" ),
			"base" => "borntogive_vc_maps",
			'icon' => 'icon-wpb-vc_icon',
			"category" => esc_html__( "Born To Give", "borntogive-vc"),
			"params" => array(
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'API Key', 'borntogive-vc' ),
					'param_name' => 'map_api',
					'description' => esc_html__( 'Enter your API key. Your map may not function correctly without one. Please ensure you have enabled the Geocoding API in the Google APIs Dashboard.', 'borntogive-vc' ).'<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank" rel="noopener noreferrer">'.esc_html__('Get an API','borntogive-vc').'</a>',
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Width', 'borntogive-vc' ),
					'param_name' => 'map_width',
					'description' => esc_html__( 'Width(px) of your map. If left blank then it will take 100% area of the container.', 'borntogive-vc' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Height', 'borntogive-vc' ),
					'param_name' => 'map_height',
					'description' => esc_html__( 'Height(px) of your map. Default is 300px.', 'borntogive-vc' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Zoom Level', 'borntogive-vc' ),
					'param_name' => 'map_zoom',
					'description' => esc_html__( 'A value from 0 (the world) to 21 (street level).', 'borntogive-vc' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Allow dragging the map to move it around.', 'borntogive-vc' ),
					'param_name' => 'map_drag',
					'value' => array(
						__( 'Yes', 'borntogive-vc' ) => 'yes',
						__( 'No', 'borntogive-vc' ) => 'no',
					),
					'param_holder_class' => 'vc_colored-dropdown',
					'std' => 'yes',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Scroll to zoom', 'borntogive-vc' ),
					'description' => esc_html__( 'Allow scrolling over the map to zoom in or out.', 'borntogive-vc' ),
					'param_name' => 'map_scroll',
					'value' => array(
						__( 'Yes', 'borntogive-vc' ) => 'yes',
						__( 'No', 'borntogive-vc' ) => 'no',
					),
					'param_holder_class' => 'vc_colored-dropdown',
					'std' => 'no',
					'dependency' => array(
						'element' => 'map_drag',
						'value' => 'yes',
					),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Address', 'borntogive-vc' ),
					'param_name' => 'marker_address',
					'description' => esc_html__( 'The name of a place, town, city, or even a country. Can be an exact address too.', 'borntogive-vc' ),
					'admin_label' => true,
				),
				array(
					'type' => 'attach_image',
					'heading' => esc_html__( 'Marker Icon', 'borntogive-vc' ),
					'description' => esc_html__( 'Replaces the default map marker with your own image.', 'borntogive-vc' ),
					'param_name' => 'marker_icon',
				),
				array(
					'type' => 'textarea_html',
					'heading' => esc_html__( 'Info window text', 'borntogive-vc' ),
					'param_name' => 'content',
					'description' => esc_html__( 'Add text to show on the tooltip of the marker.', 'borntogive-vc' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Info window max width', 'borntogive-vc' ),
					'param_name' => 'marker_info_width',
					'description' => esc_html__( 'Enter maximum width for info window. Enter in px for example 150px', 'borntogive-vc' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'When should Info Windows be displayed?', 'borntogive-vc' ),
					'param_name' => 'marker_info_show',
					'value' => array(
						__( 'Click', 'borntogive-vc' ) => 'click',
						__( 'Mouse over', 'borntogive-vc' ) => 'mouseover',
						__( 'Always', 'borntogive-vc' ) => 'always',
					),
					'param_holder_class' => 'vc_colored-dropdown',
					'std' => 'click',
				),
				array(
					'type'       => 'css_editor',
					'heading'    => esc_html__( 'Css', 'borntogive-vc' ),
					'param_name' => 'css',
					'group'      => esc_html__( 'Design options', 'borntogive-vc' )
				)
			)
		) 
	);
}