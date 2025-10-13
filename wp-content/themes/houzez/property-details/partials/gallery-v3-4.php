<?php
global $post, $top_area;

// Get the dynamically assigned image size for this layout
$image_size = houzez_get_image_size_for('property_detail_v3-4');

$images_ids = get_post_meta($post->ID, 'fave_property_images', false);

$gallery_caption = houzez_option('gallery_caption', 0); 
$property_gallery_popup_type = houzez_get_popup_gallery_type(); 
$gallery_token = wp_generate_password(5, false, false);

$builtin_gallery_class = ' houzez-trigger-popup-slider-js';
$dataModal = 'href="#" data-bs-toggle="modal" data-bs-target="#property-lightbox"';

if( !empty($images_ids) && count($images_ids) ) {
    $images_ids = array_unique($images_ids);
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
        <?php
        foreach( $images_ids as $image_id ) {
            $image_data = wp_get_attachment_image_src($image_id, $image_size);
			$image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
			$image_title = get_the_title($image_id);
			$image_caption = wp_get_attachment_caption($image_id);

            $thumb = wp_get_attachment_image_src($image_id, 'houzez-item-image-6');

            // Skip this iteration if image_data is false
            if(!$image_data) {
                continue;
            }

            $image_url = $image_data[0] ?? '';
			$thumb_url = $thumb[0] ?? '';

			if( $property_gallery_popup_type == 'photoswipe' ) {
                $full_image = wp_get_attachment_image_src($image_id, 'full');
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
</div><!-- top-gallery-section -->
<?php } else if( has_post_thumbnail() ) {
        $output = '';
        $thumb = houzez_get_image_by_id( get_post_thumbnail_id(), $image_size) ;
        $output .= '<div data-thumb="'.esc_url( $thumb[0] ).'">';
        $output .= '<a rel="gallery-1" data-slider-no="1" href="#" class="houzez-trigger-popup-slider-js" data-bs-toggle="modal" data-bs-target="#property-lightbox">
            <img class="img-fluid" src="'.esc_url( $thumb[0] ).'" alt="" title="">
        </a>';
        $output .= '</div>';
        echo $output;   

} else { ?>
<div class="top-gallery-section">
    <?php houzez_image_placeholder( $image_size ); ?>
</div>
<?php } ?>