<div class="ffw-popup-form-wrapper" id="ffw-popup-form-wrapper">
    <div class="ffw-modal-frame">
        <div class="modal">
            <div class="modal-inset">
                <div class="button ffw-modal-close">
                    <img width="26px" src="<?php echo FFW_PLUGIN_URL . '/assets/admin/images/close.svg'; ?>" alt="FAQ Woocommerce Close">
                </div>
                <div class="modal-body">
                    <?php echo sprintf( '<h3>%s</h3>', esc_html__('FAQ Form', 'faq-for-woocommerce') ); ?>
                    <div class="modal-content">
                        <?php echo sprintf('<label>%s</label>', esc_html__('Question:', 'faq-for-woocommerce')); ?>
                        <input class="ffw-popup-form-add-question" type="text" placeholder="Enter Question here"/>

						<?php echo sprintf('<label>%s</label>', esc_html__('Answer:', 'faq-for-woocommerce')); ?>
                        <?php
						$settings  = array(
							'media_buttons' => true,
							'editor_class'  => 'ffw-popup-form-add-answer',
							'textarea_name' => 'ffw_popup_answer',
						);
						wp_editor( '', 'ffw_popup_answer', $settings );
                        ?>
                        <input type="hidden" id="ffw-current-ques-id"  current-id="">
                        <div class="modal-buttons">
                            <input type="submit" id="ffw_modal_submit" value="Submit"/>
                            <input type="submit" style="display: none;" id="ffw_modal_update" value="Update"/>
                            <input type="button" value="Cancel">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-overlay"></div>
</div>