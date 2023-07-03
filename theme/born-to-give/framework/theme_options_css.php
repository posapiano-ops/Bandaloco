<?php
$options_saved = get_option('borntogive_options');
$css = '';
if ($options_saved && isset($options_saved['content_background'])) {
    $saved_css = get_option('borntogive_dynamic_css');
    if ($saved_css == '') {
        $fonts_args = array('family' => '', 'subset' => '');
        $font_family = $font_subset = array();
        foreach ($options_saved as $key => $value) {
            if ($key == 'content_background') {
                $class = '.content';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'content_padding') {
                $class = '.content';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'search_cart_link_color') {
                $class = '.search-module-trigger, .cart-module-trigger';
                $style = 'color:' . $value['regular'] . ';';
                $css .= $class . '{' . $style . '}';
                $css .= $class . ':hover{color:' . $value['hover'] . ';}';
                $css .= $class . ':active{color:' . $value['active'] . ';}';
            } elseif ($key == 'header_info_typo') {
                $class = '.header-style1 .header-info-col';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'header_info_icon') {
                $class = '.header-info-col i';
                $style = '';
                $css .= $class . '{color:' . $value . '}';
            } elseif ($key == 'sticky_header_background') {
                $class = '.sticky.site-header';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'sticky_link_color') {
                $class = '.sticky .dd-menu > li > a';
                $style = 'color:' . $value['regular'] . ';';
                $css .= $class . '{' . $style . '}';
                $css .= $class . ':hover{color:' . $value['hover'] . ';}';
                $css .= $class . ':active{color:' . $value['active'] . ';}';
            } elseif ($key == 'inner_page_header_background') {
                $class = '.page-banner';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'inner_page_header_title_typography') {
                $class = '.page-banner h1, .page-banner-text';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'header_info_link_color') {
                $class = '.topmenu a';
                $style = 'color:' . $value['regular'] . ';';
                $css .= $class . '{' . $style . '}';
                $css .= $class . ':hover{color:' . $value['hover'] . ';}';
                $css .= $class . ':active{color:' . $value['active'] . ';}';
            } elseif ($key == 'logo_spacing') {
                $class = '.site-logo';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'nav_items_spacing') {
                $class = '.header-style1 .dd-menu > li, .header-style2 .dd-menu > li';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'main_nav_link_typo') {
                $class = '.dd-menu > li > a';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'main_nav_link') {
                $class = '.dd-menu > li > a';
                $style = 'color:' . $value['regular'] . ';';
                $css .= $class . '{' . $style . '}';
                $css .= $class . ':hover{color:' . $value['hover'] . ';}';
                $css .= $class . ':active{color:' . $value['active'] . ';}';
            } elseif ($key == 'main_nav_link2') {
                $class = '.header-style2 .dd-menu > li > a';
                $style = 'color:' . $value['regular'] . ';';
                $css .= $class . '{' . $style . '}';
                $css .= $class . ':hover{color:' . $value['hover'] . ';}';
                $css .= $class . ':active{color:' . $value['active'] . ';}';
            } elseif ($key == 'main_nav_link3') {
                $class = '.header-style3 .dd-menu > li > a';
                $style = 'color:' . $value['regular'] . ';';
                $css .= $class . '{' . $style . '}';
                $css .= $class . ':hover{color:' . $value['hover'] . ';}';
                $css .= $class . ':active{color:' . $value['active'] . ';}';
            } elseif ($key == 'fw_menu_background') {
                $class = '.header-style3 .fw-menu-wrapper';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'dd_background') {
                $class = '.dd-menu > li ul';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'dd_item_border' && isset($value['border-bottom'])) {
                $class = '.dd-menu > li > ul > li > a, .dd-menu > li > ul > li > ul > li > a, .dd-menu > li > ul > li > ul > li > ul > li > a';
                $style = 'border-bottom:' . $value['border-bottom'] . ' ' . $value['border-style'] . ' ' . $value['border-color'];
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'dd_item_spacing') {
                $class = '.dd-menu > li > ul > li > a, .dd-menu > li > ul > li > ul > li > a, .dd-menu > li > ul > li > ul > li > ul > li > a';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'dd_item_background') {
                $class = '.dd-menu > li > ul > li > a:hover, .dd-menu > li > ul > li > ul > li > a:hover, .dd-menu > li > ul > li > ul > li > ul > li > a:hover';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'dd_item_link_typo') {
                $class = '.dd-menu > li > ul > li > a, .dd-menu > li > ul > li > ul > li > a, .dd-menu > li > ul > li > ul > li > ul > li > a';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'dd_item_link_color') {
                $class = '.dd-menu > li > ul > li > a, .dd-menu > li > ul > li > ul > li > a, .dd-menu > li > ul > li > ul > li > ul > li > a';
                $style = 'color:' . $value['regular'] . ';';
                $css .= $class . '{' . $style . '}';
                $css .= $class . ':hover{color:' . $value['hover'] . ';}';
                $css .= $class . ':active{color:' . $value['active'] . ';}';
            } elseif ($key == 'mm_background') {
                $class = '.dd-menu > li.megamenu > ul';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'mm_title_typo') {
                $class = '.dd-menu .megamenu-container .megamenu-sub-title, .dd-menu .megamenu-container .widgettitle, .dd-menu .megamenu-container .widget-title';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'mm_content_typo') {
                $class = '.dd-menu .megamenu-container';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'menu_toggle_typo') {
                $class = '#menu-toggle';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'menu_toggle_color') {
                $class = '#menu-toggle';
                $style = 'color:' . $value['regular'] . ';';
                $css .= $class . '{' . $style . '}';
                $css .= $class . ':hover{color:' . $value['hover'] . ';}';
                $css .= $class . ':active{color:' . $value['active'] . ';}';
            } elseif ($key == 'menu_toggle_spacing') {
                $class = '#menu-toggle';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'footer_background') {
                $class = '.site-footer';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'footer_top_spacing') {
                $class = '.site-footer';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'tfooter_border') {
                $class = '.site-footer';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'widgettitle_typo') {
                $class = '.footer_widget h4.widgettitle, .footer_widget h4.widget-title';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'tfwidget_typo') {
                $class = '.site-footer .footer_widget';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'tfooter_link_color') {
                $class = '.body .site-footer .footer_widget a';
                $style = 'color:' . $value['regular'] . ';';
                $css .= $class . '{' . $style . '}';
                $css .= $class . ':hover{color:' . $value['hover'] . ';}';
                $css .= $class . ':active{color:' . $value['active'] . ';}';
            } elseif ($key == 'bfooter_bg') {
                $class = '.site-footer-bottom';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'footer_bottom_spacing') {
                $class = '.site-footer-bottom';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'bfooter_border') {
                $class = '.site-footer-bottom';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'bfwidget_typo') {
                $class = '.site-footer-bottom';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'bfooter_link_color') {
                $class = '.body .site-footer-bottom a';
                $style = 'color:' . $value['regular'] . ';';
                $css .= $class . '{' . $style . '}';
                $css .= $class . ':hover{color:' . $value['hover'] . ';}';
                $css .= $class . ':active{color:' . $value['active'] . ';}';
            } elseif ($key == 'share_before_typo') {
                $class = '.social-share-bar .share-title';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'share_icons_box_size') {
                $class = '.social-share-bar li a';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'share_icons_font_size') {
                $class = '.social-share-bar li a';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'heading_font_typography') {
                $class = 'h1,h2,h3,h4,h5,h6, .featured-link strong, .featured-text strong';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'body_font_typography') {
                $class = 'body, .widget h5';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'body_tag_typography') {
                $class = 'body';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'h1_tag_typography') {
                $class = 'h1';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'h2_tag_typography') {
                $class = 'h2';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'h3_tag_typography') {
                $class = 'h3';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'h4_tag_typography') {
                $class = 'h4';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'h5_tag_typography') {
                $class = 'h5';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'h6_tag_typography') {
                $class = 'h6';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            }













            if ($key == 'content_padding') {
                $class = '.content';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'header_background_image') {
                $class = '.middle-header';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'header_info_icon_typo') {
                $class = '.contact-info-blocks > div > i';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'header_info_title_typo') {
                $class = '.contact-info-blocks > div';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'header_info_text_typo') {
                $class = '.contact-info-blocks > div > span';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'topbar_info_typography') {
                $class = '.horiz-nav > li > a';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'topbar_social_icon_color') {
                $class = '.horiz-nav > li > a';
                $style = 'color:' . $value['regular'] . ';';
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'inner_page_header_background') {
                $class = '.page-header';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'page_header_padding') {
                $class = '.page-header';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'page_header_banner_height') {
                $class = '.page-header .col-md-12';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'page_header_map_height') {
                $class = '#onemap.map-single-page';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'inner_page_header_title_typography') {
                $class = '.page-header h1';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'menu_background') {
                $class = '.main-menu-wrapper';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'menu_typo') {
                $class = '.navigation > ul > li > a';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'menu_link_color') {
                $class = '.navigation > ul > li > a';
                $style = 'color:' . $value['regular'] . ';';
                $css .= $class . '{' . $style . '}';
                $css .= $class . ':hover{color:' . $value['hover'] . ';}';
                $css .= $class . ':active{color:' . $value['active'] . ';}';
            } elseif ($key == 'dd_background') {
                $class = '.navigation > ul > li ul, .navigation ul > li:hover > a';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'dd_item_border' && isset($value['border-bottom'])) {
                $class = '.navigation > ul > li > ul li > a';
                $style = 'border-bottom:' . $value['border-bottom'] . ' ' . $value['border-style'] . ' ' . $value['border-color'];
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'dd_item_link_typo') {
                $class = '.navigation > ul > li > ul li > a';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'dd_item_link_color') {
                $class = '.navigation > ul > li > ul li > a';
                $style = 'color:' . $value['regular'] . ';';
                $css .= $class . '{' . $style . '}';
                $css .= $class . ':hover{color:' . $value['hover'] . ';}';
                $css .= $class . ':active{color:' . $value['active'] . ';}';
            } elseif ($key == 'menu_toggle_typo') {
                $class = '.site-header .menu-toggle';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'menu_toggle_color') {
                $class = '.site-header .menu-toggle';
                $style = 'color:' . $value['regular'] . ';';
                $css .= $class . '{' . $style . '}';
                $css .= $class . ':hover{color:' . $value['hover'] . ';}';
                $css .= $class . ':active{color:' . $value['active'] . ';}';
            } elseif ($key == 'menu_toggle_spacing') {
                $class = '.site-header .menu-toggle';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'footer_background') {
                $class = '.site-footer';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'footer_border' && isset($value['border-bottom'])) {
                $class = '.site-footer';
                $style = 'border-top:' . $value['border-top'] . ' ' . $value['border-style'] . ' ' . $value['border-color'];
                //$css .= $class . '{' . $style . '}';
            } elseif ($key == 'footer_top_spacing') {
                $class = '.site-footer';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'widgettitle_typo') {
                $class = '.footer-widget .widgettitle, .footer-widget .widget-title';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'tfwidget_typo') {
                $class = '.site-footer .footer-widget';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'tfooter_link_color') {
                $class = '.body .site-footer .footer-widget a';
                $style = 'color:' . $value['regular'] . ';';
                $css .= $class . '{' . $style . '}';
                $css .= $class . ':hover{color:' . $value['hover'] . ';}';
                $css .= $class . ':active{color:' . $value['active'] . ';}';
            } elseif ($key == 'footer_bottom_spacing') {
                $class = '.site-footer-bottom';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'bfooter_border' && isset($value['border-bottom'])) {
                $class = '.site-footer-bottom';
                $style = 'border-top:' . $value['border-top'] . ' ' . $value['border-style'] . ' ' . $value['border-color'];
                //$css .= $class . '{' . $style . '}';
            } elseif ($key == 'bfwidget_typo') {
                $class = '.site-footer-bottom';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'bfooter_link_color') {
                $class = '.site-footer-bottom a';
                $style = 'color:' . $value['regular'] . ';';
                $css .= $class . '{' . $style . '}';
                $css .= $class . ':hover{color:' . $value['hover'] . ';}';
                $css .= $class . ':active{color:' . $value['active'] . ';}';
            } elseif ($key == 'footer_bottom_icons') {
                $class = '.site-footer-bottom .social-icons a';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'footer_bottom_icons_bg') {
                $class = '.site-footer-bottom .social-icons a';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'footer_bottom_icons') {
                $class = '.site-footer-bottom .social-icons a';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'footer_bottom_icons_dimension') {
                $class = '.site-footer-bottom .social-icons a';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'swidget_bg') {
                $class = '.sidebar .widget';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'swidget_padding') {
                $class = '.sidebar .widget';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'swidget_border') {
                $class = '.sidebar .widget';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'body_font_typo') {
                $class = 'body';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'body_h1_font_typo') {
                $class = 'h1';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'body_h2_font_typo') {
                $class = 'h2';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'body_h3_font_typo') {
                $class = 'h3';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'body_h4_font_typo') {
                $class = 'h4';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'body_h5_font_typo') {
                $class = 'h5';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'body_h6_font_typo') {
                $class = 'h6';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'share_before_typo') {
                $class = '.share-buttons .share-title';
                $style = '';
                $font_separator = '';
                $subset_separator = '';
                $font_family[] = (isset($value['google']) && $value['google'] == true && $value['font-family'] != '') ? $font_separator . $value['font-family'] : '';
                $font_subset[] = (isset($value['google']) && $value['google'] == true && $value['subsets'] != '') ? $subset_separator . $value['subsets'] : '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    if ($tag == 'background-image') {
                        $st = 'url("' . $st . '")';
                    }
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'share_icons_box_size') {
                $class = '.share-buttons > li > a';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            } elseif ($key == 'share_icons_font_size') {
                $class = '.share-buttons > li > a';
                $style = '';
                foreach ($value as $tag => $st) {
                    if ($st == '' || is_array($st) || $tag == 'google' || $tag == 'units') continue;
                    $style .= $tag . ':' . $st . ';';
                }
                $css .= $class . '{' . $style . '}';
            }
        }
        $font_family_implode = implode('|', array_unique(array_filter($font_family)));
        $font_subset_implode = implode(',', array_unique(array_filter($font_subset)));
        update_option('borntogive_dynamic_css', $css);
        update_option('borntogive_dynamic_fonts', array('family' => $font_family_implode, 'subset' => $font_subset_implode));
    } else {
        function borntogive_enqueue_dynamic_css()
        {
            $dynamic_css = get_option('borntogive_dynamic_css');
            $dynamic_fonts = get_option('borntogive_dynamic_fonts');
            if ($dynamic_css != '1' && $dynamic_css != '') {
                $theme_info = wp_get_theme();
                wp_enqueue_style('borntogive-fonts', add_query_arg($dynamic_fonts, "//fonts.googleapis.com/css"), array(), $theme_info->get('Version'), 'all');
                wp_add_inline_style('borntogive_main', $dynamic_css);
            }
        }
        add_action('wp_enqueue_scripts', 'borntogive_enqueue_dynamic_css', 9999);
    }
}
add_action('redux/options/imic_options/saved', 'borntogive_use_new_dynamic_css');
function realspaces_use_new_dynamic_css()
{
    update_option('borntogive_dynamic_css', '1');
}
