<?php
/**
 * The template for displaying all single posts.
 * Overrides the Astra parent theme single.php.
 *
 * If the post has the is_trip_report ACF field enabled,
 * it renders the structured trip report layout. Otherwise
 * it falls back to Astra's default content loop.
 *
 * @package Custom_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

get_header();

if ( astra_page_layout() === 'left-sidebar' ) {
  get_sidebar();
}
?>

<div id="primary" <?php astra_primary_class(); ?>>

  <?php astra_primary_content_top(); ?>

  <?php
  if ( function_exists( 'get_field' ) && get_field( 'is_trip_report' ) ) {
    while ( have_posts() ) :
      the_post();
      get_template_part( 'template-parts/single/content', 'trip-report' );
    endwhile;
  } else {
    astra_content_loop();
  }
  ?>

  <?php astra_primary_content_bottom(); ?>

</div><!-- #primary -->

<?php
if ( astra_page_layout() === 'right-sidebar' ) {
  get_sidebar();
}

get_footer();
