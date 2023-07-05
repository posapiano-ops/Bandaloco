<?php
/**
 * Class to assist with the setup of extension licenses.
 *
 * @package     Charitable/Classes/Charitable_Licenses
 * @version     1.4.20
 * @author      David Bisset
 * @copyright   Copyright (c) 2022, WP Charitable LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Charitable_Licenses' ) ) :

	/**
	 * Charitable_Licenses
	 *
	 * @since   1.0.0
	 */
	class Charitable_Licenses {

		/* @var string */
		const UPDATE_URL = 'https://wpcharitable.com';

		/**
		 * The single instance of this class.
		 *
		 * @var Charitable_Licenses|null
		 */
		private static $instance = null;

		/**
		 * All the registered products requiring licensing.
		 *
		 * @var array
		 */
		private $products;

		/**
		 * All the stored licenses.
		 *
		 * @var array
		 */
		private $licenses;

		/**
		 * Cached update data.
		 *
		 * @var array
		 */
		private $update_data;

		/**
		 * Returns and/or create the single instance of this class.
		 *
		 * @since  1.2.0
		 *
		 * @return Charitable_Licenses
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Create class object.
		 *
		 * @since 1.0.0
		 */
		private function __construct() {
			$this->products    = array();
			$this->update_data = array();

			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );
			add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3 );
			add_action( 'charitable_deactivate_license', array( $this, 'deactivate_license' ) );

			add_action( 'init', array( $this, 'ajax_license_check' ) );
			add_action( 'wp_ajax_charitable_license_check', array( $this, 'ajax_license_check' ) );
		}

		/**
		 * Checks licenses (new and legacy) to see if any are valid.
		 *
		 * @since  1.7.0
		 *
		 * @return boolean
		 */
		public function is_pro() {

			$products = $this->get_products();

			if ( is_multisite() ) {

				// get the main blog id to check for license
				$network = get_network();
				if ( ! $network ) {
					return 0;
				}

				$blog_id = $network->site_id;

				switch_to_blog( $blog_id );

				if ( $this->is_v3_license_valid() ) {
					return true;
				}

				$licenses = charitable_get_option( 'licenses', array() );

				// go through licenses - if there is at least ONE VALID ADDON LICNESE then it's pro.
				foreach ( $licenses as $slug => $license ) {
					if ( isset( $license['valid'] ) && 1 === intval( $license['valid'] ) ) {
						restore_current_blog();
						return true;
					}
				}

				restore_current_blog();

			} else {

				if ( empty( $products ) ) {
					return false;
				}

				if ( $this->is_v3_license_valid() ) {
					return true;
				}

				// fallback: go through licenses - if there is at least ONE VALID ADDON LICNESE then it's pro.
				foreach ( $products as $slug => $item ) {
					$valid = $this->has_valid_license( $slug );
					if ( $valid ) {
						return true;
					}
				}
			}
		}

		/**
		 * Checks the "v3" license (starting Nov 2022) to see if it's valid.
		 * If valid, normally doesn't require other license/pro checks.
		 *
		 * @since  1.7.0.4
		 *
		 * @return boolean
		 */
		public function is_v3_license_valid() {

			$settings = get_option( 'charitable_settings' );
			if ( false === $settings || ! isset( $settings['licenses']['charitable-v2'] ) || ! isset( $settings['licenses']['charitable-v2'][ 'valid' ] ) ) {
				return false;
			}
			$valid = $settings['licenses']['charitable-v2'][ 'valid' ];
			if ( 1 === intval( $valid ) ) {
				return true;
			}

			return false;

		}

		/**
		 * Checks for any Charitable extensions with updates.
		 *
		 * @since  1.4.0
		 *
		 * @param  array $_transient_data The plugin updates data.
		 * @return array
		 */
		public function check_for_updates( $_transient_data ) {
			global $pagenow;

			if ( ! is_object( $_transient_data ) ) {
				$_transient_data = new stdClass;
			}

			if ( 'plugins.php' == $pagenow && is_multisite() ) {
				return $_transient_data;
			}

			/* Loop over our licensed products and check whether any are missing transient data. */
			$missing_data = array();

			foreach ( $this->get_products() as $product ) {
				if ( $this->is_missing_version_info( $product, $_transient_data ) ) {
					$missing_data[] = $product;
				}
			}

			/* If we are missing data for any of our products, check whether any have an update. */
			if ( ! empty( $missing_data ) ) {

				$versions = $this->get_versions();

				unset( $versions['request_speed'] );

				if ( ! empty( $versions ) ) {

					$versions_name_lookup = wp_list_pluck( $versions, 'name' );

					foreach ( $missing_data as $product ) {

						if ( ! in_array( $product['name'], $versions_name_lookup ) ) {
							continue;
						}

						$plugin_file             = plugin_basename( $product['file'] );
						$product_key             = array_search( $product['name'], wp_list_pluck( $this->get_products(), 'name' ) );
						$version_info            = $versions[ array_search( $product['name'], $versions_name_lookup ) ];
						$version_info['license'] = $this->get_license_details( $product_key );

						if ( version_compare( $product['version'], $version_info['new_version'], '<' ) ) {

							if ( isset( $version_info['sections'] ) ) {
								$version_info['sections'] = maybe_unserialize( $version_info['sections'] );
							}

							$can_update = $this->able_to_update( $version_info );

							if ( is_array( $can_update ) ) {
								$version_info['package']                      = $can_update['reason_code'];
								$version_info['download_link']                = $can_update['reason_code'];
								$version_info['package_download_restriction'] = $can_update['description'];
							}

							$_transient_data->response[ $plugin_file ] = (object) $version_info;
						}

						$_transient_data->last_checked            = time();
						$_transient_data->checked[ $plugin_file ] = $product['version'];

					}//end foreach
				}//end if
			}//end if

			return $_transient_data;
		}

		/**
		 * Updates information on the "View version x.x details" page with custom data.
		 *
		 * @uses   api_request()
		 *
		 * @since  1.4.20
		 * @since  1.7.0.10
		 *
		 * @param  mixed  $_data   Default set of data.
		 * @param  string $_action The current action.
		 * @param  object $_args   Request args.
		 * @return object $_data
		 */
		public function plugins_api_filter( $_data, $_action = '', $_args = null ) {
			if ( 'plugin_information' != $_action ) {
				return $_data;
			}

			if ( ! isset( $_args->slug ) ) {
				return $_data;
			}

			$plugin_key = str_replace( '-', '_', $_args->slug );

			if ( 'charitable' === $plugin_key || ! array_key_exists( $plugin_key, $this->products ) ) {
				return $_data;
			}

			$version_info = $this->get_version_info( plugin_basename( $this->products[ $plugin_key ]['file'] ) );

			if ( $version_info ) {
				$_data = $version_info;
			}

			return $_data;
		}

		/**
		 * Return whether a particular plugin is missing version info.
		 *
		 * @since  1.4.20
		 *
		 * @param  array        $product      Product details array.
		 * @param  false|object $update_cache Optional argument to pass update cache.
		 * @return boolean
		 */
		public function is_missing_version_info( $product, $update_cache = false ) {
			return ! $this->get_version_info( plugin_basename( $product['file'] ), $update_cache );
		}

		/**
		 * Return the version update info for a particular plugin.
		 *
		 * @since  1.4.20
		 *
		 * @param  string       $slug         The plugin slug.
		 * @param  false|object $update_cache Optional argument to pass update cache.
		 * @return array|false Array if an update is available. False otherwise.
		 */
		public function get_version_info( $slug, $update_cache = false ) {
			if ( ! $update_cache ) {
				$update_cache = get_site_transient( 'update_plugins' );
			}

			if ( ! is_object( $update_cache ) || empty( $update_cache->response ) || ! array_key_exists( $slug, $update_cache->response ) ) {
				return false;
			}

			return $update_cache->response[ $slug ];
		}

		/**
		 * Checks whether the given plugin can be updated.
		 *
		 * @since  1.6.14
		 *
		 * @param  array $version_info Version information.
		 * @return true|array If it can be updated, returns true. Otherwise
		 *                    returns an array with a reason_code and description.
		 */
		protected function able_to_update( $version_info ) {
			$changelog_link = self_admin_url( 'index.php?edd_sl_action=view_plugin_changelog&plugin=' . $version_info['name'] . '&slug=' . $version_info['slug'] . '&TB_iframe=true&width=772&height=911' );

			switch ( $version_info['package'] ) {

				case 'missing_license':
					return array(
						'reason_code' => 'missing_license',
						'description' => sprintf(
							__( '<p>There is a new version of %1$s available but you have not activated your license. <a target="_top" href="%2$s">Activate your license</a> or <a target="_blank" class="thickbox" href="%3$s">view version %4$s details</a>.</p>', 'charitable' ),
							esc_html( $version_info['name'] ),
							admin_url( 'admin.php?page=charitable-settings&tab=licenses' ),
							esc_url( $changelog_link ),
							esc_html( $version_info['new_version'] )
						),
					);

				case 'expired_license':
					$base_renewal_url = isset( $version_info['renewal_link'] ) ? $version_info['renewal_link'] : 'https://www.wpcharitable.com/account';

					return array(
						'reason_code' => 'expired_license',
						'description' => sprintf(
							__( '<p>There is a new version of %1$s available but your license has expired. <a target="_blank" href="%2$s">Renew your license</a> or <a target="_blank" class="thickbox" href="%3$s">view version %4$s details</a>.</p>', 'charitable' ),
							esc_html( $version_info['name'] ),
							charitable_ga_url(
								$base_renewal_url,
								urlencode( 'Expired License Notice'),
								urlencode( 'Renew your license' )
							),
							esc_url( $changelog_link ),
							esc_html( $version_info['new_version'] )
						),
					);

				default:
					if ( ! isset( $version_info['requirements'] ) ) {
						return true;
					}

					$messages = array();

					foreach ( $version_info['requirements'] as $type => $details ) {

						switch ( $type ) {
							case 'php':
								if ( version_compare( phpversion(), $details, '<' ) ) {
									$messages[] = esc_html(
										sprintf(
											__( '<li>Requires PHP version %s or greater.</li>' ),
											$details
										)
									);
								}
								break;

							case 'charitable':
								if ( version_compare( charitable()->get_version(), $details, '<' ) ) {
									$messages[] = esc_html(
										sprintf(
											__( 'Requires Charitable version %s or greater.' ),
											$details
										)
									);
								}
								break;
						}

						if ( empty( $messages ) ) {
							return true;
						}

						return array(
							'reason_code' => 'missing_requirements',
							'description' => sprintf(
								__( '<p>There is a new version of %1$s available but you are missing the following minimum requirements:</p>%2$s', 'charitable' ),
								esc_html( $version_info['name'] ),
								'<ul>' . implode( '<br/>', $messages ) . '</ul>'
							),
						);
					}

					return true;
			}
		}

		/**
		 * Return the package string for an expired license.
		 *
		 * @since  1.4.20
		 *
		 * @param  string $plugin Plugin basename.
		 * @return string
		 */
		public function get_expired_license_package( $plugin ) {
			$version_info     = $this->get_version_info( $plugin );
			$base_renewal_url = isset( $version_info->renewal_link ) ? $version_info->renewal_link : 'https://www.wpcharitable.com/account';

			return sprintf( 'expired_license:%s', charitable_ga_url(
				$base_renewal_url,
				urlencode( 'WordPress Dashboard'),
				urlencode( 'Expired License' )
			));

		}

		/**
		 * Return the package string for a plugin missing requirements.
		 *
		 * @since  1.6.14
		 *
		 * @param  string $plugin Plugin basename.
		 * @return string
		 */
		public function get_missing_requirements_package( $plugin ) {
			$version_info = $this->get_version_info( $plugin );

			return sprintf(
				'missing_requirements:%s',
				$version_info->package_download_restriction
			);
		}

		/**
		 * Register a product that requires licensing.
		 *
		 * @since  1.0.0
		 *
		 * @param  string $item_name The title of the product.
		 * @param  string $author    The author of the product.
		 * @param  string $version   The current product version we have installed.
		 * @param  string $file      The path to the plugin file.
		 * @param  string $url       The base URL where the plugin is licensed. Defaults to Charitable_Licenses::UPDATE_URL.
		 * @return void
		 */
		public function register_licensed_product( $item_name, $author, $version, $file, $url = false ) {
			if ( ! $url ) {
				$url = Charitable_Licenses::UPDATE_URL;
			}

			$product_key = $this->get_item_key( $item_name );

			$this->products[ $product_key ] = array(
				'name'    => $item_name,
				'author'  => $author,
				'version' => $version,
				'url'     => $url,
				'file'    => $file,
			);

			$licenses = $this->get_licenses();
			$license = isset( $licenses[ $product_key ]['license'] ) ? trim( $licenses[ $product_key ]['license'] ) : '';

			new Charitable_Plugin_Updater( $url, $file, array(
				'version'   => $version,
				'license'   => $license,
				'item_name' => $item_name,
				'author'    => $author,
			) );

		}

		/**
		 * Return the list of products requiring licensing.
		 *
		 * @since  1.0.0
		 *
		 * @return array[]
		 */
		public function get_products() {
			return $this->products;
		}

		/**
		 * Return a specific product's details.
		 *
		 * @since  1.0.0
		 *
		 * @param  string $item The item for which we are getting product details.
		 * @return string[]
		 */
		public function get_product_license_details( $item ) {
			return isset( $this->products[ $item ] ) ? $this->products[ $item ] : false;
		}

		/**
		 * Returns whether the given product has a valid license.
		 *
		 * @since  1.0.0
		 *
		 * @param  string $item The item to check.
		 * @return boolean
		 */
		public function has_valid_license( $item ) {
			$license = $this->get_license_details( $item );

			if ( ! $license || ! isset( $license['valid'] ) ) {
				return false;
			}

			return $license['valid'];
		}

		/**
		 * Returns the license details for the given product.
		 *
		 * @since   1.0.0
		 *
		 * @param  string $item The item to get the license for.
		 * @return mixed[]
		 */
		public function get_license( $item ) {
			$license = $this->get_license_details( $item );

			if ( ! $license || ! is_array( $license )  ) {
				return false;
			}

			return $license['license'];
		}

		/**
		 * Returns the active license details for the given product.
		 *
		 * @since  1.0.0
		 *
		 * @param  string $item The item to get active licensing details for.
		 * @return mixed[]
		 */
		public function get_license_details( $item ) {
			$licenses = $this->get_licenses();

			if ( ! isset( $licenses[ $item ] ) ) {
				return false;
			}

			return $licenses[ $item ];
		}

		/**
		 * Return the list of licenses.
		 *
		 * Note: The licenses are not necessarily valid. If a user enters an invalid
		 * license, the license will be stored but it will be flagged as invalid.
		 *
		 * @since  1.0.0
		 *
		 * @return array[]
		 */
		public function get_licenses() {
			if ( ! isset( $this->licenses ) ) {
				$this->licenses = charitable_get_option( 'licenses', array() );
			}

			return $this->licenses;
		}

		/**
		 * Verify a license.
		 *
		 * @since  1.0.0
		 *
		 * @param  string  $item    The item to verify.
		 * @param  string  $license The license key for the item.
		 * @param  boolean $force   Whether to force the verification check.
		 * @return mixed[]
		 */
		public function verify_license( $item, $license, $force = false, $legacy = false ) {

			$license = trim( $license );

			if ( $license === $this->get_license( $item ) && ! $force ) {
				return $this->get_license_details( $item );
			}

			$product_details = $this->get_product_license_details( $item );

			/* This product was not correctly registered. */
			if ( ! $product_details ) {
				return;
			}

			/* Data to send in our API request */
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'legacy'     => $legacy,
				'item_name'  => urlencode( $product_details['name'] ),
				'url'        => home_url(),
			);

			/* Call the custom API */
			$response = wp_remote_post(
				$product_details['url'],
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
				)
			);

			if ( defined( 'CHARITABLE_DEBUG' ) && CHARITABLE_DEBUG ) {
				error_log( print_r( $api_params, true ) );
				error_log( print_r( $product_details, true ) );
				error_log( print_r( $response, true ) );
			}

			/* Make sure the response came back okay */
			if ( is_wp_error( $response ) ) {
				return;
			}

			$this->flush_update_cache();

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			$comm_success = isset( $license_data ) && ! empty( $license_data ) ? true : false;

			// if the license is not valid, attempt to discover why - in this case, maybe the activation limit was exceeded.

			$license_limit  = isset( $license_data->license_limit ) ? intval( $license_data->license_limit ) : false;
			$site_count     = isset( $license_data->site_count ) ? intval( $license_data->site_count ) : false;
			$limit_exceeded = ( $license_limit > 0 && $site_count >= $license_limit ) ? true : false;

			return array(
				'license'         => $license,
				'expiration_date' => isset( $license_data->expires) ? $license_data->expires : false,
				'plan_id'         => isset( $license_data->price_id ) ? $license_data->price_id : false,
				'valid'           => ( isset( $license_data->license ) && 'valid' === $license_data->license ),
				'license_limit'   => $limit_exceeded,
				'comm_success'    => $comm_success,
			);
		}

		/**
		 * Return the URL to deactivate a specific license.
		 *
		 * @since   1.0.0
		 *
		 * @param  string $item The item to deactivate.
		 * @return string
		 */
		public function get_license_deactivation_url( $item ) {
			return esc_url( add_query_arg( array(
				'charitable_action' => 'deactivate_license',
				'product_key'       => $item,
				'_nonce'            => wp_create_nonce( 'license' ),
			), admin_url( 'admin.php?page=charitable-settings&tab=advanced' ) ) );
		}

		/**
		 * Deactivate a license.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		public function deactivate_license() {
			if ( ! wp_verify_nonce( $_REQUEST['_nonce'], 'license' ) ) {
				wp_die( esc_attr__( 'Cheatin\' eh?!', 'charitable' ) );
			}

			$product_key = isset( $_REQUEST['product_key'] ) ? $_REQUEST['product_key'] : false;

			/* Product key must be set */
			if ( false === $product_key ) {
				wp_die( esc_attr__( 'Missing product key', 'charitable' ) );
			}

			$product = $this->get_product_license_details( $product_key );

			/* Make sure we have a valid product with a valid license. */
			if ( ! $product || ! $this->has_valid_license( $product_key ) ) {
				wp_die( esc_attr__( 'This product is not valid or does not have a valid license key.', 'charitable' ) );
			}

			$license = $this->get_license( $product_key );

			/* Data to send to wpcharitable.com to deactivate the license. */
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => urlencode( $product['name'] ),
				'url'        => home_url(),
			);

			/* Call the custom API. */
			$response = wp_remote_post( $product['url'], array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			/* Make sure the response came back okay */
			if ( is_wp_error( $response ) ) {
				return;
			}

			$this->flush_update_cache();

			/* Decode the license data */
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			$settings = get_option( 'charitable_settings' );

			unset( $settings['licenses'][ $product_key ] );

			update_option( 'charitable_settings', $settings );

			wp_redirect( add_query_arg( array( 'tab' => 'advanced' ), admin_url( 'admin.php?page=charitable-settings' ) ) );
			exit;

		}

		/**
		 * Flush the version update cache.
		 *
		 * @since  1.4.20
		 *
		 * @return void
		 */
		protected function flush_update_cache() {
			wp_cache_delete( 'plugin_versions', 'charitable' );
			set_site_transient( 'update_plugins', null );
		}

		/**
		 * Return a key for the item, based on the item name.
		 *
		 * @since  1.0.0
		 *
		 * @param  string $item_name Name of the item.
		 * @return string
		 */
		protected function get_item_key( $item_name ) {
			return strtolower( str_replace( ' ', '_', $item_name ) );
		}

		/**
		 * Return the latest versions of Charitable plugins.
		 *
		 * @since  1.4.0
		 *
		 * @return array
		 */
		protected function get_versions() {
			$versions = wp_cache_get( 'plugin_versions', 'charitable' );

			if ( false === $versions ) {

				$licenses = array();

				foreach ( $this->get_licenses() as $license ) {
					if ( isset( $license['license'] ) ) {
						$licenses[] = $license['license'];
					}
				}

				$response = wp_remote_post(
					Charitable_Licenses::UPDATE_URL . '/edd-api/versions-v2/',
					array(
						'sslverify' => false,
						'timeout'   => 15,
						'body'      => array(
							'licenses' => $licenses,
							'url'      => home_url(),
						),
					)
				);

				$versions = wp_remote_retrieve_body( $response );

				$versions = json_decode( $versions, true );

				wp_cache_set( 'plugin_versions', $versions, 'charitable' );
			} //end if

			return $versions;
		}

		public function ajax_license_check() {

			if ( ! is_admin() ) {
				return;
			}

			if ( ! isset( $_REQUEST['license'] ) ) {
				return;
			}

			if ( ! isset( $_REQUEST['action'] ) && 'charitable_license_check' !== $_REQUEST['action'] ) {
				return;
			}

			if ( ! isset( $_REQUEST['chartiable_action'] ) && 'verify' !== $_REQUEST['chartiable_action'] ) {
				return;
			}

			// to-do: adjust spelling of chartiable_action and add nonce check.

			$product_key  = 'charitable';
			$re_check     = true;
			$license      = trim( esc_html( $_REQUEST['license'] ) );
			$license_data = $this->verify_license( $product_key, $license, $re_check );

			$this->flush_update_cache();

			$settings = get_option( 'charitable_settings' );

			$settings['licenses'][ $product_key . '-v2' ] = array(
				'license'         => $license,
				'expiration_date' => isset( $license_data['expiration_date'] ) ? $license_data['expiration_date'] : false,
				'plan_id'         => isset( $license_data['plan_id'] ) ? $license_data['plan_id'] : false,
				'valid'           => isset( $license_data['plan_id'] ) ? 1 === intval( $license_data['valid'] ) : false,
			);

			update_option( 'charitable_settings', $settings );

			if ( isset( $license_data['valid'] ) && intval( $license_data['valid'] ) === 1 ) {
				$license_data['message'] = Charitable_Licenses_Settings::get_instance()->get_licensed_message();
			} else {
				$license_data['message'] = Charitable_Licenses_Settings::get_instance()->get_unlicensed_message( false, $license_data );
			}

			wp_send_json_success( $license_data );

		}

	}

endif;
