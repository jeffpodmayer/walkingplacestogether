<?php
/**
 * Day trip report template part for single posts.
 * Loaded by single.php when the is_day_trip_report ACF field is true.
 *
 * @package Custom_Theme
 */
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

// ── Field retrieval (same ACF fields as multi-day trip reports) ───
$hero_image       = get_field( 'hero_image' );
$distance         = get_field( 'distance' );
$start_date       = get_field( 'start_date' );
$map_url          = get_field( 'map' );
$map_description  = get_field( 'map_description' );
$trip_overview    = get_field( 'trip_overview' );
$gear_notes       = get_field( 'gear_notes' );
$resource_links   = get_field( 'trip_report_resource_links' );
$photo_gallery    = get_field( 'photo_gallery' );
$related_reports  = get_field( 'related_trip_reports' );

// Day-trip stats live in the Day 1 ACF fields when filled.
$day_distance = get_field( 'day_1_distance' );
if ( $day_distance === null || $day_distance === false || $day_distance === '' ) {
  $day_distance = get_field( 'day_1_distance_copy' );
}
if ( $day_distance !== null && $day_distance !== false && $day_distance !== '' ) {
  $distance = $day_distance;
}

$elevation_gain = get_field( 'day_1_elevation_gain' );
$elevation_loss = get_field( 'day_1_elevation_loss' );
$campsite       = get_field( 'day_1_campsite' );

$day_date_raw   = get_field( 'day_1_date' );
$trip_date      = '';
$trip_date_short = '';
if ( $day_date_raw ) {
  $parsed_date = custom_theme_parse_acf_date( $day_date_raw );
  if ( $parsed_date ) {
    $ts              = $parsed_date->getTimestamp();
    $trip_date       = date_i18n( 'M j, Y', $ts );
    $trip_date_short = date_i18n( 'm/d/Y', $ts );
  }
} elseif ( $start_date ) {
  $parsed_date = custom_theme_parse_acf_date( $start_date );
  if ( $parsed_date ) {
    $ts              = $parsed_date->getTimestamp();
    $trip_date       = date_i18n( 'M j, Y', $ts );
    $trip_date_short = date_i18n( 'm/d/Y', $ts );
  }
}

// ── Helper: normalize an ACF/Galerie image to id/url/caption ─────
$normalize_image = function( $image ) {
  if ( is_array( $image ) && isset( $image['attachment'] ) && is_object( $image['attachment'] ) ) {
    $id      = (int) $image['attachment']->ID;
    $url     = $image['metadata']['full']['file_url'] ?? '';
    if ( ! $url ) {
      $src = wp_get_attachment_image_src( $id, 'full' );
      $url = $src ? $src[0] : '';
    }
    $caption = wp_get_attachment_caption( $id );
    return compact( 'id', 'url', 'caption' );
  }

  if ( is_array( $image ) ) {
    $id      = (int) ( $image['ID'] ?? $image['id'] ?? 0 );
    $url     = $image['url'] ?? '';
    $caption = $image['caption'] ?? '';
  } elseif ( is_numeric( $image ) ) {
    $id      = (int) $image;
    $url     = '';
    $caption = '';
  } elseif ( is_object( $image ) && isset( $image->ID ) ) {
    $id      = (int) $image->ID;
    $url     = '';
    $caption = '';
  } elseif ( is_string( $image ) ) {
    return [
      'id'      => 0,
      'url'     => $image,
      'caption' => '',
    ];
  } else {
    return null;
  }

  if ( $id ) {
    $src     = wp_get_attachment_image_src( $id, 'full' );
    $url     = $url ?: ( $src ? $src[0] : '' );
    $caption = $caption ?: wp_get_attachment_caption( $id );
  }

  return $url ? compact( 'id', 'url', 'caption' ) : null;
};

// ── Helper: render an ACF gallery ───────────────────────────────
$render_gallery = function( $images, $extra_class = '', $collapsible = false ) use ( $normalize_image ) {
  if ( ! $images || ! is_array( $images ) ) {
    return;
  }

  $normalized = [];
  foreach ( $images as $raw ) {
    $image = $normalize_image( $raw );
    if ( $image ) {
      $normalized[] = $image;
    }
  }
  $count = count( $normalized );
  if ( ! $count ) {
    return;
  }

  $render_figure = function( $image, $item_class ) {
    echo '<figure class="' . esc_attr( $item_class ) . '">';
    echo '<a href="' . esc_url( $image['url'] ) . '" class="trail-lightbox" data-title="' . esc_attr( $image['caption'] ) . '">';
    if ( $image['id'] ) {
      echo wp_get_attachment_image( $image['id'], 'large', false, [ 'loading' => 'lazy' ] );
    } else {
      echo '<img src="' . esc_url( $image['url'] ) . '" alt="" loading="lazy">';
    }
    echo '</a>';
    if ( $image['caption'] ) {
      echo '<figcaption class="trail-gallery-caption">' . esc_html( $image['caption'] ) . '</figcaption>';
    }
    echo '</figure>';
  };

  // Same 3×3 collapse pattern as multi-day / trail galleries.
  if ( $collapsible && $count > 9 ) {
    echo '<div class="trail-gallery-grid ' . esc_attr( $extra_class ) . '" data-collapsed="true">';
    foreach ( $normalized as $image ) {
      $render_figure( $image, 'trail-gallery-grid-item' );
    }
    echo '</div>';
    echo '<button class="trail-gallery-toggle trail-gallery-toggle--bar" type="button" data-more="Show more photos" data-fewer="Show fewer photos">Show more photos</button>';
    return;
  }

  if ( $count === 1 ) {
    $layout = 'trail-gallery--1';
  } elseif ( $count === 2 || $count === 4 ) {
    $layout = 'trail-gallery--2';
  } else {
    $layout = 'trail-gallery--3';
  }
  echo '<div class="trail-image-gallery ' . esc_attr( trim( $layout . ' ' . $extra_class ) ) . '">';
  foreach ( $normalized as $image ) {
    $render_figure( $image, 'trail-gallery-item' );
  }
  echo '</div>';
};

$hero = $normalize_image( $hero_image );
?>

<?php astra_entry_before(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-trail single-day-trip-report' ); ?>>
  <?php astra_entry_top(); ?>

  <?php if ( $hero ) : ?>
    <div class="trip-report-featured-image">
      <?php if ( $hero['id'] ) : ?>
        <?php echo wp_get_attachment_image( $hero['id'], 'full', false, [ 'loading' => 'eager' ] ); ?>
      <?php else : ?>
        <img src="<?php echo esc_url( $hero['url'] ); ?>" alt="" loading="eager">
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="trail-content-wrap">

    <?php
    // Meta bar (miles / elev / campsite); date sits beside the title
    $meta_bar_html = '';
    if ( $distance || $elevation_gain || $elevation_loss || $campsite ) {
      $meta_bar_html .= '<div class="trip-meta-bar trip-day-meta">';

      if ( $distance ) {
        $meta_bar_html .= '<div class="trip-meta-item">'
          . custom_theme_trip_meta_icon( 'route' )
          . '<span>' . esc_html( custom_theme_format_distance( $distance, 0 ) ) . ' miles</span>'
          . '</div>';
      }

      if ( $elevation_gain || $elevation_loss ) {
        $elev_parts = [];
        if ( $elevation_gain ) {
          $elev_parts[] = '+ ' . custom_theme_format_elevation( $elevation_gain );
        }
        if ( $elevation_loss ) {
          $elev_parts[] = '− ' . custom_theme_format_elevation( $elevation_loss );
        }
        $meta_bar_html .= '<div class="trip-meta-item">'
          . custom_theme_trip_meta_icon( 'mountain' )
          . '<span>' . esc_html( implode( ' / ', $elev_parts ) ) . ' ft</span>'
          . '</div>';
      }

      if ( $campsite ) {
        $meta_bar_html .= '<div class="trip-meta-item trip-meta-item--campsite">'
          . custom_theme_trip_meta_icon( 'tent' )
          . '<span>' . esc_html( $campsite ) . '</span>'
          . '</div>';
      }

      $meta_bar_html .= '</div>';
    }

    $has_day_trip_header = $trip_date || $meta_bar_html;
    ?>

    <?php
    $nav_items = [];
    if ( $trip_overview || $map_url ) $nav_items[] = [ 'icon' => 'compass', 'label' => 'Trip Report', 'href' => '#day-trip-report' ];
    if ( $gear_notes )      $nav_items[] = [ 'icon' => 'backpack', 'label' => 'Gear', 'href' => '#day-trip-gear' ];
    if ( $resource_links )  $nav_items[] = [ 'icon' => 'link', 'label' => 'Resources & Links', 'href' => '#day-trip-resources' ];
    if ( $related_reports ) $nav_items[] = [ 'icon' => 'bookmark', 'label' => 'Related Trip Reports', 'href' => '#day-trip-related' ];
    if ( $photo_gallery )   $nav_items[] = [ 'icon' => 'image', 'label' => 'Photo Gallery', 'href' => '#day-trip-gallery' ];

    ?>

    <div class="trip-report-layout">

    <aside class="trip-report-sidebar">
      <?php
      get_template_part( 'template-parts/trail/trip-nav', null, [
        'items'    => $nav_items,
        'show_map' => (bool) $map_url,
      ] );
      ?>
    </aside>

    <div class="trip-report-content">

    <header class="trip-report-heading">
      <p class="trip-report-eyebrow">Day Trip</p>
      <h1 class="entry-title trip-report-title"><?php the_title(); ?></h1>
    </header>

    <div class="trip-report-main">

    <?php
    $map_html = '';
    if ( $map_url ) {
      $map_src = add_query_arg( 'embed', 'True', esc_url_raw( $map_url ) );
      ob_start();
      ?>
      <div class="trail-map-block">
        <div class="trail-map-home">
          <div class="trail-map-embed">
            <iframe src="<?php echo esc_url( $map_src ); ?>" style="border:none; width:100%; height:600px;" loading="lazy" title="Route map"></iframe>
          </div>
        </div>
      </div>
      <?php
      $map_html = ob_get_clean();
    }
    ?>

    <div class="trail-section day-trip-story">

    <?php if ( $trip_overview || $map_html ) : ?>
      <div class="day-trip-block" id="day-trip-report">
        <?php if ( $has_day_trip_header ) : ?>
          <div class="trip-day-header trip-day-header--match">
            <div class="trip-day-title-row">
              <h2 class="trip-day-title">Trip Report</h2>
              <?php if ( $trip_date ) : ?>
                <p class="trip-day-date">
                  <span class="trip-day-date-long"><?php echo esc_html( $trip_date ); ?></span>
                  <?php if ( $trip_date_short ) : ?>
                    <span class="trip-day-date-short"><?php echo esc_html( $trip_date_short ); ?></span>
                  <?php endif; ?>
                </p>
              <?php endif; ?>
            </div>
            <?php echo $meta_bar_html; ?>
          </div>
        <?php else : ?>
          <h2 class="trip-day-title">Trip Report</h2>
        <?php endif; ?>
        <?php echo $map_html; ?>
        <?php if ( $trip_overview ) : ?>
          <div class="trail-section-content">
            <?php echo wp_kses_post( $trip_overview ); ?>
          </div>
        <?php endif; ?>
      </div>
    <?php elseif ( $has_day_trip_header ) : ?>
      <div class="trip-day-header trip-day-header--match">
        <div class="trip-day-title-row">
          <h2 class="trip-day-title">Trip Report</h2>
          <?php if ( $trip_date ) : ?>
            <p class="trip-day-date">
              <span class="trip-day-date-long"><?php echo esc_html( $trip_date ); ?></span>
              <?php if ( $trip_date_short ) : ?>
                <span class="trip-day-date-short"><?php echo esc_html( $trip_date_short ); ?></span>
              <?php endif; ?>
            </p>
          <?php endif; ?>
        </div>
        <?php echo $meta_bar_html; ?>
      </div>
    <?php endif; ?>

    <?php if ( $gear_notes ) : ?>
      <div class="day-trip-block" id="day-trip-gear">
        <h2 class="trip-day-title">Gear</h2>
        <div class="trail-section-content">
          <?php echo wp_kses_post( $gear_notes ); ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if ( $resource_links ) : ?>
      <div class="day-trip-block" id="day-trip-resources">
        <h2 class="trip-day-title">Resources &amp; Links</h2>
        <div class="trail-section-content">
          <?php echo wp_kses_post( $resource_links ); ?>
        </div>
      </div>
    <?php endif; ?>

    <?php
    if ( $related_reports ) :
      if ( ! is_array( $related_reports ) ) {
        $related_reports = [ $related_reports ];
      }
      ?>
      <div class="day-trip-block trail-related-posts" id="day-trip-related">
        <h2 class="trip-day-title">Related Trip Reports</h2>
        <div class="related-posts-grid">
          <?php foreach ( $related_reports as $post_obj ) :
            if ( ! $post_obj ) {
              continue;
            }
            $post_id = $post_obj->ID;
            $title   = get_the_title( $post_id );
            $link    = get_permalink( $post_id );
            $excerpt = get_the_excerpt( $post_id );
            $thumb   = get_the_post_thumbnail( $post_id, 'medium', [ 'class' => 'related-post-thumb' ] );
            ?>
            <div class="related-post-item">
              <?php if ( $thumb ) : ?>
                <a href="<?php echo esc_url( $link ); ?>" class="related-post-image"><?php echo $thumb; ?></a>
              <?php endif; ?>
              <h3 class="related-post-title"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a></h3>
              <?php if ( $excerpt ) : ?>
                <p class="related-post-excerpt"><?php echo esc_html( $excerpt ); ?></p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if ( $photo_gallery && is_array( $photo_gallery ) ) : ?>
      <div class="day-trip-block trail-photo-gallery" id="day-trip-gallery">
        <h2 class="trip-day-title">Photo Gallery</h2>
        <?php $render_gallery( $photo_gallery, 'day-trip-gallery', true ); ?>
      </div>
    <?php endif; ?>

    </div><!-- .day-trip-story -->

    </div><!-- .trip-report-main -->

    </div><!-- .trip-report-content -->

    </div><!-- .trip-report-layout -->

  </div><!-- .trail-content-wrap -->

  <?php astra_entry_bottom(); ?>
</article>
<?php astra_entry_after(); ?>
