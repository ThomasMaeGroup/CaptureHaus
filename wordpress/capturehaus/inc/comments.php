<?php
/**
 * Comments are not used on this site: switch them off everywhere, front end and admin.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* No post type supports comments or trackbacks. */
add_action( 'init', function () {
	foreach ( get_post_types() as $type ) {
		remove_post_type_support( $type, 'comments' );
		remove_post_type_support( $type, 'trackbacks' );
	}
}, 100 );

/* Closed everywhere, and any existing comments are hidden. */
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 10 );
add_filter( 'get_comments_number', '__return_zero' );
add_filter( 'pre_option_default_comment_status', function () { return 'closed'; } );
add_filter( 'pre_option_default_ping_status', function () { return 'closed'; } );
add_filter( 'pre_option_default_pingback_flag', '__return_zero' );

/* No pingbacks, comment feeds or X-Pingback header. */
add_filter( 'xmlrpc_methods', function ( $methods ) {
	unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );
	return $methods;
} );
add_filter( 'wp_headers', function ( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
} );
add_filter( 'feed_links_show_comments_feed', '__return_false' );
add_action( 'template_redirect', function () {
	if ( is_comment_feed() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
} );
add_filter( 'rest_endpoints', function ( $endpoints ) {
	unset( $endpoints['/wp/v2/comments'], $endpoints['/wp/v2/comments/(?P<id>[\d]+)'] );
	return $endpoints;
} );

/* Admin: no Comments menu, Discussion settings, dashboard widget or admin bar icon. */
add_action( 'admin_menu', function () {
	remove_menu_page( 'edit-comments.php' );
	remove_submenu_page( 'options-general.php', 'options-discussion.php' );
}, 999 );
add_action( 'admin_init', function () {
	global $pagenow;
	if ( in_array( $pagenow, array( 'edit-comments.php', 'comment.php', 'options-discussion.php' ), true ) ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
} );
add_action( 'admin_bar_menu', function ( $bar ) {
	$bar->remove_node( 'comments' );
}, 999 );
add_action( 'wp_dashboard_setup', function () {
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
} );
/* Hide the "Comments" count in the dashboard's At a Glance box. */
add_action( 'admin_head-index.php', function () {
	echo '<style>#dashboard_right_now .comment-count,#dashboard_right_now .comment-mod-count,#latest-comments{display:none!important}</style>';
} );
/* Remove the Discussion panel from the block editor. */
add_action( 'enqueue_block_editor_assets', function () {
	wp_add_inline_script( 'wp-edit-post', "wp.domReady(function(){wp.data.dispatch('core/editor').removeEditorPanel&&wp.data.dispatch('core/editor').removeEditorPanel('discussion-panel');});" );
} );
