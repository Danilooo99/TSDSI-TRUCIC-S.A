<?php
add_action('wp_footer', 'ffw_footer_content');
if( ! function_exists( "ffw_footer_content" ) ) {
    function ffw_footer_content() {
        $options = get_option( 'ffw_general_settings' );
        $question_text_color = isset($options['ffw_question_text_color']) && !empty($options['ffw_question_text_color']) ? $options['ffw_question_text_color'] : '';
        $question_bg_color = isset($options['ffw_question_bg_color']) && !empty($options['ffw_question_bg_color']) ? $options['ffw_question_bg_color'] : '';
        $question_bg_secondary_color = isset($options['ffw_question_bg_secondary_color']) && !empty($options['ffw_question_bg_secondary_color']) ? $options['ffw_question_bg_secondary_color'] : '';
        $question_font_size = isset($options['ffw_question_font_size']) && !empty($options['ffw_question_font_size']) ? $options['ffw_question_font_size'] . 'px' : '';
        $question_border_color = isset($options['ffw_question_border_color']) && !empty($options['ffw_question_border_color']) ? $options['ffw_question_border_color'] : '';

        //answer values
        $answer_bg_color = isset($options['ffw_answer_bg_color']) && !empty($options['ffw_answer_bg_color']) ? $options['ffw_answer_bg_color'] : '';
        $answer_text_color = isset($options['ffw_answer_text_color']) && !empty($options['ffw_answer_text_color']) ? $options['ffw_answer_text_color'] : '';
        $answer_border_color = isset($options['ffw_answer_border_color']) && !empty($options['ffw_answer_border_color']) ? $options['ffw_answer_border_color'] : '';

        ?>
        <style>
            #ffw-wrapper .ffw-accordion .ffw-accordion-item .ffw-button {
                background: <?php echo $question_bg_color; ?> !important;
                color: <?php echo $question_text_color; ?> !important;
                font-size: <?php echo $question_font_size; ?> !important;
                border: 1px solid <?php echo $question_border_color; ?> !important;
            }
            #ffw-wrapper .ffw-accordion .ffw-accordion-item .ffw-classic-answer {
                background: <?php echo $answer_bg_color; ?> !important;
                border: 1px solid <?php echo $answer_border_color; ?> !important;
                color: <?php echo $answer_text_color; ?> !important;
            }

            /* Trip template css */
            #ffw-main-wrapper details {
                background: <?php echo $question_bg_color; ?> !important;
                color: <?php echo $question_text_color; ?> !important;
                border: 1px solid <?php echo $question_border_color; ?> !important;
            }
            #ffw-main-wrapper details summary {
                font-size: <?php echo $question_font_size; ?> !important;
            }
            #ffw-main-wrapper details .ffw-trip-answer {
                background: <?php echo $answer_bg_color; ?> !important;
                color: <?php echo $answer_text_color; ?> !important;
                padding: 10px 0;

                <?php
                    if( ! empty($answer_border_color) ) {
                        echo "border: 1px solid " . $answer_border_color . " !important;";
                    }else {
                        echo "border: none;";
                    }
                ?>
            }

            #ffw-main-wrapper details .ffw-trip-answer iframe {
                display: block;
                position: relative;
                z-index: 9999;
            }

            /* Pop template css */
            #ffw-main-wrapper .ffw-collapse:nth-child(even) span{
                background: <?php echo $question_bg_color; ?> !important;
            }
            #ffw-main-wrapper .ffw-collapse:nth-child(odd) span{
                background: <?php echo $question_bg_secondary_color; ?> !important;
            }
            #ffw-main-wrapper .ffw-collapse span {
                color: <?php echo $question_text_color; ?> !important;
            }
            #ffw-main-wrapper .ffw-collapse .ffw-content {
                background: <?php echo $answer_bg_color; ?> !important;
                color: <?php echo $answer_text_color; ?> !important;
                border: 1px solid <?php echo $answer_border_color; ?> !important;
            }

        </style>
        <?php

        //print ffw footer custom css
        if( isset($options['ffw_custom_css']) ) {
            echo "<style>". $options['ffw_custom_css'] ."</style>";
        }
    }
}

add_filter( 'woocommerce_product_tabs', 'ffw_new_product_tab' );
function ffw_new_product_tab( $tabs ) {
	global $post;
	$id = $post->ID;
	$faqs = get_product_faqs($id);

    // Get registered option
    $options = get_option( 'ffw_general_settings' );
    $tab_label = isset($options['ffw_tab_label']) && !empty($options['ffw_tab_label']) ? $options['ffw_tab_label'] : __('FAQs', 'faq-for-woocommerce');

    if( isset($options['ffw_faq_counter_in_front']) && 1 === (int) $options['ffw_faq_counter_in_front'] ) {
        $counter =  (string) ffw_get_faqs_number_for_product($id);
        $tab_label = $tab_label . ' (' . $counter . ')';
    }

    $priority = ( isset( $options['ffw_tab_priority'] ) ) ? $options['ffw_tab_priority'] : '50';
    
	if ( ! empty($faqs) ) {
		// Adds the new tab
		$tabs['ffw_faqs_tab'] = array(
			'title'    => __( $tab_label, 'faq-for-woocommerce' ),
			'priority' => apply_filters('ffw_tab_priority', $priority),
			'callback' => 'ffw_new_product_tab_content',
		);
	}else {
		$tabs = [];
	}

	return $tabs;

}

function ffw_new_product_tab_content() {
	global $product;
	$id = $product->get_id();

	//get layout
	$options = get_option( 'ffw_general_settings' );
	$layout = isset( $options['ffw_layout'] ) ? (int) $options['ffw_layout'] : 1;

	echo ffw_get_template($layout, $id, false);

}

add_action('ffw_before_faq_start', 'ffw_before_faq_start');
function ffw_before_faq_start() {
    $options = get_option( 'ffw_general_settings' );

    if( isset($options['ffw_before_faq']) ) {
        _e( $options['ffw_before_faq'], 'faq-for-woocommerce' );
    }

}

add_action('ffw_expand_collapse_all', 'ffw_expand_collapse_all_action_cb');
function ffw_expand_collapse_all_action_cb() {
    $options = get_option( 'ffw_general_settings' );
    $options = ! empty( $options ) ? $options : [];
    $ffw_expand_collapse_all = isset( $options['ffw_expand_collapse_all'] ) ? $options['ffw_expand_collapse_all'] : "2";
    $ffw_expand_collapse_label = isset( $options['ffw_expand_collapse_label'] ) ? $options['ffw_expand_collapse_label'] : __( "Expand/Collapse All", 'faq-for-woocommerce' );


    if( "1" === $ffw_expand_collapse_all && !empty($ffw_expand_collapse_label) ) {
        ?>
        <button class="ffw-btn-expand-collapse-all"><?php echo $ffw_expand_collapse_label; ?></button>
        <?php
    }

}

add_action('ffw_after_faq_end', 'ffw_after_faq_end');
function ffw_after_faq_end() {
    $options = get_option( 'ffw_general_settings' );

    if( isset($options['ffw_after_faq']) ) {
        _e( $options['ffw_after_faq'], 'faq-for-woocommerce' );
    }

}

