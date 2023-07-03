<?php 
/**
 * Displays the campaign progress bar.
 *
 * Override this template by copying it to yourtheme/charitable/campaign/progress-bar.php
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * @var Charitable_Campaign
 */
$campaign = $view_args[ 'campaign' ];

if ( ! $campaign->has_goal() ) :
    return;
endif;
if($campaign->get_percent_donated_raw()<=30)
{
	$color = 'F23827';
}
elseif($campaign->get_percent_donated_raw()>30&&$campaign->get_percent_donated_raw()<=60)
{
	$color = 'F6bb42';
}
else
{
	$color = '8cc152';
}

$options = get_option('borntogive_options');
$cam_show_progress = (isset($options['cam_show_progress']))?$options['cam_show_progress']:1;
$cam_show_timeleft = (isset($options['cam_show_timeleft']))?$options['cam_show_timeleft']:1;
?>

<?php if($cam_show_progress != 0){ ?>
<a class="cProgress" <?php if($cam_show_timeleft != 0){ ?>data-original-title="<?php echo strip_tags($campaign->get_time_left()); ?>" data-toggle="tooltip" <?php } ?> data-color="<?php echo esc_attr($color); ?>" data-complete="<?php echo esc_attr($campaign->get_percent_donated_raw()); ?>">
<strong>
<?php echo esc_attr($campaign->get_percent_donated_raw()); ?>
</strong>
</a>
<?php } ?>