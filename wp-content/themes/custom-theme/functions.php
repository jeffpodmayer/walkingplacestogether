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

/**
 * Compact day-level trip date range for the overview meta bar.
 * Same month/year: May 23 – 25, 2026
 * Same year:       May 23 – Jun 2, 2026
 * Otherwise:       Dec 28, 2025 – Jan 3, 2026
 */
function custom_theme_format_trip_date_range( $start_date, $end_date = '' ) {
  $start = custom_theme_parse_acf_date( $start_date );
  $end   = custom_theme_parse_acf_date( $end_date );

  if ( ! $start && ! $end ) {
    return '';
  }

  if ( ! $start ) {
    return date_i18n( 'M j, Y', $end->getTimestamp() );
  }

  if ( ! $end || $start->format( 'Ymd' ) === $end->format( 'Ymd' ) ) {
    return date_i18n( 'M j, Y', $start->getTimestamp() );
  }

  $same_year  = $start->format( 'Y' ) === $end->format( 'Y' );
  $same_month = $same_year && $start->format( 'm' ) === $end->format( 'm' );

  if ( $same_month ) {
    return date_i18n( 'M j', $start->getTimestamp() ) . ' – ' . date_i18n( 'j, Y', $end->getTimestamp() );
  }

  if ( $same_year ) {
    return date_i18n( 'M j', $start->getTimestamp() ) . ' – ' . date_i18n( 'M j, Y', $end->getTimestamp() );
  }

  return date_i18n( 'M j, Y', $start->getTimestamp() ) . ' – ' . date_i18n( 'M j, Y', $end->getTimestamp() );
}

function custom_theme_format_distance( $distance, $decimals = null ) {
  $distance = (float) $distance;

  if ( $decimals !== null ) {
    $decimals = (int) $decimals;
    if ( $decimals <= 0 ) {
      return (string) (int) round( $distance );
    }
    return number_format( $distance, $decimals );
  }

  if ( $distance == (int) $distance ) {
    return (string) (int) $distance;
  }
  return number_format( $distance, 1 );
}

function custom_theme_format_days( $days ) {
  $days = (int) $days;
  return $days === 1 ? '1 day' : $days . ' days';
}

function custom_theme_format_elevation( $feet ) {
  $feet = (float) $feet;
  if ( $feet == (int) $feet ) {
    return number_format( (int) $feet );
  }
  return number_format( $feet, 1 );
}

// Inline SVG icons for the trip report meta bar (route / sun / calendar).
function custom_theme_trip_meta_icon( $name ) {
  $attrs = 'class="trip-meta-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"';

  switch ( $name ) {
    case 'route':
      return '<svg ' . $attrs . '><circle cx="6" cy="19" r="3"/><path d="M9 19h8.5a3.5 3.5 0 0 0 0-7h-11a3.5 3.5 0 0 1 0-7H15"/><circle cx="18" cy="5" r="3"/></svg>';
    case 'sun':
      return '<svg ' . $attrs . '><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>';
    case 'calendar':
      return '<svg ' . $attrs . '><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>';
    case 'trending-up':
      return '<svg ' . $attrs . '><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>';
    case 'trending-down':
      return '<svg ' . $attrs . '><polyline points="22 17 13.5 8.5 8.5 13.5 2 7"/><polyline points="16 17 22 17 22 11"/></svg>';
    case 'tent':
      return '<svg ' . $attrs . '><path d="M3.5 21 14 3"/><path d="M20.5 21 10 3"/><path d="M15.5 21 12 15l-3.5 6"/><path d="M2 21h20"/></svg>';
    case 'mountain':
      return '<svg ' . $attrs . '><path d="m8 3 4 8 5-5 5 15H2L8 3z"/></svg>';
    case 'compass':
      return '<svg ' . $attrs . '><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>';
    case 'map':
      return '<svg ' . $attrs . '><path d="M14.106 5.553a2 2 0 0 0 1.788 0l3.659-1.83A1 1 0 0 1 21 4.619v12.764a1 1 0 0 1-.553.894l-4.553 2.277a2 2 0 0 1-1.788 0l-4.212-2.106a2 2 0 0 0-1.788 0l-3.659 1.83A1 1 0 0 1 3 19.381V6.618a1 1 0 0 1 .553-.894l4.553-2.277a2 2 0 0 1 1.788 0z"/><path d="M15 5.764v15"/><path d="M9 3.236v15"/></svg>';
    case 'backpack':
      return '<svg ' . $attrs . '><path d="M4 10a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M8 21v-5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v5"/><path d="M8 10h8"/></svg>';
    case 'link':
      return '<svg ' . $attrs . '><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>';
    case 'image':
      return '<svg ' . $attrs . '><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>';
    case 'bookmark':
      return '<svg ' . $attrs . '><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/></svg>';
    case 'footprints':
      return '<svg ' . $attrs . '><path d="M4 16v-2.38C4 11.5 2.97 10.5 3 8c.03-2.72 1.49-6 4.5-6C9.37 2 10 3.8 10 5.5c0 3.11-2 5.66-2 8.68V16a2 2 0 1 1-4 0Z"/><path d="M20 20v-2.38c0-2.12 1.03-3.12 1-5.62-.03-2.72-1.49-6-4.5-6C14.63 6 14 7.8 14 9.5c0 3.11 2 5.66 2 8.68V20a2 2 0 1 0 4 0Z"/><path d="M16 17h4"/><path d="M4 13h4"/></svg>';
    case 'list':
      return '<svg ' . $attrs . '><path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/></svg>';
    case 'more':
      return '<svg ' . $attrs . '><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>';
  }
  return '';
}

// Disable Astra's Scroll to Top (conflicts with map preview control).
add_filter( 'astra_get_option_scroll-to-top-enable', '__return_false' );

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
    $global_css = get_stylesheet_directory() . '/css/global.css';
    wp_enqueue_style(
        'custom-theme-global',
        get_stylesheet_directory_uri() . '/css/global.css',
        array( 'custom-theme-child-style' ),
        file_exists( $global_css ) ? (string) filemtime( $global_css ) : wp_get_theme()->get( 'Version' )
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
    array( 'custom-theme-child-style', 'glightbox' ),
    filemtime( get_stylesheet_directory() . '/css/trail.css' )
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

  wp_add_inline_style(
    'glightbox',
    '#glightbox-body .ginner-container{'
    . 'width:fit-content!important;max-width:100%!important;margin:0 auto!important;'
    . 'display:flex!important;flex-direction:column!important;align-items:stretch!important;}'
    . '#glightbox-body .gslide-description{'
    . 'position:static!important;width:100%!important;margin:0!important;'
    . 'padding:1rem 1.25rem!important;background:#000!important;'
    . 'text-align:center!important;display:block!important;box-sizing:border-box!important;}'
    . '#glightbox-body .gdesc-inner,#glightbox-body .gslide-title,#glightbox-body .gslide-desc{'
    . 'text-align:center!important;width:100%!important;margin:0 auto!important;float:none!important;color:#fff!important;}'
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
    'document.addEventListener("DOMContentLoaded",function(){'
    . 'var lb=GLightbox({selector:".trail-lightbox"});'
    . 'function fixCaptions(){'
    . 'var slide=document.querySelector("#glightbox-body .gslide.current")||document.querySelector("#glightbox-body .gslide");'
    . 'if(!slide)return;'
    . 'var img=slide.querySelector(".gslide-media img,.gslide-image img");'
    . 'var desc=slide.querySelector(".gslide-description");'
    . 'var wrap=slide.querySelector(".ginner-container");'
    . 'if(img&&wrap){wrap.style.setProperty("width",img.getBoundingClientRect().width+"px","important");}'
    . 'if(desc){'
    . 'desc.style.setProperty("position","static","important");'
    . 'desc.style.setProperty("width","100%","important");'
    . 'desc.style.setProperty("text-align","center","important");'
    . 'desc.style.setProperty("background","#000","important");'
    . 'desc.style.setProperty("display","block","important");'
    . '}'
    . 'slide.querySelectorAll(".gslide-title,.gslide-desc,.gdesc-inner").forEach(function(el){'
    . 'el.style.setProperty("text-align","center","important");'
    . 'el.style.setProperty("width","100%","important");'
    . 'el.style.setProperty("color","#fff","important");'
    . '});'
    . '}'
    . 'lb.on("open",function(){requestAnimationFrame(fixCaptions);});'
    . 'lb.on("slide_changed",function(){requestAnimationFrame(fixCaptions);});'
    . 'window.addEventListener("resize",function(){if(document.getElementById("glightbox-body"))fixCaptions();});'
    . '});'
  );
} );

/**
 * Resolve a human caption for a content image (prefer caption over alt/UUID).
 */
function custom_theme_lightbox_image_caption( DOMElement $img, DOMXPath $xpath ) {
  // Figcaption beside the image.
  $fig = $xpath->query( 'ancestor::figure[1]//figcaption', $img )->item( 0 );
  if ( $fig ) {
    $text = trim( preg_replace( '/\s+/', ' ', $fig->textContent ) );
    if ( $text !== '' ) {
      return $text;
    }
  }

  // Classic editor caption.
  $cap = $xpath->query( 'ancestor::*[contains(concat(" ", normalize-space(@class), " "), " wp-caption ")][1]//*[contains(concat(" ", normalize-space(@class), " "), " wp-caption-text ")]', $img )->item( 0 );
  if ( $cap ) {
    $text = trim( preg_replace( '/\s+/', ' ', $cap->textContent ) );
    if ( $text !== '' ) {
      return $text;
    }
  }

  // Media library caption / title from attachment ID.
  if ( preg_match( '/wp-image-(\d+)/', $img->getAttribute( 'class' ), $match ) ) {
    $id = (int) $match[1];
    $caption = wp_get_attachment_caption( $id );
    if ( is_string( $caption ) && trim( $caption ) !== '' ) {
      return trim( $caption );
    }
    $title = get_the_title( $id );
    if ( is_string( $title ) && trim( $title ) !== '' && ! custom_theme_lightbox_is_junk_label( $title ) ) {
      return trim( $title );
    }
  }

  $alt = trim( $img->getAttribute( 'alt' ) );
  if ( $alt !== '' && ! custom_theme_lightbox_is_junk_label( $alt ) ) {
    return $alt;
  }

  $title_attr = trim( $img->getAttribute( 'title' ) );
  if ( $title_attr !== '' && ! custom_theme_lightbox_is_junk_label( $title_attr ) ) {
    return $title_attr;
  }

  return '';
}

/**
 * Detect UUID / hash-like labels that shouldn't appear as captions.
 */
function custom_theme_lightbox_is_junk_label( $label ) {
  $label = trim( $label );
  if ( $label === '' ) {
    return true;
  }

  // UUID with or without hyphens/spaces.
  if ( preg_match( '/^[0-9a-f]{8}[- ]?[0-9a-f]{4}[- ]?[0-9a-f]{4}[- ]?[0-9a-f]{4}[- ]?[0-9a-f]{12}$/i', $label ) ) {
    return true;
  }

  // Bare hex hash / filename stem.
  if ( preg_match( '/^[0-9a-f]{16,}$/i', $label ) ) {
    return true;
  }

  return false;
}

/**
 * Make images in WYSIWYG / post content open in the same lightbox as galleries.
 */
function custom_theme_lightbox_content_images( $content ) {
  if ( ! is_string( $content ) || $content === '' || false === stripos( $content, '<img' ) ) {
    return $content;
  }

  if ( is_admin() && ! wp_doing_ajax() ) {
    return $content;
  }

  $wrapped = '<div id="custom-theme-lightbox-root">' . $content . '</div>';
  $dom     = new DOMDocument();
  $prev    = libxml_use_internal_errors( true );
  $dom->loadHTML( '<?xml encoding="utf-8">' . $wrapped, LIBXML_HTML_NODEFDTD );
  libxml_clear_errors();
  libxml_use_internal_errors( $prev );

  $root = $dom->getElementById( 'custom-theme-lightbox-root' );
  if ( ! $root ) {
    return $content;
  }

  $xpath = new DOMXPath( $dom );

  // Wrap bare images (not already inside a link).
  $imgs = $xpath->query( './/img[not(ancestor::a)]', $root );
  if ( $imgs ) {
    foreach ( $imgs as $img ) {
      $src = $img->getAttribute( 'src' );
      if ( ! $src ) {
        continue;
      }

      $href  = $src;
      $att_id = 0;
      if ( preg_match( '/wp-image-(\d+)/', $img->getAttribute( 'class' ), $match ) ) {
        $att_id = (int) $match[1];
        $full   = wp_get_attachment_image_url( $att_id, 'full' );
        if ( $full ) {
          $href = $full;
        }
      }

      $link = $dom->createElement( 'a' );
      $link->setAttribute( 'href', esc_url( $href ) );
      $link->setAttribute( 'class', 'trail-lightbox' );
      $caption = custom_theme_lightbox_image_caption( $img, $xpath );
      if ( $caption !== '' ) {
        $link->setAttribute( 'data-title', $caption );
      }

      $img->parentNode->insertBefore( $link, $img );
      $link->appendChild( $img );
    }
  }

  // Promote existing image links to the lightbox + fix titles.
  $links = $xpath->query( './/a[.//img]', $root );
  if ( $links ) {
    foreach ( $links as $link ) {
      $class = $link->getAttribute( 'class' );
      $href  = $link->getAttribute( 'href' );
      $is_image_link = (bool) preg_match( '/\.(jpe?g|png|gif|webp|avif)(\?|#|$)/i', $href )
        || false !== strpos( $href, '/uploads/' )
        || strpos( $class, 'trail-lightbox' ) !== false;

      if ( ! $is_image_link ) {
        continue;
      }

      if ( strpos( $class, 'trail-lightbox' ) === false ) {
        $link->setAttribute( 'class', trim( $class . ' trail-lightbox' ) );
      }

      $img = $xpath->query( './/img', $link )->item( 0 );
      if ( ! $img ) {
        continue;
      }

      $caption = custom_theme_lightbox_image_caption( $img, $xpath );
      $existing = $link->getAttribute( 'data-title' );
      if ( $caption !== '' ) {
        $link->setAttribute( 'data-title', $caption );
      } elseif ( $existing !== '' && custom_theme_lightbox_is_junk_label( $existing ) ) {
        $link->removeAttribute( 'data-title' );
      }
    }
  }

  $out = '';
  foreach ( $root->childNodes as $child ) {
    $out .= $dom->saveHTML( $child );
  }

  return $out !== '' ? $out : $content;
}
add_filter( 'acf_the_content', 'custom_theme_lightbox_content_images', 20 );
add_filter( 'the_content', 'custom_theme_lightbox_content_images', 20 );

add_action( 'wp_enqueue_scripts', function() {
  if ( ! custom_theme_is_trail_page() ) {
    return;
  }
  wp_enqueue_script(
    'trail',
    get_stylesheet_directory_uri() . '/js/trail.js',
    array( 'glightbox' ),
    filemtime( get_stylesheet_directory() . '/js/trail.js' ),
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
