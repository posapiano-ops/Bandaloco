<div class="wrap about-wrap">
    <h1><?php esc_html_e( 'Redux Framework - Changelog', 'borntogive' ); ?></h1>

    <div class="about-text">
        <?php esc_html_e( 'Our core mantra at Redux is backwards compatibility. With hundreds of thousands of instances worldwide, you can be assured that we will take care of you and your clients.', 'borntogive' ); ?>
    </div>
    <div class="redux-badge">
        <i class="el el-redux"></i>
        <span>
            <?php printf( __( 'Version %s', 'borntogive' ), esc_html(ReduxFramework::$_version) ); ?>
        </span>
    </div>

    <?php $this->actions(); ?>
    <?php $this->tabs(); ?>

    <div class="changelog">
        <div class="feature-section">
            <?php echo wp_kses_post($this->parse_readme()); ?>
        </div>
    </div>

</div>