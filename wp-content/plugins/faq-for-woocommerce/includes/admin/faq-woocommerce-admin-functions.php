<?php
if ( ! function_exists( 'ffw_check_woocommerce' ) ) {
	function ffw_check_woocommerce() {
		return class_exists( 'WooCommerce', false );
	}
}

if ( ! function_exists( 'ffw_is_WC_supported' ) ) {
	function ffw_is_WC_supported() {
		// Ensure WC is loaded before checking version
		return ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, FFW_MIN_WC_VERSION, '>=' ) );
	}
}

/*
 * Tab
 */
add_filter('woocommerce_product_data_tabs', 'ffw_product_settings_tabs' );
function ffw_product_settings_tabs( $tabs ) {
	$tabs['faq_for_woocommerce'] = array(
		'label'    => 'FAQs',
		'target'   => 'ffw_product_data',
		'priority' => 100,
	);
	
	return $tabs;
}


/*
 * Tab content
 */
add_action( 'woocommerce_product_data_panels', 'ffw_product_panels' );
function ffw_product_panels() {
    ?>
    <div id="ffw_product_data" class="panel ffw_options_panel woocommerce_options_panel hidden">
        <?php
        //faqs list
        if ( isset($_GET['post']) && ! empty($_GET['post']) ) {
            $post_id = sanitize_text_field( wp_unslash($_GET['post']) );
            $faq_post_ids = get_post_meta($post_id, 'ffw_product_faq_post_ids', true);
            $faq_post_ids = !empty($faq_post_ids) ? $faq_post_ids : [];
            $product_faqs_data = json_encode($faq_post_ids);
            $faq_posts = ffw_get_faqs_post_list();
            ?>
                <?php include FFW_FILE_DIR . '/views/faq-woocommerce-modal-form.php'; ?>
                <div class="ffw-product-loader">
                    <div class="ffw-product-loader-overlay">
                        <span class="spinner is-active"></span>
                    </div>
                </div>
                <div class="ffw-product-form-header" id="ffw-product-form-header">
                    <?php do_action( 'before_faq_woocommerce_product_options' ); ?>
                    <div class="ffw-sortable-options-wrapper">
                        <div class="ffw-sortable-options-header">
                            <?php echo sprintf('<h3 class="ffw-option-header-title">%s</h3>', esc_html__('FAQ List', 'faq-for-woocommerce')); ?>
                            <select class="ffw_search" id="ffw_search">
                                <option value="">Select a FAQ</option>
                                <?php
                                if( $faq_posts ) {
                                    foreach($faq_posts as $post) {
                                        echo sprintf('<option value="%s">%s</option>', $post->ID, esc_html__( $post->post_title, 'faq-for-woocommerce' ));
                                    }
                                }
                                ?>

                            </select>
                            <div class="ffw-option-buttons">
                                <?php echo sprintf('<button class="ffw-add-new ffw-options-header-btn">%s</button>', esc_html__('Quick Add', 'faq-for-woocommerce')); ?>
                                <?php echo sprintf('<button class="ffw-delete-all ffw-options-header-btn" id="ffw-delete-all-faq">%s</button>', esc_html__('Delete All', 'faq-for-woocommerce')); ?>
                            </div>
                            <input type="hidden" id="ffw_products" value='<?php echo $product_faqs_data; ?>'>
                            <input type="hidden" id="ffw_product_page_id" value="<?php echo isset($_GET['post']) ? sanitize_text_field( wp_unslash($_GET['post']) ) : ''; ?>">
                        </div>
                        <div class="ffw-body">
                            <?php
                            ffw_get_option_panel_body($_GET['post']);
                            ?>
                        </div>
                    </div>

                    <?php do_action( 'after_faq_woocommerce_product_options' ); ?>
                </div>
            <?php
        }else {
            echo sprintf('<div class="ffw-product-publish-msg">%s</div>', __("Please publish the product first to insert the faqs", "faq-for-woocommerce"));
        }
        ?>
    </div>
    <?php
}

if ( ! function_exists( 'ffw_insert_new_faq' ) ) {
	add_action( 'wp_ajax_ffw_insert_new_faq', 'ffw_insert_new_faq' );
	/**
	 * Insert FAQ
     *
     * @since 1.0.0
	 */
	function ffw_insert_new_faq() {
		//check_ajax_referer( 'ffw_admin' );

		if ( ! isset( $_REQUEST['faq_data'] ) ) {
			wp_send_json_error( esc_html__( 'Invalid Request.', 'faq-for-woocommerce' ) );
			wp_die();
		}

		if ( ! isset( $_REQUEST['faq_data']['product_id'] ) ) {
			wp_send_json_error( esc_html__( 'Not in Edit page.', 'faq-for-woocommerce' ) );
			wp_die();
        }

		if( ! isset( $_REQUEST['faq_data']['question'] ) ) {
            wp_send_json_error( esc_html__( 'No Question added.', 'faq-for-woocommerce' ) );
            wp_die();
        }

        if( ! isset( $_REQUEST['faq_data']['answer'] ) ) {
            wp_send_json_error( esc_html__( 'No Answer added.', 'faq-for-woocommerce' ) );
            wp_die();
        }

		$product_id = (int) sanitize_text_field( wp_unslash($_REQUEST['faq_data']['product_id'] ));

		$new_faq_post                   = [];
        $new_faq_post['post_title']     = $_REQUEST['faq_data']['question'];
        $new_faq_post['post_content']   = $_REQUEST['faq_data']['answer'];
        $new_faq_post['post_type']      = 'ffw';
        $new_faq_post['post_status']    = 'publish';
        $inserted_post_id = wp_insert_post($new_faq_post);
        if( isset($_POST['faq_data']['faq_list']) ) {
            $faq_lists = $_POST['faq_data']['faq_list'];
        }else {
            $faq_lists = [];
        }

        array_push($faq_lists,  (string) $inserted_post_id);

		//update faq list
		$updated = update_post_meta( $product_id, 'ffw_product_faq_post_ids', $faq_lists );

		if ( $updated ) {
		    $msg = esc_html__('Update Successful!', 'faq-for-woocommerce');
        }else {
			$msg = esc_html__('Something Wrong!', 'faq-for-woocommerce');
        }

		ob_start();

		ffw_get_option_panel_body($product_id);

        $faq_body = ob_get_clean();

		if ( $updated ) {
            wp_send_json_success(
                [
                    'faq'           => $faq_body,
                    'new_post_id'   => $inserted_post_id,
                    'faq_data'      => $faq_lists,
                    'message'       => $msg,
                    'success'       => false,
                    'product_id'    => $product_id,
                ], 200
            );
            wp_die();
        } else {
            wp_send_json_error(
                [
                    'message' => esc_html__( 'No faq added.', 'faq-for-woocommerce' ),
                    'success' => false,
                ]
            );
            wp_die();
		}
	}
}

if ( ! function_exists( 'ffw_delete_all_faqs' ) ) {
	add_action( 'wp_ajax_ffw_delete_all_faqs', 'ffw_delete_all_faqs' );
	/**
	 * Delete All FAQ
     *
     * @since 1.0.0
	 */
	function ffw_delete_all_faqs() {
		//check_ajax_referer( 'ffw_admin' );

		if ( ! isset( $_REQUEST['faq_delete_data'] ) ) {
			wp_send_json_error( esc_html__( 'Invalid Request.', 'faq-for-woocommerce' ) );
			wp_die();
		}

		if ( ! isset( $_REQUEST['faq_delete_data']['product_id'] ) ) {
			wp_send_json_error( esc_html__( 'Not in Edit page.', 'faq-for-woocommerce' ) );
			wp_die();
        }

		$product_id = (int) sanitize_text_field( wp_unslash( $_REQUEST['faq_delete_data']['product_id'] ) );

		//deleted all faq list
		$deleted = delete_post_meta( $product_id, 'ffw_product_faq_post_ids' );

		if ( $deleted ) {
		    $msg = esc_html__('Deleted All FAQ Successfull!', 'faq-for-woocommerce');
        }else {
			$msg = esc_html__('Something Wrong!', 'faq-for-woocommerce');
        }


		if ( $deleted ) {
            wp_send_json_success(
                [
                    'message' => $msg,
                    'success' => true,
                ], 200
            );
            wp_die();

        } else {
            wp_send_json_error(
                [
                    'message' => esc_html__( 'No faq deleted.', 'faq-for-woocommerce' ),
                    'success' => false,
                ]
            );
            wp_die();
		}
	}
}

if ( ! function_exists( 'ffw_delete_single_faq' ) ) {
	add_action( 'wp_ajax_ffw_delete_single_faq', 'ffw_delete_single_faq' );
	/**
	 * Delete Single FAQ
	 *
	 * @since 1.0.0
	 */
	function ffw_delete_single_faq() {
		//check_ajax_referer( 'ffw_admin' );

		if ( ! isset( $_REQUEST['faq_updated_data'] ) ) {
			wp_send_json_error( esc_html__( 'Invalid Request.', 'faq-for-woocommerce' ) );
			wp_die();
		}

		if ( ! isset( $_REQUEST['faq_updated_data']['product_id'] ) ) {
			wp_send_json_error( esc_html__( 'Not in Edit page.', 'faq-for-woocommerce' ) );
			wp_die();
		}

		if ( ! isset( $_REQUEST['faq_updated_data']['updated_faq_list'] ) ) {
		    $update_faq_list = [];
		}else {
            $update_faq_list = $_REQUEST['faq_updated_data']['updated_faq_list'];
        }

		//remove empty field value from the faq list
        $update_faq_list = array_filter($update_faq_list, 'strlen');

		$product_id = (int) sanitize_text_field( wp_unslash($_REQUEST['faq_updated_data']['product_id'] ) );

		//delete current faq and update faq list
		$updated = update_post_meta( $product_id, 'ffw_product_faq_post_ids', $update_faq_list );

		if ( $updated ) {
			$msg = esc_html__('Delete Successfull!', 'faq-for-woocommerce');
			wp_send_json_success(
				[
                    'update_faq_list' => $update_faq_list,
					'message' => $msg,
					'success' => false,
				], 200
			);
			wp_die();
		}else {
			$msg = esc_html__('Something Wrong!', 'faq-for-woocommerce');
			wp_send_json_error(
				[
					'message' => esc_html__( 'No faq deleted.', 'faq-for-woocommerce' ),
					'success' => false,
				]
			);
			wp_die();
		}
	}
}

if ( ! function_exists( 'ffw_sort_faq_data' ) ) {
    add_action( 'wp_ajax_ffw_sort_faq_data', 'ffw_sort_faq_data' );
    /**
     * Sort All FAQ
     *
     * @since 1.3.0
     */
    function ffw_sort_faq_data() {
        //check_ajax_referer( 'ffw_admin' );

        if ( ! isset( $_REQUEST['faq_sorted_data'] ) ) {
            wp_send_json_error( esc_html__( 'Invalid Request.', 'faq-for-woocommerce' ) );
            wp_die();
        }

        if ( ! isset( $_REQUEST['faq_sorted_data']['product_id'] ) ) {
            wp_send_json_error( esc_html__( 'Not in Edit page.', 'faq-for-woocommerce' ) );
            wp_die();
        }

        if ( ! isset( $_REQUEST['faq_sorted_data']['faq_sorted_list'] ) ) {
            $faq_sorted_list = [];
        } else {
            $faq_sorted_list = $_REQUEST['faq_sorted_data']['faq_sorted_list'];
        }

        $product_id = (int) sanitize_text_field( wp_unslash( $_REQUEST['faq_sorted_data']['product_id'] ) );

        //updated faq sorted data
        $updated = update_post_meta( $product_id, 'ffw_product_faq_post_ids', $faq_sorted_list );

        if ( $updated ) {
            $msg = esc_html__('Updated All FAQ Successfully!', 'faq-for-woocommerce');
        }else {
            $msg = esc_html__('Something Wrong!', 'faq-for-woocommerce');
        }


        if ( $updated ) {
            wp_send_json_success(
                [
                    'message' => $msg,
                    'success' => true,
                ], 200
            );
            wp_die();

        } else {
            wp_send_json_error(
                [
                    'message' => esc_html__( 'No faq deleted.', 'faq-for-woocommerce' ),
                    'success' => false,
                ]
            );
            wp_die();
        }
    }
}

if ( ! function_exists( 'ffw_insert_data_from_search' ) ) {
    add_action( 'wp_ajax_ffw_insert_data_from_search', 'ffw_insert_data_from_search' );
    /**
     * Insert search FAQs datas
     *
     * @since 1.3.0
     */
    function ffw_insert_data_from_search() {
        //check_ajax_referer( 'ffw_admin' );

        if ( ! isset( $_REQUEST['faq_insert_search_data'] ) ) {
            wp_send_json_error( esc_html__( 'Invalid Request.', 'faq-for-woocommerce' ) );
            wp_die();
        }

        if ( ! isset( $_REQUEST['faq_insert_search_data']['product_id'] ) ) {
            wp_send_json_error( esc_html__( 'No product id.', 'faq-for-woocommerce' ) );
            wp_die();
        }

        $new_item_id = $_REQUEST['faq_insert_search_data']['new_item_id'];

        $product_id = (int) $_REQUEST['faq_insert_search_data']['product_id'];

        $old_faq_data = get_post_meta($product_id, 'ffw_product_faq_post_ids', true);
        $old_faq_data = empty($old_faq_data) ? [] : $old_faq_data;

        if( ! in_array($new_item_id, $old_faq_data) ) {

            if( isset($_REQUEST['faq_insert_search_data']['product_faqs']) ) {
                $data = $_REQUEST['faq_insert_search_data']['product_faqs'];
            }else {
                $data = [];
            }

            array_push($data, $new_item_id);

            $updated = update_post_meta($product_id, 'ffw_product_faq_post_ids', $data);

            ob_start();

            ffw_get_option_panel_body($product_id);

            $faq_body = ob_get_clean();

            if ( $updated ) {
                $msg = esc_html__('FAQ updated Successfully!', 'faq-for-woocommerce');
            }else {
                $msg = esc_html__('Something Wrong!', 'faq-for-woocommerce');
            }


            if ( $updated ) {
                wp_send_json_success(
                    [
                        'faq_body'      => $faq_body,
                        'updated_data'  => $data,
                        'message' => $msg,
                        'success' => true,
                    ], 200
                );
                wp_die();

            } else {
                wp_send_json_error(
                    [
                        'message' => esc_html__( 'No faq datas.', 'faq-for-woocommerce' ),
                        'success' => false,
                    ]
                );
                wp_die();
            }
        }


    }
}