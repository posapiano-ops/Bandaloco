<?php
// Way to set menu, import revolution slider, set blog page and set home page
if ( !function_exists( 'imi_wbc_extended' ) ) :
	
	add_action( 'wbc_importer_after_content_import', 'imi_wbc_extended', 10, 4 );

	function imi_wbc_extended( $demo_active_import , $demo_directory_path, $import_sliders, $import_theme_opts ) {

		reset( $demo_active_import );
		$current_key = key( $demo_active_import );

		if ( isset( $demo_active_import[$current_key]['directory'] ) && !empty( $demo_active_import[$current_key]['directory'] ) ) :

			


			/**
			 * Import theme options
			 *
			 * @since 1.0.0
			 */
			if ( $import_theme_opts ) :
				// Get file contents and decode
				$file = $demo_directory_path . 'theme-options.txt';
				$data = file_get_contents( $file );
				$data = json_decode( $data, true );
				$data = maybe_unserialize( $data );
				update_option( 'borntogive_options', $data );
			endif;


			$main_menu = get_term_by( 'name', 'Primary Menu', 'nav_menu' );
			$footer_menu = get_term_by( 'name', 'Footer Menu', 'nav_menu' );
			if ( isset( $main_menu->term_id ) ) {
				set_theme_mod( 'nav_menu_locations', array(
					'primary-menu' => $main_menu->term_id,
					'footer-menu' => $footer_menu->term_id
				));
			}
		
			
		
			/**
			 * Set HomePage
			 *
			 * @since 1.0.0
			 */
			$wbc_home_pages = array(
				// folder name
				'classic'			=> 'Home'
			);

			if ( array_key_exists( $demo_active_import[$current_key]['directory'], $wbc_home_pages ) ) :
				$home_page = get_page_by_title( $wbc_home_pages[$demo_active_import[$current_key]['directory']] );
				if ( isset( $home_page->ID ) ) :
					update_option( 'page_on_front', $home_page->ID );
					update_option( 'show_on_front', 'page' );
				endif;
			endif;



			/**
			 * Set BlogPage
			 *
			 * @since 1.0.0
			 */
			$wbc_blog_pages = array(
				// folder name
				'classic'		=> 'Blog'
			);

			
			if ( array_key_exists( $demo_active_import[$current_key]['directory'], $wbc_blog_pages ) ) :
				$bpage = get_page_by_title( $wbc_blog_pages[$demo_active_import[$current_key]['directory']] );
				if ( isset( $bpage->ID ) ) :
					update_option( 'page_for_posts', $bpage->ID );
				endif;
			endif;
		
		
			/**
			 * Import slider(s) for the current demo being imported
			 *
			 * @since 1.0.0
			 */
			if ( $import_sliders && class_exists( 'RevSlider' ) ) :

				// Set sliders zip name
				$wbc_sliders_array = array(
					// folder name
					'classic'			=> array( 'revslider.zip','news-background-video5.zip','duotone1.zip' ),
				);

				if ( array_key_exists( $demo_active_import[$current_key]['directory'], $wbc_sliders_array ) ) :
					$wbc_slider_imports = $wbc_sliders_array[$demo_active_import[$current_key]['directory']];
					if ( is_array( $wbc_slider_imports ) ) :
						foreach( $wbc_slider_imports as $wbc_slider_import ) :
							if ( file_exists( $demo_directory_path.$wbc_slider_import ) ) :
								$slider = new RevSlider();
								$slider->importSliderFromPost( true, true, $demo_directory_path.$wbc_slider_import );
							endif;
						endforeach;
					endif;
				endif;

			endif; // end Import slider(s)


		endif;

		/**
		 * Update Woocommerce Image Size
		 *
		 * @version 1.0.0  
		*/
		if ( class_exists( 'Woocommerce' ) ) {
			update_option( 'woocommerce_thumbnail_cropping', 'uncropped' );
			update_option( 'woocommerce_thumbnail_image_width', '242' );
			update_option( 'woocommerce_single_image_width', '660' );
		}

	} // end imi_wbc_extended function

endif;

// required plugins
function imi_demo_plugins( $demo_id ) {

	$main_plugins = array(
		'btg-core',
		'js_composer',
		'borntogive-vc-elements',
		'contact-form-7',
		'revslider',
		'pojo-sidebars',
		'breadcrumb-navxt',
		'meta-box',
		'woocommerce',
	);

	$plugins_array = array(
		// Classic
		'wbc-import-1'  => array_merge( $main_plugins, array( 'imic-shortcodes','tinymce-advanced','charitable' ) ),
	);

	return $plugins_array[ $demo_id ];
}
