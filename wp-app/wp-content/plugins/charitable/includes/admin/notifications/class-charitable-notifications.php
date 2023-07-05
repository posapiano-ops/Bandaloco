<?php
/**
 * Admin form model class.
 *
 * @package   Charitable/Classes/Charitable_Admin_Form
 * @author    David Bisset
 * @copyright Copyright (c) 2022, WP Charitable LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.5.0
 * @version   1.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notifications.
 *
 * @since 1.7.5
 */
class Charitable_Notifications {

	/**
	 * The single instance of this class.
	 *
	 * @var Charitable_Notifications|null
	 */
	private static $instance = null;

	/**
	 * Source of notifications content.
	 *
	 * @since 1.7.5
	 *
	 * @var string
	 */
	const SOURCE_URL = 'https://plugin.wpcharitable.com/wp-content/notifications.json';

	/**
	 * Array of license types, that are considered being Elite level.
	 *
	 * @since 1.7.5
	 *
	 * @var array
	 */
	const LICENSES_ELITE = [ 'agency', 'pro' ];

	/**
	 * Option value.
	 *
	 * @since 1.7.5
	 *
	 * @var bool|array
	 */
	public $option = false;

	/**
	 * Current license type.
	 *
	 * @since 1.7.5
	 *
	 * @var string
	 */
	private $license_type;

	/**
	 * Returns and/or create the single instance of this class.
	 *
	 * @since  1.2.0
	 *
	 * @return Charitable_User_Dashboard
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {

		$this->init();

	}

	/**
	 * Initialize class.
	 *
	 * @since 1.7.5
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.7.5
	 */
	public function hooks() {

		add_action( 'after_charitable_admin_enqueue_scripts', [ $this, 'enqueues' ], 10, 3 );

		// add_action( 'charitable_admin_overview_before_table', [ $this, 'output' ] ); // where the notifications comes out in the admin
		add_action( 'admin_notices', [ $this, 'output_notices' ], 99 ); // where the notifications comes out in the admin
		add_action( 'charitable_maybe_show_notification', [ $this, 'output' ], 99 ); // where the notifications comes out in the admin

		add_action( 'charitable_admin_notifications_update', [ $this, 'update' ] );

		add_action( 'deactivate_plugin', [ $this, 'delete' ], 10, 2 );

		add_action( 'wp_ajax_charitable_notification_dismiss', [ $this, 'dismiss' ] );
	}

	/**
	 * Check if user has access and is enabled.
	 *
	 * @since 1.7.5
	 *
	 * @return bool
	 */
	public function has_access() {

		$access = charitable_current_user_can( 'administrator' ) && ! charitable_get_option( 'hide-announcements' );

		/**
		 * Allow modifying state if a user has access.
		 *
		 * @since 1.7.0.3
		 *
		 * @param bool $access True if user has access.
		 */
		return (bool) apply_filters( 'charitable_admin_notifications_has_access', $access );
	}

	/**
	 * Get option value.
	 *
	 * @since 1.7.5
	 *
	 * @param bool $cache Reference property cache if available.
	 *
	 * @return array
	 */
	public function get_option( $cache = true ) {

		if ( $this->option && $cache ) {
			return $this->option;
		}

		$option = (array) get_option( 'charitable_notifications', [] );

		$this->option = [
			'update'    => ! empty( $option['update'] ) ? (int) $option['update'] : 0,
			'feed'      => ! empty( $option['feed'] ) ? (array) $option['feed'] : [],
			'events'    => ! empty( $option['events'] ) ? (array) $option['events'] : [],
			'dismissed' => ! empty( $option['dismissed'] ) ? (array) $option['dismissed'] : [],
		];

		return $this->option;
	}

	/**
	 * Fetch notifications from feed.
	 *
	 * @since 1.7.5
	 *
	 * @return array
	 */
	public function fetch_feed() {

		$response = wp_remote_get(
			self::SOURCE_URL,
			[
				'timeout'    => 10,
				'user-agent' => charitable_get_default_user_agent(),
			]
		);

		if ( is_wp_error( $response ) ) {
			return [];
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			return [];
		}

		return $this->verify( json_decode( $body, true ) );
	}

	/**
	 * Verify notification data before it is saved.
	 *
	 * @since 1.7.5
	 *
	 * @param array $notifications Array of notifications items to verify.
	 *
	 * @return array
	 */
	public function verify( $notifications ) {

		$data = [];

		if ( ! is_array( $notifications ) || empty( $notifications ) ) {
			return $data;
		}

		foreach ( $notifications as $notification ) {

			// Ignore if one of the conditional checks is true:
			//
			// 1. notification message is empty.
			// 2. license type does not match.
			// 3. notification is expired.
			// 4. notification has already been dismissed.
			// 5. notification existed before installing WPCharitable.
			// (Prevents bombarding the user with notifications after activation).
			if (
				empty( $notification['content'] ) ||
				! $this->is_license_type_match( $notification ) ||
				$this->is_expired( $notification ) ||
				$this->is_dismissed( $notification ) ||
				$this->is_existed( $notification )
			) {
				continue;
			}

			$data[] = $notification;
		}

		return $data;
	}

	/**
	 * Verify saved notification data for active notifications.
	 *
	 * @since 1.7.5
	 *
	 * @param array $notifications Array of notifications items to verify.
	 *
	 * @return array
	 */
	public function verify_active( $notifications ) {

		if ( ! is_array( $notifications ) || empty( $notifications ) ) {
			return [];
		}

		$current_timestamp = time();

		// Remove notifications that are not active.
		foreach ( $notifications as $key => $notification ) {
			if (
				( ! empty( $notification['start'] ) && $current_timestamp < strtotime( $notification['start'] ) ) ||
				( ! empty( $notification['end'] ) && $current_timestamp > strtotime( $notification['end'] ) )
			) {
				unset( $notifications[ $key ] );
			}
		}

		return $notifications;
	}

	/**
	 * Get notification data.
	 *
	 * @since 1.7.5
	 *
	 * @return array
	 */
	public function get() {

		if ( ! $this->has_access() ) {
			return [];
		}

		$option = $this->get_option();

		// Update notifications using async task.
		if ( empty( $option['update'] ) || time() > $option['update'] + DAY_IN_SECONDS ) {

			$this->update();

			// $tasks = charitable()->get( 'tasks' );

			// if ( ! $tasks->is_scheduled( 'charitable_admin_notifications_update' ) !== false ) {
			// 	$tasks
			// 		->create( 'charitable_admin_notifications_update' )
			// 		->async()
			// 		->params()
			// 		->register();
			// }
		}

		$feed   = ! empty( $option['feed'] ) ? $this->verify_active( $option['feed'] ) : [];
		$events = ! empty( $option['events'] ) ? $this->verify_active( $option['events'] ) : [];

		return array_merge( $feed, $events );
	}

	/**
	 * Get notification count.
	 *
	 * @since 1.7.5
	 *
	 * @return int
	 */
	public function get_count() {

		return count( $this->get() );
	}

	/**
	 * Add a new Event Driven notification.
	 *
	 * @since 1.7.5
	 *
	 * @param array $notification Notification data.
	 */
	public function add( $notification ) {

		if ( ! $this->is_valid( $notification ) ) {
			return;
		}

		$option = $this->get_option();

		// Notification ID already exists.
		if ( ! empty( $option['events'][ $notification['id'] ] ) ) {
			return;
		}

		update_option(
			'charitable_notifications',
			[
				'update'    => $option['update'],
				'feed'      => $option['feed'],
				'events'    => array_merge( $notification, $option['events'] ),
				'dismissed' => $option['dismissed'],
			]
		);
	}

	/**
	 * Determine if notification data is valid.
	 *
	 * @since 1.7.5
	 *
	 * @param array $notification Notification data.
	 *
	 * @return bool
	 */
	public function is_valid( $notification ) {

		if ( empty( $notification['id'] ) ) {
			return false;
		}

		return ! empty( $this->verify( [ $notification ] ) );
	}

	/**
	 * Determine if notification has already been dismissed.
	 *
	 * @since 1.7.5
	 *
	 * @param array $notification Notification data.
	 *
	 * @return bool
	 */
	private function is_dismissed( $notification ) {

		$option = $this->get_option();

		// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		return ! empty( $option['dismissed'] ) && in_array( $notification['id'], $option['dismissed'] );
	}

	/**
	 * Determine if license type is match.
	 *
	 * @since 1.7.5
	 *
	 * @param array $notification Notification data.
	 *
	 * @return bool
	 */
	private function is_license_type_match( $notification ) {

		// A specific license type is not required.
		if ( empty( $notification['type'] ) ) {
			return true;
		}

		return in_array( $this->get_license_type(), (array) $notification['type'], true );
	}

	/**
	 * Determine if notification is expired.
	 *
	 * @since 1.7.5
	 *
	 * @param array $notification Notification data.
	 *
	 * @return bool
	 */
	private function is_expired( $notification ) {

		return ! empty( $notification['end'] ) && time() > strtotime( $notification['end'] );
	}

	/**
	 * Determine if notification existed before installing WPForms.
	 *
	 * @since 1.7.5
	 *
	 * @param array $notification Notification data.
	 *
	 * @return bool
	 */
	private function is_existed( $notification ) {

		$activated = charitable_get_activated_timestamp();

		return ! empty( $activated ) &&
			! empty( $notification['start'] ) &&
			$activated > strtotime( $notification['start'] );
	}

	/**
	 * Update notification data from feed.
	 *
	 * @since 1.7.5
	 */
	public function update() {

		$option = $this->get_option();
		$data   = [
			'update'    => time(),
			'feed'      => $this->fetch_feed(),
			'events'    => $option['events'],
			'dismissed' => $option['dismissed'],
		];

		// phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName
		/**
		 * Allow changing notification data before it will be updated in database.
		 *
		 * @since 1.7.5
		 *
		 * @param array $data New notification data.
		 */
		$data = (array) apply_filters( 'charitable_admin_notifications_update_data', $data );
		// phpcs:enable WPForms.PHP.ValidateHooks.InvalidHookName

		update_option( 'charitable_notifications', $data );
	}

	/**
	 * Remove notification data from database before a plugin is deactivated.
	 *
	 * @since 1.7.5
	 *
	 * @param string $plugin               Path to the plugin file relative to the plugins directory.
	 * @param bool   $network_deactivating Whether the plugin is deactivated for all sites in the network
	 *                                     or just the current site. Multisite only. Default false.
	 */
	public function delete( $plugin, $network_deactivating ) {

		$charitable_plugins = [
			'charitable-lite/charitable.php',
			'charitable/charitable.php',
		];

		if ( ! in_array( $plugin, $charitable_plugins, true ) ) {
			return;
		}

		delete_option( 'charitable_notifications' );
	}

	/**
	 * Enqueue assets on Form Overview admin page.
	 *
	 * @since 1.7.5
	 */
	public function enqueues( $suffix, $version, $assets_dir ) {

		if ( ! $this->get_count() ) {
			return;
		}

		$suffix = false;

		wp_register_style(
			'charitable-admin-notifications',
			$assets_dir . 'css/charitable-admin-notifications' . $suffix . '.css',
			array( 'charitable-lity' ),
			$version
		);

		wp_enqueue_style( 'charitable-admin-notifications' );

		wp_register_script(
			'charitable-admin-notifications',
			$assets_dir . 'js/charitable-admin-notifications' . $suffix . '.js',
			array( 'jquery', 'charitable-lity' ),
			$version,
			true
		);

		wp_enqueue_script( 'charitable-admin-notifications' );

		// Lity.
		wp_enqueue_style(
			'charitable-lity',
			$assets_dir . 'lib/lity/lity.min.css',
			[],
			$version
		);

		wp_enqueue_script(
			'charitable-lity',
			$assets_dir . 'lib/lity/lity.min.js',
			[ 'jquery' ],
			$version,
			true
		);

	}

	public function output_notices() {
		return $this->output( 'notices' );
	}

	/**
	 * Output notifications on Form Overview admin area.
	 *
	 * @since 1.7.5
	 */
	public function output( $location = false ) {

		global $post;

		if ('notices' === $location && ! $this->show_notifications() ) {
			return;
		}

		$notifications = $this->get();

		if ( empty( $notifications ) ) {
			return;
		}

		$notifications_html   = '';
		$current_class        = ' current';
		$content_allowed_tags = [
			'em'     => [],
			'strong' => [],
			'span'   => [
				'style' => [],
			],
			'a'      => [
				'href'   => [],
				'target' => [],
				'rel'    => [],
			],
		];

		foreach ( $notifications as $notification ) {

			// Prepare required arguments.
			$notification = wp_parse_args(
				$notification,
				[
					'id'      => 0,
					'title'   => '',
					'content' => '',
					'video'   => '',
				]
			);

			$title   = $this->get_component_data( $notification['title'] );
			$content = $this->get_component_data( $notification['content'] );

			if ( ! $title && ! $content ) {
				continue;
			}

			// Notification HTML.
			$notifications_html .= sprintf(
				'<div class="charitable-notifications-message%5$s" data-message-id="%4$s">
					<h3 class="charitable-notifications-title">%1$s%6$s</h3>
					<p class="charitable-notifications-content">%2$s</p>
					%3$s
				</div>',
				esc_html( $title ),
				wp_kses( $content, $content_allowed_tags ),
				$this->get_notification_buttons_html( $notification ),
				esc_attr( $notification['id'] ),
				esc_attr( $current_class ),
				$this->get_video_badge_html( $this->get_component_data( $notification['video'] ) )
			);

			// Only first notification is current.
			$current_class = '';
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo charitable_render(
			'/admin/templates/notifications',
			[
				'notifications' => [
					'count' => count( $notifications ),
					'html'  => $notifications_html,
				],
			],
			true
		);

	}

	/**
	 * Retrieve notification's buttons HTML.
	 *
	 * @since 1.7.5
	 *
	 * @param array $notification Notification data.
	 *
	 * @return string
	 */
	private function get_notification_buttons_html( $notification ) {

		$html = '';

		if ( empty( $notification['btns'] ) || ! is_array( $notification['btns'] ) ) {
			return $html;
		}

		foreach ( $notification['btns'] as $btn_type => $btn ) {

			$btn = $this->get_component_data( $btn );

			if ( ! $btn ) {
				continue;
			}

			$url    = $this->prepare_btn_url( $btn );
			$target = ! empty( $btn['target'] ) ? $btn['target'] : '_blank';
			$target = ! empty( $url ) && strpos( $url, home_url() ) === 0 ? '_self' : $target;

			$html .= sprintf(
				'<a href="%1$s" class="button button-%2$s"%3$s>%4$s</a>',
				esc_url( $url ),
				$btn_type === 'main' ? 'primary' : 'secondary',
				$target === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '',
				! empty( $btn['text'] ) ? esc_html( $btn['text'] ) : ''
			);
		}

		return ! empty( $html ) ? sprintf( '<div class="charitable-notifications-buttons">%s</div>', $html ) : '';
	}

	/**
	 * Retrieve notification's component data by a license type.
	 *
	 * @since 1.7.5
	 *
	 * @param mixed $data Component data.
	 *
	 * @return false|mixed
	 */
	private function get_component_data( $data ) {

		if ( empty( $data['license'] ) ) {
			return $data;
		}

		$license_type = $this->get_license_type();

		if ( in_array( $license_type, self::LICENSES_ELITE, true ) ) {
			$license_type = 'elite';
		}

		return ! empty( $data['license'][ $license_type ] ) ? $data['license'][ $license_type ] : false;
	}

	/**
	 * Retrieve the current installation license type (always lowercase).
	 *
	 * @since 1.7.5
	 *
	 * @return string
	 */
	private function get_license_type() {

		if ( $this->license_type ) {
			return $this->license_type;
		}

		if ( charitable_is_pro() ) {
			return 'pro';
		} else {
			return 'lite';
		}

		// $this->license_type = charitable_get_license_type();

		// if ( ! $this->license_type ) {
		// 	$this->license_type = 'lite';
		// }

		// return $this->license_type;
	}

	/**
	 * Dismiss notification via AJAX.
	 *
	 * @since 1.7.5
	 */
	public function dismiss() {

		// Check for required param, security and access.
		if (
			empty( $_POST['id'] ) ||
			! check_ajax_referer( 'charitable-admin', 'nonce', false ) ||
			! $this->has_access()
		) {
			wp_send_json_error();
		}

		$id     = sanitize_key( $_POST['id'] );
		$type   = is_numeric( $id ) ? 'feed' : 'events';
		$option = $this->get_option();

		$option['dismissed'][] = $id;
		$option['dismissed']   = array_unique( $option['dismissed'] );

		// Remove notification.
		if ( is_array( $option[ $type ] ) && ! empty( $option[ $type ] ) ) {
			foreach ( $option[ $type ] as $key => $notification ) {
				if ( (string) $notification['id'] === (string) $id ) {
					unset( $option[ $type ][ $key ] );

					break;
				}
			}
		}

		update_option( 'charitable_notifications', $option );

		wp_send_json_success();
	}

	/**
	 * Prepare button URL.
	 *
	 * @since 1.7.5
	 *
	 * @param array $btn Button data.
	 *
	 * @return string
	 */
	private function prepare_btn_url( $btn ) {

		if ( empty( $btn['url'] ) ) {
			return '';
		}

		$replace_tags = [
			'{admin_url}' => admin_url(),
		];

		return str_replace( array_keys( $replace_tags ), array_values( $replace_tags ), $btn['url'] );
	}

	/**
	 * Get the notification's video badge HTML.
	 *
	 * @since 1.7.5
	 *
	 * @param string $video_url Valid video URL.
	 *
	 * @return string
	 */
	private function get_video_badge_html( $video_url ) {

		$video_url = wp_http_validate_url( $video_url );

		if ( empty( $video_url ) ) {
			return '';
		}

		$data_attr_lity = wp_is_mobile() ? '' : 'data-lity';

		return sprintf(
			'<a class="charitable-notifications-badge" href="%1$s" %2$s>%3$s</a>',
			esc_url( $video_url ),
			esc_attr( $data_attr_lity ),
			esc_html__( 'Watch Video', 'charitable-lite' )
		);
	}

	/**
	 * Determine to show notifications on this page or not.
	 *
	 * @since 1.7.0.3
	 *
	 * @return boolean
	 */
	public function show_notifications() {
		if ( isset( $_GET['taxonomy'] ) ) {
			return false;
		}
		if ( isset( $_GET['post_type'] ) && $_GET['post_type']  == 'campaign' ) {
			return true;
		}
		if ( isset( $_GET['post_type'] ) && $_GET['post_type']  == 'donation' ) {
			return true;
		}
		return false;
	}
}
