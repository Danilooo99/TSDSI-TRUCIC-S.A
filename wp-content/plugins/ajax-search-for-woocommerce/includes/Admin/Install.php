<?php

namespace DgoraWcas\Admin;

use  DgoraWcas\Engines\TNTSearchMySQL\Indexer\Builder ;
use  DgoraWcas\Helpers ;
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Install
{
    /**
     * Call installation callback
     *
     * @return void
     */
    public static function maybeInstall()
    {
        if ( !(defined( 'DOING_AJAX' ) && DOING_AJAX) ) {
            add_action( 'admin_init', array( __CLASS__, 'checkVersion' ), 5 );
        }
    }
    
    /**
     * Install process
     *
     * @return void
     */
    public static function install()
    {
        if ( !defined( 'DGWT_WCAS_INSTALLING' ) ) {
            define( 'DGWT_WCAS_INSTALLING', true );
        }
        self::saveActivationDate();
        self::createOptions();
        // Update plugin version
        update_option( 'dgwt_wcas_version', DGWT_WCAS_VERSION );
    }
    
    /**
     * Save default options
     *
     * @return void
     */
    private static function createOptions()
    {
        global  $dgwtWcasSettings ;
        $sections = DGWT_WCAS()->settings->settingsFields();
        $settings = array();
        if ( is_array( $sections ) && !empty($sections) ) {
            foreach ( $sections as $options ) {
                if ( is_array( $options ) && !empty($options) ) {
                    foreach ( $options as $option ) {
                        if ( isset( $option['name'] ) && !isset( $dgwtWcasSettings[$option['name']] ) ) {
                            $settings[$option['name']] = ( isset( $option['default'] ) ? $option['default'] : '' );
                        }
                    }
                }
            }
        }
        $updateOptions = array_merge( $settings, $dgwtWcasSettings );
        update_option( DGWT_WCAS_SETTINGS_KEY, $updateOptions );
    }
    
    /**
     * Save activation timestamp
     * Used to display notice, asking for a feedback
     *
     * @return void
     */
    private static function saveActivationDate()
    {
        $date = get_option( 'dgwt_wcas_activation_date' );
        if ( empty($date) ) {
            update_option( 'dgwt_wcas_activation_date', time() );
        }
    }
    
    /**
     * Compare plugin version and install if a new version is available
     *
     * @return void
     */
    public static function checkVersion()
    {
        if ( !defined( 'IFRAME_REQUEST' ) ) {
            if ( !dgoraAsfwFs()->is_premium() && get_option( 'dgwt_wcas_version' ) != DGWT_WCAS_VERSION ) {
                self::install();
            }
        }
    }

}