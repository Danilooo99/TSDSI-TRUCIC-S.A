<?php

// Create a helper function for easy SDK access.
class dapofFsNull {
    public function is_plan__premium_only() {
        return true;
    }
    public function is_plan() {
        return true;
    }
    public function can_use_premium_code__premium_only() {
        return true;
    }
    public function checkout_url() {
        return '';
    }
}
if ( !function_exists( 'dapof_fs' ) ) {
    function dapof_fs()
    {
        global  $dapof_fs ;
        
        if ( !isset( $dapof_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_1877_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_1877_MULTISITE', true );
            }
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $dapof_fs = new dapofFsNull();
        }
        
        return $dapof_fs;
    }
    
    // Init Freemius.
    dapof_fs();
    // Signal that SDK was initiated.
    do_action( 'dapof_fs_loaded' );
}
