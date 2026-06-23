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

$trip_date = '';
if ( $start_date ) {
  $parsed_date = custom_theme_parse_acf_date( $start_date );
  $trip_date   = $parsed_date ? date_i18n( 'M j, Y', $parsed_date->getTimestamp() ) : '';
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
    if ( ! $image ) {
      continue;
    }

    echo '<figure class="trail-gallery-item">';
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

    <div class="trail-title-row">
      <h1 class="entry-title"><?php the_title(); ?></h1>
    </div>

    <?php if ( $distance || $trip_date ) : ?>
      <div class="trail-details-container">
        <?php if ( $distance ) : ?>
          <div class="trail-detail-card">
            <span class="trail-detail-value js-count" data-count="<?php echo esc_attr( $distance ); ?>"><?php echo esc_html( $distance ); ?></span>
            <span class="trail-detail-label">Miles</span>
          </div>
        <?php endif; ?>

        <?php if ( $trip_date ) : ?>
          <div class="trail-detail-card trail-detail-card--date">
            <span class="trail-detail-value"><?php echo esc_html( $trip_date ); ?></span>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php
    $quick_links = [];
    if ( $map_url )       $quick_links['Map']             = '#day-trip-map';
    if ( $trip_overview )   $quick_links['Trip Report'] = '#day-trip-report';
    if ( $gear_notes )      $quick_links['Gear']        = '#day-trip-gear';
    if ( $resource_links )  $quick_links['Resources & Links']     = '#day-trip-resources';
    if ( $related_reports ) $quick_links['Related Trip Reports'] = '#day-trip-related';
    if ( $photo_gallery )   $quick_links['Photo Gallery']         = '#day-trip-gallery';

    get_template_part( 'template-parts/trail/quick-links', null, [
      'links' => $quick_links,
    ] );
    ?>

    <?php if ( $map_url ) : ?>
      <?php $map_src = add_query_arg( 'embed', 'True', esc_url_raw( $map_url ) ); ?>
      <div class="trail-section trail-map" id="day-trip-map">
        <h2 class="trail-section-title">Map</h2>
        <div class="trail-section-content trail-map-embed">
          <?php
          if ( $map_description ) {
            echo wp_kses_post( $map_description );
          }
          ?>
          <iframe src="<?php echo esc_url( $map_src ); ?>" style="border:none; width:100%; height:600px;" loading="lazy"></iframe>
        </div>
      </div>
    <?php endif; ?>

    <?php if ( $trip_overview ) : ?>
      <div class="trail-section" id="day-trip-report">
        <h2 class="trail-section-title">Trip Report</h2>
        <div class="trail-section-content">
          <?php echo wp_kses_post( $trip_overview ); ?>
        </div>
      </div>
    <?php endif; ?>

    <?php
    get_template_part( 'template-parts/trail/section', null, [
      'id'      => 'day-trip-gear',
      'title'   => 'Gear',
      'content' => $gear_notes,
    ] );
    ?>

    <?php
    get_template_part( 'template-parts/trail/section', null, [
      'id'      => 'day-trip-resources',
      'title'   => 'Resources & Links',
      'content' => $resource_links,
    ] );
    ?>

    <?php
    if ( $related_reports ) :
      if ( ! is_array( $related_reports ) ) {
        $related_reports = [ $related_reports ];
      }
      echo '<div class="trail-section trail-related-posts" id="day-trip-related">';
      echo '<h2 class="trail-section-title">Related Trip Reports</h2>';
      echo '<div class="related-posts-grid">';
      foreach ( $related_reports as $post_obj ) {
        if ( ! $post_obj ) {
          continue;
        }
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

    <?php if ( $photo_gallery && is_array( $photo_gallery ) ) : ?>
      <div class="trail-section trail-photo-gallery" id="day-trip-gallery">
        <h2 class="trail-section-title">Photo Gallery</h2>
        <?php $render_gallery( $photo_gallery, 'day-trip-gallery' ); ?>
      </div>
    <?php endif; ?>

  </div><!-- .trail-content-wrap -->

  <?php astra_entry_bottom(); ?>
</article>
<?php astra_entry_after(); ?>
