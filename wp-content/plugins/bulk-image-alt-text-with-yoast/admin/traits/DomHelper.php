<?php
namespace Pagup\Bialty\Traits;

use Pagup\Bialty\Core\Option;

trait DomHelper {

    /**
     * Sets the alt attribute for an image node if it's empty.
     *
     * Depending on the provided option, the alt attribute is set to
     * the focus keyword, the post title, the image name, or a combination
     * of the previous three, followed by the site title.
     *
     * @param string $option
     * @param \DOMElement $node
     * @param string $img_url
     */
    public function setEmpty($option, $node, $img_url)
    {
        if ( Option::check($option) ) 
        {

            switch ( Option::get($option) ) 
            {
                case Option::get($option) == 'alt_empty_fkw':
                    $node->setAttribute("alt", $this->focus_keyword() . $this->site_title());
                    break;

                case Option::get($option) == 'alt_empty_title':
                    $node->setAttribute("alt", $this->post_title() . $this->site_title());
                    break;

                case Option::get($option) == 'alt_empty_imagename':
                    $node->setAttribute("alt", $this->image_name($img_url) . $this->site_title());
                    break;

                case Option::get($option) == 'alt_empty_both':
                    $node->setAttribute("alt", $this->focus_keyword() . ', ' . $this->post_title() . $this->site_title());
                    break;
            }

        }

    }
    
    /**
     * Sets the alt attribute for an image node if it's not empty.
     *
     * Depending on the provided option, the alt attribute is set to 
     * different combinations of focus keyword, post title, image name, 
     * and site title.
     *
     * @param string $option The option to determine the alt text format.
     * @param \DOMElement $node The DOM element representing the image.
     * @param string $img_url The URL of the image, used for generating the image name.
     */
    public function setNotEmpty($option, $node, $img_url)
    {
        if ( Option::check($option) ) 
        {

            switch ( Option::get($option) ) 
            {
                case Option::get($option) == 'alt_not_empty_fkw':
                    $node->setAttribute("alt", $this->focus_keyword() . $this->site_title());
                    break;

                case Option::get($option) == 'alt_not_empty_title':
                    $node->setAttribute("alt", $this->post_title() . $this->site_title());
                    break;

                case Option::get($option) == 'alt_not_empty_imagename':
                    $node->setAttribute("alt", $this->image_name($img_url) . $this->site_title());
                    break;

                case Option::get($option) == 'alt_not_empty_both':
                    $node->setAttribute("alt", $this->focus_keyword() . ', ' . $this->post_title() . $this->site_title());
                    break;
            }

        }

    }

    /**
     * Sets alt attribute for images in Woocommerce if it's empty.
     *
     * @param string $option
     * @param \DOMElement $node
     * @param string $img_url
     */
    public function setEmptyWoo($option, $node, $img_url)
    {
        if ( Option::check($option) ) 
        {

            switch ( Option::get($option) ) 
            {
                case Option::get($option) == 'woo_alt_empty_fkw':
                    $node->setAttribute("alt", $this->focus_keyword() . $this->site_title());
                    break;

                case Option::get($option) == 'woo_alt_empty_title':
                    $node->setAttribute("alt", $this->post_title() . $this->site_title());
                    break;

                case Option::get($option) == 'woo_alt_empty_imagename':
                    $node->setAttribute("alt", $this->image_name($img_url) . $this->site_title());
                    break;

                case Option::get($option) == 'woo_alt_empty_both':
                    $node->setAttribute("alt", $this->focus_keyword() . ', ' . $this->post_title() . $this->site_title());
                    break;
            }

        }

    }

    /**
     * Sets alt attribute for images in Woocommerce if it's not empty.
     *
     * @param string $option
     * @param \DOMElement $node
     * @param string $img_url
     */
    public function setNotEmptyWoo($option, $node, $img_url)
    {
        if ( Option::check($option) ) 
        {

            switch ( Option::get($option) ) 
            {
                case Option::get($option) == 'woo_alt_not_empty_fkw':
                    $node->setAttribute("alt", $this->focus_keyword() . $this->site_title());
                    break;

                case Option::get($option) == 'woo_alt_not_empty_title':
                    $node->setAttribute("alt", $this->post_title() . $this->site_title());
                    break;

                case Option::get($option) == 'woo_alt_not_empty_imagename':
                    $node->setAttribute("alt", $this->image_name($img_url) . $this->site_title());
                    break;

                case Option::get($option) == 'woo_alt_not_empty_both':
                    $node->setAttribute("alt", $this->focus_keyword() . ', ' . $this->post_title() . $this->site_title());
                    break;
            }

        }

    }

    /**
     * Retrieves the focus keyword for the current post/page using Yoast SEO, Rank Math or All in One SEO.
     *
     * @return string The focus keyword for the current post/page.
     */
    public function focus_keyword()
    {
        global $wpdb;
        $post_id = get_queried_object_id();

        $focus_keyword = "";
        
        if ( class_exists('WPSEO_Meta') ) {

            // define focus keyword for Yoast SEO
            $focus_keyword = \WPSEO_Meta::get_value('focuskw', $post_id);

        }
        
        elseif ( class_exists('RankMath') ) {
    
            // define focus keyword for Rank Math
            $focus_keyword = get_post_meta( $post_id, 'rank_math_focus_keyword', true );

        }

        elseif (function_exists('aioseo') && $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}aioseo_posts'") === "{$wpdb->prefix}aioseo_posts") {

            // Define focus keyword for All in One SEO
            $query = $wpdb->prepare(
                "SELECT keyphrases FROM {$wpdb->prefix}aioseo_posts WHERE post_id = %d",
                $post_id
            );

            $keyphrases = $wpdb->get_var($query);

            if ($keyphrases) {

                $keyphrases_data = json_decode($keyphrases, true);

                if (isset($keyphrases_data['focus']['keyphrase'])) {
                    $focus_keyword = $keyphrases_data['focus']['keyphrase'];
                }
            }
        }

        return $focus_keyword;
    }

    /**
     * Retrieves the title of the current post.
     *
     * @global WP_Post $post The global post object.
     * @return string The title of the current post.
     */
    public function post_title()
    {
        global $post;
        return get_the_title( $post->ID );
    }

    public function image_name($url)
    {
        $path = pathinfo($url);

        // Remove the size part from the filename if it's a thumbnail
        $filename = preg_replace('/-\d+x\d+$/', '', $path['filename']);

        return $this->fileName($filename);
    }

    /**
     * Return the site title as a string, if the option is set. Otherwise an empty string.
     * The site title is prefixed with a comma and a space, so it can be appended to the alt text.
     *
     * @return string The site title, or an empty string if the option is not set.
     */
    public function site_title()
    {
        $site_title = "";
        
        if ( Option::check('add_site_title') ) {
            $site_title = ', ' . get_bloginfo( 'name' );
        }

        return $site_title;
        
    }

    /**
     * Convert a string to title case, suitable for use as a file name.
     * This function cleans up dashes and underscores, converts to spaces, and then
     * capitalizes the first letter of each word.
     *
     * @param string $string The string to convert.
     * @return string The converted string.
     */
    public function fileName($string)
    {
        $string = preg_replace("/[\s-]+/", " ", $string); // clean dashes/whitespaces
        $string = preg_replace("/[_]/", " ", $string); // convert whitespaces/underscore to space
        $string = ucwords($string); // convert first letter of each word to capital
        return $string;
    }

    /**
     * Get the list of blacklist URL's string from Options, converts it to an array, and use the array map function to convert each URL to ID.
     * 
     * @return array
    */
    public function blacklist(): array
    {
        $blacklist = Option::check('blacklist') ? Option::get('blacklist') : [];

        if ( is_array($blacklist) ) {
            return $blacklist;
        }
        
        $urls_array = explode("\n", str_replace("\r", "", $blacklist));

        // Convert URL's to Id's, skipping URLs that don't return an ID
        $ids_array = array();
        foreach ($urls_array as $link) {
            $post_id = url_to_postid($link);
            if ($post_id > 0) {
                $ids_array[] = $post_id;
            }
        }

        return $ids_array;
    }
    
}