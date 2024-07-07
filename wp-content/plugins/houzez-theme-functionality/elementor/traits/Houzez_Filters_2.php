<?php

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Houzez_Property_Filters_2 {
    public function register_common_filter_controls_2() {

        $this->add_control(
            'sort_by',
            [
                'label'     => esc_html__( 'Sort By', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => houzez_sorting_array(),
                'description' => '',
                'default' => '',
            ]
        );

        $this->add_control(
            'posts_limit',
            [
                'label'     => esc_html__('Number of properties', 'houzez-theme-functionality'),
                'type'      => Controls_Manager::NUMBER,
                'min'     => 1,
                'max'     => 500,
                'step'    => 1,
                'default' => 9,
            ]
        );

        $this->add_control(
            'offset',
            [
                'label'     => 'Offset',
                'type'      => Controls_Manager::TEXT,
                'description' => '',
            ]
        );

        $this->add_control(
            'post_status',
            [
                'label'     => esc_html__( 'Post Status', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => houzez_ele_property_status(),
                'description' => '',
                'default' => 'all',
            ]
        );

    }
}
