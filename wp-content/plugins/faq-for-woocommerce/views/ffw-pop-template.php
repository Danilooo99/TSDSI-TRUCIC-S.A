<div class="ffw-wrapper ffw-pop-wrapper <?php echo esc_attr($layout_name); ?>" id="ffw-wrapper">
    <div class="ffw-pop-container">
		<?php
        $counter = 0;
		if ( is_array($faqs) && ! empty($faqs) ) {
			foreach ( $faqs as $faq ) {
				?>
                <div class="ffw-collapse ffw-accordion-item">
					<?php echo sprintf('<span class="ffw-button">%s</span>', esc_html__($faq['question'], 'faq-for-woocommerce') ); ?>
                    <div class="ffw-content ffw-classic-answer" <?php echo isset($ffw_display_all_answers) && '1' === $ffw_display_all_answers ? 'style="display:block"' : '';  ?>>
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
			$counter++;
		}
		?>
    </div>
</div>

