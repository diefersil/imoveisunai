<?php
/**
 * Run cron Jobs for different events
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 04/02/16
 * Time: 5:33 PM
 */

/*-----------------------------------------------------------------------------------*/
// Add Weekly crop interval
/*-----------------------------------------------------------------------------------*/
add_filter( 'cron_schedules', 'houzez_add_weekly_cron_schedule' );
if( !function_exists('houzez_add_weekly_cron_schedule') ):
    function houzez_add_weekly_cron_schedule( $schedules ) {

        $schedules['weekly'] = array(
            'interval' => 7 * 24 * 60 * 60, //7 days * 24 hours * 60 minutes * 60 seconds
            'display'  => 'Once Weekly',
        );
        $schedules['one_minute'] = array(
            'interval' => 60,
            'display'  => 'One minute',
        );

        return $schedules;
    }
endif;

/*-----------------------------------------------------------------------------------*/
// Schedule core
/*-----------------------------------------------------------------------------------*/
function houzez_schedule_checks() {
    $enable_paid_submission = esc_html ( fave_option('enable_paid_submission') );
    $per_listing_expire_unlimited = fave_option('per_listing_expire_unlimited');

    // Per listings expire
    if( ( $enable_paid_submission == 'per_listing' || $enable_paid_submission == 'free_paid_listing') && $per_listing_expire_unlimited != 0 ) {

        if (wp_next_scheduled('houzez_per_listing_expire_check')) {
            wp_clear_scheduled_hook('houzez_per_listing_expire_check');
        }
       
        if (!wp_next_scheduled('houzez_per_listing_expire_check')) {
            wp_schedule_event(time(), 'hourly', 'houzez_per_listing_expire_check');
        }
    }

    // Schedule Membership check
    if( $enable_paid_submission == 'membership' ) {
        if (wp_next_scheduled('houzez_check_membership_expire')) {
            wp_clear_scheduled_hook('houzez_check_membership_expire');
        }
        houzez_setup_daily_membership_check_schedule();
    }

    if (!wp_next_scheduled('houzez_check_featured_listing_expiry')) {
        wp_schedule_event(time(), 'twicedaily', 'houzez_check_featured_listing_expiry');
    }

    if (!wp_next_scheduled('houzez_check_expired_listings')) {
        wp_schedule_event(time(), 'daily', 'houzez_check_expired_listings');
    }

    /*if (!wp_next_scheduled('houzez_check_cron')) {
        wp_schedule_event(time(), 'one_minute', 'houzez_check_cron');
    }*/

}
add_action( 'init', 'houzez_schedule_checks' );


/*-----------------------------------------------------------------------------------*/
// Schedule daily membership check
/*-----------------------------------------------------------------------------------*/
if( !function_exists('houzez_setup_daily_membership_check_schedule') ):
    function  houzez_setup_daily_membership_check_schedule(){
        $enable_paid_submission = esc_html ( fave_option('enable_paid_submission') );

        if( $enable_paid_submission == "membership" ) {
            if (!wp_next_scheduled('houzez_check_membership_expire')) {
                wp_schedule_event(time(), 'daily', 'houzez_check_membership_expire');
            }
        }
    }
endif;
add_action( 'houzez_check_membership_expire', 'houzez_check_membership_expire_cron' );

if( !function_exists('houzez_per_listing_expire_check') ) {
    function houzez_per_listing_expire_check () {

        $enable_paid_submission = esc_html ( fave_option('enable_paid_submission') );
        $per_listing_expire_unlimited = fave_option('per_listing_expire_unlimited');
        $per_listing_expiration = intval ( fave_option('per_listing_expire') );

        if( $enable_paid_submission == 'per_listing' || $enable_paid_submission == 'free_paid_listing' ) {
            if ( $per_listing_expiration != 0 && $per_listing_expiration != '' && $per_listing_expire_unlimited != 0 ) {

                $args = array(
                    'post_type' => 'property',
                    'fields'          => 'ids', // Only get post IDs
                    'posts_per_page'  => -1,
                    'numberposts' => -1
                );

                $prop_selection = get_posts($args);
               
                foreach($prop_selection as $the_id) {

                    $prop_listed_date = strtotime(get_the_date("Y-m-d g:i:s", $the_id));

                    $houzez_manual_expire = get_post_meta( $the_id, 'houzez_manual_expire', true );
                    
                    // Check if manual expire date enable
                    if( empty( $houzez_manual_expire )) {
                        
                        $expiration_date = $prop_listed_date + $per_listing_expiration * 24 * 60 * 60;
                        
                        $today = strtotime(date( 'Y-m-d g:i:s', current_time( 'timestamp', 0 ) ));
                        
                        $user_id = houzez_get_author_by_post_id($the_id);
                        $user = new WP_User($user_id); //administrator
                        $user_role = $user->roles[0];

                        if ($user_role != 'administrator') {
                            
                            if ($expiration_date < $today) {                        
                                houzez_listing_set_to_expire($the_id);
                            }
                        }
                    }

                }

            }
        } // Enabled

    }
}
add_action( 'houzez_per_listing_expire_check', 'houzez_per_listing_expire_check' );


if( ! function_exists('houzez_check_featured_listing_status') ) {

    function houzez_check_featured_listing_status() {
        $enable_paid_submission = esc_html ( fave_option('enable_paid_submission') );
        $featured_listing_expiration = intval ( fave_option('featured_listing_expire', 30) );

        if( $enable_paid_submission == 'free_paid_listing'  ) {

            if ( $featured_listing_expiration != 0 && $featured_listing_expiration != '' ) {

               $args = array(
                    'post_type' => 'property',
                    'fields'          => 'ids', // Only get post IDs
                    'posts_per_page'  => -1,
                    'numberposts' => -1
                );

                $prop_selection = get_posts($args);
                
                foreach($prop_selection as $the_id) {

                    $prop_featured_date = get_post_meta( $the_id, 'houzez_featured_listing_date', true );
                    $prop_featured_date = strtotime($prop_featured_date);
                    $is_featured = get_post_meta( $the_id, 'fave_featured', true );
                    
                    $expiration_date = $prop_featured_date + $featured_listing_expiration * 24 * 60 * 60;
                    $today = strtotime(date( 'Y-m-d g:i:s', current_time( 'timestamp', 0 ) ));

                    $user_id = houzez_get_author_by_post_id($the_id);
                    $user = new WP_User($user_id); //administrator
                    $user_role = $user->roles[0];

                    if ($user_role != 'administrator' && $is_featured != 0 ) {
                        if ($expiration_date < $today) {
                            
                            update_post_meta($the_id, 'fave_featured', '0');
                            update_post_meta($the_id, 'houzez_featured_listing_date', '');

                            $user_email =   $user->user_email;

                            $args = array(
                                'expired_listing_url'  => get_permalink($the_id),
                                'expired_listing_name' => get_the_title($the_id)
                            );
                            houzez_email_type( $user_email, 'featured_listing_expired', $args );
                        }
                    }

                }

            }

        } // end enabled
    }
    add_action( 'houzez_check_featured_listing_expiry', 'houzez_check_featured_listing_status' );
}


/*-----------------------------------------------------------------------------------*/
// Add Weekly crop interval
/*-----------------------------------------------------------------------------------*/
if( !function_exists('houzez_schedule_email_events') ):
    function houzez_schedule_email_events(){
        global $houzez_options;
        $saved_search = $houzez_options['enable_disable_save_search'];
        $saved_search_duration = $houzez_options['save_search_duration'];

        if( $saved_search == '1' ) {
            if( $saved_search_duration == 'daily' ) {
                houzez_setup_saved_search_daily_schedule();

            } elseif( $saved_search_duration == 'weekly' ) {
                houzez_setup_saved_search_weekly_schedule();
            }

        } else {
            wp_clear_scheduled_hook('houzez_check_new_listing_action_hook');
        }

    }
endif;

/*-----------------------------------------------------------------------------------*/
// Add daily crop interval
/*-----------------------------------------------------------------------------------*/
if( !function_exists( 'houzez_setup_saved_search_daily_schedule' ) ) {
    function houzez_setup_saved_search_daily_schedule() {
        if (!wp_next_scheduled('houzez_check_new_listing_action_hook')) {
            wp_schedule_event(time(), 'daily', 'houzez_check_new_listing_action_hook');
        }
    }
}

/*-----------------------------------------------------------------------------------*/
// Add Weekly crop interval
/*-----------------------------------------------------------------------------------*/
if( !function_exists( 'houzez_setup_saved_search_weekly_schedule' ) ) {
    function houzez_setup_saved_search_weekly_schedule() {
        if (!wp_next_scheduled('houzez_check_new_listing_action_hook2')) {
            wp_schedule_event(time(), 'weekly', 'houzez_check_new_listing_action_hook2');
        }
    }
}



/*-----------------------------------------------------------------------------------*/
// Add Weekly crop interval
/*-----------------------------------------------------------------------------------*/
add_action('houzez_check_expired_listings', 'houzez_check_expired_listings');

if( !function_exists('houzez_check_expired_listings') ) {
    function houzez_check_expired_listings() {

        $auto_delete_expired_listings = fave_option('auto_delete_expired_listings', 0);
        $auto_delete_expired_listings_days = fave_option('auto_delete_expired_listings_days');

        if( $auto_delete_expired_listings && ! empty($auto_delete_expired_listings_days) ) {

            $days_ago = intval($auto_delete_expired_listings_days); // Number of days ago
            $date_threshold = date('Y-m-d H:i:s', strtotime("-{$days_ago} days")); // 10 days ago

            $args = array(
                'post_type' => 'property',
                'post_status' => 'expired',
                'posts_per_page' => -1, // Query all expired properties
                'meta_query' => array(
                    array(
                        'key' => 'houzez_expired_listing_date',
                        'value' => $date_threshold,
                        'compare' => '<=',
                        'type' => 'DATETIME'
                    )
                )
            );

            $expired_properties_query = new WP_Query($args);

            if ($expired_properties_query->have_posts()) {
                while ($expired_properties_query->have_posts()) {
                    $expired_properties_query->the_post();
                    $post_id = get_the_ID();
                    wp_delete_post($post_id, true); // Set to true to bypass trash and permanently delete
                }
            }

            wp_reset_postdata();
        }

    }
}

/*-----------------------------------------------------------------------------------*/
// Add Weekly crop interval
/*-----------------------------------------------------------------------------------*/
add_action('houzez_check_new_listing_action_hook', 'houzez_check_new_listing');

if( !function_exists('houzez_check_new_listing') ) {
    function houzez_check_new_listing() {

        $wp_date_query = houzez_get_mail_period();
        $args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'date_query' => $wp_date_query

        );
        $properties = new WP_Query($args);

        if ($properties->have_posts()) {
            houzez_check_saved_search();
        }
    }
}

/*-----------------------------------------------------------------------------------*/
// Check saved searches
/*-----------------------------------------------------------------------------------*/
if( !function_exists('houzez_check_saved_search') ) :
    function houzez_check_saved_search() {
        global $wpdb;

        $table_name     = $wpdb->prefix . 'houzez_search';
        $results        = $wpdb->get_results( 'SELECT * FROM ' . $table_name, OBJECT );

        if ( sizeof ( $results ) !== 0 ) :

            foreach ( $results as $houzez_saved_search ) :

                $arguments = unserialize( base64_decode( $houzez_saved_search->query ) );

                $user_email = $houzez_saved_search->email;

                $data = houzez_compose_send_email($arguments);

                if ($user_email != '' && $data['mail_content'] != '') :

                    $args = array(
                        'matching_submissions' => $data['mail_content'],
                        'listing_count' => $data['prop_count']
                    );

                    $value_message = fave_option('houzez_matching_submissions');
                    $value_subject = fave_option('houzez_subject_matching_submissions');

                    do_action( 'wpml_register_single_string', 'houzez', 'houzez_email_' . $value_message, $value_message );
                    do_action( 'wpml_register_single_string', 'houzez', 'houzez_email_subject_' . $value_subject, $value_subject );

                    $value_message = apply_filters('wpml_translate_single_string', $value_message, 'houzez', 'houzez_email_' . $value_message );
                    $value_subject = apply_filters('wpml_translate_single_string', $value_subject, 'houzez', 'houzez_email_subject_' . $value_subject );

                    $notificationArgs = array(
                        'title' => $value_subject,
                        'message' => $value_message,
                        'type' => 'matching_submissions',
                        'to' => $user_email,
                        'search_url' => $houzez_saved_search->url,
                    );
                    $notifArgs = array_merge($notificationArgs, $args);
                    
                    do_action('houzez_send_notification', $notificationArgs);

                    houzez_emails_filter_replace_2( $user_email, $value_message, $value_subject, $args);

                endif;

            endforeach;

        endif;

    }

endif;

/*-----------------------------------------------------------------------------------*/
// Add Weekly crop interval
/*-----------------------------------------------------------------------------------*/
if( !function_exists('houzez_get_mail_period') ) {
    function houzez_get_mail_period() {
        global $houzez_options;
        $saved_search_duration = fave_option('save_search_duration');
        //$houzez_options['save_search_duration'];

        if ( $saved_search_duration == 'daily' ) {
            $today = getdate();
            $wp_date_query = array(
                array(
                    'year' => $today['year'],
                    'month' => $today['mon'],
                    'day' => $today['mday'],
                )
            );
        } else {
            $wp_date_query = array(
                array(
                    'year' => date('Y'),
                    'week' => date('W'),
                )
            );
        }
        return $wp_date_query;
    }
}

/*-----------------------------------------------------------------------------------*/
// Add Weekly crop interval
/*-----------------------------------------------------------------------------------*/
if( !function_exists('houzez_compose_send_email') ):
    function houzez_compose_send_email($args){
        $mail_content   = '';
        
        $arguments      = $args;


        $arguments['date_query'] = $date_query_array = houzez_get_mail_period();
        $arguments['posts_per_page'] = -1;


        $prop_selection = new WP_Query($arguments);


        if($prop_selection->have_posts()){

            $prop_count = $prop_selection->post_count;

            $mail_content .= '<div class="email-wrap">';
            $mail_content .= '<div class="email-content">';
            $mail_content .= '<div class="email-listings">';
            $mail_content .= '<div class="email-listings-table">';

            while ($prop_selection->have_posts()): $prop_selection->the_post();
                $post_id = get_the_id();
                $sale_price = get_post_meta( get_the_ID(), 'fave_property_price', true );
                $price_postfix  = get_post_meta( get_the_ID(), 'fave_property_price_postfix', true );
                $price_prefix  = get_post_meta( get_the_ID(), 'fave_property_price_prefix', true );

                $prop_bed     = get_post_meta( get_the_ID(), 'fave_property_bedrooms', true );
                $prop_bath     = get_post_meta( get_the_ID(), 'fave_property_bathrooms', true );
                $prop_size     = get_post_meta( get_the_ID(), 'fave_property_size', true );
                $address     = get_post_meta( get_the_ID(), 'fave_property_map_address', true );

                if( !empty( $prop_bed ) ) {
                    $prop_bed = esc_attr( $prop_bed );
                    $prop_bed_label = ($prop_bed > 1 ) ? fave_option('glc_beds', 'Beds') : fave_option('glc_bed', 'Bed');

                    $beds = '<span>'.$prop_bed.'</span> <span class="item-amenities-text">'.$prop_bed_label.'</span> ';
                }
                if( !empty( $prop_bath ) ) {
                    $prop_bath = esc_attr( $prop_bath );
                    $prop_bath_label = ($prop_bath > 1 ) ? fave_option('glc_baths', 'Baths') : fave_option('glc_bath', 'Bath');

                    $baths = '<span>'.$prop_bath.'</span> <span class="item-amenities-text">'.$prop_bath_label.':</span> ';
                }

                $listing_area_size = houzez_get_listing_area_size_saved_searches( $post_id );

                if( !empty( $listing_area_size ) ) {
                    $area = '<span>'.houzez_get_listing_area_size_saved_searches($post_id).'</span> <span>'.houzez_get_listing_size_unit_saved_searches($post_id).'</span>';
                }

                if( !empty( $price_prefix ) ) {
                    $price_prefix = '<span class="price-start">'.$price_prefix.'</span>';
                }

                $thumbnail = houzez_get_image_url('houzez-item-image-1', $post_id);


                $mail_content .= '
                    <div class="email-listings-item">
                        <div class="email-listings-image">
                            <a href="'.get_permalink().'">
                                <div class="status-label">'.houzez_taxonomy_simple('property_status').'</div>
                                <ul class="item-price-wrap">
                                    '.houzez_listing_price_for_saved_searches().'
                                </ul>
                                <img class="img-fluid" src="'.$thumbnail[0].'" alt="image">
                            </a>
                        </div>
                        <!-- email-listings-image -->
                        <div class="email-listings-content">
                            <h2 class="item-title"><a href="'.get_permalink().'">'.get_the_title().'</a></h2>
                            <address class="item-address">'.esc_attr($address).'</address>
                            <ul class="item-amenities">
                                <li class="item-amenities-beds">
                                    '.$beds.' 
                                </li>
                                <li class="item-amenities-baths">
                                    '.$baths.' 
                                </li>
                                <li class="item-amenities-sqft">
                                    '.$area.'
                                </li>
                                <li class="item-amenities-type">
                                    <span class="type-label">'.houzez_taxonomy_simple('property_type').'</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                ';


            endwhile;
            $mail_content .= '</div>';
            $mail_content .= '</div>';
            $mail_content .= '</div>';
            $mail_content .= "\r\n".esc_html__('If you do not wish to be notified anymore please enter your account and delete the saved search.Thank you!', 'houzez');
            $mail_content .= '</div>';
            
        }else{
            $mail_content='';
        }
        wp_reset_postdata();

        $data = array(
            'mail_content' => $mail_content,
            'prop_count'  => $prop_count
        );

        return $data;
    }

endif;