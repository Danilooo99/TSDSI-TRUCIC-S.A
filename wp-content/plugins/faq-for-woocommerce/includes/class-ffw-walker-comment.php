<?php
/**
 * Custom comment walker for this theme.
 *
 * @package FAQ For Woocommerce
 * @since 1.3.7
 */

if ( ! class_exists( 'FFW_Walker_Comment' ) ) {
    /**
     * CUSTOM COMMENT WALKER
     */
    class FFW_Walker_Comment extends Walker_Comment {
        public $options;

        public function __construct() {
            $this->options  = get_option( 'ffw_general_settings' );
        }

        /**
         * Outputs a comment in the HTML5 format.
         *
         * @param WP_Comment $comment Comment to display.
         * @param int        $depth   Depth of the current comment.
         * @param array      $args    An array of arguments.
         */
        protected function html5_comment( $comment, $depth, $args ) {
            $reply_form_title           = ( isset( $this->options['ffw_comments_reply_form_title'] ) && !empty($this->options['ffw_comments_reply_form_title']) ) ? $this->options['ffw_comments_reply_form_title'] : __("Write a Reply", "faq-for-woocommerce");
            $reply_form_submit_btn_text = ( isset( $this->options['ffw_comments_reply_form_submit_button_text'] ) && !empty($this->options['ffw_comments_reply_form_submit_button_text']) ) ? $this->options['ffw_comments_reply_form_submit_button_text'] : __("Reply Comment", "faq-for-woocommerce");
            $tag = ( 'div' === $args['style'] ) ? 'div' : 'li';

            ?>
            <<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static output ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( $this->has_children ? 'parent' : '', $comment ); ?>>
            <article id="ffw-comment-<?php comment_ID(); ?>" class="ffw-comment-body">
                <footer class="ffw-comment-meta">
                    <div class="ffw-comment-author vcard">
                        <?php
                        $comment_author_url = get_comment_author_url( $comment );
                        $comment_author     = get_comment_author( $comment );
                        $avatar             = get_avatar( $comment, $args['avatar_size'] );
                        if ( 0 !== $args['avatar_size'] ) {
                            if ( empty( $comment_author_url ) ) {
                                echo wp_kses_post( $avatar );
                            } else {
                                printf( '<a href="%s" rel="external nofollow" class="ffw-comment-url">', $comment_author_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Escaped in https://developer.wordpress.org/reference/functions/get_comment_author_url/
                                echo wp_kses_post( $avatar );
                            }
                        }

                        printf(
                            '<span class="fn">%1$s</span><span class="screen-reader-text says">%2$s</span>',
                            esc_html( $comment_author ),
                            __( 'says:', 'faq-for-woocommerce' )
                        );

                        if ( ! empty( $comment_author_url ) ) {
                            echo '</a>';
                        }
                        ?>
                    </div><!-- .comment-author -->

                    <div class="ffw-comment-metadata">
                        <?php
                        /* translators: 1: Comment date, 2: Comment time. */
                        $comment_timestamp = sprintf( __( '%1$s at %2$s', 'faq-for-woocommerce' ), get_comment_date( '', $comment ), get_comment_time() );

                        printf(
                            '<time datetime="%s" title="%s">%s</time>',
                            esc_url( get_comment_link( $comment, $args ) ),
                            get_comment_time( 'c' ),
                            esc_attr( $comment_timestamp )
                        );

                        if ( get_edit_comment_link() ) {
                            printf(
                                ' <a class="ffw-comment-edit-link" href="%s">%s</a>',
                                esc_url( get_edit_comment_link() ),
                                __( 'Edit', 'faq-for-woocommerce' )
                            );
                        }
                        ?>
                    </div><!-- .comment-metadata -->

                </footer><!-- .comment-meta -->

                <div class="ffw-comment-content entry-content">

                    <?php

                    comment_text();

                    if ( '0' === $comment->comment_approved ) {
                        ?>
                        <p class="ffw-comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'faq-for-woocommerce' ); ?></p>
                        <?php
                    }

                    ?>

                </div><!-- .comment-content -->

                <footer class="ffw-comment-footer-meta">

                    <?php
                    ffw_reply_comment_link($comment);
                    $by_post_author = ffw_is_comment_by_post_author($comment);

                    if ( $by_post_author ) {
                        echo '<span class="ffw-by-post-author">' . __( 'By Author', 'faq-for-woocommerce' ) . '</span>';
                    }
                    ?>

                </footer>


            </article><!-- .comment-body -->
            <form action="<?php echo home_url("wp-comments-post.php"); ?>" method="post" class="ffw-comment-reply-form">
                <p class="comment-form-comment">
                    <label for="comment"><?php echo $reply_form_title; ?></label>
                    <textarea id="comment" name="comment" cols="45" rows="5" maxlength="65525" required="required" spellcheck="false"></textarea>
                </p>
                <p class="form-submit"><input name="submit" type="submit" id="submit" class="submit" value="<?php echo $reply_form_submit_btn_text; ?>">
                    <input type="hidden" name="comment_post_ID" value="<?php echo $comment->comment_post_ID; ?>" id="comment_post_ID">
                    <input type="hidden" name="comment_parent" id="comment_parent" value="<?php echo $comment->comment_ID; ?>">
                    <input type="hidden" name="ffw_product_id_for_comment" class="ffw_product_id_for_comment_reply" value="">
                </p>
            </form>
            <?php
        }
    }
}
