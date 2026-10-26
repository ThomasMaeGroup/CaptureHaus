<?php
/**
 * Editing UX: keep long section-built pages compact in the admin.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Collapsed section rows read "Hero: Let them walk through…" instead of just "Hero".
 */
add_filter( 'acf/fields/flexible_content/layout_title/name=sections', function ( $title, $field, $layout ) {
	$name    = $layout['name'];
	$summary = '';

	if ( 'gallery' === $name ) {
		$count   = count( (array) get_sub_field( 'images' ) );
		$summary = sprintf( _n( '%d image', '%d images', $count, 'capturehaus' ), $count );
	} elseif ( 'ticker' === $name ) {
		$summary = implode( ' · ', array_slice( capturehaus_lines( get_sub_field( 'items' ) ), 0, 4 ) );
	} elseif ( 'sectors' === $name ) {
		$summary = get_sub_field( 'title' ) ?: get_sub_field( 'feature_title' );
	} else {
		$summary = get_sub_field( 'title' ) ?: get_sub_field( 'eyebrow' );
	}

	$summary = trim( preg_replace( '/\s+/', ' ', str_replace( '*', '', (string) $summary ) ) );
	if ( '' === $summary ) {
		return $title;
	}
	return esc_html( $title ) . ': <span style="font-weight:400;opacity:.75">' . esc_html( wp_trim_words( $summary, 10 ) ) . '</span>';
}, 10, 3 );

/**
 * Open the editor with every section and repeater row collapsed; click one to edit it.
 * Newly added sections/rows stay open.
 */
add_action( 'acf/input/admin_footer', function () {
	?>
	<script>
	(function () {
		if (typeof acf === 'undefined') return;
		acf.addAction('ready', function () {
			document.querySelectorAll('.acf-flexible-content > .values > .layout').forEach(function (layout) {
				layout.classList.add('-collapsed');
			});
			document.querySelectorAll('.acf-repeater .acf-row:not(.acf-clone)').forEach(function (row) {
				if (row.querySelector(':scope > .acf-row-handle [data-event="collapse-row"]')) row.classList.add('-collapsed');
			});
		});
	})();
	</script>
	<?php
} );

/**
 * Pages built from sections don't need the empty WordPress editor above them.
 * Plain pages (e.g. Privacy Policy) and new pages keep it.
 */
add_action( 'load-post.php', function () {
	$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0; // phpcs:ignore WordPress.Security.NonceVerification
	if ( ! $post_id || 'page' !== get_post_type( $post_id ) || ! function_exists( 'get_field' ) ) {
		return;
	}
	if ( get_field( 'sections', $post_id ) && '' === trim( (string) get_post_field( 'post_content', $post_id ) ) ) {
		remove_post_type_support( 'page', 'editor' );
	}
} );
