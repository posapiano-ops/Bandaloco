<?php
/**
 * OAuth section
 *
 * @package Envato_Market
 * @since 1.0.0
 */

?>

<p>
	<?php printf( esc_html__( 'This area enables WordPress Theme &amp; Plugin updates from Envato Market. Read more about how this process works at %s. Please follow the steps below:', 'borntogive' ), '<a href="https://envato.com/market-plugin/" target="_blank">' . esc_html__( 'envato.com', 'borntogive' ) . '</a>' ); ?>
</p>
<ol>
	<li><?php printf( esc_html__( 'Generate an Envato API Personal Token by %s.', 'borntogive' ), '<a href="https://build.envato.com/create-token/?purchase:download=t&purchase:verify=t&purchase:list=t" target="_blank">' . esc_html__( 'clicking this link', 'borntogive' ) . '</a>' ); ?></li>
	<li><?php esc_html_e( 'Copy the token into the box below.', 'borntogive' ); ?></li>
	<li><?php esc_html_e( 'Click the "Save Changes" button.', 'borntogive' ); ?></li>
	<li><?php esc_html_e( 'A list of purchased Themes &amp; Plugins from Envato Market will appear.', 'borntogive' ); ?></li>
</ol>
