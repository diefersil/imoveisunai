<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use \HouzezThemeFunctionality\Elementor\Traits\Houzez_Preview_Query;
use Houzez_Image_Sizes;

class Houzez_Property_Images_Gallery_v2 extends Widget_Base {
	use Houzez_Preview_Query;

	public function __construct( array $data = [], ?array $args = null ) {
        parent::__construct( $data, $args );
    }

	public function get_name() {
		return 'houzez-property-detail-gallery-v2';
	}

	public function get_title() {
		return __( 'Property Images Gallery v2', 'houzez-theme-functionality' );
	}

	public function get_icon() {
		return 'houzez-element-icon eicon-slider-push';
	}

	public function get_categories() {
		if(get_post_type() === 'fts_builder' && htb_get_template_type(get_the_id()) === 'single-listing')  {
            return ['houzez-single-property-builder']; 
        }

		return [ 'houzez-single-property' ];
	}

	public function get_keywords() {
		return ['gallery', 'property images', 'property images gallery', 'property gallery', 'houzez', 'top' ];
	}

	protected function register_controls() {
		parent::register_controls();

		$this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Content', 'houzez-theme-functionality' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'image_size_size',
            [
                'label' => esc_html__( 'Image Size', 'houzez-theme-functionality' ),
                'type' => Controls_Manager::SELECT,
                'options' => \Houzez_Image_Sizes::get_enabled_image_sizes_for_elementor(),
                'default' => 'houzez-gallery',
            ]
        );

        $this->add_control(
            'gallery_type',
            [
                'label' => esc_html__( 'Popup Gallery Type', 'houzez-theme-functionality' ),
                'type' => Controls_Manager::SELECT,
				'options' => [
					'builtin' => esc_html__( 'Built In', 'houzez-theme-functionality' ),
					'photoswipe' => esc_html__( 'Photo Swipe', 'houzez-theme-functionality' )
				],
				'default' => 'builtin',
            ]
        );

        $this->add_control(
            'btn_gallery',
            [
                'label' => esc_html__( 'Gallery Button', 'houzez-theme-functionality' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'houzez-theme-functionality' ),
                'label_off' => esc_html__( 'No', 'houzez-theme-functionality' ),
                'return_value' => 'true',
                'default' => 'true',
            ]
        );

        $this->add_control(
            'gallery_note',
            [
                'label' => __( 'Gallery button can only be disabled if all other buttons (Video, 360° Tour, Map, Street View) are disabled', 'houzez-theme-functionality' ),
                'type' => 'houzez-info-note',
                'condition' => [
                    'btn_gallery!' => 'true'
                ]
            ]
        );

        $this->add_control(
            'btn_video',
            [
                'label' => esc_html__( 'Video Button', 'houzez-theme-functionality' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'houzez-theme-functionality' ),
                'label_off' => esc_html__( 'No', 'houzez-theme-functionality' ),
                'return_value' => 'true',
                'default' => 'false',
            ]
        );

        $this->add_control(
            'btn_360_tour',
            [
                'label' => esc_html__( '360° Virtual Tour', 'houzez-theme-functionality' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'houzez-theme-functionality' ),
                'label_off' => esc_html__( 'No', 'houzez-theme-functionality' ),
                'return_value' => 'true',
                'default' => 'false',
            ]
        );

        $this->add_control(
            'btn_map',
            [
                'label' => esc_html__( 'Map Button', 'houzez-theme-functionality' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'houzez-theme-functionality' ),
                'label_off' => esc_html__( 'No', 'houzez-theme-functionality' ),
                'return_value' => 'true',
                'default' => 'true',
            ]
        );

        $this->add_control(
            'map_note',
            [
                'label' => __( 'Map will only show if you have enabled when add/edit property', 'houzez-theme-functionality' ),
                'type' => 'houzez-warning-note',
                'condition' => [
                    'btn_map' => 'true'
                ]
            ]
        );

        $this->add_control(
            'btn_street',
            [
                'label' => esc_html__( 'Street View Button', 'houzez-theme-functionality' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'houzez-theme-functionality' ),
                'label_off' => esc_html__( 'No', 'houzez-theme-functionality' ),
                'return_value' => 'true',
                'default' => 'true',
            ]
        );

        $this->add_control(
            'street_note',
            [
                'label' => __( 'Street view will only show if you have enabled when add/edit property and map type set to Google', 'houzez-theme-functionality' ),
                'type' => 'houzez-warning-note',
                'condition' => [
                    'btn_street' => 'true'
                ]
            ]
        );

		$this->end_controls_section();
		
	}

	protected function render() {
		global $settings, $post, $map_street_view;

		$settings = $this->get_settings_for_display();
        $this->single_property_preview_query(); // Only for preview

        $image_size = houzez_get_image_size_for('property_detail_v3-4');
        
        $image_size = ($settings['image_size_size'] === 'global') ? $image_size : $settings['image_size_size'];

		$map_street_view = get_post_meta( $post->ID, 'fave_property_map_street_view', true );

        $images_ids = get_post_meta($post->ID, 'fave_property_images', false);
        $images_ids = array_unique($images_ids);
		
		$gallery_caption = houzez_option('gallery_caption', 0);
		$gallery_type = $settings['gallery_type'];
		$token = wp_generate_password(5, false, false);

        $builtin_gallery_class = ' houzez-trigger-popup-slider-js';
        $dataModal = 'href="#" data-bs-toggle="modal" data-bs-target="#property-lightbox"';

		?>
		<div class="hs-gallery-v2-wrap hs-property-gallery-wrap">
            <div class="hs-gallery-v2-top-wrap property-top-wrap">
                
                <div class="property-banner">
                    <div class="d-none d-md-block">
                        <?php htf_get_template_part('elementor/template-part/single-property/media-btns'); ?>
                    </div>
                    <div class="tab-content" id="pills-tabContent" style="min-height: 590px; transition: height 0.3s ease;">
                        <div class="tab-pane show active h-100" id="pills-gallery" role="tabpanel" aria-labelledby="pills-gallery-tab">
                            <?php if (!empty($images_ids)) { 
                                $total_images = count($images_ids);
                            ?>
                                <div class="top-gallery-section">
                                    <!-- Gallery skeleton loader -->
                                    <div class="houzez-gallery-skeleton" id="gallery-skeleton-loader">
                                        <div class="skeleton-main-image">
                                            <div class="skeleton-shimmer"></div>
                                        </div>
                                        <div class="skeleton-thumbs">
                                            <?php for($i = 0; $i < min(8, $total_images); $i++): ?>
                                            <div class="skeleton-thumb">
                                                <div class="skeleton-shimmer"></div>
                                            </div>
                                            <?php endfor; ?>
                                        </div>
                                    </div>

                                    <div id="property-gallery-js" class="listing-slider cS-hidden" itemscope itemtype="http://schema.org/ImageGallery">
                                        <?php foreach ($images_ids as $image_id) {
                                            $image_data = wp_get_attachment_image_src($image_id, $image_size);

                                            // Skip this iteration if image_data is false
                                            if(!$image_data) {
                                                continue;
                                            }

                                            $image_url = $image_data[0] ?? '';
                                            $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                                            $image_title = get_the_title($image_id);
                                            $image_caption = wp_get_attachment_caption($image_id);
                                            
                                            $thumb = wp_get_attachment_image_src($image_id, 'houzez-item-image-6');
                                            $thumb_url = $thumb[0] ?? '';

                                            if( $gallery_type == 'photoswipe' ) {
                                                $full_image = wp_get_attachment_image_src( $image_id, 'full' );
                                                $full_image_url = $full_image[0] ?? '';
                                                $dataModal = 'href="#" data-src="'.esc_url($full_image_url).'" data-houzez-fancybox data-fancybox="gallery-v3-4"';
                                                $builtin_gallery_class = '';
                                            }
                                            ?>
                                            <div data-thumb="<?php echo esc_url( $thumb_url );?>" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
                                                <a class="<?php echo $builtin_gallery_class; ?>" itemprop="contentUrl" data-gallery-item <?php echo $dataModal; ?>>        
                                                    <img class="img-fluid houzez-gallery-img" data-lazy="<?php echo $image_url; ?>" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 3 2'%3E%3C/svg%3E" itemprop="thumbnail" alt="<?php echo $image_alt; ?>" title="<?php echo $image_title; ?>" />
                                                </a>
                                                <?php
                                                if( !empty($image_caption) && $gallery_caption != 0 ) { ?>
                                                    <span class="hz-image-caption"><?php esc_attr($image_caption); ?></span>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        
                        <?php htf_get_template_part('elementor/template-part/single-property/media-tabs-gallery'); ?>
                    </div>
                </div>
            </div>

            <div class="hs-gallery-v1-bottom-wrap">
                <div class="container d-md-none d-sm-block">
                    <div class="mobile-top-wrap">
                        <div class="mobile-property-tools d-flex align-items-center justify-content-center">
                            <?php htf_get_template_part('elementor/template-part/single-property/media-btns'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php 
		$this->reset_preview_query(); // Only for preview
	}

}
Plugin::instance()->widgets_manager->register( new Houzez_Property_Images_Gallery_v2 );