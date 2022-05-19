<?php
/**
 * FAQ Woocommerce Admin Settings
 *
 * @class    FAQ_Woocommerce_Settings
 * @package  FAQ_Woocommerce\Admin\Setting
 * @version  1.0.0
 *
 * @link    https://wpfeel.net/
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * FAQ_Woocommerce_Settings class.
 */
class FAQ_Woocommerce_Settings {
    /* Saved options */
    public $options;

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'ffw_add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'ffw_settings_init' ) );

        // Get registered option
        $this->options = get_option( 'ffw_general_settings' );

        //echo "<pre>";
        //print_r($this->options);
    }

	/**
	 * Menu Page.
	 */
    public function ffw_add_admin_menu() {

        // Get registered option
        $options = $this->options;

        if( isset($options['ffw_set_role']) ) {
            $ffw_roles = isset( $options['ffw_set_role'] ) ? $options['ffw_set_role'] : ["administrator"];

            //get current user
            $user = wp_get_current_user();
            $roles = $user->roles;

            //when user role is not set to ffw_set_role, hide the ffw post type
            if( ! empty($roles) && is_array($roles) ) {
                foreach($roles as $role) {
                    if( ! in_array( $role, (array) $ffw_roles )  ) {
                        remove_menu_page( 'edit.php?post_type=ffw' );
                    }
                }
            }
        }

		add_submenu_page( 'edit.php?post_type=ffw', 'XPlainer – WooCommerce Product FAQ', 'Settings', 'manage_options', 'woocommerce-faq', array( $this, 'ffw_options_page' ), 9999 );
    }

	/**
	 * Settings Init.
	 */
    public function ffw_settings_init() {
        register_setting( 'ffw_settings', 'ffw_general_settings' );
        add_settings_section(
            'ffw_general_setting',
            __( '', 'faq-for-woocommerce' ),
            '',
            'ffw_settings'
        );

        add_settings_field(
            'ffw_tab_label',
            __( 'Tab Label', 'faq-for-woocommerce' ),
            array( $this, 'ffw_tab_label' ),
            'ffw_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_tab_priority',
            __( 'Tab Priority', 'faq-for-woocommerce' ),
            array( $this, 'ffw_tab_priority' ),
            'ffw_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_layout',
            __( 'Layout', 'faq-for-woocommerce' ),
            array( $this, 'ffw_select_layout' ),
            'ffw_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_display_all_faq_answers',
            __( 'Display All Answers', 'faq-for-woocommerce' ),
            array( $this, 'ffw_display_all_faq_answers' ),
            'ffw_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_expand_collapse_all',
            __( 'Expand/Collapse All', 'faq-for-woocommerce' ),
            array( $this, 'ffw_expand_collapse_all' ),
            'ffw_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_expand_collapse_label',
            __( 'Expand/Collapse All Label', 'faq-for-woocommerce' ),
            array( $this, 'ffw_expand_collapse_label' ),
            'ffw_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_set_role',
            __( 'Set Access Role', 'faq-for-woocommerce' ),
            array( $this, 'ffw_set_role' ),
            'ffw_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_faq_counter_in_front',
            __( 'FAQ Counter', 'faq-for-woocommerce' ),
            array( $this, 'ffw_faq_counter_in_front' ),
            'ffw_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_hide_faq_number_for_product',
            __( 'FAQ Counter in Product List', 'faq-for-woocommerce' ),
            array( $this, 'ffw_hide_faq_number_for_product' ),
            'ffw_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_hide_general_shortcode_preview',
            __( 'Hide General Shortcode Preview', 'faq-for-woocommerce' ),
            array( $this, 'ffw_hide_general_shortcode_preview' ),
            'ffw_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_hide_dynamic_shortcode_preview',
            __( 'Hide Dynamic Shortcode Preview in Product Page', 'faq-for-woocommerce' ),
            array( $this, 'ffw_hide_dynamic_shortcode_preview' ),
            'ffw_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_post_index',
            __( 'FAQ Post Behaviour', 'faq-for-woocommerce' ),
            array( $this, 'ffw_post_index' ),
            'ffw_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_before_faq',
            __( 'Before FAQ', 'faq-for-woocommerce' ),
            array( $this, 'ffw_before_faq_render' ),
            'ffw_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_after_faq',
            __( 'After FAQ', 'faq-for-woocommerce' ),
            array( $this, 'ffw_after_faq_render' ),
            'ffw_settings',
            'ffw_general_setting'
        );

        // Register FAQ Styles
        register_setting( 'ffw_style_settings', 'ffw_general_settings' );
        add_settings_section(
            'ffw_general_setting',
            __( '', 'faq-for-woocommerce' ),
            '',
            'ffw_style_settings'
        );

        add_settings_field(
            'ffw_width',
            __( 'Width (%)', 'faq-for-woocommerce' ),
            array( $this, 'ffw_width_field' ),
            'ffw_style_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_question_text_color',
            __( 'Question Text Color', 'faq-for-woocommerce' ),
            array( $this, 'ffw_question_text_color_field' ),
            'ffw_style_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_question_bg_color',
            __( 'Question Background Color', 'faq-for-woocommerce' ),
            array( $this, 'ffw_question_bg_color_field' ),
            'ffw_style_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_question_bg_secondary_color',
            __( 'Question Background Secondary Color', 'faq-for-woocommerce' ),
            array( $this, 'ffw_question_bg_secondary_color_field' ),
            'ffw_style_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_question_border_color',
            __( 'Question Border Color', 'faq-for-woocommerce' ),
            array( $this, 'ffw_question_border_color_field' ),
            'ffw_style_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_question_font_size',
            __( 'Question Font Size', 'faq-for-woocommerce' ),
            array( $this, 'ffw_question_font_size_field' ),
            'ffw_style_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_answer_bg_color',
            __( 'Answer Background Color', 'faq-for-woocommerce' ),
            array( $this, 'ffw_answer_bg_color_field' ),
            'ffw_style_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_answer_text_color',
            __( 'Answer Text Color', 'faq-for-woocommerce' ),
            array( $this, 'ffw_answer_text_color_field' ),
            'ffw_style_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_answer_border_color',
            __( 'Answer Border Color', 'faq-for-woocommerce' ),
            array( $this, 'ffw_answer_border_color_field' ),
            'ffw_style_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_custom_css',
            __( 'Custom CSS', 'faq-for-woocommerce' ),
            array( $this, 'ffw_custom_css_field' ),
            'ffw_style_settings',
            'ffw_general_setting'
        );

//        add_settings_field(
//            'ffw_answer_font_size',
//            __( 'Answer Font Size', 'faq-for-woocommerce' ),
//            array( $this, 'ffw_answer_font_size_field' ),
//            'ffw_style_settings',
//            'ffw_general_setting'
//        );

        // Register FAQ Schema Settings
        register_setting( 'ffw_schema_settings', 'ffw_general_settings' );
        add_settings_section(
            'ffw_general_setting',
            __( '', 'faq-for-woocommerce' ),
            '',
            'ffw_schema_settings'
        );

        add_settings_field(
            'ffw_disable_schema',
            __( 'Enable/Disable Schema', 'faq-for-woocommerce' ),
            array( $this, 'ffw_disable_schema' ),
            'ffw_schema_settings',
            'ffw_general_setting'
        );

        add_settings_field(
            'ffw_schema_description_type',
            __( 'Schema Description Type', 'faq-for-woocommerce' ),
            array( $this, 'ffw_schema_description_type' ),
            'ffw_schema_settings',
            'ffw_general_setting'
        );


        // Register FAQ Comments
        register_setting( 'ffw_comment_settings', 'ffw_general_settings' );
        add_settings_section(
            'ffw_comment_general_setting',
            __( '', 'faq-for-woocommerce' ),
            array($this, 'ffw_comment_general_setting_cb'),
            'ffw_comment_settings'
        );

        add_settings_field(
            'ffw_comments_on',
            __( 'Enable Comment', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_on_cb' ),
            'ffw_comment_settings',
            'ffw_comment_general_setting'
        );

        add_settings_field(
            'ffw_comments_ordering',
            __( 'Sort Comment Ordering', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_ordering_cb' ),
            'ffw_comment_settings',
            'ffw_comment_general_setting'
        );

        add_settings_field(
            'ffw_comments_avatar',
            __( 'Comment Avatar', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_avatar_cb' ),
            'ffw_comment_settings',
            'ffw_comment_general_setting'
        );

        add_settings_field(
            'ffw_comments_section_title',
            __( 'Comment Section Title', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_section_title_cb' ),
            'ffw_comment_settings',
            'ffw_comment_general_setting'
        );

        add_settings_field(
            'ffw_comments_form_title',
            __( 'Comment Form Title', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_form_title_cb' ),
            'ffw_comment_settings',
            'ffw_comment_general_setting'
        );

        add_settings_field(
            'ffw_comments_reply_form_title',
            __( 'Comment Reply Form Title', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_reply_form_title_cb' ),
            'ffw_comment_settings',
            'ffw_comment_general_setting'
        );

        add_settings_field(
            'ffw_comments_reply_button_text',
            __( 'Comment Reply Button Text', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_reply_button_text_cb' ),
            'ffw_comment_settings',
            'ffw_comment_general_setting'
        );

        add_settings_field(
            'ffw_comments_submit_button_text',
            __( 'Comment Submit Button Text', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_submit_button_text_cb' ),
            'ffw_comment_settings',
            'ffw_comment_general_setting'
        );

        add_settings_section(
            'ffw_comment_style_setting',
            __( '', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comment_style_setting_cb' ),
            'ffw_comment_settings'
        );

        add_settings_field(
            'ffw_comments_section_title_color',
            __( 'Form Section Title Color', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_section_title_color_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );


        add_settings_field(
            'ffw_comments_form_section_title_font_size',
            __( 'Form Section Title Font Size', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_form_section_title_font_size_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_avatar_style',
            __( 'Avatar Style', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_avatar_style_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_date_time_font_size',
            __( 'Comment Date Font Size', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_date_time_font_size_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_date_time_color',
            __( 'Comment Date Color', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_date_time_color_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_content_font_size',
            __( 'Comment Content Font Size', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_content_font_size_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_content_color',
            __( 'Comment Content Color', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_content_color_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_author_name_font_size',
            __( 'Author Name Font Size', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_author_name_font_size_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_author_name_color',
            __( 'Author Name Color', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_author_name_color_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_reply_button_font_size',
            __( 'Reply Button Font Size', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_reply_button_font_size_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_reply_button_text_color',
            __( 'Reply Button Text Color', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_reply_button_text_color_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_submit_button_font_size',
            __( 'Submit Button Size', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_submit_button_font_size_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_submit_button_text_color',
            __( 'Submit Button Text Color', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_submit_button_text_color_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_submit_button_bg_color',
            __( 'Submit Button Background', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_submit_button_bg_color_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_form_border_color',
            __( 'Form Border Color', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_form_border_color_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_form_title_font_size',
            __( 'Form Title Font Size', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_form_title_font_size_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_form_title_color',
            __( 'Form Title Color', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_form_title_color_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_reply_form_title_font_size',
            __( 'Reply Form Title Font Size', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_reply_form_title_font_size_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_reply_form_title_color',
            __( 'Reply Form Title Color', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_reply_form_title_color_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_reply_form_submit_button_text',
            __( 'Reply Form Submit Button Text', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_reply_form_submit_button_text_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_reply_form_submit_button_font_size',
            __( 'Reply Form Submit Button Font Size', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_reply_form_submit_button_font_size_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_reply_form_submit_button_text_color',
            __( 'Reply Form Submit Button Color', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_reply_form_submit_button_text_color_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_reply_form_submit_button_bg_color',
            __( 'Reply Form Submit Button Background', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_reply_form_submit_button_bg_color_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );

        add_settings_field(
            'ffw_comments_reply_form_border_color',
            __( 'Reply Form Border Color', 'faq-for-woocommerce' ),
            array( $this, 'ffw_comments_reply_form_border_color_cb' ),
            'ffw_comment_settings',
            'ffw_comment_style_setting'
        );
    }

    /**
     * Enable/Disable FAQ Schema.
     *
     * @since 1.2.2
     * @return
     */
    function ffw_disable_schema( ) {
        $options        = get_option( 'ffw_general_settings' );
        $options        = ! empty( $options ) ? $options : [];
        $disable_schema = array_key_exists( 'ffw_disable_schema', $options ) && ! empty($options['ffw_disable_schema']) ? $options['ffw_disable_schema'] : '';
        ?>
        <select class="widefat ffw-schema-enable-disable" name='ffw_general_settings[ffw_disable_schema]'>
            <option value="1" <?php selected( $disable_schema, 1 ); ?>>Enable</option>
            <option value="2" <?php selected( $disable_schema, 2 ); ?>>Disable</option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Schema can be disabled from the product page by selecting disable.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Schema Description Type.
     *
     * @since 1.3.18
     * @return void
     */
    function ffw_schema_description_type( ) {
        $options            = get_option( 'ffw_general_settings' );
        $options            = ! empty( $options ) ? $options : [];
        $schema_desc_type   = array_key_exists( 'ffw_schema_description_type', $options ) && ! empty($options['ffw_schema_description_type']) ? $options['ffw_schema_description_type'] : 1;
        ?>
        <select class="widefat ffw-schema-description-type" name='ffw_general_settings[ffw_schema_description_type]'>
            <option value="1" <?php selected( $schema_desc_type, 1 ); ?>><?php echo __("Description (with HTML)", "faq-for-woocommerce"); ?></option>
            <option value="2" <?php selected( $schema_desc_type, 2 ); ?>><?php echo __("Description (without HTML)", "faq-for-woocommerce"); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Select schema description type.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Width Callback.
     */
    function ffw_tab_label() {
        $val = ( isset( $this->options['ffw_tab_label'] ) ) ? $this->options['ffw_tab_label'] : '';
        echo '<input type="text" placeholder="FAQs" class="ffw-tab-label" name="ffw_general_settings[ffw_tab_label]" value="'. $val .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq tab label, it will affect in the product page description faq tab.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Tab Priority Callback.
     */
    function ffw_tab_priority() {
        $val = ( isset( $this->options['ffw_tab_priority'] ) ) ? $this->options['ffw_tab_priority'] : '50';
        echo '<input type="text" placeholder="100" class="ffw-tab-priority" name="ffw_general_settings[ffw_tab_priority]" value="'. $val .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Reorder faq tab according to its priority. Increase or decrease with 10 volume.', 'faq-for-woocommerce'));
    }


	/**
	 * Before FAQ Callback.
	 */
    function ffw_before_faq_render( ) {
        $options = get_option( 'ffw_general_settings' );
		$options = ! empty( $options ) ? $options : [];
		$before_faq = array_key_exists( 'ffw_before_faq', $options ) && ! empty($options['ffw_before_faq']) ? $options['ffw_before_faq'] : '';

		$settings  = array(
			'media_buttons' => true,
			'textarea_name' => 'ffw_general_settings[ffw_before_faq]',
		);
        wp_editor( $before_faq, 'ffw_before_faq_html', $settings );
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Before faq content will appear just before first faq accordion start.', 'faq-for-woocommerce'));
    }

	/**
	 * Layout options.
	 */
    function ffw_select_layout() {
        $options = get_option( 'ffw_general_settings' );
		$options = ! empty( $options ) ? $options : [];
		$layout = isset( $options['ffw_layout'] ) ? $options['ffw_layout'] : 1;

        ?>
        <select class="widefat ffw-layout-select" name='ffw_general_settings[ffw_layout]'>
            <option value="1" <?php selected( $layout, 1 ); ?>>Classic</option>
            <option value="2" <?php selected( $layout, 2 ); ?>>Whitish</option>
            <option value="3" <?php selected( $layout, 3 ); ?>>Trip</option>
            <option value="4" <?php selected( $layout, 4 ); ?>>Pop</option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Choose your desire layout design, currently it has four layouts.', 'faq-for-woocommerce'));
    }

	/**
	 * Role options.
	 */
    function ffw_set_role() {
        $options = get_option( 'ffw_general_settings' );
		$options = ! empty( $options ) ? $options : [];
		$role = isset( $options['ffw_set_role'] ) ? $options['ffw_set_role'] : ["administrator"];

        ?>
        <select class="widefat ffw-role-select" name='ffw_general_settings[ffw_set_role][]' multiple>
            <option value="administrator" <?php selected( true, in_array("administrator", $role) ); ?>><?php _e('Administrator', 'faq-for-woocommerce'); ?></option>
            <option value="editor" <?php selected( true, in_array("editor", $role) ); ?>><?php _e('Editor', 'faq-for-woocommerce'); ?></option>
            <option value="author" <?php selected( true, in_array("author", $role) ); ?>><?php _e('Author', 'faq-for-woocommerce'); ?></option>
            <option value="contributor" <?php selected( true, in_array("contributor", $role) ); ?>><?php _e('Contributor', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Which role of user should have access to FAQ posts?', 'faq-for-woocommerce'));
    }

	/**
	 * Show/Hide FAQs counter in front.
	 */
    function ffw_faq_counter_in_front() {
        $options = get_option( 'ffw_general_settings' );
		$options = ! empty( $options ) ? $options : [];
		$ffw_counter = isset( $options['ffw_faq_counter_in_front'] ) ? $options['ffw_faq_counter_in_front'] : "2";
        ?>
        <select class="widefat ffw-counter-in-front" name='ffw_general_settings[ffw_faq_counter_in_front]'>
            <option value="1" <?php selected( $ffw_counter, "1" ); ?>><?php _e('Show', 'faq-for-woocommerce'); ?></option>
            <option value="2" <?php selected( $ffw_counter, "2" ); ?>><?php _e('Hide', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Show/Hide FAQs counter in front faqs tab.', 'faq-for-woocommerce'));
    }

	/**
	 * Show/Hide FAQs Counter in Product List.
	 */
    function ffw_hide_faq_number_for_product() {
        $options = get_option( 'ffw_general_settings' );
		$options = ! empty( $options ) ? $options : [];
		$ffw_counter = isset( $options['ffw_hide_faq_number_for_product'] ) ? $options['ffw_hide_faq_number_for_product'] : "1";
        ?>
        <select class="widefat ffw-counter-in-product-list" name='ffw_general_settings[ffw_hide_faq_number_for_product]'>
            <option value="1" <?php selected( $ffw_counter, "1" ); ?>><?php _e('Show', 'faq-for-woocommerce'); ?></option>
            <option value="2" <?php selected( $ffw_counter, "2" ); ?>><?php _e('Hide', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Hide FAQs counter column from product list table.', 'faq-for-woocommerce'));
    }

    /**
     * Hide shortcode preview for general uses.
     */
    function ffw_hide_general_shortcode_preview() {
        $options = get_option( 'ffw_general_settings' );
        $options = ! empty( $options ) ? $options : [];
        $ffw_general_shortcode_preview = isset( $options['ffw_hide_general_shortcode_preview'] ) ? $options['ffw_hide_general_shortcode_preview'] : "1";
        ?>
        <select class="widefat ffw-hide-general-shortcode-preview" name='ffw_general_settings[ffw_hide_general_shortcode_preview]'>
            <option value="1" <?php selected( $ffw_general_shortcode_preview, "1" ); ?>><?php _e('Show', 'faq-for-woocommerce'); ?></option>
            <option value="2" <?php selected( $ffw_general_shortcode_preview, "2" ); ?>><?php _e('Hide', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Hide FAQs general shortcode like [ffw_template]', 'faq-for-woocommerce'));
    }


    /**
     * Hide dynamic shortcode preview for product/single page.
     */
    function ffw_hide_dynamic_shortcode_preview() {
        $options = get_option( 'ffw_general_settings' );
        $options = ! empty( $options ) ? $options : [];
        $ffw_dynamic_shortcode_preview = isset( $options['ffw_hide_dynamic_shortcode_preview'] ) ? $options['ffw_hide_dynamic_shortcode_preview'] : "1";
        ?>
        <select class="widefat ffw-hide-general-shortcode-preview" name='ffw_general_settings[ffw_hide_dynamic_shortcode_preview]'>
            <option value="1" <?php selected( $ffw_dynamic_shortcode_preview, "1" ); ?>><?php _e('Show', 'faq-for-woocommerce'); ?></option>
            <option value="2" <?php selected( $ffw_dynamic_shortcode_preview, "2" ); ?>><?php _e('Hide', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Hide dynamic shortcode preview in single product page.', 'faq-for-woocommerce'));
    }

	/**
	 * Display all faq answers.
	 */
    function ffw_display_all_faq_answers() {
        $options = get_option( 'ffw_general_settings' );
		$options = ! empty( $options ) ? $options : [];
		$ffw_display_all_answers = isset( $options['ffw_display_all_faq_answers'] ) ? $options['ffw_display_all_faq_answers'] : "2";
        ?>
        <select class="widefat ffw-display-all-answers" name='ffw_general_settings[ffw_display_all_faq_answers]'>
            <option value="1" <?php selected( $ffw_display_all_answers, "1" ); ?>><?php _e('Show', 'faq-for-woocommerce'); ?></option>
            <option value="2" <?php selected( $ffw_display_all_answers, "2" ); ?>><?php _e('Hide', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Display all the answers when the page loads.', 'faq-for-woocommerce'));
    }

    /**
     * Expand/Collapse All faqs.
     * 
     * @since 1.3.31
     */
    function ffw_expand_collapse_all() {
        $options = get_option( 'ffw_general_settings' );
        $options = ! empty( $options ) ? $options : [];
        $ffw_expand_collapse_all = isset( $options['ffw_expand_collapse_all'] ) ? $options['ffw_expand_collapse_all'] : "2";
        ?>
        <select class="widefat ffw-expend-collapse-label-all" name='ffw_general_settings[ffw_expand_collapse_all]'>
            <option value="1" <?php selected( $ffw_expand_collapse_all, "1" ); ?>><?php _e('Enable', 'faq-for-woocommerce'); ?></option>
            <option value="2" <?php selected( $ffw_expand_collapse_all, "2" ); ?>><?php _e('Disable', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Enable to open and close all FAQs simultaneously.', 'faq-for-woocommerce'));
    }

    /**
     * Expand/Collapse Label.
     * 
     * @since 1.3.31
     */
    function ffw_expand_collapse_label() {
        $options = get_option( 'ffw_general_settings' );
        $options = ! empty( $options ) ? $options : [];
        $ffw_expand_collapse_label = isset( $options['ffw_expand_collapse_label'] ) ? $options['ffw_expand_collapse_label'] : __( "Expand/Collapse All", 'faq-for-woocommerce' );

        echo sprintf('<input type="text" name="ffw_general_settings[ffw_expand_collapse_label]" class="ffw-expand-collapse-label" placeholder="%s" value="%s"/>', __( "Expand/Collapse All", 'faq-for-woocommerce' ), $ffw_expand_collapse_label );
        
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Set a label for expend/collapse all button.', 'faq-for-woocommerce'));
    }


    /**
     * Index/Noindexing for faq post type
     * 
     * @since 1.3.30
     */
    function ffw_post_index() {
        $options = get_option( 'ffw_general_settings' );
        $options = ! empty( $options ) ? $options : [];
        $ffw_post_index = isset( $options['ffw_post_index'] ) ? $options['ffw_post_index'] : "1";
        ?>
        <select class="widefat ffw-disable-noindex" name='ffw_general_settings[ffw_post_index]'>
            <option value="1" <?php selected( $ffw_post_index, "1" ); ?>><?php _e('Noindex', 'faq-for-woocommerce'); ?></option>
            <option value="2" <?php selected( $ffw_post_index, "2" ); ?>><?php _e('Index', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Index/Noindex FAQ posts.', 'faq-for-woocommerce'));
    }

	/**
	 * After FAQ Callback.
	 */
    function ffw_after_faq_render() {
        $options = get_option( 'ffw_general_settings' );
		$options = ! empty( $options ) ? $options : [];

		$after_faq = array_key_exists( 'ffw_after_faq', $options ) && ! empty($options['ffw_after_faq']) ? $options['ffw_after_faq'] : '';
		$settings  = array(
			'media_buttons' => true,
			'textarea_name' => 'ffw_general_settings[ffw_after_faq]',
		);
		wp_editor( $after_faq, 'ffw_after_faq_html', $settings );
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Before faq content will appear just after last faq accordion end.', 'faq-for-woocommerce'));
    }

	/**
	 * Setting Description Callback.
	 */
    function ffw_settings_section_callback() {
        echo sprintf('<p>%s</p>', esc_html__('This is where you can set faq options.The following options affect the faqs are displayed on the frontend.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Width Callback.
     */
    function ffw_width_field() {
        $val = ( isset( $this->options['ffw_width'] ) ) ? $this->options['ffw_width'] : '';
        echo '<input type="number" max="100" placeholder="100" class="ffw-width" name="ffw_general_settings[ffw_width]" value="'. $val .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Control faq wrapper width by percentage.', 'faq-for-woocommerce'));
    }

    /**
     * Custom CSS Callback.
     */
    function ffw_custom_css_field() {
        $val = ( isset( $this->options['ffw_custom_css'] ) ) ? $this->options['ffw_custom_css'] : '';
        echo '<textarea class="ffw-custom-css" name="ffw_general_settings[ffw_custom_css]" >'. $val .'</textarea>';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Custom css is the most priority css, you can customize anything.', 'faq-for-woocommerce'));
    }

    /**
     * Question Text Color Callback.
     */
    function ffw_question_text_color_field() {
        $val = ( isset( $this->options['ffw_question_text_color'] ) ) ? $this->options['ffw_question_text_color'] : '';
        echo '<input type="text" class="ffw_question_text_color" name="ffw_general_settings[ffw_question_text_color]" value="' . $val . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq question text color.', 'faq-for-woocommerce'));
    }

    /**
     * Question Background Color Callback.
     */
    function ffw_question_bg_color_field() {
        $val = ( isset( $this->options['ffw_question_bg_color'] ) ) ? $this->options['ffw_question_bg_color'] : '';
        echo '<input type="text" class="ffw_question_bg_color" name="ffw_general_settings[ffw_question_bg_color]" value="' . $val . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq question background color.', 'faq-for-woocommerce'));
    }

    /**
     * Question Background Secondary Color Callback.
     */
    function ffw_question_bg_secondary_color_field() {
        $val = ( isset( $this->options['ffw_question_bg_secondary_color'] ) ) ? $this->options['ffw_question_bg_secondary_color'] : '';
        echo '<input type="text" class="ffw_question_bg_secondary_color" name="ffw_general_settings[ffw_question_bg_secondary_color]" value="' . $val . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq question background color, this setting is for only pop layout.', 'faq-for-woocommerce'));
    }

    /**
     * Question Border Color Callback.
     */
    function ffw_question_border_color_field() {
        $val = ( isset( $this->options['ffw_question_border_color'] ) ) ? $this->options['ffw_question_border_color'] : '';
        echo '<input type="text" class="ffw_question_border_color" name="ffw_general_settings[ffw_question_border_color]" value="' . $val . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq question border color.', 'faq-for-woocommerce'));
    }

    /**
     * Question Font Size Callback.
     */
    function ffw_question_font_size_field() {
        $val = ( isset( $this->options['ffw_question_font_size'] ) ) ? $this->options['ffw_question_font_size'] : '';
        echo '<input type="range" class="ffw_question_font_size" name="ffw_general_settings[ffw_question_font_size]" min="0" max="50" step="1" value="' . $val . '" />';
        echo sprintf('<span class="ffw_question_font_size_label">%s</span>', esc_html__($val . 'px', 'faq-for-woocommerce') );
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq question font size, move the range control for increase/decrease font size.', 'faq-for-woocommerce'));
    }

    /**
     * Answer Text Color Callback.
     */
    function ffw_answer_text_color_field() {
        $val = ( isset( $this->options['ffw_answer_text_color'] ) ) ? $this->options['ffw_answer_text_color'] : '';
        echo '<input type="text" class="ffw_answer_text_color" name="ffw_general_settings[ffw_answer_text_color]" value="' . $val . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq answer text color.', 'faq-for-woocommerce'));
    }

    /**
     * Answer Background Color Callback.
     */
    function ffw_answer_bg_color_field() {
        $val = ( isset( $this->options['ffw_answer_bg_color'] ) ) ? $this->options['ffw_answer_bg_color'] : '';
        echo '<input type="text" class="ffw_answer_bg_color" name="ffw_general_settings[ffw_answer_bg_color]" value="' . $val . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq answer background color.', 'faq-for-woocommerce'));
    }

    /**
     * Answer Border Color Callback.
     */
    function ffw_answer_border_color_field() {
        $val = ( isset( $this->options['ffw_answer_border_color'] ) ) ? $this->options['ffw_answer_border_color'] : '';
        echo '<input type="text" class="ffw_answer_border_color" name="ffw_general_settings[ffw_answer_border_color]" value="' . $val . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq answer border color.', 'faq-for-woocommerce'));
    }

    /**
     * Answer Font Size Callback.
     */
    function ffw_answer_font_size_field() {
        $val = ( isset( $this->options['ffw_answer_font_size'] ) ) ? $this->options['ffw_answer_font_size'] : '';
        echo '<input type="range" class="ffw_answer_font_size" name="ffw_general_settings[ffw_answer_font_size]" min="0" max="50" step="1" value="' . $val . '" />';
        echo sprintf('<span class="ffw_answer_font_size_label">%s</span>', esc_html__($val . 'px', 'faq-for-woocommerce') );
    }

    /**
     * Comment General Section.
     *
     * @param $args array comment general section arguments
     */
    function ffw_comment_general_setting_cb($args) {
        echo sprintf("<h6>%s</h6>", __("Comment General Settings", "faq-for-woocommerce"));
    }

    /**
     * Comment options.
     */
    function ffw_comments_on_cb() {
        $options = ! empty( $this->options ) ? $this->options : [];
        $faq_comment_enable = isset( $options['ffw_comment_on'] ) && !empty($options['ffw_comment_on']) ? $options['ffw_comment_on'] : 1;

        ?>
        <select class="widefat ffw-comment-on-select" name='ffw_general_settings[ffw_comment_on]'>
            <option value="1" <?php selected( $faq_comment_enable, 1 ); ?>>Disable</option>
            <option value="2" <?php selected( $faq_comment_enable, 2 ); ?>>Enable</option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Enable/Disable FAQs comment feature.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Sort Ordering Callback.
     */
    function ffw_comments_ordering_cb() {
        $options = ! empty( $this->options ) ? $this->options : [];
        $faq_comment_ordering = isset( $options['ffw_comments_ordering'] ) && !empty($options['ffw_comments_ordering']) ? $options['ffw_comments_ordering'] : 2;

        ?>
        <select class="widefat ffw-comment-on-select" name='ffw_general_settings[ffw_comments_ordering]'>
            <option value="1" <?php selected( $faq_comment_ordering, 1 ); ?>><?php _e("Ascending", "faq-for-woocommerce"); ?></option>
            <option value="2" <?php selected( $faq_comment_ordering, 2 ); ?>><?php _e("Descending", "faq-for-woocommerce"); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change to sort the faq comments ordeirng.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Avatar Callback.
     */
    function ffw_comments_avatar_cb() {
        $options = ! empty( $this->options ) ? $this->options : [];
        $faq_comment_avatar = isset( $options['ffw_comments_avatar'] ) && !empty($options['ffw_comments_avatar']) ? $options['ffw_comments_avatar'] : 1;

        ?>
        <select class="widefat ffw-comments-avatar" name='ffw_general_settings[ffw_comments_avatar]'>
            <option value="1" <?php selected( $faq_comment_avatar, 1 ); ?>><?php _e("Show", "faq-for-woocommerce"); ?></option>
            <option value="2" <?php selected( $faq_comment_avatar, 2 ); ?>><?php _e("Hide", "faq-for-woocommerce"); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change to show/hide the faq comments avatar.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Section Title.
     */
    function ffw_comments_section_title_cb() {
        $val = ( isset( $this->options['ffw_comments_section_title'] ) && !empty($this->options['ffw_comments_section_title']) ) ? $this->options['ffw_comments_section_title'] : __("Comments", "faq-for-woocommerce");
        echo '<input type="text" placeholder="Comment section title here" class="widefat ffw-widefat ffw-comment-section-title" name="ffw_general_settings[ffw_comments_section_title]" value="'. $val .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments section title.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Form Title.
     */
    function ffw_comments_form_title_cb() {
        $val = ( isset( $this->options['ffw_comments_form_title'] ) && !empty($this->options['ffw_comments_form_title']) ) ? $this->options['ffw_comments_form_title'] : __("Comment Box", "faq-for-woocommerce");
        echo '<input type="text" placeholder="Comment form title here" class="widefat ffw-widefat ffw-comment-form-title" name="ffw_general_settings[ffw_comments_form_title]" value="'. $val .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments form title.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Form Title.
     */
    function ffw_comments_reply_form_title_cb() {
        $val = ( isset( $this->options['ffw_comments_reply_form_title'] ) && !empty($this->options['ffw_comments_reply_form_title']) ) ? $this->options['ffw_comments_reply_form_title'] : __("Write a Reply", "faq-for-woocommerce");
        echo '<input type="text" placeholder="Comment reply form title here" class="widefat ffw-widefat ffw-comment-form-title" name="ffw_general_settings[ffw_comments_reply_form_title]" value="'. $val .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments reply form title.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Button Title.
     */
    function ffw_comments_reply_button_text_cb() {
        $val = ( isset( $this->options['ffw_comments_reply_button_text'] ) && !empty($this->options['ffw_comments_reply_button_text']) ) ? $this->options['ffw_comments_reply_button_text'] : __("Reply", "faq-for-woocommerce");
        echo '<input type="text" placeholder="Comment reply button text here" class="widefat ffw-widefat ffw-comment-form-title" name="ffw_general_settings[ffw_comments_reply_button_text]" value="'. $val .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments reply button text.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Submit Button Text.
     */
    function ffw_comments_submit_button_text_cb() {
        $val = ( isset( $this->options['ffw_comments_submit_button_text'] ) && !empty($this->options['ffw_comments_submit_button_text']) ) ? $this->options['ffw_comments_submit_button_text'] : __("Comment", "faq-for-woocommerce");
        echo '<input type="text" placeholder="Comment submit button text here" class="widefat ffw-widefat ffw-comment-submit-button-text" name="ffw_general_settings[ffw_comments_submit_button_text]" value="'. $val .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments submit button text.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Form Submit Button Text.
     */
    function ffw_comments_reply_form_submit_button_text_cb() {
        $val = ( isset( $this->options['ffw_comments_reply_form_submit_button_text'] ) && !empty($this->options['ffw_comments_reply_form_submit_button_text']) ) ? $this->options['ffw_comments_reply_form_submit_button_text'] : __("Reply Comment", "faq-for-woocommerce");
        echo '<input type="text" placeholder="Comment reply form submit button text here" class="widefat ffw-widefat ffw-comment-form-title" name="ffw_general_settings[ffw_comments_reply_form_submit_button_text]" value="'. $val .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments reply form submit button text.', 'faq-for-woocommerce'));
    }


    /**
     * FAQ Avatar Style.
     */
    function ffw_comments_avatar_style_cb() {
        $options = ! empty( $this->options ) ? $this->options : [];
        $faq_comment_avatar_style = isset( $options['ffw_comments_avatar_style'] ) && !empty($options['ffw_comments_avatar_style']) ? $options['ffw_comments_avatar_style'] : 1;

        ?>
        <select class="widefat ffw-comment-avatar-style" name='ffw_general_settings[ffw_comments_avatar_style]'>
            <option value="1" <?php selected( $faq_comment_avatar_style, 1 ); ?>><?php _e("Circle", "faq-for-woocommerce"); ?></option>
            <option value="2" <?php selected( $faq_comment_avatar_style, 2 ); ?>><?php _e("Rectangle", "faq-for-woocommerce"); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change comments avatar style.', 'faq-for-woocommerce'));
    }


    /**
     * FAQ Comment Date Font Size.
     */
    function ffw_comments_date_time_font_size_cb() {
        $val = ( isset( $this->options['ffw_comments_date_time_font_size'] ) && !empty($this->options['ffw_comments_date_time_font_size']) ) ? $this->options['ffw_comments_date_time_font_size'] : '14px';
        echo '<input type="text" placeholder="Comment date font size here" class="widefat ffw-widefat ffw-comment-form-title" name="ffw_general_settings[ffw_comments_date_time_font_size]" value="'. $val .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments date-time font size.', 'faq-for-woocommerce'));
    }


    /**
     * FAQ Comment Date Time Color.
     */
    function ffw_comments_date_time_color_cb() {
        $val = ( isset( $this->options['ffw_comments_date_time_color'] ) && !empty($this->options['ffw_comments_date_time_color']) ) ? $this->options['ffw_comments_date_time_color'] : "#28303d";
        echo '<input type="text" class="ffw-comments-date-time-color" name="ffw_general_settings[ffw_comments_date_time_color]" value="' . $val . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments date color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Content Color.
     */
    function ffw_comments_content_color_cb() {
        $val = ( isset( $this->options['ffw_comments_content_color'] ) && !empty($this->options['ffw_comments_content_color']) ) ? $this->options['ffw_comments_content_color'] : "#28303d";
        echo '<input type="text" class="ffw-comments-content-color" name="ffw_general_settings[ffw_comments_content_color]" value="' . $val . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments content color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Section Title Color.
     */
    function ffw_comments_section_title_color_cb() {
        $val = ( isset( $this->options['ffw_comments_section_title_color'] ) && !empty($this->options['ffw_comments_section_title_color']) ) ? $this->options['ffw_comments_section_title_color'] : "#28303d";
        echo '<input type="text" class="ffw-comments-content-color" name="ffw_general_settings[ffw_comments_section_title_color]" value="' . $val . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments section title color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Author Name Color.
     */
    function ffw_comments_author_name_color_cb() {
        $val = ( isset( $this->options['ffw_comments_author_name_color'] ) && !empty($this->options['ffw_comments_author_name_color']) ) ? $this->options['ffw_comments_author_name_color'] : "#28303d";
        echo '<input type="text" class="ffw-comments-author-name-color" name="ffw_general_settings[ffw_comments_author_name_color]" value="' . $val . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments author name color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Button Text Color.
     */
    function ffw_comments_reply_button_text_color_cb() {
        $val = ( isset( $this->options['ffw_comments_reply_button_text_color'] ) && !empty($this->options['ffw_comments_reply_button_text_color']) ) ? $this->options['ffw_comments_reply_button_text_color'] : "#0b0beb";
        echo '<input type="text" class="ffw-comments-reply-button-text-color" name="ffw_general_settings[ffw_comments_reply_button_text_color]" value="' . $val . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments reply button text color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Submit Button Background Color.
     */
    function ffw_comments_submit_button_bg_color_cb() {
        $val = ( isset( $this->options['ffw_comments_submit_button_bg_color'] ) && !empty($this->options['ffw_comments_submit_button_bg_color']) ) ? $this->options['ffw_comments_submit_button_bg_color'] : "#008000";
        echo '<input type="text" class="ffw-comments-submit-button-bg-color" name="ffw_general_settings[ffw_comments_submit_button_bg_color]" value="' . $val . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments submit button background color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Form Border Color.
     */
    function ffw_comments_form_border_color_cb() {
        $val = ( isset( $this->options['ffw_comments_form_border_color'] ) && !empty($this->options['ffw_comments_form_border_color']) ) ? $this->options['ffw_comments_form_border_color'] : "#008000";
        echo '<input type="text" class="ffw-comments-form-border-color" name="ffw_general_settings[ffw_comments_form_border_color]" value="' . $val . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments form border color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Form Title Color.
     */
    function ffw_comments_form_title_color_cb() {
        $val = ( isset( $this->options['ffw_comments_form_title_color'] ) && !empty($this->options['ffw_comments_form_title_color']) ) ? $this->options['ffw_comments_form_title_color'] : "#28303d";
        echo '<input type="text" class="ffw-comments-form-title-color" name="ffw_general_settings[ffw_comments_form_title_color]" value="' . $val . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments form title color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Form Title Color.
     */
    function ffw_comments_reply_form_title_color_cb() {
        $val = ( isset( $this->options['ffw_comments_reply_form_title_color'] ) && !empty($this->options['ffw_comments_reply_form_title_color']) ) ? $this->options['ffw_comments_reply_form_title_color'] : "#28303d";
        echo '<input type="text" class="ffw-comments-reply-form-title-color" name="ffw_general_settings[ffw_comments_reply_form_title_color]" value="' . $val . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments reply form title color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Form Submit Button Color.
     */
    function ffw_comments_submit_button_text_color_cb() {
        $val = ( isset( $this->options['ffw_comments_submit_button_text_color'] ) && !empty($this->options['ffw_comments_submit_button_text_color']) ) ? $this->options['ffw_comments_submit_button_text_color'] : "#ffffff";
        echo '<input type="text" class="ffw-comments-submit-button-text-color" name="ffw_general_settings[ffw_comments_submit_button_text_color]" value="' . $val . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments form submit button color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Form Submit Button Color.
     */
    function ffw_comments_reply_form_submit_button_text_color_cb() {
        $val = ( isset( $this->options['ffw_comments_reply_form_submit_button_text_color'] ) && !empty($this->options['ffw_comments_reply_form_submit_button_text_color']) ) ? $this->options['ffw_comments_reply_form_submit_button_text_color'] : "#ffffff";
        echo '<input type="text" class="ffw-comments-reply-form-submit-button-text-color" name="ffw_general_settings[ffw_comments_reply_form_submit_button_text_color]" value="' . $val . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments reply form submit button text color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Form Submit Button Background Color.
     */
    function ffw_comments_reply_form_submit_button_bg_color_cb() {
        $val = ( isset( $this->options['ffw_comments_reply_form_submit_button_bg_color'] ) && !empty($this->options['ffw_comments_reply_form_submit_button_bg_color']) ) ? $this->options['ffw_comments_reply_form_submit_button_bg_color'] : "#008000";
        echo '<input type="text" class="ffw-comments-reply-form-submit-button-bg-color" name="ffw_general_settings[ffw_comments_reply_form_submit_button_bg_color]" value="' . $val . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments reply form submit button background color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Form Border Color Color.
     */
    function ffw_comments_reply_form_border_color_cb() {
        $val = ( isset( $this->options['ffw_comments_reply_form_border_color'] ) && !empty($this->options['ffw_comments_reply_form_border_color']) ) ? $this->options['ffw_comments_reply_form_border_color'] : "#008000";
        echo '<input type="text" class="ffw-comments-reply-form-border-color" name="ffw_general_settings[ffw_comments_reply_form_border_color]" value="' . $val . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments reply form border color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Content Font Size.
     */
    function ffw_comments_content_font_size_cb() {
        $val = ( isset( $this->options['ffw_comments_content_font_size'] ) && !empty($this->options['ffw_comments_content_font_size']) ) ? $this->options['ffw_comments_content_font_size'] : "18px";
        echo '<input type="text" placeholder="Comment content font size here" class="widefat ffw-widefat ffw-comment-content-font-size" name="ffw_general_settings[ffw_comments_content_font_size]" value="'. $val .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments content font size.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Author Name Font Size.
     */
    function ffw_comments_author_name_font_size_cb() {
        $val = ( isset( $this->options['ffw_comments_author_name_font_size'] ) && !empty($this->options['ffw_comments_author_name_font_size']) ) ? $this->options['ffw_comments_author_name_font_size'] : "16px";
        echo '<input type="text" placeholder="Comment author name font size here" class="widefat ffw-widefat ffw-comment-author-name-font-size" name="ffw_general_settings[ffw_comments_author_name_font_size]" value="'. $val .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments author name font size.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Button Font Size.
     */
    function ffw_comments_reply_button_font_size_cb() {
        $val = ( isset($this->options['ffw_comments_reply_button_font_size']) && !empty($this->options['ffw_comments_reply_button_font_size']) ) ? $this->options['ffw_comments_reply_button_font_size'] : "16px";
        echo '<input type="text" placeholder="Comment reply button size here" class="widefat ffw-widefat ffw-comment-reply-button-font-size" name="ffw_general_settings[ffw_comments_reply_button_font_size]" value="'. $val .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments reply button font size.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Submit Button Font Size.
     */
    function ffw_comments_submit_button_font_size_cb() {
        $val = ( isset( $this->options['ffw_comments_submit_button_font_size'] ) && !empty($this->options['ffw_comments_submit_button_font_size']) ) ? $this->options['ffw_comments_submit_button_font_size'] : "16px";
        echo '<input type="text" placeholder="Comment submit button font size here" class="widefat ffw-widefat ffw-comment-submit-button-font-size" name="ffw_general_settings[ffw_comments_submit_button_font_size]" value="'. $val .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments submit button font size.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Section Title Font Size.
     */
    function ffw_comments_form_section_title_font_size_cb() {
        $val = ( isset( $this->options['ffw_comments_form_section_title_font_size'] ) && !empty($this->options['ffw_comments_form_section_title_font_size']) ) ? $this->options['ffw_comments_form_section_title_font_size'] : "20px";
        echo '<input type="text" placeholder="Comment section header title font size here" class="widefat ffw-widefat ffw-comment-form-title-font-size" name="ffw_general_settings[ffw_comments_form_section_title_font_size]" value="'. $val .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments Section title font size.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Form Title Font Size.
     */
    function ffw_comments_form_title_font_size_cb() {
        $val = ( isset( $this->options['ffw_comments_form_title_font_size'] ) && !empty($this->options['ffw_comments_form_title_font_size']) ) ? $this->options['ffw_comments_form_title_font_size'] : "16px";
        echo '<input type="text" placeholder="Comment form title font size here" class="widefat ffw-widefat ffw-comment-form-title-font-size" name="ffw_general_settings[ffw_comments_form_title_font_size]" value="'. $val .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments form title font size.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Form Title Font Size.
     */
    function ffw_comments_reply_form_title_font_size_cb() {
        $val = ( isset( $this->options['ffw_comments_reply_form_title_font_size'] ) && !empty($this->options['ffw_comments_reply_form_title_font_size']) ) ? $this->options['ffw_comments_reply_form_title_font_size'] : "16px";
        echo '<input type="text" placeholder="Comment reply form title font size here" class="widefat ffw-widefat ffw-comment-reply-form-title-font-size" name="ffw_general_settings[ffw_comments_reply_form_title_font_size]" value="'. $val .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments reply form title font size.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Form Submit Button Font Size.
     */
    function ffw_comments_reply_form_submit_button_font_size_cb() {
        $val = ( isset( $this->options['ffw_comments_reply_form_submit_button_font_size'] ) && !empty($this->options['ffw_comments_reply_form_submit_button_font_size']) ) ? $this->options['ffw_comments_reply_form_submit_button_font_size'] : "16px";
        echo '<input type="text" placeholder="Comment reply form submit button font size here" class="widefat ffw-widefat ffw-comment-reply-form-submit-button-font-size" name="ffw_general_settings[ffw_comments_reply_form_submit_button_font_size]" value="'. $val .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments reply form submit button font size.', 'faq-for-woocommerce'));
    }

    /**
     * Comment Styles Section.
     *
     * @param $args array comment style section arguments
     */
    function ffw_comment_style_setting_cb($args) {
        echo sprintf("<h6>%s</h6>", __("Comment Style Settings", "faq-for-woocommerce"));
    }

    /**
	 * Options Page HTML.
	 */
    function ffw_options_page() {
        ?>
        <div class="wrap ffw-setting-wrap">
            <form action='options.php' method='post'>
                <div class="ffw-setting-header">
                    <?php
                        echo sprintf('<h1>%s <span class="ffw-plugin-version">v%s</span></h1>', esc_html__( "XPlainer – WooCommerce Product FAQ", "faq-for-woocommerce" ), FFW_VERSION );
                        _e( '<p>This is where you can set faq options.The following options affect the faqs are displayed on the frontend.</p>', 'faq-for-woocommerce' );
                    ?>
                </div>
                <?php $this->ffw_nav_items(); ?>
                <div class="tab-content">
                    <div class="tab-pane active" id="ffw-general">
                        <div class="ffw-setting-wrapper">
                            <div class="ffw-setting-form">
                                <?php
                                    settings_fields( 'ffw_settings' );
                                    do_settings_sections( 'ffw_settings' );
                                ?>
                            </div>

                            <div class="ffw-setting-layout" id="ffw-preview">
                                <?php
                                    echo sprintf( '<h3 class="ffw-preview-title">%s</h3>', esc_html__('Preview', 'faq-for-woocommerce') );

                                    //get layout
                                    $options = get_option( 'ffw_general_settings' );
                                    $layout = isset( $options['ffw_layout'] ) ? (int) $options['ffw_layout'] : 1;

                                    //preview images url
                                    $classic_image_url  = FFW_PLUGIN_URL . '/assets/admin/images/classic.svg';
                                    $whitish_image_url  = FFW_PLUGIN_URL . '/assets/admin/images/whitish.svg';
                                    $trip_image_url     = FFW_PLUGIN_URL . '/assets/admin/images/trip.svg';
                                    $pop_image_url      = FFW_PLUGIN_URL . '/assets/admin/images/pop.svg';

                                ?>

                                <img data-layout="1" class="<?php echo 1 === $layout ? esc_attr('ffw-visible') : 'ffw-hide'; ?>"  src="<?php echo esc_attr( $classic_image_url ); ?>" alt="Faq Classic Layout">
                                <img data-layout="2" class="<?php echo 2 === $layout ? esc_attr('ffw-visible') : 'ffw-hide'; ?>" src="<?php echo esc_attr( $whitish_image_url ); ?>" alt="Faq Whitish Layout">
                                <img data-layout="3" class="<?php echo 3 === $layout ? esc_attr('ffw-visible') : 'ffw-hide'; ?>"  src="<?php echo esc_attr( $trip_image_url ); ?>" alt="Faq Trip Layout">
                                <img data-layout="4" class="<?php echo 4 === $layout ? esc_attr('ffw-visible') : 'ffw-hide'; ?>"  src="<?php echo esc_attr( $pop_image_url ); ?>" alt="Faq Pop Layout">
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="ffw-style">
                        <div class="ffw-setting-form">
                            <?php
                                settings_fields( 'ffw_style_settings' );
                                do_settings_sections( 'ffw_style_settings' );
                            ?>
                        </div>
                    </div>
                    <div class="tab-pane" id="ffw-schema">
                        <div class="ffw-setting-form">
                            <?php
                                settings_fields( 'ffw_schema_settings' );
                                do_settings_sections( 'ffw_schema_settings' );
                            ?>
                        </div>
                    </div>
                    <div class="tab-pane" id="ffw-comment">
                        <div class="ffw-setting-form">
                            <?php
                            settings_fields( 'ffw_comment_settings' );
                            do_settings_sections( 'ffw_comment_settings' );
                            ?>
                        </div>
                    </div>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Setting Page Nav Items.
     */
    function ffw_nav_items() {

        //setting options class initialize
        $tabs = $this->ffw_get_options_tabs();
        ?>
            <nav>
                <div class="nav nav-tabs nav-tab-wrapper wflg-nav-tab-wrapper" id="nav-tab" role="tablist">
                    <?php
                    foreach ($tabs as $tab) {
                        ?>
                        <a href="#<?php echo $tab['slug']; ?>" class="<?php echo ffw_array_separator(' ', $tab['class']); ?>"
                           data-toggle="tab" role="tab" aria-selected="true"><?php echo $tab['title']; ?></a>
                        <?php
                    }
                    ?>
                </div>
                
                <div class="ffw-nav-submit">
                    <?php submit_button(); ?>
                </div>
            </nav>
        <?php
    }

    /**
     * Get setting tab items.
     *
     * @since  1.1.8
     * @return array
     */
    function ffw_get_options_tabs() {
        $settings = array(
            array(
                'title' => __( 'General', 'faq-for-woocommerce' ),
                'slug' => 'ffw-general',
                'class' => array('nav-item','nav-link', 'active')
            ),
            array(
                'title' => __( 'Style', 'faq-for-woocommerce' ),
                'slug' => 'ffw-style',
                'class' => array('nav-item', 'nav-link')
            ),
            array(
                'title' => __( 'Schema', 'faq-for-woocommerce' ),
                'slug' => 'ffw-schema',
                'class' => array('nav-item', 'nav-link')
            ),
            array(
                'title' => __( 'Comment', 'faq-for-woocommerce' ),
                'slug' => 'ffw-comment',
                'class' => array('nav-item', 'nav-link')
            ),
        );

        return apply_filters('ffw_options_tabs', $settings);
    }
}

new FAQ_Woocommerce_Settings();