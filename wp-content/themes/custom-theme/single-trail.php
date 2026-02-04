<?php
/**
 * Single Trail template.
 * Keeps Astra layout and content; add ACF output in the optional block below.
 *
 * @package Custom_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>

<?php if ( astra_page_layout() === 'left-sidebar' ) { ?>
	<?php get_sidebar(); ?>
<?php } ?>

	<div id="primary" <?php astra_primary_class(); ?>>
  <?php astra_primary_content_top(); ?>

<?php
while ( have_posts() ) :
  the_post();

  if ( ! function_exists( 'get_field' ) ) {
    astra_content_loop();
    break;
  }

  $acf_value_to_string = function( $value ) {
    if ( is_array( $value ) ) {
      $parts = array();
      foreach ( $value as $item ) {
        if ( is_object( $item ) && isset( $item->name ) ) {
          $parts[] = $item->name;
        } elseif ( is_object( $item ) && isset( $item->post_title ) ) {
          $parts[] = $item->post_title;
        } elseif ( is_scalar( $item ) ) {
          $parts[] = $item;
        }
      }
      return implode( ', ', $parts );
    }
    return is_scalar( $value ) ? (string) $value : '';
  };

  $region = get_field( 'region' );
  ?>
  <?php astra_entry_before(); ?>
  <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php astra_entry_top(); ?>

    <div <?php astra_blog_layout_class( 'single-layout-1' ); ?>>
      <div class="entry-content clear">
        <?php the_content(); ?>
      </div>
    </div>

    <div class="trail-title-row">
      <h1 class="entry-title"><?php the_title(); ?></h1>
      <?php if ( $region ) : ?>
        <div class="trail-region-card">
          <span class="trail-region-value">
          <?php
          if ( is_array( $region ) ) {
            $region_parts = array();
            foreach ( $region as $item ) {
              if ( is_object( $item ) && isset( $item->name ) ) {
                $region_parts[] = $item->name;
              } elseif ( is_object( $item ) && isset( $item->post_title ) ) {
                $region_parts[] = $item->post_title;
              } elseif ( is_scalar( $item ) ) {
                $region_parts[] = $item;
              }
            }
            echo esc_html( implode( ' | ', $region_parts ) );
          } else {
            echo esc_html( $acf_value_to_string( $region ) );
          }
          ?>
          </span>
        </div>
      <?php endif; ?>
    </div>


    <?php
    $distance        = get_field( 'distance' );
    $trip_duration   = get_field( 'trip_duration_days' );
    $direction_style  = get_field( 'direction_style' );
    $season          = get_field( 'season' );

    if ( $distance || $trip_duration || $direction_style || $season ) {
      echo '<div class="trail-details">';
      if ( $distance ) {
        echo '<div class="trail-detail-card"><span class="trail-detail-label">Distance</span><span class="trail-detail-value">' . esc_html( $acf_value_to_string( $distance ) ) . '</span></div>';
      }
      if ( $trip_duration ) {
        echo '<div class="trail-detail-card"><span class="trail-detail-label">Trip duration (days)</span><span class="trail-detail-value">' . esc_html( $acf_value_to_string( $trip_duration ) ) . '</span></div>';
      }
      if ( $direction_style ) {
        echo '<div class="trail-detail-card"><span class="trail-detail-label">Direction / style</span><span class="trail-detail-value">' . esc_html( $acf_value_to_string( $direction_style ) ) . '</span></div>';
      }
      if ( $season ) {
        echo '<div class="trail-detail-card"><span class="trail-detail-label">Season hiked</span><span class="trail-detail-value">' . esc_html( $acf_value_to_string( $season ) ) . '</span></div>';
      }
      echo '</div>';
    }
    ?>

    <?php astra_entry_bottom(); ?>
  </article>
  <?php astra_entry_after(); ?>
<?php endwhile; ?>

<?php astra_primary_content_bottom(); ?>
	</div>

<?php if ( astra_page_layout() === 'right-sidebar' ) { ?>
	<?php get_sidebar(); ?>
<?php } ?>

<?php get_footer();