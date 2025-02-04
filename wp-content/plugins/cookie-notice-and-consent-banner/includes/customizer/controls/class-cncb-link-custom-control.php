<?php
/**
 * Link Custom Control Class
 *
 * @package Cookie Notice & Consent Banner
 */

if ( ! class_exists( 'CNCB_Link_Custom_Control' ) ) :
	/**
	 * Link Custom Control Class
	 */
	class CNCB_Link_Custom_Control extends CNCB_Custom_Control {
		/**
		 * The type of control being rendered
		 *
		 * @var $type
		 */
		public $type = 'cncb_link_control';
		/**
		 * The units of control being rendered
		 *
		 * @var $label_units
		 */
		public $label_units = '';

		/**
		 * Render the control in the customizer
		 */
		public function render_content() {
			?>
            <div class="link_control">
                <?php if ( ! empty( $this->label ) ) { ?>
                    <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
                <?php } ?>
                <?php if ( ! empty( $this->description ) ) { ?>
                    <span class="customize-control-description"><?php echo esc_html( $this->description ); ?></span>
                <?php } ?>
                <div class="link-container">
                    <a href="#" id="cncb_step_2" class="cncb-link cncb-link-step" onclick="simulateClick()">Go to step 2</a>
                </div>
            </div>
            <?php
		}
	}
endif;
