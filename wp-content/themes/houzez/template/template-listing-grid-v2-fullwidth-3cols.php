<?php
/**
 * Template Name: Template listings grid v2 full width 3Cols
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 16/12/15
 * Time: 3:27 PM
 */
get_header();

global $post, $listings_tabs, $total_listing_found;
$page_content_position = houzez_get_listing_data('listing_page_content_area');

$listing_args = array(
    'post_type' => 'property',
    'post_status' => 'publish'
);

$listing_args = apply_filters( 'houzez20_property_filter', $listing_args );
$listing_args = houzez_prop_sort ( $listing_args );

$listings_query = new WP_Query( $listing_args );
$total_listing_found = $listings_query->found_posts;

$fave_prop_no = get_post_meta( $post->ID, 'fave_prop_no', true );
?>
<section class="listing-wrap listing-v2">
    <div class="container">

        <div class="page-title-wrap">

            <?php get_template_part('template-parts/listing/listing-page-title'); ?>  

        </div><!-- page-title-wrap -->

        <div class="row">
            <div class="col-lg-12 col-md-12">

                <?php
                if ( $page_content_position !== '1' ) {
                    if ( have_posts() ) {
                        while ( have_posts() ) {
                            the_post();
                            ?>
                            <article <?php post_class(); ?>>
                                <?php the_content(); ?>
                            </article>
                            <?php
                        }
                    } 
                }?>
                
                <?php get_template_part( 'template-parts/listing/listing', 'tools' ); ?>

                <div class="listing-view grid-view card-deck grid-view-3-cols">
                    <?php
                    if ( $listings_query->have_posts() ) :
                        while ( $listings_query->have_posts() ) : $listings_query->the_post();

                            get_template_part('template-parts/listing/item', 'v2');

                        endwhile;
                    else:
                        get_template_part('template-parts/listing/item', 'none');
                    endif;
                    wp_reset_postdata();
                    ?>  
                </div><!-- listing-view -->

                <?php houzez_pagination( $listings_query->max_num_pages, $total_listing_found, $fave_prop_no ); ?>

            </div><!-- col-lg-12 col-md-12 -->
        </div><!-- row -->
    </div><!-- container -->
</section><!-- listing-wrap -->

<?php
if ('1' === $page_content_position ) {
    if ( have_posts() ) {
        while ( have_posts() ) {
            the_post();
            ?>
            <section class="content-wrap">
                <?php the_content(); ?>
            </section>
            <?php
        }
    }
}
?>

<?php get_footer(); ?>