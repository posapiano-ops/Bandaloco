<?php
/**
 * This class is responsible for adding the Charitable admin pages.
 *
 * @package   Charitable/Classes/Charitable_Import_Items
 * @author    David Bisset
 * @copyright Copyright (c) 2022, WP Charitable LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.7.0.7
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Charitable_Import_Items' ) ) :

	/**
	 * Charitable_Import_Items
	 *
	 * @since 1.7.0.7
	 */
	final class Charitable_Import_Items {

		/**
		 * The single instance of this class.
		 *
		 * @var     Charitable_Import_Items|null
		 */
		private static $instance = null;

        /**
         * Holds any plugin error messages.
         *
         * @since 1.0.0
         *
         * @var array
         */
        public $errors = array();

		/**
		 * Create class object.
		 *
		 * @since  1.7.0.7
		 */
		private function __construct() {
		}

		/**
		 * Returns and/or create the single instance of this class.
		 *
		 * @since  1.7.0.7
		 *
		 * @return Charitable_Import_Items
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * This forces the charitable menu to be open for category and tag pages.
		 *
		 * @since  1.7.0.7
		 *
		 * @return string
		 */
		public function admin_accept_import_campaign_request() {

			global $wpdb;

			if ( ! is_admin() || empty( $_POST ) ) {
				return;
			}

			if (! isset( $_POST['charitable_nonce'] ) || ! wp_verify_nonce( $_POST['charitable_nonce'], 'import_campaign' ) ) {
				return;
			}

            if ( ! $this->has_json_extension() ) {
                $this->errors[] = __( 'Sorry, but Envira Gallery import files must be in <code>.json</code> format.', 'envira-gallery' );
                $redirect_link = admin_url( 'admin.php?page=charitable-settings&tab=tools' );
                wp_safe_redirect( $redirect_link );
                exit;
            }

            // Retrieve the JSON contents of the file. If that fails, return an error.
            $contents = $this->get_file_contents();
            if ( ! $contents ) {
                $this->errors[] = __( 'Sorry, but there was an error retrieving the contents of the campaign export file. Please try again.', 'envira-gallery' );
                $redirect_link = admin_url( 'admin.php?page=charitable-settings&tab=tools' );
                wp_safe_redirect( $redirect_link );
                exit;
            }

            // Decode the settings and start processing.
            $data = json_decode( $contents, true );

            // get the old post ID as we assign the new one, as we will want to document this as metadata.
            $old_post_id = $data['post']['ID'];

            // since we don't import authors/campaign creators, determine if the email address of the campaign creater exists in this site.
            $old_post_author_email = $data['campaign_creator']['user_email'];
            $campaign_creator      = get_user_by('email', $old_post_author_email );
            if ( $campaign_creator ) {
                $campaign_creator_id = $campaign_creator->ID;
            } else {
                // assign it to the user who is logged in?
                $campaign_creator_id = get_current_user_id();
            }

            $campaign_data = array(
                'post_title'            => $data['post']['post_title'],
                'post_content'          => $data['post']['post_content'],
                'post_excerpt'          => $data['post']['post_excerpt'],
                'post_date'             => $data['post']['post_date'],
                'post_date_gmt'         => $data['post']['post_date_gmt'],
                'comment_status'        => $data['post']['comment_status'],
                'ping_status'           => $data['post']['ping_status'],
                'post_name'             => $data['post']['post_name'],
                'post_modified'         => $data['post']['post_modified'],
                'post_modified_gmt'     => $data['post']['post_modified_gmt'],
                'post_content_filtered' => $data['post']['post_content_filtered'],
                'guid'                  => $data['post']['guid'],
                'post_mime_type'        => $data['post']['post_mime_type'],
                'comment_count'         => $data['post']['comment_count'],
                'post_status'           => 'draft',
                'post_type'             => 'campaign',
                'post_author'           => $campaign_creator_id,
            );
            $campaign_id = wp_insert_post( $campaign_data );

            foreach ( $data['meta'] as $meta_import_id => $meta_to_import ) {
                if ( is_serialized( $meta_to_import['meta_value'] ) ) {
                    $data_to_import = unserialize( $meta_to_import['meta_value'] );
                } else {
                    $data_to_import = $meta_to_import['meta_value'];
                }
                add_post_meta( $campaign_id, $meta_to_import['meta_key'], $data_to_import );
            }

            add_post_meta( $campaign_id,'_campaign_imported_campaign_key', intval( $old_post_id ) );

            // categories and tags
            if ( isset( $data['campaign_category'] ) && ! empty( $data['campaign_category'] ) ) {
                foreach ( $data['campaign_category'] as $term_to_import ) {
                    $term = term_exists( $term_to_import['slug'], 'campaign_category' );
                    if ( ! $term ) { // create category }
                        $term_id = wp_insert_term(
                            $term_to_import['name'],   // the term
                            'campaign_category', // the taxonomy
                            array(
                                'description' => $term_to_import['description'],
                                'slug'        => $term_to_import['slug'],
                            )
                        );
                    } else {
                        $term_id = intval( $term['term_id'] );
                    }
                    wp_set_post_terms( $campaign_id, array( $term_id ), 'campaign_category', true );

                }
            }
            if ( isset( $data['campaign_tag'] ) && ! empty( $data['campaign_tag'] ) ) {
                foreach ( $data['campaign_tag'] as $term_to_import ) {
                    $term = term_exists( $term_to_import['slug'], 'campaign_tag' );
                    if ( ! $term ) { // create category }
                        $term_id = wp_insert_term(
                            $term_to_import['name'],   // the term
                            'campaign_tag', // the taxonomy
                            array(
                                'description' => $term_to_import['description'],
                                'slug'        => $term_to_import['slug'],
                            )
                        );
                    } else {
                        $term_id = intval( $term['term_id'] );
                    }
                    wp_set_post_terms( $campaign_id, array( $term_id ), 'campaign_tag', true );

                }
            }

            // get featured image
            if ( isset( $data['thumbnail'] ) && ! empty( $data['thumbnail'] ) ) {

                // Add Featured Image to Post
                // $image_url        = esc_url( $data['thumbnail'] ); // Define the image URL here
                // $image_name       = basename( $data['thumbnail'] );
                // $upload_dir       = wp_upload_dir(); // Set upload folder
                // $image_data       = file_get_contents( $image_url ); // Get image data
                // $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
                // $filename         = basename( $unique_file_name ); // Create image file name

                // Prepare variables.
                $src      = esc_url( $data['thumbnail'] );
                $stream   = wp_remote_get( $src, array( 'timeout' => 60 ) );
                $type     = wp_remote_retrieve_header( $stream, 'content-type' );
                $filename = basename( $src );
                $fileinfo = pathinfo( $filename );

                // If the filename doesn't have an extension on it, determine the filename to use to save this image to the Media Library
                // This fixes importing URLs with no file extension e.g. http://placehold.it/300x300 (which is a PNG).
                if ( ! isset( $fileinfo['extension'] ) || empty( $fileinfo['extension'] ) ) {
                    switch ( $type ) {
                        case 'image/jpeg':
                            $filename = $filename . '.jpeg';
                            break;
                        case 'image/jpg':
                            $filename = $filename . '.jpg';
                            break;
                        case 'image/gif':
                            $filename = $filename . '.gif';
                            break;
                        case 'image/png':
                            $filename = $filename . '.png';
                            break;
                        case 'image/webp':
                            $filename = $filename . '.webp';
                            break;
                    }
                }

                // If we cannot get the image or determine the type, bail.
                if ( is_wp_error( $stream ) ) {

                    if ( defined( 'CHARITABLE_DEBUG' ) && CHARITABLE_DEBUG ) {
                        error_log ('admin_accept_import_campaign_request:: ');
                        error_log ( print_r( $stream, true ) );
                    }

                } elseif ( ! $type || strpos( $type, 'text/html' ) !== false ) {

                    if ( defined( 'CHARITABLE_DEBUG' ) && CHARITABLE_DEBUG ) {
                        error_log ('admin_accept_import_campaign_request type: ');
                        error_log ( print_r( $type, true ) );
                    }

                } else {
                    // It is an image. Stream the image.
                    $mirror = wp_upload_bits( $filename, null, wp_remote_retrieve_body( $stream ) );

                    // If there is an error, bail.
                    if ( ! empty( $mirror['error'] ) ) {

                        if ( defined( 'CHARITABLE_DEBUG' ) && CHARITABLE_DEBUG ) {
                            error_log ('admin_accept_import_campaign_request:: ');
                            error_log ( print_r( $stream, true ) );
                        }

                    } else {
                        // Check if the $item has title, caption, alt specified
                        // If so, store those values against the attachment so they're included in the Gallery
                        // If not, fallback to the defaults.
                        $attachment = array(
                            'post_title'     => ( ( isset( $data['post']['post_title'] ) && ! empty($data['post']['post_title'] ) ) ? $data['post']['post_title'] : urldecode( $filename ) ), // Title.
                            'post_mime_type' => $type,
                            'post_excerpt'   => ( ( isset( $data['post']['post_excerpt'] ) && ! empty( $data['post']['post_excerpt'] ) ) ? $data['post']['post_excerpt'] : '' ), // Caption.
                        );
                        $attach_id  = wp_insert_attachment( $attachment, $mirror['file'], $campaign_id );
                        if ( ( isset( $item['alt'] ) && ! empty( $item['alt'] ) ) ) {
                            update_post_meta( $attach_id, '_wp_attachment_image_alt', $item['alt'] );
                        }

                        // Generate and update attachment metadata.
                        if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
                            require ABSPATH . 'wp-admin/includes/image.php';
                        }

                        // Generate and update attachment metadata.
                        $attach_data = wp_generate_attachment_metadata( $attach_id, $mirror['file'] );
                        wp_update_attachment_metadata( $attach_id, $attach_data );

                        // And finally assign featured image to post
                        set_post_thumbnail( $campaign_id, $attach_id );

                    }
                }

            }

            $this->add_update_message( __( 'Campaign imported.', 'charitable' ) . ' <a href="' . admin_url( 'post.php?post=' . $campaign_id . '&action=edit' ) . '">View campaign</a>.', 'success' );

			$redirect_link = admin_url( 'admin.php?page=charitable-settings&tab=tools' );
			wp_safe_redirect( $redirect_link );
			exit;

        }

		/**
		 * This forces the charitable menu to be open for category and tag pages.
		 *
		 * @since  1.7.0.7
		 *
		 * @return string
		 */
		public function admin_accept_import_donations_request() {

			global $wpdb;

			if ( ! is_admin() || empty( $_POST ) ) {
				return;
			}

			if ( ! isset( $_POST['charitable_nonce'] ) || ! wp_verify_nonce( $_POST['charitable_nonce'], 'import_donations' ) ) {
				return;
			}

            if ( ! $this->has_json_extension('import_donations' ) ) {
                $this->add_update_message( __( 'Sorry, but Envira Gallery import files must be in <code>.json</code> format.', 'charitable' ), 'error' );
                $redirect_link = admin_url( 'admin.php?page=charitable-settings&tab=tools' );
                wp_safe_redirect( $redirect_link );
                exit;
            }

            // Retrieve the JSON contents of the file. If that fails, return an error.
            $contents = $this->get_file_contents( 'import_donations' );
            if ( ! $contents ) {
                $this->add_update_message( __( 'Sorry, but there was an error retrieving the contents of the donation export file. Please try again.', 'charitable' ), 'error' );
                $redirect_link = admin_url( 'admin.php?page=charitable-settings&tab=tools' );
                wp_safe_redirect( $redirect_link );
                exit;
            }

            // Decode the settings and start processing.
            $data = json_decode( $contents, true );

            // Get the campaign we are importing these donations into, otherwise bail.
            $campaign_id = isset( $_POST['charitable_settings']['tools_campaign'] ) ? intval( $_POST['charitable_settings']['tools_campaign'] ) : false;

            if ( false === $campaign_id || ! isset( $data['campaigns'] ) || count( $data['campaigns'] ) === 0 ) {
                $this->add_update_message( __( 'Sorry, but there was an error attempting to determine the campaign ID for this import. Please try again.', 'charitable' ), 'error' );
                $redirect_link = admin_url( 'admin.php?page=charitable-settings&tab=tools' );
                wp_safe_redirect( $redirect_link );
                exit;
            }

            $campaign_name   = get_the_title( $campaign_id ) ? get_the_title( $campaign_id ) : 'Unknown Campaign';
            $donations_added = 0;

            foreach ( $data['campaigns'] as $old_campaign_id => $campaign_donations_info ) {
                foreach ( $campaign_donations_info['donation_posts'] as $old_donation_id => $donation_data ) {

                    $donor_data   = false;
                    $found_key    = false;
                    $donation_log = array();

                    if ( ! isset( $data['campaigns'][ $old_campaign_id ]['charitable_campaign_donations'] ) ) {
                        continue;
                    }

                    // confirm the existance of the extra table data, otherwise adding this donation is meaningless.
                    foreach ( $campaign_donations_info['charitable_campaign_donations'] as $key => $charitable_campaign_donation ) {
                        if ( intval( $charitable_campaign_donation['donation_id'] ) === $old_donation_id ) {
                            $found_key = $key;
                        }
                    }

                    if ( ! $found_key ) {
                        continue;
                    }

                    if ( ! isset( $donation_data['post'] ) ) {
                        if ( defined( 'CHARITABLE_DEBUG' ) && CHARITABLE_DEBUG ) {
                            error_log ('import post fail donation data: ');
                            error_log ( print_r( $donation_data, true ) );
                        }
                        return;
                    }

                    // create the donation post.
                    $donation_post_args = array(
                        'post_title'            => $donation_data['post']['post_title'],
                        'post_content'          => $donation_data['post']['post_content'],
                        'post_excerpt'          => $donation_data['post']['post_excerpt'],
                        'post_date'             => $donation_data['post']['post_date'],
                        'post_date_gmt'         => $donation_data['post']['post_date_gmt'],
                        'comment_status'        => $donation_data['post']['comment_status'],
                        'ping_status'           => $donation_data['post']['ping_status'],
                        'post_name'             => $donation_data['post']['post_name'],
                        'post_modified'         => $donation_data['post']['post_modified'],
                        'post_modified_gmt'     => $donation_data['post']['post_modified_gmt'],
                        'post_content_filtered' => $donation_data['post']['post_content_filtered'],
                        'guid'                  => $donation_data['post']['guid'],
                        'post_mime_type'        => $donation_data['post']['post_mime_type'],
                        'comment_count'         => $donation_data['post']['comment_count'],
                        'post_status'           => $donation_data['post']['post_status'], // 'charitable-pending',
                        'post_type'             => 'donation',
                        'post_author'           => $this->get_creator_id( $donation_data['author'] ) // $campaign_creator_id,
                    );
                    $donation_id = wp_insert_post( $donation_post_args );

                    // import meta
                    foreach ( $donation_data['meta'] as $meta_import_id => $meta_to_import ) {
                        if ( is_serialized( $meta_to_import['meta_value'] ) ) {
                            $data_to_import = unserialize( $meta_to_import['meta_value'] );
                        } else {
                            $data_to_import = $meta_to_import['meta_value'];
                        }
                        if ( $meta_to_import['meta_key'] === 'donor' ) {
                            $donor_data = unserialize( $meta_to_import['meta_value'] );
                        }
                        if ( $meta_to_import['meta_key'] === '_donation_log' ) {
                            $donation_log = unserialize( $meta_to_import['meta_value'] );
                        }

                        add_post_meta( $donation_id, $meta_to_import['meta_key'], $data_to_import );
                    }

                    add_post_meta( $donation_id,'_campaign_imported_donation_id', intval( $old_campaign_id ) );

                    // determine donor id from the donor data (email is key).
                    $donor_id = $this->get_donor_id( $donor_data ); // $campaign_creator_id,

                    // update custom table.
                    $table = $wpdb->prefix . 'charitable_campaign_donations';
                    $args = array(
                        'donation_id'   => $donation_id,
                        'donor_id'      => $donor_id,
                        'campaign_id'   => $campaign_id,
                        'campaign_name' => $campaign_name,
                        'amount'        => $campaign_donations_info['charitable_campaign_donations'][ $found_key ]['amount'], // ... and so on
                    );

                    $wpdb->insert( $table, $args );

                    // update log.
                    $donation_log[] = array ( 'time' => time(), 'message' => 'Donation Imported.' );
                    update_post_meta( $donation_id, '_donation_log', $donation_log );

                    $donations_added++;

                }
            }

            $this->add_update_message( __( $donations_added . ' donations imported.', 'charitable' ), 'success' );

			$redirect_link = admin_url( 'admin.php?page=charitable-settings&tab=tools&status=success+' . $donations_added );
			wp_safe_redirect( $redirect_link );
			exit;

        }

	/**
	 * Determines if a gallery import file has a proper file extension.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the imported gallery file has a proper file extension, false otherwise.
	 */
	public function has_json_extension( $import_what = 'import_campaign' ) {

		$file_array = isset( $_FILES['charitable_settings']['name']['tools'][$import_what] ) ? explode( '.', $_FILES['charitable_settings']['name']['tools'][$import_what] ) : null;
		$extension  = end( $file_array );
		return 'json' === $extension;

	}

	/**
	 * Retrieve the contents of the imported gallery file.
	 *
	 * @since 1.0.0
	 *
	 * @return string|bool JSON contents string if successful, false otherwise.
	 */
	public function get_file_contents( $import_what = 'import_campaign' ) {

		$file = isset( $_FILES['charitable_settings']['tmp_name']['tools'][$import_what] ) ? wp_unslash( $_FILES['charitable_settings']['tmp_name']['tools'][$import_what] ) : false;
		return file_get_contents( $file );

	}

	/**
	 * Locate the user id of someone using this email
	 *
	 * @since 1.0.0
	 *
	 * @return string|bool JSON contents string if successful, false otherwise.
	 */
    public function get_creator_id( $author_data, $zero_allowed = false ) {

        $email = false;

        // try finding a user by email first
        if ( isset( $author_data['email'] ) ) {
            $email = $author_data['email'];
        } else if ( isset( $author_data['user_email'] ) ) {
            $email = $author_data['user_email'];
        }

        if ( $email ) {
            $user = get_user_by( 'email', $email );
            if ( $user ) {
                return $user->ID;
            }
        }

        return $zero_allowed ? 0 : get_current_user_id();

    }

	/**
	 * Locate the user id of someone using this email
	 *
	 * @since 1.0.0
	 *
	 * @return string|bool JSON contents string if successful, false otherwise.
	 */
    public function get_donor_id( $donor_data ) {

        global $wpdb;

        $email = esc_html( $donor_data['email'] );

        // update custom table.
        $table = $wpdb->prefix . 'charitable_donors';

        // try finding if they still exist first.
        $donor_id = $wpdb->get_var("SELECT donor_id FROM $table WHERE email = '$email'");

        // if not, create it in the table.
        if ( $donor_id == null)  {

            $donor_id = $wpdb->insert( $table, array(
                'donor_id'    => $donor_id,
                'user_id'     => $this->get_creator_id( $donor_data, true ),
                'email'       => $donor_data['email'],
                'first_name'  => $donor_data['first_name'],
                'last_name'   => $donor_data['last_name'],
                'date_joined' => $donor_data['date_joined'],
            ));

        }

        return $donor_id;
    }

    /**
     * Add an update message.
     *
     * @since  1.4.6
     *
     * @param  string  $message     The message text.
     * @param  string  $type        The type of message. Options: 'error', 'success', 'warning', 'info'.
     * @param  boolean $dismissible Whether the message can be dismissed.
     * @return void
     */
    public function add_update_message( $message, $type = 'error', $dismissible = true ) {
        if ( ! in_array( $type, array( 'error', 'success', 'warning', 'info' ) ) ) {
            $type = 'error';
        }

        charitable_get_admin_notices()->add_notice( $message, $type, false, $dismissible );
    }


}

endif;
