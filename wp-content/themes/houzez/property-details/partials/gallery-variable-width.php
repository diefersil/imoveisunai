<?php
global $post, $property_gallery_popup_type;
$images_ids = get_post_meta($post->ID, 'fave_property_images', false);
$gallery_caption = houzez_option('gallery_caption', 0); 
$output = '';

// Get the dynamically assigned image size for this layout
$image_size = houzez_get_image_size_for('property_detail_v5');

$builtin_gallery_class = ' houzez-trigger-popup-slider-js';
$dataModal = 'href="#" data-bs-toggle="modal" data-bs-target="#property-lightbox"';

if( !empty($images_ids) && count($images_ids)) {
?>
<div class="top-gallery-section top-gallery-variable-width-section" role="region">
	
	<div class="listing-slider-variable-width houzez-all-slider-wrap">
		<?php
		$j = 0;
		foreach( $images_ids as $image_id ) { $j++;
			$image_data = wp_get_attachment_image_src($image_id, $image_size);

			// Skip this iteration if image_data is false
			if(!$image_data) {
				continue;
			}

			$image_url = $image_data[0] ?? '';
			$image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
			$image_title = get_the_title($image_id);
			$image_caption = wp_get_attachment_caption($image_id);

			if( $property_gallery_popup_type == 'photoswipe' ) {
				$full_image= wp_get_attachment_image_src($image_id, 'full');
				$full_image_url = $full_image[0] ?? '';
				$dataModal = 'href="#" data-src="'.esc_url($full_image_url).'" data-fancybox="gallery-variable-width"';
				$builtin_gallery_class = '';
			}
			echo '<div>
				<a href="#" data-slider-no="'.esc_attr($j).'" class="'.$builtin_gallery_class.'" '.$dataModal.'>
					<img class="img-responsive img-fluid" data-lazy="'.esc_attr( $image_url ).'" src="'.esc_attr( $image_url ).'" alt="'.esc_attr($image_alt).'" title="'.esc_attr($image_title).'">
				</a>';

				if( !empty($image_caption) && $gallery_caption != 0 ) {
						echo '<span class="hz-image-caption">'.esc_attr($image_caption).'</span>';
					}

				echo '</div>';
		}?>
	</div>
    
</div><!-- top-gallery-section -->
<?php } ?>