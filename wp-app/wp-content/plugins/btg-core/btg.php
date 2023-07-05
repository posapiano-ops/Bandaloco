<?php
/* 
 * Plugin Name: Born To Give Core
 * Plugin URI:  http://www.imithemes.com
 * Description: Create Post Types for Born To Give Theme
 * Author:      imithemes
 * Version:     2.2
 * Author URI:  http://www.imithemes.com
 * Licence:     GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Copyright:   (c) 2021 imithemes. All rights reserved
 * Text Domain: borntogive-core
 * Domain Path: /language
 */

// Do not allow direct access to this file.
defined('ABSPATH') or die('No script kiddies please!');
$path = plugin_dir_path(__FILE__);
define('BTG__PLUGIN_PATH', plugin_dir_path(__FILE__));
define('BTG__PLUGIN_URL', plugin_dir_url(__FILE__));
/* CUSTOM POST TYPES
================================================== */
//require_once $path . '/imic-post-type-permalinks.php';
require_once $path . '/custom-post-types/testimonial-type.php';
require_once $path . '/custom-post-types/gallery-type.php';
require_once $path . '/custom-post-types/event-type.php';
require_once $path . '/custom-post-types/team-type.php';
require_once $path . '/custom-post-types/imi-vc-section.php';

/* FUNCTION INCLUDES
================================================== */
require_once BTG__PLUGIN_PATH . 'includes.php';

/* SET LANGUAGE FILE FOLDER
=================================================== */
add_action('after_setup_theme', 'btg_core_setup');
function btg_core_setup()
{
    load_theme_textdomain('borntogive-core', plugin_dir_path(__FILE__) . '/language');
}