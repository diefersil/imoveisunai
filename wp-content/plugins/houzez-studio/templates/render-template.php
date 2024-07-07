<?php
/**
 * Single template for render 
 * @package Favethemes Studio
 * @since 1.0
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
    <meta name="format-detection" content="telephone=no">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<main class="main-wrap">

		<?php
		while ( have_posts() ) :
			the_post();

			fts_load_template_part();

		endwhile;
		wp_reset_postdata();
		?>

	</main>
</body>
<?php
wp_footer();

