<?php
/**
 * ACF field groups, registered in code so they are version-controlled with the theme.
 * Keys are generated from each field's path, so they stay stable between deploys.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- Tiny builders ---------- */
function ch_field( $type, $name, $label, $extra = array() ) {
	return array_merge( array( 'type' => $type, 'name' => $name, 'label' => $label ), $extra );
}
function ch_text( $name, $label, $extra = array() )     { return ch_field( 'text', $name, $label, $extra ); }
function ch_area( $name, $label, $extra = array() )     { return ch_field( 'textarea', $name, $label, array_merge( array( 'rows' => 3, 'new_lines' => '' ), $extra ) ); }
function ch_lines_field( $name, $label )                { return ch_area( $name, $label, array( 'rows' => 4, 'instructions' => 'One item per line.' ) ); }
function ch_link( $name, $label, $extra = array() )     { return ch_field( 'link', $name, $label, array_merge( array( 'return_format' => 'array' ), $extra ) ); }
function ch_url( $name, $label, $extra = array() )      { return ch_field( 'url', $name, $label, $extra ); }
function ch_image( $name, $label, $extra = array() )    { return ch_field( 'image', $name, $label, array_merge( array( 'return_format' => 'id', 'preview_size' => 'medium', 'library' => 'all' ), $extra ) ); }
function ch_gallery( $name, $label, $extra = array() )  { return ch_field( 'gallery', $name, $label, array_merge( array( 'return_format' => 'id', 'preview_size' => 'medium', 'library' => 'all', 'insert' => 'append' ), $extra ) ); }
function ch_video( $name, $label, $extra = array() )    { return ch_field( 'file', $name, $label, array_merge( array( 'return_format' => 'url', 'library' => 'all', 'mime_types' => 'mp4', 'instructions' => 'MP4 (H.264). Keep it short and compressed: aim for under 6 MB.' ), $extra ) ); }
function ch_bool( $name, $label, $extra = array() )     { return ch_field( 'true_false', $name, $label, array_merge( array( 'ui' => 1 ), $extra ) ); }
function ch_rich( $name, $label, $extra = array() )     { return ch_field( 'wysiwyg', $name, $label, array_merge( array( 'tabs' => 'all', 'toolbar' => 'basic', 'media_upload' => 0 ), $extra ) ); }
function ch_repeat( $name, $label, $sub, $extra = array() ) {
	return ch_field( 'repeater', $name, $label, array_merge( array( 'layout' => 'block', 'button_label' => 'Add', 'sub_fields' => $sub ), $extra ) );
}
function ch_tab( $label ) { return ch_field( 'tab', '', $label, array( 'placement' => 'top' ) ); }
function ch_anchor() {
	return ch_text( 'anchor', 'Anchor ID', array( 'instructions' => 'Optional. Lets menus link here, e.g. "capture" for /#capture.', 'wrapper' => array( 'width' => 50 ) ) );
}
/** Show a field only when a sibling select has one of the given values. */
function ch_when( $field, $select_key, $values ) {
	$field['conditional_logic'] = array_map( function ( $v ) use ( $select_key ) {
		return array( array( 'field' => $select_key, 'operator' => '==', 'value' => $v ) );
	}, (array) $values );
	return $field;
}

/** Give every field (and nested sub field / layout) a stable key based on its path. */
function ch_keys( array $fields, $prefix ) {
	foreach ( $fields as $i => &$f ) {
		$slug     = $f['name'] !== '' ? $f['name'] : 'tab' . $i;
		$f['key'] = $prefix . '_' . $slug;
		if ( ! empty( $f['sub_fields'] ) ) {
			$f['sub_fields'] = ch_keys( $f['sub_fields'], $f['key'] );
		}
		if ( ! empty( $f['layouts'] ) ) {
			foreach ( $f['layouts'] as $name => &$layout ) {
				$layout['key']        = 'layout_' . $f['key'] . '_' . $name;
				$layout['name']       = $name;
				$layout['sub_fields'] = ch_keys( $layout['sub_fields'], $f['key'] . '_' . $name );
			}
			unset( $layout );
		}
	}
	unset( $f );
	return $fields;
}

/** Conditional-logic keys must match the generated keys, so resolve "@name" references after keying. */
function ch_resolve_conditions( array $fields ) {
	foreach ( $fields as &$f ) {
		if ( ! empty( $f['conditional_logic'] ) ) {
			$parent = substr( $f['key'], 0, -( strlen( $f['name'] ) + 1 ) );
			foreach ( $f['conditional_logic'] as &$group ) {
				foreach ( $group as &$rule ) {
					if ( str_starts_with( $rule['field'], '@' ) ) {
						$rule['field'] = $parent . '_' . substr( $rule['field'], 1 );
					}
				}
			}
			unset( $group, $rule );
		}
		if ( ! empty( $f['collapsed'] ) && str_starts_with( $f['collapsed'], '@' ) ) {
			$f['collapsed'] = $f['key'] . '_' . substr( $f['collapsed'], 1 );
		}
		if ( ! empty( $f['sub_fields'] ) ) {
			$f['sub_fields'] = ch_resolve_conditions( $f['sub_fields'] );
		}
		if ( ! empty( $f['layouts'] ) ) {
			foreach ( $f['layouts'] as &$layout ) {
				$layout['sub_fields'] = ch_resolve_conditions( $layout['sub_fields'] );
			}
			unset( $layout );
		}
	}
	unset( $f );
	return $fields;
}

function ch_group( $key, $title, $fields, $location, $extra = array() ) {
	acf_add_local_field_group( array_merge( array(
		'key'      => 'group_' . $key,
		'title'    => $title,
		'fields'   => ch_resolve_conditions( ch_keys( $fields, 'field_' . $key ) ),
		'location' => $location,
	), $extra ) );
}

/* ---------- Shared choices (case study details and the case study filter) ---------- */
function ch_sector_choices() {
	$sectors = array( 'Estate agent / lettings', 'Restaurant / café', 'Pub / bar', 'Holiday let', 'Hotel / venue', 'Shop / retail', 'Property developer', 'Other' );
	return array_combine( $sectors, $sectors );
}
function ch_service_choices() {
	$services = array( '360° virtual tour', '2D floor plans', '3D floor plan', 'Walkthrough video', 'HDR photography', 'Drone', 'CGI renders' );
	return array_combine( $services, $services );
}

/* ---------- Section layouts ---------- */
function ch_section_layouts() {
	$media_types = array(
		'video'     => 'Looping video',
		'image'     => 'Single image',
		'slideshow' => 'Slideshow of images',
		'plans'     => 'Two floor plans side by side',
		'drone'     => 'Image with vertical video card',
	);

	return array(
		'hero' => array(
			'label'      => 'Hero (full-screen video)',
			'display'    => 'block',
			'sub_fields' => array(
				ch_tab( 'Text' ),
				ch_text( 'eyebrow', 'Small line above the headline' ),
				ch_text( 'title', 'Headline', array( 'instructions' => 'Wrap a word in *asterisks* to italicise it.' ) ),
				ch_area( 'lede', 'Intro text' ),
				ch_link( 'button', 'Main button' ),
				ch_tab( 'Video' ),
				ch_video( 'video', 'Video (desktop, landscape)', array( 'wrapper' => array( 'width' => 50 ) ) ),
				ch_video( 'video_mobile', 'Video (mobile, portrait)', array( 'wrapper' => array( 'width' => 50 ), 'instructions' => 'Optional 9:16 version for phones.' ) ),
				ch_image( 'poster', 'Poster image', array( 'instructions' => 'Shown while the video loads. Use the video\'s first frame.' ) ),
				ch_tab( 'Live tour' ),
				ch_url( 'tour_url', 'Tour link', array( 'instructions' => 'Giraffe360 tour link. Adds a button that opens the tour full-screen.' ) ),
				ch_text( 'tour_label', 'Tour button text', array( 'default_value' => 'Take a live 360° tour', 'wrapper' => array( 'width' => 50 ) ) ),
				ch_text( 'tour_title', 'Tour name (for screen readers)', array( 'wrapper' => array( 'width' => 50 ) ) ),
				ch_tab( 'Quick links' ),
				ch_text( 'chips_label', 'Label', array( 'default_value' => 'Built for' ) ),
				ch_repeat( 'chips', 'Links', array( ch_link( 'link', 'Link' ) ), array( 'layout' => 'table' ) ),
			),
		),

		'page_header' => array(
			'label'      => 'Page header (inner pages)',
			'display'    => 'block',
			'sub_fields' => array(
				ch_tab( 'Text' ),
				ch_text( 'eyebrow', 'Small line above the headline', array( 'wrapper' => array( 'width' => 50 ) ) ),
				ch_text( 'title', 'Headline', array( 'instructions' => 'Use the page\'s main search term, e.g. "360° virtual tours in Wiltshire". Wrap a word in *asterisks* to highlight it.' ) ),
				ch_area( 'lede', 'Intro text' ),
				ch_lines_field( 'ticks', 'Tick points (optional)' ),
				ch_link( 'button', 'Main button' ),
				ch_tab( 'Media' ),
				ch_image( 'image', 'Image', array( 'instructions' => 'Landscape, our own work. Leave empty for a text-only header.' ) ),
				ch_url( 'embed_url', 'Live embed link (optional)', array( 'instructions' => 'Giraffe360 tour or 3D floor plan link. Adds a button that loads it over the image.' ) ),
				ch_text( 'embed_label', 'Embed button text', array( 'default_value' => 'Explore it live', 'wrapper' => array( 'width' => 50 ) ) ),
				ch_text( 'embed_title', 'Embed name (for screen readers)', array( 'wrapper' => array( 'width' => 50 ) ) ),
			),
		),

		'links' => array(
			'label'      => 'Related pages (cards)',
			'display'    => 'block',
			'sub_fields' => array(
				ch_anchor(),
				ch_text( 'eyebrow', 'Small heading', array( 'wrapper' => array( 'width' => 50 ) ) ),
				ch_text( 'title', 'Title' ),
				ch_area( 'lede', 'Intro text' ),
				ch_field( 'select', 'source', 'Which pages', array(
					'choices'       => array(
						'children' => 'Pages under this page',
						'parent'   => 'Pages under another page (e.g. all services)',
						'pick'     => 'Pick pages',
					),
					'default_value' => 'children',
					'instructions'  => 'Cards use each page\'s featured image and excerpt. Only published pages are shown.',
				) ),
				ch_when( ch_field( 'post_object', 'parent', 'Parent page', array( 'post_type' => array( 'page' ), 'return_format' => 'id', 'allow_null' => 1 ) ), '@source', array( 'parent' ) ),
				ch_when( ch_field( 'relationship', 'pages', 'Pages', array( 'post_type' => array( 'page', 'case_study' ), 'return_format' => 'id', 'filters' => array( 'search', 'post_type' ) ) ), '@source', array( 'pick' ) ),
			),
		),

		'ticker' => array(
			'label'      => 'Scrolling ticker',
			'display'    => 'block',
			'sub_fields' => array( ch_lines_field( 'items', 'Words' ) ),
		),

		'features' => array(
			'label'      => 'Services list (numbered features)',
			'display'    => 'block',
			'sub_fields' => array(
				ch_anchor(),
				ch_text( 'eyebrow', 'Small heading', array( 'wrapper' => array( 'width' => 50 ) ) ),
				ch_area( 'title', 'Title', array( 'rows' => 2, 'instructions' => 'Press Enter for a line break.' ) ),
				ch_area( 'lede', 'Intro text' ),
				ch_repeat( 'items', 'Services', array(
					ch_tab( 'Text' ),
					ch_text( 'title', 'Title' ),
					ch_area( 'text', 'Description' ),
					ch_lines_field( 'ticks', 'Tick points' ),
					ch_link( 'button', 'Button' ),
					ch_tab( 'Media' ),
					ch_field( 'select', 'media', 'Media type', array( 'choices' => $media_types, 'default_value' => 'image' ) ),
					ch_text( 'tag', 'Label on the media', array( 'instructions' => 'e.g. Interactive, Video, Aerial' ) ),
					ch_when( ch_video( 'video', 'Video' ), '@media', array( 'video' ) ),
					ch_when( ch_bool( 'contain', 'Show the whole video on a light background', array( 'instructions' => 'Use for floor plan models and other footage that must not be cropped.' ) ), '@media', array( 'video' ) ),
					ch_when( ch_image( 'poster', 'Video poster image' ), '@media', array( 'video' ) ),
					ch_when( ch_image( 'image', 'Image' ), '@media', array( 'image', 'drone' ) ),
					ch_when( ch_gallery( 'images', 'Images', array( 'instructions' => 'Slideshow: 3–6 images. Floor plans: exactly 2.' ) ), '@media', array( 'slideshow', 'plans' ) ),
					ch_when( ch_video( 'vertical_video', 'Vertical video (9:16)' ), '@media', array( 'drone' ) ),
					ch_when( ch_image( 'vertical_poster', 'Vertical video poster' ), '@media', array( 'drone' ) ),
					ch_url( 'embed_url', 'Live embed link (optional)', array( 'instructions' => 'Giraffe360 tour or 3D floor plan link. Adds a button that loads it in place of the media.' ) ),
					ch_text( 'embed_label', 'Embed button text', array( 'default_value' => 'Explore it live', 'wrapper' => array( 'width' => 50 ) ) ),
					ch_text( 'embed_title', 'Embed name (for screen readers)', array( 'wrapper' => array( 'width' => 50 ) ) ),
				), array( 'button_label' => 'Add service', 'collapsed' => '@title' ) ),
			),
		),

		'statement' => array(
			'label'      => 'Statement band (dark, with video)',
			'display'    => 'block',
			'sub_fields' => array(
				ch_text( 'eyebrow', 'Small heading' ),
				ch_text( 'title', 'Statement', array( 'instructions' => 'Wrap a word in *asterisks* to highlight it in yellow.' ) ),
				ch_video( 'video', 'Background video', array( 'wrapper' => array( 'width' => 50 ) ) ),
				ch_image( 'poster', 'Poster image', array( 'wrapper' => array( 'width' => 50 ) ) ),
				ch_repeat( 'stats', 'Stats', array(
					ch_text( 'value', 'Number', array( 'wrapper' => array( 'width' => 30 ) ) ),
					ch_text( 'label', 'Label', array( 'wrapper' => array( 'width' => 70 ) ) ),
				), array( 'layout' => 'table', 'max' => 4, 'collapsed' => '@value' ) ),
			),
		),

		'extras' => array(
			'label'      => 'Before/after slider + tiles',
			'display'    => 'block',
			'sub_fields' => array(
				ch_text( 'eyebrow', 'Small heading', array( 'wrapper' => array( 'width' => 50 ) ) ),
				ch_text( 'title', 'Title', array( 'wrapper' => array( 'width' => 50 ) ) ),
				ch_area( 'lede', 'Intro text' ),
				ch_image( 'before', 'Before image', array( 'wrapper' => array( 'width' => 50 ) ) ),
				ch_image( 'after', 'After image', array( 'wrapper' => array( 'width' => 50 ) ) ),
				ch_repeat( 'tiles', 'Tiles (up to 3)', array(
					ch_field( 'select', 'type', 'Type', array( 'choices' => array( 'image' => 'Image', 'video' => 'Video' ), 'default_value' => 'image', 'wrapper' => array( 'width' => 30 ) ) ),
					ch_text( 'caption', 'Caption', array( 'wrapper' => array( 'width' => 70 ) ) ),
					ch_when( ch_image( 'image', 'Image' ), '@type', array( 'image' ) ),
					ch_when( ch_video( 'video', 'Video' ), '@type', array( 'video' ) ),
					ch_when( ch_image( 'poster', 'Video poster' ), '@type', array( 'video' ) ),
				), array( 'max' => 3, 'button_label' => 'Add tile', 'collapsed' => '@caption' ) ),
			),
		),

		'sectors' => array(
			'label'      => 'Who it\'s for (sectors)',
			'display'    => 'block',
			'sub_fields' => array(
				ch_anchor(),
				ch_text( 'eyebrow', 'Small heading', array( 'wrapper' => array( 'width' => 50 ) ) ),
				ch_text( 'title', 'Title' ),
				ch_area( 'lede', 'Intro text' ),
				ch_tab( 'Featured' ),
				ch_image( 'feature_image', 'Image' ),
				ch_text( 'feature_kicker', 'Small heading' ),
				ch_text( 'feature_title', 'Title' ),
				ch_area( 'feature_text', 'Text' ),
				ch_lines_field( 'feature_ticks', 'Tick points' ),
				ch_link( 'feature_button', 'Button' ),
				ch_tab( 'Cards' ),
				ch_repeat( 'items', 'Sector cards', array(
					ch_image( 'image', 'Image', array( 'wrapper' => array( 'width' => 30 ) ) ),
					ch_text( 'kicker', 'Small heading', array( 'wrapper' => array( 'width' => 70 ) ) ),
					ch_text( 'title', 'Title' ),
					ch_area( 'text', 'Text' ),
					ch_link( 'link', 'Link' ),
				), array( 'button_label' => 'Add sector', 'collapsed' => '@title' ) ),
				ch_rich( 'more', 'Line under the cards' ),
			),
		),

		'process' => array(
			'label'      => 'How it works (steps)',
			'display'    => 'block',
			'sub_fields' => array(
				ch_anchor(),
				ch_text( 'eyebrow', 'Small heading', array( 'wrapper' => array( 'width' => 50 ) ) ),
				ch_text( 'title', 'Title' ),
				ch_video( 'video', 'Video (portrait works best)', array( 'wrapper' => array( 'width' => 50 ) ) ),
				ch_image( 'poster', 'Poster image', array( 'wrapper' => array( 'width' => 50 ), 'instructions' => 'Or use an image on its own.' ) ),
				ch_repeat( 'steps', 'Steps', array(
					ch_text( 'title', 'Title' ),
					ch_area( 'text', 'Text' ),
				), array( 'button_label' => 'Add step', 'collapsed' => '@title' ) ),
				ch_link( 'button', 'Button' ),
			),
		),

		'gallery' => array(
			'label'      => 'Scrolling photo strip',
			'display'    => 'block',
			'sub_fields' => array( ch_gallery( 'images', 'Images', array( 'instructions' => 'Landscape photos work best. 6–12 images.' ) ) ),
		),

		'cta_band' => array(
			'label'      => 'Yellow call-to-action band',
			'display'    => 'block',
			'sub_fields' => array(
				ch_text( 'title', 'Title', array( 'wrapper' => array( 'width' => 60 ) ) ),
				ch_link( 'button', 'Button', array( 'wrapper' => array( 'width' => 40 ) ) ),
			),
		),

		'faqs' => array(
			'label'      => 'FAQs',
			'display'    => 'block',
			'sub_fields' => array(
				ch_anchor(),
				ch_text( 'eyebrow', 'Small heading', array( 'wrapper' => array( 'width' => 50 ) ) ),
				ch_text( 'title', 'Title' ),
				ch_area( 'lede', 'Intro text' ),
				ch_link( 'button', 'Button' ),
				ch_repeat( 'items', 'Questions', array(
					ch_text( 'question', 'Question' ),
					ch_rich( 'answer', 'Answer' ),
				), array( 'button_label' => 'Add question', 'collapsed' => '@question' ) ),
			),
		),

		'text' => array(
			'label'      => 'Text block',
			'display'    => 'block',
			'sub_fields' => array(
				ch_anchor(),
				ch_text( 'eyebrow', 'Small heading', array( 'wrapper' => array( 'width' => 50 ) ) ),
				ch_text( 'title', 'Title' ),
				ch_rich( 'content', 'Content', array( 'toolbar' => 'full', 'media_upload' => 1 ) ),
			),
		),

		'case_studies' => array(
			'label'      => 'Latest case studies',
			'display'    => 'block',
			'sub_fields' => array(
				ch_text( 'eyebrow', 'Small heading', array( 'default_value' => 'Case studies', 'wrapper' => array( 'width' => 50 ) ) ),
				ch_text( 'title', 'Title', array( 'default_value' => 'Recent captures' ) ),
				ch_field( 'number', 'count', 'How many', array( 'default_value' => 3, 'min' => 1, 'max' => 12 ) ),
				ch_field( 'select', 'sector', 'Only this sector (optional)', array( 'choices' => ch_sector_choices(), 'allow_null' => 1, 'wrapper' => array( 'width' => 50 ) ) ),
				ch_field( 'select', 'service', 'Only this service (optional)', array( 'choices' => ch_service_choices(), 'allow_null' => 1, 'wrapper' => array( 'width' => 50 ) ) ),
			),
		),
	);
}

/* ---------- Register ---------- */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page( array(
		'page_title' => 'Site settings',
		'menu_title' => 'Site settings',
		'menu_slug'  => 'capturehaus-settings',
		'capability' => 'edit_posts',
		'icon_url'   => 'dashicons-admin-generic',
		'position'   => 59,
		'redirect'   => false,
	) );

	ch_group( 'sections', 'Page sections', array(
		ch_field( 'flexible_content', 'sections', 'Sections', array(
			'button_label' => 'Add section',
			'instructions' => 'Build the page from sections. Drag to reorder; use a Hero first for a full-screen opening.',
			'layouts'      => ch_section_layouts(),
		) ),
	), array(
		array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ) ),
	), array( 'menu_order' => 0, 'style' => 'seamless' ) );

	ch_group( 'page_options', 'Page options', array(
		ch_bool( 'hide_quote', 'Hide the quote form at the bottom of this page' ),
		ch_area( 'intro', 'Intro under the page title', array( 'instructions' => 'Only shown on pages that don\'t start with a Hero section.' ) ),
	), array(
		array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ) ),
		array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ) ),
		array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'case_study' ) ),
	), array( 'position' => 'side', 'menu_order' => 10 ) );

	ch_group( 'case_study', 'Case study details', array(
		ch_text( 'location', 'Location', array( 'wrapper' => array( 'width' => 50 ) ) ),
		ch_field( 'select', 'sector', 'Sector', array(
			'choices'       => ch_sector_choices(),
			'allow_null'    => 1,
			'wrapper'       => array( 'width' => 50 ),
		) ),
		ch_field( 'checkbox', 'services', 'Services delivered', array(
			'choices' => ch_service_choices(),
			'layout'  => 'horizontal',
		) ),
		ch_url( 'tour_url', 'Live 360° tour link', array( 'wrapper' => array( 'width' => 50 ) ) ),
		ch_url( 'plan_url', '3D floor plan link', array( 'wrapper' => array( 'width' => 50 ) ) ),
		ch_gallery( 'gallery', 'Gallery' ),
	), array(
		array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'case_study' ) ),
	), array( 'menu_order' => 0 ) );

	ch_group( 'settings', 'Site settings', array(
		ch_tab( 'Contact' ),
		ch_text( 'phone', 'Phone number', array( 'default_value' => '01672 559033', 'wrapper' => array( 'width' => 50 ) ) ),
		ch_link( 'header_cta', 'Header button', array( 'wrapper' => array( 'width' => 50 ) ) ),
		ch_tab( 'Quote form' ),
		ch_text( 'quote_eyebrow', 'Small heading', array( 'wrapper' => array( 'width' => 50 ) ) ),
		ch_text( 'quote_title', 'Title', array( 'wrapper' => array( 'width' => 50 ) ) ),
		ch_area( 'quote_lede', 'Intro text' ),
		ch_lines_field( 'quote_ticks', 'Tick points' ),
		ch_image( 'quote_image', 'Image (desktop only)' ),
		ch_text( 'hubspot_portal_id', 'HubSpot portal ID', array( 'default_value' => '9342384', 'wrapper' => array( 'width' => 33 ) ) ),
		ch_text( 'hubspot_form_id', 'HubSpot form ID', array( 'default_value' => '836fd6c5-0ff3-4238-8a02-c9d22cae39d6', 'wrapper' => array( 'width' => 33 ) ) ),
		ch_text( 'hubspot_region', 'HubSpot region', array( 'default_value' => 'eu1', 'wrapper' => array( 'width' => 33 ) ) ),
		ch_tab( 'Footer' ),
		ch_area( 'footer_towns', 'Towns we cover', array( 'rows' => 4, 'instructions' => 'One per line. Shown in the footer after links to the area pages (pages under "Areas we cover").' ) ),
		ch_text( 'footer_small', 'Copyright line', array( 'instructions' => '{year} is replaced with the current year.' ) ),
		ch_area( 'footer_legal', 'Company / legal line' ),
	), array(
		array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'capturehaus-settings' ) ),
	) );
} );
