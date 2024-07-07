<?php

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Elementor Properties Carousel v6 Widget.
 * @since 2.0
 */
class Houzez_Elementor_Properties_Carousels_v6 extends Widget_Base {
    use Houzez_Property_Card_Common_Filters;
    use Houzez_Property_Filters_2;

    /**
     * Get widget name.
     *
     * Retrieve widget name.
     *
     * @since 2.0
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'houzez_elementor_properties_carousel_v6';
    }

    /**
     * Get widget title.
     * @since 2.0
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return esc_html__( 'Property Cards Carousel v6', 'houzez-theme-functionality' );
    }

    /**
     * Get widget icon.
     *
     * @since 2.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'houzez-element-icon eicon-posts-carousel';
    }

    /**
     * Get widget categories.
     *
     * Retrieve the list of categories the widget belongs to.
     *
     * @since 2.0
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return [ 'houzez-elements' ];
    }

    /**
     * Register widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 2.0
     * @access protected
     */
    protected function register_controls() {


        //Carousel Settings
        $this->start_controls_section(
            'filters_section',
            [
                'label'     => esc_html__( 'Carousel Settings', 'houzez-theme-functionality' ),
                'tab'       => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name' => 'listing_thumb', // // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `thumbnail_size` and `thumbnail_custom_dimension`.
                'exclude' => [ 'custom', 'thumbnail', 'houzez-image_masonry', 'houzez-map-info', 'houzez-variable-gallery', 'houzez-gallery' ],
                'include' => [],
                'default' => 'houzez-item-image-6',
            ]
        );

        $this->add_control(
            'slides_to_show',
            [
                'label'     => esc_html__('Slides To Show', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
                "description" => '',
                'default' => '3',
            ]
        );
        $this->add_control(
            'slides_to_scroll',
            [
                'label'     => esc_html__('Slides To Scroll', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
                "description" => '',
                'default' => '1',
            ]
        );
        $this->add_control(
            'slide_infinite',
            [
                'label'     => esc_html__('Infinite Scroll', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    'true' => esc_html__('Yes', 'houzez-theme-functionality' ),
                    'false' => esc_html__('No', 'houzez-theme-functionality' )
                ],
                "description" => '',
                'default' => 'true',
            ]
        );
        $this->add_control(
            'slide_auto',
            [
                'label'     => esc_html__('Auto Play', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    'false' => esc_html__('No', 'houzez-theme-functionality' ),
                    'true' => esc_html__('Yes', 'houzez-theme-functionality' )
                    
                ],
                "description" => '',
                'default' => 'false',
            ]
        );

        $this->add_control(
            'auto_speed',
            [
                'label'     => 'Auto Play Speed',
                'type'      => Controls_Manager::TEXT,
                'description' => esc_html__("Autoplay Speed in milliseconds. Default 3000", 'houzez-theme-functionality'),
                'default' => '3000'
            ]
        );
        $this->add_control(
            'navigation',
            [
                'label'     => esc_html__('Next/Prev Navigation', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    'false' => esc_html__('No', 'houzez-theme-functionality' ),
                    'true' => esc_html__('Yes', 'houzez-theme-functionality' )
                    
                ],
                "description" => '',
                'default' => 'true',
            ]
        );
        $this->add_control(
            'slide_dots',
            [
                'label'     => esc_html__('Dots Nav', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    'false' => esc_html__('No', 'houzez-theme-functionality' ),
                    'true' => esc_html__('Yes', 'houzez-theme-functionality' )
                    
                ],
                "description" => '',
                'default' => 'false',
            ]
        );
        
        $this->end_controls_section();

        $this->start_controls_section(
            'content_section',
            [
                'label'     => esc_html__( 'Filters', 'houzez-theme-functionality' ),
                'tab'       => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->register_common_filters_controls();

        $this->register_common_filter_controls_2();

        $this->add_control(
            'all_btn',
            [
                'label'     => esc_html__("All - Button Text", "houzez-theme-functionality"),
                'type'      => Controls_Manager::TEXT,
                'description' => '',
            ]
        );
        $this->add_control(
            'all_url',
            [
                'label'     => esc_html__("All - Button URL", "houzez-theme-functionality"),
                'type'      => Controls_Manager::TEXT,
                'description' => '',
            ]
        );
        
        $this->end_controls_section();

        /*--------------------------------------------------------------------------------
        * Show/Hide
        * -------------------------------------------------------------------------------*/

        $this->start_controls_section(
            'hide_show_section',
            [
                'label'     => esc_html__( 'Show/Hide Data', 'houzez-theme-functionality' ),
                'tab'       => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'hide_compare',
            [
                'label' => esc_html__( 'Hide Compare Button', 'houzez-theme-functionality' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'houzez-theme-functionality' ),
                'label_off' => esc_html__( 'No', 'houzez-theme-functionality' ),
                'return_value' => 'none',
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .property-carousel-module .item-tools .item-compare' => 'display: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'hide_favorite',
            [
                'label' => esc_html__( 'Hide Favorite Button', 'houzez-theme-functionality' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'houzez-theme-functionality' ),
                'label_off' => esc_html__( 'No', 'houzez-theme-functionality' ),
                'return_value' => 'none',
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .property-carousel-module .item-tools .item-favorite' => 'display: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'hide_preview',
            [
                'label' => esc_html__( 'Hide Preview Button', 'houzez-theme-functionality' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'houzez-theme-functionality' ),
                'label_off' => esc_html__( 'No', 'houzez-theme-functionality' ),
                'return_value' => 'none',
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .property-carousel-module .item-tools .item-preview' => 'display: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'hide_featured_label',
            [
                'label' => esc_html__( 'Hide Featured Label', 'houzez-theme-functionality' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'houzez-theme-functionality' ),
                'label_off' => esc_html__( 'No', 'houzez-theme-functionality' ),
                'return_value' => 'none',
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .property-carousel-module .label-featured' => 'display: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'hide_label',
            [
                'label' => esc_html__( 'Hide Labels', 'houzez-theme-functionality' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'houzez-theme-functionality' ),
                'label_off' => esc_html__( 'No', 'houzez-theme-functionality' ),
                'return_value' => 'none',
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .property-carousel-module .labels-wrap .hz-label' => 'display: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        /*--------------------------------------------------------------------------------
        * Typography
        * -------------------------------------------------------------------------------*/
        $this->start_controls_section(
            'typography_section',
            [
                'label'     => esc_html__( 'Typography', 'houzez-theme-functionality' ),
                'tab'       => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'hz_property_title',
                'label'    => esc_html__( 'Property Title', 'houzez-theme-functionality' ),
                'selector' => '{{WRAPPER}} .item-title',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'hz_meta_figure',
                'label'    => esc_html__( 'Meta Figure', 'houzez-theme-functionality' ),
                'selector' => '{{WRAPPER}} .hz-figure',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'hz_item_price',
                'label'    => esc_html__( 'Price', 'houzez-theme-functionality' ),
                'selector' => '{{WRAPPER}} .item-price',
            ]
        );
    
        $this->end_controls_section();

        /*--------------------------------------------------------------------------------
        * Margin and Spacing
        * -------------------------------------------------------------------------------*/
        $this->start_controls_section(
            'hz_spacing_margin_section',
            [
                'label'     => esc_html__( 'Spaces & Sizes', 'houzez-theme-functionality' ),
                'tab'       => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'hz_title_margin_bottom',
            [
                'label' => esc_html__( 'Title Margin Bottom(px)', 'houzez-theme-functionality' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'devices' => [ 'desktop', 'tablet', 'mobile' ],
                'desktop_default' => [
                    'size' => '',
                    'unit' => 'px',
                ],
                'tablet_default' => [
                    'size' => '',
                    'unit' => 'px',
                ],
                'mobile_default' => [
                    'size' => '',
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .item-wrap-v6 .item-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );


        $this->add_responsive_control(
            'hz_meta_icons',
            [
                'label' => esc_html__( 'Meta Icons Size(px)', 'houzez-theme-functionality' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'devices' => [ 'desktop', 'tablet', 'mobile' ],
                'desktop_default' => [
                    'size' => '',
                    'unit' => 'px',
                ],
                'tablet_default' => [
                    'size' => '',
                    'unit' => 'px',
                ],
                'mobile_default' => [
                    'size' => '',
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .item-amenities i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'hz_content_padding',
            [
                'label'      => esc_html__( 'Content Area Padding', 'houzez-theme-functionality' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .item-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        /*--------------------------------------------------------------------------------
        * Box Shadow
        * -------------------------------------------------------------------------------*/
        $this->start_controls_section(
            'hz_grid_box_shadow',
            [
                'label' => esc_html__( 'Box Shadow', 'houzez-theme-functionality' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'box_shadow',
                'label'    => esc_html__( 'Box Shadow', 'houzez-theme-functionality' ),
                'selector' => '{{WRAPPER}} .item-wrap-v6',
            ]
        );

        $this->end_controls_section();

        /*--------------------------------------------------------------------------------
        * Colors
        * -------------------------------------------------------------------------------*/
        $this->start_controls_section(
            'hz_grid_colors',
            [
                'label' => esc_html__( 'Colors', 'houzez-theme-functionality' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'grid_bg_color',
            [
                'label'     => esc_html__( 'Grid Background', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .item-wrap' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'price_color',
            [
                'label'     => esc_html__( 'Price', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .item-price-wrap' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'buttons_bg_color',
            [
                'label'     => esc_html__( 'Item Tools Background Color', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .item-tool > span' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'buttons_bg_color_hover',
            [
                'label'     => esc_html__( 'Item Tools Background Color Hover', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .item-tool > span:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'buttons_color',
            [
                'label'     => esc_html__( 'Item Tools Color', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .item-tool > span' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'buttons_color_hover',
            [
                'label'     => esc_html__( 'Item Tools Color Hover', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .item-tool > span:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label'     => esc_html__( 'Title Color', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .item-title a' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'icons_color',
            [
                'label'     => esc_html__( 'Icons', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .item-amenities i' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'figure_color',
            [
                'label'     => esc_html__( 'Figure', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .hz-figure' => 'color: {{VALUE}}'
                ],
            ]
        );        
        
        $this->end_controls_section();

        /*--------------------------------------------------------------------------------
        * Next Prev button
        * -------------------------------------------------------------------------------*/
        $this->start_controls_section(
            'hz_next_prev',
            [
                'label' => esc_html__( 'Next/Prev buttons', 'houzez-theme-functionality' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs( 'image_effects' );

        $this->start_controls_tab( 'normal',
            array(
                'label' => __( 'Normal', 'houzez-theme-functionality' ),
            )
        );
        $this->add_control(
            'np_bg_color',
            [
                'label'     => esc_html__( 'Background Color', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .property-carousel-buttons-wrap button' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .btn-view-all' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        $this->add_control(
            'np_color',
            [
                'label'     => esc_html__( 'Color', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .property-carousel-buttons-wrap button' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .btn-view-all' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'np_border_color',
            [
                'label'     => esc_html__( 'Border Color', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .property-carousel-buttons-wrap button' => 'border: 1px solid {{VALUE}}',
                    '{{WRAPPER}} .btn-view-all' => 'border: 1px solid {{VALUE}}',
                ],
            ]
        );
        $this->end_controls_tab();

        $this->start_controls_tab( 'hover',
            array(
                'label' => __( 'Hover', 'houzez-theme-functionality' ),
            )
        );
    
        $this->add_control(
            'np_bg_color_hover',
            [
                'label'     => esc_html__( 'Background Color', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .property-carousel-buttons-wrap button:hover' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .btn-view-all:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        $this->add_control(
            'np_color_hover',
            [
                'label'     => esc_html__( 'Color', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .property-carousel-buttons-wrap button:hover' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .btn-view-all:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'np_border_color_hover',
            [
                'label'     => esc_html__( 'Border Color', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .property-carousel-buttons-wrap button:hover' => 'border: 1px solid {{VALUE}}',
                    '{{WRAPPER}} .btn-view-all:hover' => 'border: 1px solid {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();
        
        $this->end_controls_section();

        $this->start_controls_section(
            'hz_dots',
            [
                'label' => esc_html__( 'Carousel Dots', 'houzez-theme-functionality' ),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'slide_dots' => 'true'
                ]
            ]
        );

        $this->add_responsive_control(
            'dots_size',
            [
                'label' => esc_html__( 'Size', 'houzez-theme-functionality' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .slick-dots li button:before' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dots_space',
            [
                'label' => esc_html__( 'Space Between', 'houzez-theme-functionality' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .slick-dots li' => 'margin: 0 {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dots_top',
            [
                'label' => esc_html__( 'Margin Top', 'houzez-theme-functionality' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => -50,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .slick-dots li button:before' => 'top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dots_opacity',
            [
                'label' => esc_html__( 'Opacity', 'houzez-theme-functionality' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'          => [
                    'px' => [
                        'min' => 1,
                        'max' => 99,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .slick-dots li button:before' => 'opacity: 0.{{SIZE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dots_active_opacity',
            [
                'label' => esc_html__( 'Opacity Active', 'houzez-theme-functionality' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'          => [
                    'px' => [
                        'min' => 1,
                        'max' => 99,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .slick-dots li.slick-active button:before' => 'opacity: 0.{{SIZE}};',
                ],
            ]
        );

        $this->add_control(
            'np_dots_color',
            [
                'label'     => esc_html__( 'Color', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .slick-dots li button:before' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'np_dots_active_color',
            [
                'label'     => esc_html__( 'Active Color', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .slick-dots li.slick-active button:before' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();


    }

    /**
     * Render widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 2.0
     * @access protected
     */
    protected function render() {

        $settings = $this->get_settings_for_display();

        $property_type = $property_status = $property_label = $property_country = $property_state = $property_city = $property_area = $properties_by_agents = $properties_by_agencies = '';

        if(!empty($settings['property_type'])) {
            $property_type = implode (",", $settings['property_type']);
        }

        if(!empty($settings['property_status'])) {
            $property_status = implode (",", $settings['property_status']);
        }

        if(!empty($settings['property_label'])) {
            $property_label = implode (",", $settings['property_label']);
        }

        if(!empty($settings['property_country'])) {
            $property_country = implode (",", $settings['property_country']);
        }

        if(!empty($settings['property_state'])) {
            $property_state = implode (",", $settings['property_state']);
        }

        if(!empty($settings['property_city'])) {
            $property_city = implode (",", $settings['property_city']);
        }

        if(!empty($settings['property_area'])) {
            $property_area = implode (",", $settings['property_area']);
        }

        if( !empty($settings['properties_by_agents']) ) {
            $properties_by_agents = $settings['properties_by_agents'];
        }

        if( !empty($settings['properties_by_agencies']) ) {
            $properties_by_agencies = $settings['properties_by_agencies'];
        }
        $args['properties_by_agencies'] = $properties_by_agencies;

        $args['property_type']   =  $property_type;
        $args['property_status']   =  $property_status;
        $args['property_label']   =  $property_label;
        $args['property_country']   =  $property_country;
        $args['property_state']   =  $property_state;
        $args['property_city']   =  $property_city;
        $args['property_area']   =  $property_area;

        $args['featured_prop'] =  $settings['featured_prop'];
        $args['property_ids'] =  $settings['property_ids'];
        $args['posts_limit'] =  $settings['posts_limit'];
        $args['sort_by'] =  $settings['sort_by'];
        $args['offset'] =  $settings['offset'];
        $args['post_status'] =  $settings['post_status'];

        $args['all_btn'] = $settings['all_btn'];
        $args['all_url'] = $settings['all_url'];
        $args['slides_to_show'] = $settings['slides_to_show'];
        $args['slides_to_scroll'] = $settings['slides_to_scroll'];
        $args['slide_infinite'] = $settings['slide_infinite'];
        $args['slide_auto'] = $settings['slide_auto'];
        $args['auto_speed'] = $settings['auto_speed'];
        $args['navigation'] = $settings['navigation'];
        $args['slide_dots'] = $settings['slide_dots'];
        $args['thumb_size'] = $settings['listing_thumb_size'];
        $args['properties_by_agents'] = $properties_by_agents;
        $args['min_price'] = $settings['min_price'];
        $args['max_price'] = $settings['max_price'];


        if( function_exists( 'houzez_prop_carousel_v6' ) ) {
            echo houzez_prop_carousel_v6( $args );
        }

        if ( Plugin::$instance->editor->is_edit_mode() ) : 
            $token = wp_generate_password(5, false, false);
            ?>

            <script>
            jQuery('.houzez-properties-carousel-js[id^="houzez-properties-carousel-"]').each(function(){
                var $div = jQuery(this);
                var token = $div.data('token');
                var obj = window['houzez_prop_caoursel_' + token];

                var slides_to_scroll = <?php echo $settings['slides_to_scroll']; ?>,
                    slides_to_show = <?php echo $settings['slides_to_show']; ?>,
                    navigation = <?php echo $settings['navigation']; ?>,
                    auto_play = <?php echo $settings['slide_auto']; ?>,
                    auto_play_speed = parseInt(<?php echo $settings['auto_speed']; ?>),
                    dots = <?php echo $settings['slide_dots']; ?>,
                    slide_infinite =  <?php echo $settings['slide_infinite']; ?>;

                var houzez_rtl = houzez_vars.houzez_rtl;

                if( houzez_rtl == 'yes' ) {
                    houzez_rtl = true;
                } else {
                    houzez_rtl = false;
                }

                function parseBool(str) {
                    if( str == 'true' ) { return true; } else { return false; }
                }

                var houzezCarousel = jQuery('#houzez-properties-carousel-'+token);

                houzezCarousel.not('.slick-initialized').slick({
                    rtl: houzez_rtl,
                    lazyLoad: 'ondemand',
                    infinite: slide_infinite,
                    speed: 300,
                    slidesToShow: slides_to_show,
                    slidesToScroll: slides_to_scroll,
                    arrows: navigation,
                    autoplay: auto_play,
                    autoplaySpeed: auto_play_speed,
                    adaptiveHeight: true,
                    dots: dots,
                    appendArrows: '.houzez-carousel-arrows-'+token,
                    prevArrow: jQuery('.slick-prev-js-'+token),
                    nextArrow: jQuery('.slick-next-js-'+token),
                    responsive: [{
                            breakpoint: 992,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 2
                            }
                        },
                        {
                            breakpoint: 769,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                        }
                    ]
                });

            });
            
            </script>
        
        <?php endif;

    }

}

Plugin::instance()->widgets_manager->register( new Houzez_Elementor_Properties_Carousels_v6 );