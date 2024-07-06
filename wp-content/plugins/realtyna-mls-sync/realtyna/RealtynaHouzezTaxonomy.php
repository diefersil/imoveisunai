<?php
// Block direct access to the main plugin file.
defined( 'ABSPATH' ) || die( 'Access Denied!' );

/**
 * Hanlde Houzez Taxonomy Data
 * 
 * @author Chris A <chris.a@realtyna.net>
 * 
 * @version 1.0
 */
class RealtynaHouzezTaxonomy {

    /** @var string taxonomy value */
    const TAXONOMY = '';

    /**
     * Add Taxonomy to Post
     * 
     * @author Chris A <chris.a@realtyna.net>
     * 
     * @param string Term Title
     * @param int Post ID
     * @param array array of Parent info
     * 
     * @return bool
     */
    public function import( $value , $postId , $parentInfo = array() ){

        $term = term_exists( $value, static::TAXONOMY );

        if ( 0 === $term || null === $term ) {
    
            $term = wp_insert_term(
                $value,
                static::TAXONOMY,
                array(
                    'slug' => strtolower( str_ireplace( ' ', '-', $value ) )
                )
            );
    
        }
    
        wp_set_post_terms( $postId, $term, static::TAXONOMY, true );

    }

}
?>