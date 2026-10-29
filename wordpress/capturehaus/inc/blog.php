<?php
/**
 * Blog: post URLs under the posts page (/blog/post-name/), reading time, the "In this article"
 * contents list, related posts, and the article patterns new posts start from.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Path of the page set as Settings → Reading → Posts page ('' when there isn't one). */
function capturehaus_blog_base() {
	$page = (int) get_option( 'page_for_posts' );
	return $page && 'publish' === get_post_status( $page ) ? get_page_uri( $page ) : '';
}

/* ---------- URLs: /blog/post-name/ (old /post-name/ links redirect via WordPress's canonical redirect) ---------- */
add_filter( 'post_link', function ( $link, $post ) {
	$base = capturehaus_blog_base();
	if ( $base && 'post' === $post->post_type && $post->post_name && ! in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ), true ) ) {
		return home_url( user_trailingslashit( $base . '/' . $post->post_name ) );
	}
	return $link;
}, 10, 2 );

add_action( 'init', function () {
	$base = capturehaus_blog_base();
	if ( $base ) {
		// Not /blog/page/2/ (the posts page's own pagination).
		add_rewrite_rule( preg_quote( $base, '#' ) . '/(?!page/)([^/]+)/?$', 'index.php?name=$matches[1]', 'top' );
	}
	// Rebuild the rules once whenever the posts page changes (no need to re-save Permalinks).
	if ( get_option( 'capturehaus_blog_base' ) !== $base ) {
		update_option( 'capturehaus_blog_base', $base );
		flush_rewrite_rules( false );
	}
}, 999 ); // Late, so other post types' rules exist when they're rebuilt.

/* A post reached by any other path (e.g. the old /post-name/) redirects to its /blog/ address. */
add_action( 'template_redirect', function () {
	if ( ! is_singular( 'post' ) || ! capturehaus_blog_base() || is_preview() ) {
		return;
	}
	$want = wp_parse_url( get_permalink(), PHP_URL_PATH );
	$have = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	if ( $want && untrailingslashit( $have ) !== untrailingslashit( $want ) ) {
		wp_safe_redirect( get_permalink(), 301 );
		exit;
	}
} );

/* ---------- Article helpers ---------- */

/** Minutes to read a post, at about 220 words a minute. */
function capturehaus_reading_time( $post = null ) {
	$words = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post ) ) );
	return max( 1, (int) round( $words / 220 ) );
}

/** The post's main category (Yoast's primary category when set), or null. */
function capturehaus_post_category( $post = null ) {
	$post = get_post( $post );
	if ( class_exists( 'WPSEO_Primary_Term' ) ) {
		$primary = ( new WPSEO_Primary_Term( 'category', $post->ID ) )->get_primary_term();
		if ( $primary && ( $term = get_term( $primary ) ) && ! is_wp_error( $term ) ) {
			return $term;
		}
	}
	$terms = get_the_category( $post->ID );
	return $terms ? $terms[0] : null;
}

/**
 * Give each H2 in the rendered content an id and return them for the contents list.
 * Returns array( $html, array( array( 'id' => …, 'text' => … ), … ) ).
 */
function capturehaus_heading_anchors( $html ) {
	$items = array();
	$html  = preg_replace_callback( '#<h2([^>]*)>(.*?)</h2>#is', function ( $m ) use ( &$items ) {
		$text = trim( wp_strip_all_tags( $m[2] ) );
		if ( preg_match( '#\bid=["\']([^"\']+)#', $m[1], $id ) ) {
			$id = $id[1];
		} else {
			$id   = sanitize_title( $text ) ?: 'section';
			$base = $id;
			for ( $n = 2; in_array( $id, wp_list_pluck( $items, 'id' ), true ); $n++ ) {
				$id = $base . '-' . $n;
			}
			$m[1] .= ' id="' . esc_attr( $id ) . '"';
		}
		$items[] = array( 'id' => $id, 'text' => $text );
		return '<h2' . $m[1] . '>' . $m[2] . '</h2>';
	}, $html );
	return array( $html, $items );
}

/** Up to $count other published posts, same category first, then the newest. */
function capturehaus_related_posts( $post = null, $count = 3 ) {
	$post = get_post( $post );
	$cat  = capturehaus_post_category( $post );
	$ids  = array();
	$base = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'fields'              => 'ids',
	);
	if ( $cat ) {
		$ids = get_posts( array_merge( $base, array( 'cat' => $cat->term_id, 'post__not_in' => array( $post->ID ), 'posts_per_page' => $count ) ) );
	}
	if ( count( $ids ) < $count ) {
		$ids = array_merge( $ids, get_posts( array_merge( $base, array( 'post__not_in' => array_merge( array( $post->ID ), $ids ), 'posts_per_page' => $count - count( $ids ) ) ) ) );
	}
	return $ids;
}

/* ---------- Article patterns: offered when a new post is started, and under Patterns → CaptureHaus articles ---------- */
add_action( 'init', function () {
	register_block_pattern_category( 'capturehaus-articles', array( 'label' => __( 'CaptureHaus articles', 'capturehaus' ) ) );

	$p  = function ( $text ) { return "<!-- wp:paragraph -->\n<p>$text</p>\n<!-- /wp:paragraph -->\n\n"; };
	$h2 = function ( $text ) { return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">$text</h2>\n<!-- /wp:heading -->\n\n"; };
	$h3 = function ( $text ) { return "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">$text</h3>\n<!-- /wp:heading -->\n\n"; };
	$ul = function ( array $items, $ordered = false ) {
		$tag  = $ordered ? 'ol' : 'ul';
		$out  = '<!-- wp:list' . ( $ordered ? ' {"ordered":true}' : '' ) . " -->\n<$tag class=\"wp-block-list\">";
		foreach ( $items as $item ) {
			$out .= "<!-- wp:list-item -->\n<li>$item</li>\n<!-- /wp:list-item -->";
		}
		return $out . "</$tag>\n<!-- /wp:list -->\n\n";
	};
	$table = function ( array $head, array $rows ) {
		$cells = function ( $tag, $row ) { return '<tr>' . implode( '', array_map( function ( $c ) use ( $tag ) { return "<$tag>$c</$tag>"; }, $row ) ) . '</tr>'; };
		$out   = "<!-- wp:table {\"hasFixedLayout\":false} -->\n<figure class=\"wp-block-table\"><table><thead>" . $cells( 'th', $head ) . '</thead><tbody>';
		foreach ( $rows as $row ) {
			$out .= $cells( 'td', $row );
		}
		return $out . "</tbody></table></figure>\n<!-- /wp:table -->\n\n";
	};
	$image  = "<!-- wp:image -->\n<figure class=\"wp-block-image\"><img alt=\"\"/></figure>\n<!-- /wp:image -->\n\n";
	$answer = function ( $text ) {
		return "<!-- wp:group {\"className\":\"callout\"} -->\n<div class=\"wp-block-group callout\"><!-- wp:paragraph -->\n<p><strong>Short answer:</strong> $text</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:group -->\n\n";
	};
	$cta = "<!-- wp:group {\"className\":\"cta-box\"} -->\n<div class=\"wp-block-group cta-box\"><!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">[Get a quote for your property, venue or site]</h3>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>[One line on what we'd capture for this reader. Keep to facts already on the site.]</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:buttons -->\n<div class=\"wp-block-buttons\"><!-- wp:button -->\n<div class=\"wp-block-button\"><a class=\"wp-block-button__link wp-element-button\" href=\"#contact\">Get a quote</a></div>\n<!-- /wp:button --></div>\n<!-- /wp:buttons --></div>\n<!-- /wp:group -->";

	$patterns = array(
		'guide' => array(
			'title'       => __( 'Guide: answer a question', 'capturehaus' ),
			'description' => __( 'Explainers and how-tos: short answer first, then the detail, then a quote box.', 'capturehaus' ),
			'content'     => $answer( '[Answer the question in the title in one or two sentences. This is what Google may quote.]' )
				. $p( '[Who this is for and what they will know by the end. Two sentences.]' )
				. $h2( '[First question the reader has]' ) . $p( '[Answer it. Short paragraphs, plain words. Link to the service page the first time you name a service.]' )
				. $h2( '[How it works, step by step]' ) . $ul( array( '[Step one]', '[Step two]', '[Step three]' ), true )
				. $image
				. $h2( '[What we see on real shoots]' ) . $p( '[A real example from a job: the property or venue, the town, what made the difference. Link the case study or area page.]' )
				. $h2( 'Questions people ask' ) . $h3( '[A related question]' ) . $p( '[Two or three sentences.]' ) . $h3( '[Another related question]' ) . $p( '[Two or three sentences.]' )
				. $cta,
		),
		'cost' => array(
			'title'       => __( 'Guide: cost or price', 'capturehaus' ),
			'description' => __( 'What something costs, what changes the price, and a table of typical prices.', 'capturehaus' ),
			'content'     => $answer( '[The price range in one sentence. Use only prices Jon has confirmed.]' )
				. $p( '[Why prices vary between suppliers, in two sentences.]' )
				. $h2( 'What affects the price' ) . $ul( array( '[Size of the property or space]', '[Number of floors or areas]', '[Extras: floor plan, photos, drone]', '[Travel outside our usual area]' ) )
				. $h2( 'Typical prices' ) . $table( array( 'Property or space', 'What you get', 'Price' ), array( array( '[e.g. 2–3 bed house]', '[Tour + floor plan]', '[£…]' ), array( '[e.g. 4–5 bed house]', '[…]', '[£…]' ), array( '[e.g. venue or commercial space]', '[…]', '[From £…]' ) ) )
				. $h2( 'What is included with CaptureHaus' ) . $p( '[Hosting, turnaround, edits: only what we actually offer.]' )
				. $h2( 'How to get the most for your money' ) . $ul( array( '[Tip]', '[Tip]', '[Tip]' ) )
				. $cta,
		),
		'comparison' => array(
			'title'       => __( 'Guide: compare two options', 'capturehaus' ),
			'description' => __( '"X vs Y" articles: the verdict, a side-by-side table, and when to choose each.', 'capturehaus' ),
			'content'     => $answer( '[Which to choose, for whom, in one or two sentences.]' )
				. $h2( '[X] and [Y] side by side' ) . $table( array( '', '[X]', '[Y]' ), array( array( 'Best for', '[…]', '[…]' ), array( 'What you get', '[…]', '[…]' ), array( 'Time on site', '[…]', '[…]' ), array( 'Where it is used', '[…]', '[…]' ) ) )
				. $h2( 'When to choose [X]' ) . $p( '[Who it suits and why. A real example.]' )
				. $h2( 'When to choose [Y]' ) . $p( '[Who it suits and why. A real example.]' )
				. $h2( 'Can you have both?' ) . $p( '[Usually yes: say how we capture both in one visit, if that is true for this pair.]' )
				. $cta,
		),
		'checklist' => array(
			'title'       => __( 'Checklist', 'capturehaus' ),
			'description' => __( 'A practical checklist, e.g. preparing a home or venue for a shoot. Also handy to send to clients.', 'capturehaus' ),
			'content'     => $p( '[What the checklist is for and when to use it. Two sentences.]' )
				. $h2( '[Before the day]' ) . $ul( array( '[Item]', '[Item]', '[Item]' ) )
				. $h2( '[Room by room / area by area]' ) . $h3( '[Kitchen]' ) . $ul( array( '[Item]', '[Item]' ) ) . $h3( '[Living spaces]' ) . $ul( array( '[Item]', '[Item]' ) )
				. $h2( '[Outside]' ) . $ul( array( '[Item]', '[Item]' ) )
				. $h2( 'On the day' ) . $p( '[How long we are on site, what the owner or agent needs to do.]' )
				. $cta,
		),
		'story' => array(
			'title'       => __( 'Project story', 'capturehaus' ),
			'description' => __( 'A recent job: the brief, what we captured, the result. Link the town page and the case study.', 'capturehaus' ),
			'content'     => $p( '[One-paragraph summary: who, where (town), what we captured.]' )
				. $image
				. $h2( 'The brief' ) . $p( '[What the client needed and why.]' )
				. $h2( 'What we captured' ) . $ul( array( '[360° tour]', '[Floor plan]', '[Photos / video / drone]' ) )
				. $h2( 'The result' ) . $p( '[Only outcomes the client has confirmed.]' )
				. "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><!-- wp:paragraph -->\n<p>[Client quote, with permission]</p>\n<!-- /wp:paragraph --><cite>[Name, company]</cite></blockquote>\n<!-- /wp:quote -->\n\n"
				. $cta,
		),
	);

	foreach ( $patterns as $slug => $pattern ) {
		register_block_pattern( 'capturehaus/article-' . $slug, array_merge( $pattern, array(
			'categories' => array( 'capturehaus-articles' ),
			'postTypes'  => array( 'post' ),
			'blockTypes' => array( 'core/post-content' ),
		) ) );
	}

	// Pieces to drop into any article.
	register_block_pattern( 'capturehaus/short-answer', array(
		'title'      => __( 'Short answer box', 'capturehaus' ),
		'categories' => array( 'capturehaus-articles' ),
		'postTypes'  => array( 'post' ),
		'content'    => $answer( '[One or two sentences.]' ),
	) );
	register_block_pattern( 'capturehaus/quote-box', array(
		'title'      => __( 'Get a quote box', 'capturehaus' ),
		'categories' => array( 'capturehaus-articles' ),
		'postTypes'  => array( 'post' ),
		'content'    => $cta,
	) );
} );
