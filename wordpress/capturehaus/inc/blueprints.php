<?php
/**
 * Page blueprints: Pages › "Add service page" (and sector / area) asks for a name, then creates a
 * draft under the right hub page (Services, Sectors, Areas) with the sections that page type needs,
 * pre-filled with prompts in [square brackets]. Hub pages are created as drafts the first time.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Page types: hub page + the label used in the admin. */
function capturehaus_blueprint_types() {
	return array(
		'service' => array(
			'label'     => __( 'service page', 'capturehaus' ),
			'example'   => '360° virtual tours',
			'hub_slug'  => 'services',
			'hub_title' => __( 'Services', 'capturehaus' ),
		),
		'sector'  => array(
			'label'     => __( 'sector page', 'capturehaus' ),
			'example'   => 'Estate agents and lettings',
			'hub_slug'  => 'sectors',
			'hub_title' => __( 'Who it\'s for', 'capturehaus' ),
		),
		'area'    => array(
			'label'     => __( 'area page', 'capturehaus' ),
			'example'   => 'Wiltshire',
			'hub_slug'  => 'areas',
			'hub_title' => __( 'Areas we cover', 'capturehaus' ),
		),
	);
}

/* ---------- Section rows used by the blueprints ---------- */

function capturehaus_bp_link( $title, $url = '#contact' ) {
	return array( 'title' => $title, 'url' => $url, 'target' => '' );
}

function capturehaus_bp_header( $eyebrow, $title, $lede ) {
	return array(
		'acf_fc_layout' => 'page_header',
		'eyebrow'       => $eyebrow,
		'title'         => $title,
		'lede'          => $lede,
		'ticks'         => "[Key point 1]\n[Key point 2]\n[Key point 3]",
		'button'        => capturehaus_bp_link( __( 'Get a quote', 'capturehaus' ) ),
		'embed_label'   => __( 'Explore it live', 'capturehaus' ),
	);
}

function capturehaus_bp_text( $eyebrow, $title, $content ) {
	return array( 'acf_fc_layout' => 'text', 'eyebrow' => $eyebrow, 'title' => $title, 'content' => $content );
}

function capturehaus_bp_links( $eyebrow, $title, $source, $parent = 0 ) {
	return array( 'acf_fc_layout' => 'links', 'eyebrow' => $eyebrow, 'title' => $title, 'source' => $source, 'parent' => $parent );
}

function capturehaus_bp_cases( $title ) {
	return array( 'acf_fc_layout' => 'case_studies', 'eyebrow' => __( 'Case studies', 'capturehaus' ), 'title' => $title, 'count' => 3 );
}

function capturehaus_bp_faqs( $title, $questions ) {
	$items = array();
	foreach ( $questions as $q ) {
		$items[] = array( 'question' => $q, 'answer' => '[Answer in 2–4 sentences. Plain words, real numbers where you can.]' );
	}
	return array( 'acf_fc_layout' => 'faqs', 'eyebrow' => __( 'FAQs', 'capturehaus' ), 'title' => $title, 'items' => $items );
}

/**
 * Copy a section (e.g. How it works, the yellow CTA band) from the homepage so new pages start with real content.
 * Uses the raw saved values: formatted ones (e.g. a video as a URL) don't save back into ACF and come out blank.
 */
function capturehaus_bp_from_home( $layout ) {
	$front = (int) get_option( 'page_on_front' );
	$raw   = $front && function_exists( 'get_field' ) ? get_field( 'sections', $front, false ) : array();
	foreach ( (array) $raw as $section ) {
		if ( $layout === $section['acf_fc_layout'] ) {
			return $section;
		}
	}
	return null;
}

/** Sections for a new page of the given type. */
function capturehaus_blueprint_sections( $type, $name, $hubs ) {
	$lower = function_exists( 'mb_strtolower' ) ? mb_strtolower( $name ) : strtolower( $name );
	$write = '<p>[Write 300–500 words for this page. Use the main search term naturally in the first paragraph, then cover:]</p>';

	switch ( $type ) {
		case 'service':
			$sections = array(
				capturehaus_bp_header( __( 'Services', 'capturehaus' ), sprintf( '%s in Wiltshire', $name ), sprintf( '[One or two sentences: what %s are and how they help. Include the main search term.]', $lower ) ),
				capturehaus_bp_text( __( 'What you get', 'capturehaus' ), __( 'What\'s included', 'capturehaus' ), $write . '<ul><li>[What the client receives, and in what formats]</li><li>[How it\'s captured: one visit, HDR + LiDAR]</li><li>[Turnaround time]</li><li>[Where it can be used: Rightmove, website, Google, social]</li></ul>' ),
				capturehaus_bp_from_home( 'process' ),
				capturehaus_bp_links( __( 'Who it\'s for', 'capturehaus' ), sprintf( 'Who uses %s', $lower ), 'parent', $hubs['sector'] ),
				capturehaus_bp_cases( __( 'Recent work', 'capturehaus' ) ),
				capturehaus_bp_faqs( sprintf( 'Questions about %s', $lower ), array( sprintf( 'How much do %s cost?', $lower ), 'How long does it take?', '[Another question customers ask]' ) ),
				capturehaus_bp_from_home( 'cta_band' ),
			);
			break;

		case 'sector':
			$sections = array(
				capturehaus_bp_header( __( 'Who it\'s for', 'capturehaus' ), sprintf( 'Virtual tours and floor plans for %s', $lower ), '[One or two sentences on the problem we solve for this sector, in their words.]' ),
				capturehaus_bp_text( __( 'How we help', 'capturehaus' ), '[Benefit-led title, e.g. "Win more instructions"]', $write . '<ul><li>[Their main challenge]</li><li>[What we capture for them, and why it helps]</li><li>[A result or quote from a client in this sector]</li></ul>' ),
				capturehaus_bp_links( __( 'What we capture', 'capturehaus' ), sprintf( 'Services for %s', $lower ), 'parent', $hubs['service'] ),
				capturehaus_bp_cases( sprintf( 'Recent work for %s', $lower ) ),
				capturehaus_bp_faqs( __( 'Good questions', 'capturehaus' ), array( '[Question this sector asks]', '[Question this sector asks]', '[Question this sector asks]' ) ),
				capturehaus_bp_from_home( 'cta_band' ),
			);
			break;

		case 'area':
			$sections = array(
				capturehaus_bp_header( __( 'Areas we cover', 'capturehaus' ), sprintf( 'Virtual tours and floor plans in %s', $name ), sprintf( '[One or two sentences: based in Marlborough, covering all of %s.]', $name ) ),
				capturehaus_bp_text( __( 'Where we work', 'capturehaus' ), sprintf( 'Covering %s', $name ), $write . '<ul><li>[Towns we visit regularly, named naturally in sentences]</li><li>[Local work we\'ve done, with links to case studies]</li><li>[Travel: no charge within X miles, or similar]</li></ul>' ),
				capturehaus_bp_links( __( 'What we capture', 'capturehaus' ), __( 'Our services', 'capturehaus' ), 'parent', $hubs['service'] ),
				capturehaus_bp_links( __( 'Who it\'s for', 'capturehaus' ), __( 'Who we work with', 'capturehaus' ), 'parent', $hubs['sector'] ),
				capturehaus_bp_cases( sprintf( 'Recent work in %s', $name ) ),
				capturehaus_bp_faqs( __( 'Good questions', 'capturehaus' ), array( '[Do you cover my town?]', '[Do you charge for travel?]', '[How soon can you visit?]' ) ),
				capturehaus_bp_from_home( 'cta_band' ),
			);
			break;

		default: // Hub pages: header, cards for every page underneath, CTA.
			$sections = array(
				capturehaus_bp_header( $name, sprintf( '[Headline for the %s page]', $lower ), '[One or two sentences introducing the pages below.]' ),
				capturehaus_bp_links( '', '', 'children' ),
				capturehaus_bp_from_home( 'cta_band' ),
			);
	}
	return array_values( array_filter( $sections ) );
}

/** Find (or create as a draft) the hub page for a type. */
function capturehaus_blueprint_hub( $type ) {
	$types = capturehaus_blueprint_types();
	$hub   = get_page_by_path( $types[ $type ]['hub_slug'] );
	if ( $hub ) {
		return $hub->ID;
	}
	$id = wp_insert_post( array(
		'post_type'   => 'page',
		'post_status' => 'draft',
		'post_title'  => $types[ $type ]['hub_title'],
		'post_name'   => $types[ $type ]['hub_slug'],
	) );
	if ( $id && ! is_wp_error( $id ) ) {
		update_field( 'field_sections_sections', capturehaus_blueprint_sections( 'hub', $types[ $type ]['hub_title'], array() ), $id );
	}
	return (int) $id;
}

/** Create the hubs if needed, then a draft page of the given type. Returns the new page ID or WP_Error. */
function capturehaus_blueprint_create( $type, $name ) {
	$hubs = array();
	foreach ( array_keys( capturehaus_blueprint_types() ) as $t ) {
		$hubs[ $t ] = capturehaus_blueprint_hub( $t );
	}
	$id = wp_insert_post( array(
		'post_type'    => 'page',
		'post_status'  => 'draft',
		'post_title'   => $name,
		'post_parent'  => $hubs[ $type ],
		'post_excerpt' => '[One-line summary of this page, shown on cards on other pages.]',
	), true );
	if ( ! is_wp_error( $id ) ) {
		update_field( 'field_sections_sections', capturehaus_blueprint_sections( $type, $name, $hubs ), $id );
	}
	return $id;
}

/* ---------- Admin: Pages › Add service / sector / area page ---------- */
add_action( 'admin_menu', function () {
	foreach ( capturehaus_blueprint_types() as $type => $t ) {
		$hook = add_submenu_page(
			'edit.php?post_type=page',
			sprintf( __( 'Add %s', 'capturehaus' ), $t['label'] ),
			sprintf( __( 'Add %s', 'capturehaus' ), $t['label'] ),
			'edit_pages',
			'capturehaus-new-' . $type,
			function () use ( $type, $t ) {
				?>
				<div class="wrap">
					<h1><?php echo esc_html( sprintf( __( 'Add %s', 'capturehaus' ), $t['label'] ) ); ?></h1>
					<p><?php echo esc_html( sprintf( __( 'Creates a draft under "%s" with the sections this page needs. Replace everything in [square brackets], add a featured image and excerpt, then publish.', 'capturehaus' ), $t['hub_title'] ) ); ?></p>
					<form method="post">
						<?php wp_nonce_field( 'capturehaus-new-' . $type ); ?>
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><label for="ch-name"><?php esc_html_e( 'Page name', 'capturehaus' ); ?></label></th>
								<td><input name="ch_name" id="ch-name" type="text" class="regular-text" required placeholder="<?php echo esc_attr( sprintf( __( 'e.g. %s', 'capturehaus' ), $t['example'] ) ); ?>"></td>
							</tr>
						</table>
						<?php submit_button( __( 'Create draft', 'capturehaus' ) ); ?>
					</form>
				</div>
				<?php
			}
		);

		add_action( 'load-' . $hook, function () use ( $type ) {
			if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
				return;
			}
			check_admin_referer( 'capturehaus-new-' . $type );
			$name = sanitize_text_field( wp_unslash( $_POST['ch_name'] ?? '' ) );
			if ( '' === $name || ! function_exists( 'update_field' ) ) {
				return;
			}
			$id = capturehaus_blueprint_create( $type, $name );
			if ( ! is_wp_error( $id ) ) {
				wp_safe_redirect( get_edit_post_link( $id, 'url' ) );
				exit;
			}
		} );
	}
} );
