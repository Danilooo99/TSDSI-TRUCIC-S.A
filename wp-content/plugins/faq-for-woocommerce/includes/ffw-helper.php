<?php
/**
 * Get product faqs list by product id.
 *
 * @param int $product_id product id.
 *
 * @return array
 * @since  1.1.3
 *
 */
if( ! function_exists( 'get_product_faqs' ) ) {
    function get_product_faqs( $product_id ) {
        if ( isset($product_id) && ! empty($product_id) ) {
            $faq_post_ids = get_post_meta($product_id, 'ffw_product_faq_post_ids', true);
            $faq_lists = [];

            if( isset($faq_post_ids) && is_array($faq_post_ids) ) {
                $new_faq = [];
                foreach ( $faq_post_ids as $post_id ) {
                    $new_faq['id']       = $post_id;
                    $new_faq['question'] = get_the_title($post_id);
                    $new_faq['answer']   = get_the_content(null, false, $post_id);
                    array_push($faq_lists, $new_faq);
                }
            }

            $faq_lists = ! empty($faq_lists) && is_array($faq_lists) ? $faq_lists : [];

            return apply_filters('ffw_get_product_faqs', $faq_lists);
        }
    }
}

/**
 * Get product faqs list by product category ids.
 *
 * @param array $cat_ids product category ids.
 *
 * @return array
 * @since  1.3.23
 *
 */
if( ! function_exists( 'get_product_faqs_by_cat_ids' ) ) {
    function get_product_faqs_by_cat_ids( $args ) {
        if ( isset($args['cat_ids']) && ! empty($args['cat_ids']) ) {
            $cat_ids = explode(',', $args['cat_ids']);

            $faq_post_ids = get_posts(
                array(
                    'posts_per_page' => -1,
                    'post_type' => 'ffw',
                    'fields' => 'ids',
                    'order_by' => $args['order_by'],
                    'order'    => $args['order'],
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'ffw-category',
                            'field' => 'term_id',
                            'terms' => $cat_ids,
                        )
                    )
                )
            );

            $faq_lists = [];

            if( isset($faq_post_ids) && is_array($faq_post_ids) ) {
                $new_faq = [];
                foreach ( $faq_post_ids as $post_id ) {
                    $new_faq['id']       = $post_id;
                    $new_faq['question'] = get_the_title($post_id);
                    $new_faq['answer']   = get_the_content(null, false, $post_id);
                    array_push($faq_lists, $new_faq);
                }
            }

            $faq_lists = ! empty($faq_lists) && is_array($faq_lists) ? $faq_lists : [];

            return apply_filters('ffw_get_product_faqs', $faq_lists);
        }
    }
}

/**
 * Get faq body by product id.
 *
 * @param int $post_id product id.
 * @since  1.3.0
 */
if( ! function_exists('ffw_get_option_panel_body') ) {
    function ffw_get_option_panel_body($post_id) {
        $product_faqs = get_product_faqs($post_id);
        ?>
        <div class="ffw-sortable ffw-sortable-options-body ffw-metaboxes-wrapper" id="ffw-sortable">
            <?php
            $counter = 1;
            if ( $product_faqs ) {
                foreach ( $product_faqs as $faq_item ) {
                    ?>
                    <div class="ffw-metabox-item ffw-metabox closed" id="<?php echo esc_attr($faq_item['id']); ?>" data-id="<?php echo esc_attr(absint( $faq_item['id'] )) + 1; ?>">
                        <h3>
                            <a href="#" class="ffw-single-delete-btn ffw-meta-btn" rel="<?php echo esc_attr(absint( $faq_item['id'] )); ?>">Remove</a>
                            <span class="ffw-sort-icon"></span>
                            <?php
                            echo sprintf('<strong>%s</strong>', esc_html__($faq_item['question'], 'faq-for-woocommerce') );
                            ?>
                        </h3>
                        <div class="ffw-metabox-content" style="display: none;">
                            <div class="data">
                                <?php
                                echo sprintf('<div>%s</div>', __($faq_item['answer'], 'faq-for-woocommerce') );
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    $counter++;
                }
            }
            ?>
        </div>
        <?php
    }
}

/**
 * Shortcode to show template.
 *
 * @param string $atts mixed.
 *
 * @return void
 * @since  1.1.3
 *
 */
if ( ! function_exists('ffw_show_template_shortcode') ) {
	function ffw_show_template_shortcode( $atts ) {
        $options = get_option( 'ffw_general_settings' );
		$rand_product_id = ffw_get_random_product_id_has_faq();
		$atts = shortcode_atts( array(
			'template' => 1,
			'dynamic_post' => false,
			'cat_ids' => array(),
            'order_by' => 'id',
            'order' => 'ASC',
			'id' => $rand_product_id,
		), $atts, 'ffw_template' );

		$template = (int) $atts['template'];

	    if( isset($atts['dynamic_post']) && true == $atts['dynamic_post'] ) {
	        if( isset($options["ffw_hide_dynamic_shortcode_preview"]) && 2 == $options["ffw_hide_dynamic_shortcode_preview"] ) {
	            return false;
            }

            global $post;
            $product_id = $post->ID;
        }else {

            if( isset($options["ffw_hide_general_shortcode_preview"]) && 2 == $options["ffw_hide_general_shortcode_preview"] ) {
                return false;
            }
            $product_id = (int) $atts['id'];
        }

	    if( isset($atts['cat_ids']) && !empty($atts['cat_ids']) ) {
	        $cat_ids = $atts['cat_ids'];
        }else {
	        $cat_ids = false;
        }


        $arguments = [];
        $arguments['cat_ids']   = $cat_ids;
        $arguments['order_by']  = $atts['order_by'];
        $arguments['order']     = $atts['order'];

		return ffw_get_template($template, $product_id, $arguments, true);
	}
}
add_shortcode('ffw_template', 'ffw_show_template_shortcode');

/**
 * Get template to show in front.
 *
 * @param int $layout template number
 * @param int $id product id
 *
 * @return void
 * @since  1.1.3
 *
 */
if ( ! function_exists('ffw_get_template') ) {
	function ffw_get_template( $layout, $id, $args, $is_shortcode = false ) {
		$content = '';

        $shortcode_wrap_class = $is_shortcode ? 'ffw-main-wrapper-shortcode' : '';
        if( ( isset($args['cat_ids']) && ! empty($args['cat_ids']) ) ) {
            $faqs = get_product_faqs_by_cat_ids($args);
            $display_schema_type = 'shortcode';
        }else {
            $faqs = get_product_faqs($id);
            $display_schema_type = 'product_page';
        }

        //faq schema
        new FAQ_Woocommerce_Schema($faqs, $display_schema_type);

        // Get registered option
        $options    = get_option( 'ffw_general_settings' );
        $width      = (isset($options['ffw_width']) && !empty($options['ffw_width'])) ? $options['ffw_width'] : '100';
        $ffw_display_all_answers = isset( $options['ffw_display_all_faq_answers'] ) ? $options['ffw_display_all_faq_answers'] : "2";

        //load fonts
        ?>
        <style type="text/css">
            @font-face {
                font-family: 'Nunito';
                font-weight: 900;
                src: url('<?php echo FFW_PLUGIN_URL; ?>/assets/public/fonts/Nunito-Black.ttf')  format('truetype'),
            }

            @font-face {
                font-family: 'Nunito';
                font-weight: 700;
                src: url('<?php echo FFW_PLUGIN_URL; ?>/assets/public/fonts/Nunito-Bold.ttf')  format('truetype'),
            }

            @font-face {
                font-family: 'Nunito';
                font-weight: 600;
                src: url('<?php echo FFW_PLUGIN_URL; ?>/assets/public/fonts/Nunito-SemiBold.ttf')  format('truetype'),
            }

            @font-face {
                font-family: 'Nunito';
                font-weight: 400;
                src: url('<?php echo FFW_PLUGIN_URL; ?>/assets/public/fonts/Nunito-Regular.ttf')  format('truetype'),
            }
        </style>
        <?php
        //init layout name
        $layout_name = '';
        
        // enqueue template CSS.
		if ( 1 === $layout ) {
            $layout_name = "ffw-classic-layout";
			wp_enqueue_style( 'ffw_classic_styles' );
		}elseif ( 2 === $layout ) {
            $layout_name = "ffw-whitish-layout";
			wp_enqueue_style( 'ffw_whitish_styles' );
		}elseif ( 3 === $layout ) {
            $layout_name = "ffw-trip-layout";
			wp_enqueue_style( 'ffw_trip_styles' );
		}elseif ( 4 === $layout ) {
            $layout_name = "ffw-pop-layout";
			wp_enqueue_style( 'ffw_pop_styles' );
		}

		// general styles
        wp_enqueue_style( 'ffw_public_styles' );

		ob_start();

		$content .= '<div style="width: '.$width.'%;max-width: 100%;" class="ffw-main-wrapper'. esc_attr($shortcode_wrap_class) .'" id="ffw-main-wrapper">';

		do_action('ffw_before_faq_start');

        do_action('ffw_expand_collapse_all');

		//get faq templates
		if ( 1 === $layout || 2 === $layout ) {
			include FFW_FILE_DIR . '/views/ffw-classic-template.php';
		} elseif ( 3 === $layout ) {
			include FFW_FILE_DIR . '/views/ffw-trip-template.php';
		} elseif ( 4 === $layout ) {
			include FFW_FILE_DIR . '/views/ffw-pop-template.php';
		}

		echo '<br>';

		do_action('ffw_after_faq_end');

		$content .= ob_get_clean();

		return $content . '</div>';
	}
}

/**
 * Get random product id which has faq list.
 *
 * @return int
 * @since  1.1.3
 */
if ( ! function_exists('ffw_get_random_product_id_has_faq') ) {
	function ffw_get_random_product_id_has_faq() {
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => 1,
			'fields' => 'ids',
			'meta_query' => array(
				array(
					'key' => 'ffw_product_faq_post_ids',
					'value' => '',
					'compare' => '!=',
				),
			),
			'orderby' => 'rand',
			'order' => 'ASC',
		);

		$product_ids = get_posts($args);

		return reset($product_ids);
	}
}

/**
 * Array to string separator
 *
 * @param string $separator - the separator to separate array items
 * @param array $array - array to slice and make string with separator
 *
 * @return string|boolean
 */
function ffw_array_separator( string $separator, array $array ) {

    return is_array( $array ) ? implode( $separator, $array ) : false;

}

/**
 * Generate ffw posts from older and newer datas
 *
 * @since 1.3.0
 * @author WPFeel
 */
add_action('init', 'ffw_generate_post');
function ffw_generate_post() {
    $generate   = get_option('ffw_post_generation');

    if( "complete" !== $generate ) {
        $posts  = ffw_get_faqs_product_list(true);

        if($posts) {
            foreach($posts as $product_id) {
                $faq_list = get_post_meta($product_id, 'ffw_faqs_list');
                $faq_list = reset($faq_list);
                $new_faq_post = [];
                $faq_ids_list = [];
                foreach($faq_list as $faq) {
                    $new_faq_post['post_title']     = $faq['question'];
                    $new_faq_post['post_content']   = $faq['answer'];
                    $new_faq_post['post_type']      = 'ffw';
                    $new_faq_post['post_status']    = 'publish';
                    $inserted_post_id = wp_insert_post($new_faq_post);
                    array_push($faq_ids_list, $inserted_post_id);
                }

                //insert faq post ids to it's own product
                update_post_meta($product_id, 'ffw_product_faq_post_ids', $faq_ids_list);

            }
        }
    }

    update_option("ffw_post_generation", "complete");
}

/**
 * Get faqs list
 *
 * @since 1.3.0
 */
function ffw_get_faqs_product_list($get_only_ids = false) {
    $posts = [];
    $fields = $get_only_ids ? 'ids' : 'all';

    $args = array(
        'post_type' => 'product',
        'fields'    => $fields,
        'meta_query' => array(
            array(
                'key' => 'ffw_faqs_list',
                'value' => '',
                'compare' => '!=',
            )
        )
    );

    $posts = get_posts($args);

    return apply_filters('ffw_get_faqs_product_list', $posts);
}

/**
 * Get faqs post list
 *
 * @since 1.3.0
 */
function ffw_get_faqs_post_list($get_only_ids = false) {
    $posts = [];
    $fields = $get_only_ids ? 'ids' : 'all';

    $args = array(
        'post_type' => 'ffw',
        'fields'    => $fields,
        'posts_per_page' => -1,
    );

    $posts = get_posts($args);

    return apply_filters('ffw_get_faqs_post_list', $posts);
}

/**
 * Get specific product faqs
 *
 * @since 1.3.1
 */
if( ! function_exists('ffw_get_faqs_number_for_product') ) {
    function ffw_get_faqs_number_for_product($product_id) {
        if( empty($product_id) ) return 0;

        $faq_post_ids = get_post_meta($product_id, 'ffw_product_faq_post_ids', true);
        if( !empty($faq_post_ids) && count($faq_post_ids) > 0 ) {
            $count = count($faq_post_ids);
        }else {
            $count = 0;
        }

        return apply_filters('ffw_get_faqs_number_for_product', $count);
    }
}

/**
 * Add the custom faq count column to the product post type
 *
 * @since 1.3.1
 */
if( ! function_exists('ffw_set_custom_faq_count_column') ) {
    add_filter( 'manage_product_posts_columns', 'ffw_set_custom_faq_count_column' );
    function ffw_set_custom_faq_count_column($columns) {
        $columns['faq_count'] = __( 'FAQs', 'faq-for-woocommerce' );

        return $columns;
    }
}

/**
 * Add the faq count data to the custom column for the product post type
 *
 * @since 1.3.1
 */
if( ! function_exists('ffw_custom_count_column_for_product') ) {
    add_action( 'manage_product_posts_custom_column' , 'ffw_custom_count_column_for_product', 10, 2 );
    function ffw_custom_count_column_for_product( $column, $post_id ) {
        switch ( $column ) {
            case 'faq_count' :
                $terms =  (string) ffw_get_faqs_number_for_product($post_id);
                if ( is_string( $terms ) )
                    _e( $terms, 'faq-for-woocommerce' );
                else
                    _e( '0', 'faq-for-woocommerce' );
                break;

        }
    }
}

/**
 * Checks if the specified comment is written by the author of the post commented on.
 *
 * @param object $comment Comment data.
 * @return bool
 */
function ffw_is_comment_by_post_author( $comment = null ) {

    if ( is_object( $comment ) && $comment->user_id > 0 ) {

        $user = get_userdata( $comment->user_id );
        $post = get_post( $comment->comment_post_ID );

        if ( ! empty( $user ) && ! empty( $post ) ) {

            return $comment->user_id === $post->post_author;

        }
    }
    return false;

}

/**
 * Get comment reply link.
 *
 * @param object $comment Comment data.
 * @return bool
 */
function ffw_reply_comment_link( $comment = null ) {
    $options = get_option( 'ffw_general_settings' );
    $reply_button_text = ( isset( $options['ffw_comments_reply_button_text'] ) && !empty($options['ffw_comments_reply_button_text']) ) ? $options['ffw_comments_reply_button_text'] : __("Reply", "faq-for-woocommerce");
    if ( is_object( $comment ) && $comment->user_id > 0 ) {

        $user = get_userdata( $comment->user_id );
        $post = get_post( $comment->comment_post_ID );

        if ( ! empty( $user ) && ! empty( $post ) ) {
            ?>
            <span class="ffw-comment-reply-button" data-ffw-comment-post-id="<?php echo $comment->comment_post_ID; ?>" data-ffw-reply-comment-id="<?php echo $comment->comment_ID; ?>"><?php echo $reply_button_text; ?></span>
            <?php

        }
    }
    return false;

}

/**
 * Register FFW post type called "ffw".
 *
 * @since 1.3.0
 * @author Nazrul Islam Nayan
 */
if( ! function_exists('ffw_post_init') ) {
    function ffw_post_init() {
        $labels = array(
            'name'                  => _x( 'WooCommerce FAQs', 'FAQ', 'faq-for-woocommerce' ),
            'singular_name'         => _x( 'FAQ', 'FAQ', 'faq-for-woocommerce' ),
            'menu_name'             => _x( 'Woo FAQ', 'Woo FAQ', 'faq-for-woocommerce' ),
            'name_admin_bar'        => _x( 'Woo FAQ', 'Woo FAQ', 'faq-for-woocommerce' ),
            'add_new'               => __( 'Add New', 'faq-for-woocommerce' ),
            'add_new_item'          => __( 'Add New FAQ', 'faq-for-woocommerce' ),
            'new_item'              => __( 'New FAQ', 'faq-for-woocommerce' ),
            'edit_item'             => __( 'Edit FAQ', 'faq-for-woocommerce' ),
            'view_item'             => __( 'View FAQ', 'faq-for-woocommerce' ),
            'all_items'             => __( 'All FAQS', 'faq-for-woocommerce' ),
            'search_items'          => __( 'Search FAQS', 'faq-for-woocommerce' ),
            'parent_item_colon'     => __( 'Parent FAQS:', 'faq-for-woocommerce' ),
            'not_found'             => __( 'No faqs found.', 'faq-for-woocommerce' ),
            'not_found_in_trash'    => __( 'No faqs found in Trash.', 'faq-for-woocommerce' ),
            'featured_image'        => _x( 'FAQ Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'faq-for-woocommerce' ),
            'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'faq-for-woocommerce' ),
            'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'faq-for-woocommerce' ),
            'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'faq-for-woocommerce' ),
            'archives'              => _x( 'FAQ archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'faq-for-woocommerce' ),
            'insert_into_item'      => _x( 'Insert into ffw', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'faq-for-woocommerce' ),
            'uploaded_to_this_item' => _x( 'Uploaded to this faq', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'faq-for-woocommerce' ),
            'filter_items_list'     => _x( 'Filter faqs list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'faq-for-woocommerce' ),
            'items_list_navigation' => _x( 'FAQS list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'faq-for-woocommerce' ),
            'items_list'            => _x( 'FAQS list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'faq-for-woocommerce' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'ffw' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor', 'comments' ),
            'taxonomies'         => array( ),
            'menu_icon'          => 'dashicons-editor-help'
        );

        register_post_type( 'ffw', $args );


        // Define the faq for woocommerce category taxonomy
        $args = array(
            'labels' => array(
                'name' 				=> __( 'FAQ Categories',			'faq-for-woocommerce' ),
                'singular_name' 	=> __( 'FAQ Category',				'faq-for-woocommerce' ),
                'search_items' 		=> __( 'Search FAQ Categories', 	'faq-for-woocommerce' ),
                'all_items' 		=> __( 'All FAQ Categories', 		'faq-for-woocommerce' ),
                'parent_item' 		=> __( 'Parent FAQ Category', 		'faq-for-woocommerce' ),
                'parent_item_colon' => __( 'Parent FAQ Category:', 		'faq-for-woocommerce' ),
                'edit_item' 		=> __( 'Edit FAQ Category', 		'faq-for-woocommerce' ),
                'update_item' 		=> __( 'Update FAQ Category', 		'faq-for-woocommerce' ),
                'add_new_item' 		=> __( 'Add New FAQ Category', 		'faq-for-woocommerce' ),
                'new_item_name' 	=> __( 'New FAQ Category Name', 	'faq-for-woocommerce' ),
                'menu_name' 		=> __( 'FAQ Categories', 			'faq-for-woocommerce' ),
            ),
            'public' 		=> true,
            'query_var'		=> true,
            'hierarchical' 	=> true,
            'show_in_rest' 	=> true,
        );

        $args = apply_filters( 'ffw_category_taxonomy_args', $args );

        register_taxonomy( 'ffw-category', 'ffw', $args );


        // Define the faq for woocommerce tag taxonomy
        $args = array(
            'labels' => array(
                'name' 				=> __( 'FAQ Tags',				'faq-for-woocommerce' ),
                'singular_name' 	=> __( 'FAQ Tag',				'faq-for-woocommerce' ),
                'search_items' 		=> __( 'Search FAQ Tags', 		'faq-for-woocommerce' ),
                'all_items' 		=> __( 'All FAQ Tags', 			'faq-for-woocommerce' ),
                'parent_item' 		=> __( 'Parent FAQ Tag', 		'faq-for-woocommerce' ),
                'parent_item_colon' => __( 'Parent FAQ Tag:', 		'faq-for-woocommerce' ),
                'edit_item' 		=> __( 'Edit FAQ Tag', 			'faq-for-woocommerce' ),
                'update_item' 		=> __( 'Update FAQ Tag', 		'faq-for-woocommerce' ),
                'add_new_item' 		=> __( 'Add New FAQ Tag', 		'faq-for-woocommerce' ),
                'new_item_name' 	=> __( 'New FAQ Tag Name', 		'faq-for-woocommerce' ),
                'menu_name' 		=> __( 'FAQ Tags', 				'faq-for-woocommerce' ),
            ),
            'public' 		=> true,
            'hierarchical' 	=> false,
            'show_in_rest' 	=> true,
        );

        $args = apply_filters( 'ffw_tag_taxonomy_args', $args );

        register_taxonomy( 'ffw-tag', 'ffw', $args );
    }
    add_action( 'init', 'ffw_post_init' );
}


/**
 * Show FAQ answer content by faq id
 */
if( ! function_exists('ffw_show_content') ) {
    function ffw_show_content($faq_id) {
        global $post;
        $post = get_post($faq_id);
        setup_postdata($post);
        the_content();
        wp_reset_postdata();
    }
}

function ffw_comments($post_id, $faq) {

    $options        = get_option( 'ffw_general_settings' );
    $ffw_comment_on = (isset($options['ffw_comment_on']) && !empty($options['ffw_comment_on'])) ? $options['ffw_comment_on'] : 1;

    if( isset($ffw_comment_on) && 1 === (int) $ffw_comment_on ) {
        return true;
    }

    ?>
    <div class="ffw-comment-wrapper">
        <div class="ffw-comment-header">
            <?php
            $ffw_comments_section_title = ( isset( $options['ffw_comments_section_title'] ) && !empty($options['ffw_comments_section_title']) ) ? $options['ffw_comments_section_title'] : __("Comments", "faq-for-woocommerce");
            echo sprintf("<h3 class='ffw-comment-heading'>%s</h3>", $ffw_comments_section_title);
            ?>
        </div>
        <div class="ffw-comment-box">
            <input type="hidden" class="ffw_product_id_for_comment" value="<?php echo $post_id; ?>">
            <?php do_action('ffw_comments_template', $post_id, $faq); ?>
            <?php do_action('ffw_comments_form', $post_id, $faq); ?>
        </div>
    </div>
    <?php
}

add_action('ffw_comments_template', 'ffw_comments_template', 10, 2);
function ffw_comments_template($post_id, $faq) {
    $ffw_comments = new FFW_Comments($post_id, $faq['id']);
    $ffw_comments->comments_template();
}

add_action('ffw_comments_form', 'ffw_comments_form', 10, 2);
function ffw_comments_form($post_id, $faq) {
    $commenter  = wp_get_current_commenter();
    $req        = get_option( 'require_name_email' );
    $html_req   = ( $req ? " required='required'" : '' );
    $options    = get_option( 'ffw_general_settings' );
    $form_title = ( isset( $options['ffw_comments_form_title'] ) && !empty($options['ffw_comments_form_title']) ) ? $options['ffw_comments_form_title'] : __("Comment Box", "faq-for-woocommerce");
    $submit_text = ( isset( $options['ffw_comments_submit_button_text'] ) && !empty($options['ffw_comments_submit_button_text']) ) ? $options['ffw_comments_submit_button_text'] : __("Comment", "faq-for-woocommerce");

    $fields = array(
        'author' => sprintf(
            '<p class="ffw-comment-form-author">%s</p>',
            sprintf(
                '<input id="author" name="author" placeholder="%s" type="text" value="%s" size="30" maxlength="245"%s />',
                __( 'Type your name (required)' ),
                esc_attr( $commenter['comment_author'] ),
                $html_req
            )
        ),
        'email'  => sprintf(
            '<p class="ffw-comment-form-email">%s</p>',
            sprintf(
                '<input id="email" name="email" placeholder="%s" %s value="%s" size="30" maxlength="100" aria-describedby="email-notes"%s />',
                __( 'Type your email (required)' ),
                ( 'type="email"' ),
                esc_attr( $commenter['comment_author_email'] ),
                $html_req
            )
        ),
        'url'    => sprintf(
            '<p class="ffw-comment-form-url">%s</p>',
            sprintf(
                '<input id="url" name="url" placeholder="%s" %s value="%s" size="30" maxlength="200" />',
                __( 'Type your website (required)' ),
                ( 'type="url"' ),
                esc_attr( $commenter['comment_author_url'] )
            )
        ),
    );

    $comments_args = array(
        'fields' => $fields,
        // Change the title of send button
        'label_submit' => $submit_text,
        // Change the title of the reply section
        'title_reply' => $form_title,
        // Remove "Text or HTML to be displayed after the set of comment fields".
        'comment_notes_after' => '',
        'class_container' => 'ffw-comment-respond',
        // Redefine your own textarea (the comment body).
        'comment_field' => '<input type="hidden" name="ffw_product_id_for_comment" value="' . $post_id . '"><p class="ffw-comment-form-comment"><label for="comment">' . _x( 'Comment', 'faq-for-woocommerce' ) . '</label><br /><textarea id="comment" name="comment" aria-required="true"></textarea></p>',
    );

    //comment form
    comment_form($comments_args, $faq['id']);
}

add_filter('comment_post_redirect', 'ffw_redirect_on_comment_submit', 99, 2);
function ffw_redirect_on_comment_submit($location, $comment) {
    if( isset($comment->comment_ID) && isset($_POST[ 'ffw_product_id_for_comment' ]) ) {
        $comment_id = $comment->comment_ID;
        $product_id = (int) $_POST[ 'ffw_product_id_for_comment' ];

        //merge comment meta and add post ids for current faq comment id
        $post_ids_for_faq_comment = get_comment_meta($comment_id, 'ffw_post_ids_for_comment');
        if( empty($post_ids_for_faq_comment) && !is_array($post_ids_for_faq_comment) ) {
            $post_ids_for_faq_comment = array();
        }
        array_push($post_ids_for_faq_comment, $product_id);
        $post_ids_for_faq_comment = array_unique($post_ids_for_faq_comment);
        $product_ids = reset($post_ids_for_faq_comment);
        update_comment_meta( $comment_id, 'ffw_post_ids_for_comment', $product_ids );

        //current page permalink
        $location = get_permalink($product_id);
    }

    return $location;
}

if( ! function_exists( 'ffw_strip_all_tags' ) ) {

    /*
     * Extends wp_strip_all_tags to fix WP_Error object passing issue
     *
     * @param string | WP_Error $string
     *
     * @return string
     * @since 1.3.20
     * */
    function ffw_strip_all_tags( $string ) {

        if( $string instanceof WP_Error ){
            return '';
        }

        return wp_strip_all_tags( $string );

    }

}

/**
 * Disable search engines indexing faq pages.
 *
 * @since 1.3.30
 * @param array $robots Associative array of robots directives.
 * @return array Filtered robots directives.
 */
function ffw_page_indexing( $robots ) {
    if ( is_singular( 'ffw' ) ) {
        // Get registered option
        $options = get_option( 'ffw_general_settings' );
        $ffw_post_index = isset( $options['ffw_post_index'] ) ? $options['ffw_post_index'] : "1";

        //index or noindex according to settings
        if( "1" === $ffw_post_index ) {
            $robots['noindex']  = true;
            $robots['nofollow'] = true;
        }else {
            $robots['index']    = true;
            $robots['noindex']  = false;
            $robots['nofollow'] = false;
        }
    }

    return $robots;
}
add_filter( 'wp_robots', 'ffw_page_indexing' );