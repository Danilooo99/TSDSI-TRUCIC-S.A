<?php
/**
 * FAQ Woocommerce Schema
 *
 * @class    FAQ_Woocommerce_Schema
 * @package  FAQ_Woocommerce\Schema
 * @version  1.2.2
 *
 * @link    https://wpfeel.net/
 * @since   1.2.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * FAQ_Woocommerce_Schema class.
 */
class FAQ_Woocommerce_Schema {
    /**
     * Faq list.
     *
     * @var array
     */
    protected $faqs = array();

    /**
     * Display Type.
     *
     * @var array
     */
    protected $disply_type = "";

    /**
     * Schema Enable/Disable.
     *
     * @var mixed
     */
    protected $schema_enable = true;

    /**
     * FFW Settings Options
     *
     * @var mixed
     */
    public $options;

    /**
     * Constructor.
     */
    public function __construct($faqs, $type) {
        $this->faqs = !empty($faqs) ? $faqs : $this->faqs;
        $this->disply_type = $type;
        $this->schema_enable = $this->get_schema_setting();
        $this->options  = get_option( 'ffw_general_settings' );
        $this->display_schema();
        //add_action( 'wp_head', array( $this, 'display_schema' ) );
    }

    /**
     * Get schema setting.
     *
     * @return boolean
     */
    public function get_schema_setting() {
        $options        = get_option( 'ffw_general_settings' );
        $options        = ! empty( $options ) ? $options : [];
        $enable_schema  = array_key_exists( 'ffw_disable_schema', $options ) && ! empty($options['ffw_disable_schema']) ? (int) $options['ffw_disable_schema'] : 1;

        return isset($enable_schema) && 1 === $enable_schema ? true : false;
    }

    /**
     * Display FAQ
     */
    public function display_schema() {
        if( $this->schema_enable && !empty($this->faqs) && !empty($this->disply_type) ) {
            if( "shortcode" === $this->disply_type ) {
                $this->process_faqs_schema($this->faqs, $this->disply_type);
            }else {
                if( is_product() ) {
                    $this->process_faqs_schema($this->faqs, $this->disply_type);
                }
            }
        }
    }

    /**
     * Process FAQs Schema to display
     *
     * @since 1.3.29
     */
    public function process_faqs_schema($faqs, $type) {
        $options            = ! empty( $this->options ) ? $this->options : [];
        $schema_desc_type   = array_key_exists( 'ffw_schema_description_type', $options ) && ! empty($options['ffw_schema_description_type']) ? $options['ffw_schema_description_type'] : 1;

        $faq_lists = [];
        $single_schema_data = [];
        if( sizeof($faqs) > 0 ) {
            foreach ($faqs as $key => $faq_list) {
                foreach( $faq_list as $faq_key => $faq_value ) {

                    //strip tags and spacial characters
                    if( 2 === (int) $schema_desc_type ) {
                        $faq_value = ffw_strip_all_tags(wp_specialchars_decode($faq_value));
                    }

                    $single_schema_data['@type'] = 'Question';
                    if( 'question' === $faq_key ) {
                        $single_schema_data['name'] = $faq_value;
                    }
                    $single_schema_data['acceptedAnswer']['@type'] = 'Answer';
                    if( 'answer' === $faq_key ) {
                        $single_schema_data['acceptedAnswer']['text'] = $faq_value;
                    }
                }
                array_push($faq_lists, $single_schema_data);
            }
        }

        if( !empty($faq_lists) ) {
            ?>
            <!-- faq schema by XPlainer - FAQ for Woocommerce -->
            <script type="application/ld+json">
                <?php
                $markup["@context"]     = "https://schema.org";
                $markup["@type"]        = "FAQPage";
                $markup["mainEntity"]   = $faq_lists;
                echo json_encode($markup, JSON_UNESCAPED_SLASHES);
                ?>
                </script>
            <?php
        }
    }

}
