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

/** Layout name of the current page's first section ('' when it has none). */
function capturehaus_first_layout() {
	if ( ! is_singular() ) {
		return '';
	}
	$sections = capturehaus_sections();
	return $sections ? $sections[0]['acf_fc_layout'] : '';
}

/** True when the current page opens with a full-bleed hero (header starts transparent). */
function capturehaus_has_hero() {
	return 'hero' === capturehaus_first_layout();
}

/**
 * Breadcrumb trail for inner pages. Uses Yoast's (which also feeds its schema) when Yoast is active,
 * otherwise Home › parent pages › this page.
 */
function capturehaus_breadcrumbs() {
	if ( is_front_page() ) {
		return '';
	}
	if ( function_exists( 'yoast_breadcrumb' ) ) {
		$yoast = yoast_breadcrumb( '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'capturehaus' ) . '">', '</nav>', false );
		if ( $yoast ) {
			return $yoast;
		}
	}

	$trail = array( array( __( 'Home', 'capturehaus' ), home_url( '/' ) ) );
	if ( is_singular( 'case_study' ) ) {
		$trail[] = array( __( 'Case studies', 'capturehaus' ), get_post_type_archive_link( 'case_study' ) );
	} elseif ( ( is_singular( 'post' ) || is_category() ) && get_option( 'page_for_posts' ) ) {
		$trail[] = array( get_the_title( (int) get_option( 'page_for_posts' ) ), get_permalink( (int) get_option( 'page_for_posts' ) ) );
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_queried_object_id() ) ) as $ancestor ) {
			$trail[] = array( get_the_title( $ancestor ), get_permalink( $ancestor ) );
		}
	}
	if ( is_singular() ) {
		$current = get_the_title();
	} elseif ( is_home() ) {
		$current = get_the_title( (int) get_option( 'page_for_posts' ) ) ?: __( 'Blog', 'capturehaus' );
	} else {
		$current = is_category() ? single_cat_title( '', false ) : wp_strip_all_tags( get_the_archive_title() );
	}

	$out = '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'capturehaus' ) . '"><ol>';
	foreach ( $trail as $crumb ) {
		$out .= sprintf( '<li><a href="%s">%s</a></li>', esc_url( $crumb[1] ), esc_html( $crumb[0] ) );
	}
	return $out . '<li aria-current="page">' . esc_html( $current ) . '</li></ol></nav>';
}

/** Collect FAQ sections' questions; printed once as FAQPage structured data in the footer. */
function capturehaus_faq_schema_add( $items ) {
	foreach ( (array) $items as $faq ) {
		if ( ! empty( $faq['question'] ) && ! empty( $faq['answer'] ) ) {
			$GLOBALS['capturehaus_faqs'][] = array(
				'@type'          => 'Question',
				'name'           => wp_strip_all_tags( $faq['question'] ),
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => wp_kses( wpautop( $faq['answer'] ), array( 'p' => array(), 'br' => array(), 'a' => array( 'href' => array() ), 'ul' => array(), 'ol' => array(), 'li' => array(), 'strong' => array(), 'em' => array() ) ),
				),
			);
		}
	}
}

add_action( 'wp_footer', function () {
	if ( empty( $GLOBALS['capturehaus_faqs'] ) ) {
		return;
	}
	$schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $GLOBALS['capturehaus_faqs'],
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
} );

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

/** Parent company name (Site settings > Business). */
function capturehaus_parent_name() {
	return capturehaus_option( 'business_parent_name' ) ?: 'Bread & Butter';
}

/** Social profile URLs (the parent company's), from Site settings > Business. Used by the footer and schema. */
function capturehaus_social_links() {
	$links = capturehaus_lines( capturehaus_option( 'business_parent_social' ) );
	if ( ! $links ) {
		$links = array(
			'https://www.instagram.com/breadandbutterhq/',
			'https://www.facebook.com/people/Bread-Butter/61555481204664/',
			'https://www.linkedin.com/company/bread-butter-studio',
		);
	}
	return array_values( array_filter( $links, 'wp_http_validate_url' ) );
}

/** Footer icon buttons for the social profiles; the network is worked out from each link. */
function capturehaus_social_icons() {
	$networks = array(
		'instagram' => array( 'Instagram', '<rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.5" cy="6.5" r="1.2"/>' ),
		'facebook'  => array( 'Facebook', '<path d="M13.5 21v-7.5h2.6l.4-3h-3V8.6c0-.9.3-1.5 1.5-1.5h1.6V4.4c-.3 0-1.2-.1-2.3-.1-2.3 0-3.8 1.4-3.8 3.9v2.3H7.9v3h2.6V21z"/>' ),
		'linkedin'  => array( 'LinkedIn', '<path d="M5 3.5a2 2 0 1 1 0 4 2 2 0 0 1 0-4zM3.3 9h3.4v11.5H3.3zM9 9h3.3v1.6h.1c.5-.9 1.6-1.9 3.3-1.9 3.5 0 4.2 2.3 4.2 5.3v6.5h-3.4v-5.8c0-1.4 0-3.1-1.9-3.1s-2.2 1.5-2.2 3v5.9H9z"/>' ),
		'youtube'   => array( 'YouTube', '<path d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2C2 8.8 2 12 2 12s0 3.2.4 4.8a2.5 2.5 0 0 0 1.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8c.4-1.6.4-4.8.4-4.8s0-3.2-.4-4.8zM10 15V9l5.2 3z"/>' ),
		'tiktok'    => array( 'TikTok', '<path d="M16.6 3h-3.1v12.3a2.6 2.6 0 1 1-2.6-2.6c.3 0 .5 0 .8.1V9.6a5.7 5.7 0 1 0 4.9 5.7V9a7 7 0 0 0 4 1.3V7.2A4 4 0 0 1 16.6 3z"/>' ),
	);
	$out = '';
	foreach ( capturehaus_social_links() as $url ) {
		$host = (string) wp_parse_url( $url, PHP_URL_HOST );
		foreach ( $networks as $key => $network ) {
			if ( false !== strpos( $host, $key ) ) {
				$out .= sprintf(
					'<a class="social__link" href="%s" target="_blank" rel="noopener me" aria-label="%s"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="currentColor">%s</svg></a>',
					esc_url( $url ),
					esc_attr( sprintf( __( '%1$s on %2$s (opens in a new tab)', 'capturehaus' ), capturehaus_parent_name(), $network[0] ) ),
					$network[1]
				);
				break;
			}
		}
	}
	return $out ? '<div class="social">' . $out . '</div>' : '';
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

/** One menu link as a bare <a>. */
function capturehaus_menu_link( $url, $title, $current = false, $target = '' ) {
	return sprintf(
		'<a href="%s"%s%s>%s</a>',
		esc_url( $url ),
		$current ? ' aria-current="page"' : '',
		$target ? ' target="' . esc_attr( $target ) . '" rel="noopener"' : '',
		esc_html( $title )
	);
}

/**
 * Bare <a> links for a menu location (the design uses bare links, not <ul> lists).
 * With $dropdowns, a top-level item gets a dropdown of its sub-items; a page with no sub-items in the
 * menu lists its published child pages instead, so new service/sector/area pages appear automatically.
 */
function capturehaus_menu_links( $location, $dropdowns = false ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return '';
	}
	$items    = (array) wp_get_nav_menu_items( $locations[ $location ] );
	$current  = get_queried_object_id();
	$children = array();
	foreach ( $items as $item ) {
		if ( $item->menu_item_parent ) {
			$children[ (int) $item->menu_item_parent ][] = $item;
		}
	}

	$out = '';
	foreach ( $items as $item ) {
		if ( $item->menu_item_parent ) {
			continue;
		}
		$is_current = (int) $item->object_id === $current && 'custom' !== $item->type;
		$link       = capturehaus_menu_link( $item->url, $item->title, $is_current, $item->target );
		if ( ! $dropdowns ) {
			$out .= $link;
			continue;
		}

		$subs = array();
		foreach ( $children[ (int) $item->ID ] ?? array() as $child ) {
			$subs[] = array( $child->url, $child->title, (int) $child->object_id === $current && 'custom' !== $child->type, $child->target );
		}
		if ( ! $subs && 'page' === $item->object ) {
			foreach ( get_pages( array( 'parent' => (int) $item->object_id, 'sort_column' => 'menu_order,post_title' ) ) as $page ) {
				$subs[] = array( get_permalink( $page ), get_the_title( $page ), $page->ID === $current, '' );
			}
		}
		if ( ! $subs ) {
			$out .= $link;
			continue;
		}

		$id      = 'nav-sub-' . (int) $item->ID;
		$in_here = in_array( true, array_column( $subs, 2 ), true );
		$out    .= '<div class="nav-item' . ( $in_here ? ' is-current' : '' ) . '" data-nav-item>' . $link;
		$out    .= sprintf(
			'<button class="nav-item__toggle" type="button" aria-expanded="false" aria-controls="%s" data-sub-toggle><span class="sr-only">%s</span><svg viewBox="0 0 12 12" aria-hidden="true" focusable="false"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg></button>',
			esc_attr( $id ),
			esc_html( sprintf( __( 'Show %s pages', 'capturehaus' ), $item->title ) )
		);
		$out .= '<div class="nav-sub" id="' . esc_attr( $id ) . '">';
		foreach ( $subs as $sub ) {
			$out .= capturehaus_menu_link( ...$sub );
		}
		$out .= '</div></div>';
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
