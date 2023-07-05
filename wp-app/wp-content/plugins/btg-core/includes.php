<?php
if (!class_exists('ReduxFramework')) {
    include_once BTG__PLUGIN_PATH . 'imi-admin/theme-options/ReduxCore/framework.php';
}
if (is_admin()) {
    include_once BTG__PLUGIN_PATH . 'imi-admin/admin.php';
}
//Meta Boxes
require_once BTG__PLUGIN_PATH . 'meta-boxes/registrant_tickets_field.php';
require_once BTG__PLUGIN_PATH . 'meta-boxes/taxonomy_banner.php';
require_once BTG__PLUGIN_PATH . 'meta-boxes/term_color_picker.php';
require_once BTG__PLUGIN_PATH . 'meta-boxes/tickets_clone_fields.php';
require_once BTG__PLUGIN_PATH . 'meta-boxes/meta-box-show-hide/meta-box-show-hide.php';
require_once BTG__PLUGIN_PATH . 'meta-boxes/meta-box-tabs/meta-box-tabs.php';
require_once BTG__PLUGIN_PATH . 'meta-boxes/mb-admin-columns/mb-admin-columns.php';
//Widgets
require_once BTG__PLUGIN_PATH . 'widgets/custom_category.php';
require_once BTG__PLUGIN_PATH . 'widgets/recent_posts.php';
require_once BTG__PLUGIN_PATH . 'widgets/tabs_widget.php';
require_once BTG__PLUGIN_PATH . 'widgets/InstaGram/insta_gallery.php';
require_once BTG__PLUGIN_PATH . 'widgets/twitter_feeds/twitter_feeds.php';

// Remove WPBakery redirect after activation
remove_action('admin_init', 'vc_page_welcome_redirect');

if (class_exists('Woocommerce')) {
    if ( ! function_exists( 'remove_class_filters' ) ) {
		function remove_class_filters( $tag, $class, $method ) {
			$filters = $GLOBALS['wp_filter'][ $tag ];
			if ( empty ( $filters ) ) {
				return;
			}
			foreach ( $filters as $priority => $filter ) {
				foreach ( $filter as $identifier => $function ) {
					if ( is_array( $function )  ) {

						if ( is_array( $function['function'] ) || is_string( $function['function'] ) ) {

							if ( is_a( $function['function'][0], $class ) and $method === $function['function'][1] ) {

								remove_filter(
									$tag,
									array ( $function['function'][0], $method ),
									$priority
								);

							}

						}

					}

				}

			}

		}

	}

	add_action( 'admin_init', 'disable_shop_redirect', 0 );
	function disable_shop_redirect() {
		remove_class_filters(
			'admin_init',
			'WC_Admin',
			'admin_redirects'
		);
	}
}

//Add New Custom Menu Option
if (!class_exists('BORNTOGIVE_Custom_Nav')) {
    class BORNTOGIVE_Custom_Nav
    {
        public function borntogive_add_nav_menu_meta_boxes()
        {

            add_meta_box(
                'mega_nav_link',
                esc_html__('Mega Menu', 'borntogive'),
                array($this, 'nav_menu_link'),
                'nav-menus',
                'side',
                'low'
            );
        }
        public function nav_menu_link()
        {

            global $_nav_menu_placeholder, $nav_menu_selected_id;
            $_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;

            ?>
        <div id="posttype-wl-login" class="posttypediv">
            <div id="tabs-panel-wishlist-login" class="tabs-panel tabs-panel-active">
                <ul id="wishlist-login-checklist" class="categorychecklist form-no-clear">
                    <li>
                        <label class="menu-item-title">
                            <input type="checkbox" class="menu-item-object-id" name="menu-item[<?php echo esc_attr($_nav_menu_placeholder); ?>][menu-item-object-id]" value="<?php echo esc_attr($_nav_menu_placeholder); ?>"> <?php esc_html_e('Create Column', 'borntogive'); ?>
                        </label>
                        <input type="hidden" class="menu-item-db-id" name="menu-item[<?php echo esc_attr($_nav_menu_placeholder); ?>][menu-item-db-id]" value="0">
                        <input type="hidden" class="menu-item-object" name="menu-item[<?php echo esc_attr($_nav_menu_placeholder); ?>][menu-item-object]" value="page">
                        <input type="hidden" class="menu-item-parent-id" name="menu-item[<?php echo esc_attr($_nav_menu_placeholder); ?>][menu-item-parent-id]" value="0">
                        <input type="hidden" class="menu-item-type" name="menu-item[<?php echo esc_attr($_nav_menu_placeholder); ?>][menu-item-type]" value="">
                        <input type="hidden" class="menu-item-title" name="menu-item[<?php echo esc_attr($_nav_menu_placeholder); ?>][menu-item-title]" value="<?php esc_html_e('Column', 'borntogive'); ?>">
                        <input type="hidden" class="menu-item-classes" name="menu-item[<?php echo esc_attr($_nav_menu_placeholder); ?>][menu-item-classes]" value="custom_mega_menu">
                    </li>
                </ul>
            </div>
            <p class="button-controls">
                <span class="add-to-menu">
                    <input type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_html_e('Add to Menu', 'borntogive'); ?>" name="add-post-type-menu-item" id="submit-posttype-wl-login">
                    <span class="spinner"></span>
                </span>
            </p>
        </div>
    <?php }
}
}
$custom_nav = new BORNTOGIVE_Custom_Nav;
add_action('admin_init', array($custom_nav, 'borntogive_add_nav_menu_meta_boxes'));


if (!function_exists('borntogive_contact_event_manager')) {
function borntogive_contact_event_manager(){
	$event_id = $_POST['itemnumber'];
	$post_type = get_post_type($event_id);
	$event_date = $_POST['event_date'];
	$exhibition_time = (isset($_POST['exhibition_time']))?$_POST['exhibition_time']:'';
	$cost = (isset($_POST['costs'])&&$_POST['costs']!=0)?$_POST['costs']:esc_html__('Free', 'borntogive');
	$name = $_POST['name'];
	$lname = $_POST['lastname'];
	$email = $_POST['email'];
	$phone = $_POST['phone'];
	$address = $_POST['address'];
	$notes = $_POST['notes'];
	$offline_payment = (isset($_POST['offline_payment']))?$_POST['offline_payment']:0;
	$ticket_details = (isset($_POST['ticket_details']))?$_POST['ticket_details']:array();
	$event_title = get_the_title($event_id);
	$registration_number = get_post_meta($event_id, 'borntogive_event_registration_number', true);
	$registration_number = ($registration_number=='')?0:$registration_number+1;
	$reg_post_type = ($post_type=='event')?'event_registrants':'exhibition_reg';
	$registrant = array(
	  'post_title'    => $name.' '.$lname,
	  'post_status'   => 'publish',
	  'post_author'   => 1,
	  'post_type' => $reg_post_type
	);
	
	// Insert the registrant into the database
	$registrant_id = wp_insert_post( $registrant );
	if($post_type=='event')
	{
		wp_set_object_terms($registrant_id, get_the_title($event_id), 'registrant-event');
	}
	elseif($post_type=='exhibition')
	{
		wp_set_object_terms($registrant_id, get_the_title($event_id), 'registrant-exhibition');
	}
	$tickets_type_event = get_post_meta($event_id, 'tickets_type', true);
	if(!empty($ticket_details))
	{
		$ticket_info = array();
		foreach($ticket_details as $key=>$value)
		{
			$ticket_info[$key]=$value;
			if(!empty($tickets_type_event))
			{
				$tickets_type_event = get_post_meta($event_id, 'tickets_type', true);
				$tickets_type = array();
				foreach($tickets_type_event as $tickets)
				{
					if($tickets[0]!=$key)
					{
						$tickets_type[]  = array($tickets[0], $tickets[1], $tickets[2], $tickets[3]);
					}
					else
					{
						$available = $tickets[1];
						$booked_tickets = $tickets[2];
						$new_booked_updated = $booked_tickets+$value;
						$new_available_updated = $available-$value;
						$tickets_type[]  = array($tickets[0], $tickets[1], $new_booked_updated, $tickets[3]);
					}
				}
				delete_post_meta( $event_id, 'tickets_type' );
				update_post_meta($event_id, 'tickets_type', $tickets_type);
			}
		}
		update_post_meta($registrant_id, 'borntogive_registrant_ticket_type', $ticket_info);
	}
	update_post_meta($registrant_id, 'borntogive_registrant_email', esc_attr($email));
	update_post_meta($registrant_id, 'borntogive_registrant_phone', esc_attr($phone));
	update_post_meta($registrant_id, 'borntogive_registrant_address', esc_attr($address));
	update_post_meta($registrant_id, 'borntogive_registrant_additional_notes', esc_attr($notes));
	update_post_meta($registrant_id, 'borntogive_registrant_event_date', $event_date);
	update_post_meta($registrant_id, 'borntogive_registrant_registration_number', esc_attr($event_id.'-'.$registration_number));
	if($offline_payment==1)
	{
		update_post_meta($registrant_id, 'borntogive_registrant_payment_status', esc_html__('offline', 'borntogive'));
	}
	update_post_meta($event_id, 'borntogive_event_registration_number', $registration_number);
	if($post_type=="exhibition")
	{
		update_post_meta($registrant_id, 'borntogive_registrant_exhibition_time', $exhibition_time);
	}
	$event_manager_email = get_post_meta($event_id,'borntogive_event_manager',true);
	$manager_email = esc_attr($event_manager_email);
	$manager_email = ($manager_email!='')?$manager_email:get_option('admin_email');
	if($post_type=="event")
	{
		$e_subject = esc_html__('Registration for Event','borntogive');
	}
	else
	{
		$e_subject = esc_html__('Registration for Exhibition','borntogive');
	}
	$result['regid'] = esc_attr($event_id.'-'.$registration_number);
  $result['reguser'] = esc_attr($name).'<br/>'. esc_attr($lname);
	$result['cost'] = esc_attr($cost);
	$result['registrant'] = esc_attr($registrant_id);
	$result = json_encode($result);
	echo ''.$result;
	$e_body = esc_html__("You have been contacted by", "borntogive").' '.$name.', '.esc_html__("for", "borntogive").' '.$event_title . PHP_EOL . PHP_EOL;
	$body = esc_html__("Your message has been delivered to Manager for", "borntogive").' '. $event_title . PHP_EOL . PHP_EOL;
    $e_content = '';
    $e_content .= esc_html__("Registration Number:", "borntogive").' '. $event_id.'-'.$registration_number . PHP_EOL . PHP_EOL;
    $e_content .= esc_html__("Date:", "borntogive").' '. $event_date . PHP_EOL . PHP_EOL;
    if($post_type!="event")
    {
        $e_content .= esc_html__("Time:", "borntogive").' '. $exhibition_time . PHP_EOL . PHP_EOL;
    }
    $e_content .= esc_html__("Name:", "borntogive").' '. esc_attr($name). esc_attr($lname). PHP_EOL . PHP_EOL;
    $e_content .= esc_html__("Email:", "borntogive").' '. esc_attr($email) . PHP_EOL . PHP_EOL;
    $e_content .= esc_html__("Phone:", "borntogive").' '. esc_attr($phone) . PHP_EOL . PHP_EOL;
    $e_content .= esc_html__("Notes:", "borntogive").' '. esc_attr($notes) . PHP_EOL . PHP_EOL;
    $e_content .= esc_html__("Address:", "borntogive").' '. esc_attr($address) . PHP_EOL . PHP_EOL;
    $e_reply = esc_html__("You can contact ", "borntogive")." ".esc_attr($name)." ".esc_html__("via email", "borntogive").', '.esc_attr($email);
    $reply = esc_html__("You can contact manager via email", "borntogive").', '. $manager_email;
    $msg = wordwrap( $e_body . $e_content . $e_reply, 70 );
    $user_msg = wordwrap( $body . $e_content . $reply, 70 );
    $headers = "From: $email" . PHP_EOL;
    $headers .= "Reply-To: $email" . PHP_EOL;
    $headers .= "MIME-Version: 1.0" . PHP_EOL;
    $headers .= "Content-type: text/plain; charset=utf-8" . PHP_EOL;
    $headers .= "Content-Transfer-Encoding: quoted-printable" . PHP_EOL;
    $user_headers = "From: $manager_email" . PHP_EOL;
    $user_headers .= "Reply-To: $email" . PHP_EOL;
    $user_headers .= "MIME-Version: 1.0" . PHP_EOL;
    $user_headers .= "Content-type: text/plain; charset=utf-8" . PHP_EOL;
    $user_headers .= "Content-Transfer-Encoding: quoted-printable" . PHP_EOL;
    if(mail($manager_email, $e_subject, $msg, $headers)&&mail($email, $e_subject, $user_msg, $user_headers)) {
        if($post_type=="event")
        {
            //echo "<p>".esc_html__("An Email has been send to you with Event Registration Number", "borntogive")."</p>";
        }
        else
        {
            //echo "<p>".esc_html__("An Email has been send to you with Exhibition Registration Number", "borntogive")."</p>";
        }
        //echo "<p>".esc_html__("Registration Number:", "borntogive")." ".$event_id."-".$registration_number.$exhibition_time."</p></div>";
    } else {
        echo '<div class="alert alert-error">ERROR!</div>';
    }
        die();
    }
    add_action('wp_ajax_nopriv_borntogive_contact_event_manager', 'borntogive_contact_event_manager');
    add_action('wp_ajax_borntogive_contact_event_manager', 'borntogive_contact_event_manager');
}
