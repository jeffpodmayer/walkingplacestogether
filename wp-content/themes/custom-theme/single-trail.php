<?php
/**
 * Single Trail template.
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

  // Fields
  $region            = get_field( 'region' );
  $direction_style   = get_field( 'direction_style' );
  $start_date        = get_field( 'start_date' );
  $end_date          = get_field( 'end_date' );
  $distance          = get_field( 'distance' );
  $trip_duration     = get_field( 'trip_duration_days' );

  $trail_overview    = get_field( 'trail_overview' );
  $experience        = get_field( 'experience' );
  $logistics         = get_field( 'logistics' );
  $gear              = get_field( 'gear' );
  $resources_links   = get_field( 'resources_links' );

  $overview_images   = get_field( 'trail_overview_images' );
  $experience_images = get_field( 'experience_images' );
  $logistics_images  = get_field( 'logistics_images' );
  $gear_images       = get_field( 'gear_images' );

  $trail_photo_gallery = get_field( 'trail_photo_gallery' );
  $related_posts        = get_field( 'related_blog_posts' );
?>
  <?php astra_entry_before(); ?>
  <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php astra_entry_top(); ?>

    <div <?php astra_blog_layout_class( 'single-layout-1' ); ?>>
      <div class="entry-content clear">
        <?php the_content(); ?>
      </div>
    </div>

    <div class="trail-content-wrap">

      <div class="trail-title-row">
        <h1 class="entry-title"><?php the_title(); ?></h1>

        <?php if ( $region ) : ?>
          <div class="trail-region-card trail-region-card--region">
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

      <?php if ( $start_date || $end_date || $direction_style ) : ?>
      <div class="trail-title-sub-row">
          <?php if ( $start_date || $end_date ) : ?>
            <div class="trail-region-card">
              <span class="trail-region-value">
                <?php
                $start = $start_date ? $acf_value_to_string( $start_date ) : '';
                $end   = $end_date ? $acf_value_to_string( $end_date ) : '';
                echo esc_html( trim( $start . ' → ' . $end, ' →' ) );
                ?>
              </span>
            </div>
          <?php endif; ?>

          <?php if ( $direction_style ) : ?>
            <div class="trail-region-card">
              <span class="trail-region-value">
                <?php echo esc_html( $acf_value_to_string( $direction_style ) ); ?>
              </span>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php
      if ( $distance || $trip_duration ) {
        echo '<div class="trail-details-container">';
        echo '<div class="trail-details-left">';
        if ( $distance ) {
          echo '<div class="trail-detail-card">';
          echo '<span class="trail-detail-value js-count" data-count="' . esc_attr( $acf_value_to_string( $distance ) ) . '">' . esc_html( $acf_value_to_string( $distance ) ) . '</span>';
          echo '<span class="trail-detail-label">Miles</span></div>';
        }
        if ( $trip_duration ) {
          echo '<div class="trail-detail-card">';
          echo '<span class="trail-detail-value js-count" data-count="' . esc_attr( $acf_value_to_string( $trip_duration ) ) . '">' . esc_html( $acf_value_to_string( $trip_duration ) ) . '</span>';
          echo '<span class="trail-detail-label">Days</span></div>';
        }
        echo '</div>';
        echo '</div>';
      }
      ?>

      <?php
      get_template_part( 'template-parts/trail/quick-links', null, [
        'links' => [
          'Experience'    => '#trail-experience',
          'Logistics'     => '#trail-logistics',
          'Gear'          => '#trail-gear',
          'Photo Gallery' => '#trail-gallery',
          'Resources'     => '#trail-resources',
        ],
      ] );
      ?>

      <?php
      get_template_part( 'template-parts/trail/section', null, [
        'id'      => 'trail-overview',
        'title'   => 'Trail Overview',
        'content' => $trail_overview,
      ] );
      get_template_part( 'template-parts/trail/gallery', null, [
        'images'      => $overview_images,
        'extra_class' => 'trail-overview-gallery',
      ] );

      get_template_part( 'template-parts/trail/section', null, [
        'id'      => 'trail-experience',
        'title'   => 'Experience',
        'content' => $experience,
      ] );
      get_template_part( 'template-parts/trail/gallery', null, [
        'images'      => $experience_images,
        'extra_class' => 'trail-experience-gallery',
      ] );

      get_template_part( 'template-parts/trail/section', null, [
        'id'      => 'trail-logistics',
        'title'   => 'Logistics',
        'content' => $logistics,
      ] );
      get_template_part( 'template-parts/trail/gallery', null, [
        'images'      => $logistics_images,
        'extra_class' => 'trail-logistics-gallery',
      ] );

      get_template_part( 'template-parts/trail/section', null, [
        'id'      => 'trail-gear',
        'title'   => 'Gear',
        'content' => $gear,
      ] );
      get_template_part( 'template-parts/trail/gallery', null, [
        'images'      => $gear_images,
        'extra_class' => 'trail-gear-gallery',
      ] );

      get_template_part( 'template-parts/trail/section', null, [
        'id'      => 'trail-resources',
        'title'   => 'Resources & Links',
        'content' => $resources_links,
      ] );

      if ( $related_posts ) {
        if ( ! is_array( $related_posts ) ) {
          $related_posts = array( $related_posts );
        }

        echo '<div class="trail-section trail-related-posts" id="trail-related-posts">';
        echo '<h2 class="trail-section-title">Related Blog Posts</h2>';
        echo '<div class="related-posts-grid">';

        foreach ( $related_posts as $post_obj ) {
          if ( ! $post_obj ) continue;
          $post_id = $post_obj->ID;
          $title   = get_the_title( $post_id );
          $link    = get_permalink( $post_id );
          $excerpt = get_the_excerpt( $post_id );
          $thumb   = get_the_post_thumbnail( $post_id, 'medium', array( 'class' => 'related-post-thumb' ) );

          echo '<div class="related-post-item">';
          if ( $thumb ) {
            echo '<a href="' . esc_url( $link ) . '" class="related-post-image">' . $thumb . '</a>';
          }
          echo '<h3 class="related-post-title"><a href="' . esc_url( $link ) . '">' . esc_html( $title ) . '</a></h3>';
          if ( $excerpt ) {
            echo '<p class="related-post-excerpt">' . esc_html( $excerpt ) . '</p>';
          }
          echo '</div>';
        }

        echo '</div>';
        echo '</div>';
      }
      get_template_part( 'template-parts/trail/photo-gallery', null, [
        'images' => $trail_photo_gallery,
      ] );
      ?>
    </div>

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