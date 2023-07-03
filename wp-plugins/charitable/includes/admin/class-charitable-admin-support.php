<?php
/**
 * Contains the class that is used for dedicated troubleshooting/support.
 *
 * @package   Charitable/Classes/Charitable_Admin_Support
 * @author    David Bisset
 * @copyright Copyright (c) 2022, WP Charitable LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.7.0.3
 * @version   1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Charitable_Admin_Support' ) ) :

	/**
	 * Charitable_Admin_Support
	 *
	 * @since 1.4.6
	 */
	class Charitable_Admin_Support {

		/**
		 * The single instance of this class.
		 *
		 * @var     Charitable_Admin_Support|null
		 */
		private static $instance = null;

		/**
		 * Create class object. A private constructor, so this is used in a singleton context.
		 *
		 * @since 1.4.6
		 * @since 1.5.4 Access changed to public.
		 */
		public function __construct() {

		}

		/**
		 * Checks and sees if there is anything being manually passed into the query vars.
		 *
		 * @since  1.7.0.3
		 *
		 */
		public static function maybe_do_charitable_support_query() {
            if ( ! is_admin() || ! isset( $_GET[ 'charitable-support' ] ) ) {
                return;
            }

            switch ( esc_html( $_GET[ 'charitable-support' ] ) ) {
                case 'clear-notifications':
                    delete_option( 'charitable_notifications' ); // delete the entire option in the database
                    if ( defined( 'CHARITABLE_DEBUG' ) && CHARITABLE_DEBUG ) {
                        error_log( 'charitable-support: clear-notification' );
                    }
                    break;

                default:
                    # code...
                    break;
            }

		}

		/**
		 * Returns and/or create the single instance of this class.
		 *
		 * @since  1.7.0.3
		 *
		 * @return Charitable_Admin_Support
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

	}

endif;
