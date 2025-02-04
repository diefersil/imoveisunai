<?php
/**
 * Plugin Name: Cookie Notice and Consent Banner
 * Description: Cookie Notice or Consent Banner as Required by Privacy Laws (GDPR & CCPA). Easily Customizable to Fit Your Design.
 * Version: 1.7.6
 * Author: GDPR
 * Author URI: https://gdprinfo.eu
 * Text Domain: cookie-notice-and-consent-banner
 * Domain Path: /languages
 *
 * @package Cookie Notice & Consent Banner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'CNCB_VERSION', '1.7.6' );
define( 'CNCB_PREFIX', 'cncb' );
define( 'CNCB_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'CNCB_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );

if ( ! class_exists( 'CNCB_Main' ) ) :
	/**
	 * Main Class.
	 *
	 * @since 1.0.0
	 */
	final class CNCB_Main {
		/**
		 * This plugin's instance.
		 *
		 * @var CNCB_Main
		 * @since 1.0.0
		 */
		private static $instance;

		/**
		 * Main CNCB_Main Instance.
		 *
		 * Insures that only one instance of CNCB_Main exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0.0
		 * @static
		 * @return object|CNCB_Main The one true CNCB_Main
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof CNCB_Main ) ) {
				self::$instance = new CNCB_Main();
				self::$instance->init();
				self::$instance->includes();
			}
			return self::$instance;
		}

		/**
		 * Throw error on object clone.
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @since 1.0.0
		 * @access protected
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Something went wrong.', 'cookie-notice-and-consent-banner' ), '1.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @since 1.0.0
		 * @access protected
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Something went wrong.', 'cookie-notice-and-consent-banner' ), '1.0' );
		}

		/**
		 * Include required files.
		 *
		 * @access private
		 * @since 1.0.0
		 * @return void
		 */
		private function includes() {
			/**
			 * Load helper class
			 */
			require_once CNCB_PATH . 'includes/helpers/class-cncb-banner-helper.php';

			/**
			 * Load all Customizer Custom Controls
			 */
			require_once CNCB_PATH . 'includes/customizer/custom-controls.php';

			/**
			 * Load Customizer Settings
			 */
			require_once CNCB_PATH . 'includes/customizer/class-cncb-customizer.php';

			if ( is_admin() ) {
				/**
				 * Load all Admin Settings
				 */
				require_once CNCB_PATH . 'includes/admin/class-cncb-admin.php';

				/**
				 * Load Deactivate Popup
				 */
				require_once CNCB_PATH . 'includes/admin/class-cncb-deactivate-popup.php';
			}
		}

		/**
		 * Load actions
		 *
		 * @return void
		 */
		private function init() {
			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ), 99 );
			add_action( 'wp_enqueue_scripts', array( $this, 'load_cncb_banner' ) );
			add_action( 'wp_head', array( $this, 'print_header_scripts' ) );
			add_action( 'wp_footer', array( $this, 'print_body_scripts' ) );
			add_action( 'wp_footer', array( $this, 'output_customize_custom_css' ) );
            add_shortcode( 'revoke_consent', array( $this, 'add_revoke_shortcode' ) );
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access public
		 * @since 1.0.0
		 * @return void
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'cookie-notice-and-consent-banner', false, basename( CNCB_PATH ) . '/languages' );
		}

		/**
		 * Loads the plugin js file.
		 *
		 * @access public
		 * @since 1.0.0
		 * @return void
		 */
		public function load_cncb_banner() {

            $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            $is_visual_builder = false;

            if( strpos($actual_link, 'PageSpeed') !== false || strpos($actual_link, 'elementor-preview') !== false || strpos($actual_link, 'vcv-editable') !== false ) {
                $is_visual_builder = true;
            }

			wp_enqueue_script(
				'cncb_banner',
				CNCB_URI . '/js/cookiebanner.js',
				array(),
				CNCB_VERSION,
				'true'
			);

			if ( get_option( 'cncb_show_banner', 'on' ) === 'on' && ! is_customize_preview() && ! $is_visual_builder) {
				wp_register_script(
					'cncb_banner_init',
					CNCB_URI . '/js/cookiebanner-init.js',
					array( 'cncb_banner' ),
					CNCB_VERSION,
					'true'
				);
				wp_localize_script(
					'cncb_banner_init',
					'cncb_plugin_object',
					CNCB_Banner_Helper::get_options_data_array()
				);
				wp_enqueue_script( 'cncb_banner_init' );

			}
		}

		/**
		 * Check if cookies are accepted.
		 *
		 * @return bool
		 */
		public static function cookies_accepted() {
			$is_cookie = isset( $_COOKIE['cookie-banner'] ) && '1' === $_COOKIE['cookie-banner'];
			return apply_filters( 'cncb_is_cookie_accepted', $is_cookie );
		}

		/**
		 * Outputs Additional CSS to site footer.
		 *
		 * @access public
		 * @since 1.0.0
		 * @return void
		 */
		public function output_customize_custom_css() {
			$css = get_option( 'cncb_custom_css', '' );

			if ( '' === $css ) {
				return;
			}

			echo '<style type="text/css">' . esc_js( $css ) . '</style>';
		}

		/**
		 * Print non functional JavaScript in head.
		 *
		 * @return mixed
		 */
		public function print_header_scripts() {
			if ( $this->cookies_accepted() ) {
				$scripts = apply_filters( 'cncb_refuse_code_scripts_html', html_entity_decode( trim( get_option( 'cncb_refuse_code' ) ) ) );

				if ( ! empty( $scripts ) ) {
					echo $scripts;
				}
			}
		}

        /**
         * Print non functional JavaScript in body.
         *
         * @return mixed
         */
        public function print_body_scripts() {
            if ( $this->cookies_accepted() ) {
                $scripts = apply_filters( 'cncb_refuse_code_body_scripts_html', html_entity_decode( trim( get_option( 'cncb_refuse_code_body' ) ) ) );

                if ( ! empty( $scripts ) ) {
                    echo $scripts;
                }
            }
        }

        /**
         * Adding shortcode with link output, that revokes cookies
         *
         * @param $atts
         * @return mixed
         */
        public function add_revoke_shortcode( $atts ){

            $text = 'Revoke Consent';

            if(isset($atts['text'])) {
                $text = $atts['text'];
            }
            return '<a href="#" class="cncb-js-restore">'. $text .'</a>';

        }
	}
endif;

register_activation_hook( __FILE__, 'cncb_admin_notice_activation_hook' );

function cncb_admin_notice_activation_hook() {
    set_transient( 'cncb_admin_notice', 1, 0 );
}

/**
 * The main function for that returns CNCB_Main
 *
 * The main function responsible for returning the one true CNCB_Main
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $cncbCookieBanner = CNCB_Main(); ?>
 *
 * @since 1.0.0
 * @return object|CNCB_Main The one true CNCB_Main Instance.
 */
function cncb_init() {
	return CNCB_Main::instance();
}

// Get the plugin running. Load on plugins_loaded action to avoid issue on multisite.
if ( function_exists( 'is_multisite' ) && is_multisite() ) {
	add_action( 'plugins_loaded', 'cncb_init', 90 );
} else {
	cncb_init();
}
