<?php
/**
 * Renders the donation details meta box for the Donation post type.
 *
 * @author    David Bisset
 * @package   Charitable/Admin Views/Metaboxes
 * @copyright Copyright (c) 2022, WP Charitable LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 * @version   1.6.35
 */

global $post;

$logs             = charitable_get_donation( $post->ID )->log()->get_log();
$date_time_format = get_option( 'date_format' ) . ' - ' . get_option( 'time_format' );

if ( '-' === trim( $date_time_format ) ) { // just in case the format is blank in settings
	$date_time_format = 'F j, Y' . ' - ' . 'g:i a';
}

?>
<div id="charitable-donation-log-metabox" class="charitable-metabox">
	<table class="widefat">
		<thead>
			<tr>
				<th><?php _e( 'Date &amp; Time', 'charitable' ); ?></th>
				<th><?php _e( 'Log', 'charitable' ); ?></th>
			</th>
		</thead>
		<?php foreach ( $logs as $log ) : ?>
		<tr>
			<td><?php echo get_date_from_gmt( date( 'Y-m-d H:i:s', $log['time'] ), $date_time_format ); ?></td>
			<td><?php echo $log['message']; ?></td>
		</tr>
		<?php endforeach ?>
	</table>
</div>
