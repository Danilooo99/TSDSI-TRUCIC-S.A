<?php
/**
 * Help Panel.
 *
 * @package Travel_Agency
 */
?>
<!-- Help file panel -->
<div id="help-panel" class="panel-left">

    <div class="panel-aside">
        <h4><?php esc_html_e( 'View Our Documentation Link', 'travel-agency' ); ?></h4>
        <p><?php esc_html_e( 'Are you new to the WordPress world? Our step by step easy documentation guide will help you create an attractive and engaging website without any prior coding knowledge or experience.', 'travel-agency' ); ?></p>
        <a class="button button-primary" href="<?php echo esc_url( 'https://docs.rarathemes.com/docs/' . TRAVEL_AGENCY_THEME_TEXTDOMAIN . '/' ); ?>" title="<?php esc_attr_e( 'Visit the Documentation', 'travel-agency' ); ?>" target="_blank">
            <?php esc_html_e( 'View Documentation', 'travel-agency' ); ?>
        </a>
    </div><!-- .panel-aside -->
    
    <div class="panel-aside">
        <h4><?php esc_html_e( 'Support Ticket', 'travel-agency' ); ?></h4>
        <p><?php printf( __( 'It\'s always better to visit our %1$sDocumentation Guide%2$s before you send us a support query.', 'travel-agency' ), '<a href="'. esc_url( 'https://docs.rarathemes.com/docs/' . TRAVEL_AGENCY_THEME_TEXTDOMAIN . '/' ) .'" target="_blank">', '</a>' ); ?></p>
        <p><?php printf( __( 'If the Documentation Guide didn\'t help you, contact us via our %1$sSupport Ticket%2$s. We reply to all the support queries within one business day, except on the weekends.', 'travel-agency' ), '<a href="'. esc_url( 'https://rarathemes.com/support-ticket/' ) .'" target="_blank">', '</a>' ); ?></p>
        <a class="button button-primary" href="<?php echo esc_url( 'https://rarathemes.com/support-ticket/' ); ?>" title="<?php esc_attr_e( 'Visit the Support', 'travel-agency' ); ?>" target="_blank">
            <?php esc_html_e( 'Contact Support', 'travel-agency' ); ?>
        </a>
    </div><!-- .panel-aside -->

    <div class="panel-aside">
        <h4><?php printf( esc_html__( 'View Our %1$s Demo', 'travel-agency' ), TRAVEL_AGENCY_THEME_NAME ); ?></h4>
        <p><?php esc_html_e( 'Visit the demo to get more idea about our theme design and its features.', 'travel-agency' ); ?></p>
        <a class="button button-primary" href="<?php echo esc_url( 'https://rarathemes.com/previews/?theme=' . TRAVEL_AGENCY_THEME_TEXTDOMAIN  ); ?>" title="<?php esc_attr_e( 'Visit the Demo', 'travel-agency' ); ?>" target="_blank">
            <?php esc_html_e( 'View Demo', 'travel-agency' ); ?>
        </a>
    </div><!-- .panel-aside -->
</div>