<?php
/**
 * Trip Report template part for single posts.
 * Loaded by single.php when the is_trip_report ACF field is true.
 *
 * @package Custom_Theme
 */
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

// ── Field retrieval ──────────────────────────────────────────────
$hero_image       = get_field( 'hero_image' );
$distance         = get_field( 'distance' );
$trip_duration    = get_field( 'trip_duration_days' );
$start_date       = get_field( 'start_date' );
$end_date         = get_field( 'end_date' );
$map_url          = get_field( 'map' );
$map_description  = get_field( 'map_description' );
$trip_overview    = get_field( 'trip_overview' );
$gear_notes       = get_field( 'gear_notes' );
$gear_photos      = get_field( 'gear_photo_gallery' );
$resource_links   = get_field( 'trip_report_resource_links' );
$photo_gallery    = get_field( 'photo_gallery' );
$related_reports  = get_field( 'related_trip_reports' );

// Collect populated days (skip completely empty ones)
$days = [];
for ( $i = 1; $i <= 12; $i++ ) {
  $title = get_field( "day_{$i}_title" );
  $desc  = get_field( "day_{$i}_description" );
  if ( $title || $desc ) {
    // Prefer the canonical name; fall back to ACF's accidental "_copy" suffix.
    $day_distance = get_field( "day_{$i}_distance" );
    if ( $day_distance === null || $day_distance === false || $day_distance === '' ) {
      $day_distance = get_field( "day_{$i}_distance_copy" );
    }

    $day_date_raw   = get_field( "day_{$i}_date" );
    $day_date       = '';
    $day_date_short = '';
    if ( $day_date_raw ) {
      $parsed_day_date = custom_theme_parse_acf_date( $day_date_raw );
      if ( $parsed_day_date ) {
        $ts             = $parsed_day_date->getTimestamp();
        $day_date       = date_i18n( 'M j, Y', $ts );
        $day_date_short = date_i18n( 'm/d/Y', $ts );
      }
    }

    $days[ $i ] = [
      'title'          => $title,
      'desc'           => $desc,
      'pictures'       => get_field( "day_{$i}_pictures" ),
      'distance'       => $day_distance,
      'elevation_gain' => get_field( "day_{$i}_elevation_gain" ),
      'elevation_loss' => get_field( "day_{$i}_elevation_loss" ),
      'campsite'       => get_field( "day_{$i}_campsite" ),
      'date'           => $day_date,
      'date_short'     => $day_date_short,
    ];
  }
}

// Prefer a sum of day distances when any are filled; otherwise use the trip-level field.
$day_distance_total = 0.0;
$has_day_distances  = false;
foreach ( $days as $day ) {
  if ( $day['distance'] !== null && $day['distance'] !== false && $day['distance'] !== '' ) {
    $has_day_distances   = true;
    $day_distance_total += (float) $day['distance'];
  }
}
if ( $has_day_distances ) {
  $distance = $day_distance_total;
}

// ── Helper: normalize an ACF image to id/url/caption ────────────
// Handles: Galerie 4 format, standard ACF array, integer ID, object
$normalize_image = function( $image ) {
  // Galerie 4 format: ['attachment'] is an object with ->ID, ['metadata']['full']['file_url'] is the URL
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
  // Standard ACF array format
  if ( is_array( $image ) ) {
    $id = (int) ( $image['ID'] ?? $image['id'] ?? 0 );
  } elseif ( is_numeric( $image ) ) {
    $id = (int) $image;
  } elseif ( is_object( $image ) && isset( $image->ID ) ) {
    $id = (int) $image->ID;
  } else {
    return null;
  }
  if ( ! $id ) return null;
  $src     = wp_get_attachment_image_src( $id, 'full' );
  $url     = $src ? $src[0] : '';
  $caption = ( is_array( $image ) && ! empty( $image['caption'] ) )
    ? $image['caption']
    : wp_get_attachment_caption( $id );
  return compact( 'id', 'url', 'caption' );
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

  // Same 3×3 collapse pattern as trail photo galleries.
  if ( $collapsible && $count > 9 ) {
    echo '<div class="trail-gallery-grid ' . esc_attr( $extra_class ) . '" data-collapsed="true">';
    foreach ( $normalized as $image ) {
      echo '<figure class="trail-gallery-grid-item">';
      echo '<a href="' . esc_url( $image['url'] ) . '" class="trail-lightbox" data-title="' . esc_attr( $image['caption'] ) . '">';
      echo wp_get_attachment_image( $image['id'], 'large', false, [ 'loading' => 'lazy' ] );
      echo '</a>';
      if ( $image['caption'] ) {
        echo '<figcaption class="trail-gallery-caption">' . esc_html( $image['caption'] ) . '</figcaption>';
      }
      echo '</figure>';
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
    echo '<figure class="trail-gallery-item">';
    echo '<a href="' . esc_url( $image['url'] ) . '" class="trail-lightbox" data-title="' . esc_attr( $image['caption'] ) . '">';
    echo wp_get_attachment_image( $image['id'], 'large', false, [ 'loading' => 'lazy' ] );
    echo '</a>';
    if ( $image['caption'] ) {
      echo '<figcaption class="trail-gallery-caption">' . esc_html( $image['caption'] ) . '</figcaption>';
    }
    echo '</figure>';
  }
  echo '</div>';
};

?>

<?php astra_entry_before(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-trail' ); ?>>
  <?php astra_entry_top(); ?>

  <?php if ( $hero_image ) :
    $hero_id = is_array( $hero_image ) ? ( $hero_image['ID'] ?? 0 ) : (int) $hero_image;
  ?>
    <div class="trip-report-featured-image">
      <?php echo wp_get_attachment_image( $hero_id, 'full', false, [ 'loading' => 'eager' ] ); ?>
    </div>
  <?php endif; ?>

  <div class="trail-content-wrap">

    <?php
    // ── Meta bar (date / miles / days), rendered below the Trip Overview heading
    $meta_bar_html = '';
    if ( $distance || $trip_duration || $start_date || $end_date ) {
      $meta_bar_html .= '<div class="trip-meta-bar">';

      if ( $start_date || $end_date ) {
        $range = custom_theme_format_trip_date_range( $start_date, $end_date );
        if ( $range ) {
          $meta_bar_html .= '<div class="trip-meta-item trip-meta-item--date">'
            . custom_theme_trip_meta_icon( 'calendar' )
            . '<span>' . esc_html( $range ) . '</span>'
            . '</div>';
        }
      }

      if ( $distance ) {
        $meta_bar_html .= '<div class="trip-meta-item">'
          . custom_theme_trip_meta_icon( 'route' )
          . '<span>' . esc_html( custom_theme_format_distance( $distance, 0 ) ) . ' miles</span>'
          . '</div>';
      }

      if ( $trip_duration ) {
        $meta_bar_html .= '<div class="trip-meta-item">'
          . custom_theme_trip_meta_icon( 'sun' )
          . '<span>' . esc_html( custom_theme_format_days( $trip_duration ) ) . '</span>'
          . '</div>';
      }

      $meta_bar_html .= '</div>';
    }
    ?>

    <?php
    // ── Sidebar navigation ───────────────────────────────────────
    $nav_items = [];
    if ( $trip_overview || $map_url ) $nav_items[] = [ 'icon' => 'compass', 'label' => 'Trip Overview', 'href' => '#trip-overview' ];
    foreach ( $days as $num => $day ) {
      $nav_items[] = [
        'label' => 'Day ' . $num,
        'sub'   => $day['title'] ?: '',
        'href'  => '#trip-day-' . $num,
      ];
    }
    if ( $gear_notes )      $nav_items[] = [ 'icon' => 'backpack', 'label' => 'Gear Notes', 'href' => '#trip-gear' ];
    if ( $resource_links )  $nav_items[] = [ 'icon' => 'link', 'label' => 'Resources & Links', 'href' => '#trip-resources' ];
    if ( $photo_gallery )   $nav_items[] = [ 'icon' => 'image', 'label' => 'Photo Gallery', 'href' => '#trip-gallery' ];
    if ( $related_reports ) $nav_items[] = [ 'icon' => 'bookmark', 'label' => 'Related Trip Reports', 'href' => '#trip-related' ];

    // Tab flow for "Continue reading" (days count as one tab).
    $tab_panels = [];
    if ( $trip_overview || $map_url ) {
      $tab_panels[] = [ 'key' => 'overview', 'href' => '#trip-overview', 'label' => 'Trip Overview' ];
    }
    if ( ! empty( $days ) ) {
      $first_day_num = (int) array_key_first( $days );
      $day_label     = 'Day ' . $first_day_num;
      if ( ! empty( $days[ $first_day_num ]['title'] ) ) {
        $day_label .= ': ' . $days[ $first_day_num ]['title'];
      }
      $tab_panels[] = [ 'key' => 'days', 'href' => '#trip-day-' . $first_day_num, 'label' => $day_label ];
    }
    if ( $gear_notes ) {
      $tab_panels[] = [ 'key' => 'gear', 'href' => '#trip-gear', 'label' => 'Gear Notes' ];
    }
    if ( $resource_links ) {
      $tab_panels[] = [ 'key' => 'resources', 'href' => '#trip-resources', 'label' => 'Resources & Links' ];
    }
    if ( $photo_gallery ) {
      $tab_panels[] = [ 'key' => 'gallery', 'href' => '#trip-gallery', 'label' => 'Photo Gallery' ];
    }
    if ( $related_reports ) {
      $tab_panels[] = [ 'key' => 'related', 'href' => '#trip-related', 'label' => 'Related Trip Reports' ];
    }

    $trip_continue = function( $from_key ) use ( $tab_panels ) {
      $keys = array_column( $tab_panels, 'key' );
      $i    = array_search( $from_key, $keys, true );
      if ( false === $i || ! isset( $tab_panels[ $i + 1 ] ) ) {
        return '';
      }
      $next = $tab_panels[ $i + 1 ];
      ob_start();
      ?>
      <div class="trip-continue">
        <a class="trip-continue-link js-trip-goto" href="<?php echo esc_attr( $next['href'] ); ?>">
          <span class="trip-continue-copy">
            <span class="trip-continue-kicker">Continue reading</span>
            <span class="trip-continue-label"><?php echo esc_html( $next['label'] ); ?></span>
          </span>
          <span class="trip-continue-arrow" aria-hidden="true">→</span>
        </a>
      </div>
      <?php
      return ob_get_clean();
    };

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
      <p class="trip-report-eyebrow">Trip Report</p>
      <h1 class="entry-title trip-report-title"><?php the_title(); ?></h1>
    </header>

    <div class="trip-report-main">

    <?php
    // ── Trip overview (+ route map) ──────────────────────────────
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

    $continue_html = $trip_continue( 'overview' );

    // Day-by-day glance strip — shown when day stats exist.
    $day_glance_html = '';
    $glance_days     = [];
    foreach ( $days as $num => $day ) {
      if ( $day['distance'] || $day['elevation_gain'] || $day['elevation_loss'] || $day['campsite'] ) {
        $glance_days[ $num ] = $day;
      }
    }

    if ( ! empty( $glance_days ) ) {
      // TEMP: pad with placeholder days so a 5-day layout can be previewed — remove when done.
      $placeholder_camps = [ 'Stuart Lake', 'Trailhead' ];
      $next_day            = (int) array_key_last( $glance_days ) + 1;
      $placeholders_needed = max( 0, 5 - count( $glance_days ) );
      for ( $p = 0; $p < $placeholders_needed; $p++ ) {
        $is_last_placeholder = ( $p === $placeholders_needed - 1 );
        $glance_days[ $next_day + $p ] = [
          'title'          => '',
          'distance'       => [ 6.2, 4.1 ][ $p ] ?? 5.0,
          'elevation_gain' => [ 890, 210 ][ $p ] ?? 500,
          'elevation_loss' => [ 410, 1640 ][ $p ] ?? 500,
          'campsite'       => $is_last_placeholder ? '' : ( $placeholder_camps[ $p ] ?? 'Camp' ),
          'placeholder'    => true,
        ];
      }

      $last_day_num = (int) array_key_last( $glance_days );
      ob_start();
      ?>
      <div class="trip-day-glance trip-day-glance--strip">
        <div class="trip-day-glance-strip" role="list">
          <?php foreach ( $glance_days as $num => $day ) :
            $place = $day['campsite'] ?: ( $day['title'] ?: ( ! empty( $day['placeholder'] ) ? 'Trailhead' : '' ) );
            $is_end     = ( (int) $num === $last_day_num && ! $day['campsite'] );
            $place_icon = $is_end ? 'footprints' : 'tent';
            $is_placeholder = ! empty( $day['placeholder'] );
            $href = $is_placeholder ? '#trip-overview' : '#trip-day-' . $num;
            ?>
            <a
              class="trip-day-glance-col js-trip-goto<?php echo $is_placeholder ? ' is-placeholder' : ''; ?>"
              href="<?php echo esc_attr( $href ); ?>"
              role="listitem"
              <?php echo $is_placeholder ? 'aria-label="Placeholder day for layout preview"' : ''; ?>
            >
              <span class="trip-day-glance-day">Day <?php echo esc_html( $num ); ?></span>
              <?php if ( $day['distance'] ) : ?>
                <span class="trip-day-glance-miles">
                  <span class="trip-day-glance-icon" aria-hidden="true"><?php echo custom_theme_trip_meta_icon( 'route' ); ?></span>
                  <span class="trip-day-glance-miles-value">
                    <?php echo esc_html( custom_theme_format_distance( $day['distance'], 1 ) ); ?><span class="trip-day-glance-unit"> mi</span>
                  </span>
                </span>
              <?php endif; ?>
              <?php if ( $day['elevation_gain'] || $day['elevation_loss'] ) : ?>
                <span class="trip-day-glance-elev">
                  <?php if ( $day['elevation_gain'] && $day['elevation_loss'] ) : ?>
                    <span>+<?php echo esc_html( custom_theme_format_elevation( $day['elevation_gain'] ) ); ?></span>
                    <span>−<?php echo esc_html( custom_theme_format_elevation( $day['elevation_loss'] ) ); ?> ft</span>
                  <?php elseif ( $day['elevation_gain'] ) : ?>
                    <span>+<?php echo esc_html( custom_theme_format_elevation( $day['elevation_gain'] ) ); ?> ft</span>
                  <?php else : ?>
                    <span>−<?php echo esc_html( custom_theme_format_elevation( $day['elevation_loss'] ) ); ?> ft</span>
                  <?php endif; ?>
                </span>
              <?php else : ?>
                <span class="trip-day-glance-elev trip-day-glance-elev--empty" aria-hidden="true"></span>
              <?php endif; ?>
              <span class="trip-day-glance-place<?php echo $place ? '' : ' trip-day-glance-place--empty'; ?>">
                <?php if ( $place ) : ?>
                  <span class="trip-day-glance-icon" aria-hidden="true"><?php echo custom_theme_trip_meta_icon( $place_icon ); ?></span>
                  <span><?php echo esc_html( $place ); ?></span>
                <?php endif; ?>
              </span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php
      $day_glance_html = ob_get_clean();
    }

    $overview_intro = $map_html . $day_glance_html;
    $overview_after = $continue_html;

    if ( $trip_overview || $overview_intro || $overview_after ) {
      get_template_part( 'template-parts/trail/section', null, [
        'id'             => 'trip-overview',
        'title'          => 'Trip Overview',
        'before_content' => $meta_bar_html,
        'intro_content'  => $overview_intro,
        'content'        => $trip_overview ?: '',
        'after_content'  => $overview_after,
      ] );
    } elseif ( $meta_bar_html ) {
      echo $meta_bar_html;
    }
    ?>

    <?php
    // ── Day by Day ───────────────────────────────────────────────
    // Day meta style options: match | muted | ruled
    $day_meta_style = 'match';

    if ( ! empty( $days ) ) :
    ?>
      <div class="trail-section trip-days-section" id="trip-days">
        <?php foreach ( $days as $num => $day ) :
          $day_title_html = '<span class="trip-day-number">Day ' . esc_html( $num ) . '</span>';
          if ( $day['title'] ) {
            $day_title_html .= '<span class="trip-day-sep"> – </span><span class="trip-day-name">' . esc_html( $day['title'] ) . '</span>';
          }

          $day_meta_html = '';
          if ( $day['distance'] || $day['elevation_gain'] || $day['elevation_loss'] || $day['campsite'] ) {
            $day_meta_html .= '<div class="trip-meta-bar trip-day-meta">';

            if ( $day['distance'] ) {
              $day_meta_html .= '<div class="trip-meta-item">'
                . custom_theme_trip_meta_icon( 'route' )
                . '<span>' . esc_html( custom_theme_format_distance( $day['distance'], 1 ) ) . ' miles</span>'
                . '</div>';
            }

            if ( $day['elevation_gain'] || $day['elevation_loss'] ) {
              $elev_parts = [];
              if ( $day['elevation_gain'] ) {
                $elev_parts[] = '+ ' . custom_theme_format_elevation( $day['elevation_gain'] );
              }
              if ( $day['elevation_loss'] ) {
                $elev_parts[] = '− ' . custom_theme_format_elevation( $day['elevation_loss'] );
              }
              $day_meta_html .= '<div class="trip-meta-item">'
                . custom_theme_trip_meta_icon( 'mountain' )
                . '<span>' . esc_html( implode( ' / ', $elev_parts ) ) . ' ft</span>'
                . '</div>';
            }

            if ( $day['campsite'] ) {
              $day_meta_html .= '<div class="trip-meta-item trip-meta-item--campsite">'
                . custom_theme_trip_meta_icon( 'tent' )
                . '<span>' . esc_html( $day['campsite'] ) . '</span>'
                . '</div>';
            }

            $day_meta_html .= '</div>';
          }

          $has_day_header = $day['date'] || $day_meta_html;
        ?>
          <div class="trip-day" id="trip-day-<?php echo esc_attr( $num ); ?>">
            <?php if ( $has_day_header ) : ?>
              <div class="trip-day-header trip-day-header--<?php echo esc_attr( $day_meta_style ); ?>">
                <div class="trip-day-title-row">
                  <h2 class="trip-day-title"><?php echo $day_title_html; ?></h2>
                  <?php if ( $day['date'] ) : ?>
                    <p class="trip-day-date">
                      <span class="trip-day-date-long"><?php echo esc_html( $day['date'] ); ?></span>
                      <?php if ( ! empty( $day['date_short'] ) ) : ?>
                        <span class="trip-day-date-short"><?php echo esc_html( $day['date_short'] ); ?></span>
                      <?php endif; ?>
                    </p>
                  <?php endif; ?>
                </div>
                <?php echo $day_meta_html; ?>
              </div>
            <?php else : ?>
              <h2 class="trip-day-title"><?php echo $day_title_html; ?></h2>
            <?php endif; ?>
            <?php if ( $day['desc'] ) : ?>
              <div class="trail-section-content trip-day-description">
                <?php echo wp_kses_post( $day['desc'] ); ?>
              </div>
            <?php endif; ?>
            <?php $render_gallery( $day['pictures'], 'trip-day-gallery', true ); ?>
          </div>
        <?php endforeach; ?>
        <?php echo $trip_continue( 'days' ); ?>
      </div>
    <?php endif; ?>

    <?php
    // ── Gear notes ───────────────────────────────────────────────
    $gear_after = '';
    if ( $gear_photos ) {
      ob_start();
      $render_gallery( $gear_photos, 'trip-gear-gallery' );
      $gear_after = ob_get_clean();
    }
    $gear_after .= $trip_continue( 'gear' );
    get_template_part( 'template-parts/trail/section', null, [
      'id'            => 'trip-gear',
      'title'         => 'Gear Notes',
      'content'       => $gear_notes,
      'after_content' => $gear_after,
    ] );
    ?>

    <?php
    // ── Resource links ───────────────────────────────────────────
    get_template_part( 'template-parts/trail/section', null, [
      'id'            => 'trip-resources',
      'title'         => 'Resources & Links',
      'content'       => $resource_links,
      'after_content' => $trip_continue( 'resources' ),
    ] );
    ?>

    <?php
    // ── Photo gallery ────────────────────────────────────────────
    if ( $photo_gallery && is_array( $photo_gallery ) ) :
    ?>
      <div class="trail-section trail-photo-gallery" id="trip-gallery">
        <h2 class="trail-section-title">Photo Gallery</h2>
        <div class="trail-gallery-grid" data-collapsed="true">
          <?php
          $gallery_count = 0;
          foreach ( $photo_gallery as $raw ) :
            $image = $normalize_image( $raw );
            if ( ! $image ) continue;
            $gallery_count++;
            $id      = $image['id'];
            $url     = $image['url'];
            $caption = $image['caption'];
          ?>
            <figure class="trail-gallery-grid-item">
              <a href="<?php echo esc_url( $url ); ?>" class="trail-lightbox" data-title="<?php echo esc_attr( $caption ); ?>">
                <?php echo wp_get_attachment_image( $id, 'large', false, [ 'loading' => 'lazy' ] ); ?>
              </a>
              <?php if ( $caption ) : ?>
                <figcaption class="trail-gallery-caption"><?php echo esc_html( $caption ); ?></figcaption>
              <?php endif; ?>
            </figure>
          <?php endforeach; ?>
        </div>
        <?php if ( $gallery_count > 9 ) : ?>
          <button class="trail-gallery-toggle trail-gallery-toggle--bar" type="button" data-more="Show more photos" data-fewer="Show fewer photos">Show more photos</button>
        <?php endif; ?>
        <?php echo $trip_continue( 'gallery' ); ?>
      </div>
    <?php endif; ?>

    <?php
    // ── Related trip reports ─────────────────────────────────────
    if ( $related_reports ) :
      if ( ! is_array( $related_reports ) ) {
        $related_reports = [ $related_reports ];
      }
      echo '<div class="trail-section trail-related-posts" id="trip-related">';
      echo '<h2 class="trail-section-title">Related Trip Reports</h2>';
      echo '<div class="related-posts-grid">';
      foreach ( $related_reports as $post_obj ) {
        if ( ! $post_obj ) continue;
        $post_id = $post_obj->ID;
        $title   = get_the_title( $post_id );
        $link    = get_permalink( $post_id );
        $excerpt = get_the_excerpt( $post_id );
        $thumb   = get_the_post_thumbnail( $post_id, 'medium', [ 'class' => 'related-post-thumb' ] );
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
      echo $trip_continue( 'related' );
      echo '</div>';
    endif;
    ?>

    </div><!-- .trip-report-main -->

    </div><!-- .trip-report-content -->

    </div><!-- .trip-report-layout -->

  </div><!-- .trail-content-wrap -->

  <?php astra_entry_bottom(); ?>
</article>
<?php astra_entry_after(); ?>
