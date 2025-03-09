<?php
/**
 * Init CNCB_Admin class.
 *
 * @file
 *
 * @package Cookie Notice & Consent Banner
 */

if ( ! class_exists( 'CNCB_Admin' ) ) :
	/**
	 * CNCB_Admin Class.
	 *
	 * @since 1.0.0
	 */
	class CNCB_Admin {

		/**
		 * CNCB_Admin
		 *
		 * @var $instance
		 **/
		private static $instance = null;

		/**
		 * Menu position in admin
		 *
		 * @var $menu_pos
		 **/
		public $menu_pos = 78;

		/**
		 * Menu slug in admin. Used as "key" in menus
		 *
		 * @var $menu_slug
		 **/
		public $menu_slug = 'cncb_options';

        /**
         * Google endpoint which collect data form popup
         *
         * @var string
         **/
        private $google_endpoint = 'https://script.google.com/macros/s/AKfycbyK4G37Z08J-EZxrAm0tDHQ559gYAs-GAal1z7fuyeDsefKCvY/exec';

		/**
		 * Create $instance
		 *
		 * @return object
		 *
		 * @access public
		 */
		public static function init() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new CNCB_Admin();
			}
			return self::$instance;
		}
		/**
		 * Define menu in admin
		 **/
		private function __construct() {
			add_action( 'plugins_loaded', array( $this, 'register_settings' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu_add_external_links_as_submenu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_settings_scripts' ) );
            add_filter( 'plugin_action_links_cookie-notice-and-consent-banner/class-cncb-main.php' , array( $this, 'cncb_add_settings_url'), 9, 1 );
            add_action( 'wp_ajax_cncb_feature_feedback', array( $this, 'ajax_cncb_feature_feedback' ) );
            add_action( 'wp_ajax_cncb_disable_notice', array( $this, 'ajax_cncb_disable_notice' ) );
            add_action( 'admin_notices', array( $this, 'cncb_admin_notice' ) );
		}

        /**
         * Show admin notice after plugin activation
         *
         * @since 1.6.8
         * @access public
         */
        function cncb_admin_notice() {

            if(!get_transient('cncb_admin_notice')) {
                return;
            }

            $reload_times = intval(get_transient( 'cncb_admin_notice' ));

            /* Check transient, if available display notice */
            if( $reload_times < 4 ) {
                ?>
                <div id="message" class="cncb-notice notice notice-info">
                    <h3><?php esc_html_e( 'Cookie Notice and Consent Banner', 'cookie-notice-and-consent-banner' ); ?></h3>
                    <a href="<?php echo esc_url( $this->get_customizer_panel_url() ); ?>" class="button blue blue-filled"><?php esc_html_e( 'Customize Design', 'cookie-notice-and-consent-banner' ); ?></a>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=cncb_options' ) ); ?>" class="button blue"><?php esc_html_e( 'Settings', 'cookie-notice-and-consent-banner' ); ?></a>
                    <button type="button" class="cncb-notice-dismiss"><span class="cncb-screen-reader-text">Dismiss this notice.</span></button>
                </div>
                <?php

                set_transient('cncb_admin_notice', $reload_times + 1, 0);

            }else {
                delete_transient('cncb_admin_notice');
            }
        }

        /**
         * Disable admin notice
         *
         * @since 1.6.8
         * @access public
         */
        function ajax_cncb_disable_notice() {
            delete_transient( 'cncb_admin_notice' );
            wp_die();
        }

		/**
		 * Enqueue settings scripts and styles.
		 *
		 * Registers the scripts / styles and enqueues them.
		 *
		 * @since 1.4.0
		 * @access public
		 */
		public function enqueue_settings_scripts() {
			$min = CNCB_Banner_Helper::get_min_prefix();

			wp_enqueue_style( 'cncb-admin-settings-css', CNCB_URI . '/css/admin-settings' . $min . '.css', array(), CNCB_VERSION, 'all' );
			wp_enqueue_script( 'cncb-admin-settings-js', CNCB_URI . '/js/admin-settings' . $min . '.js', array('jquery'), CNCB_VERSION, 'true' );
		}
		/**
		 * Generate customizer panel url
		 *
		 * @return string
		 */
		private function get_customizer_panel_url() {
			return CNCB_Banner_Helper::get_customizer_panel_url();
		}
		/**
		 * Register admin settings
		 *
		 * @return void
		 */
		public function register_settings() {
			add_option( 'cncb_show_banner', 'on' );
			add_option( 'cncb_by_scroll', 'off' );
			add_option( 'cncb_by_click', 'off' );
			add_option( 'cncb_by_delay', 'off' );
			add_option( 'cncb_refreshonallow', 'off' );
			add_option( 'cncb_by_scroll_value', '100', '', 'yes' );
			add_option( 'cncb_by_delay_value', '10000', '', 'yes' );
			add_option( 'cncb_refuse_code' );
			add_option( 'cncb_refuse_code_body' );
			register_setting( 'cncb_options_group', 'cncb_show_banner' );
			register_setting( 'cncb_options_group', 'cncb_by_scroll' );
			register_setting( 'cncb_options_group', 'cncb_by_delay' );
            register_setting( 'cncb_options_group', 'cncb_refreshonallow' );
			register_setting( 'cncb_options_group', 'cncb_by_click' );
			register_setting( 'cncb_options_group', 'cncb_refuse_code' );
			register_setting( 'cncb_options_group', 'cncb_refuse_code_body' );
			register_setting( 'cncb_options_group', 'cncb_by_scroll_value' );
			register_setting( 'cncb_options_group', 'cncb_by_delay_value' );
		}
		/**
		 * Create submenu link
		 *
		 * @return void
		 */
		public function admin_menu_add_external_links_as_submenu() {
			global $submenu;

			add_menu_page(
                esc_html__( 'Cookie Consent', 'cookie-notice-and-consent-banner' ),
                esc_html__( 'Cookie Consent', 'cookie-notice-and-consent-banner' ),
				'manage_options',
				$this->menu_slug,
				array( $this, 'options_page' ),
				'dashicons-shield',
				$this->menu_pos
			);
      
      if (current_user_can('manage_options')) {
        add_submenu_page( 'cncb_options', '', '', 'manage_options', 'cncb_manage_options' );
            
        if (isset($submenu[$this->menu_slug])) {
          // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
          $submenu[ $this->menu_slug ][0][0] = esc_html__( 'Settings', 'cookie-notice-and-consent-banner' );
              // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
          $submenu[ $this->menu_slug ][1] = array( esc_html__( 'Customize Design', 'cookie-notice-and-consent-banner' ), 'manage_options', $this->get_customizer_panel_url() );
        }
      }
    }

		/**
		 * Render admin option page
		 *
		 * @return void
		 */
		public function options_page() {
			?>
			<div class="wrap">
                <h1><?php esc_html_e( 'Cookie Notice & Consent Banner', 'cookie-notice-and-consent-banner' ); ?></h1>
                <div class="container">
                    <form method="post" action="options.php" class="container-left">
                        <?php settings_fields( 'cncb_options_group' ); ?>
                        <table class="form-table cncb-form-table">
                            <tbody>
                                <tr>
                                    <th>
                                        <label for="cncb_show_banner"><?php esc_html_e( 'Display notice', 'cookie-notice-and-consent-banner' ); ?></label>
                                    </th>
                                    <td>
                                        <input name="cncb_show_banner" id="cncb_show_banner_yes" type="radio" class="radio-input" value="on" <?php echo ( get_option( 'cncb_show_banner', 'on') === 'on' ) ? 'checked' : ''; ?>>
                                        <label for="cncb_show_banner_yes" class="radio-label"><?php esc_html_e( 'Yes', 'cookie-notice-and-consent-banner' ); ?></label>

                                        <input name="cncb_show_banner" id="cncb_show_banner_no" type="radio" class="radio-input" value="off" <?php echo ( get_option( 'cncb_show_banner', 'on') === 'off' ) ? 'checked' : ''; ?>>
                                        <label for="cncb_show_banner_no" class="radio-label"><?php esc_html_e( 'No', 'cookie-notice-and-consent-banner' ); ?></label>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php esc_html_e( 'Design', 'cookie-notice-and-consent-banner' ); ?>
                                    </th>
                                    <td>
                                        <a href="<?php echo esc_url( $this->get_customizer_panel_url() ); ?>" id="cncb_go_customizer" class="button"><?php esc_html_e( 'Customize Design', 'cookie-notice-and-consent-banner' ); ?></a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="cncb_refuse_code"><?php esc_html_e( 'Script blocking', 'cookie-notice-and-consent-banner' ); ?></label>
                                    </th>
                                    <td>
                                        <h2 class="nav-tab-wrapper">
                                            <a href="#head-tab" class="nav-tab nav-tab-active cncb-nav-tab-js">Head</a>
                                            <a href="#body-tab" class="nav-tab cncb-nav-tab-js">Body</a>
                                        </h2>
                                        <div id="head-tab" class="head-tab content-tab">
                                            <p class="description"><?php esc_html_e( 'This code will be used in the site header before closing the &#60;head&#62; tag.', 'cookie-notice-and-consent-banner' ); ?></p>
                                            <textarea name="cncb_refuse_code" id="cncb_refuse_code" class="large-text" cols="50" rows="10"><?php echo html_entity_decode( trim( get_option( 'cncb_refuse_code' ) ) ); ?></textarea>
                                        </div>
                                        <div id="body-tab" class="body-tab content-tab" style="display: none;">
                                            <p class="description"><?php esc_html_e( 'This code will be used in the site footer before closing the &#60;body&#62; tag.', 'cookie-notice-and-consent-banner' ); ?></p>
                                            <textarea name="cncb_refuse_code_body" id="cncb_refuse_code_body" class="large-text" cols="50" rows="10"><?php echo html_entity_decode( trim( get_option( 'cncb_refuse_code_body' ) ) ); ?></textarea>
                                        </div>
                                        <p class="description"><?php esc_html_e( 'Enter non-functional cookies Javascript code here (e.g. Google Analitycs) to be used after the consent is given. Include &#60;script&#62; tag.', 'cookie-notice-and-consent-banner' ); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php esc_html_e( 'Reload page', 'cookie-notice-and-consent-banner' ); ?>
                                    </th>
                                    <td>
                                        <fieldset>
                                            <input name="cncb_refreshonallow" id="cncb_refreshonallow" type="checkbox" class="regular-text code cncb_parent_field"  <?php echo ( get_option( 'cncb_refreshonallow', 'on' ) === 'on' ) ? 'checked' : ''; ?>>
                                            <label for="cncb_refreshonallow">
                                                <?php esc_html_e('Reload a page after the visitor clicks on the "allow" button.', 'cookie-notice-and-consent-banner'); ?>
                                            </label>
                                        </fieldset>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="cncb-settings-section">
                            <h2><?php esc_html_e('Auto Accept and Hide', 'cookie-notice-and-consent-banner'); ?></h2>
                            <p><?php esc_html_e( 'Under GDPR, explicit consent for the cookies is required. Use these options with discretion, especially if you serve EU countries.', 'cookie-notice-and-consent-banner' ); ?></p>
                        </div>
                         <table  class="form-table cncb-form-table">
                             <tbody>
                                 <tr>
                                     <th>
                                         <?php esc_html_e( 'On scroll', 'cookie-notice-and-consent-banner' ); ?>
                                     </th>
                                     <td>
                                         <fieldset>
                                             <input name="cncb_by_scroll" id="cncb_by_scroll" type="checkbox" class="regular-text code cncb_parent_field"  <?php echo ( get_option( 'cncb_by_scroll', 'on' ) === 'on' ) ? 'checked' : ''; ?>>
                                             <label for="cncb_by_scroll">
                                                <?php esc_html_e('Auto accept and hide banner after scroll.', 'cookie-notice-and-consent-banner'); ?>
                                             </label>
                                             <div class="cncb_sub_field_wrapper <?php echo ( get_option( 'cncb_by_scroll', 'on' ) === 'on' ) ? 'cncb-show' : ''; ?>">
                                                 <input type="text" name="cncb_by_scroll_value" id="cncb_by_scroll_value" value="<?php echo esc_attr(get_option( 'cncb_by_scroll_value' )); ?>">
                                                 <p class="description">
                                                     <?php esc_html_e('Number of pixels user has to scroll to accept the notice and make it disappear', 'cookie-notice-and-consent-banner'); ?>
                                                 </p>
                                             </div>
                                         </fieldset>
                                     </td>
                                 </tr>
                                 <tr>
                                    <th>
                                        <?php esc_html_e( 'After delay', 'cookie-notice-and-consent-banner' ); ?>
                                    </th>
                                    <td>
                                        <fieldset>
                                            <input name="cncb_by_delay" id="cncb_by_delay" type="checkbox" class="regular-text code cncb_parent_field"  <?php echo ( get_option( 'cncb_by_delay' ) === 'on' ) ? 'checked' : ''; ?>>
                                            <label for="cncb_by_delay">
                                                <?php esc_html_e('Auto accept and hide banner when time passed', 'cookie-notice-and-consent-banner'); ?>
                                            </label>
                                            <div class="cncb_sub_field_wrapper <?php echo ( get_option( 'cncb_by_delay') === 'on' ) ? 'cncb-show' : ''; ?>">
                                                <input type="text" name="cncb_by_delay_value" id="cncb_by_delay_value" value="<?php echo esc_attr(get_option( 'cncb_by_delay_value' )); ?>">
                                                <p class="description">
                                                    <?php esc_html_e('Milliseconds until hidden', 'cookie-notice-and-consent-banner'); ?>
                                                </p>
                                            </div>
                                        </fieldset>
                                    </td>
                                </tr>
                                 <tr>
                                    <th>
                                        <?php esc_html_e( 'On click', 'cookie-notice-and-consent-banner' ); ?>
                                    </th>
                                    <td>
                                        <fieldset>
                                            <label>
                                                <input name="cncb_by_click" id="cncb_by_click" type="checkbox" class="regular-text code"  <?php echo ( get_option( 'cncb_by_click' ) === 'on' ) ? 'checked' : ''; ?>>
                                                <?php esc_html_e('Auto accept and hide banner when the user clicks anywhere on the page.', 'cookie-notice-and-consent-banner'); ?>
                                            </label>
                                        </fieldset>
                                    </td>
                                </tr>
                             </tbody>
                         </table>
                        <div class="cncb-settings-section">
                            <h2><?php esc_html_e('Shortcodes', 'cookie-notice-and-consent-banner'); ?></h2>
                        </div>
                        <div class="cncb-settings-section no-line">
                            <p><code>[revoke_consent]</code></p>
                            <p><?php esc_html_e( 'This shortcode displays a link that will make the website forget the user\'s previous choice and display a cookie banner again. Paste this shortcode to the Privacy Policy page.', 'cookie-notice-and-consent-banner' ); ?></p>
                        </div>
                        <div class="cncb-settings-section no-line">
                            <p><code>[revoke_consent text="Revoke Consent"]</code></p>
                            <p><?php esc_html_e( 'Change the value of the "text" parameter - useful if the website language is not English', 'cookie-notice-and-consent-banner' ); ?></p>
                        </div>
                        <?php submit_button(); ?>
                    </form>
                    <aside class="container-right info-popups">
                        <div class="popup-box">
                            <h3><?php esc_html_e('Help us improve', 'cookie-notice-and-consent-banner'); ?></h3>
                            <form class="request-feature" method="POST">
                                <textarea name="feature" id="feature" cols="30" rows="7" placeholder="Let us know what you think about this plugin. What features are missing?" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Let us know what you think about this plugin. What features are missing?'"></textarea>
                                <button type="submit" class="button">Submit</button>
                            </form>
                        </div>
                        <div class="popup-box">
                            <h3><?php esc_html_e('Find this plugin useful?', 'cookie-notice-and-consent-banner'); ?></h3>
                            <p><?php esc_html_e('Please rate this plugin', 'cookie-notice-and-consent-banner');?> <a href="https://wordpress.org/support/plugin/cookie-notice-and-consent-banner/reviews/" target="_blank"><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span></a> <?php esc_html_e('on', 'cookie-notice-and-consent-banner' ); ?> <a href="https://wordpress.org/support/plugin/cookie-notice-and-consent-banner/reviews/" target="_blank">Wordpress.org</a> - <?php esc_html_e('much appreciated! :)', 'cookie-notice-and-consent-banner'); ?></p>
                        </div>
                    </aside>
                </div>
			</div>
			<?php
		}

        /**
         * Adding url in plugin list for settings page
         *
         * @param $links
         * @return mixed $links
         */
        public function cncb_add_settings_url( $links ) {
            array_unshift($links , '<a href="' . admin_url( 'admin.php?page=cncb_options' ) . '">' . __('Settings') . '</a>');
            return $links;
        }

        /**
         * Ajax cncb feature feedback.
         *
         * Send the user feature feedback.
         *
         * Fired by `wp_ajax_cncb_feature_feedback` action.
         *
         * @since 1.2.0
         * @access public
         */
        public function ajax_cncb_feature_feedback() {

            $feature = '';

            if ( ! empty( $_POST['feature'] ) ) {
                $feature = $_POST['feature'];
            }

            $admin_email = get_bloginfo('admin_email');

            wp_remote_get( $this->google_endpoint .'?feature=' . $feature . '&site=' . site_url() . '&email=' . $admin_email);

            wp_send_json_success();
        }

	}
	CNCB_Admin::init();
endif;
