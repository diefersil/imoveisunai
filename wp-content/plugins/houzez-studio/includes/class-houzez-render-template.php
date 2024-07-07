<?php
namespace HouzezStudio;

use HouzezStudio\admin\fieldsManager as FieldManager;

defined( 'ABSPATH' ) || exit;

class FTS_Render_Template {

	/**
	 * FTS_Render_Template version.
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * post id
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var int
	 */
	public $post_id;

	/**
	 * post type
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var string
	 */
	public $post_type;

	/**
	 * The single instance of the class.
	 *
	 * @var FTS_Render_Template
	 * @since 1.0
	 */
	public static $_instance;

	/**
	 * Main FTS_Render_Template Instance.
	 *
	 * Ensures only one instance of FTS_Render_Template is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @return FTS_Render_Template - Main instance.
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

		add_action( 'houzez_header_studio', array( $this, 'render_header' ), 10 );
		add_action( 'houzez_before_header', array( $this, 'render_before_header' ), 10 );
		add_action( 'houzez_after_header', array( $this, 'render_after_header' ), 10 );

		add_action( 'houzez_footer_studio', array( $this, 'render_footer' ), 10 );
		add_action( 'houzez_before_footer', array( $this, 'render_before_footer' ), 10 );
		add_action( 'houzez_after_footer', array( $this, 'render_after_footer' ), 10 );
		
	}

	public function single_template( $single_template ) {
		if ( 'fts_builder' == get_post_type() ) { // phpcs:ignore
			$single_template = FTS_DIR_PATH . '/templates/render-template.php';
		}

		return $single_template;
	}

	/**
	 * Retrieve the header.
	 */
	public function render_header() {

		fts_render_header();
	}

	/**
	 * Retrieve the before header.
	 */
	public function render_before_header() {

		fts_render_before_header();
	}

	/**
	 * Retrieve the after header.
	 */
	public function render_after_header() {

		fts_render_after_header();
	}

	/**
	 * Retrieve the footer.
	 */
	public function render_footer() {

		fts_render_footer();
	}

	/**
	 * Retrieve the before footer.
	 */
	public function render_before_footer() {

		fts_render_before_footer();
	}

	/**
	 * Retrieve the after footer.
	 */
	public function render_after_footer() {

		fts_render_after_footer();
	}


	/**
	 * Retrieves plugin settings based on the provided option name.
	 *
	 * @param string $setting Option name.
	 * @param mixed  $default Default value if the option is not set.
	 *
	 * @return mixed Setting value or default value.
	 */
	public function fetch_plugin_settings($setting = '', $default = '') {
	    if (in_array($setting, ['tmp_header', 'tmp_before_header', 'tmp_after_header', 'tmp_footer', 'tmp_before_footer', 'tmp_after_footer', 'tmp_megamenu', 'tmp_custom_block'])) {
	        $templateId = $this->fetch_template_id($setting);
	        return apply_filters("fts_fetch_plugin_settings_{$setting}", $templateId, $default);
	    }

	    return $default;
	}

	/**
	 * Fetches the template ID based on the specified type.
	 *
	 * @param string $type The type of template (e.g., header, footer).
	 *
	 * @return mixed Template ID if found, else returns an empty string.
	 */
	public static function fetch_template_id($type) {
	    $options = [
	        'included'  => 'fts_included_options',
	        'exclusion' => 'fts_excluded_options',
	    ];

	    $templates = FieldManager\Favethemes_Field_Manager::instance()->fetch_posts_by_criteria('fts_builder', $options);

	    foreach ($templates as $template) {
	        if (self::is_matching_template($template['id'], $type)) {
	            return $template['id'];
	        }
	    }

	    return '';
	}

	/**
	 * Checks if a template matches the specified type and language settings.
	 *
	 * @param int    $templateId The ID of the template.
	 * @param string $type       The type of the template.
	 *
	 * @return bool True if it matches, false otherwise.
	 */
	private static function is_matching_template($templateId, $type) {
	    if (get_post_meta(absint($templateId), 'fts_template_type', true) !== $type) {
	        return false;
	    }

	    if (function_exists('pll_current_language') && pll_current_language('slug') !== pll_get_post_language($templateId, 'slug')) {
	        return false;
	    }

	    return true;
	}

}

FTS_Render_Template::instance();

