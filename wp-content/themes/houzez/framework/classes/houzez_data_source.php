<?php
if(!class_exists('houzez_data_source')) {
    class houzez_data_source {

        static $fake_loop_offset = 0; 

        static function shortcode_to_args($atts = '', $paged = '') {
            extract(shortcode_atts(
                    array(
                        'property_type' => '',
                        'property_status' => '',
                        'property_city' => '',
                        'property_country' => '',
                        'property_state' => '',
                        'property_area' => '',
                        'property_label' => '',
                        'houzez_user_role' => '',
                        'featured_prop' => '',
                        'property_ids' => '',
                        'posts_limit' => '',
                        'sort_by' => '',
                        'post_status' => '',
                        'offset' => '',
                        'min_price' => '',
                        'max_price' => '',
                        'min_beds' => '',
                        'max_beds' => '',
                        'min_baths' => '',
                        'max_baths' => '',
                        'properties_by_agents' => '',
                        'properties_by_agencies' => ''
                    ),
                    $atts
                )
            );

            $tax_query = array();
            $meta_query = array();
            //$property_ids_array = array();

            $wp_query_args = array(
                'ignore_sticky_posts' => 1
            );

            if( !empty($houzez_user_role) ) {
                $role_ids = self::houzez20_author_ids_by_role( $houzez_user_role );
                if (!empty($role_ids)) {
                    $wp_query_args['author__in'] = $role_ids;
                }
            }

            $sort_taxonomy = isset($_GET['tax']) ? $_GET['tax'] : '';

            if ( isset( $_GET['tab'] ) && isset( $_GET['tax'] ) ) {
                $tax_query[] = array(
                    'taxonomy' => esc_attr($_GET['tax']),
                    'field' => 'slug',
                    'terms' => esc_attr($_GET['tab'])
                );
            }

            if (!empty($property_type) && $sort_taxonomy != 'property_type') {
                $tax_query[] = array(
                    'taxonomy' => 'property_type',
                    'field' => 'slug',
                    'terms' => self::houzez20_traverse_comma_string($property_type)
                );
            }
            if (!empty($property_status) && $sort_taxonomy != 'property_status') {
                $tax_query[] = array(
                    'taxonomy' => 'property_status',
                    'field' => 'slug',
                    'terms' => self::houzez20_traverse_comma_string($property_status)
                );
            }
            if (!empty($property_state)) {
                $tax_query[] = array(
                    'taxonomy' => 'property_state',
                    'field' => 'slug',
                    'terms' => self::houzez20_traverse_comma_string($property_state)
                );
            }

            if (!empty($property_country)) {
                $tax_query[] = array(
                    'taxonomy' => 'property_country',
                    'field' => 'slug',
                    'terms' => self::houzez20_traverse_comma_string($property_country)
                );
            }
            if (!empty($property_city) && $sort_taxonomy != 'property_city' ) {
                $tax_query[] = array(
                    'taxonomy' => 'property_city',
                    'field' => 'slug',
                    'terms' => self::houzez20_traverse_comma_string($property_city)
                );
            }
            if (!empty($property_area)) {
                $tax_query[] = array(
                    'taxonomy' => 'property_area',
                    'field' => 'slug',
                    'terms' => self::houzez20_traverse_comma_string($property_area)
                );
            }
            if (!empty($property_label)) {
                $tax_query[] = array(
                    'taxonomy' => 'property_label',
                    'field' => 'slug',
                    'terms' => self::houzez20_traverse_comma_string($property_label)
                );
            }

            if ( !empty($properties_by_agents) ) {
                $meta_query[] = array(
                    'key'     => 'fave_agents',
                    'value'   => self::houzez20_traverse_comma_string($properties_by_agents),
                    'compare' => 'IN',
                );
                $meta_query[] = array(
                    'key'     => 'fave_agent_display_option',
                    'value'   => 'agent_info',
                    'compare' => '=',
                );
            }

            if ( !empty($properties_by_agencies) ) {
                $meta_query[] = array(
                    'key'     => 'fave_property_agency',
                    'value'   => self::houzez20_traverse_comma_string($properties_by_agencies),
                    'compare' => 'IN',
                );

                $meta_query[] = array(
                    'key'     => 'fave_agent_display_option',
                    'value'   => 'agency_info',
                    'compare' => '=',
                );
            }


            if (!empty($min_price) && !empty($max_price)) {
                $min_price = doubleval(houzez_clean($min_price));
                $max_price = doubleval(houzez_clean($max_price));

                if ($min_price >= 0 && $max_price > $min_price) {
                    $meta_query[] = array(
                        'key' => 'fave_property_price',
                        'value' => array($min_price, $max_price),
                        'type' => 'NUMERIC',
                        'compare' => 'BETWEEN',
                    );
                }
            } else if (!empty($min_price)) {
                $min_price = doubleval(houzez_clean($min_price));
                if ($min_price >= 0) {
                    $meta_query[] = array(
                        'key' => 'fave_property_price',
                        'value' => $min_price,
                        'type' => 'NUMERIC',
                        'compare' => '>=',
                    );
                }
            } else if (!empty($max_price)) {
                $max_price = doubleval(houzez_clean($max_price));
                if ($max_price >= 0) {
                    $meta_query[] = array(
                        'key' => 'fave_property_price',
                        'value' => $max_price,
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    );
                }
            }

            if ( !empty($min_beds) && !empty($max_beds) ) {
                $min_beds = intval($min_beds);
                $max_beds = intval($max_beds);

                if ($min_beds > 0 && $max_beds >= $min_beds) {
                    $meta_query[] = array(
                        'key' => 'fave_property_bedrooms',
                        'value' => array($min_beds, $max_beds),
                        'type' => 'NUMERIC',
                        'compare' => 'BETWEEN',
                    );
                }
            } else if ( !empty($min_beds) ) {
                $min_beds = intval($min_beds);
                if ($min_beds > 0) {
                    $meta_query[] = array(
                        'key' => 'fave_property_bedrooms',
                        'value' => $min_beds,
                        'type' => 'NUMERIC',
                        'compare' => '>=',
                    );
                }
            } else if ( ! empty($max_beds) ) {
                $max_beds = intval($max_beds);
                if ($max_beds > 0) {
                    $meta_query[] = array(
                        'key' => 'fave_property_bedrooms',
                        'value' => $max_beds,
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    );
                }
            }

            if ( !empty($min_baths) && !empty($max_baths) ) {
                $min_baths = intval($min_baths);
                $max_baths = intval($max_baths);

                if ($min_baths > 0 && $max_baths >= $min_baths) {
                    $meta_query[] = array(
                        'key' => 'fave_property_bathrooms',
                        'value' => array($min_baths, $max_baths),
                        'type' => 'NUMERIC',
                        'compare' => 'BETWEEN',
                    );
                }
            } else if ( !empty($min_baths) ) {
                $min_baths = intval($min_baths);
                if ($min_baths > 0) {
                    $meta_query[] = array(
                        'key' => 'fave_property_bathrooms',
                        'value' => $min_baths,
                        'type' => 'NUMERIC',
                        'compare' => '>=',
                    );
                }
            } else if ( !empty($max_baths) ) {
                $max_baths = intval($max_baths);
                if ($max_baths > 0) {
                    $meta_query[] = array(
                        'key' => 'fave_property_bathrooms',
                        'value' => $max_baths,
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    );
                }
            }

            if (!empty($featured_prop)) {
                if ($featured_prop == "yes") {
                    $meta_query[] = [
                        'key' => 'fave_featured',
                        'value' => '1',
                        'compare' => '='
                    ];
                } else {
                    $meta_query[] = [
                        'relation' => 'OR',
                        [
                            'key' => 'fave_featured',
                            'value' => '0',
                            'compare' => '='
                        ],
                        [
                            'key' => 'fave_featured',
                            'compare' => 'NOT EXISTS'
                        ]
                    ];
                }
            }

            $property_ids_array = explode(',', $property_ids);

            if (!empty($property_ids)) {
                $wp_query_args['post__in'] = $property_ids_array;
            }

            $tax_count = count( $tax_query );

            if( $tax_count > 1 ) {
                $tax_query['relation'] = 'AND';
            }
            if( $tax_count > 0 ){
                $wp_query_args['tax_query'] = $tax_query;
            }

            $meta_count = count($meta_query);
            if( $meta_count > 1 ) {
                $meta_query['relation'] = 'AND';
            }
            if ($meta_count > 0) {
                $wp_query_args['meta_query'] = $meta_query;
            }

            if ( isset( $_GET['sortby'] ) ) {
                $sort_by = $_GET['sortby'];
            }

            if ( $sort_by == 'a_title' ) {
                $wp_query_args['orderby'] = 'title';
                $wp_query_args['order'] = 'ASC';
            } else if ( $sort_by == 'd_title' ) {
                $wp_query_args['orderby'] = 'title';
                $wp_query_args['order'] = 'DESC';
            } else if ( $sort_by == 'a_price' ) {
                $wp_query_args['orderby'] = 'meta_value_num';
                $wp_query_args['meta_key'] = 'fave_property_price';
                $wp_query_args['order'] = 'ASC';
            } else if ( $sort_by == 'd_price' ) {
                $wp_query_args['orderby'] = 'meta_value_num';
                $wp_query_args['meta_key'] = 'fave_property_price';
                $wp_query_args['order'] = 'DESC';
            } else if ( $sort_by == 'a_date' ) {
                $wp_query_args['orderby'] = 'date';
                $wp_query_args['order'] = 'ASC';
            } else if ( $sort_by == 'd_date' ) {
                $wp_query_args['orderby'] = 'date';
                $wp_query_args['order'] = 'DESC';
            } else if ( $sort_by == 'featured_top' ) {
                $wp_query_args['orderby'] = 'meta_value';
                $wp_query_args['meta_key'] = 'fave_featured';
                $wp_query_args['order'] = 'DESC';
            } else if ( $sort_by == 'random' ) {
                $wp_query_args['orderby'] = 'rand';
                $wp_query_args['order'] = 'DESC';
            } else if ( $sort_by == 'featured_first' ) {
                $wp_query_args['orderby'] = 'meta_value date';
                $wp_query_args['meta_key'] = 'fave_featured';
            } else if ( $sort_by == 'featured_first_random' ) {
                $wp_query_args['meta_key'] = 'fave_featured';
                $wp_query_args['orderby'] = 'meta_value DESC rand'; 
            }

            if( $post_status == 'publish' ) {
                $wp_query_args['post_status'] = 'publish';
            } else if( $post_status == 'houzez_sold' ) {
                $wp_query_args['post_status'] = 'houzez_sold';
            } else {
                $wp_query_args['post_status'] = array('publish', 'houzez_sold');
            }

            if (empty($posts_limit)) {
                $posts_limit = get_option('posts_per_page');
            }
            $wp_query_args['posts_per_page'] = $posts_limit;

            if (!empty($paged)) {
                $wp_query_args['paged'] = $paged;
            } else {
                $wp_query_args['paged'] = 1;
            }

            if (!empty($offset) and $paged > 1) {
                $wp_query_args['offset'] = $offset + ( ($paged - 1) * $posts_limit) ;
            } else {
                $wp_query_args['offset'] = $offset ;
            }

            self::$fake_loop_offset = $offset;

            $wp_query_args['post_type'] = 'property';

            /*echo '<pre>';
            print_r($wp_query_args);
            echo '</pre>';*/

            return $wp_query_args;
        }


        /************************************************************************************** 
            converts a post metabox value array to a wordpress query args array
        ***************************************************************************************/
        static function metabox_to_args($homepage_loop_filter, $paged = '') {


            $wp_query_args = self::shortcode_to_args($homepage_loop_filter, $paged);


            $wp_query_args['ignore_sticky_posts'] = 0;

            if (isset($wp_query_args['offset']) and $wp_query_args['offset'] > 0) {
                add_filter('found_posts', array(__CLASS__, 'hook_fix_offset_pagination'), 1, 2 );
            }

            return $wp_query_args;
        }


        /************************************************************************************** 
            custom pagination for the fake template loops - used by hook
        ***************************************************************************************/
        static function hook_fix_offset_pagination($found_posts, $query) {
            remove_filter('found_posts','hook_fix_offset_pagination');
            return $found_posts - houzez_data_source::$fake_loop_offset;
        }


        static function &get_wp_query ($atts = '', $paged = '') {
            $args = self::shortcode_to_args($atts, $paged);
            $fave_query = new WP_Query($args);
            return $fave_query;
        }

        public static function houzez20_traverse_comma_string($string) {
            // Check if the input string is non-empty and valid
            if (!empty($string) && is_string($string)) {
                // Trim whitespace and split the string by commas
                $string_array = array_filter(array_map('trim', explode(',', $string)));
                
                // Return the array if it's not empty, otherwise return an empty string
                if (!empty($string_array)) {
                    return $string_array;
                }
            }
            return '';
        }


        public static function houzez20_author_ids_by_role($role) {
            $ids = get_users(array('role' => $role, 'fields' => 'ID'));
            return $ids;
        }
    }
}

