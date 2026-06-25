<?php
/**
 * Custom Theme functions and definitions
 */

// Ensure featured image (post thumbnail) support is enabled for all post types.
add_action( 'after_setup_theme', function() {
  add_theme_support( 'post-thumbnails' );
} );

// Explicitly register thumbnail support on the post type itself.
add_action( 'init', function() {
  add_post_type_support( 'post', 'thumbnail' );
  add_post_type_support( 'trail', 'thumbnail' );
}, 20 );

function custom_theme_get_image_url( $image, $size = 'large' ) {
  if ( empty( $image ) ) {
    return '';
  }

  if ( is_array( $image ) ) {
    return $image['sizes'][ $size ] ?? $image['url'] ?? '';
  }

  if ( is_numeric( $image ) ) {
    return wp_get_attachment_image_url( (int) $image, $size ) ?: '';
  }

  return is_string( $image ) ? $image : '';
}

function custom_theme_parse_acf_date( $date ) {
  if ( empty( $date ) ) {
    return null;
  }

  if ( $date instanceof DateTimeInterface ) {
    return $date;
  }

  $date = trim( (string) $date );
  if ( preg_match( '/^\d{8}$/', $date ) ) {
    $parsed = DateTime::createFromFormat( 'Ymd', $date );
    return $parsed ?: null;
  }

  $timestamp = strtotime( $date );
  return $timestamp ? ( new DateTime() )->setTimestamp( $timestamp ) : null;
}

function custom_theme_format_trail_date_range( $start_date, $end_date = '' ) {
  $start = custom_theme_parse_acf_date( $start_date );
  $end   = custom_theme_parse_acf_date( $end_date );

  if ( ! $start && ! $end ) {
    return '';
  }

  if ( ! $start ) {
    return $end->format( 'F Y' );
  }

  if ( ! $end || $start->format( 'Ym' ) === $end->format( 'Ym' ) ) {
    return $start->format( 'F Y' );
  }

  if ( $start->format( 'Y' ) === $end->format( 'Y' ) ) {
    return $start->format( 'F' ) . ' - ' . $end->format( 'F Y' );
  }

  return $start->format( 'F Y' ) . ' - ' . $end->format( 'F Y' );
}

function custom_theme_format_distance( $distance ) {
  $distance = (float) $distance;
  if ( $distance == (int) $distance ) {
    return (string) (int) $distance;
  }
  return number_format( $distance, 1 );
}

function custom_theme_format_days( $days ) {
  $days = (int) $days;
  return $days === 1 ? '1 day' : $days . ' days';
}

// Enqueue Google Fonts, parent/child styles, and global stylesheet
add_action( 'wp_enqueue_scripts', 'custom_theme_enqueue_styles', 15 );

function custom_theme_enqueue_styles() {
    wp_enqueue_style(
        'custom-theme-google-fonts',
        'https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600;700&family=Karla:wght@400;500;600&display=swap',
        array(),
        null
    );
    wp_enqueue_style(
        'custom-theme-parent-style',
        get_template_directory_uri() . '/style.css',
        array( 'custom-theme-google-fonts' )
    );
    wp_enqueue_style(
        'custom-theme-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( 'custom-theme-parent-style' ),
        wp_get_theme()->get( 'Version' )
    );
    wp_enqueue_style(
        'custom-theme-global',
        get_stylesheet_directory_uri() . '/css/global.css',
        array( 'custom-theme-child-style' ),
        wp_get_theme()->get( 'Version' )
    );
}

/**
 * Returns true on trail CPT pages and on regular posts
 * where the is_trip_report ACF field is enabled.
 */
function custom_theme_is_trail_page() {
  if ( is_singular( 'trail' ) ) {
    return true;
  }
  if (
    is_singular( 'post' )
    && function_exists( 'get_field' )
    && ( get_field( 'is_trip_report' ) || get_field( 'is_day_trip_report' ) )
  ) {
    return true;
  }
  return false;
}

// Load trail styles on trail CPT pages and trip report posts.
add_action( 'wp_enqueue_scripts', 'custom_theme_enqueue_trail_styles', 20 );

function custom_theme_enqueue_trail_styles() {
  if ( ! custom_theme_is_trail_page() ) {
    return;
  }
  wp_enqueue_style(
    'custom-theme-trail',
    get_stylesheet_directory_uri() . '/css/trail.css',
    array( 'custom-theme-child-style' ),
    wp_get_theme()->get( 'Version' )
  );
}

add_action( 'wp_enqueue_scripts', function() {
  if ( ! custom_theme_is_trail_page() ) {
    return;
  }

  wp_enqueue_style(
    'glightbox',
    'https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css',
    array(),
    '3.3.0'
  );

  wp_enqueue_script(
    'glightbox',
    'https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js',
    array(),
    '3.3.0',
    true
  );

  wp_add_inline_script(
    'glightbox',
    'document.addEventListener("DOMContentLoaded",function(){GLightbox({selector: ".trail-lightbox"});});'
  );
} );

add_action( 'wp_enqueue_scripts', function() {
  if ( ! custom_theme_is_trail_page() ) {
    return;
  }
  wp_enqueue_script(
    'trail',
    get_stylesheet_directory_uri() . '/js/trail.js',
    array( 'glightbox' ),
    wp_get_theme()->get( 'Version' ),
    true
  );
  wp_oembed_add_provider(
    '#https?://(www\.)?gaiagps\.com/public/.*#i',
    'https://www.gaiagps.com/oembed',
    true
  );
} );

  // AIOSEO: Append ACF content for SEO analysis
// ── Home page: full-width layout class ────────────────────────────────────
add_filter( 'body_class', function( $classes ) {
  if ( is_front_page() ) {
    $classes[] = 'ast-page-builder-template';
  }
  return $classes;
} );

// ── Archive pages: enqueue archive.css ───────────────────────────────────
add_action( 'wp_enqueue_scripts', function() {
  if ( ! is_page( [ 4380, 15 ] ) ) {
    return;
  }
  wp_enqueue_style(
    'custom-theme-archive',
    get_stylesheet_directory_uri() . '/css/archive.css',
    array( 'custom-theme-child-style' ),
    wp_get_theme()->get( 'Version' )
  );
} );

// ── Home page: enqueue home.css + home.js ─────────────────────────────────
add_action( 'wp_enqueue_scripts', function() {
  if ( ! is_front_page() ) {
    return;
  }
  wp_enqueue_style(
    'custom-theme-home',
    get_stylesheet_directory_uri() . '/css/home.css',
    array( 'custom-theme-child-style' ),
    wp_get_theme()->get( 'Version' )
  );
  wp_enqueue_script(
    'custom-theme-home',
    get_stylesheet_directory_uri() . '/js/home.js',
    array(),
    wp_get_theme()->get( 'Version' ),
    true
  );
} );

// ── Home page stats: clear cached totals when a trail or trip report is saved
add_action( 'save_post_trail', function() {
  delete_transient( 'custom_theme_home_stats' );
} );

add_action( 'save_post', function( $post_id ) {
  if ( get_post_type( $post_id ) !== 'post' || ! function_exists( 'get_field' ) ) {
    return;
  }
  if ( get_field( 'is_trip_report', $post_id ) || get_field( 'is_day_trip_report', $post_id ) ) {
    delete_transient( 'custom_theme_home_stats' );
  }
} );

// AIOSEO: Append ACF content for SEO analysis
add_filter( 'aioseo_content', function( $content ) {
  if ( ! is_singular( 'trail' ) || ! function_exists( 'get_field' ) ) {
    return $content;
  }

  $parts = [];

  // Text/WYSIWYG sections
  $parts[] = get_field( 'trail_overview' );
  $parts[] = get_field( 'experience' );
  $parts[] = get_field( 'logistics' );
  $parts[] = get_field( 'gear' );
  $parts[] = get_field( 'resources_links' );
  $parts[] = get_field( 'map_description' );

  // Key meta
  $parts[] = get_field( 'region' );
  $parts[] = get_field( 'direction_style' );
  $parts[] = get_field( 'start_date' );
  $parts[] = get_field( 'end_date' );
  $parts[] = get_field( 'distance' );
  $parts[] = get_field( 'trip_duration_days' );

  // Related post titles
  $related = get_field( 'related_blog_posts' );
  if ( $related && is_array( $related ) ) {
    foreach ( $related as $post ) {
      if ( is_object( $post ) && isset( $post->post_title ) ) {
        $parts[] = $post->post_title;
      }
    }
  }

  $acf_text = wp_strip_all_tags( implode( ' ', array_filter( $parts ) ) );

  return $content . ' ' . $acf_text;
});

// ── ACF Galerie 4: default return_format for legacy field definitions ────
add_filter( 'acf/load_field/type=galerie-4', function( $field ) {
  if ( empty( $field['return_format'] ) ) {
    $field['return_format'] = 'media_array';
  }
  return $field;
} );

// ── Admin: ACF Galerie 4 layout fixes in post editor ─────────────────────
add_action( 'admin_enqueue_scripts', function( $hook ) {
  if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
    return;
  }

  wp_enqueue_style(
    'custom-theme-admin-galerie-fix',
    get_stylesheet_directory_uri() . '/css/admin-galerie-fix.css',
    [],
    wp_get_theme()->get( 'Version' )
  );
}, 99 );

// ── Header site title: wrap custom-HTML <p> with a home page link ─────────
add_action( 'wp_footer', function() {
  $home = esc_url( home_url( '/' ) );
  ?>
  <script>
  (function () {
    var masthead = document.getElementById('masthead');
    if (!masthead) return;
    masthead.querySelectorAll('p').forEach(function (p) {
      if (p.closest('a')) return; // already linked
      var text = p.textContent.trim().toUpperCase();
      if (text.indexOf('WALKING PLACES TOGETHER') === -1) return;
      var a = document.createElement('a');
      a.href = '<?php echo $home; ?>';
      a.style.cssText = 'text-decoration:none;color:inherit;cursor:pointer;display:block;';
      p.parentNode.insertBefore(a, p);
      a.appendChild(p);
    });
  })();
  </script>
  <?php
} );
