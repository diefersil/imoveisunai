<?php
/**
 * Template Nam1: Testing
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 16/12/15
 * Time: 3:27 PM
 */
get_header();

/*$url = "https://api.walkscore.com/score?format=json&
address=1119%8th%20Avenue%20Seattle%20WA%2098101&lat=47.6085&
lon=-122.3295&transit=1&bike=1&wsapikey=6acf594b7b8899d0954d11fb38b90e3f";

$response = wp_safe_remote_get( $url, $args = array() );

if ( is_wp_error( $response ) ) {
    return false;
}

if ( ! empty( $response['body'] ) && is_ssl() ) {
    $response['body'] = str_replace( 'http:', 'https:', $response['body'] );
} elseif ( is_ssl() ) {
    $response = str_replace( 'http:', 'https:', $response );
}

print_r($response['body']);*/

get_footer(); ?>