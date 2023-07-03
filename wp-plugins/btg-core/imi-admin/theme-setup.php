<?php

/*Theme info*/
function imi_get_theme_info() {
	$theme = wp_get_theme();
	$theme_name = $theme->get('Name');
	$theme_v = $theme->get('Version');

	$theme_info = array(
		'name' => $theme_name,
		'slug' => sanitize_file_name(strtolower($theme_name)),
		'v'    => $theme_v,
	);

	return $theme_info;
}

function imi_get_creds() {

	$t = get_option('envato_market', array());
	if( !empty($t['token']) ) {
		$creds['t'] = $t['token'];
	}else{
		$creds['t'] = '';
	}
	$creds['host'] = false;

	return $creds;
}


function imi_set_creds() {
	if(isset($_POST['imi_registration'])) {
		if(isset($_POST['imi_registration']['token'])) {
			delete_site_transient('imi_theme_auth');

			$token = array();
			$token['token'] = sanitize_text_field($_POST['imi_registration']['token']);

			update_option('envato_market', $token);

				$envato_market = Envato_Market::instance();
				$envato_market->items()->set_themes(true);
		}
	}
}

add_action('init', 'imi_set_creds');


function imi_check_url($url) {
	$headers = @get_headers( $url);
	$headers = ( is_array($headers ) ) ? implode( "\n ", $headers) : $headers;
	return (bool)preg_match('#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers);
}

//Admin tabs
function imi_get_admin_tabs($screen='welcome') {
	if(empty($screen)) {
		$screen ='imi-admin-'.$screen;
	}
	?>
	<h1><?php echo esc_html__( 'Welcome to ', 'borntogive-core' ) . IMI_Admin::theme( 'name' ); ?></h1>
	<div class="about-text"><?php echo IMI_Admin::theme( 'name' ) . esc_html__( ' is now installed and ready to use! Start creating your website from scratch or import a demo. Please register your purchase to get automatic theme updates. Read additional information about registration on the welcome page.', 'borntogive-core' ); ?></div>
	<div class="wp-badge"><?php printf( esc_html__( 'Version %s', 'borntogive-core' ), IMI_Admin::theme( 'version' ) ); ?></div>

	<h2 class="nav-tab-wrapper wp-clearfix">
		<?php if ( !defined('ENVATO_HOSTED_SITE') ): ?>
			<a class="<?php echo ( 'welcome' === $screen ) ? 'nav-tab-active' : ''; ?> nav-tab" href="<?php echo esc_url( self_admin_url( 'admin.php?page=imi-admin-welcome' ) ); ?>"><?php esc_html_e( 'Welcome', 'borntogive-core' ); ?></a>
			<a class="<?php echo ( 'demo-importer' === $screen ) ? 'nav-tab-active' : ''; ?> nav-tab" href="<?php echo esc_url( self_admin_url( 'admin.php?page=imi-admin-demo-importer' ) ); ?>"><?php esc_html_e( 'Demo Importer', 'borntogive-core' ); ?></a>
			<a class="<?php echo ( 'plugins' === $screen ) ? 'nav-tab-active' : ''; ?> nav-tab" href="<?php echo esc_url( self_admin_url( 'admin.php?page=imi-admin-plugins' ) ); ?>"><?php esc_html_e( 'Plugins', 'borntogive-core' ); ?></a>
			<a class="<?php echo ( 'support' === $screen ) ? 'nav-tab-active' : ''; ?> nav-tab" href="<?php echo esc_url( self_admin_url( 'admin.php?page=imi-admin-support' ) ); ?>"><?php esc_html_e( 'Support', 'borntogive-core' ); ?></a>
			<a class="<?php echo ( 'system-status' === $screen ) ? 'nav-tab-active' : ''; ?> nav-tab" href="<?php echo esc_url( self_admin_url( 'admin.php?page=imi-admin-system-status' ) ); ?>"><?php esc_html_e( 'System Status', 'borntogive-core' ); ?></a>
			<a class="nav-tab" href="<?php echo esc_url( self_admin_url( 'admin.php?page=_options' ) ); ?>"><?php esc_html_e( 'Theme Options', 'borntogive-core' ); ?></a>
		<?php else: ?>
			<a class="<?php echo ( 'demo-importer' === $screen ) ? 'nav-tab-active' : ''; ?> nav-tab" href="<?php echo esc_url( self_admin_url( 'admin.php?page=imi-admin-demo-importer' ) ); ?>"><?php esc_html_e( 'Demo Importer', 'borntogive-core' ); ?></a>
			<a class="<?php echo ( 'plugins' === $screen ) ? 'nav-tab-active' : ''; ?> nav-tab" href="<?php echo esc_url( self_admin_url( 'admin.php?page=imi-admin-plugins' ) ); ?>"><?php esc_html_e( 'Plugins', 'borntogive-core' ); ?></a>
			<a class="nav-tab" href="<?php echo esc_url( self_admin_url( 'admin.php?page=_options' ) ); ?>"><?php esc_html_e( 'Theme Options', 'borntogive-core' ); ?></a>
		<?php endif; ?>
	</h2>
	<?php
}