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
    $days[ $i ] = [
      'title'    => $title,
      'desc'     => $desc,
      'pictures' => get_field( "day_{$i}_pictures" ),
    ];
  }
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
$render_gallery = function( $images, $extra_class = '' ) use ( $normalize_image ) {
  if ( ! $images || ! is_array( $images ) ) {
    return;
  }
  $count = count( $images );
  if ( $count === 1 ) {
    $layout = 'trail-gallery--1';
  } elseif ( $count === 2 || $count === 4 ) {
    $layout = 'trail-gallery--2';
  } else {
    $layout = 'trail-gallery--3';
  }
  echo '<div class="trail-image-gallery ' . esc_attr( trim( $layout . ' ' . $extra_class ) ) . '">';
  foreach ( $images as $raw ) {
    $image = $normalize_image( $raw );
    if ( ! $image ) continue;
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
    // ── Meta bar (miles / days / dates), rendered below the Trip Overview heading
    $meta_bar_html = '';
    if ( $distance || $trip_duration || $start_date || $end_date ) {
      $meta_bar_html .= '<div class="trip-meta-bar">';

      if ( $distance ) {
        $meta_bar_html .= '<div class="trip-meta-item">'
          . custom_theme_trip_meta_icon( 'route' )
          . '<span>' . esc_html( custom_theme_format_distance( $distance ) ) . ' miles</span>'
          . '</div>';
      }

      if ( $trip_duration ) {
        $meta_bar_html .= '<div class="trip-meta-item">'
          . custom_theme_trip_meta_icon( 'sun' )
          . '<span>' . esc_html( custom_theme_format_days( $trip_duration ) ) . '</span>'
          . '</div>';
      }

      if ( $start_date || $end_date ) {
        $start = $start_date ? date_i18n( 'M j, Y', strtotime( $start_date ) ) : '';
        $end   = $end_date   ? date_i18n( 'M j, Y', strtotime( $end_date ) )   : '';
        $range = ( $start && $end && $start !== $end ) ? $start . ' – ' . $end : ( $start ?: $end );
        $meta_bar_html .= '<div class="trip-meta-item">'
          . custom_theme_trip_meta_icon( 'calendar' )
          . '<span>' . esc_html( $range ) . '</span>'
          . '</div>';
      }

      $meta_bar_html .= '</div>';
    }
    ?>

    <?php
    // ── Sidebar navigation ───────────────────────────────────────
    $nav_items = [];
    if ( $trip_overview ) $nav_items[] = [ 'icon' => 'compass', 'label' => 'Trip Overview', 'href' => '#trip-overview' ];
    if ( $map_url )       $nav_items[] = [ 'icon' => 'map', 'label' => 'Route Map', 'href' => '#trip-map' ];
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

    ?>

    <div class="trip-report-layout">

    <header class="trip-report-heading">
      <p class="trip-report-eyebrow">Trip Report</p>
      <h1 class="entry-title trip-report-title"><?php the_title(); ?></h1>
    </header>

    <aside class="trip-report-sidebar">
      <?php
      get_template_part( 'template-parts/trail/trip-nav', null, [
        'items' => $nav_items,
      ] );
      ?>
    </aside>

    <div class="trip-report-main">

    <?php
    // ── Trip overview ────────────────────────────────────────────
    if ( $trip_overview ) {
      get_template_part( 'template-parts/trail/section', null, [
        'id'             => 'trip-overview',
        'title'          => 'Trip Overview',
        'before_content' => $meta_bar_html,
        'content'        => $trip_overview,
      ] );
    } elseif ( $meta_bar_html ) {
      echo $meta_bar_html;
    }
    ?>

    <?php
    // ── Map ──────────────────────────────────────────────────────
    if ( $map_url ) :
      $map_src = add_query_arg( 'embed', 'True', esc_url_raw( $map_url ) );
      echo '<div class="trail-section trail-map" id="trip-map">';
      echo '<h2 class="trail-section-title">Route Map</h2>';
      echo '<div class="trail-section-content trail-map-embed">';
      if ( $map_description ) {
        echo wp_kses_post( $map_description );
      }
      echo '<iframe src="' . esc_url( $map_src ) . '" style="border:none; width:100%; height:600px;" loading="lazy"></iframe>';
      echo '</div></div>';
    endif;
    ?>

    <?php
    // ── Day by Day ───────────────────────────────────────────────
    if ( ! empty( $days ) ) :
    ?>
      <div class="trail-section trip-days-section" id="trip-days">
        <?php foreach ( $days as $num => $day ) : ?>
          <div class="trip-day" id="trip-day-<?php echo esc_attr( $num ); ?>">
            <h2 class="trip-day-title"><?php
              echo '<span class="trip-day-number">Day ' . esc_html( $num ) . '</span>';
              if ( $day['title'] ) {
                echo '<span class="trip-day-sep"> – </span><span class="trip-day-name">' . esc_html( $day['title'] ) . '</span>';
              }
            ?></h2>
            <?php if ( $day['desc'] ) : ?>
              <div class="trail-section-content trip-day-description">
                <?php echo wp_kses_post( $day['desc'] ); ?>
              </div>
            <?php endif; ?>
            <?php $render_gallery( $day['pictures'], 'trip-day-gallery' ); ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php
    // ── Gear notes ───────────────────────────────────────────────
    $gear_gallery_html = '';
    if ( $gear_photos ) {
      ob_start();
      $render_gallery( $gear_photos, 'trip-gear-gallery' );
      $gear_gallery_html = ob_get_clean();
    }
    get_template_part( 'template-parts/trail/section', null, [
      'id'            => 'trip-gear',
      'title'         => 'Gear Notes',
      'content'       => $gear_notes,
      'after_content' => $gear_gallery_html,
    ] );
    ?>

    <?php
    // ── Resource links ───────────────────────────────────────────
    get_template_part( 'template-parts/trail/section', null, [
      'id'      => 'trip-resources',
      'title'   => 'Resources & Links',
      'content' => $resource_links,
    ] );
    ?>

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
      echo '</div></div>';
    endif;
    ?>

    <?php
    // ── Photo gallery ────────────────────────────────────────────
    if ( $photo_gallery && is_array( $photo_gallery ) ) :
    ?>
      <div class="trail-section trail-photo-gallery" id="trip-gallery">
        <h2 class="trail-section-title">Photo Gallery</h2>
        <div class="trail-gallery-grid" data-collapsed="true">
          <?php foreach ( $photo_gallery as $raw ) :
            $image = $normalize_image( $raw );
            if ( ! $image ) continue;
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
        <button class="trail-gallery-toggle" type="button">Show all photos</button>
      </div>
    <?php endif; ?>

    </div><!-- .trip-report-main -->

    </div><!-- .trip-report-layout -->

  </div><!-- .trail-content-wrap -->

  <?php astra_entry_bottom(); ?>
</article>
<?php astra_entry_after(); ?>
