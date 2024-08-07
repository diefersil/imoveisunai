<?php
/*
Plugin Name: Favethemes Insights
Plugin URI:  http://themeforest.net/user/favethemes
Description: Add insights for favethemes themes
Version:     1.2.9
Author:      Favethemes
Author URI:  http://themeforest.net/user/favethemes
*/


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Favethemes_Insights' ) ) :

    final class Favethemes_Insights {

        /**
         * Plugin's current version
         *
         * @var string
         */
        public $version;

        /**
         * Plugin Name
         *
         * @var string
         */
        public $plugin_name;

        /**
         * Plugin's instance.
         *
         * @var Favethemes_Insights
         */
        protected static $_instance;

        /**
         * Constructor function.
         */
        public function __construct() {

            $this->plugin_name = 'favethemes-insights';
            $this->version     = '1.0.0';

            $this->define_constants();

            $this->init_hooks();

            $this->initialize_admin_menu();

            $this->include_files();

            do_action( 'fave_insights_loaded' );  // Favethemes Insights plugin loaded action hook
        }

        /**
         * Provides instance.
         */
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Defines constants.
         */
        protected function define_constants() {

            if ( ! defined( 'FAVE_INSIGHTS_VERSION' ) ) {
                define( 'FAVE_INSIGHTS_VERSION', $this->version );
            }

            if ( ! defined( 'FAVE_INSIGHTS_PLUGIN_FILE' ) ) {
                define( 'FAVE_INSIGHTS_PLUGIN_FILE', __FILE__ );
            }

            if ( ! defined( 'FAVE_INSIGHTS_DIR' ) ) {
                define( 'FAVE_INSIGHTS_DIR', plugin_dir_path( __FILE__ ) );
            }

            if ( ! defined( 'FAVE_INSIGHTS_URL' ) ) {
                define( 'FAVE_INSIGHTS_URL', plugin_dir_url( __FILE__ ) );
            }

            if ( ! defined( 'FAVE_INSIGHTS_BASENAME' ) ) {
                define( 'FAVE_INSIGHTS_BASENAME', plugin_basename( __FILE__ ) );
            }

        }


        /**
         * Functions
         */
        public function include_files() {
            include_once( FAVE_INSIGHTS_DIR . 'includes/cookie.php' ); 
            include_once( FAVE_INSIGHTS_DIR . 'includes/visitor.php' ); 
            include_once( FAVE_INSIGHTS_DIR . 'includes/visits.php' ); 
            include_once( FAVE_INSIGHTS_DIR . 'includes/insights.php' ); 
        }


        /**
         * Admin menu.
         */
        public function initialize_admin_menu() {
            
        }

        /**
         * Initialize hooks.
         */
        public function init_hooks() {
            add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
            add_action( 'favethemes_insights', array( $this, 'favethemes_clear_insights_data' ) );
            register_activation_hook( __FILE__, array( $this, 'plugin_activation' ) );
            register_deactivation_hook( __FILE__, array( $this, 'plugin_deactivate' ) );
        }

        /**
         * clear insights data more then x days
         */
        public function favethemes_clear_insights_data() {
            $days = get_option('houzez_insights_removal');
            $custom_days = get_option('houzez_custom_days');

            $runQuery = true;

            if( $days == '7days' ) {
                $number_of_days = 7;
            } else if ( $days == '14days' ) {
                $number_of_days = 14;
            } else if ( $days == '30days' ) {
                $number_of_days = 30;
            } else if ( $days == '60days' ) {
                $number_of_days = 60;
            } else if ( $days == 'custom' ) {
                if( !empty( $custom_days ) ) {
                    $number_of_days = $custom_days;
                }
            } else if( $days == 'keep' ) {
                $runQuery = false;
            }

            if( $runQuery ) {
                //'delete from DemoTable where UserMessageSentDate < now() - interval 30 DAY;';
                global $wpdb;
                $table_name = $wpdb->prefix . 'favethemes_insights';
                
                $wpdb->query( 
                    $wpdb->prepare( 
                        "DELETE FROM {$table_name}
                         WHERE time < now() - interval %d DAY
                        ",
                            $number_of_days
                        )
                );
            }
        }

        /**
         * Load text domain for translation.
         */
        public function load_plugin_textdomain() {
            load_plugin_textdomain( 'favethemes-insights', false, dirname( FAVE_INSIGHTS_BASENAME ) . '/languages' );
        }

        /**
         * plugin activation
         */
        public function plugin_activation() {
            global $wpdb;

            $table_name = $wpdb->prefix . 'favethemes_insights';
            $sql = "CREATE TABLE $table_name (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                listing_id bigint(20) unsigned NOT NULL,
                time datetime NOT NULL,
                ip_address varchar(35),
                unique_identifier varchar(70) NOT NULL,
                referral_url varchar(512),
                referral_domain varchar(256),
                platform varchar(35),
                device varchar(35),
                browser varchar(35),
                http_user_agent varchar(512),
                language varchar(35),
                country_code varchar(35),
                country varchar(35),
                city varchar(64),
                PRIMARY KEY  (id),
                INDEX referral_domain (referral_domain),
                INDEX referral_url (referral_url),
                INDEX unique_identifier (unique_identifier),
                INDEX time (time)
            );";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta( $sql );


            if (!wp_next_scheduled('favethemes_insights')) {
                wp_schedule_event(time(), 'daily', 'favethemes_insights');
            }
            
        }


        /**
         * plugin de-activation
         */
        public function plugin_deactivate() {

        }


        /**
         * Cloning is forbidden.
         */
        public function __clone() {
            _doing_it_wrong( __FUNCTION__, __( 'Not good; huh?', 'favethemes-insights' ), ERE_VERSION );
        }

        /**
         * Unserializing instances of this class is forbidden.
         */
        public function __wakeup() {
            _doing_it_wrong( __FUNCTION__, __( 'Not good; huh?', 'favethemes-insights' ), ERE_VERSION );
        }

    }

endif; // End class_exists check.


/**
 * Main instance of Favethemes_Insights.
 *
 * Returns the main instance of Favethemes_Insights to prevent the need to use globals.
 *
 * @return Favethemes_Insights
 */
function FTI() {
    return Favethemes_Insights::instance();
}
FTI();