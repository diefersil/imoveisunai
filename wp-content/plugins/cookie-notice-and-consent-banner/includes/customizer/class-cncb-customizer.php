<?php
/**
 * Class extends WordPress customizer functionality.
 *
 * Add and manage customizer panels, sections, controls.
 *
 * @package Cookie Notice & Consent Banner
 * @since   1.0.0
 */

/* No direct access. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main customizer class
 *
 * @since 1.0.0
 */
class CNCB_Customizer {
	/**
	 * Constructor
	 *
	 * Add actions for methods that define constants, load translation and load includes.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'customize_register', array( $this, 'register_main_panel' ) );
		/* Scripts for Preview */
		add_action( 'customize_preview_init', array( $this, 'customize_preview_js' ) );
	}

	/**
	 * Register customizer section
	 *
	 * @param \WP_Customize_Manager $wp_customize The customize manager object.
	 * @since  1.0.0
	 * @access public
	 */
	public function register_main_panel( $wp_customize ) {

		$wp_customize->add_panel(
			'cncb_settings',
			array(
				'priority'       => 10,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '',
				'title'          => __( 'Cookie Notice & Consent Banner' ),
				'description'    => '',
			)
		);

		$this->register_sections( $wp_customize );
	}

	/**
	 * Register customizer section
	 *
	 * @param \WP_Customize_Manager $wp_customize The customize manager object.
	 * @since  1.0.0
	 * @access private
	 */
	private function register_sections( $wp_customize ) {
		$this->wizard_section( $wp_customize );
		$this->dimension_and_margin_section( $wp_customize );
		$this->border_and_shadow_section( $wp_customize );
		$this->animation_section( $wp_customize );
		$this->font_colors_section( $wp_customize );
		$this->accept_button_section( $wp_customize );
		$this->decline_button_section( $wp_customize );
		$this->additional_css( $wp_customize );
	}

	/**
	 * Register the Wizard Controls within Customize.
	 *
	 * @param \WP_Customize_Manager $wp_customize The customize manager object.
	 *
	 * @return void
	 */
	private function wizard_section( $wp_customize ) {
		$wp_customize->add_section(
			new CNCB_Section(
				$wp_customize,
				'cncb_wizard_section',
				array(
					'custom_title' => esc_html__( 'Step 1: Run Wizard', 'cookie-notice-and-consent-banner' ),
					'title'        => esc_html__( 'Wizard', 'cookie-notice-and-consent-banner' ),
					'priority'     => 1,
					'panel'        => 'cncb_settings',
				)
			)
		);

		/* Choose type */
		$wp_customize->add_setting(
			'cncb_widget_type',
			array(
				'default'           => 'line',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'cncb_text_sanitization',
			)
		);

		$wp_customize->add_control(
			new CNCB_Image_Radio_Button_Custom_Control(
				$wp_customize,
				'cncb_widget_type',
				array(
					'label'   => esc_attr__( 'Type', 'cookie-notice-and-consent-banner' ),
					'section' => 'cncb_wizard_section',
					'choices' => array(
						'line'  => array(
							'image' => CNCB_URI . 'img/form-line.png',
							'name'  => __( 'Line', 'cookie-notice-and-consent-banner' ),
							'class' => 'radio-button-label-2-col',
						),
						'block' => array(
							'image' => CNCB_URI . 'img/form-block.png',
							'name'  => __( 'Block', 'cookie-notice-and-consent-banner' ),
							'class' => 'radio-button-label-2-col',
						),
					),
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_position',
			array(
				'default'           => 'bottom',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'cncb_text_sanitization',
			)
		);

		$wp_customize->add_control(
			new CNCB_Image_Radio_Button_Custom_Control(
				$wp_customize,
				'cncb_position',
				array(
					'label'   => esc_html__( 'Position', 'cookie-notice-and-consent-banner' ),
					'section' => 'cncb_wizard_section',
					'choices' => array(
						'top'          => array(
							'image' => CNCB_URI . 'img/position-top.png',
							'name'  => __( 'Top', 'cookie-notice-and-consent-banner' ),
							'class' => 'radio-button-label-2-col',
						),
						'bottom'       => array(
							'image' => CNCB_URI . 'img/position-bottom.png',
							'name'  => __( 'Bottom', 'cookie-notice-and-consent-banner' ),
							'class' => 'radio-button-label-2-col',
						),
						'top-left'     => array(
							'image' => CNCB_URI . 'img/position-top-left.png',
							'name'  => __( 'Top Left', 'cookie-notice-and-consent-banner' ),
							'class' => 'radio-button-label-2-col',
						),
						'top-right'    => array(
							'image' => CNCB_URI . 'img/position-top-right.png',
							'name'  => __( 'Top Right', 'cookie-notice-and-consent-banner' ),
							'class' => 'radio-button-label-2-col',
						),
						'bottom-left'  => array(
							'image' => CNCB_URI . 'img/position-bottom-left.png',
							'name'  => __( 'Bottom Left', 'cookie-notice-and-consent-banner' ),
							'class' => 'radio-button-label-2-col',
						),
						'bottom-right' => array(
							'image' => CNCB_URI . 'img/position-bottom-right.png',
							'name'  => __( 'Bottom Right', 'cookie-notice-and-consent-banner' ),
							'class' => 'radio-button-label-2-col',
						),
						'center'       => array(
							'image' => CNCB_URI . 'img/position-center.png',
							'name'  => __( 'Center', 'cookie-notice-and-consent-banner' ),
							'class' => 'radio-button-label-2-col',
						),
					),
				)
			)
		);

		/* Show border */
		$wp_customize->add_setting(
			'cncb_show_border',
			array(
				'default'           => 0,
				'transport'         => 'postMessage',
				'sanitize_callback' => 'cncb_switch_sanitization',
			)
		);

		$wp_customize->add_control(
			new cncb_Toggle_Switch_Custom_control(
				$wp_customize,
				'cncb_show_border',
				array(
					'label'   => esc_attr__( 'Border', 'cookie-notice-and-consent-banner' ),
					'section' => 'cncb_wizard_section',
				)
			)
		);

		/* Blind the Screen */
		$wp_customize->add_setting(
			'cncb_blind_screen',
			array(
				'default'           => 0,
				'transport'         => 'postMessage',
				'sanitize_callback' => 'cncb_switch_sanitization',
			)
		);

		$wp_customize->add_control(
			new cncb_Toggle_Switch_Custom_control(
				$wp_customize,
				'cncb_blind_screen',
				array(
					'label'   => esc_attr__( 'Display overlay', 'cookie-notice-and-consent-banner' ),
					'section' => 'cncb_wizard_section',
				)
			)
		);

		/* Choose buttons */
		$wp_customize->add_setting(
			'cncb_type',
			array(
				'transport' => 'postMessage',
				'default'   => 'confirm',
			)
		);

		$wp_customize->add_control(
			'cncb_type',
			array(
				'section' => 'cncb_wizard_section',
				'label'   => esc_attr__( 'Consent or Inform', 'cookie-notice-and-consent-banner' ),
				'type'    => 'radio',
				'choices' => array(
					'confirm' => esc_html__( 'Two buttons (let visitors accept/decline cookies)', 'cookie-notice-and-consent-banner' ),
					'alert'   => esc_html__( 'One button (just inform visitors)', 'cookie-notice-and-consent-banner' ),
				),
			)
		);
		/* Choose button's type */
		$wp_customize->add_setting(
			'cncb_buttons_type',
			array(
				'default'           => 'filled-rectangle',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'cncb_text_sanitization',
			)
		);

		$wp_customize->add_control(
			new CNCB_Image_Radio_Button_Custom_Control(
				$wp_customize,
				'cncb_buttons_type',
				array(
					'label'   => esc_html__( 'Buttons Form', 'cookie-notice-and-consent-banner' ),
					'section' => 'cncb_wizard_section',
					'choices' => array(
						'filled-rectangle'       => array(
							'image' => CNCB_URI . 'img/filled-rectangle.jpg',
							'name'  => 'filled-rectangle',
                            'class' => 'radio-button-label-2-col',
						),
						'blank-rectangle' => array(
							'image' => CNCB_URI . 'img/blank-rectangle.jpg',
							'name'  => 'blank-rectangle',
                            'class' => 'radio-button-label-2-col',
						),
						'filled-rounded'  => array(
							'image' => CNCB_URI . 'img/filled-rounded.jpg',
							'name'  => 'filled-rounded',
                            'class' => 'radio-button-label-2-col',
						),
                        'blank-rounded'  => array(
                            'image' => CNCB_URI . 'img/blank-rounded.jpg',
                            'name'  => 'blank-rounded',
                            'class' => 'radio-button-label-2-col',
                        ),
                        'filled-round'  => array(
                            'image' => CNCB_URI . 'img/filled-round.jpg',
                            'name'  => 'filled-round',
                            'class' => 'radio-button-label-2-col',
                        ),
                        'blank-round'  => array(
                            'image' => CNCB_URI . 'img/blank-round.jpg',
                            'name'  => 'blank-round',
                            'class' => 'radio-button-label-2-col',
                        ),
					),
				)
			)
		);

		/* Vertical Buttons */
		$wp_customize->add_setting(
			'cncb_vertical_btn',
			array(
				'transport' => 'postMessage',
				'default'   => 0,
			)
		);

		$wp_customize->add_control(
			'cncb_vertical_btn',
			array(
				'section' => 'cncb_wizard_section',
				'label'   => esc_attr__( 'Vertical Buttons', 'cookie-notice-and-consent-banner' ),
				'type'    => 'checkbox',
				'choices' => array(
					'1' => __( 'Yes', 'cookie-notice-and-consent-banner' ),
					'0' => __( 'No', 'cookie-notice-and-consent-banner' ),
				),
			)
		);

		/* Choose Palette */
		$wp_customize->add_setting(
			'cncb_theme',
			array(
				'default'           => 'CodGrayWhite',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'cncb_text_sanitization',
			)
		);

		$wp_customize->add_control(
			new CNCB_Palette_Radio_Button_Custom_Control(
				$wp_customize,
				'cncb_theme',
				array(
					'label'   => esc_attr__( 'Palette', 'cookie-notice-and-consent-banner' ),
					'section' => 'cncb_wizard_section',
					'choices' => array(
						'CodGrayWhite'            => array(
							'colors' => array(
								'left'  => '#141414',
								'right' => '#fff',
							),
							'name'   => __( 'Cod gray white', 'cookie-notice-and-consent-banner' ),
						),
						'BigStoneTurquoise'       => array(
							'colors' => array(
								'left'  => '#122C34',
								'right' => '#44CFCB',
							),
							'name'   => __( 'Big stone turquoise', 'cookie-notice-and-consent-banner' ),
						),
						'SeaweedAtlantis'         => array(
							'colors' => array(
								'left'  => '#19220E',
								'right' => 'linear-gradient(180deg, #9BC53D 0%, #ABDB41 100%)',
							),
							'name'   => __( 'Seaweed atlantis', 'cookie-notice-and-consent-banner' ),
						),
						'CharadeJaffa'            => array(
							'colors' => array(
								'left'  => '#2B303A',
								'right' => 'linear-gradient(180deg, #D64933 0%, #F19947 100%)',
							),
							'name'   => __( 'Charade jaffa', 'cookie-notice-and-consent-banner' ),
						),
						'RhinoShakespeare'        => array(
							'colors' => array(
								'left'  => '#29335C',
								'right' => '#3E92CC',
							),
							'name'   => __( 'Rhino shakes peare', 'cookie-notice-and-consent-banner' ),
						),
						'CloudBurstGorse'         => array(
							'colors' => array(
								'left'  => '#1C2541',
								'right' => '#FFF05A',
							),
							'name'   => __( 'Cloud burst gorse', 'cookie-notice-and-consent-banner' ),
						),
						'SanJuanGold'             => array(
							'colors' => array(
								'left'  => '#324B6A',
								'right' => '#FFD600',
							),
							'name'   => __( 'San juan gold', 'cookie-notice-and-consent-banner' ),
						),
						'BlueChillCanary'         => array(
							'colors' => array(
								'left'  => '#0A8D88',
								'right' => '#D3FF55',
							),
							'name'   => __( 'Blue chill canary', 'cookie-notice-and-consent-banner' ),
						),
						'AffairBrightSun'         => array(
							'colors' => array(
								'left'  => '#723C86',
								'right' => '#FFD23F',
							),
							'name'   => __( 'Affair bright sun', 'cookie-notice-and-consent-banner' ),
						),
						'PorcelainMalibu'         => array(
							'colors' => array(
								'left'  => '#E6EDEC',
								'right' => '#6CD4FF',
							),
							'name'   => __( 'Porcelain malibu', 'cookie-notice-and-consent-banner' ),
						),
						'AliceBlueCornflowerBlue' => array(
							'colors' => array(
								'left'  => '#EEF8FF',
								'right' => '#7678ED',
							),
							'name'   => __( 'Alice blue cornflower blue', 'cookie-notice-and-consent-banner' ),
						),
						'LinkWaterChathamsBlue'   => array(
							'colors' => array(
								'left'  => '#EBF2FA',
								'right' => '#0F4C81',
							),
							'name'   => __( 'Link water chathams blue', 'cookie-notice-and-consent-banner' ),
						),
						'SazeracTuscany'          => array(
							'colors' => array(
								'left'  => '#FFF3E0',
								'right' => '#CF5C36',
							),
							'name'   => __( 'Sazerac tuscany', 'cookie-notice-and-consent-banner' ),
						),
						'CatskillWhiteAquaForest' => array(
							'colors' => array(
								'left'  => '#E9F4F3',
								'right' => '#57A773',
							),
							'name'   => __( 'Catskill white aquaForest', 'cookie-notice-and-consent-banner' ),
						),
						'WhiteMineShaft'          => array(
							'colors' => array(
								'left'  => '#FFFFFF',
								'right' => '#282828',
							),
							'name'   => __( 'White mine shaft', 'cookie-notice-and-consent-banner' ),
						),
					),
				)
			)
		);

		/* Animation */
		$wp_customize->add_setting(
			'cncb_animation',
			array(
				'transport' => 'postMessage',
				'default'   => 'no',
			)
		);
		$wp_customize->add_control(
			'cncb_animation',
			array(
				'section' => 'cncb_wizard_section',
				'label'   => esc_attr__( 'Animation', 'cookie-notice-and-consent-banner' ),
				'type'    => 'radio',
				'choices' => array(
					'no'           => esc_html__( 'No Animation', 'cookie-notice-and-consent-banner' ),
					'slide-top'    => esc_html__( 'Slide Top', 'cookie-notice-and-consent-banner' ),
					'slide-bottom' => esc_html__( 'Slide Bottom', 'cookie-notice-and-consent-banner' ),
					'slide-left'   => esc_html__( 'Slide Left', 'cookie-notice-and-consent-banner' ),
					'slide-right'  => esc_html__( 'Slide Right', 'cookie-notice-and-consent-banner' ),
					'fade'         => esc_html__( 'Fade', 'cookie-notice-and-consent-banner' ),
				),
			)
		);

        /* Text & Customize */
        $wp_customize->add_setting(
            'cncb_text',
            array(
                'transport' => 'postMessage',
                'default'   => esc_attr__( 'This website uses cookies to improve your browsing experience.', 'cookie-notice-and-consent-banner' ),
                'sanitize_callback' => 'cncb_textarea_sanitization',
            )
        );

        $wp_customize->add_control(
            'cncb_text',
            array(
                'section' => 'cncb_wizard_section',
                'label'   => esc_attr__( 'Info Text', 'cookie-notice-and-consent-banner' ),
                'type'    => 'textarea',
            )
        );

        $wp_customize->add_setting(
            'cncb_allow_text',
            array(
                'transport' => 'postMessage',
                'default'   => esc_attr__( 'ALLOW', 'cookie-notice-and-consent-banner' ),
                'sanitize_callback' => 'cncb_text_sanitization',
            )
        );

        $wp_customize->add_control(
            'cncb_allow_text',
            array(
                'section' => 'cncb_wizard_section',
                'label'   => esc_attr__( 'Allow Text', 'cookie-notice-and-consent-banner' ),
                'type'    => 'text',
            )
        );

        $wp_customize->add_setting(
            'cncb_dismiss_text',
            array(
                'transport' => 'postMessage',
                'default'   => esc_attr__( 'OK', 'cookie-notice-and-consent-banner' ),
                'sanitize_callback' => 'cncb_text_sanitization',
            )
        );

        $wp_customize->add_control(
            'cncb_dismiss_text',
            array(
                'section' => 'cncb_wizard_section',
                'label'   => esc_attr__( 'Allow Text', 'cookie-notice-and-consent-banner' ),
                'type'    => 'text',
            )
        );

        $wp_customize->add_setting(
            'cncb_decline_text',
            array(
                'transport' => 'postMessage',
                'default'   => esc_attr__( 'DECLINE', 'cookie-notice-and-consent-banner' ),
                'sanitize_callback' => 'cncb_text_sanitization',
            )
        );

        $wp_customize->add_control(
            'cncb_decline_text',
            array(
                'section' => 'cncb_wizard_section',
                'label'   => esc_attr__( 'Decline Text', 'cookie-notice-and-consent-banner' ),
                'type'    => 'text',
            )
        );

        $wp_customize->add_setting(
            'cncb_widget_link_show',
            array(
                'transport'         => 'postMessage',
                'sanitize_callback' => 'cncb_switch_sanitization',
                'default'           => 1,
            )
        );

        $wp_customize->add_control(
            new cncb_Toggle_Switch_Custom_control(
                $wp_customize,
                'cncb_widget_link_show',
                array(
                    'label'   => esc_attr__( 'Link', 'cookie-notice-and-consent-banner' ),
                    'section' => 'cncb_wizard_section',
                )
            )
        );

        $wp_customize->add_setting(
            'cncb_widget_link_text',
            array(
                'transport' => 'postMessage',
                'default'   => esc_attr__( 'GDPR', 'cookie-notice-and-consent-banner' ),
                'sanitize_callback' => 'cncb_text_sanitization',
            )
        );

        $wp_customize->add_control(
            'cncb_widget_link_text',
            array(
                'section' => 'cncb_wizard_section',
                'label'   => esc_attr__( 'Link Text', 'cookie-notice-and-consent-banner' ),
                'type'    => 'text',
            )
        );

        $wp_customize->add_setting(
            'cncb_widget_link_href',
            array(
                'transport' => 'postMessage',
                'default'   => esc_attr__( 'https://gdprinfo.eu', 'cookie-notice-and-consent-banner' ),
                'sanitize_callback' => 'cncb_text_sanitization',
            )
        );

        $wp_customize->add_control(
            'cncb_widget_link_href',
            array(
                'section' => 'cncb_wizard_section',
                'label'   => esc_attr__( 'Link Href', 'cookie-notice-and-consent-banner' ),
                'type'    => 'text',
            )
        );

        $wp_customize->add_setting(
            'cncb_widget_link_to_step'
        );

        $wp_customize->add_control(
            new CNCB_Link_Custom_Control(
                $wp_customize,
                'cncb_widget_link_to_step',
                array(
                    'label'   => esc_attr__( 'Want more customization options?', 'cookie-notice-and-consent-banner' ),
                    'section' => 'cncb_wizard_section',
                )
            )
        );
	}

	/**
	 * Register the Dimension and Margin within Customize.
	 *
	 * @param \WP_Customize_Manager $wp_customize The customize manager object.
	 *
	 * @return void
	 */
	private function dimension_and_margin_section( $wp_customize ) {
		$wp_customize->add_section(
			new CNCB_Section(
				$wp_customize,
				'cncb_style_section',
				array(
					'custom_title' => esc_attr__( 'Step 2: Customize Styles', 'cookie-notice-and-consent-banner' ),
					'title'        => esc_attr__( 'Dimensions & Space', 'cookie-notice-and-consent-banner' ),
					'priority'     => 2,
					'panel'        => 'cncb_settings',
				)
			)
		);
		$wp_customize->add_setting(
			'cncb_banner_margin',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new CNCB_Space_Custom_Control(
				$wp_customize,
				'cncb_banner_margin',
				array(
					'label_units' => esc_html__( 'PX', 'cookie-notice-and-consent-banner' ),
					'section'     => 'cncb_style_section',
					'label'       => esc_html__( 'Banner Margins', 'cookie-notice-and-consent-banner' ),
					'type'        => 'hidden',
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_banner_width',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new CNCB_Text_Custom_Control(
				$wp_customize,
				'cncb_banner_width',
				array(
					'section' => 'cncb_style_section',
					'label'   => esc_attr__( 'Banner Width', 'cookie-notice-and-consent-banner' ),
					'units'   => 'px',
					'type'    => 'number',
				)
			)
		);
	}

	/**
	 * Register the Border and Shadow Controls within Customize.
	 *
	 * @param \WP_Customize_Manager $wp_customize The customize manager object.
	 *
	 * @return void
	 */
	private function border_and_shadow_section( $wp_customize ) {
		$wp_customize->add_section(
			'cncb_border_and_shadow_section',
			array(
				'title'    => esc_attr__( 'Border & Shadows', 'cookie-notice-and-consent-banner' ),
				'priority' => 2,
				'panel'    => 'cncb_settings',
			)
		);

		$wp_customize->add_setting(
			'cncb_border_radius',
			array(
				'transport' => 'postMessage',
                'sanitize_callback' => 'cncb_text_sanitization',
			)
		);

		$wp_customize->add_control(
			new CNCB_Text_Custom_Control(
				$wp_customize,
				'cncb_border_radius',
				array(
					'section' => 'cncb_border_and_shadow_section',
					'label'   => esc_attr__( 'Border Radius', 'cookie-notice-and-consent-banner' ),
					'units'   => 'px',
					'type'    => 'text',
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_border_width',
			array(
				'transport' => 'postMessage',
                'sanitize_callback' => 'cncb_text_sanitization',
			)
		);

		$wp_customize->add_control(
			new CNCB_Text_Custom_Control(
				$wp_customize,
				'cncb_border_width',
				array(
					'section' => 'cncb_border_and_shadow_section',
					'label'   => esc_attr__( 'Border Width', 'cookie-notice-and-consent-banner' ),
					'units'   => 'px',
					'type'    => 'text',
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_border_color',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'cncb_border_color',
				array(
					'section'  => 'cncb_border_and_shadow_section',
					'settings' => 'cncb_border_color',
					'label'    => esc_attr__( 'Border Color', 'cookie-notice-and-consent-banner' ),
				)
			)
		);

		/* Blind the Screen */
		$wp_customize->add_setting(
			'cncb_shadow',
			array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'cncb_switch_sanitization',
				'default'           => 0,
			)
		);

		$wp_customize->add_control(
			new cncb_Toggle_Switch_Custom_control(
				$wp_customize,
				'cncb_shadow',
				array(
					'label'   => esc_attr__( 'Shadow', 'cookie-notice-and-consent-banner' ),
					'section' => 'cncb_border_and_shadow_section',
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_shadow_style',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new CNCB_Text_Custom_Control(
				$wp_customize,
				'cncb_shadow_style',
				array(
					'section'            => 'cncb_border_and_shadow_section',
					'custom_description' => esc_html__( 'Example: 0px 1px 10px #616161', 'cookie-notice-and-consent-banner' ),
					'full_width'         => true,
					'type'               => 'text',
				)
			)
		);
	}

	/**
	 * Register the Animation Controls within Customize.
	 *
	 * @param \WP_Customize_Manager $wp_customize The customize manager object.
	 *
	 * @return void
	 */
	private function animation_section( $wp_customize ) {
		$wp_customize->add_section(
			'cncb_animation_section',
			array(
				'title'    => esc_attr__( 'Animation', 'cookie-notice-and-consent-banner' ),
				'priority' => 3,
				'panel'    => 'cncb_settings',
			)
		);

		$wp_customize->add_setting(
			'cncb_animation_delay',
			array(
				'transport' => 'postMessage',
				'default'   => esc_attr__( '0' ),
                'sanitize_callback' => 'cncb_text_sanitization',
			)
		);

		$wp_customize->add_control(
			new CNCB_Text_Custom_Control(
				$wp_customize,
				'cncb_animation_delay',
				array(
					'section' => 'cncb_animation_section',
					'label'   => esc_attr__( 'Animation Delay', 'cookie-notice-and-consent-banner' ),
					'units'   => 'milliseconds',
					'type'    => 'text',
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_animation_duration',
			array(
				'transport' => 'postMessage',
				'default'   => esc_attr__( '600', 'cookie-notice-and-consent-banner' ),
                'sanitize_callback' => 'cncb_text_sanitization',
			)
		);

		$wp_customize->add_control(
			new CNCB_Text_Custom_Control(
				$wp_customize,
				'cncb_animation_duration',
				array(
					'section' => 'cncb_animation_section',
					'label'   => esc_attr__( 'Animation Duration', 'cookie-notice-and-consent-banner' ),
					'units'   => 'milliseconds',
					'type'    => 'text',
				)
			)
		);
	}

	/**
	 * Register the Font and Colors Controls within Customize.
	 *
	 * @param \WP_Customize_Manager $wp_customize The customize manager object.
	 *
	 * @return void
	 */
	private function font_colors_section( $wp_customize ) {
		$wp_customize->add_section(
			'cncb_font_colors_section',
			array(
				'title'    => esc_attr__( 'Font & Colors', 'cookie-notice-and-consent-banner' ),
				'priority' => 4,
				'panel'    => 'cncb_settings',
			)
		);

		$wp_customize->add_setting(
			'cncb_text_color',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'cncb_text_color',
				array(
					'section'  => 'cncb_font_colors_section',
					'settings' => 'cncb_text_color',
					'label'    => esc_attr__( 'Text Color', 'cookie-notice-and-consent-banner' ),
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_text_font_family',
			array(
				'transport' => 'postMessage',
                'sanitize_callback' => 'cncb_text_sanitization',
			)
		);

		$wp_customize->add_control(
			'cncb_text_font_family',
			array(
				'section'     => 'cncb_font_colors_section',
				'label'       => esc_attr__( 'Font Family', 'cookie-notice-and-consent-banner' ),
				'description' => esc_html__( 'Example: Arial', 'cookie-notice-and-consent-banner' ),
				'type'        => 'text',
			)
		);

		$wp_customize->add_setting(
			'cncb_link_color',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'cncb_link_color',
				array(
					'section'  => 'cncb_font_colors_section',
					'settings' => 'cncb_link_color',
					'label'    => esc_attr__( 'Link Color', 'cookie-notice-and-consent-banner' ),
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_link_hover_color',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'cncb_link_hover_color',
				array(
					'section'  => 'cncb_font_colors_section',
					'settings' => 'cncb_link_hover_color',
					'label'    => esc_attr__( 'Link Color (on hover)', 'cookie-notice-and-consent-banner' ),
				)
			)
		);
	}

	/**
	 * Register the Accept button Controls within Customize.
	 *
	 * @param \WP_Customize_Manager $wp_customize The customize manager object.
	 *
	 * @return void
	 */
	private function accept_button_section( $wp_customize ) {
		$wp_customize->add_section(
			'cncb_accept_button_section',
			array(
				'title'    => esc_attr__( 'Accept Button', 'cookie-notice-and-consent-banner' ),
				'priority' => 5,
				'panel'    => 'cncb_settings',
			)
		);

		$wp_customize->add_setting(
			'cncb_ab_text_color',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'cncb_ab_text_color',
				array(
					'section'  => 'cncb_accept_button_section',
					'settings' => 'cncb_ab_text_color',
					'label'    => esc_attr__( 'Text Color', 'cookie-notice-and-consent-banner' ),
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_ab_font_family',
			array(
				'transport' => 'postMessage',
                'sanitize_callback' => 'cncb_text_sanitization',
			)
		);

		$wp_customize->add_control(
			'cncb_ab_font_family',
			array(
				'section'     => 'cncb_accept_button_section',
				'label'       => esc_attr__( 'Font Family', 'cookie-notice-and-consent-banner' ),
				'description' => esc_html__( 'Example: Arial', 'cookie-notice-and-consent-banner' ),
				'type'        => 'text',
			)
		);

		$wp_customize->add_setting(
			'cncb_ab_border_radius',
			array(
				'transport' => 'postMessage',
                'sanitize_callback' => 'cncb_text_sanitization',
			)
		);

		$wp_customize->add_control(
			new CNCB_Text_Custom_Control(
				$wp_customize,
				'cncb_ab_border_radius',
				array(
					'section' => 'cncb_accept_button_section',
					'label'   => esc_attr__( 'Border Radius', 'cookie-notice-and-consent-banner' ),
					'units'   => 'px',
					'type'    => 'text',
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_ab_border_width',
			array(
				'transport' => 'postMessage',
                'sanitize_callback' => 'cncb_text_sanitization',
			)
		);

		$wp_customize->add_control(
			new CNCB_Text_Custom_Control(
				$wp_customize,
				'cncb_ab_border_width',
				array(
					'section' => 'cncb_accept_button_section',
					'label'   => esc_attr__( 'Border Width', 'cookie-notice-and-consent-banner' ),
					'units'   => 'px',
					'type'    => 'text',
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_ab_border_color',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'cncb_ab_border_color',
				array(
					'section'  => 'cncb_accept_button_section',
					'settings' => 'cncb_ab_border_color',
					'label'    => esc_attr__( 'Border Color', 'cookie-notice-and-consent-banner' ),
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_ab_shadow',
			array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'cncb_switch_sanitization',
				'default'           => 0,
			)
		);

		$wp_customize->add_control(
			new cncb_Toggle_Switch_Custom_control(
				$wp_customize,
				'cncb_ab_shadow',
				array(
					'label'   => esc_attr__( 'Shadow', 'cookie-notice-and-consent-banner' ),
					'section' => 'cncb_accept_button_section',
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_ab_shadow_style',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new CNCB_Text_Custom_Control(
				$wp_customize,
				'cncb_ab_shadow_style',
				array(
					'section'            => 'cncb_accept_button_section',
					'custom_description' => esc_html__( 'Example: 0px 1px 10px #616161', 'cookie-notice-and-consent-banner' ),
					'full_width'         => true,
					'type'               => 'text',
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_ab_bg_color',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'cncb_ab_bg_color',
				array(
					'section'  => 'cncb_accept_button_section',
					'settings' => 'cncb_ab_bg_color',
					'label'    => esc_attr__( 'Background Color', 'cookie-notice-and-consent-banner' ),
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_ab_gradient',
			array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'cncb_switch_sanitization',
				'default'           => 0,
			)
		);

		$wp_customize->add_control(
			new cncb_Toggle_Switch_Custom_control(
				$wp_customize,
				'cncb_ab_gradient',
				array(
					'label'   => esc_attr__( 'Gradient', 'cookie-notice-and-consent-banner' ),
					'section' => 'cncb_accept_button_section',
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_ab_gradient_style',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new CNCB_Text_Custom_Control(
				$wp_customize,
				'cncb_ab_gradient_style',
				array(
					'section'            => 'cncb_accept_button_section',
					'custom_description' => esc_html__( 'Example: linear-gradient(#e66465, #9198e5)', 'cookie-notice-and-consent-banner' ),
					'full_width'         => true,
					'type'               => 'text',
				)
			)
		);

		/* Hover settings */
		$wp_customize->add_setting(
			'cncb_ab_hover_text_color',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'cncb_ab_hover_text_color',
				array(
					'section'  => 'cncb_accept_button_section',
					'settings' => 'cncb_ab_hover_text_color',
					'label'    => esc_attr__( 'Text Color (on hover)', 'cookie-notice-and-consent-banner' ),
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_ab_hover_bg_color',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'cncb_ab_hover_bg_color',
				array(
					'section'  => 'cncb_accept_button_section',
					'settings' => 'cncb_ab_hover_bg_color',
					'label'    => esc_attr__( 'Background Color (on hover)', 'cookie-notice-and-consent-banner' ),
				)
			)
		);
		$wp_customize->add_setting(
			'cncb_ab_hover_gradient',
			array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'cncb_switch_sanitization',
				'default'           => 0,
			)
		);

		$wp_customize->add_control(
			new cncb_Toggle_Switch_Custom_control(
				$wp_customize,
				'cncb_ab_hover_gradient',
				array(
					'label'   => esc_attr__( 'Gradient (on hover)', 'cookie-notice-and-consent-banner' ),
					'section' => 'cncb_accept_button_section',
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_ab_hover_gradient_style',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new CNCB_Text_Custom_Control(
				$wp_customize,
				'cncb_ab_hover_gradient_style',
				array(
					'section'            => 'cncb_accept_button_section',
					'custom_description' => esc_html__( 'Example: linear-gradient(#e66465, #9198e5)', 'cookie-notice-and-consent-banner' ),
					'full_width'         => true,
					'type'               => 'text',
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_ab_hover_border_color',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'cncb_ab_hover_border_color',
				array(
					'section'  => 'cncb_accept_button_section',
					'settings' => 'cncb_ab_hover_border_color',
					'label'    => esc_attr__( 'Border Color (on hover)', 'cookie-notice-and-consent-banner' ),
				)
			)
		);
	}

	/**
	 * Register the Decline button Controls within Customize.
	 *
	 * @param \WP_Customize_Manager $wp_customize The customize manager object.
	 *
	 * @return void
	 */
	private function decline_button_section( $wp_customize ) {
		$wp_customize->add_section(
			'cncb_decline_button_section',
			array(
				'title'    => esc_attr__( 'Decline Button', 'cookie-notice-and-consent-banner' ),
				'priority' => 6,
				'panel'    => 'cncb_settings',
			)
		);

		$wp_customize->add_setting(
			'cncb_db_text_color',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'cncb_db_text_color',
				array(
					'section'  => 'cncb_decline_button_section',
					'settings' => 'cncb_db_text_color',
					'label'    => esc_attr__( 'Text Color', 'cookie-notice-and-consent-banner' ),
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_db_font_family',
			array(
				'transport' => 'postMessage',
                'sanitize_callback' => 'cncb_text_sanitization',
			)
		);

		$wp_customize->add_control(
			'cncb_db_font_family',
			array(
				'section'     => 'cncb_decline_button_section',
				'label'       => esc_attr__( 'Font Family', 'cookie-notice-and-consent-banner' ),
				'description' => esc_html__( 'Example: Arial', 'cookie-notice-and-consent-banner' ),
				'type'        => 'text',
			)
		);

		$wp_customize->add_setting(
			'cncb_db_border_radius',
			array(
				'transport' => 'postMessage',
                'sanitize_callback' => 'cncb_text_sanitization',
			)
		);

		$wp_customize->add_control(
			new CNCB_Text_Custom_Control(
				$wp_customize,
				'cncb_db_border_radius',
				array(
					'section' => 'cncb_decline_button_section',
					'label'   => esc_attr__( 'Border Radius', 'cookie-notice-and-consent-banner' ),
					'units'   => 'px',
					'type'    => 'text',
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_db_border_width',
			array(
				'transport' => 'postMessage',
                'sanitize_callback' => 'cncb_text_sanitization',
			)
		);

		$wp_customize->add_control(
			new CNCB_Text_Custom_Control(
				$wp_customize,
				'cncb_db_border_width',
				array(
					'section' => 'cncb_decline_button_section',
					'label'   => esc_attr__( 'Border Width', 'cookie-notice-and-consent-banner' ),
					'units'   => 'px',
					'type'    => 'text',
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_db_border_color',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'cncb_db_border_color',
				array(
					'section'  => 'cncb_decline_button_section',
					'settings' => 'cncb_db_border_color',
					'label'    => esc_attr__( 'Border Color', 'cookie-notice-and-consent-banner' ),
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_db_shadow',
			array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'cncb_switch_sanitization',
				'default'           => 0,
			)
		);

		$wp_customize->add_control(
			new cncb_Toggle_Switch_Custom_control(
				$wp_customize,
				'cncb_db_shadow',
				array(
					'label'   => esc_attr__( 'Shadow', 'cookie-notice-and-consent-banner' ),
					'section' => 'cncb_decline_button_section',
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_db_shadow_style',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new CNCB_Text_Custom_Control(
				$wp_customize,
				'cncb_db_shadow_style',
				array(
					'section'            => 'cncb_decline_button_section',
					'custom_description' => esc_html__( 'Example: 0px 1px 10px #616161', 'cookie-notice-and-consent-banner' ),
					'full_width'         => true,
					'type'               => 'text',
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_db_bg_color',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'cncb_db_bg_color',
				array(
					'section'  => 'cncb_decline_button_section',
					'settings' => 'cncb_db_bg_color',
					'label'    => esc_attr__( 'Background Color', 'cookie-notice-and-consent-banner' ),
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_db_gradient',
			array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'cncb_switch_sanitization',
				'default'           => 0,
			)
		);

		$wp_customize->add_control(
			new cncb_Toggle_Switch_Custom_control(
				$wp_customize,
				'cncb_db_gradient',
				array(
					'label'   => esc_attr__( 'Gradient', 'cookie-notice-and-consent-banner' ),
					'section' => 'cncb_decline_button_section',
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_db_gradient_style',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new CNCB_Text_Custom_Control(
				$wp_customize,
				'cncb_db_gradient_style',
				array(
					'section'            => 'cncb_decline_button_section',
					'custom_description' => esc_html__( 'Example: linear-gradient(#e66465, #9198e5)', 'cookie-notice-and-consent-banner' ),
					'full_width'         => true,
					'type'               => 'text',
				)
			)
		);

		/* Hover settings */
		$wp_customize->add_setting(
			'cncb_db_hover_text_color',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'cncb_db_hover_text_color',
				array(
					'section'  => 'cncb_decline_button_section',
					'settings' => 'cncb_db_hover_text_color',
					'label'    => esc_attr__( 'Text Color (on hover)', 'cookie-notice-and-consent-banner' ),
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_db_hover_bg_color',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'cncb_db_hover_bg_color',
				array(
					'section'  => 'cncb_decline_button_section',
					'settings' => 'cncb_db_hover_bg_color',
					'label'    => esc_attr__( 'Background color (on hover)', 'cookie-notice-and-consent-banner' ),
				)
			)
		);
		$wp_customize->add_setting(
			'cncb_db_hover_gradient',
			array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'cncb_switch_sanitization',
				'default'           => 0,
			)
		);

		$wp_customize->add_control(
			new cncb_Toggle_Switch_Custom_control(
				$wp_customize,
				'cncb_db_hover_gradient',
				array(
					'label'   => esc_attr__( 'Gradient (on hover)', 'cookie-notice-and-consent-banner' ),
					'section' => 'cncb_decline_button_section',
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_db_hover_gradient_style',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new CNCB_Text_Custom_Control(
				$wp_customize,
				'cncb_db_hover_gradient_style',
				array(
					'section'            => 'cncb_decline_button_section',
					'custom_description' => esc_html__( 'Example: linear-gradient(#e66465, #9198e5)', 'cookie-notice-and-consent-banner' ),
					'full_width'         => true,
					'type'               => 'text',
				)
			)
		);

		$wp_customize->add_setting(
			'cncb_db_hover_border_color',
			array(
				'transport' => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'cncb_db_hover_border_color',
				array(
					'section'  => 'cncb_decline_button_section',
					'settings' => 'cncb_db_hover_border_color',
					'label'    => esc_attr__( 'Border Color (on hover)', 'cookie-notice-and-consent-banner' ),
				)
			)
		);
	}

	/**
	 * Register the Additional css Controls within Customize.
	 *
	 * @param \WP_Customize_Manager $wp_customize The customize manager object.
	 *
	 * @return void
	 */
	private function additional_css( $wp_customize ) {
		$wp_customize->add_section(
			'cncb_custom_css_section',
			array(
				'title'    => esc_html__( 'Additional CSS', 'cookie-notice-and-consent-banner' ),
				'panel'    => 'cncb_settings',
				'priority' => 7,
			)
		);
		$wp_customize->add_setting(
			'cncb_custom_css',
			array(
				'type' => 'option',
                'sanitize_callback' => 'cncb_text_sanitization',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Code_Editor_Control(
				$wp_customize,
				'cncb_custom_css',
				array(
					'code_type' => 'css',
					'settings'  => 'cncb_custom_css',
					'section'   => 'cncb_custom_css_section',
				)
			)
		);
	}

	/**
	 * Enqueues js file to preview and creates settings for the file.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function customize_preview_js() {

		wp_register_script(
			CNCB_PREFIX . '-customizer',
			CNCB_URI . '/js/preview-customizer' . CNCB_Banner_Helper::get_min_prefix() . '.js',
			array( 'customize-preview', 'cncb_banner' ),
			CNCB_VERSION,
			true
		);

		wp_localize_script(
			CNCB_PREFIX . '-customizer',
			'cncb_plugin_object',
			CNCB_Banner_Helper::get_options_data_array()
		);
		wp_enqueue_script( CNCB_PREFIX . '-customizer' );
	}
}
/* Instantiate the main class. */
new CNCB_Customizer();
