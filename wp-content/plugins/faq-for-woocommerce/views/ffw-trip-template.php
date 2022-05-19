<div class="ffw-wrapper ffw-trip-wrapper <?php echo esc_attr($layout_name); ?>" id="ffw-wrapper">
	<div style="position: absolute; width: 0px; height: 0px;">
		<svg xmlns="http://www.w3.org/2000/svg">
			<symbol viewBox="0 0 24 24" id="expand-more">
				<path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6z"/><path d="M0 0h24v24H0z" fill="none"/>
			</symbol>
			<symbol viewBox="0 0 24 24" id="close">
				<path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/><path d="M0 0h24v24H0z" fill="none"/>
			</symbol>
		</svg>
	</div>
	<?php
	if ( is_array($faqs) && ! empty($faqs) ) {
		foreach ( $faqs as $faq ) {
			?>
			<details <?php echo isset($ffw_display_all_answers) && '1' === $ffw_display_all_answers ? 'open' : '';  ?>>
				<summary>
					<?php echo sprintf('<span>%s</span>', esc_html__($faq['question'], 'faq-for-woocommerce') ); ?>
					<svg class="control-icon control-icon-expand" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#expand-more" /></svg>
					<svg class="control-icon control-icon-close" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#close" /></svg>
				</summary>
                <div class="ffw-trip-answer">
                    <?php
                    $post_id = get_the_ID();
                    $faq_id = (int) $faq['id'];
                    ffw_show_content($faq_id);
                    ?>
                </div>

                <?php
                    //faq comment
                    ffw_comments($post_id, $faq);
                ?>

			</details>
			<?php
		}
	}
	?>
</div>
