<?php
/**
 * One-off: rebuild the static homepage (index.html) as WordPress content.
 *
 * Usage (from the WordPress install):
 *   wp eval-file /path/to/CaptureHaus/wordpress/import-static-site.php /path/to/CaptureHaus
 *
 * Safe to re-run: media is matched by source path and the Home page is updated in place.
 */
if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

// wp eval-file runs this inside a function, so shared state goes in $GLOBALS.
$root = $GLOBALS['root'] = rtrim( $args[0] ?? dirname( __DIR__ ), '/' );
$html = file_get_contents( "$root/index.html" );
if ( ! $html ) {
	WP_CLI::error( "Can't read $root/index.html" );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/* ---------- DOM helpers ---------- */
libxml_use_internal_errors( true );
$doc = new DOMDocument();
$doc->loadHTML( '<?xml encoding="utf-8"?>' . $html );
$xp = $GLOBALS['xp'] = new DOMXPath( $doc );

function q( $expr, $ctx = null ) {
	global $xp;
	return $xp->query( $expr, $ctx );
}
function q1( $expr, $ctx = null ) {
	$n = q( $expr, $ctx );
	return $n->length ? $n->item( 0 ) : null;
}
function cls( $c ) {
	return "contains(concat(' ', normalize-space(@class), ' '), ' $c ')";
}
/** Text without the decorative arrow spans. */
function txt( $node ) {
	if ( ! $node ) {
		return '';
	}
	$clone = $node->cloneNode( true );
	foreach ( iterator_to_array( $clone->getElementsByTagName( 'span' ) ) as $span ) {
		if ( 'true' === $span->getAttribute( 'aria-hidden' ) || $span->getAttribute( 'class' ) === 'dot' ) {
			$span->parentNode->removeChild( $span );
		}
	}
	return trim( preg_replace( '/\s+/u', ' ', $clone->textContent ) );
}
function inner( $node ) {
	$out = '';
	foreach ( $node->childNodes as $child ) {
		$out .= $node->ownerDocument->saveHTML( $child );
	}
	return trim( $out );
}
/** <em>x</em> / <span class="hl">x</span> -> *x*, <br> -> newline. */
function marked( $node ) {
	if ( ! $node ) {
		return '';
	}
	$h = inner( $node );
	$h = preg_replace( '#<(em|span)[^>]*>(.*?)</\1>#s', '*$2*', $h );
	$h = preg_replace( '#<br\s*/?>#', "\n", $h );
	return trim( html_entity_decode( wp_strip_all_tags( $h ), ENT_QUOTES, 'UTF-8' ) );
}
function lines_of( $ul ) {
	if ( ! $ul ) {
		return '';
	}
	$items = array();
	foreach ( q( './li', $ul ) as $li ) {
		$items[] = txt( $li );
	}
	return implode( "\n", $items );
}
function link_of( $a ) {
	return $a ? array( 'title' => txt( $a ), 'url' => $a->getAttribute( 'href' ), 'target' => '' ) : '';
}

/* ---------- Media ---------- */
function media( $rel, $alt = '' ) {
	global $root;
	static $cache = array();
	$rel = ltrim( (string) $rel, '/' );
	if ( '' === $rel ) {
		return 0;
	}
	if ( isset( $cache[ $rel ] ) ) {
		return $cache[ $rel ];
	}
	$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'any', 'meta_key' => '_ch_source', 'meta_value' => $rel, 'fields' => 'ids', 'numberposts' => 1 ) );
	if ( $found ) {
		return $cache[ $rel ] = $found[0];
	}
	$src = "$root/$rel";
	if ( ! file_exists( $src ) ) {
		WP_CLI::warning( "Missing media: $rel" );
		return 0;
	}
	$tmp = wp_tempnam( basename( $src ) );
	copy( $src, $tmp );
	$id = media_handle_sideload( array( 'name' => basename( $src ), 'tmp_name' => $tmp ), 0, null, array( 'post_title' => $alt ?: pathinfo( $src, PATHINFO_FILENAME ) ) );
	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( "$rel: " . $id->get_error_message() );
		return 0;
	}
	update_post_meta( $id, '_ch_source', $rel );
	if ( $alt ) {
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	}
	WP_CLI::log( "  media: $rel" );
	return $cache[ $rel ] = $id;
}
function img_id( $img ) {
	return $img ? media( $img->getAttribute( 'src' ), $img->getAttribute( 'alt' ) ) : 0;
}
/** Video attachment ID (ACF file fields store IDs). */
function video_url( $video ) {
	if ( ! $video ) {
		return 0;
	}
	$src = q1( './/source[not(@media)]', $video ) ?: q1( './/source', $video ); // libxml nests <source> tags
	$rel = $src->getAttribute( 'data-src' ) ?: $src->getAttribute( 'src' );
	return media( $rel );
}
function poster_id( $video ) {
	return $video && $video->getAttribute( 'poster' ) ? media( $video->getAttribute( 'poster' ) ) : 0;
}

/* ---------- Sections ---------- */
$sections = array();

// Hero
$hero   = q1( "//section[" . cls( 'hero' ) . "]" );
$video  = q1( './/video', $hero );
$mobile = q1( ".//source[@media]", $video );
$dialog = q1( "//dialog[@data-tour-modal]/iframe" );
$chips  = array();
foreach ( q( ".//div[" . cls( 'hero__sectors' ) . "]/a", $hero ) as $a ) {
	$chips[] = array( 'link' => link_of( $a ) );
}
$sections[] = array(
	'acf_fc_layout' => 'hero',
	'eyebrow'       => txt( q1( ".//p[" . cls( 'eyebrow' ) . "]", $hero ) ),
	'title'         => marked( q1( './/h1', $hero ) ),
	'lede'          => txt( q1( ".//p[" . cls( 'hero__lede' ) . "]", $hero ) ),
	'button'        => link_of( q1( ".//div[" . cls( 'hero__ctas' ) . "]/a", $hero ) ),
	'video'         => video_url( $video ),
	'video_mobile'  => $mobile ? media( $mobile->getAttribute( 'src' ) ) : 0,
	'poster'        => poster_id( $video ),
	'tour_url'      => $dialog ? $dialog->getAttribute( 'data-src' ) : '',
	'tour_label'    => txt( q1( './/button[@data-tour-open]', $hero ) ),
	'tour_title'    => $dialog ? $dialog->getAttribute( 'title' ) : '',
	'chips_label'   => txt( q1( ".//span[" . cls( 'hero__sectors-label' ) . "]", $hero ) ),
	'chips'         => $chips,
);

// Ticker (the track repeats its words twice; keep one set)
$words = array();
foreach ( q( "//div[" . cls( 'ticker__track' ) . "]/span" ) as $span ) {
	$words[] = txt( $span );
}
$sections[] = array( 'acf_fc_layout' => 'ticker', 'items' => implode( "\n", array_slice( $words, 0, (int) ( count( $words ) / 2 ) ) ) );

// Services
$cap   = q1( "//section[@id='capture']" );
$items = array();
foreach ( q( ".//article[" . cls( 'feature' ) . "]", $cap ) as $art ) {
	$m     = q1( ".//div[" . cls( 'feature__media' ) . "]", $art );
	$mc    = $m->getAttribute( 'class' );
	$embed = q1( './/button[@data-embed]', $m );
	$item  = array(
		'title'       => txt( q1( './/h3', $art ) ),
		'text'        => txt( q1( ".//div[" . cls( 'feature__body' ) . "]/p", $art ) ),
		'ticks'       => lines_of( q1( ".//ul[" . cls( 'ticks' ) . "]", $art ) ),
		'button'      => link_of( q1( ".//div[" . cls( 'feature__body' ) . "]/a", $art ) ),
		'tag'         => txt( q1( ".//span[" . cls( 'feature__tag' ) . "]", $m ) ),
		'embed_url'   => $embed ? $embed->getAttribute( 'data-embed' ) : '',
		'embed_title' => $embed ? $embed->getAttribute( 'data-embed-title' ) : '',
		'embed_label' => $embed ? txt( $embed ) : '',
	);
	if ( q1( ".//div[@data-slideshow]", $m ) ) {
		$item['media']  = 'slideshow';
		$item['images'] = array_map( 'img_id', iterator_to_array( q( './/div[@data-slideshow]/img', $m ) ) );
	} elseif ( str_contains( $mc, 'feature__media--plans' ) ) {
		$item['media']  = 'plans';
		$item['images'] = array_map( 'img_id', iterator_to_array( q( './img', $m ) ) );
	} elseif ( str_contains( $mc, 'feature__media--drone' ) ) {
		$v                       = q1( ".//div[" . cls( 'drone-card' ) . "]/video", $m );
		$item['media']           = 'drone';
		$item['image']           = img_id( q1( './img', $m ) );
		$item['vertical_video']  = video_url( $v );
		$item['vertical_poster'] = poster_id( $v );
	} elseif ( $v = q1( './video', $m ) ) {
		$item['media']   = 'video';
		$item['video']   = video_url( $v );
		$item['poster']  = poster_id( $v );
		$item['contain'] = str_contains( $mc, 'feature__media--light' ) ? 1 : 0;
	} else {
		$item['media'] = 'image';
		$item['image'] = img_id( q1( './img', $m ) );
	}
	$items[] = $item;
}
$sections[] = array(
	'acf_fc_layout' => 'features',
	'anchor'        => 'capture',
	'eyebrow'       => txt( q1( ".//header//p[" . cls( 'eyebrow' ) . "]", $cap ) ),
	'title'         => marked( q1( './/header//h2', $cap ) ),
	'lede'          => txt( q1( ".//header//p[" . cls( 'section-lede' ) . "]", $cap ) ),
	'items'         => $items,
);

// LiDAR statement
$st    = q1( "//section[" . cls( 'statement' ) . "]" );
$v     = q1( './/video', $st );
$stats = array();
foreach ( q( ".//dl[" . cls( 'stats' ) . "]/div", $st ) as $d ) {
	$stats[] = array( 'value' => txt( q1( './dt', $d ) ), 'label' => txt( q1( './dd', $d ) ) );
}
$sections[] = array(
	'acf_fc_layout' => 'statement',
	'eyebrow'       => txt( q1( ".//p[" . cls( 'eyebrow' ) . "]", $st ) ),
	'title'         => marked( q1( './/h2', $st ) ),
	'video'         => video_url( $v ),
	'poster'        => poster_id( $v ),
	'stats'         => $stats,
);

// Extras
$ex    = q1( "//section[" . cls( 'extras' ) . "]" );
$tiles = array();
foreach ( q( ".//div[" . cls( 'tile' ) . "]", $ex ) as $t ) {
	$tv      = q1( './video', $t );
	$tiles[] = $tv
		? array( 'type' => 'video', 'video' => video_url( $tv ), 'poster' => poster_id( $tv ), 'caption' => txt( q1( './p', $t ) ) )
		: array( 'type' => 'image', 'image' => img_id( q1( './img', $t ) ), 'caption' => txt( q1( './p', $t ) ) );
}
$sections[] = array(
	'acf_fc_layout' => 'extras',
	'eyebrow'       => txt( q1( ".//header//p[" . cls( 'eyebrow' ) . "]", $ex ) ),
	'title'         => marked( q1( './/header//h2', $ex ) ),
	'lede'          => txt( q1( ".//header//p[" . cls( 'section-lede' ) . "]", $ex ) ),
	'after'         => img_id( q1( ".//figure[@data-compare]/img", $ex ) ),
	'before'        => img_id( q1( ".//div[" . cls( 'compare__before' ) . "]/img", $ex ) ),
	'tiles'         => $tiles,
);

// Sectors
$se    = q1( "//section[@id='sectors']" );
$feat  = q1( ".//article[" . cls( 'sector--feature' ) . "]", $se );
$cards = array();
foreach ( q( ".//div[" . cls( 'sectors__grid' ) . "]/article", $se ) as $art ) {
	$cards[] = array(
		'image'  => img_id( q1( './img', $art ) ),
		'kicker' => txt( q1( ".//span[" . cls( 'sector__kicker' ) . "]", $art ) ),
		'title'  => txt( q1( './/h3', $art ) ),
		'text'   => txt( q1( './/p', $art ) ),
		'link'   => link_of( q1( ".//a[" . cls( 'sector__link' ) . "]", $art ) ),
	);
}
$more = q1( ".//p[" . cls( 'sectors__more' ) . "]", $se );
$sections[] = array(
	'acf_fc_layout'  => 'sectors',
	'anchor'         => 'sectors',
	'eyebrow'        => txt( q1( ".//header//p[" . cls( 'eyebrow' ) . "]", $se ) ),
	'title'          => marked( q1( './/header//h2', $se ) ),
	'lede'           => txt( q1( ".//header//p[" . cls( 'section-lede' ) . "]", $se ) ),
	'feature_image'  => img_id( q1( './img', $feat ) ),
	'feature_kicker' => txt( q1( ".//span[" . cls( 'sector__kicker' ) . "]", $feat ) ),
	'feature_title'  => txt( q1( './/h3', $feat ) ),
	'feature_text'   => txt( q1( ".//div[" . cls( 'sector__body' ) . "]/p", $feat ) ),
	'feature_ticks'  => lines_of( q1( './/ul', $feat ) ),
	'feature_button' => link_of( q1( ".//a[" . cls( 'btn' ) . "]", $feat ) ),
	'items'          => $cards,
	'more'           => $more ? preg_replace( '/\s+data-sector="[^"]*"/', '', inner( $more ) ) : '',
);

// Process
$pr    = q1( "//section[@id='process']" );
$v     = q1( './/video', $pr );
$steps = array();
foreach ( q( ".//ol[" . cls( 'steps' ) . "]/li", $pr ) as $li ) {
	$steps[] = array( 'title' => txt( q1( './/h3', $li ) ), 'text' => txt( q1( './/p', $li ) ) );
}
$sections[] = array(
	'acf_fc_layout' => 'process',
	'anchor'        => 'process',
	'eyebrow'       => txt( q1( ".//p[" . cls( 'eyebrow' ) . "]", $pr ) ),
	'title'         => marked( q1( './/h2', $pr ) ),
	'video'         => video_url( $v ),
	'poster'        => poster_id( $v ),
	'steps'         => $steps,
	'button'        => link_of( q1( ".//div[" . cls( 'process__body' ) . "]/a", $pr ) ),
);

// Gallery
$sections[] = array(
	'acf_fc_layout' => 'gallery',
	'images'        => array_map( 'img_id', iterator_to_array( q( "//section[" . cls( 'gallery' ) . "]//img" ) ) ),
);

// CTA band
$cb         = q1( "//section[" . cls( 'cta-band' ) . "]" );
$sections[] = array( 'acf_fc_layout' => 'cta_band', 'title' => marked( q1( './/h2', $cb ) ), 'button' => link_of( q1( './/a', $cb ) ) );

// FAQs
$fq   = q1( "//section[@id='faqs']" );
$faqs = array();
foreach ( q( ".//details", $fq ) as $d ) {
	$faqs[] = array( 'question' => txt( q1( './summary', $d ) ), 'answer' => inner( q1( ".//div[" . cls( 'faq__a' ) . "]", $d ) ) );
}
$sections[] = array(
	'acf_fc_layout' => 'faqs',
	'anchor'        => 'faqs',
	'eyebrow'       => txt( q1( ".//p[" . cls( 'eyebrow' ) . "]", $fq ) ),
	'title'         => marked( q1( './/h2', $fq ) ),
	'lede'          => txt( q1( ".//p[" . cls( 'section-lede' ) . "]", $fq ) ),
	'button'        => link_of( q1( ".//header//a", $fq ) ),
	'items'         => $faqs,
);

/* ---------- Home page ---------- */
$home = get_page_by_path( 'home' );
$home_id = $home ? $home->ID : wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Home', 'post_name' => 'home' ) );
update_field( 'field_sections_sections', $sections, $home_id );
WP_CLI::log( 'Home page: ' . count( $sections ) . ' sections' );

$blog = get_page_by_path( 'blog' );
$blog_id = $blog ? $blog->ID : wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Blog', 'post_name' => 'blog' ) );

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home_id );
update_option( 'page_for_posts', $blog_id );

/* ---------- Site settings ---------- */
$co    = q1( "//section[@id='contact']" );
$hs    = q1( "//div[" . cls( 'hs-form-frame' ) . "]" );
$small = txt( q1( "//p[" . cls( 'site-footer__small' ) . "]" ) );
update_field( 'phone', txt( q1( "//a[" . cls( 'site-footer__phone' ) . "]" ) ), 'option' );
update_field( 'header_cta', link_of( q1( "//a[" . cls( 'site-nav__cta' ) . "]" ) ), 'option' );
update_field( 'quote_eyebrow', txt( q1( ".//p[" . cls( 'eyebrow' ) . "]", $co ) ), 'option' );
update_field( 'quote_title', txt( q1( './/h2', $co ) ), 'option' );
update_field( 'quote_lede', txt( q1( ".//p[" . cls( 'section-lede' ) . "]", $co ) ), 'option' );
update_field( 'quote_ticks', lines_of( q1( './/ul', $co ) ), 'option' );
update_field( 'quote_image', img_id( q1( ".//img[" . cls( 'contact__img' ) . "]", $co ) ), 'option' );
update_field( 'hubspot_portal_id', $hs->getAttribute( 'data-portal-id' ), 'option' );
update_field( 'hubspot_form_id', $hs->getAttribute( 'data-form-id' ), 'option' );
update_field( 'hubspot_region', $hs->getAttribute( 'data-region' ), 'option' );
update_field( 'footer_small', preg_replace( '/©\s*\d{4}/u', '© {year}', $small ), 'option' );
update_field( 'footer_legal', txt( q1( "//p[" . cls( 'site-footer__legal' ) . "]" ) ), 'option' );

/* ---------- Menus ---------- */
function ch_menu( $name, $location, $node_xpath ) {
	$menu = wp_get_nav_menu_object( $name );
	$id   = $menu ? $menu->term_id : wp_create_nav_menu( $name );
	foreach ( (array) wp_get_nav_menu_items( $id ) as $old ) {
		wp_delete_post( $old->ID, true );
	}
	foreach ( q( $node_xpath ) as $i => $a ) {
		if ( str_contains( $a->getAttribute( 'class' ), 'btn' ) ) {
			continue;
		}
		wp_update_nav_menu_item( $id, 0, array(
			'menu-item-title'    => txt( $a ),
			'menu-item-url'      => home_url( '/' . $a->getAttribute( 'href' ) ),
			'menu-item-status'   => 'publish',
			'menu-item-position' => $i + 1,
		) );
	}
	$locations              = get_theme_mod( 'nav_menu_locations', array() );
	$locations[ $location ] = $id;
	set_theme_mod( 'nav_menu_locations', $locations );
}
ch_menu( 'Header', 'primary', "//nav[@id='site-nav']/a" );
ch_menu( 'Footer', 'footer', "//nav[" . cls( 'site-footer__nav' ) . "]/a" );

WP_CLI::success( 'Static site imported.' );
