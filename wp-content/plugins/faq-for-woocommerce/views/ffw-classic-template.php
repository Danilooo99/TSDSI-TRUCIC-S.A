<div class="ffw-wrapper <?php echo esc_attr($layout_name); ?>" id="ffw-wrapper">
    <div class="ffw-accordion">
		<?php
		if ( is_array($faqs) && ! empty($faqs) ) {
			foreach ( $faqs as $faq ) {
			    ?>
                <div class="ffw-accordion-item">
                    <button class="ffw-button <?php echo isset($ffw_display_all_answers) && '1' === $ffw_display_all_answers ? 'ffw-active' : '';  ?>">
                        <?php echo sprintf('<span class="ffw-question">%s</span>', esc_html__($faq['question'], 'faq-for-woocommerce') ); ?>
                        <span class="ffw-classic-icon" aria-hidden="true"></span>
                    </button>
                    <div class="ffw-classic-answer" style="display:block;">
                        <?php
                        $post_id = get_the_ID();
                        $faq_id = (int) $faq['id'];
                        ffw_show_content($faq_id);

                        //faq comment
                        ffw_comments($post_id, $faq);
                        ?>
                    </div>
                </div>
                <?php
			}
		}
		?>
    </div>
</div>

