<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/*
 *
 * 	borntogive Theme Functions
 * 	------------------------------------------------
 * 	borntogive Framework v1.0
 * 	Copyright borntogive  2016 - http://www.imithemes.com/
 *	
 */
/* MAINTENANCE MODE
  ================================================== */
if (!function_exists('borntogive_maintenance_mode')) {
    function borntogive_maintenance_mode() {
        $options = get_option('borntogive_options');
        $custom_logo = $custom_logo_output = $maintenance_mode = "";
        if ((isset($options['custom_admin_login_logo']))&&(isset($options['custom_admin_login_logo']['url']))) {
            $custom_logo = $options['custom_admin_login_logo']['url'];
        }
        $custom_logo_output = '<img src="' . $custom_logo . '" alt="maintenance" style="height: 62px!important;margin: 0 auto; display: block;" />';
        if (isset($options['enable_maintenance'])) {
            $maintenance_mode = $options['enable_maintenance'];
        } else {
            $maintenance_mode = false;
        }
        if ($maintenance_mode) {
            if (!current_user_can('edit_themes') || !is_user_logged_in()) {
                wp_die($custom_logo_output . '<p style="text-align:center">' . esc_html__('We are currently in maintenance mode, please check back shortly.', 'borntogive') . '</p>', esc_html__('Maintenance Mode', 'borntogive'));
            }
        }
    }
    add_action('get_header', 'borntogive_maintenance_mode');
}
/* CUSTOM LOGIN LOGO
  ================================================== */
if (!function_exists('borntogive_custom_login_logo')) {
    function borntogive_custom_login_logo() {
        $options = get_option('borntogive_options');
        $custom_logo = array('url'=>'');
        if (isset($options['custom_admin_login_logo'])) {
            $custom_logo = $options['custom_admin_login_logo'];
        }
        echo '<style type="text/css">
			    .login h1 a { background-image:url(' . $custom_logo['url'] . ') !important; background-size: auto !important; width: auto !important; height: 95px !important; }
			</style>';
    }
    add_action('login_head', 'borntogive_custom_login_logo');
}
/* CUSTOM CSS OUTPUT
  ================================================== */
if (!function_exists('borntogive_custom_styles')) {
    function borntogive_custom_styles() {
        $options = get_option('borntogive_options');
        
        // OPEN STYLE TAG
        echo '<style type="text/css">' . "\n";
        // Custom CSS
        $custom_css = (isset($options['custom_css']))?$options['custom_css']:'';
        if (isset($options['theme_color_type'][0])&&$options['theme_color_type'][0] == 1) {
            $primaryColor = $options['primary_theme_color'];
            echo 'a, .text-primary, .btn-primary .badge, .btn-link,a.list-group-item.active > .badge,.nav-pills > .active > a > .badge, p.drop-caps:first-letter, .accent-color, .nav-np .next:hover, .nav-np .prev:hover, .basic-link, .pagination > li > a:hover,.pagination > li > span:hover,.pagination > li > a:focus,.pagination > li > span:focus, .icon-box-inline span, .pricing-column h3, .post .post-title a:hover, a, .post-actions .comment-count a:hover, .pricing-column .features a:hover, a:hover, .list-group-item a:hover, .icon-box.ibox-plain .ibox-icon i,.icon-box.ibox-plain .ibox-icon img, .icon-box.ibox-border .ibox-icon i,.icon-box.ibox-border .ibox-icon img, address strong, ul.checks > li > i, ul.angles > li > i, ul.carets > li > i, ul.chevrons > li > i, ul.icon > li > i, .widget li .meta-data a:hover, .sort-source li.active a, #menu-toggle:hover, .site-footer .footer_widget a:hover, .megamenu-sub-title i, .dd-menu .megamenu-container ul li a:hover, .fact-ico, .widget_categories ul li a:hover, .widget_archive ul li a:hover, .widget_recent_entries ul li a:hover, .widget_recent_entries ul li a:hover, .widget_recent_comments ul li a:hover, .widget_links ul li a:hover, .widget_meta ul li a:hover, .widget.recent_posts ul li h5 a:hover, .testimonial-block blockquote:before, .testimonial-info strong, .header-style2 .dd-menu > li:hover > a, .header-style3 .dd-menu > li:hover > a, .widget_links ul li.active a, .causes-list-item h4 a, .urgent-cause .post-title a, .site-footer .sp-links:hover strong, .header-info-col i, .flex-caption-cause h3 a, .dd-menu > li > a > i, .cause-target, .owl-carousel .blog-grid-item h3 a:hover, .sticky .dd-menu > li:hover > a > i, .header-style2 .dd-menu > li:hover > a > i, .header-style3 .dd-menu > li:hover > a > i, .goal-amount, .widget a:hover{
	color:'.esc_attr($primaryColor).';
}
p.drop-caps.secondary:first-letter, .accent-bg, .btn-primary,
.btn-primary.disabled,
.btn-primary[disabled],
fieldset[disabled] .btn-primary,
.btn-primary.disabled:hover,
.btn-primary[disabled]:hover,
fieldset[disabled] .btn-primary:hover,
.btn-primary.disabled:focus,
.btn-primary[disabled]:focus,
fieldset[disabled] .btn-primary:focus,
.btn-primary.disabled:active,
.btn-primary[disabled]:active,
fieldset[disabled] .btn-primary:active,
.btn-primary.disabled.active,
.btn-primary[disabled].active,
fieldset[disabled] .btn-primary.active,
.dropdown-menu > .active > a,
.dropdown-menu > .active > a:hover,
.dropdown-menu > .active > a:focus,
.nav-pills > li.active > a,
.nav-pills > li.active > a:hover,
.nav-pills > li.active > a:focus,
.pagination > .active > a,
.pagination > .active > span,
.pagination > .active > a:hover,
.pagination > .active > span:hover,
.pagination > .active > a:focus,
.pagination > .active > span:focus,
.label-primary,
.progress-bar-primary,
a.list-group-item.active,
a.list-group-item.active:hover,
a.list-group-item.active:focus,
.panel-primary > .panel-heading, .carousel-indicators .active, .flex-control-nav a:hover, .flex-control-nav a.flex-active, .media-box .media-box-wrapper, .icon-box.icon-box-style1:hover .ico, .owl-theme .owl-page.active span, .owl-theme .owl-controls.clickable .owl-page:hover span, .ibox-effect.ibox-dark .ibox-icon i:hover,.ibox-effect.ibox-dark:hover .ibox-icon i,.ibox-border.ibox-effect.ibox-dark .ibox-icon i:after, .icon-box .ibox-icon i,.icon-box .ibox-icon img, .icon-box .ibox-icon i,.icon-box .ibox-icon img, .icon-box.ibox-dark.ibox-outline:hover .ibox-icon i, .pricing-column.highlight h3, #back-to-top:hover, .widget_donations, .fblock-image-overlay, .overlay-accent, .tagcloud a:hover, .nav-tabs > li.active > a, .nav-tabs > li.active > a:hover, .nav-tabs > li.active > a:focus, .accordion-heading .accordion-toggle.active, .predefined-amount li label.selected, .donation-choice-breaker, .event-date, .featured-links, hr.sm, hr.sm:before, hr.sm:after, .gallery-updates, .block-title:before, .block-title:after, .widgettitle:before, .widgettitle:after, .widget-title:before, .widget-title:after, .vc_custom_heading:before, .vc_custom_heading:after, .social-share-bar .share-buttons-tc li a, .charitable-submit-field .button, .charitable-login-form .button-primary, .donation-summary,.charitable-button{
  background-color: '.esc_attr($primaryColor).';
}
.donate-button, .woocommerce a.button.alt:hover, .woocommerce button.button.alt:hover, .woocommerce input.button.alt:hover, .woocommerce #respond input#submit.alt:hover, .woocommerce #content input.button.alt:hover, .woocommerce-page a.button.alt:hover, .woocommerce-page button.button.alt:hover, .woocommerce-page input.button.alt:hover, .woocommerce-page #respond input#submit.alt:hover, .woocommerce-page #content input.button.alt:hover, .woocommerce a.button.alt:active, .woocommerce button.button.alt:active, .woocommerce input.button.alt:active, .woocommerce #respond input#submit.alt:active, .woocommerce #content input.button.alt:active, .woocommerce-page a.button.alt:active, .woocommerce-page button.button.alt:active, .woocommerce-page input.button.alt:active, .woocommerce-page #respond input#submit.alt:active, .woocommerce-page #content input.button.alt:active, .wpcf7-form .wpcf7-submit, #charitable-donation-form .donation-amounts .donation-amount.selected, a.featured-link:hover{
  background-color: '.esc_attr($primaryColor).'!important;
}
p.demo_store, .woocommerce a.button.alt, .woocommerce button.button.alt, .woocommerce input.button.alt, .woocommerce #respond input#submit.alt, .woocommerce #content input.button.alt, .woocommerce-page a.button.alt, .woocommerce-page button.button.alt, .woocommerce-page input.button.alt, .woocommerce-page #respond input#submit.alt, .woocommerce-page #content input.button.alt, .woocommerce span.onsale, .woocommerce-page span.onsale, .wpcf7-form .wpcf7-submit, .woocommerce .widget_price_filter .ui-slider .ui-slider-handle, .woocommerce-page .widget_price_filter .ui-slider .ui-slider-handle, .woocommerce .widget_layered_nav ul li.chosen a, .woocommerce-page .widget_layered_nav ul li.chosen a, #charitable_field_recipient .charitable-recipient-type.selected{
  background: '.esc_attr($primaryColor).';
}
.btn-primary:hover,
.btn-primary:focus,
.btn-primary:active,
.btn-primary.active,
.open .dropdown-toggle.btn-primary{
  background: '.esc_attr($primaryColor).';
  opacity:.9
}
.nav .open > a,
.nav .open > a:hover,
.nav .open > a:focus,
.pagination > .active > a,
.pagination > .active > span,
.pagination > .active > a:hover,
.pagination > .active > span:hover,
.pagination > .active > a:focus,
.pagination > .active > span:focus,
a.thumbnail:hover,
a.thumbnail:focus,
a.thumbnail.active,
a.list-group-item.active,
a.list-group-item.active:hover,
a.list-group-item.active:focus,
.panel-primary,
.panel-primary > .panel-heading, .btn-primary.btn-transparent, .icon-box.icon-box-style1 .ico, .icon-box-inline span, .icon-box.ibox-border .ibox-icon, .icon-box.ibox-outline .ibox-icon, .icon-box.ibox-dark.ibox-outline:hover .ibox-icon, .nav-tabs > li.active > a, .nav-tabs > li.active > a:hover, .nav-tabs > li.active > a:focus, .predefined-amount li label.selected, .event-ticket-left .ticket-handle, #charitable-donation-form .donation-amounts .donation-amount.selected, #charitable_field_recipient .charitable-recipient-type:hover, #charitable_field_recipient .charitable-recipient-type.selected{
	border-color:'.esc_attr($primaryColor).';
}
.panel-primary > .panel-heading + .panel-collapse .panel-body, .widget_special_events .event-item, .dd-menu > li ul, .woocommerce .woocommerce-info, .woocommerce-page .woocommerce-info, .woocommerce .woocommerce-message, .woocommerce-page .woocommerce-message{
	border-top-color:'.esc_attr($primaryColor).';
}
.panel-primary > .panel-footer + .panel-collapse .panel-body{
	border-bottom-color:'.esc_attr($primaryColor).';
}
.dd-menu > ul > li > ul li:hover{
	border-left-color:'.esc_attr($primaryColor).';
}
.ibox-border.ibox-effect.ibox-dark .ibox-icon i:hover,.ibox-border.ibox-effect.ibox-dark:hover .ibox-icon i {
	box-shadow:0 0 0 1px '.esc_attr($primaryColor).';
}
.ibox-effect.ibox-dark .ibox-icon i:after {
	box-shadow:0 0 0 2px '.esc_attr($primaryColor).';
}
@media only screen and (max-width: 992px) {
	.dd-menu > li:hover > a, .dd-menu > li:hover > a > i{
		color:'.esc_attr($primaryColor).';
	}
}';
		}
		
		$site_width = (isset($options['site_width']))?$options['site_width']:'1170';
        $site_width=!empty($site_width)?$site_width:1170;
		$site_width_spaced=!empty($site_width)?$site_width+30:1200;
		$site_width_diff=$site_width_spaced/2;
		$SiteMinHeight = (isset($options['content_min_height']))?$options['content_min_height']:'400';
		$PageBannerMinHeight = (isset($options['inner_page_header_min_height']))?$options['inner_page_header_min_height']:'300';
		$pagetitlecolor = (isset($options['inner_page_header_title_typography']['color']))?$options['inner_page_header_title_typography']['color']:'';
		$footer_wide_width = (isset($options['full_width_footer']))?$options['full_width_footer']:'';
		$NavItemSpacing = (isset($options['nav_items_spacing']['margin-top']))?$options['nav_items_spacing']['margin-top']:'';
		$ddRadius = (isset($options['dd_border_radius']))?$options['dd_border_radius']:'';
		$mmtopposition = (isset($options['mobile_menu_drop_top_position']))?$options['mobile_menu_drop_top_position']:'73';
		$mmbackground = (isset($options['mobile_menu_background']['background-color']))?$options['mobile_menu_background']['background-color']:'';
		$menualign = (isset($options['middle_align_menu']))?$options['middle_align_menu']:'';
		$campaign_progress_sh = (isset($options['single_cam_show_progress']))?$options['single_cam_show_progress']:1;
		
		
		if ($menualign == 1)
		{
			$logoheight = (isset($options['logo_upload']))?$options['logo_upload']['height']:'';
			$logospacinguse = (isset($options['logo_spacing']))?$options['logo_spacing']['padding-top']:'';
			$slogoheight = (isset($options['logo_upload_sticky']))?$options['logo_upload_sticky']['height']:'';
			
			echo '
			.header-style1 .dd-menu > li, .header-style2 .dd-menu > li, #menu-toggle{margin-top:'.esc_attr($logospacinguse).'!important;}
			@media only screen and (min-width: 992px) {.dd-menu > li ul{top:100%;}
			.header-style1 .dd-menu > li > a, .header-style2 .dd-menu > li > a{line-height:'.esc_attr($logoheight).'px;-webkit-transition:all 0.3s ease 0.2s;-moz-transition:all 0.3s ease 0.2s;-ms-transition:all 0.3s ease 0.2s;-o-transition:all 0.3s ease 0.2s;transition:all 0.3s ease 0.1s;}
			.header-style1 .site-header.sticky .dd-menu > li > a, .header-style2 .site-header.sticky .dd-menu > li > a{line-height:'.esc_attr($slogoheight).'px;}}
			@media only screen and (max-width: 992px) {#menu-toggle{line-height:'.esc_attr($logoheight).'px!important;-webkit-transition:all 0.3s ease 0.2s;-moz-transition:all 0.3s ease 0.2s;-ms-transition:all 0.3s ease 0.2s;-o-transition:all 0.3s ease 0.2s;transition:all 0.3s ease 0.1s;}}
			@media only screen and (max-width: 992px) {.header-style1 .site-header.sticky #menu-toggle, .header-style2 .site-header.sticky #menu-toggle{line-height:'.esc_attr($slogoheight).'px!important;}}';
		}
		echo '@media (min-width:1200px){.container{width:'.$site_width.'px;}}
		body.boxed .body, body.boxed .vc_row-no-padding{max-width:'.$site_width_spaced.'px!important;}
		@media (min-width: 1200px) {body.boxed .site-header{width:'.$site_width_spaced.'px;margin-left:-'.$site_width_diff.'px;}}';
		if (isset($options['sidebar_position'])&&$options['sidebar_position'] == 2) {
			echo ' #content-col, #sidebar-col{float:right!important;}';
		}
		if (isset($options['content_wide_width'])&&$options['content_wide_width'] == 1)
		{
			echo '.content .container{width:100%;}';
		}
		if (isset($options['header_wide_width'])&&$options['header_wide_width'] == 1)
		{
			echo '.site-header .container, .topbar .container, .fw-menu-wrapper .container{width:100%;}';
		}
		if ($SiteMinHeight != '')
		{
			echo '.content{min-height:'.esc_attr($SiteMinHeight).'px}';
		}
		if (isset($options['mmenu_subm_opener_dim'])&&isset($options['mmenu_subm_opener_dim']['height'])&&$options['mmenu_subm_opener_dim']['height'] != '')
		{
			echo '.smenu-opener{line-height:'.$options['mmenu_subm_opener_dim']['height'].'}';
		}
		if (isset($options['mmenu_subsubm_opener_dim'])&&isset($options['mmenu_subsubm_opener_dim']['height'])&&$options['mmenu_subsubm_opener_dim']['height'] != '')
		{
			echo '.dd-menu ul li .smenu-opener{line-height:'.$options['mmenu_subsubm_opener_dim']['height'].'}';
		}
		if ($PageBannerMinHeight != '')
		{
			echo '.page-banner{min-height:'.esc_attr($PageBannerMinHeight).'px}';
		}
		if (isset($options['inner_page_header_title'])&&$options['inner_page_header_title'] == 0)
		{
			echo '.page-banner-text{display:none!important;}';
		}
		if (isset($options['inner_page_header_title_line'])&&$options['inner_page_header_title_line'] == 0)
		{
			echo '.page-banner .block-title:before, .page-banner .block-title:after{display:none!important;}.page-banner .block-title{margin-bottom:0;}';
		}
		else
		{
			echo '.page-banner .block-title:before, .page-banner .block-title:after{display:block;}.page-banner .block-title{margin-bottom:40px;}';
		}
		if (isset($options['show_topbar'])&&$options['show_topbar'] == 0)
		{
			echo '.topbar{display:none;}';
		}
		if ($campaign_progress_sh == 0)
		{
			echo '.campaign-progress-wrap{display:none!important}';
		}elseif ($campaign_progress_sh == 1)
		{
			echo '.campaign-progress-wrap{display:block!important}';
		}
		if (isset($options['inner_page_header_display'])&&$options['inner_page_header_display'] == 1)
		{
			echo '.hero-area{display:none!important;}';
		}
		echo '.page-banner .block-title:before, .page-banner .block-title:after{background:'.$pagetitlecolor.';}';
		if ($footer_wide_width == 1) {
			echo '.site-footer > .container{width:100%;}';
		}
		if (isset($options['footer_bottom_enable'])&&$options['footer_bottom_enable'] == 0)
		{
			echo '.site-footer-bottom{display:none;}';
		}
		if (isset($options['nav_directions_arrows'])&&$options['nav_directions_arrows'] == 0)
		{
			echo '.dd-menu > li > a > i, .dd-menu > li > ul > li > a > i, .dd-menu > li > ul > li > ul > li > a > i{display:none;}';
		}
		if (isset($options['dd_dropshadow'])&&$options['dd_dropshadow'] == 0)
		{
			echo '.dd-menu > li ul{-webkit-box-shadow:none; -moz-box-shadow:none; box-shadow:none;}';
		}
		if ($ddRadius != '')
		{
			echo '.dd-menu > li ul{border-radius:'.esc_attr($ddRadius).'px;}.dd-menu > li > ul li ul{border-radius:'.esc_attr($ddRadius).'px;}.dd-menu > li > ul > li:first-child > a {border-radius:'.esc_attr($ddRadius).'px '.esc_attr($ddRadius).'px 0 0;} .dd-menu > li > ul > li:last-child > a {border-radius:0 0 '.esc_attr($ddRadius).'px '.esc_attr($ddRadius).'px;}.dd-menu > li > ul > li > ul > li:first-child > a {border-radius:'.esc_attr($ddRadius).'px '.esc_attr($ddRadius).'px 0 0;} .dd-menu > li > ul > li > ul > li:last-child > a {border-radius:0 0 '.esc_attr($ddRadius).'px '.esc_attr($ddRadius).'px;}';
		}
		if ($NavItemSpacing != '')
		{
			echo '.header-style1 .header-info-col, .header-style1 .search-module-trigger, .header-style1 .cart-module-trigger, .header-style2 .search-module-trigger, .header-style2 .cart-module-trigger{margin-top:'.esc_attr($NavItemSpacing).';}';
		}
		if ($mmtopposition != '')
		{
			echo '.dd-menu{top:'.esc_attr($mmtopposition).'px;}';
		}
		if ($mmbackground != '')
		{
			echo '@media only screen and (max-width: 992px) {.dd-menu{background:'.esc_attr($mmbackground).'!important;}}';
		}
		
		if (isset($options['mobile_menu_color'])&&isset($options['mobile_menu_color']['regular'])&&$options['mobile_menu_color']['regular'] != '') {
			echo '@media only screen and (max-width: 992px) {.dd-menu > li > a{color:'.$options['mobile_menu_color']['regular'].'!important;}}';
		}
		if (isset($options['mobile_menu_color'])&&isset($options['mobile_menu_color']['hover'])&&$options['mobile_menu_color']['hover'] != '') {
			echo '@media only screen and (max-width: 992px) {.dd-menu > li > a:hover{color:'.$options['mobile_menu_color']['hover'].'!important;}}';
		}
		if (isset($options['mobile_menu_color'])&&isset($options['mobile_menu_color']['active'])&&$options['mobile_menu_color']['active'] != '') {
			echo '@media only screen and (max-width: 992px) {.dd-menu > li > a:active{color:'.$options['mobile_menu_color']['active'].'!important;}}';
		}
		if (isset($options['mobile_menu_border']['border-bottom'])&&$options['mobile_menu_border']['border-bottom'] != '') {
			echo '@media only screen and (max-width: 992px) {.dd-menu > li > a{border-bottom-width:'.$options['mobile_menu_border']['border-bottom'].';border-bottom-style:'.$options['mobile_menu_border']['border-style'].';border-bottom-color:'.$options['mobile_menu_border']['border-color'].'!important;}}';
		}
		if (isset($options['no_sticky_mobile'])&&$options['no_sticky_mobile'] == 1)
		{
			echo '@media screen and (max-width:992px){
			.site-header,.site-header.sticky{position:relative!important}
			}';
		}
		
		
        // USER STYLES
        if ($custom_css) {
            echo "\n" . '/*========== User Custom CSS Styles ==========*/' . "\n";
            echo ''.$custom_css;
        }
        // CLOSE STYLE TAG
        echo "</style>" . "\n";
    }
    add_action('wp_head', 'borntogive_custom_styles');
}
/* CUSTOM JS OUTPUT
  ================================================== */
if (!function_exists('borntogive_custom_script')) {
    function borntogive_custom_script() {
        $options = get_option('borntogive_options');
        $custom_js = (isset($options['custom_js']))?$options['custom_js']:'';
        if ($custom_js) {
            echo'<script type ="text/javascript">';
            echo ''.$custom_js;
            echo '</script>';
        }
    }
    add_action('wp_footer', 'borntogive_custom_script');
}
/* REGISTER SIDEBARS
  ================================================== */
if (!function_exists('borntogive_register_sidebars')) {
    function borntogive_register_sidebars() {
        if (function_exists('register_sidebar')) {
			$options = get_option('borntogive_options');
			$footer_class = (isset($options["footer_layout"]))?$options["footer_layout"]:'4';
			register_sidebar(array(
                'name' => esc_html__('Blog Sidebar', 'borntogive'),
                'id' => 'blog-sidebar',
                'description' => '',
                'class' => '',
                'before_widget' => '<div id="%1$s" class="widget sidebar-widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>'
            ));
			register_sidebar(array(
                'name' => esc_html__('Page Sidebar', 'borntogive'),
                'id' => 'page-sidebar',
                'description' => '',
                'class' => '',
                'before_widget' => '<div id="%1$s" class="widget sidebar-widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>'
            ));
			register_sidebar(array(
                'name' => esc_html__('Post Sidebar', 'borntogive'),
                'id' => 'post-sidebar',
                'description' => '',
                'class' => '',
                'before_widget' => '<div id="%1$s" class="widget sidebar-widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>'
            ));
			register_sidebar(array(
                'name' => esc_html__('Footer Widgets', 'borntogive'),
                'id' => 'footer-sidebar',
                'description' => '',
                'class' => '',
                'before_widget' => '<div class="col-md-'.$footer_class.' col-sm-'.$footer_class.' widget footer_widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<h4 class="footer-widget-title">',
                'after_title' => '</h4>'
            ));
			register_sidebar(array(
                'name' => esc_html__('Mega Menu Widgets', 'borntogive'),
                'id' => 'megamenu-sidebar',
                'description' => '',
                'class' => '',
                'before_widget' => '<div id="%1$s" class="widget sidebar-widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<span class="megamenu-sub-title">',
                'after_title' => '</span>'
            ));
        }
    }
    add_action('widgets_init', 'borntogive_register_sidebars', 35);
}
/* -------------------------------------------------------------------------------------
  Filter the Widget Title.
  ----------------------------------------------------------------------------------- */
if (!function_exists('borntogive_widget_titles')) {
    add_filter('dynamic_sidebar_params', 'borntogive_widget_titles', 20);
    function borntogive_widget_titles(array $params) {
        // $params will ordinarily be an array of 2 elements, we're only interested in the first element
        $widget = & $params[0];
        $id = $params[0]['id'];
        if ($id == 'footer-sidebar') {
            $widget['before_title'] = '<h4 class="widgettitle">';
            $widget['after_title'] = '</h4>';
        } else {
            $widget['before_title'] = '<h3 class="widgettitle">';
            $widget['after_title'] = '</h3>';
        }
        return $params;
    }
}
//Get all Sidebars
if (!function_exists('borntogive_get_all_sidebars')) {
    function borntogive_get_all_sidebars() {
        $all_sidebars = array();
        global $wp_registered_sidebars;
        $all_sidebars = array('' => '');
        foreach ($wp_registered_sidebars as $sidebar) {
            $all_sidebars[$sidebar['id']] = $sidebar['name'];
        }
        return $all_sidebars;
    }
}

/** -------------------------------------------------------------------------------------
 * Gallery Flexslider
 * @param ID of current Post.
 * @return Div with flexslider parameter.
  ----------------------------------------------------------------------------------- */
if (!function_exists('borntogive_gallery_flexslider')) {
    function borntogive_gallery_flexslider($id) {
		$speed = (get_post_meta(get_the_ID(), 'borntogive_gallery_slider_speed', true)!='')?get_post_meta(get_the_ID(), 'borntogive_gallery_slider_speed', true):5000;
        $pagination = get_post_meta(get_the_ID(), 'borntogive_gallery_slider_pagination', true);
        $auto_slide = get_post_meta(get_the_ID(), 'borntogive_gallery_slider_auto_slide', true);
        $direction = get_post_meta(get_the_ID(), 'borntogive_gallery_slider_direction_arrows', true);
        $effect = get_post_meta(get_the_ID(), 'borntogive_gallery_slider_effects', true);
        $pagination = !empty($pagination) ? $pagination : 'yes';
        $auto_slide = !empty($auto_slide) ? $auto_slide : 'yes';
        $direction = !empty($direction) ? $direction : 'yes';
        $effect = !empty($effect) ? $effect : 'slide';
        return '<div class="flexslider galleryflex" data-autoplay="' . $auto_slide . '" data-pagination="' . $pagination . '" data-arrows="' . $direction . '" data-style="' . $effect . '" data-pause="yes" data-speed='.$speed.'>';
    }
}
 /**
 * BORNTOGIVE SIDEBAR POSITION
 */
if(!function_exists('borntogive_sidebar_position_module'))
{
	function borntogive_sidebar_position_module()
	{
		$sidebar_position = get_post_meta(get_the_ID(),'borntogive_select_sidebar_position',true);
		if(is_home())
		{
			$id = get_option('page_for_posts');
			$sidebar_position = get_post_meta($id,'borntogive_select_sidebar_position',true);
		}
		if(class_exists('buddypress') && is_buddypress()){
			$component = bp_current_component();
			$bp_pages = get_option( 'bp-pages' );
			$id = $bp_pages[$component];
			$sidebar_position = get_post_meta($id,'borntogive_select_sidebar_position',true);
		}
		if($sidebar_position == 2)
		{
			echo ' <style type="text/css">#content-col, #sidebar-col{float:right!important;}</style>';	
		}
		elseif($sidebar_position == 1)
		{
			echo ' <style type="text/css">#content-col, #sidebar-col{float:left!important;}</style>';	
		}
	}
}
 /**
 * BORNTOGIVE SHARE BUTTONS
 */
if(!function_exists('borntogive_share_buttons')){
function borntogive_share_buttons(){
$posttitle = get_the_title();
$postpermalink = get_permalink();
$postexcerpt = wp_trim_words(get_the_excerpt());
$borntogive_options = get_option('borntogive_options');
if(!isset($borntogive_options['facebook_share_alt'])) return;
$facebook_share_alt = $borntogive_options['facebook_share_alt'];
$twitter_share_alt = $borntogive_options['twitter_share_alt'];
$google_share_alt = $borntogive_options['google_share_alt'];
$tumblr_share_alt = $borntogive_options['tumblr_share_alt'];
$pinterest_share_alt = $borntogive_options['pinterest_share_alt'];
$reddit_share_alt = $borntogive_options['reddit_share_alt'];
$linkedin_share_alt = $borntogive_options['linkedin_share_alt'];
$email_share_alt = $borntogive_options['email_share_alt'];
$vk_share_alt = $borntogive_options['vk_share_alt'];
			
            echo '<div class="social-share-bar">';
			if($borntogive_options['sharing_style'] == '0'){
				if($borntogive_options['sharing_color'] == '0'){
            		echo '<ul class="social-icons-colored share-buttons-bc">';
				}elseif($borntogive_options['sharing_color'] == '1'){
            		echo '<ul class="social-icons-colored share-buttons-tc">';
				}elseif($borntogive_options['sharing_color'] == '2'){
            		echo '<ul class="social-icons-colored share-buttons-gs">';
				}
			} elseif($borntogive_options['sharing_style'] == '1'){
				if($borntogive_options['sharing_color'] == '0'){
            		echo '<ul class="social-icons-colored share-buttons-bc share-buttons-squared">';
				}elseif($borntogive_options['sharing_color'] == '1'){
            		echo '<ul class="social-icons-colored share-buttons-tc share-buttons-squared">';
				}elseif($borntogive_options['sharing_color'] == '2'){
            		echo '<ul class="social-icons-colored share-buttons-gs share-buttons-squared">';
				}
			};
					if($borntogive_options['share_before_icon'] == 1){
						echo '<li class="share-title"><i class="fa fa-share-alt"></i></li>';
					}
					if($borntogive_options['share_before_text'] != ''){
						echo '<li class="share-title">'.$borntogive_options['share_before_text'].'</li>';
					}
                	echo '<li class="share-title"></li>';
					if($borntogive_options['share_icon']['1'] == '1'){
                   		echo '<li class="facebook-share"><a href="https://www.facebook.com/sharer/sharer.php?u=' . $postpermalink . '&amp;t=' . $posttitle . '" target="_blank" title="' . $facebook_share_alt . '"><i class="fa fa-facebook"></i></a></li>';
					}
					if($borntogive_options['share_icon']['2'] == '1'){
                     	echo '<li class="twitter-share"><a href="https://twitter.com/intent/tweet?source=' . $postpermalink . '&amp;text=' . $posttitle . ':' . $postpermalink . '" target="_blank" title="' . $twitter_share_alt . '"><i class="fa fa-twitter"></i></a></li>';
					}
					if($borntogive_options['share_icon']['3'] == '1'){
                    echo '<li class="google-share"><a href="https://plus.google.com/share?url=' . $postpermalink . '" target="_blank" title="' . $google_share_alt . '"><i class="fa fa-google-plus"></i></a></li>';
					}
					if($borntogive_options['share_icon']['4'] == '1'){
                    	echo '<li class="tumblr-share"><a href="http://www.tumblr.com/share?v=3&amp;u=' . $postpermalink . '&amp;t=' . $posttitle . '&amp;s=" target="_blank" title="' . $tumblr_share_alt . '"><i class="fa fa-tumblr"></i></a></li>';
					}
					if($borntogive_options['share_icon']['5'] == '1'){
                    	echo '<li class="pinterest-share"><a href="http://pinterest.com/pin/create/button/?url=' . $postpermalink . '&amp;description=' . $postexcerpt . '" target="_blank" title="' . $pinterest_share_alt . '"><i class="fa fa-pinterest"></i></a></li>';
					}
					if($borntogive_options['share_icon']['6'] == '1'){
                    	echo '<li class="reddit-share"><a href="http://www.reddit.com/submit?url=' . $postpermalink . '&amp;title=' . $posttitle . '" target="_blank" title="' . $linkedin_share_alt . '"><i class="fa fa-reddit"></i></a></li>';
					}
					if($borntogive_options['share_icon']['7'] == '1'){
                    	echo '<li class="linkedin-share"><a href="http://www.linkedin.com/shareArticle?mini=true&url=' . $postpermalink . '&amp;title=' . $posttitle . '&amp;summary=' . $postexcerpt . '&amp;source=' . $postpermalink . '" target="_blank" title="' . $linkedin_share_alt . '"><i class="fa fa-linkedin"></i></a></li>';
					}
					if($borntogive_options['share_icon']['8'] == '1'){
                    	echo '<li class="email-share"><a href="mailto:?subject=' . $posttitle . '&amp;body=' . $postexcerpt . ':' . $postpermalink . '" target="_blank" title="' . $email_share_alt . '"><i class="fa fa-envelope"></i></a></li>';
					}
					if(isset($borntogive_options['share_icon']['9'])&&$borntogive_options['share_icon']['9'] == 1){
                    	echo '<li class="vk-share"><a href="http://vk.com/share.php?url=' . $postpermalink . '" target="_blank" title="' . $vk_share_alt . '"><i class="fa fa-vk"></i></a></li>';
					}
                echo '</ul>
            </div>';
	}
}
/*======================
Change Excerpt Length*/
if (!function_exists('borntogive_custom_excerpt_length')) {
function borntogive_custom_excerpt_length( $length ) {
	return 520;
}
add_filter( 'excerpt_length', 'borntogive_custom_excerpt_length', 999 );
}
//Attachment Meta Box
if(!function_exists('borntogive_attachment_url')){
function borntogive_attachment_url( $fields, $post ) {
$meta = get_post_meta($post->ID, 'meta_link', true);
$fields['meta_link'] = array(
'label' => esc_html__('Image URL','borntogive'),
'input' => 'text',
'value' => $meta,
'show_in_edit' => true,
);
return $fields;
}
add_filter( 'attachment_fields_to_edit', 'borntogive_attachment_url', 10, 2 );
}
/**
* Update custom field on save
*/
if(!function_exists('borntogive_update_attachment_url')){
function borntogive_update_attachment_url($attachment){
global $post;
update_post_meta($post->ID, 'meta_link', $attachment['attachments'][$post->ID]['meta_link']);
return $attachment;
}
add_filter( 'attachment_fields_to_save', 'borntogive_update_attachment_url', 4);
}
/**
* Update custom field via ajax
*/
if(!function_exists('borntogive_save_attachment_url')){
function borntogive_save_attachment_url() {
$post_id = $_POST['id'];
$meta = $_POST['attachments'][$post_id ]['meta_link'];
update_post_meta($post_id , 'meta_link', $meta);
clean_post_cache($post_id);
}
add_action('wp_ajax_save-attachment-compat', 'borntogive_save_attachment_url', 0, 1);
}
//Attachment Meta Box
if(!function_exists('borntogive_attachment_postid')){
function borntogive_attachment_postid( $fields, $post ) {
$meta = get_post_meta($post->ID, 'meta_postid', true);
$fields['meta_postid'] = array(
'label' => esc_html__('Post ID','borntogive'),
'input' => 'text',
'value' => $meta,
'show_in_edit' => true,
);
return $fields;
}
add_filter( 'attachment_fields_to_edit', 'borntogive_attachment_postid', 10, 2 );
}
/**
* Update custom field on save
*/
if(!function_exists('borntogive_update_attachment_postid')){
function borntogive_update_attachment_postid($attachment){
global $post;
update_post_meta($post->ID, 'meta_postid', $attachment['attachments'][$post->ID]['meta_postid']);
return $attachment;
}
add_filter( 'attachment_fields_to_save', 'borntogive_update_attachment_postid', 4);
}
/**
* Update custom field via ajax
*/
if(!function_exists('borntogive_save_attachment_postid')){
function borntogive_save_attachment_postid() {
$post_id = $_POST['id'];
$meta = $_POST['attachments'][$post_id ]['meta_postid'];
update_post_meta($post_id , 'meta_postid', $meta);
clean_post_cache($post_id);
}
add_action('wp_ajax_save-attachment-compat', 'borntogive_save_attachment_postid', 0, 1);
}
//Get Attachment details
if (!function_exists('borntogive_wp_get_attachment')) {
function borntogive_wp_get_attachment( $attachment_id ) {
	$attachment = get_post( $attachment_id );
	if(!empty($attachment)) {
	return array(
		'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
		'caption' => $attachment->post_excerpt,
		'description' => $attachment->post_content,
		'href' => get_permalink( $attachment->ID ),
		'src' => $attachment->guid,
		'title' => $attachment->post_title,
		'url' => $attachment->meta_link,
		'postid' => $attachment->meta_postid
	); }
} }
if(!function_exists('borntogive_get_post_content'))
{
	function borntogive_get_post_content($update_id, $filter='', $limit='25')
	{
		$post_id = get_post($update_id);
		$content = $post_id->post_content;
		if($filter=='1')
		{
			$excerpt = apply_filters('the_content', $content);
		}
		else
		{
			$excerpt = wp_trim_words($content, $limit);
		}
		return $excerpt;
	}
}
/* Related Posts
========================================================= */
//Get related posts ID
if(!function_exists('borntogive_related_posts_single'))
{
	function borntogive_related_posts_single($single_id = '')
	{
		$single = ($single_id!='')?$single_id:get_the_ID();
		$ids = array();
		$cat_name = get_the_category_by_ID( $single );
		$post_arg = array('post_type'=>'post', 'post__not_in'=>array($single), 'posts_per_page'=>3, 'category_name'=>$cat_name);
		$post_list = new WP_Query($post_arg);
		if($post_list->have_posts()):
		while($post_list->have_posts()):
		$post_list->the_post();
		$ids[] = get_the_ID();
		endwhile;
		endif;
		wp_reset_postdata();
		return $ids;
	}
}
/* Add Class to Next/Previous Posts Link
========================================================= */
add_filter('next_posts_link_attributes', 'borntogive_older_posts_link');
add_filter('previous_posts_link_attributes', 'borntogive_newer_posts_link');

function borntogive_older_posts_link() {
    return 'class="pull-left"';
}
function borntogive_newer_posts_link() {
    return 'class="pull-right"';
}
add_filter('next_post_link', 'borntogive_post_link_attributes_next');
add_filter('previous_post_link', 'borntogive_post_link_attributes_prev');
 
function borntogive_post_link_attributes_prev($output) {
    $code = 'class="pull-left"';
    return str_replace('<a href=', '<a '.$code.' href=', $output);
}
function borntogive_post_link_attributes_next($output) {
    $code = 'class="pull-right"';
    return str_replace('<a href=', '<a '.$code.' href=', $output);
}
//Event Recurring Date/Time
function borntogive_afterSavePost()
{
	if(isset($_GET['post']))
	 { 
	 $postId = $_GET['post'];
	$post_type = get_post_type($postId);
	if($post_type=='event')
	{
		
		$event_start_date = get_post_meta($postId, 'borntogive_event_start_dt', true);
		$event_end_date = get_post_meta($postId, 'borntogive_event_end_dt', true);
		$event_start_date_unix = strtotime($event_start_date);
		$event_end_date_unix = strtotime($event_end_date);
		$event_frequency_type = get_post_meta($postId, 'borntogive_event_frequency_type', true);
		$fixed_date_frequency = get_post_meta($postId, 'borntogive_event_frequency', true);
		$event_weekday_frequency_week = get_post_meta($postId, 'borntogive_event_day_month', true);
		$event_weekday_frequency_day = get_post_meta($postId, 'borntogive_event_week_day', true);
		$event_recur_count = get_post_meta($postId, 'borntogive_event_frequency_count', true);
		$event_multiple_type = get_post_meta($postId, 'borntogive_event_multiple_type', true);
		$event_multiple_dates = get_post_meta($postId, 'borntogive_event_multiple_dates', true);
		if($event_frequency_type==1)
		{
			if($event_recur_count>0)
			{
				$days = intval($fixed_date_frequency)*intval($event_recur_count);
				$update_date = strtotime("+".$days." days", strtotime($event_start_date));
				update_post_meta($postId,'borntogive_event_frequency_end',borntogive_date_localization('Y-m-d', $update_date));
			}
			else
			{
				
			}
		}
		elseif($event_frequency_type==2)
		{
				$update_date = strtotime("+".$event_recur_count." month", strtotime($event_start_date));
				update_post_meta($postId,'borntogive_event_frequency_end',borntogive_date_localization('Y-m-d', $update_date));
		}
		elseif($event_multiple_type==1)
		{
			$mostRecent= 0;
			foreach($event_multiple_dates as $date)
			{ 
				$curDate = strtotime($date);
				if ($curDate > $mostRecent) {
					 $mostRecent = $curDate;
				}
			}
			update_post_meta($postId,'borntogive_event_frequency_end',borntogive_date_localization('Y-m-d', $mostRecent));
		}
		else
		{
			update_post_meta($postId,'borntogive_event_frequency_end',borntogive_date_localization('Y-m-d', $event_end_date_unix));
		}
		
	} 
	}
}
borntogive_afterSavePost();
if (!function_exists('borntogive_dateDiff')) {
function borntogive_dateDiff($start, $end) {
  $start_ts = strtotime($start);
  $end_ts = strtotime($end);
  $diff = $end_ts - $start_ts;
  return round($diff / 86400);
}
}
if(!function_exists('borntogive_recur_events'))
{
	function borntogive_recur_events($status="future", $term=array())
	{
		$borntogive_options = get_option('borntogive_options');
		$show_event = (isset($borntogive_options['countdown_timer']))?$borntogive_options['countdown_timer']:'0';
		
		$today = borntogive_date_localization('Y-m-d');
				if(!empty($term))
				{ 
				$event_list = new WP_Query(array('post_type' => 'event','post_status' => 'publish', 'tax_query'=>array(array('taxonomy'=>'event-category', 'field'=>'term_id', 'terms'=>$term,'operator'=>'IN',)), 'meta_key' => 'borntogive_event_start_dt', 'meta_query' => array(array('key' => 'borntogive_event_frequency_end', 'value' => $today, 'compare' => '>=')), 'orderby' => 'meta_value', 'order' => 'ASC', 'posts_per_page' => -1)); 
				}
				else
				{
					$event_list = new WP_Query(array('post_type' => 'event','post_status' => 'publish', 'meta_key' => 'borntogive_event_start_dt', 'meta_query' => array(array('key' => 'borntogive_event_frequency_end', 'value' => $today, 'compare' => '>=')), 'orderby' => 'meta_value', 'order' => 'ASC', 'posts_per_page' => -1)); 
				}
		$count = 1;
		$all_events = array();
		if($event_list->have_posts()):while($event_list->have_posts()):$event_list->the_post();
		//Get all meta values of event
		$event_start_date = get_post_meta(get_the_ID(), 'borntogive_event_start_dt', true);
		$event_end_date = get_post_meta(get_the_ID(), 'borntogive_event_end_dt', true);
		$event_start_date_unix = strtotime($event_start_date);
		$event_end_date_unix = strtotime($event_end_date);
		$event_frequency_type = get_post_meta(get_the_ID(), 'borntogive_event_frequency_type', true);
		$fixed_date_frequency = get_post_meta(get_the_ID(), 'borntogive_event_frequency', true);
		$event_weekday_frequency_week = get_post_meta(get_the_ID(), 'borntogive_event_day_month', true);
		$event_weekday_frequency_day = get_post_meta(get_the_ID(), 'borntogive_event_week_day', true);
		$event_recur_count = get_post_meta(get_the_ID(), 'borntogive_event_frequency_count', true);
		$event_multiple_type = get_post_meta(get_the_ID(), 'borntogive_event_multiple_type', true);
		$event_multiple_dates = get_post_meta(get_the_ID(), 'borntogive_event_multiple_dates', true);
		$event_multiple_dates = (!empty($event_multiple_dates))?$event_multiple_dates:array();
		$days_total = borntogive_dateDiff($event_start_date, $event_end_date);
		if($days_total<=0)
		{
			if($event_start_date_unix>borntogive_date_localization('U'))
			{
				//$all_events[$event_start_date_unix] = get_the_ID();
			}
		}
		//Run condition basis on event recur type
		//If recur type is fixed date
		if($event_frequency_type==1&&$days_total<1)
		{
			if($event_recur_count>0)
			{
				if($show_event==0)
				{
					$this_event_start_date = $event_start_date;
				}
				else
				{
					$event_start_date_m = borntogive_date_localization('Y-m-d', $event_start_date_unix);
					$date_end_time_m = borntogive_date_localization('G:i', $event_end_date_unix);
					$get_updated_date = strtotime($event_start_date_m.' '.$date_end_time_m);
					$this_event_start_date = borntogive_date_localization('Y-m-d G:i', $get_updated_date);
				} 
				for($i=0; $i<$event_recur_count; $i++)
				{ 
					$event_new_date = strtotime("+".$fixed_date_frequency." days", strtotime($this_event_start_date));
					if(!in_array(borntogive_date_localization('Y-m-d', $event_new_date), $event_multiple_dates)&&$event_new_date>=borntogive_date_localization('U'))
					{ 
					if (array_key_exists($event_new_date, $all_events))
					{
						if($event_new_date>borntogive_date_localization('U'))
						{
							$all_events[$event_new_date+1] = get_the_ID();
						}
					}
					else
					{ 
						if($event_new_date>borntogive_date_localization('U'))
						{ 
							$all_events[$event_new_date] = get_the_ID();
						}
					}
					}
					$this_event_start_date = borntogive_date_localization('Y-m-d G:i', $event_new_date);
				}
			}
		 }
		//If recur type is week day
		elseif($event_frequency_type==2&&$days_total<1)
		{
			($event_start_date_unix>borntogive_date_localization('U'))?$all_events[$event_start_date_unix] = get_the_ID():'';
			if($show_event==0)
				{
					$get_updated_date = strtotime($event_start_date);
				}
				else
				{
					$event_start_date_m = borntogive_date_localization('Y-m-d', $event_start_date_unix);
					$date_end_time_m = borntogive_date_localization('G:i', $event_end_date_unix);
					$get_updated_date = strtotime($event_start_date_m.' '.$date_end_time_m);
				}
			for($i=1; $i<=$event_recur_count; $i++)
			{
				$eventDate = strtotime( borntogive_date_localization('Y-m-01', $get_updated_date) );
				$event_start_date = strtotime("+".$i." month", $eventDate);
				$next_month = borntogive_date_localization('F',$event_start_date);
				$next_event_year = borntogive_date_localization('Y',$event_start_date);
				$start_date_time = borntogive_date_localization('G:i', $get_updated_date);
				$all_events_add = borntogive_date_localization('Y-m-d '.$start_date_time, strtotime($event_weekday_frequency_week.' '.$event_weekday_frequency_day.' of '.$next_month.' '.$next_event_year));
				if(!in_array(borntogive_date_localization('Y-m-d', strtotime($all_events_add)), $event_multiple_dates))
				{
					if(strtotime($all_events_add)>borntogive_date_localization('U'))
					{
						$all_events[strtotime($all_events_add)] = get_the_ID();
					}
				}
			}
		}
		//If start date and end date do not match
		else
		{
			$days_total = borntogive_dateDiff($event_start_date, $event_end_date);
			$start = 0;
			if($show_event==0)
			{
				$date_time = $event_start_date_unix;
				$increment_date = $date_time;
			}
			else
			{
				$event_start_date_m = borntogive_date_localization('Y-m-d', $event_start_date_unix);
				$date_end_time_m = borntogive_date_localization('G:i', $event_end_date_unix);
				$date_time = strtotime($event_start_date_m.' '.$date_end_time_m);
				$increment_date = $date_time;
			}
			while($start<=$days_total)
			{ 
				if (array_key_exists($date_time, $all_events))
				{
					if($date_time>borntogive_date_localization('U'))
					{
						$all_events[$date_time+1] = get_the_ID();
						break;
					}
				}
				else
				{ 
					if($date_time>borntogive_date_localization('U'))
					{
						$all_events[$date_time] = get_the_ID();
						break;
					}
				}
				$start++;
				$date_time = strtotime("+".$start." days", $increment_date);
			}
		}
		if($event_multiple_type==1&&$days_total<=1)
		{
			if(!empty($event_multiple_dates))
			{
				$start_date_time = borntogive_date_localization('G:i', $event_start_date_unix);
				foreach($event_multiple_dates as $date)
				{
					if(strtotime($date.' '.$start_date_time)>borntogive_date_localization('U'))
					{
						$all_events[strtotime($date.' '.$start_date_time)] = get_the_ID();
					}
				}
			}
		}
		endwhile; endif; wp_reset_postdata(); 
		return $all_events;
	}
}
if(!function_exists('borntogive_recur_events_future'))
{
	function borntogive_recur_events_future($status="future", $term=array())
	{
		$borntogive_options = get_option('borntogive_options');
		$show_event = (isset($borntogive_options['countdown_timer']))?$borntogive_options['countdown_timer']:'0';
		
		$today = borntogive_date_localization('Y-m-01');
				if(!empty($term))
				{ 
				$event_list = new WP_Query(array('post_type' => 'event','post_status' => 'publish', 'tax_query'=>array(array('taxonomy'=>'event-category', 'field'=>'term_id', 'terms'=>$term,'operator'=>'IN',)), 'meta_key' => 'borntogive_event_start_dt', 'meta_query' => array(array('key' => 'borntogive_event_frequency_end', 'value' => $today, 'compare' => '>=')), 'orderby' => 'meta_value', 'order' => 'ASC', 'posts_per_page' => -1)); 
				}
				else
				{
					$event_list = new WP_Query(array('post_type' => 'event','post_status' => 'publish', 'meta_key' => 'borntogive_event_start_dt', 'meta_query' => array(array('key' => 'borntogive_event_frequency_end', 'value' => $today, 'compare' => '>=')), 'orderby' => 'meta_value', 'order' => 'ASC', 'posts_per_page' => -1)); 
				}
		$count = 1;
		$all_events = array();
		if($event_list->have_posts()):while($event_list->have_posts()):$event_list->the_post();
		//Get all meta values of event
		$event_start_date = get_post_meta(get_the_ID(), 'borntogive_event_start_dt', true);
		$event_end_date = get_post_meta(get_the_ID(), 'borntogive_event_end_dt', true);
		$event_start_date_unix = strtotime($event_start_date);
		$event_end_date_unix = strtotime($event_end_date);
		$event_frequency_type = get_post_meta(get_the_ID(), 'borntogive_event_frequency_type', true);
		$fixed_date_frequency = get_post_meta(get_the_ID(), 'borntogive_event_frequency', true);
		$event_weekday_frequency_week = get_post_meta(get_the_ID(), 'borntogive_event_day_month', true);
		$event_weekday_frequency_day = get_post_meta(get_the_ID(), 'borntogive_event_week_day', true);
		$event_recur_count = get_post_meta(get_the_ID(), 'borntogive_event_frequency_count', true);
		$event_multiple_type = get_post_meta(get_the_ID(), 'borntogive_event_multiple_type', true);
		$event_multiple_dates = get_post_meta(get_the_ID(), 'borntogive_event_multiple_dates', true);
		$event_multiple_dates = (!empty($event_multiple_dates))?$event_multiple_dates:array();
		$days_total = borntogive_dateDiff($event_start_date, $event_end_date);
		$this_month_start = borntogive_date_localization('Y-m-01');
		$this_month_start = strtotime($this_month_start);
		if($days_total<=0)
		{
			if($event_start_date_unix>borntogive_date_localization('U'))
			{
				//$all_events[$event_start_date_unix] = get_the_ID();
			}
		}
		//Run condition basis on event recur type
		//If recur type is fixed date
		if($event_frequency_type==1&&$days_total<1)
		{
			if($event_recur_count>0)
			{
				if($show_event==0)
				{
					$this_event_start_date = $event_start_date;
				}
				else
				{
					$event_start_date_m = borntogive_date_localization('Y-m-d', $event_start_date_unix);
					$date_end_time_m = borntogive_date_localization('G:i', $event_end_date_unix);
					$get_updated_date = strtotime($event_start_date_m.' '.$date_end_time_m);
					$this_event_start_date = borntogive_date_localization('Y-m-d G:i', $get_updated_date);
				} 
				for($i=0; $i<$event_recur_count; $i++)
				{ 
					$event_new_date = strtotime("+".$fixed_date_frequency." days", strtotime($this_event_start_date));
					if(!in_array(borntogive_date_localization('Y-m-d', $event_new_date), $event_multiple_dates)&&$event_new_date>=$this_month_start)
					{ 
					if (array_key_exists($event_new_date, $all_events))
					{
						if($event_new_date>$this_month_start)
						{
							$all_events[$event_new_date+1] = get_the_ID();
						}
					}
					else
					{ 
						if($event_new_date>$this_month_start)
						{ 
							$all_events[$event_new_date] = get_the_ID();
						}
					}
					}
					$this_event_start_date = borntogive_date_localization('Y-m-d G:i', $event_new_date);
				}
			}
		 }
		//If recur type is week day
		elseif($event_frequency_type==2&&$days_total<1)
		{
			if($show_event==0)
				{
					$get_updated_date = strtotime($event_start_date);
				}
				else
				{
					$event_start_date_m = borntogive_date_localization('Y-m-d', $event_start_date_unix);
					$date_end_time_m = borntogive_date_localization('G:i', $event_end_date_unix);
					$get_updated_date = strtotime($event_start_date_m.' '.$date_end_time_m);
				}
			for($i=1; $i<=$event_recur_count; $i++)
			{
				$eventDate = strtotime( borntogive_date_localization('Y-m-01', $get_updated_date) );
				$event_start_date = strtotime("+".$i." month", $eventDate);
				$next_month = borntogive_date_localization('F',$event_start_date);
				$next_event_year = borntogive_date_localization('Y',$event_start_date);
				$start_date_time = borntogive_date_localization('G:i', $get_updated_date);
				$all_events_add = borntogive_date_localization('Y-m-d '.$start_date_time, strtotime($event_weekday_frequency_week.' '.$event_weekday_frequency_day.' of '.$next_month.' '.$next_event_year));
				if(!in_array(borntogive_date_localization('Y-m-d', strtotime($all_events_add)), $event_multiple_dates))
				{ 
					if(strtotime($all_events_add)>$this_month_start)
					{
						$all_events[strtotime($all_events_add)] = get_the_ID();
					}
				}
			}
		}
		//If start date and end date do not match
		else
		{
			$days_total = borntogive_dateDiff($event_start_date, $event_end_date);
			$start = 0;
			if($show_event==0)
			{
				$date_time = $event_start_date_unix;
				$increment_date = $date_time;
			}
			else
			{
				$event_start_date_m = borntogive_date_localization('Y-m-d', $event_start_date_unix);
				$date_end_time_m = borntogive_date_localization('G:i', $event_end_date_unix);
				$date_time = strtotime($event_start_date_m.' '.$date_end_time_m);
				$increment_date = $date_time;
			}
			while($start<=$days_total)
			{ 
				if (array_key_exists($date_time, $all_events))
				{
					if($date_time>$this_month_start)
					{
						$all_events[$date_time+1] = get_the_ID();
						break;
					}
				}
				else
				{ 
					if($date_time>$this_month_start)
					{
						$all_events[$date_time] = get_the_ID();
						break;
					}
				}
				$start++;
				$date_time = strtotime("+".$start." days", $increment_date);
			}
		}
		if($event_multiple_type==1&&$days_total<=1)
		{
			if(!empty($event_multiple_dates))
			{
				$start_date_time = borntogive_date_localization('G:i', $event_start_date_unix);
				foreach($event_multiple_dates as $date)
				{
					if(strtotime($date.' '.$start_date_time)>$this_month_start)
					{
						$all_events[strtotime($date.' '.$start_date_time)] = get_the_ID();
					}
				}
			}
		}
		endwhile; endif; wp_reset_postdata(); 
		return $all_events;
	}
}
if(!function_exists('borntogive_recur_events_past'))
{
	function borntogive_recur_events_past($term=array())
	{
		$borntogive_options = get_option('borntogive_options');
		$show_event = (isset($borntogive_options['countdown_timer']))?$borntogive_options['countdown_timer']:'0';
		
		$today = borntogive_date_localization('Y-m-d');
				if(!empty($term))
				{ 
				$event_list = new WP_Query(array('post_type' => 'event','post_status' => 'publish', 'tax_query'=>array(array('taxonomy'=>'event-category', 'field'=>'term_id', 'terms'=>$term,'operator'=>'IN',)), 'posts_per_page' => -1)); 
				}
				else
				{
					$event_list = new WP_Query(array('post_type' => 'event','post_status' => 'publish', 'posts_per_page' => -1)); 
				}
		$count = 1;
		$all_events = array();
		if($event_list->have_posts()):while($event_list->have_posts()):$event_list->the_post();
		//Get all meta values of event
		$event_start_date = get_post_meta(get_the_ID(), 'borntogive_event_start_dt', true);
		$event_end_date = get_post_meta(get_the_ID(), 'borntogive_event_end_dt', true);
		$event_start_date_unix = strtotime($event_start_date);
		$event_end_date_unix = strtotime($event_end_date);
		$event_frequency_type = get_post_meta(get_the_ID(), 'borntogive_event_frequency_type', true);
		$fixed_date_frequency = get_post_meta(get_the_ID(), 'borntogive_event_frequency', true);
		$event_weekday_frequency_week = get_post_meta(get_the_ID(), 'borntogive_event_day_month', true);
		$event_weekday_frequency_day = get_post_meta(get_the_ID(), 'borntogive_event_week_day', true);
		$event_recur_count = get_post_meta(get_the_ID(), 'borntogive_event_frequency_count', true);
		$event_multiple_type = get_post_meta(get_the_ID(), 'borntogive_event_multiple_type', true);
		$event_multiple_dates = get_post_meta(get_the_ID(), 'borntogive_event_multiple_dates', true);
		$event_multiple_dates = (!empty($event_multiple_dates))?$event_multiple_dates:array();
		$days_total = borntogive_dateDiff($event_start_date, $event_end_date);
		if($days_total<=0)
		{
			if($event_start_date_unix<borntogive_date_localization('U'))
			{
				$all_events[$event_start_date_unix] = get_the_ID();
			}
		}
		//Run condition basis on event recur type
		//If recur type is fixed date
		if($event_frequency_type==1&&$days_total<1)
		{
			if($event_recur_count>0)
			{
				if($show_event==0)
				{
					$this_event_start_date = $event_start_date;
				}
				else
				{
					$event_start_date_m = borntogive_date_localization('Y-m-d', $event_start_date_unix);
					$date_end_time_m = borntogive_date_localization('G:i', $event_end_date_unix);
					$get_updated_date = strtotime($event_start_date_m.' '.$date_end_time_m);
					$this_event_start_date = borntogive_date_localization('Y-m-d G:i', $get_updated_date);
				}
				for($i=0; $i<$event_recur_count; $i++)
				{
					$event_new_date = strtotime("+".$fixed_date_frequency." days", strtotime($this_event_start_date));
					if(!in_array(borntogive_date_localization('Y-m-d', $event_new_date), $event_multiple_dates)&&$event_new_date<=borntogive_date_localization('U'))
					{
						if($event_new_date<borntogive_date_localization('U'))
						{
							$all_events[$event_new_date+1] = get_the_ID();
						}	
					}
					$this_event_start_date = borntogive_date_localization('Y-m-d G:i', $event_new_date);
				}
			}
		}
		//If recur type is week day
		elseif($event_frequency_type==2&&$days_total<1)
		{
			if($show_event==0)
				{
					$get_updated_date = strtotime($event_start_date);
				}
				else
				{
					$event_start_date_m = borntogive_date_localization('Y-m-d', $event_start_date_unix);
					$date_end_time_m = borntogive_date_localization('G:i', $event_end_date_unix);
					$get_updated_date = strtotime($event_start_date_m.' '.$date_end_time_m);
				}
			for($i=1; $i<=$event_recur_count; $i++)
			{
				$eventDate = strtotime( borntogive_date_localization('Y-m-01', $get_updated_date) );
				$event_start_date = strtotime("+".$i." month", $eventDate);
				$next_month = borntogive_date_localization('F',$event_start_date);
				$next_event_year = borntogive_date_localization('Y',$event_start_date);
				$start_date_time = borntogive_date_localization('G:i', $get_updated_date);
				$all_events_add = borntogive_date_localization('Y-m-d '.$start_date_time, strtotime($event_weekday_frequency_week.' '.$event_weekday_frequency_day.' of '.$next_month.' '.$next_event_year));
				if(!in_array(borntogive_date_localization('Y-m-d', strtotime($all_events_add)), $event_multiple_dates))
				{
					if(strtotime($all_events_add)<borntogive_date_localization('U'))
					{
						$all_events[strtotime($all_events_add)] = get_the_ID();
					}
				}
			}
		}
		//If start date and end date do not match
		else
		{
			$days_total = borntogive_dateDiff($event_start_date, $event_end_date);
			$start = 0;
			if($show_event==0)
			{
				$date_time = $event_start_date_unix;
				$increment_date = $date_time;
			}
			else
			{
				$event_start_date_m = borntogive_date_localization('Y-m-d', $event_start_date_unix);
				$date_end_time_m = borntogive_date_localization('G:i', $event_end_date_unix);
				$date_time = strtotime($event_start_date_m.' '.$date_end_time_m);
				$increment_date = $date_time;
			}
			while($start<=$days_total)
			{ 
				if($date_time<borntogive_date_localization('U'))
				{
					$all_events[$date_time] = get_the_ID();
					break;
				}
				$start++;
				$date_time = strtotime("+".$start." days", $increment_date);
			}
		}
		if($event_multiple_type==1&&$days_total<=1)
		{
			if(!empty($event_multiple_dates))
			{
				$start_date_time = borntogive_date_localization('G:i', $event_start_date_unix);
				foreach($event_multiple_dates as $date)
				{
					if(strtotime($date.' '.$start_date_time)<borntogive_date_localization('U'))
					{
						$all_events[strtotime($date.' '.$start_date_time)] = get_the_ID();
					}
				}
			}
		}
		endwhile; endif; wp_reset_postdata();
		return $all_events;
	}
}
/** -------------------------------------------------------------------------------------
 * Convert the Format String from php to fullcalender
 * @see http://arshaw.com/fullcalendar/docs/utilities/formatDate/
 * @param $format
----------------------------------------------------------------------------------- */
if(!function_exists('BornToGiveConvertDate')){
	 function BornToGiveConvertDate($format) {
	 	$format_rules = array('a'=>'t',
			 'A'=>'T',
			 'B'=>'',
			 'c'=>'u',
			 'd'=>'dd',
			 'D'=>'ddd',
			 'F'=>'MMMM',
			 'g'=>'h',
			 'G'=>'H',
			 'h'=>'hh',
			 'H'=>'HH',
			 'i'=>'mm',
			 'I'=>'',
			 'j'=>'d',
			 'l'=>'dddd',
			 'L'=>'',
			 'm'=>'MM',
			 'M'=>'MMM',
			 'n'=>'M',
			 'O'=>'',
			 'r'=>'',
			 's'=>'ss',
			 'S'=>'S',
			 't'=>'',
			 'T'=>'',
			 'U'=>'',
			 'w'=>'',
			 'W'=>'',
			 'y'=>'yy',
			 'Y'=>'yyyy',
			 'z'=>'',
			 'Z'=>'');
	 	  $ret = '';
	 	for ($i=0; $i<strlen($format); $i++) {
	 		if (isset($format_rules[$format[$i]])) {
	 			$ret .= $format_rules[$format[$i]];
	 		} else {
	 			$ret .= $format[$i];
	 		}
	 	}
	 	return $ret;
}}
if (!function_exists('borntogive_social_staff_icon')) {
function borntogive_social_staff_icon($id = '') {
        $output = '';
		if($id=='') { $id = get_the_ID(); }
        $staff_icons = get_post_meta($id, 'borntogive_social_icon_list', false);
        if (!empty($staff_icons[0]) || get_post_meta($id, 'borntogive_staff_member_email', true) != '') {
            $output.='<ul class="social-icons-rounded social-icons-colored">';
            if (!empty($staff_icons[0])) {
                foreach ($staff_icons[0] as $list => $values) {
                    if (!empty($values[1])) {
                        $className = preg_replace('/\s+/', '-', strtolower($values[0]));
                        $className = 'fa fa-' . $className;
                        $output.='<li class="'.$values[0].'"><a href="' . $values[1] . '" target ="_blank"><i class="' . $className . '"></i></a></li>';
                    }
                }
            }
            if (get_post_meta($id, 'borntogive_staff_member_email', true) != '') {
                $output.='<li class="email"><a href="mailto:' . get_post_meta($id, 'borntogive_staff_member_email', true) . '"><i class="fa fa-envelope"></i></a></li>';
            }
            $output.='</ul>';
        }
        return $output;
    }
}
if(!function_exists('borntogive_event_arg')){
 function borntogive_event_arg($date_converted,$id){
        $custom_event_url=esc_url(add_query_arg('event_date',$date_converted,get_permalink($id)));
    return $custom_event_url;
  }
}
//Get All Post Types
if(!function_exists('borntogive_get_all_types')){
add_action( 'wp_loaded', 'borntogive_get_all_types');
function borntogive_get_all_types(){
   $args = array(
   'public'   => true,
   );
$output = 'names'; // names or objects, note names is the default
return $post_types = get_post_types($args, $output); 
}
}
if(!function_exists('borntogive_get_event_time_format'))
{
	function borntogive_get_event_time_format($id, $key, $layout="list")
	{
		$print_time = '';
		$borntogive_options = get_option('borntogive_options');
		$date_format = (isset($borntogive_options['event_tm_opt']))?$borntogive_options['event_tm_opt']:0;
		$event_start_date = get_post_meta($id, 'borntogive_event_start_dt', true);
		$event_end_date = get_post_meta($id, 'borntogive_event_end_dt', true);
		$event_all_day = get_post_meta($id, 'borntogive_event_all_day', true);
		$days_difference = borntogive_dateDiff($event_start_date, $event_end_date);
		$event_start_date = strtotime($event_start_date);
		$event_end_date = strtotime($event_end_date);
		$event_day = ($layout=="grid")?borntogive_date_localization('l', $key).', ':'';
		if($event_all_day!='1')
		{
			switch ($date_format) 
			{
				case 0:
				$print_time .= $event_day.borntogive_date_localization(get_option('time_format'), $event_start_date);
				break;
				case 1:
				if($event_end_date!='')
				{
					$print_time .= $event_day.borntogive_date_localization(get_option('time_format'), $event_end_date);
				}
				else
				{
					$print_time .= $event_day.borntogive_date_localization(get_option('time_format'), $event_start_date);
				}
				break;
				case 2:
				if($event_end_date!='')
				{
					$print_time .= $event_day.borntogive_date_localization(get_option('time_format'), $event_start_date);
					$print_time .= ' - '.borntogive_date_localization(get_option('time_format'), $event_end_date);
				}
				else
				{
					$print_time .= $event_day.borntogive_date_localization(get_option('time_format'), $event_start_date);
				}
				break;
			}
		}
		return $print_time;
	}
}
if(!function_exists('borntogive_get_event_datetime_format'))
{
	function borntogive_get_event_date_format($id, $key)
	{
		$print_date = '';
		$borntogive_options = get_option('borntogive_options');
		$date_format = (isset($borntogive_options['event_dt_opt']))?$borntogive_options['event_dt_opt']:0;
		$event_start_date = get_post_meta($id, 'borntogive_event_start_dt', true);
		$event_end_date = get_post_meta($id, 'borntogive_event_end_dt', true);
		$days_difference = borntogive_dateDiff($event_start_date, $event_end_date);
		$event_start_date = strtotime($event_start_date);
		$event_end_date = strtotime($event_end_date);
		switch ($date_format) 
		{
			case 0:
			$print_date .= borntogive_date_localization(get_option('date_format'), $key);
			break;
			case 1:
			if($days_difference>0)
			{
				$print_date .= borntogive_date_localization(get_option('date_format'), $event_end_date);
			}
			else
			{
				$print_date .= borntogive_date_localization(get_option('date_format'), $key);
			}
			break;
			case 2:
			if($days_difference>0)
			{
				$print_date .= borntogive_date_localization(get_option('date_format'), $event_start_date);
				$print_date .= ' - '.borntogive_date_localization(get_option('date_format'), $event_end_date);
			}
			else
			{
				$print_date .= borntogive_date_localization(get_option('date_format'), $key);
			}
			break;
		}
		return $print_date;
	}
}
if(!function_exists('borntogiveAddQueryVarsFilter')){
function borntogiveAddQueryVarsFilter( $vars ){
  $vars[] = "event_date";
  $vars[] = "event_cat";
  $vars[] = "pg";
  $vars[] = "speakers";
  $vars[] = "reg";
	$vars[] = "registrant";
  return $vars;
}
add_filter('query_vars','borntogiveAddQueryVarsFilter');
}
function borntogive_post_excerpt_by_id( $post_id ) {
    global $post;
    $post = get_post( $post_id );
    setup_postdata( $post );
    $the_excerpt = get_the_excerpt();
    wp_reset_postdata();
    return $the_excerpt;
}

/* -------------------------------------------------------------------------------------
  RevSlider ShortCode
  ----------------------------------------------------------------------------------- */
if(!function_exists('borntogive_RevSliderShortCode')){
    function borntogive_RevSliderShortCode(){
        $slidernames = array();
        if(class_exists('RevSlider')){
            $sld = new RevSlider();
            $sliders = $sld->getArrSliders();
            if(!empty($sliders)){
                foreach($sliders as $slider){
                    $title = $slider->title;
                    $slidernames[esc_attr($slider->id)] = $title;
                }
            }
        }
        return $slidernames;
    }
}
add_filter( 'widget_text', 'do_shortcode', 11);
/* -------------------------------------------------------------------------------------
Month Translate in Default.
  ----------------------------------------------------------------------------------- */
    if(!function_exists('borntogive_month_translate')){
function borntogive_month_translate( $str ) {
  
	$options = get_option('borntogive_options');
       $months = (isset($options["calendar_month_name"]))?$options["calendar_month_name"]:'';
    $months = explode(',',$months);
  if(count($months)<=1){
  $months = array("January","February","March","April","May","June","July","August","September","October","November","December");
}
$sb = array();
foreach($months as $month) { $sb[] = $month; } 
    $engMonth = array("January","February","March","April","May","June","July","August","September","October","November","December");
    $trMonth = $sb;
    $converted = str_replace($engMonth, $trMonth, $str);
    return $converted;
    }
    /* -------------------------------------------------------------------------------------
  Filter the  Month name of Post.
  ----------------------------------------------------------------------------------- */
add_filter( 'get_the_time', 'borntogive_month_translate' );
add_filter( 'the_date', 'borntogive_month_translate' );
add_filter( 'get_the_date', 'borntogive_month_translate' );
add_filter( 'comments_number', 'borntogive_month_translate' );
add_filter( 'get_comment_date', 'borntogive_month_translate' );
add_filter( 'get_comment_time', 'borntogive_month_translate' );
add_filter( 'date_i18n', 'borntogive_month_translate' );
}
/* -------------------------------------------------------------------------------------
  Short Month Translate in Default.
  ----------------------------------------------------------------------------------- */
if(!function_exists('borntogive_short_month_translate')){
function borntogive_short_month_translate( $str ) {
    
       $options = get_option('borntogive_options');
       $months = (isset($options["calendar_month_name_short"]))?$options["calendar_month_name_short"]:'';
    $months = explode(',',$months);
  if(count($months)<=1){
  $months = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
}
$sb = array();
foreach($months as $month) { $sb[] = $month; } 
    $engMonth = array("/\bJan\b/","/\bFeb\b/","/\bMar\b/","/\bApr\b/","/\bMay\b/","/\bJun\b/","/\bJul\b/","/\bAug\b/","/\bSep\b/","/\bOct\b/","/\bNov\b/","/\bDec\b/");
    $trMonth = $sb;
    $converted = preg_replace($engMonth, $trMonth, $str);
    return $converted;
}
/* -------------------------------------------------------------------------------------
  Filter the  Sort Month name of Post.
  ----------------------------------------------------------------------------------- */
add_filter( 'get_the_time', 'borntogive_short_month_translate' );
add_filter( 'the_date', 'borntogive_short_month_translate' );
add_filter( 'get_the_date', 'borntogive_short_month_translate' );
add_filter( 'comments_number', 'borntogive_short_month_translate' );
add_filter( 'get_comment_date', 'borntogive_short_month_translate' );
add_filter( 'get_comment_time', 'borntogive_short_month_translate' );
add_filter( 'date_i18n', 'borntogive_short_month_translate' );
}
 if(!function_exists('borntogive_day_translate')){
function borntogive_day_translate( $str ) {
	$options = get_option('borntogive_options');
       $days = (isset($options["calendar_day_name"]))?$options["calendar_day_name"]:'';;
    $days = explode(',',$days);
  if(count($days)<=1){
  $days = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
}
$sb = array();
foreach($days as $month) { $sb[] = $month; } 
    $engDay = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
    $trDay = $sb;
    $converted = str_replace($engDay, $trDay, $str);
    return $converted;
    }
    /* -------------------------------------------------------------------------------------
  Filter the  Day name of Post.
  ----------------------------------------------------------------------------------- */
add_filter('date_i18n', 'borntogive_day_translate');
}
// UPDATING CHARITABLE SETTINGS DATA
function borntogive_charitable_plugin_settings_update(){
	if(!get_option( 'charitable_settings' )){
		$ss = 'a:14:{s:15:"active_gateways";a:1:{s:7:"offline";s:26:"Charitable_Gateway_Offline";}s:15:"default_gateway";s:7:"offline";s:7:"section";s:7:"general";s:7:"country";s:2:"AU";s:8:"currency";s:3:"AUD";s:15:"currency_format";s:4:"left";s:17:"decimal_separator";s:1:".";s:19:"thousands_separator";s:1:",";s:13:"decimal_count";s:1:"2";s:21:"donation_form_display";s:5:"modal";s:12:"profile_page";s:2:"87";s:10:"login_page";s:2:"wp";s:17:"registration_page";s:2:"wp";s:21:"donation_receipt_page";s:4:"auto";}';
		$overriting_settings = unserialize($ss);
		update_option('charitable_settings', $overriting_settings);
	}
}
add_action('after_switch_theme','borntogive_charitable_plugin_settings_update');
if(!function_exists('borntogive_merge_content'))
{
	function borntogive_merge_content()
	{
		$campaign_arg = array('post_type'=>'campaign', 'posts_per_page'=>-1);
		$campaign_posts = new WP_Query($campaign_arg);
		if($campaign_posts->have_posts()):while($campaign_posts->have_posts()):$campaign_posts->the_post();
		$editor_meta = get_post_meta(get_the_ID(), 'borntogive_campaign_editor', true);
		$content = get_post_field('post_content', get_the_ID());
		if($editor_meta!=''&&$content=='')
		{
			$campaign_coontent = array('ID'=>get_the_ID(), 'post_content'=>$editor_meta);
			wp_update_post($campaign_coontent);
		}
		endwhile; endif; wp_reset_postdata();
		update_option('borntogive_campaign_merge', 1);
	}
	$merge_status = get_option('borntogive_campaign_merge');
	if($merge_status!=1)
	{
		borntogive_merge_content();
	}
}

 /**
 * IMIC SEARCH BUTTON
 */
if(!function_exists('imic_search_button_header')){
function imic_search_button_header(){
global $options;
			
            echo '<div class="search-module">
                	<a href="#" class="search-module-trigger"><i class="fa fa-search"></i></a>
                    <div class="search-module-opened">
                    	 <form method="get" id="searchform" action="' .home_url('/').'/">
                        	<div class="input-group input-group-sm">
                        		<input type="text" name="s" id="s" class="form-control input-sm">
                            	<span class="input-group-btn"><button name ="submit" type="submit" class="btn btn-lg btn-default"><i class="fa fa-search"></i></button></span>
                       		</div>
                        </form>
                    </div>
                </div>';
	}
}
// IMIC CART BUTTON
if(!function_exists('imic_cart_button_header')){
	function imic_cart_button_header(){
		if(class_exists('Woocommerce')):
			$wcurrency = get_woocommerce_currency_symbol();
			 ?>
			<div class="cart-module header-equaler"><div><div>
				<a href="#" class="cart-module-trigger" id="cart-module-trigger"><i class="fa fa-shopping-cart"></i><span class="cart-tquant">

						<span class="cart-contents">
						</span>
					</span></a>
				<div class="cart-module-opened">
					<div class="cart-module-items">

						<div class="header-quickcart"></div>

					</div>
				</div>
			</div></div></div>
		<?php endif;
	}
}


// Random ID Generator
if(!function_exists('borntogive_mapRandomId')){
	function borntogive_mapRandomId($length = 6) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
}

$default_attribs = array('data-skin' => array(),'data-layout' => array(),'name' => array(),'action' => array(),'method' => array(),'type' => array(),'placeholder' => array(),'data-padding' => array(),'data-margin' => array(),'data-autoplay-timeout' => array(),'data-loop' => array(),'data-rtl' => array(),'data-auto-height' => array(),'data-displayinput' => array(), 'data-readonly' => array(), 'value' => array(), 'data-fgcolor' => array(), 'data-bgcolor' => array(), 'data-thickness' => array(), 'data-linecap' => array(), 'data-option-value' => array(), 'data-style' => array(), 'data-pause' => array(), 'data-speed' => array(), 'data-option-key' => array(), 'data-sort-id' => array(),'href' => array(),'rel' => array(),'data-appear-progress-animation' => array(),'data-appear-animation-delay' => array(), 'target' => array('_blank','_self','_top'), 'data-items-mobile' => array(), 'data-items-tablet' => array(), 'data-items-desktop-small' => array(), 'data-items-desktop' => array(), 'data-single-item' => array(), 'data-arrows' => array(), 'data-pagination' => array(), 'data-autoplay' => array(), 'data-columns' => array(), 'data-columns-tab' => array(), 'data-columns-mobile' => array(), 'width' => array(), 'data-srcset' => array(), 'height' => array(), 'src' => array(), 'id' => array(), 'class' => array(), 'title' => array(), 'style' => array(), 'alt' => array(), 'data' => array(), 'data-mce-id' => array(), 'data-mce-style' => array(), 'data-mce-bogus' => array());

$borntogive_allowed_tags = array(
	'div'           => $default_attribs,
	'span'          => $default_attribs,
	'p'             => $default_attribs,
	'a'             => $default_attribs,
	'u'             => $default_attribs,
	'i'             => $default_attribs,
	'q'             => $default_attribs,
	'b'             => $default_attribs,
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
	'h1'            => $default_attribs,
	'h2'            => $default_attribs,
	'h3'            => $default_attribs,
	'h4'            => $default_attribs,
	'h5'            => $default_attribs,
	'h6'            => $default_attribs,
	'cite'          => $default_attribs,
	'img'           => $default_attribs,
	'section'       => $default_attribs,
	'iframe'        => $default_attribs,
	'input'         => $default_attribs,
	'label'         => $default_attribs,
	'canvas'        => $default_attribs,
	'form'        	=> $default_attribs,
	'sub'        	=> $default_attribs,
	'sup'        	=> $default_attribs,
);

function borntogive_date_localization($format = "Y-m-d H:i:s", $timestamp = null, $timezone = null){
	$timestamp_with_offset = ($timestamp)?$timestamp:false;
	$gmt = ($timezone)?$timezone:false;
	return date_i18n($format, $timestamp_with_offset, $gmt);
}
?>