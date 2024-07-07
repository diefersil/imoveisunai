<?php

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Houzez_Property_Taxonomies {
    public function register_houzez_taxonomies_controls() {

        
        // Property taxonomies controls
        $prop_taxonomies = get_object_taxonomies( 'property', 'objects' );
        unset( $prop_taxonomies['property_feature'] );
        unset( $prop_taxonomies['property_country'] );
        unset( $prop_taxonomies['property_state'] );
        unset( $prop_taxonomies['property_city'] );
        unset( $prop_taxonomies['property_area'] );

        $hide_filters = array();
        $page_filters = houzez_option('houzez_page_filters');

        if( isset($page_filters) && !empty($page_filters) ) {
            $hide_filters = $page_filters;
            foreach ($page_filters as $filter) {
                unset( $prop_taxonomies[$filter] );
            }
        }

        if ( ! empty( $prop_taxonomies ) && ! is_wp_error( $prop_taxonomies ) ) {
            foreach ( $prop_taxonomies as $single_tax ) {

                $options_array = array();
                $terms = get_terms( 
                    array(
                        'taxonomy' => $single_tax->name,
                        'hide_empty' => false
                )   );

                if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                    foreach ( $terms as $term ) {
                        $options_array[ $term->slug ] = $term->name;
                    }
                }

                $this->add_control(
                    $single_tax->name,
                    [
                        'label'    => $single_tax->label,
                        'type'     => Controls_Manager::SELECT2,
                        'multiple' => true,
                        'label_block' => true,
                        'options'  => $options_array,
                        'condition' => [
                            'houzez_cards_from' => $single_tax->name,
                        ],
                    ]
                );
            }
        }

        $this->add_control(
            'property_country',
            [
                'label'         => esc_html__('Country', 'houzez'),
                'multiple'      => true,
                'label_block'   => true,
                'type'          => 'houzez_autocomplete',
                'make_search'   => 'houzez_get_taxonomies',
                'render_result' => 'houzez_render_taxonomies',
                'taxonomy'      => array('property_country'),
                'condition' => [
                    'houzez_cards_from' => 'property_country',
                ],
            ]
        );

        $this->add_control(
            'property_state',
            [
                'label'         => esc_html__('State', 'houzez'),
                'multiple'      => true,
                'label_block'   => true,
                'type'          => 'houzez_autocomplete',
                'make_search'   => 'houzez_get_taxonomies',
                'render_result' => 'houzez_render_taxonomies',
                'taxonomy'      => array('property_state'),
                'condition' => [
                    'houzez_cards_from' => 'property_state',
                ],
            ]
        );

        $this->add_control(
            'property_city',
            [
                'label'         => esc_html__('City', 'houzez'),
                'multiple'      => true,
                'label_block'   => true,
                'type'          => 'houzez_autocomplete',
                'make_search'   => 'houzez_get_taxonomies',
                'render_result' => 'houzez_render_taxonomies',
                'taxonomy'      => array('property_city'),
                'condition' => [
                    'houzez_cards_from' => 'property_city',
                ],
            ]
        );

        $this->add_control(
            'property_area',
            [
                'label'         => esc_html__('Area', 'houzez'),
                'multiple'      => true,
                'label_block'   => true,
                'type'          => 'houzez_autocomplete',
                'make_search'   => 'houzez_get_taxonomies',
                'render_result' => 'houzez_render_taxonomies',
                'taxonomy'      => array('property_area'),
                'condition' => [
                    'houzez_cards_from' => 'property_area',
                ],
            ]
        );

        $this->add_control(
            'houzez_show_child',
            [
                'label'     => esc_html__( 'Show Child', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    '0'  => esc_html__( 'No', 'houzez-theme-functionality'),
                    '1'    => esc_html__( 'Yes', 'houzez-theme-functionality')
                ],
                'description' => '',
                'default' => '0',
            ]
        );

        $this->add_control(
            'houzez_hide_empty',
            [
                'label'     => esc_html__( 'Hide Empty', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    '0'  => esc_html__( 'No', 'houzez-theme-functionality'),
                    '1'    => esc_html__( 'Yes', 'houzez-theme-functionality')
                ],
                'description' => '',
                'default' => '1',
            ]
        );

        $this->add_control(
            'houzez_hide_count',
            [
                'label'     => esc_html__( 'Hide Count', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    '0'  => esc_html__( 'No', 'houzez-theme-functionality'),
                    '1'    => esc_html__( 'Yes', 'houzez-theme-functionality')
                ],
                'description' => '',
                'default' => '0',
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label'     => esc_html__( 'Order By', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    'name'  => esc_html__( 'Name', 'houzez-theme-functionality'),
                    'count'    => esc_html__( 'Count', 'houzez-theme-functionality'),
                    'id'    => esc_html__( 'ID', 'houzez-theme-functionality')
                ],
                'description' => '',
                'default' => 'name',
            ]
        );

        $this->add_control(
            'order',
            [
                'label'     => esc_html__( 'Order', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    'ASC'  => esc_html__( 'ASC', 'houzez-theme-functionality'),
                    'DESC'  => esc_html__( 'DESC', 'houzez-theme-functionality')
                ],
                'default' => 'ASC',
            ]
        );


        $this->add_control(
            'no_of_terms',
            [
                'label'     => esc_html__('Number of Items to Show', 'houzez-theme-functionality'),
                'type'      => Controls_Manager::TEXT,
                'description' => '',
                'default' => '',
            ]
        );

    }
}
