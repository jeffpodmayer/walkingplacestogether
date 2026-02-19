<?php
/**
 * Custom Theme functions and definitions
 */

// Enqueue parent and child theme styles
add_action( 'wp_enqueue_scripts', 'custom_theme_enqueue_styles', 15 );

function custom_theme_enqueue_styles() {
    wp_enqueue_style(
        'custom-theme-parent-style',
        get_template_directory_uri() . '/style.css'
    );
    wp_enqueue_style(
        'custom-theme-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( 'custom-theme-parent-style' ),
        wp_get_theme()->get( 'Version' )
    );
}

// Load Trail post type styles only on single trail pages.
add_action( 'wp_enqueue_scripts', 'custom_theme_enqueue_trail_styles', 20 );

function custom_theme_enqueue_trail_styles() {
	if ( ! is_singular( 'trail' ) ) {
		return;
	}
	wp_enqueue_style(
		'custom-theme-trail',
		get_stylesheet_directory_uri() . '/css/trail.css',
		array( 'custom-theme-child-style' ),
		wp_get_theme()->get( 'Version' )
	);
}

// functions.php
add_action( 'wp_enqueue_scripts', function() {
    if ( ! is_singular( 'trail' ) ) {
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
  });

  add_action( 'wp_enqueue_scripts', function() {
    if ( ! is_singular( 'trail' ) ) {
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
  });

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
