<?php
/**
 * Name         : Elementor Addons For Houzez
 * Description  : Provides additional Elementor Elements for the Houzez theme
 * Author : Waqas Riaz
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! class_exists( 'Houzez_Elementor_Extensions' ) ) {
    final class Houzez_Elementor_Extensions {

        const HOUZEZ_GROUP = 'houzez';

        /**
         * Houzez_Extensions The single instance of Houzez_Extensions.
         * @var     object
         * @access  private
         * @since   1.5.6
         */
        private static $_instance = null;

        /**
         * Constructor function.
         * @access  public
         * @since   1.5.6
         * @return  void
         */
        public function __construct() {

            $this->houzez_register_traits();

            add_action( 'elementor/elements/categories_registered', array( $this, 'add_widget_categories' ) );
            add_action( 'elementor/widgets/register', array( $this, 'elementor_widgets' ) );
            add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'houzez_enqueue_scripts' ) );
            add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'enqueue_frontend_styles' ) );
            add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_editor_styles' ), 0 );

            add_action( 'elementor/dynamic_tags/register', array( $this, 'register_tags' ) );
            add_action( 'elementor/controls/register', array( $this, 'register_controls' ) );


            //$this->houzez_wpml_elementor_translation();
        }

        /**
         * Houzez_Elementor_Extensions Instance
         *
         * Ensures only one instance of Houzez_Elementor_Extensions is loaded or can be loaded.
         *
         * @since 1.5.6
         * @static
         * @return Houzez_Elementor_Extensions instance
         */
        public static function instance () {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }


        /**
         * Widget Category Register
         *
         * @since  1.5.6
         * @access public
         */
        public function add_widget_categories( $elements_manager ) {
            $elements_manager->add_category(
                'houzez-elements',
                [
                    'title' => esc_html__( 'Houzez Elements', 'houzez-theme-functionality' ),
                    'icon' => 'fa fa-plug',
                ]
            );

            $elements_manager->add_category(
                'houzez-header-footer',
                [
                    'title' => esc_html__( 'Houzez Header & Footer', 'houzez-theme-functionality' ),
                    'icon' => 'fa fa-plug',
                ]
            );

            //if( did_action( 'elementor_pro/init' ) ) {
                $elements_manager->add_category(
                    'houzez-single-property',
                    [
                        'title' => esc_html__( 'Houzez Single Property', 'houzez-theme-functionality' ),
                        'icon' => 'fa fa-plug',
                    ]
                );
            //}
        }

        /**
         * Widgets
         *
         * @since  1.0.0
         * @access public
         */
        public function elementor_widgets( $widgets_manager ) {

            if( class_exists('houzez_data_source') ) {
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-title.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/space.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/search-builder.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/advanced-search.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/page-title.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/sort-by.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/listings-tabs.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/properties-ajax-tabs.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-cards-v1.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-cards-v2.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-cards-v3.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-cards-v4.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-cards-v5.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-cards-v6.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-cards-v7.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-cards-v8.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/properties.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-carousel-v1.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-carousel-v2.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-carousel-v3.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-carousel-v5.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-carousel-v6.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-carousel-v7.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-by-id.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-by-ids.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/properties-recent-viewed.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/grids.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/grid-builder.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/taxonomies-cards.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/taxonomies-cards-carousel.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/taxonomies-grids.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/taxonomies-grids-carousel.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/properties-grids.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/taxonomies-list.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/properties-slider.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/banner-image.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/google-map.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/open-street-map.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/agents.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/agents-grid.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/contact-form.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/inquiry-form.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/testimonials.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/testimonials-v2.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/team-member.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/partners.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/icon-box.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/blog-posts.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/blog-posts-v2.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/blog-posts-carousel.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/price-table.php';

                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/header-footer/site-logo.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/header-footer/menu.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/header-footer/login-modal.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/header-footer/create-listing-btn.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/header-footer/currency.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/header-footer/area-switcher.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/header-footer/lang.php';

                //if( did_action( 'elementor_pro/init' ) ) {
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/breadcrumb.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-title.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-price.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-address.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/item-tools.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/status-label.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/item-label.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/featured-label.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-content.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/property-excerpt.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/featured-image.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-toparea-v1.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-toparea-v2.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-toparea-v3.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-toparea-v5.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-toparea-v6.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-toparea-v7.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-overview.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-overview-v2.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-description.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-details.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-address.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-block-gallery.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-google-map.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-open-street-map.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-features.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-attachments.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-video.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-floorplan.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-floorplan-v2.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-360-virtual.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-energy.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-calculator.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-nearby.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-walkscore.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-contact-bottom.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-contact-2.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-schedule-tour.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-schedule-tour-v2.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-similar.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-sublistings.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-review.php';
                    require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/section-calendar.php';
                //}

            }
        }

        public function houzez_register_traits() {
            require_once HOUZEZ_PLUGIN_PATH . '/elementor/traits/Houzez_Property_Card_Common_Filters.php';
            require_once HOUZEZ_PLUGIN_PATH . '/elementor/traits/Houzez_Property_Taxonomies.php';
            require_once HOUZEZ_PLUGIN_PATH . '/elementor/traits/Houzez_Filters_2.php';
        }

        public function houzez_wpml_elementor_translation() {

            if ( class_exists( 'SitePress' ) ) {

                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/wpml/section-title-wpml.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/wpml/advanced-search-wpml.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/wpml/sort-by-wpml.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/wpml/property-carousel-v1-wpml.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/wpml/property-carousel-v2-wpml.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/wpml/property-carousel-v3-wpml.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/wpml/property-carousel-v5-wpml.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/wpml/property-carousel-v6-wpml.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/wpml/grid-builder-wpml.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/wpml/contact-form-wpml.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/wpml/inquiry-form-wpml.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/wpml/search-builder-wpml.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/wpml/team-member-wpml.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/wpml/icon-box-wpml.php';
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/widgets/wpml/price-table-wpml.php';

            }

        }

        public function register_tags( $dynamic_tags ) {

        
            // In our Dynamic Tag we use a group named request-variables so we need 
            // To register that group as well before the tag
            \Elementor\Plugin::$instance->dynamic_tags->register_group( self::HOUZEZ_GROUP, [
                'title' => 'Houzez' 
            ] );

            $files = [
                'property-title',
                'property-excerpt',
                'property-content',
            ];

            foreach ( $files as $file ) {
                require_once HOUZEZ_PLUGIN_PATH . '/elementor/tags/'.$file.'.php';
            }


            $tags = [
                'Property_Title_Tag',
                'Property_Excerpt_Tag',
                'Property_Content_Tag',
            ];

            foreach ( $tags as $tag ) {
                $dynamic_tags->register( new $tag );
            }

        }

        public function register_controls( $controls_manager ) {

            require_once( HOUZEZ_PLUGIN_DIR . 'elementor/controls/warning.php' );
            require_once( HOUZEZ_PLUGIN_DIR . 'elementor/controls/info.php' );
            require_once( HOUZEZ_PLUGIN_DIR . 'elementor/controls/address-control.php' );
            require_once( HOUZEZ_PLUGIN_DIR . 'elementor/controls/details-control.php' );
            require_once( HOUZEZ_PLUGIN_DIR . 'elementor/controls/autocomplete.php' );

            $controls_manager->register( new Houzez_Warning_note() );
            $controls_manager->register( new Houzez_Info_note() );
            $controls_manager->register( new Houzez_Address_Control() );
            $controls_manager->register( new Houzez_Property_Details_Control() );
            $controls_manager->register( new Houzez_Autocomplete() );

        }


        /**
         * Enqueue scripts
         *
         * @since  1.0.0
         * @access public
         */
        public function houzez_enqueue_scripts() {
            $js_path = 'assets/frontend/js/';
        
        }

        /**
         * Enqueue scripts
         *
         * @since  1.0.0
         * @access public
         */
        public function enqueue_editor_styles() {
            $js_path = 'assets/frontend/css/';
            wp_enqueue_style( 'houzez-ele-editor', HOUZEZ_PLUGIN_URL.$js_path . 'ele-editor.css', [], '1.0.0' );
        }

        /**
         * Enqueue Styles
         *
         * @since  2.2.0
         * @access public
         */
        public function enqueue_frontend_styles() { 
            
        }

    }
}

if ( did_action( 'elementor/loaded' ) ) {
    // Finally initialize code
    Houzez_Elementor_Extensions::instance();
}