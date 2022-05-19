<?php
/**
 * FFW_Comments setup
 *
 * @package FFW_Comments
 * @since   1.3.17
 */

defined( 'ABSPATH' ) || exit;

/**
 * FFW_Comments Class.
 *
 * @class FFW_Comments
 */

if ( ! class_exists( 'FFW_Comments', false ) ) :
    /**
     * FFW_Comments Class.
     *
     * @class FFW_Comments
     */
    class FFW_Comments {
        public $post_id;

        public $faq_id;

        public $options;

        /**
         * The single instance of the class.
         *
         * @var FFW_Comments
         * @since 1.3.17
         */
        protected static $_instance = null;

        function __construct($post_id, $faq_id) {
            $this->post_id  = $post_id;
            $this->faq_id   = $faq_id;
            $this->options  = get_option( 'ffw_general_settings' );
            add_filter('comment_post_redirect', array($this, 'ffw_redirect_on_comment_submit'), 99, 2);

        }

        /**
         * Show Comment Template
         *
         * @since 1.3.17
         */
        function comments_template() {
            $faq_comment_ordering       = isset( $this->options['ffw_comments_ordering'] ) && !empty($this->options['ffw_comments_ordering']) ? $this->options['ffw_comments_ordering'] : 2;

            //comments internal styles
            $this->comments_styles();

            //Gather comments for a specific page/post
            $comments = get_comments(array(
                'post_id' => $this->faq_id,
                'status' => 'approve', //Change this to the type of comments to be displayed
                'order' => (2 === (int) $faq_comment_ordering) ? 'DESC' : 'ASC',
                'meta_query'    => [
                    [
                        'key'   => 'ffw_post_ids_for_comment',
                        'value' => $this->post_id
                    ]
                ]
            ));

            //Display the list of comments
            wp_list_comments(array(
                'walker'      => new FFW_Walker_Comment(),
                'per_page' => 2, //Allow comment pagination
                'reverse_top_level' => false //Show the oldest comments at the top of the list
            ), $comments);

            if ( ! comments_open($this->faq_id) ) : ?>
			<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'faq-for-woocommerce' ); ?></p>
		    <?php endif;

        }

        function comments_styles() {
            $section_title_color        = ( isset( $this->options['ffw_comments_section_title_color'] ) && !empty($this->options['ffw_comments_section_title_color']) ) ? $this->options['ffw_comments_section_title_color'] : "#28303d";
            $section_title_font_size    = ( isset( $this->options['ffw_comments_form_section_title_font_size'] ) && !empty($this->options['ffw_comments_form_section_title_font_size']) ) ? $this->options['ffw_comments_form_section_title_font_size'] : "20px";

            $faq_comment_avatar = isset( $this->options['ffw_comments_avatar'] ) && !empty($this->options['ffw_comments_avatar']) ? $this->options['ffw_comments_avatar'] : 1;
            $is_avatar_display  = (1 === (int) $faq_comment_avatar) ? "block !important" : "none !important";

            $faq_comment_avatar_style   = isset( $this->options['ffw_comments_avatar_style'] ) && !empty($this->options['ffw_comments_avatar_style']) ? $this->options['ffw_comments_avatar_style'] : 1;
            $avatar_style = (1 === (int) $faq_comment_avatar_style) ? "50%" : "none";

            $content_color      = ( isset( $this->options['ffw_comments_content_color'] ) && !empty($this->options['ffw_comments_content_color']) ) ? $this->options['ffw_comments_content_color'] : "#28303d";
            $content_font_size  = ( isset( $this->options['ffw_comments_content_font_size'] ) && !empty($this->options['ffw_comments_content_font_size']) ) ? $this->options['ffw_comments_content_font_size'] : "18px";

            $author_name_font_size  = ( isset( $this->options['ffw_comments_author_name_font_size'] ) && !empty($this->options['ffw_comments_author_name_font_size']) ) ? $this->options['ffw_comments_author_name_font_size'] : "16px";
            $author_name_color      = ( isset( $this->options['ffw_comments_author_name_color'] ) && !empty($this->options['ffw_comments_author_name_color']) ) ? $this->options['ffw_comments_author_name_color'] : "#28303d";

            $date_color     = ( isset( $this->options['ffw_comments_date_time_color'] ) && !empty($this->options['ffw_comments_date_time_color']) ) ? $this->options['ffw_comments_date_time_color'] : "#28303d";
            $date_font_size = ( isset( $this->options['ffw_comments_date_time_font_size'] ) && !empty($this->options['ffw_comments_date_time_font_size']) ) ? $this->options['ffw_comments_date_time_font_size'] : '14px';

            $reply_form_title_color     = ( isset( $this->options['ffw_comments_reply_form_title_color'] ) && !empty($this->options['ffw_comments_reply_form_title_color']) ) ? $this->options['ffw_comments_reply_form_title_color'] : "#28303d";
            $reply_form_border_color    = ( isset( $this->options['ffw_comments_reply_form_border_color'] ) && !empty($this->options['ffw_comments_reply_form_border_color']) ) ? $this->options['ffw_comments_reply_form_border_color'] : "#008000";

            $reply_form_btn_text_color  = ( isset( $this->options['ffw_comments_reply_button_text_color'] ) && !empty($this->options['ffw_comments_reply_button_text_color']) ) ? $this->options['ffw_comments_reply_button_text_color'] : "#0b0beb";
            $reply_form_btn_bg_color    = ( isset( $this->options['ffw_comments_reply_form_submit_button_bg_color'] ) && !empty($this->options['ffw_comments_reply_form_submit_button_bg_color']) ) ? $this->options['ffw_comments_reply_form_submit_button_bg_color'] : "#008000";
            $reply_form_btn_font_size   = ( isset($this->options['ffw_comments_reply_button_font_size']) && !empty($this->options['ffw_comments_reply_button_font_size']) ) ? $this->options['ffw_comments_reply_button_font_size'] : "16px";


            $form_title_color       = ( isset( $this->options['ffw_comments_form_title_color'] ) && !empty($this->options['ffw_comments_form_title_color']) ) ? $this->options['ffw_comments_form_title_color'] : "#28303d";
            $form_title_font_size   = ( isset( $this->options['ffw_comments_form_title_font_size'] ) && !empty($this->options['ffw_comments_form_title_font_size']) ) ? $this->options['ffw_comments_form_title_font_size'] : "16px";
            $form_border_color      = ( isset( $this->options['ffw_comments_form_border_color'] ) && !empty($this->options['ffw_comments_form_border_color']) ) ? $this->options['ffw_comments_form_border_color'] : "#008000";

            $form_btn_text_color    = ( isset( $this->options['ffw_comments_submit_button_text_color'] ) && !empty($this->options['ffw_comments_submit_button_text_color']) ) ? $this->options['ffw_comments_submit_button_text_color'] : "#ffffff";
            $form_btn_bg_color      = ( isset( $this->options['ffw_comments_submit_button_bg_color'] ) && !empty($this->options['ffw_comments_submit_button_bg_color']) ) ? $this->options['ffw_comments_submit_button_bg_color'] : "#008000";
            $form_btn_font_size     = ( isset( $this->options['ffw_comments_submit_button_font_size'] ) && !empty($this->options['ffw_comments_submit_button_font_size']) ) ? $this->options['ffw_comments_submit_button_font_size'] : "16px";
            ?>
            <style type="text/css">
                .ffw-comment-header h3 {
                    font-size: <?php echo $section_title_font_size; ?> !important;
                    color: <?php echo $section_title_color; ?> !important;
                }

                .ffw-comment-url img {
                    display: <?php echo $is_avatar_display; ?>;
                    border-radius: <?php echo $avatar_style; ?>;
                }

                .ffw-comment-url span.fn {
                    font-size: <?php echo $author_name_font_size; ?> !important;
                    color: <?php echo $author_name_color; ?> !important;
                }

                .ffw-comment-content p {
                    color: <?php echo $content_color; ?> !important;
                    font-size: <?php echo $content_font_size; ?> !important;
                }

                .ffw-comment-metadata time {
                    color: <?php echo $date_color; ?> !important;
                    font-size: <?php echo $date_font_size; ?> !important;
                }

                .ffw-comment-respond .comment-reply-title {
                    font-size: <?php echo $form_title_font_size; ?> !important;
                    color: <?php echo $form_title_color; ?> !important;
                }

                .ffw-comment-form-comment textarea#comment {
                    border-bottom: 2px solid <?php echo $form_border_color; ?> !important;
                }

                .ffw-comment-respond .form-submit input[type=submit] {
                    color: <?php echo $form_btn_text_color; ?> !important;
                    background: <?php echo $form_btn_bg_color; ?> !important;
                    font-size: <?php echo $form_btn_font_size; ?> !important;
                    border: none !important;
                }

                .ffw-comment-reply-form label {
                    color: <?php echo $reply_form_title_color; ?> !important;
                    font-size: <?php echo $reply_form_btn_font_size; ?> !important;
                }

                .ffw-comment-reply-form textarea#comment {
                    border-bottom: 2px solid <?php echo $reply_form_border_color; ?> !important;
                }

                .ffw-comment-reply-form input[type=submit] {
                    color: <?php echo $reply_form_btn_text_color; ?> !important;
                    background: <?php echo $reply_form_btn_bg_color; ?> !important;
                    font-size: <?php echo $reply_form_btn_font_size; ?> !important;
                    border: none !important;
                }
            </style>
            <?php
        }

        /**
         * Redirect when comment is submitted
         *
         * @since 1.3.17
         */
        function ffw_redirect_on_comment_submit($location, $comment) {
            if( isset($comment->comment_post_ID) && $comment->comment_post_ID == $this->faq_id ) {
                error_log("when redirect: " . $comment->comment_post_ID);
                $location = get_permalink($this->post_id);
            }

            return $location;
        }
    }

endif;

