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

    <div class="trail-title-row">
      <h1 class="entry-title"><?php the_title(); ?></h1>
    </div>

    <?php
    // ── Detail cards ─────────────────────────────────────────────
    if ( $distance || $trip_duration || $start_date || $end_date ) :
      echo '<div class="trail-details-container">';

      if ( $distance ) {
        echo '<div class="trail-detail-card">';
        echo '<span class="trail-detail-value js-count" data-count="' . esc_attr( $distance ) . '">' . esc_html( $distance ) . '</span>';
        echo '<span class="trail-detail-label">Miles</span>';
        echo '</div>';
      }

      if ( $trip_duration ) {
        echo '<div class="trail-detail-card">';
        echo '<span class="trail-detail-value js-count" data-count="' . esc_attr( $trip_duration ) . '">' . esc_html( $trip_duration ) . '</span>';
        echo '<span class="trail-detail-label">Days</span>';
        echo '</div>';
      }

      if ( $start_date || $end_date ) {
        $start = $start_date ? date_i18n( 'M j, Y', strtotime( $start_date ) ) : '';
        $end   = $end_date   ? date_i18n( 'M j, Y', strtotime( $end_date ) )   : '';
        echo '<div class="trail-detail-card trail-detail-card--secondary">';
        echo '<span class="trail-detail-value">'
          . esc_html( $start )
          . '<br><span class="trail-date-arrow">↓</span><br>'
          . esc_html( $end )
          . '</span>';
        echo '</div>';
      }

      echo '</div>';
    endif;
    ?>

    <?php
    // ── Quick links ──────────────────────────────────────────────
    $quick_links = [];
    if ( $trip_overview )  $quick_links['Trip Overview']    = '#trip-overview';
    if ( $map_url )        $quick_links['Map']              = '#trip-map';
    foreach ( $days as $num => $day ) {
      $label = 'Day ' . $num;
      if ( ! empty( $day['title'] ) ) {
        $label .= ' – ' . $day['title'];
      }
      $quick_links[ $label ] = '#trip-day-' . $num;
    }
    if ( $gear_notes )     $quick_links['Gear Notes']       = '#trip-gear';
    if ( $resource_links ) $quick_links['Resources & Links'] = '#trip-resources';
    if ( $photo_gallery )  $quick_links['Photo Gallery']    = '#trip-gallery';
    if ( $related_reports ) $quick_links['Related Trip Reports'] = '#trip-related';

    get_template_part( 'template-parts/trail/quick-links', null, [
      'links' => $quick_links,
    ] );
    ?>

    <?php
    // ── Trip overview ────────────────────────────────────────────
    get_template_part( 'template-parts/trail/section', null, [
      'id'      => 'trip-overview',
      'title'   => 'Trip Overview',
      'content' => $trip_overview,
    ] );
    ?>

    <?php
    // ── Map ──────────────────────────────────────────────────────
    if ( $map_url ) :
      $map_src = add_query_arg( 'embed', 'True', esc_url_raw( $map_url ) );
      echo '<div class="trail-section trail-map" id="trip-map">';
      echo '<h2 class="trail-section-title">Map</h2>';
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
    get_template_part( 'template-parts/trail/section', null, [
      'id'      => 'trip-gear',
      'title'   => 'Gear Notes',
      'content' => $gear_notes,
    ] );
    $render_gallery( $gear_photos, 'trip-gear-gallery' );
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

  </div><!-- .trail-content-wrap -->

  <?php astra_entry_bottom(); ?>
</article>
<?php astra_entry_after(); ?>
