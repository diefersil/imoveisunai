<?php

namespace Pagup\Bialty\Controllers;

use Pagup\Bialty\Core\Option;
use Pagup\Bialty\Core\Plugin;
use Pagup\Bialty\Core\Request;
use Pagup\Bialty\Traits\DomHelper;
use Pagup\Bialty\Traits\SettingHelper;
class SettingsController {
    use SettingHelper, DomHelper;
    public function add_settings() {
        add_menu_page(
            __( 'Bulk Image Alt Text Settings', 'bulk-image-alt-text-with-yoast' ),
            __( 'Bulk Image Alt Text', 'bulk-image-alt-text-with-yoast' ),
            'manage_options',
            'bialty',
            array(&$this, 'page'),
            'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2aWV3Qm94PSIwIDAgMjAgMjAiPiAgICA8ZyBmaWxsPSJub25lIj4gICAgICAgIDxwYXRoIGQ9Ik01IDNhMiAyIDAgMCAwLTIgMnYxMGEyIDIgMCAwIDAgMiAyaDQuMjJsLjIxMi0uODQ1Yy4wMTMtLjA1Mi4wMjctLjEwNC4wNDMtLjE1NUg1YTEgMSAwIDAgMS0xLTFWNWExIDEgMCAwIDEgMS0xaDEwYTEgMSAwIDAgMSAxIDF2NC4yMzJjLjMyLS4xMzcuNjU5LS4yMTMgMS0uMjI5VjVhMiAyIDAgMCAwLTItMkg1em00LjUgMTFoMS40NDNsMS0xSDkuNWEuNS41IDAgMCAwIDAgMXptLTItNi43NWEuNzUuNzUgMCAxIDEtMS41IDBhLjc1Ljc1IDAgMCAxIDEuNSAwek02Ljc1IDExYS43NS43NSAwIDEgMCAwLTEuNWEuNzUuNzUgMCAwIDAgMCAxLjV6bTAgM2EuNzUuNzUgMCAxIDAgMC0xLjVhLjc1Ljc1IDAgMCAwIDAgMS41ek05LjUgN2EuNS41IDAgMCAwIDAgMWg0YS41LjUgMCAwIDAgMC0xaC00em0wIDNhLjUuNSAwIDAgMCAwIDFoNGEuNS41IDAgMCAwIDAtMWgtNHptMS40OCA1LjM3N2w0LjgzLTQuODNhMS44NyAxLjg3IDAgMSAxIDIuNjQ0IDIuNjQ2bC00LjgzIDQuODI5YTIuMTk3IDIuMTk3IDAgMCAxLTEuMDIuNTc4bC0xLjQ5OC4zNzRhLjg5Ljg5IDAgMCAxLTEuMDc5LTEuMDc4bC4zNzUtMS40OThjLjA5Ni0uMzg2LjI5Ni0uNzQuNTc4LTEuMDJ6IiBmaWxsPSJjdXJyZW50Q29sb3IiPjwvcGF0aD4gICAgPC9nPjwvc3ZnPg=='
        );
    }

    public function page() {
        // Get list of post types to display as checkbox options
        $post_types = $this->cpts( ['attachment'] );
        $get_options = new Option();
        $options = $get_options::all();
        $blacklist = $this->blacklist();
        $options['blacklist'] = $blacklist;
        // Get blacklisted post IDs
        $blacklisted_posts = ( isset( $options['blacklist'] ) ? (array) $options['blacklist'] : [] );
        // Format blacklisted posts with titles
        $formatted_blacklist = array_map( function ( $post_id ) {
            return [
                'id'    => $post_id,
                'title' => get_the_title( $post_id ),
            ];
        }, $blacklisted_posts );
        // var_dump($options);
        wp_localize_script( 'bialty__main', 'data', array(
            'post_types'       => $post_types,
            'options'          => $options,
            'blacklistedPosts' => $formatted_blacklist,
            'onboarding'       => get_option( 'bialty_tour' ),
            'pro'              => bialty_fs()->can_use_premium_code__premium_only(),
            'plugins'          => $this->installable_plugins(),
            'language'         => get_locale(),
            'nonce'            => wp_create_nonce( 'bialty_nonce' ),
            'purchase_url'     => bialty_fs()->get_upgrade_url(),
            'recommendations'  => $this->recommendations_list(),
        ) );
        if ( BIALTY_PLUGIN_MODE !== "production" ) {
            echo $this->devNotification();
        }
        echo '<div id="bialty__app"></div>';
    }

    public function save_options() {
        // check the nonce
        if ( check_ajax_referer( 'bialty_nonce', 'nonce', false ) == false ) {
            wp_send_json_error( "Invalid nonce", 401 );
            wp_die();
        }
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( "Unauthorized user", 403 );
            wp_die();
        }
        $safe = [
            "alt_empty_fkw",
            "alt_empty_title",
            "alt_empty_imagename",
            "alt_empty_both",
            "alt_not_empty_fkw",
            "alt_not_empty_title",
            "alt_not_empty_imagename",
            "alt_not_empty_both",
            "woo_alt_empty_fkw",
            "woo_alt_empty_title",
            "woo_alt_empty_imagename",
            "woo_alt_empty_both",
            "woo_alt_not_empty_fkw",
            "woo_alt_not_empty_title",
            "woo_alt_not_empty_imagename",
            "woo_alt_not_empty_both",
            "woo_disable_gallery",
            "add_site_title",
            "debug_mode",
            "remove_settings",
            "promo",
            "allow"
        ];
        $options = [
            'post_types'      => array_map( 'sanitize_key', $_POST['post_types'] ),
            'alt_empty'       => Request::safe( $_POST['alt_empty'] ?? '', $safe ),
            'alt_not_empty'   => Request::safe( $_POST['alt_not_empty'] ?? '', $safe ),
            'disable_home'    => Request::safe( $_POST['disable_home'] ?? null, $safe ),
            'debug_mode'      => Request::safe( $_POST['debug_mode'] ?? null, $safe ),
            'remove_settings' => Request::safe( $_POST['remove_settings'] ?? null, $safe ),
        ];
        $result = update_option( 'bialty', $options );
        if ( $result ) {
            wp_send_json_success( [
                'options' => $options,
                'message' => "Saved Successfully",
            ] );
        } else {
            wp_send_json_error( [
                'options' => $options,
                'message' => "Error Saving Options",
            ] );
        }
    }

    /**
     * Handles AJAX search for posts based on a query.
     *
     * Validates nonce and user capabilities before executing a search query 
     * against the database for post titles matching the input query. 
     * Filters results by allowed post types and ensures posts are published.
     *
     * @throws Exception If an error occurs during database query execution.
     * 
     * @return void Outputs a JSON response with the search results or an error message.
     */
    public function search_posts_callback() {
        try {
            if ( !check_ajax_referer( 'bialty_nonce', 'nonce', false ) ) {
                wp_send_json_error( "Invalid nonce", 401 );
                wp_die();
            }
            if ( !current_user_can( 'manage_options' ) ) {
                wp_send_json_error( "Unauthorized user", 403 );
                wp_die();
            }
            $query = ( isset( $_POST['query'] ) ? sanitize_text_field( $_POST['query'] ) : '' );
            $allowed_post_types = ( Option::check( 'post_types' ) ? maybe_unserialize( Option::get( 'post_types' ) ) : ['post', 'page'] );
            global $wpdb;
            // Create placeholders for post types
            $placeholders = array_fill( 0, count( $allowed_post_types ), '%s' );
            $placeholders_string = implode( ',', $placeholders );
            // Prepare the query parameters
            $query_params = array_merge( ['%' . $wpdb->esc_like( $query ) . '%'], $allowed_post_types );
            // Build and execute the query
            $sql = $wpdb->prepare( "SELECT ID, post_title \r\n                FROM {$wpdb->posts} \r\n                WHERE post_title LIKE %s \r\n                AND post_type IN ({$placeholders_string})\r\n                AND post_status = 'publish'\r\n                LIMIT 20", $query_params );
            $results = $wpdb->get_results( $sql );
            if ( is_wp_error( $results ) ) {
                wp_send_json_error( $results->get_error_message(), 500 );
                wp_die();
            }
            $formatted_posts = array_map( function ( $post ) {
                return [
                    'id'    => $post->ID,
                    'title' => $post->post_title,
                ];
            }, $results );
            wp_send_json_success( $formatted_posts );
        } catch ( Exception $e ) {
            wp_send_json_error( $e->getMessage(), 500 );
        }
    }

    public function onboarding() {
        // check the nonce
        if ( check_ajax_referer( 'bialty_nonce', 'nonce', false ) == false ) {
            wp_send_json_error( "Invalid nonce", 401 );
            wp_die();
        }
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( "Unauthorized user", 403 );
            wp_die();
        }
        $closed = ( isset( $_POST['closed'] ) ? $_POST['closed'] === 'true' || $_POST['closed'] === true : false );
        $result = update_option( 'bialty_tour', $closed );
        if ( $result ) {
            wp_send_json_success( [
                'bialty_tour' => get_option( 'bialty_tour' ),
                'message'     => "Tour closed value saved successfully",
            ] );
        } else {
            wp_send_json_error( [
                'bialty_tour' => get_option( 'bialty_tour' ),
                'message'     => "Error Saving Tour closed value",
            ] );
        }
    }

}

$settings = new SettingsController();