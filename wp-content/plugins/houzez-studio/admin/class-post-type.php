<?php
namespace HouzezStudio\admin\post_type;

use HouzezStudio\admin\fieldsManager as FieldManager;

defined( 'ABSPATH' ) || exit;

class Houzez_Studio_Post_Type {

	/**
	 * Version.
	 *
	 * @var string
	 */
	public $version = '1.0.0';


	/**
	 * The single instance of the class.
	 *
	 * @var Houzez_Studio_Post_Type
	 * @since 1.0
	 */
	private static $_instance;

	/**
	 * Main Houzez_Studio_Post_Type Instance.
	 *
	 * Ensures only one instance of Houzez_Studio_Post_Type is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @return Houzez_Studio_Post_Type - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}



	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'houzez-studio' ), '1.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'houzez-studio' ), '1.0' );
	}


	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->cpt_support();

		add_action( 'init', array( $this, 'post_type' ) );
		
		if ( is_admin() ) {
			add_filter( 'manage_fts_builder_posts_columns', array( $this, 'columns_head' ) );
			add_action( 'manage_fts_builder_posts_custom_column', array( $this, 'columns_content' ), 10, 2 );
		}

	}

	/**
     * Custom post type
     *
     * @access public
     * @return void
     */
    public static function post_type() {
    	global $wp;
    	

        $labels = array(
            'name' => __( 'Header & Footer Builder','houzez-studio'),
            'singular_name' => __( 'Header & Footer Builder','houzez-studio' ),
            'add_new_item' => __('Add New Header, Footer','houzez-studio'),
            'edit_item' => __('Edit Header, Footer','houzez-studio'),
            'all_items'     => esc_html__( 'All Header, Footer', 'houzez-studio' ),
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => false,
            'hierarchical' => false,
            'can_export' => true,
            'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
            'menu_icon' => 'dashicons-editor-kitchensink',
            'supports'     => array( 'title', 'thumbnail', 'page-attributes' ),
            'show_in_rest'       => true,
            'rest_base'          => 'fts_studio',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'rewrite'      => array(
				'slug'       => 'houzez-studio',
				'with_front' => false,
				'feeds'      => true,
			),
        );

        register_post_type('fts_builder',$args);
    }

    /**
	 * Add post type support.
	 */
	public function cpt_support() {
		add_post_type_support( 'fts_builder', 'elementor' );
	}


	/**
	 * Add columns for custom post type
	 */
	public function columns_head($columns) {
	    $date_column = $columns['date'];
	    unset($columns['date']);
	    $columns['type'] = __('Type', 'houzez-studio');
	    $columns['display_rules'] = __('Display Rules', 'houzez-studio');
	    $columns['shortcode'] = __('Shortcode', 'houzez-studio');
	    $columns['date'] = $date_column;
	    return $columns;
	}

	/**
	 * Add columns content
	 */
	public function columns_content($column_name, $post_id) {
	    
	    switch ($column_name) {

	        case 'type':
	            $type = get_post_meta($post_id, 'fts_template_type', true);
	            $this->outputColumnHTML("fts-template-type", esc_html($type));
	            break;

	        case 'display_rules':
	            $included = get_post_meta($post_id, 'fts_included_options', true);
	            if (!empty($included)) {
	                echo '<div class="fts-columns-content">';
	                echo '<strong>Display: </strong>';
	                $this->display_rules_markup($included);
	                echo '</div>';
	            }

	            $excluded = get_post_meta($post_id, 'fts_excluded_options', true);
	            if (!empty($excluded)) {
	                echo '<div class="fts-columns-content">';
	                echo '<strong>Exclusion: </strong>';
	                $this->display_rules_markup($excluded);
	                echo '</div>';
	            }
	            break;

	        case 'shortcode':
	            $this->outputColumnHTML("fts-shortcode-wrap", "[fts_template id='" . esc_attr($post_id) . "']", true);
	            break;
	    }
	}

	/**
	 * Generates and outputs HTML markup based on provided display rules for a specific column.
	 * This function iterates over display rules, fetches corresponding labels, and constructs
	 * a comma-separated list to be displayed in the admin column.
	 *
	 * @param array $display_rules An associative array containing the display rules.
	 */

	public function display_rules_markup($display_rules) {
	    $labels = [];

	    // Consolidate the logic for processing 'rule' and 'specific' elements.
	    $rule_types = ['rule', 'specific'];

	    foreach ($rule_types as $rule_type) {
	        if (isset($display_rules[$rule_type]) && is_array($display_rules[$rule_type])) {
	            // Remove 'specifics' from 'rule' type if it exists.
	            if ($rule_type === 'rule') {
	                $index = array_search('specifics', $display_rules[$rule_type]);
	                if ($index !== false) {
	                    unset($display_rules[$rule_type][$index]);
	                }
	            }

	            // Process each rule in the current type.
	            foreach ($display_rules[$rule_type] as $rule) {
	                $label = FieldManager\Favethemes_Field_Manager::derive_label_from_key($rule);
	                if ($label) {
	                    $labels[] = $label;
	                }
	            }
	        }
	    }

	    // Output the combined labels.
	    if (!empty($labels)) {
	        echo esc_html(join(', ', $labels));
	    }
	}

	private function outputColumnHTML($class, $text, $is_code = false) {
	    echo "<span class=\"" . esc_attr($class) . "\">";

	    if ($is_code) {
	        echo "<input type=\"text\" onfocus=\"this.select();\" readonly=\"readonly\" value=\"" . esc_attr($text) . "\" class=\"code\">";
	    } else {
	        // Use an associative array for cleaner code and easier updates
	        $textMappings = array(
	            'tmp_header'        => 'Header',
	            'tmp_footer'        => 'Footer',
	            'tmp_before_header' => 'Before Header',
	            'tmp_after_header'  => 'After Header',
	            'tmp_before_footer' => 'Before Footer',
	            'tmp_after_footer'  => 'After Footer',
	            'tmp_megamenu'      => 'Mega Menu',
	            'tmp_custom_block'  => 'Custom Block',
	        );

	        // Check if the text has a mapped value and update it accordingly
	        if (array_key_exists($text, $textMappings)) {
	            $text = $textMappings[$text];
	        }

	        echo esc_html($text);
	    }
	    echo "</span>";
	}


}
Houzez_Studio_Post_Type::instance();