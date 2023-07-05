<?php
/**
 * Importer
 *
 * Radium Importer - Modified For ReduxFramework
 * @link https://github.com/FrankM1/radium-one-click-demo-install
 *
 * @package     WBC_Importer - Extension for Importing demo content
 * @author      Webcreations907
 * @version     1.0.3
 */

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// Don't duplicate me!
if (!class_exists('ReduxFramework_extension_wbc_importer')) {
    class ReduxFramework_extension_wbc_importer
    {

        public static $instance;

        static $version = "1.0.3";

        protected $parent;

        private $filesystem = array();

        public $extension_url;
        public $extension_dir;
        public $demo_data_dir;

        public $wbc_import_files = array();

        public $active_import_id;
        public $active_import;
        public $import_contents;
        public $fetch_attachments;
        public $import_sliders;
        public $import_theme_opts;
        public $import_widgets;
        public $page_builder;
        private $imi_dir;
        private $demo_dir;

        /**
         * Class Constructor
         *
         * @since       1.0
         * @access      public
         * @return      void
         */
        public function __construct($parent)
        {
            $this->parent = $parent;

            if (!is_admin()) return;

            // Hides importer section if anything but true returned. Way to abort :)
            if (true !== apply_filters('wbc_importer_abort', true)) {
                return;
            }

            $this->imi_dir = ABSPATH . 'wp-content/uploads/imithemes/';
            $this->demo_dir = $this->imi_dir . 'demo-data/';

            if (empty($this->extension_dir)) {
                $this->extension_dir = trailingslashit(str_replace('\\', '/', dirname(__FILE__)));
                $this->extension_url = site_url(str_replace(trailingslashit(str_replace('\\', '/', ABSPATH)), '', $this->extension_dir));
                $this->demo_data_dir = apply_filters("wbc_importer_dir_path", $this->demo_dir);
            }

            // Delete saved options of imported demos, for dev/testing purpose
            // delete_option('wbc_imported_demos');

            $this->getImports();

            $this->field_name = 'wbc_importer';

            self::$instance = $this;

            add_filter('redux/' . $this->parent->args['opt_name'] . '/field/class/' . $this->field_name, array(
                &$this,
                'overload_field_path'
            ));

            // call ajax actions
            add_action('wp_ajax_redux_wbc_importer', array($this, 'ajax_importer'));
            add_action('wp_ajax_imi_create_demo_dir', array($this, 'create_demo_dir'));

            add_filter('redux/' . $this->parent->args['opt_name'] . '/field/wbc_importer_files', array($this, 'addImportFiles'));

            // Adds Importer section to panel
            // $this->add_importer_section();

            include $this->extension_dir . 'inc/class-wbc-importer-progress.php';
            $wbc_progress = new Wbc_Importer_Progress($this->parent);

            include_once BTG__PLUGIN_PATH . '/imi-admin/theme-options/extensions/wbc_importer/imi-wbc-configs.php';
        }

        /**
         * Get the demo folders/files
         * Provided fallback where some host require FTP info
         *
         * @return array list of files for demos
         */
        public function demoFiles()
        {
            $fix_options = array(
                'content.xml' => array(
                    'name' => 'content.xml',
                    'type' => 'f',
                ),
                'screen-image.jpg' => array(
                    'name' => "screen-image.jpg",
                    'type' => "f"
                ),
                'theme_options.txt' => array(
                    'name' => "theme-options.txt",
                    'type' => "f"
                ),
                'widgets.json' => array(
                    'name' => "widgets.json",
                    'type' => "f"
                ),
            );

            // imi
            $dir_array = array(
                'classic' => array(
                    'name' => 'classic',
                    'type' => 'd',
                    'display_name' => 'Classic Demo',
                    'preview_url' => 'https://demo.imithemes.com/born-to-give/',
                    'import_name' => 'wbc-import-1',
                    'files' => array_merge($fix_options, array(
                        'revslider.zip' => array(
                            'name' => 'revslider.zip',
                            'type' => 'f',
                        ),
                        'duotone1.zip' => array(
                            'name' => 'duotone1.zip',
                            'type' => 'f',
                        ),
                        'news-background-video5.zip' => array(
                            'name' => 'news-background-video5.zip',
                            'type' => 'f',
                        )
                    )),
                ),

            );

            return $dir_array;
        }

        public function getImports()
        {

            if (!empty($this->wbc_import_files)) {
                return $this->wbc_import_files;
            }

            $imports = $this->demoFiles();

            $imported = get_option('wbc_imported_demos');

            if (!empty($imports) && is_array($imports)) {
                $x = 1;
                foreach ($imports as $import) {

                    if (!isset($import['files']) || empty($import['files'])) {
                        continue;
                    }

                    if ($import['type'] == "d" && !empty($import['name'])) {
                        $this->wbc_import_files['wbc-import-' . $x] = isset($this->wbc_import_files['wbc-import-' . $x]) ? $this->wbc_import_files['wbc-import-' . $x] : array();
                        $this->wbc_import_files['wbc-import-' . $x]['directory'] = $import['name'];

                        if (!empty($imported) && is_array($imported)) {
                            if (array_key_exists('wbc-import-' . $x, $imported)) {
                                $this->wbc_import_files['wbc-import-' . $x]['imported'] = 'imported';
                            }
                        }

                        foreach ($import['files'] as $file) {

                            switch ($file['name']) {
                                case 'content.xml':
                                    $this->wbc_import_files['wbc-import-' . $x]['content_file'] = $file['name'];
                                    break;

                                case 'theme-options.txt':
                                case 'theme-options.json':
                                    $this->wbc_import_files['wbc-import-' . $x]['theme_options'] = $file['name'];
                                    break;

                                case 'widgets.json':
                                case 'widgets.txt':
                                    $this->wbc_import_files['wbc-import-' . $x]['widgets'] = $file['name'];
                                    break;

                                case 'screen-image.png':
                                case 'screen-image.jpg':
                                case 'screen-image.gif':
                                    $this->wbc_import_files['wbc-import-' . $x]['image'] = $file['name'];
                                    break;
                            }
                        }

                        $this->wbc_import_files['wbc-import-' . $x]['preview'] = $import['preview_url'];
                        $this->wbc_import_files['wbc-import-' . $x]['display'] = $import['display_name'];
                    }

                    $x++;
                }
            }
        }

        public function addImportFiles($wbc_import_files)
        {

            if (!is_array($wbc_import_files) || empty($wbc_import_files)) {
                $wbc_import_files = array();
            }

            $wbc_import_files = wp_parse_args($wbc_import_files, $this->wbc_import_files);

            return $wbc_import_files;
        }

        public function create_demo_dir()
        {
			
			if ( is_multisite() ) {

                // Get site option upload_filetypes
                $upload_filetypes   = explode( ' ', get_site_option( 'upload_filetypes' ) );
                $array_size         = sizeof($upload_filetypes);
                
                // add zip format to upload_filetypes
                if ( ! in_array( 'zip', $upload_filetypes ) ) {
                    
                    $upload_filetypes[$array_size] = 'zip';

                    // change format to string with one space
                    $upload_filetypes = implode( ' ', $upload_filetypes );

                    // update upload_filetypes
                    update_site_option( 'upload_filetypes', $upload_filetypes );
                
                }

            }

            $this->active_import_id     = $_REQUEST['demo_import_id'];

            // Get target importer directory
            $get_all_demoes = $this->demoFiles();
            $target_demo = '';
            foreach ($get_all_demoes as $demo) {
                if ($demo['import_name'] == $this->active_import_id) {
                    $target_demo = $demo['name'];
                }
            }

            // Create demo-data folder
            if (wp_mkdir_p($this->demo_dir . $target_demo)) {
                wp_mkdir_p($this->demo_dir . $target_demo);
            }

            // Upload files in target folder
            //$http = new WP_Http();
            $value = '';

            if (imi_check_url('https://data.imithemes.com/demo-data/borntogive/' . $target_demo . '/' . $target_demo . '.zip')) {
                $value = 'https://data.imithemes.com/demo-data/borntogive/' . $target_demo . '/' . $target_demo . '.zip';
            } else {
                $value = 'https://preview.imithemes.com/demo-data/borntogive/' . $target_demo . '/' . $target_demo . '.zip';
            }

            //$response = $http->request( $value );
            $get_file = wp_remote_get($value, array('timeout' => 120, 'httpversion' => '1.1',));

            $upload = wp_upload_bits(basename($value), '', wp_remote_retrieve_body($get_file));
            if (!empty($upload['error'])) {
                return false;
            }

            // unzip demo files
            if ( class_exists('ZipArchive', false) == false ) {
                require_once ( 'zip-extention/zip.php' );
                $zip = new Zip();
                $zip->unzip_file( $upload['file'], $this->demo_dir . $target_demo . '/' );

            } else {

                $zip = new ZipArchive;
                $success_unzip = '';
                if ( $zip->open( $upload['file'] ) === TRUE ) {
                    $zip->extractTo( $this->demo_dir . $target_demo . '/' );
                    $zip->deleteAfterUnzip = true;
                    $zip->close();
                    $success_unzip = 'success';
                } else {
                    $success_unzip = 'failed';
                }

            }
        }

        public function ajax_importer()
        {

            if (is_plugin_active('wordpress-importer/wordpress-importer.php')) {
                deactivate_plugins('/wordpress-importer/wordpress-importer.php');
            }

            wp_delete_post(1, false);

            if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], "redux_{$this->parent->args['opt_name']}_wbc_importer")) {
                die(0);
            }

            if (isset($_REQUEST['type']) && $_REQUEST['type'] == "import-demo-content" && array_key_exists($_REQUEST['demo_import_id'], $this->wbc_import_files)) {
                $reimporting = false;

                if (isset($_REQUEST['wbc_import']) && $_REQUEST['wbc_import'] == 're-importing') {
                    $reimporting = true;
                }

                $this->active_import_id     = $_REQUEST['demo_import_id'];
                $this->import_contents   = ($_REQUEST['import_contents'] == 'yes') ? true : false;
                // $this->import_contents      = true;
                $this->fetch_attachments    = ($_REQUEST['fetch_attachments'] == 'yes') ? true : false;
                $this->import_sliders       = ($_REQUEST['import_sliders'] == 'yes') ? true : false;
                $this->import_theme_opts    = ($_REQUEST['import_theme_opts'] == 'yes') ? true : false;
                $this->import_widgets       = ($_REQUEST['import_widgets'] == 'yes') ? true : false;


                $this->active_import = array($this->active_import_id => $this->wbc_import_files[$this->active_import_id]);

                if (!isset($import_parts['imported']) || true === $reimporting) {
                    include $this->extension_dir . 'inc/init-installer.php';
                    $installer = new Radium_Theme_Demo_Data_Importer($this, $this->parent);
                } else {
                    echo esc_html__("Demo Already Imported", 'borntogive');
                }

                die();
            }

            die();
        }

        public static function get_instance()
        {
            return self::$instance;
        }

        // Forces the use of the embeded field path vs what the core typically would use
        public function overload_field_path($field)
        {
            return dirname(__FILE__) . '/' . $this->field_name . '/field_' . $this->field_name . '.php';
        }

        function add_importer_section()
        {
            // Checks to see if section was set in config of redux.
            for ($n = 0; $n <= count($this->parent->sections); $n++) {
                if (isset($this->parent->sections[$n]['id']) && $this->parent->sections[$n]['id'] == 'wbc_importer_section') {
                    return;
                }
            }

            $wbc_importer_label = trim(esc_html(apply_filters('wbc_importer_label', __('Demo Importer', 'borntogive'))));

            $wbc_importer_label = (!empty($wbc_importer_label)) ? $wbc_importer_label : __('Demo Importer', 'borntogive');

            $this->parent->sections[] = array(
                'id'     => 'wbc_importer_section',
                'title'  => $wbc_importer_label,
                'desc'   => '<p class="description">' . apply_filters('wbc_importer_description', esc_html__('Works best to import on a new install of WordPress', 'borntogive')) . '</p>',
                'icon'   => 'el-icon-website',
                'fields' => array(
                    array(
                        'id'   => 'wbc_demo_importer',
                        'type' => 'wbc_importer'
                    )
                )
            );
        }
    } // class
} // if
