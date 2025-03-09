<?php

namespace Pagup\Bialty\Controllers;

use Pagup\Bialty\Core\Option;
use Pagup\Bialty\Traits\DomHelper;
class DomController {
    use DomHelper;
    public function __construct() {
        add_filter( 'the_content', array(&$this, 'bialty'), 99999 );
        add_filter( 'woocommerce_single_product_image_thumbnail_html', array(&$this, 'bialty_woocommerce_gallery'), 99999 );
        add_filter( 'post_thumbnail_html', array(&$this, 'bialty'), 99999 );
        add_filter(
            'woocommerce_related_products_args',
            array(&$this, 'bialty_prepare_related_products'),
            10,
            1
        );
        add_action(
            'woocommerce_before_template_part',
            array(&$this, 'start_related_products_buffer'),
            10,
            4
        );
        add_action(
            'woocommerce_after_template_part',
            array(&$this, 'end_related_products_buffer'),
            10,
            4
        );
    }

    public function bialty( $content ) {
        // global $post;
        // Disable Bialty on Homepage if option is enabled
        if ( Option::check( 'disable_home' ) && (is_front_page() || is_home()) ) {
            return $content;
        }
        // Disable Bialty if URL exist in Blacklist
        // $post_id = $post->IDs;
        $post_id = get_queried_object_id();
        if ( !empty( $post_id ) && is_numeric( $post_id ) && in_array( (int) $post_id, $this->blacklist() ) ) {
            return $content;
        }
        // Check and Disable Bialty if page is edited by Beaver Builder.
        if ( isset( $_GET['fl_builder'] ) ) {
            return $content;
        }
        $dom = new \DOMDocument('1.0', 'UTF-8');
        if ( Option::check( 'debug_mode' ) ) {
            if ( !empty( $content ) ) {
                @$dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
            } else {
                // Handle the case where $content is empty
                error_log( 'Error: $content is empty in debug mode' );
            }
        } else {
            if ( !empty( $content ) ) {
                @$dom->loadHTML( mb_convert_encoding( "<div class='bialty-container'>{$content}</div>", 'HTML-ENTITIES', 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
            } else {
                // Handle the case where $content is empty
                error_log( 'Error: $content is empty' );
            }
        }
        $post_types = ( Option::check( 'post_types' ) ? array_intersect( Option::get( 'post_types' ), ['post', 'page'] ) : ['post', 'page'] );
        $html = new \DOMXPath($dom);
        foreach ( $html->query( "//img" ) as $node ) {
            // Return image URL
            $img_url = $node->getAttribute( "src" );
            // Set alt for Post & Pages
            if ( is_singular( $post_types ) ) {
                if ( empty( $node->getAttribute( 'alt' ) ) ) {
                    if ( Option::check( 'alt_empty' ) ) {
                        $this->setEmpty( 'alt_empty', $node, $img_url );
                    }
                } else {
                    if ( Option::check( 'alt_not_empty' ) ) {
                        $this->setNotEmpty( 'alt_not_empty', $node, $img_url );
                    }
                }
                // Set custom keyword for all alt tags
                if ( Option::post_meta( 'use_bialty_alt' ) == true && !empty( Option::post_meta( 'bialty_cs_alt' ) ) ) {
                    $node->setAttribute( "alt", Option::post_meta( 'bialty_cs_alt' ) );
                }
            }
        }
        // Set alt for Post/Pages
        if ( is_singular( $post_types ) ) {
            if ( empty( Option::post_meta( 'disable_bialty' ) ) ) {
                $content = $dom->saveHtml();
            }
        }
        return $content;
    }

    public function bialty_woocommerce_gallery( $content ) {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        @$dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
        $html = new \DOMXPath($dom);
        return $content;
    }

    public function bialty_prepare_related_products( $args ) {
        // Flag that we're in related products context
        $GLOBALS['bialty_in_related'] = true;
        return $args;
    }

    public function start_related_products_buffer(
        $template_name,
        $template_path,
        $located,
        $args
    ) {
        if ( $template_name === 'single-product/related.php' ) {
            ob_start();
        }
    }

    public function end_related_products_buffer(
        $template_name,
        $template_path,
        $located,
        $args
    ) {
        if ( $template_name === 'single-product/related.php' ) {
            $html = ob_get_clean();
            echo $html;
            return;
            $dom = new \DOMDocument('1.0', 'UTF-8');
            if ( Option::check( 'debug_mode' ) ) {
                @$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
            } else {
                @$dom->loadHTML( mb_convert_encoding( "<div class='bialty-container'>{$html}</div>", 'HTML-ENTITIES', 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
            }
            $xpath = new \DOMXPath($dom);
            // Find all product sections
            $products = $xpath->query( "//section[contains(@class, 'product')]" );
            foreach ( $products as $product ) {
                // Get product ID from the data attribute
                $product_id = $product->getAttribute( 'data-product_id' );
                // Get product title
                $product_title = get_the_title( $product_id );
                // Find images within this product section
                $images = $xpath->query( ".//img", $product );
                foreach ( $images as $node ) {
                    $img_url = $node->getAttribute( "src" );
                    if ( empty( $node->getAttribute( 'alt' ) ) ) {
                        if ( Option::check( 'woo_alt_empty' ) ) {
                            // Override the alt tag with this product's title
                            $node->setAttribute( "alt", $product_title . $this->site_title() );
                        }
                    } else {
                        if ( Option::check( 'woo_alt_not_empty' ) ) {
                            // Override existing alt tag with this product's title
                            $node->setAttribute( "alt", $product_title . $this->site_title() );
                        }
                    }
                }
            }
            echo $dom->saveHTML();
            unset($GLOBALS['bialty_in_related']);
        }
    }

    /**
     * Register a custom filter to apply Bialty alt text functionality
     * 
     * @param string $filter_name The name of the filter to hook into
     * @param int $priority Priority of the filter (default: 99999)
     * @return void
     */
    public function register_custom_filter( $filter_name, $priority = 99999 ) {
        // Add the filter using the main bialty method
        add_filter( $filter_name, array($this, 'bialty'), $priority );
    }

    /**
     * Register multiple custom filters at once
     * 
     * @param array $filters Array of filter names or arrays with [name, priority]
     * @return void
     */
    public function register_custom_filters( $filters ) {
        foreach ( $filters as $filter ) {
            if ( is_array( $filter ) ) {
                $this->register_custom_filter( $filter[0], $filter[1] ?? 99999 );
            } else {
                $this->register_custom_filter( $filter );
            }
        }
    }

}

$DomController = new DomController();