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
