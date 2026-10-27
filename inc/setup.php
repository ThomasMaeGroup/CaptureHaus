<?php
/**
 * Theme supports, menus, post types and assets.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'yoast-seo-breadcrumbs' );

	// Pages get an excerpt: it's the summary on "Related pages" cards.
	add_post_type_support( 'page', 'excerpt' );

	register_nav_menus( array(
		'primary' => __( 'Header menu', 'capturehaus' ),
		'footer'  => __( 'Footer menu', 'capturehaus' ),
	) );
} );

/* ---------- Case studies ---------- */
add_action( 'init', function () {
	register_post_type( 'case_study', array(
		'labels'        => array(
			'name'          => __( 'Case studies', 'capturehaus' ),
			'singular_name' => __( 'Case study', 'capturehaus' ),
			'add_new_item'  => __( 'Add case study', 'capturehaus' ),
			'edit_item'     => __( 'Edit case study', 'capturehaus' ),
			'all_items'     => __( 'All case studies', 'capturehaus' ),
		),
		'public'        => true,
		'has_archive'   => true,
		'rewrite'       => array( 'slug' => 'case-studies' ),
		'menu_icon'     => 'dashicons-camera',
		'menu_position' => 21,
		'show_in_rest'  => true,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
	) );
} );

/* ---------- Assets: version = file modified time, so caches refresh after every edit ---------- */
function capturehaus_asset_ver( $rel ) {
	$file = get_theme_file_path( $rel );
	return file_exists( $file ) ? (string) filemtime( $file ) : null;
}

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'capturehaus-base', get_theme_file_uri( 'assets/css/base.css' ), array(), capturehaus_asset_ver( 'assets/css/base.css' ) );
	wp_enqueue_style( 'capturehaus-site', get_theme_file_uri( 'assets/css/site.css' ), array( 'capturehaus-base' ), capturehaus_asset_ver( 'assets/css/site.css' ) );
	wp_enqueue_script( 'capturehaus-main', get_theme_file_uri( 'assets/js/main.js' ), array(), capturehaus_asset_ver( 'assets/js/main.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );

	if ( capturehaus_show_quote() ) {
		$portal = capturehaus_option( 'hubspot_portal_id' );
		$region = capturehaus_option( 'hubspot_region' ) ?: 'eu1';
		if ( $portal ) {
			wp_enqueue_script( 'hubspot-forms', sprintf( 'https://js-%s.hsforms.net/forms/embed/%s.js', rawurlencode( $region ), rawurlencode( $portal ) ), array(), null, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		}
	}
} );

/* Keep the front end lean: no emoji script, no block library CSS for section-built pages. */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

add_filter( 'body_class', function ( $classes ) {
	if ( capturehaus_has_hero() ) {
		$classes[] = 'has-hero';
	}
	return $classes;
} );

/* Theme colour for mobile browser chrome. */
add_action( 'wp_head', function () {
	echo '<meta name="theme-color" content="#1a222e">' . "\n";
}, 1 );

/* Uploads: allow SVG is deliberately NOT enabled (security). Logos live in the theme. */

/* Admin notice if ACF Pro is missing. */
add_action( 'admin_notices', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		echo '<div class="notice notice-error"><p><strong>CaptureHaus theme:</strong> please install and activate Advanced Custom Fields Pro.</p></div>';
	}
} );
