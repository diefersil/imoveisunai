<?php 
global $post, $top_area, $media_tabs; 
$fave_property_images = get_post_meta(get_the_ID(), 'fave_property_images', false);
$tools_position = houzez_option('property_tools_mobile_pos', 'under_banner');
$media_tabs = houzez_get_media_tabs();
$tabs_count = count($media_tabs);
$tabs_count = $tabs_count + 2; //add 2 for mobile
?>
<div class="visible-on-mobile">
    <div class="mobile-top-wrap">
        <div class="mobile-property-tools houzez-media-tabs-<?php esc_attr_e($tabs_count);?> clearfix">
            <?php 
            if( !empty($fave_property_images) ) {
                get_template_part('property-details/partials/banner-nav'); 
            }?>
            <?php 
            if( $tools_position == 'under_banner' ) {
                get_template_part('property-details/partials/tools'); 
            } ?> 
        </div><!-- mobile-property-tools -->
        <div class="mobile-property-title clearfix">
            <?php 
            if( houzez_option( 'detail_featured_label', 1 ) != 0 ) {
                get_template_part('template-parts/listing/partials/item-featured-label'); 
            }?>
            <?php get_template_part('property-details/partials/item-labels-mobile'); ?>
            <?php get_template_part('property-details/partials/title', 'mobile'); ?> 
            <?php get_template_part('property-details/partials/item-address'); ?>
            <?php get_template_part('property-details/partials/item-price'); ?>
            <?php if( $tools_position == 'under_title' ) { ?>
            <div class="mobile-property-tools mobile-property-tools-bottom clearfix">
                <?php get_template_part('property-details/partials/tools'); ?> 
            </div>
            <?php } ?>
            
        </div><!-- mobile-property-title -->
    </div><!-- mobile-top-wrap -->
</div><!-- visible-on-mobile -->