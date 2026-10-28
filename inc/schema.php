<?php
/**
 * Structured data added to Yoast's schema graph:
 * - the Organization also becomes a ProfessionalService with phone, address and areas served (local SEO),
 *   and is linked to its parent company Bread & Butter, whose social profiles these are,
 * - service pages (under Services) and sector pages (under Who it's for) get a Service node,
 * - names are decoded so breadcrumbs read "Who it's for", not "Who it&#8217;s for".
 * FAQ markup is added separately by the FAQs section (see capturehaus_faq_schema_add()).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Business address: Site settings > Business, falling back to the registered address. */
function capturehaus_business_address() {
	$value = function ( $name, $fallback ) {
		$v = capturehaus_option( $name );
		return null === $v || '' === $v ? $fallback : $v;
	};
	return array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => $value( 'business_street', 'Rockley 3, Elcot Mews, Elcot Lane' ),
		'addressLocality' => $value( 'business_town', 'Marlborough' ),
		'addressRegion'   => 'Wiltshire',
		'postalCode'      => $value( 'business_postcode', 'SN8 2AE' ),
		'addressCountry'  => 'GB',
	);
}

/** Parent company (Bread & Butter, trading name of Thomas Mae Group Ltd) with its social profiles. */
function capturehaus_parent_organization() {
	$url = capturehaus_option( 'business_parent_url' ) ?: 'https://bread-butter.co.uk/';
	return array(
		'@type'     => 'Organization',
		'@id'       => trailingslashit( $url ) . '#organization',
		'name'      => capturehaus_parent_name(),
		'legalName' => 'Thomas Mae Group Ltd',
		'url'       => $url,
		'sameAs'    => capturehaus_social_links(),
	);
}

/** Areas served: Wiltshire plus the towns listed in Site settings > Footer. */
function capturehaus_areas_served() {
	$areas = array( array( '@type' => 'AdministrativeArea', 'name' => 'Wiltshire' ) );
	foreach ( capturehaus_lines( capturehaus_option( 'footer_towns' ) ) as $town ) {
		if ( 0 !== strcasecmp( $town, 'Wiltshire' ) ) {
			$areas[] = array( '@type' => 'Place', 'name' => ucfirst( preg_replace( '/^the\s+/i', 'The ', $town ) ) );
		}
	}
	return $areas;
}

/** Decode HTML entities in every "name" in the graph (WordPress curls apostrophes into &#8217;). */
function capturehaus_schema_decode_names( $node ) {
	if ( ! is_array( $node ) ) {
		return $node;
	}
	foreach ( $node as $key => $value ) {
		if ( 'name' === $key && is_string( $value ) ) {
			$node[ $key ] = html_entity_decode( $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		} elseif ( is_array( $value ) ) {
			$node[ $key ] = capturehaus_schema_decode_names( $value );
		}
	}
	return $node;
}

add_filter( 'wpseo_schema_graph', function ( $graph ) {
	if ( ! is_array( $graph ) ) {
		return $graph;
	}
	$org_id  = '';
	$page_id = '';
	$phone   = capturehaus_option( 'phone' ) ?: '01672 559033';

	foreach ( $graph as $i => $node ) {
		$types = (array) ( $node['@type'] ?? array() );
		if ( in_array( 'Organization', $types, true ) ) {
			$graph[ $i ]['@type']      = array_values( array_unique( array_merge( $types, array( 'ProfessionalService' ) ) ) );
			$graph[ $i ]['telephone']  = $phone;
			$graph[ $i ]['address']    = capturehaus_business_address();
			$graph[ $i ]['areaServed'] = capturehaus_areas_served();
			$graph[ $i ]['parentOrganization'] = capturehaus_parent_organization();
			$graph[ $i ]['url']        = $graph[ $i ]['url'] ?? home_url( '/' );
			if ( empty( $graph[ $i ]['image'] ) && ! empty( $graph[ $i ]['logo'] ) ) {
				$graph[ $i ]['image'] = $graph[ $i ]['logo']; // LocalBusiness types expect an image.
			}
			$org_id = $node['@id'] ?? '';
		}
		if ( in_array( 'WebPage', $types, true ) ) {
			$page_id = $node['@id'] ?? '';
		}
	}

	// Service node for pages under Services (what we capture) and Who it's for (who it's for).
	if ( is_page() && $org_id ) {
		$post   = get_queried_object();
		$parent = $post->post_parent ? get_post( $post->post_parent ) : null;
		if ( $parent && in_array( $parent->post_name, array( 'services', 'sectors' ), true ) ) {
			$service = array(
				'@type'       => 'Service',
				'@id'         => get_permalink( $post ) . '#service',
				'name'        => get_the_title( $post ),
				'url'         => get_permalink( $post ),
				'provider'    => array( '@id' => $org_id ),
				'areaServed'  => capturehaus_areas_served(),
				'description' => has_excerpt( $post ) ? get_the_excerpt( $post ) : '',
			);
			if ( 'services' === $parent->post_name ) {
				$service['serviceType'] = get_the_title( $post );
			} else {
				$service['serviceType'] = '360° virtual tours, floor plans and property media';
				$service['audience']    = array( '@type' => 'Audience', 'audienceType' => get_the_title( $post ) );
			}
			if ( $page_id ) {
				$service['mainEntityOfPage'] = array( '@id' => $page_id );
			}
			if ( has_post_thumbnail( $post ) ) {
				$service['image'] = get_the_post_thumbnail_url( $post, 'large' );
			}
			$graph[] = array_filter( $service );
		}
	}

	return capturehaus_schema_decode_names( $graph );
}, 20 );
