<?php
/*** Widget code for Twitter Feeds ***/
class borntogive_insta_gallery extends WP_Widget
{
	// constructor
	public function __construct()
	{
		$widget_ops = array('description' => __("Show Instagram images in a grid.", 'borntogive-core'));
		parent::__construct(false, $name = __('(N) Instagram Gallery', 'borntogive-core'), $widget_ops);
	}
	// widget form creation
	public function form($instance)
	{
		// Check values
		if ($instance) {
			$title = esc_attr($instance['title']);
			$username = esc_attr($instance['username']);
			$tag = esc_attr($instance['tag']);
			$display_profile = $instance['display_profile'];
			$display_biography = $instance['display_biography'];
			$items = esc_attr($instance['items']);
			$items_per_row = esc_attr($instance['items_per_row']);
			$margin = esc_attr($instance['margin']);
			$image_size = esc_attr($instance['image_size']);
		} else {
			$title = '';
			$username = '';
			$tag = '';
			$display_profile = false;
			$display_biography = false;
			$items = '6';
			$items_per_row = '3';
			$margin = '1';
			$image_size = '150';
		}
		?>
	<p>
		<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title', 'borntogive-core'); ?></label>
		<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
	</p>
	<p>
		<label for="<?php echo esc_attr($this->get_field_id('username')); ?>"><?php _e('Username', 'borntogive-core'); ?></label>
		<input class="widefat" id="<?php echo esc_attr($this->get_field_id('username')); ?>" name="<?php echo esc_attr($this->get_field_name('username')); ?>" type="text" value="<?php echo esc_attr($username); ?>" />
	</p>
	<p>
		<label for="<?php echo esc_attr($this->get_field_id('tag')); ?>"><?php _e('Tag', 'borntogive-core'); ?></label>
		<input class="widefat" id="<?php echo esc_attr($this->get_field_id('tag')); ?>" name="<?php echo esc_attr($this->get_field_name('tag')); ?>" type="text" value="<?php echo esc_attr($tag); ?>" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'display_profile' ); ?>">
		<input type="checkbox" value="1" id="<?php echo $this->get_field_id( 'display_profile' ); ?>" name="<?php echo $this->get_field_name( 'display_profile' ); ?>" <?php checked( $display_profile); ?>/> <?php _e( 'Display Profile?', 'borntogive-core' ); ?>
		</label>
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'display_biography' ); ?>">
		<input type="checkbox" value="1" id="<?php echo $this->get_field_id( 'display_biography' ); ?>" name="<?php echo $this->get_field_name( 'display_biography' ); ?>" <?php checked( $display_biography); ?>/> <?php _e( 'Display Biography?', 'borntogive-core' ); ?>
        <div style="padding-bottom: 0" class="description"><?php _e('Enables displaying the biography. Only for users', 'borntogive-core'); ?></div>
		</label>
	</p>
	<p>
		<label for="<?php echo esc_attr($this->get_field_id('items')); ?>"><?php _e('Number of items', 'borntogive-core'); ?></label>
		<input class="widefat" id="<?php echo esc_attr($this->get_field_id('items')); ?>" name="<?php echo esc_attr($this->get_field_name('items')); ?>" type="text" value="<?php echo esc_attr($items); ?>" />
        <div style="padding-bottom: 0" class="description"><?php _e('Number of items to display. Up to 12 for users, up to 72 for tags', 'borntogive-core'); ?></div>
	</p>
	<p>
		<label for="<?php echo esc_attr($this->get_field_id('items_per_row')); ?>"><?php _e('Items per row', 'borntogive-core'); ?></label>
		<input class="widefat" id="<?php echo esc_attr($this->get_field_id('items_per_row')); ?>" name="<?php echo esc_attr($this->get_field_name('items_per_row')); ?>" type="text" value="<?php echo esc_attr($items_per_row); ?>" />
        <div style="padding-bottom: 0" class="description"><?php _e('Number of items that will be displayed for each row', 'borntogive-core'); ?></div>
	</p>
	<p>
		<label for="<?php echo esc_attr($this->get_field_id('margin')); ?>"><?php _e('Margin between items', 'borntogive-core'); ?></label>
		<input class="widefat" id="<?php echo esc_attr($this->get_field_id('margin')); ?>" name="<?php echo esc_attr($this->get_field_name('margin')); ?>" type="text" value="<?php echo esc_attr($margin); ?>" />
        <div style="padding-bottom: 0" class="description"><?php _e('Margin (percentage) between items in gallery/igtv', 'borntogive-core'); ?></div>
	</p>
	<p>
		<label for="<?php echo esc_attr($this->get_field_id('image_size')); ?>"><?php _e('Image Size', 'borntogive-core'); ?></label>
		<input class="widefat" id="<?php echo esc_attr($this->get_field_id('image_size')); ?>" name="<?php echo esc_attr($this->get_field_name('image_size')); ?>" type="text" value="<?php echo esc_attr($image_size); ?>" />
        <div style="padding-bottom: 0" class="description"><?php _e('Scale of items to build gallery. Accepted values [150, 240, 320, 480, 640]. Does not apply to video previews.', 'borntogive-core'); ?></div>
	</p>
<?php
}
// update widget
public function update($new_instance, $old_instance)
{
	$instance = $old_instance;
	// Fields
	$instance['title'] = strip_tags($new_instance['title']);
	$instance['username'] = strip_tags($new_instance['username']);
	$instance['tag'] = strip_tags($new_instance['tag']);
	$instance['display_profile'] = isset( $new_instance['display_profile'] ) ? true : false;
	$instance['display_biography'] = isset( $new_instance['display_biography'] ) ? true : false;
	$instance['items'] = absint($new_instance['items']);
	$instance['items_per_row'] = absint($new_instance['items_per_row']);
	$instance['margin'] = absint($new_instance['margin']);
	$instance['image_size'] = absint($new_instance['image_size']);
	return $instance;
}
// display widget
public function widget($args, $instance)
{
	extract($args);
	// these are the widget options
	$title = apply_filters('widget_title', $instance['title']);
	$username = apply_filters('widget_username', $instance['username']);
	$tag = apply_filters('widget_tag', $instance['tag']);
	$display_profile = apply_filters('widget_display_profile', $instance['display_profile']);
	$display_biography = apply_filters('widget_display_biography', $instance['display_biography']);
	$items = apply_filters('widget_items', $instance['items']);
	$items_per_row = apply_filters('widget_items_per_row', $instance['items_per_row']);
	$margin = apply_filters('widget_margin', $instance['margin']);
	$image_size = apply_filters('widget_image_size', $instance['image_size']);
	echo '' . $args['before_widget'];
	if (!empty($instance['title'])) {
		echo '' . $args['before_title'];
		echo apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
		echo '' . $args['after_title'];
	}
	$username;
	$tag;
	$display_profile;
	$display_biography;
	$items;
	$items_per_row;
	$margin;
	$image_size;
	$getid = uniqid('imi-insta-widget_');
	wp_enqueue_script('imi_insta', plugin_dir_url( __DIR__ ) . 'InstaGram/js/InstagramFeed.js', array(), '', true);
	wp_enqueue_script($getid, plugin_dir_url( __DIR__ ) . 'InstaGram/js/insta.js', array(), '', true);
	wp_localize_script($getid, 'insta', array('id' => $getid, 'username' => $username, 'tag' => $tag, 'display_profile' => $display_profile, 'display_biography' => $display_biography, 'items' => $items, 'items_per_row' => $items_per_row, 'margin' => $margin, 'image_size' => $image_size));
	echo '<div id="'.$getid.'" class="clearfix"></div>';
	echo '' . $args['after_widget'];
}
}
// register widget
add_action('widgets_init', function () {
	register_widget('borntogive_insta_gallery');
});
?>