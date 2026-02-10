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
        <?php
          $direction_style = get_field( 'direction_style' );
          if ( $direction_style ) : ?>
            <div class="trail-region-card">
              <span class="trail-region-value">
                <?php echo esc_html( $acf_value_to_string( $direction_style ) ); ?>
              </span>
            </div>
          <?php endif; ?>
        <?php
        $start_date = get_field( 'start_date' );
        $end_date   = get_field( 'end_date' );

        if ( $start_date || $end_date ) : ?>
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
      <?php endif; ?>
    </div>


    <?php
      $distance      = get_field( 'distance' );
      $trip_duration = get_field( 'trip_duration_days' );

      if ( $distance || $trip_duration ) {
        echo '<div class="trail-details-container">';
        
        /* Left: Miles and Days in a horizontal row */
        echo '<div class="trail-details-left">';
        if ( $distance ) {
          echo '<div class="trail-detail-card">';
          echo '<span class="trail-detail-value">' . esc_html( $acf_value_to_string( $distance ) ) . '</span>';
          echo '<span class="trail-detail-label">Miles</span></div>';
        }
        if ( $trip_duration ) {
          echo '<div class="trail-detail-card">';
          echo '<span class="trail-detail-value">' . esc_html( $acf_value_to_string( $trip_duration ) ) . '</span>';
          echo '<span class="trail-detail-label">Days</span></div>';
        }
        echo '</div>'; // close trail-details-left

        echo '</div>'; // close trail-details-container

        // Quick Links section
        echo '<div class="trail-quick-links-section">';
        echo '<div class="trail-details-right">'; // reuse styling
        echo '<h3 class="trail-section-title">Quick Links</h3>';
        echo '<div class="trail-quick-links">';
        echo '<a href="#trail-experience">Experience</a>';
        echo '<a href="#trail-logistics">Logistics</a>';
        echo '<a href="#trail-gear">Gear</a>';
        echo '<a href="#trail-gallery">Photo Gallery</a>';
        echo '<a href="#trail-resources">Resources</a>';
        echo '</div>';
        echo '</div>'; // close trail-details-right
        echo '</div>'; // close trail-quick-links-section
      }
    ?>

    <?php /* Trail Photo Gallery (film strip) */
    ?>

<?php
    // Trail Overview section
    $trail_overview = get_field( 'trail_overview' );
    if ( $trail_overview ) {
      echo '<div class="trail-section trail-overview" id="trail-overview">';
      echo '<h2 class="trail-section-title">Trail Overview</h2>';
      echo '<div class="trail-section-content">' . wp_kses_post( $trail_overview ) . '</div>';
      echo '</div>';
    }


    // Trail Overview Images (Galerie ACF 4 - Responsive)
    $overview_images = get_field( 'trail_overview_images' );
    if ( $overview_images && is_array( $overview_images ) ) {
      $count = count( $overview_images );
    if ( $count === 1 ) {
      $layout_class = 'trail-gallery--1';
    } elseif ( $count === 2 || $count === 4 ) {
      $layout_class = 'trail-gallery--2';
    } else {
      $layout_class = 'trail-gallery--3';
    }
    echo '<div class="trail-image-gallery trail-overview-gallery ' . esc_attr( $layout_class ) . '">';
      foreach ( $overview_images as $image ) {
        $image_id = $image['attachment']->ID;
        $full_url = $image['metadata']['full']['file_url'];
        $caption = $image['attachment']->post_excerpt;
        
        echo '<figure class="trail-gallery-item">';
        echo '<a href="' . esc_url( $full_url ) . '" class="trail-lightbox" data-title="' . esc_attr( $caption ) . '">';
        echo wp_get_attachment_image( $image_id, 'large', false, array(
          'loading' => 'lazy'
        ));
        echo '</a>';
        if ( $caption ) {
          echo '<figcaption class="trail-gallery-caption">' . esc_html( $caption ) . '</figcaption>';
        }
        echo '</figure>';
      }
      echo '</div>';
    }

    // Experience section
    $experience = get_field( 'experience' );
    if ( $experience ) {
      echo '<div class="trail-section trail-experience" id="trail-experience">';
      echo '<h2 class="trail-section-title">Experience</h2>';
      echo '<div class="trail-section-content">' . wp_kses_post( $experience ) . '</div>';
      echo '</div>';
    }

    // Experience Images (Galerie ACF 4 - Responsive)
    $experience_images = get_field( 'experience_images' );
    if ( $experience_images && is_array( $experience_images ) ) {
      $count = count( $experience_images );
    if ( $count === 1 ) {
      $layout_class = 'trail-gallery--1';
    } elseif ( $count === 2 || $count === 4 ) {
      $layout_class = 'trail-gallery--2';
    } else {
      $layout_class = 'trail-gallery--3';
    }
    echo '<div class="trail-image-gallery trail-overview-gallery ' . esc_attr( $layout_class ) . '">';
      foreach ( $experience_images as $image ) {
        $image_id = $image['attachment']->ID;
        $full_url = $image['metadata']['full']['file_url'];
        $caption = $image['attachment']->post_excerpt;
        
        echo '<figure class="trail-gallery-item">';
        echo '<a href="' . esc_url( $full_url ) . '" class="trail-lightbox" data-title="' . esc_attr( $caption ) . '">';
        echo wp_get_attachment_image( $image_id, 'large', false, array(
          'loading' => 'lazy'
        ));
        echo '</a>';
        if ( $caption ) {
          echo '<figcaption class="trail-gallery-caption">' . esc_html( $caption ) . '</figcaption>';
        }
        echo '</figure>';
      }
      echo '</div>';
    }
    ?>

<?php
    // Logistics section
    $logistics = get_field( 'logistics' );
    if ( $logistics ) {
      echo '<div class="trail-section trail-logistics" id="trail-logistics">';
      echo '<h2 class="trail-section-title">Logistics</h2>';
      echo '<div class="trail-section-content">' . wp_kses_post( $logistics ) . '</div>';
      echo '</div>';
    }

    // Logistics Images (Galerie ACF 4 - Responsive)
    $logistics_images = get_field( 'logistics_images' );
    if ( $logistics_images && is_array( $logistics_images ) ) {
      $count = count( $logistics_images );
    if ( $count === 1 ) {
      $layout_class = 'trail-gallery--1';
    } elseif ( $count === 2 || $count === 4 ) {
      $layout_class = 'trail-gallery--2';
    } else {
      $layout_class = 'trail-gallery--3';
    }
    echo '<div class="trail-image-gallery trail-overview-gallery ' . esc_attr( $layout_class ) . '">';
      foreach ( $logistics_images as $image ) {
        $image_id = $image['attachment']->ID;
        $full_url = $image['metadata']['full']['file_url'];
        $caption = $image['attachment']->post_excerpt;
        
        echo '<figure class="trail-gallery-item">';
        echo '<a href="' . esc_url( $full_url ) . '" class="trail-lightbox" data-title="' . esc_attr( $caption ) . '">';
        echo wp_get_attachment_image( $image_id, 'large', false, array(
          'loading' => 'lazy'
        ));
        echo '</a>';
        if ( $caption ) {
          echo '<figcaption class="trail-gallery-caption">' . esc_html( $caption ) . '</figcaption>';
        }
        echo '</figure>';
      }
      echo '</div>';
    }

    // Gear section
    $gear = get_field( 'gear' );
    if ( $gear ) {
      echo '<div class="trail-section trail-gear" id="trail-gear">';
      echo '<h2 class="trail-section-title">Gear</h2>';
      echo '<div class="trail-section-content">' . wp_kses_post( $gear ) . '</div>';
      echo '</div>';
    }

    // Gear Images (Galerie ACF 4 - Responsive)
    $gear_images = get_field( 'gear_images' );
    if ( $gear_images && is_array( $gear_images ) ) {
      $count = count( $gear_images );
    if ( $count === 1 ) {
      $layout_class = 'trail-gallery--1';
    } elseif ( $count === 2 || $count === 4 ) {
      $layout_class = 'trail-gallery--2';
    } else {
      $layout_class = 'trail-gallery--3';
    }
    echo '<div class="trail-image-gallery trail-overview-gallery ' . esc_attr( $layout_class ) . '">';
      foreach ( $gear_images as $image ) {
        $image_id = $image['attachment']->ID;
        $full_url = $image['metadata']['full']['file_url'];
        $caption = $image['attachment']->post_excerpt;
        
        echo '<figure class="trail-gallery-item">';
        echo '<a href="' . esc_url( $full_url ) . '" class="trail-lightbox" data-title="' . esc_attr( $caption ) . '">';
        echo wp_get_attachment_image( $image_id, 'large', false, array(
          'loading' => 'lazy'
        ));
        echo '</a>';
        if ( $caption ) {
          echo '<figcaption class="trail-gallery-caption">' . esc_html( $caption ) . '</figcaption>';
        }
        echo '</figure>';
      }
      echo '</div>';
    }

    // Resources & Links section
    $resources_links = get_field( 'resources_links' );
    if ( $resources_links ) {
      echo '<div class="trail-section trail-resources" id="trail-resources">';
      echo '<h2 class="trail-section-title">Resources & Links</h2>';
      echo '<div class="trail-section-content">' . wp_kses_post( $resources_links ) . '</div>';
      echo '</div>';
    }

  
    // Trail Photo Gallery (grid with expand)
    $trail_photo_gallery = get_field( 'trail_photo_gallery' );
    if ( $trail_photo_gallery && is_array( $trail_photo_gallery ) ) {
      echo '<div class="trail-section trail-photo-gallery" id="trail-gallery">';
      echo '<h2 class="trail-section-title">Photo Gallery</h2>';

      echo '<div class="trail-gallery-grid" data-collapsed="true">';
      foreach ( $trail_photo_gallery as $image ) {
        $image_id = $image['attachment']->ID;
        $full_url = $image['metadata']['full']['file_url'];
        $caption = wp_get_attachment_caption( $image_id );

      echo '<figure class="trail-gallery-grid-item">';
      echo '<a href="' . esc_url( $full_url ) . '" class="trail-lightbox" data-title="' . esc_attr( $caption ) . '">';
      echo wp_get_attachment_image( $image_id, 'large', false, array( 'loading' => 'lazy' ) );
      echo '</a>';
      if ( $caption ) {
        echo '<figcaption class="trail-gallery-caption">' . esc_html( $caption ) . '</figcaption>';
      }
      echo '</figure>';
      }
      echo '</div>';

      echo '<button class="trail-gallery-toggle" type="button">Show all photos</button>';
      echo '</div>';
    }

    ?>

<?php
    // Related Blog Posts (post object field)
    $related_posts = get_field( 'related_blog_posts' );
    if ( $related_posts ) {
      // Ensure it's an array
      if ( ! is_array( $related_posts ) ) {
        $related_posts = array( $related_posts );
      }

      echo '<div class="trail-section trail-related-posts" id="trail-related-posts" >';
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
        echo '<a href="' . esc_url( $link ) . '" class="related-post-link">Read more</a>';
        echo '</div>';
      }
      
      echo '</div>';
      echo '</div>';
    }
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