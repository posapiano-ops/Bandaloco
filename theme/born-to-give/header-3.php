<?php
$borntogive_options = get_option('borntogive_options');
$menu_locations = get_nav_menu_locations();
$logo = (isset($borntogive_options['logo_upload'])) ? $borntogive_options['logo_upload'] : '';
$logo_retina = (isset($borntogive_options['retina_logo_upload'])) ? $borntogive_options['retina_logo_upload'] : '';
$logo_sticky = (isset($borntogive_options['logo_upload_sticky'])) ? $borntogive_options['logo_upload_sticky'] : '';
$logo_retina_sticky = (isset($borntogive_options['retina_logo_upload_sticky'])) ? $borntogive_options['retina_logo_upload_sticky'] : '';
$logo_url = $logo['url'];
$logo_retina_url = (isset($borntogive_options['retina_logo_upload']) && !empty($borntogive_options['retina_logo_upload']['url']) != '') ? $logo_retina['url'] : $logo_url;
$logo_sticky_url = (isset($borntogive_options['logo_upload_sticky']) && !empty($borntogive_options['logo_upload_sticky']['url']) != '') ? $logo_sticky['url'] : $logo_url;
$logo_retina_sticky_url_check = (isset($borntogive_options['retina_logo_upload_sticky']) && !empty($borntogive_options['retina_logo_upload_sticky']['url']) != '') ? $logo_retina_sticky['url'] : '';

if ($logo_retina_sticky_url_check != '') {
    $logo_retina_sticky_url = $logo_retina_sticky_url_check;
} elseif ($logo_sticky_url != '') {
    $logo_retina_sticky_url = $logo_sticky_url;
} elseif ($logo_retina_url != '') {
    $logo_retina_sticky_url = $logo_retina_url;
} elseif ($logo_url != '') {
    $logo_retina_sticky_url = $logo_url;
} else {
    $logo_retina_sticky_url = '';
}
$retina_logo_width = (isset($borntogive_options['retina_logo_width'])) ? $borntogive_options['retina_logo_width'] : '199';
$retina_logo_height = (isset($borntogive_options['retina_logo_height'])) ? $borntogive_options['retina_logo_height'] : '30';
$sticky_retina_logo_width = (isset($borntogive_options['sticky_retina_logo_width'])) ? $borntogive_options['sticky_retina_logo_width'] : '199';
$sticky_retina_logo_height = (isset($borntogive_options['sticky_retina_logo_height'])) ? $borntogive_options['sticky_retina_logo_height'] : '30';
$topmenu = (isset($borntogive_options['topbar_menu_replace'])) ? $borntogive_options['topbar_menu_replace'] : '';
?>
<header class="topbar">
    <div class="container">
        <ul class="social-icons topmenu pull-right">
            <?php
            $socialSites = (isset($borntogive_options['header_social_links'])) ? $borntogive_options['header_social_links'] : array();
            if ($socialSites) {
                foreach ($socialSites as $key => $value) {
                    $string = substr($key, 3);
                    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        echo '<li class="' . $string . '"><a href="mailto:' . $value . '"><i class="fa ' . $key . '"></i></a></li>';
                    }
                    if (filter_var($value, FILTER_VALIDATE_URL)) {
                        echo '<li class="' . $string . '"><a href="' . esc_url($value) . '" target="_blank"><i class="fa ' . $key . '"></i></a></li>';
                    } elseif ($key == 'fa-skype' && $value != '') {
                        echo '<li class="' . $string . '"><a href="skype:' . $value . '?call"><i class="fa ' . $key . '"></i></a></li>';
                    }
                }
            }

            ?>
        </ul>

        <?php if ($topmenu != '') {
            wp_nav_menu(array('menu' => $topmenu, 'container' => '', 'items_wrap' => '<ul id="%1$s" class="topmenu sf-menu">%3$s</ul>'));
        } else { ?>
            <?php if (isset($borntogive_options['header_info_1_text']) && $borntogive_options['header_info_1_text'] != '') { ?>
                <div class="header-info-col">
                    <?php if ($borntogive_options['header_info_1_icon'] != '') { ?><i class="fa <?php echo esc_attr($borntogive_options['header_info_1_icon']); ?>"></i><?php } ?> <strong><?php echo esc_attr($borntogive_options['header_info_1_text']); ?></strong>
                </div>
            <?php }
        if (isset($borntogive_options['header_info_2_text']) && $borntogive_options['header_info_2_text'] != '') { ?>
                <div class="header-info-col">
                    <?php if ($borntogive_options['header_info_2_icon'] != '') { ?><i class="fa <?php echo esc_attr($borntogive_options['header_info_2_icon']); ?>"></i><?php } ?> <strong><?php echo esc_attr($borntogive_options['header_info_2_text']); ?></strong>
                </div>
            <?php }
    } ?>
    </div>
</header>
<!-- Site Header Wrapper -->
<div class="site-header-wrapper">
    <!-- Site Header -->
    <header class="site-header">
        <div class="container">
            <?php if (isset($borntogive_options['enable-search']) && $borntogive_options['enable-search'] == 1) {
                imic_search_button_header();
            } ?>
            <?php if (isset($borntogive_options['enable-cart']) && $borntogive_options['enable-cart'] == 1) {
                echo imic_cart_button_header();
            } ?>
            <div class="site-logo">
                <?php if ($logo_url == '' && $logo_retina_url == '' && $logo_sticky_url == '' && $logo_retina_sticky_url == '') { ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        <span class="site-name"><?php echo get_bloginfo('name'); ?></span>
                        <span class="site-tagline"><?php echo get_bloginfo('description'); ?></span>
                    </a>
                <?php } else { ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="default-logo"><img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo get_bloginfo('name'); ?>"></a>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="default-retina-logo"><img src="<?php echo esc_url($logo_retina_url); ?>" alt="<?php echo get_bloginfo('name'); ?>" width="<?php echo esc_attr($retina_logo_width); ?>" height="<?php echo esc_attr($retina_logo_height); ?>"></a>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="sticky-logo"><img src="<?php echo esc_url($logo_sticky_url); ?>" alt="<?php echo get_bloginfo('name'); ?>"></a>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="sticky-retina-logo"><img src="<?php echo esc_url($logo_retina_sticky_url); ?>" alt="<?php echo get_bloginfo('name'); ?>" width="<?php echo esc_attr($sticky_retina_logo_width); ?>" height="<?php echo esc_attr($sticky_retina_logo_height); ?>"></a>
                <?php } ?>
            </div>
            <a href="#" class="visible-sm visible-xs" id="menu-toggle"><i class="fa fa-bars"></i></a>
        </div>
    </header>
</div>
<div class="fw-menu-wrapper">
    <div class="container">
        <?php if (!empty($menu_locations['primary-menu'])) {
            wp_nav_menu(array('theme_location' => 'primary-menu', 'container' => '', 'items_wrap' => '<ul id="%1$s" class="sf-menu dd-menu pull-right">%3$s</ul>', 'walker' => new borntogive_mega_menu_walker));
        } ?>
    </div>
</div>