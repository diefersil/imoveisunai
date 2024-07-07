<?php

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Houzez_Property_Card_Common_Filters {
    public function register_common_filters_controls() {

        
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
                    ]
                );
            }
        }

        if( isset($hide_filters) && ! in_array('property_country', $hide_filters) ) {
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
                ]
            );
        }

        if( isset($hide_filters) && ! in_array('property_state', $hide_filters) ) {
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
                ]
            );
        }

        if( isset($hide_filters) && ! in_array('property_city', $hide_filters) ) {
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
                ]
            );
        }

        if( isset($hide_filters) && ! in_array('property_area', $hide_filters) ) {
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
                ]
            );
        }
        

        $this->add_control(
            'properties_by_agents',
            [
                'label'    => esc_html__('Properties by Agents', 'houzez'),
                'type'     => Controls_Manager::SELECT2,
                'multiple' => true,
                'label_block' => true,
                'options'  => array_slice( houzez_get_agents_array(), 1, null, true ),
            ]
        );

        $this->add_control(
            'properties_by_agencies',
            [
                'label'    => esc_html__('Properties by Agencies', 'houzez'),
                'type'     => Controls_Manager::SELECT2,
                'multiple' => true,
                'label_block' => true,
                'options'  => array_slice( houzez_get_agency_array(), 1, null, true ),
            ]
        );

        $this->add_control(
            'min_price',
            [
                'label'    => esc_html__('Minimum Price', 'houzez'),
                'type'     => Controls_Manager::NUMBER,
                'label_block' => false,
            ]
        );
        $this->add_control(
            'max_price',
            [
                'label'    => esc_html__('Maximum Price', 'houzez'),
                'type'     => Controls_Manager::NUMBER,
                'label_block' => false,
            ]
        );
        

        $this->add_control(
            'houzez_user_role',
            [
                'label'     => esc_html__( 'User Role', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    ''  => esc_html__( 'All', 'houzez-theme-functionality'),
                    'houzez_owner'    => 'Owner',
                    'houzez_manager'  => 'Manager',
                    'houzez_agent'  => 'Agent',
                    'author'  => 'Author',
                    'houzez_agency'  => 'Agency',
                ],
                'description' => '',
                'default' => '',
            ]
        );

        $this->add_control(
            'featured_prop',
            [
                'label'     => esc_html__( 'Featured Properties', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    ''  => esc_html__( '- Any -', 'houzez-theme-functionality'),
                    'no'    => esc_html__('Without Featured', 'houzez'),
                    'yes'  => esc_html__('Only Featured', 'houzez')
                ],
                "description" => esc_html__("You can make a post featured by clicking featured properties checkbox while add/edit post", "houzez-theme-functionality"),
                'default' => '',
            ]
        );

        $this->add_control(
            'property_ids',
            [
                'label'     => esc_html__( 'Properties IDs', 'houzez-theme-functionality' ),
                'type'      => Controls_Manager::TEXT,
                'description'   => esc_html__( 'Enter properties ids comma separated. Ex 12,305,34', 'houzez-theme-functionality' ),
            ]
        );

    }
}
