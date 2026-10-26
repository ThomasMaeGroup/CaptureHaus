<?php
/**
 * Small template helpers shared by the section templates.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Site setting from the "Site settings" options page. */
function capturehaus_option( $name ) {
	return function_exists( 'get_field' ) ? get_field( $name, 'option' ) : null;
}

/** Page sections for a post (defaults to the current one). */
function capturehaus_sections( $post_id = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}
	$sections = get_field( 'sections', $post_id ?: get_queried_object_id() );
	return is_array( $sections ) ? $sections : array();
}

/** True when the current page opens with a full-bleed hero (header starts transparent). */
function capturehaus_has_hero() {
	if ( ! is_singular() ) {
		return false;
	}
	$sections = capturehaus_sections();
	return ! empty( $sections ) && 'hero' === $sections[0]['acf_fc_layout'];
}

/** Quote form appears on every page unless switched off on that page. */
function capturehaus_show_quote() {
	if ( is_singular() && function_exists( 'get_field' ) && get_field( 'hide_quote', get_queried_object_id() ) ) {
		return false;
	}
	return true;
}

/**
 * Escape text, then turn *word* into <em>word</em> (or another tag/class),
 * and line breaks into <br> when asked. Lets editors add emphasis without HTML.
 */
function capturehaus_text( $text, $tag = 'em', $class = '', $breaks = false ) {
	$out  = esc_html( (string) $text );
	$open = $class ? sprintf( '<%s class="%s">', $tag, esc_attr( $class ) ) : "<$tag>";
	$out  = preg_replace( '/\*(.+?)\*/', $open . '$1</' . $tag . '>', $out );
	return $breaks ? nl2br( $out, false ) : $out;
}

/** Non-empty lines of a textarea. */
function capturehaus_lines( $text ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\R/', (string) $text ) ) ) );
}

/** Tick list from a one-item-per-line textarea. */
function capturehaus_ticks( $text, $light = false ) {
	$lines = capturehaus_lines( $text );
	if ( ! $lines ) {
		return '';
	}
	$out = '<ul class="ticks' . ( $light ? ' ticks--light' : '' ) . '">';
	foreach ( $lines as $line ) {
		$out .= '<li>' . esc_html( $line ) . '</li>';
	}
	return $out . '</ul>';
}

/** Button/link from an ACF link field. */
function capturehaus_button( $link, $class = 'btn btn--dark', $arrow = '' ) {
	if ( empty( $link['url'] ) ) {
		return '';
	}
	$target = ! empty( $link['target'] ) ? ' target="' . esc_attr( $link['target'] ) . '" rel="noopener"' : '';
	$arrow  = $arrow ? ' <span aria-hidden="true">' . esc_html( $arrow ) . '</span>' : '';
	return sprintf( '<a class="%s" href="%s"%s>%s%s</a>', esc_attr( $class ), esc_url( $link['url'] ), $target, esc_html( $link['title'] ), $arrow );
}

/** Responsive <img> for an attachment ID. */
function capturehaus_image( $id, $class = '', $sizes = '100vw', $eager = false, $alt = null ) {
	if ( ! $id ) {
		return '';
	}
	$attr = array(
		'class'   => $class,
		'sizes'   => $sizes,
		'loading' => $eager ? 'eager' : 'lazy',
	);
	if ( $eager ) {
		$attr['fetchpriority'] = 'high';
	}
	$attr = array_filter( $attr, 'strlen' );
	if ( null !== $alt ) {
		$attr['alt'] = $alt; // '' marks the image as decorative.
	}
	return wp_get_attachment_image( $id, 'full', false, $attr );
}

/** Image URL for an attachment ID. */
function capturehaus_image_url( $id, $size = 'full' ) {
	return $id ? (string) wp_get_attachment_image_url( $id, $size ) : '';
}

/** Lazy-loaded looping video (played only while on screen by main.js). */
function capturehaus_video( $url, $poster_id = 0, $class = '', $label = '' ) {
	if ( ! $url ) {
		return '';
	}
	$aria = $label ? ' aria-label="' . esc_attr( $label ) . '"' : ' aria-hidden="true"';
	return sprintf(
		'<video data-lazy-video%s muted loop playsinline preload="none"%s%s><source data-src="%s" type="video/mp4"></video>',
		$class ? ' class="' . esc_attr( $class ) . '"' : '',
		$poster_id ? ' poster="' . esc_url( capturehaus_image_url( $poster_id, 'large' ) ) . '"' : '',
		$aria,
		esc_url( $url )
	);
}

/** Phone number link target, e.g. 01672 559033 -> +441672559033. */
function capturehaus_tel( $phone ) {
	$digits = preg_replace( '/\D+/', '', (string) $phone );
	if ( str_starts_with( $digits, '0' ) ) {
		$digits = '44' . substr( $digits, 1 );
	}
	return 'tel:+' . $digits;
}

function capturehaus_phone_icon() {
	return '<svg class="icon-phone" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>';
}

/** Plain <a> links for a menu location (the design uses bare links, not <ul> lists). */
function capturehaus_menu_links( $location ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return '';
	}
	$items = wp_get_nav_menu_items( $locations[ $location ] );
	$out   = '';
	foreach ( (array) $items as $item ) {
		if ( $item->menu_item_parent ) {
			continue; // Single-level menus only.
		}
		$current = ( (int) $item->object_id === get_queried_object_id() && 'custom' !== $item->type ) ? ' aria-current="page"' : '';
		$target  = $item->target ? ' target="' . esc_attr( $item->target ) . '" rel="noopener"' : '';
		$out    .= sprintf( '<a href="%s"%s%s>%s</a>', esc_url( $item->url ), $current, $target, esc_html( $item->title ) );
	}
	return $out;
}

/** Anchor attribute for a section. */
function capturehaus_anchor( $section ) {
	return ! empty( $section['anchor'] ) ? ' id="' . esc_attr( sanitize_title( $section['anchor'] ) ) . '"' : '';
}

/** Output each page section with its template from template-parts/sections/. */
function capturehaus_render_sections( $post_id = null ) {
	foreach ( capturehaus_sections( $post_id ) as $i => $section ) {
		get_template_part( 'template-parts/sections/' . sanitize_key( $section['acf_fc_layout'] ), null, array( 'section' => $section, 'index' => $i ) );
	}
}
