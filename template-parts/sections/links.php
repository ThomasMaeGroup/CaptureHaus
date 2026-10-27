<?php
/**
 * Cards linking to related pages: the pages under this one (hubs), the pages under another
 * page (e.g. every service from a sector page), or a hand-picked list.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$s       = $args['section'];
$source  = $s['source'] ?? 'children';
$current = get_queried_object_id();

if ( 'pick' === $source ) {
	$query_args = array(
		'post_type' => array( 'page', 'case_study' ),
		'post__in'  => array_map( 'intval', (array) ( $s['pages'] ?? array() ) ) ?: array( 0 ),
		'orderby'   => 'post__in',
	);
} else {
	$parent = 'parent' === $source ? (int) ( $s['parent'] ?? 0 ) : $current;
	if ( ! $parent ) {
		return;
	}
	$query_args = array(
		'post_type'   => 'page',
		'post_parent' => $parent,
		'orderby'     => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	);
}

$query = new WP_Query( array_merge( $query_args, array(
	'post_status'         => 'publish',
	'post__not_in'        => array( $current ),
	'posts_per_page'      => 12,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
) ) );
if ( ! $query->have_posts() ) {
	return;
}
?>
<section class="section links"<?php echo capturehaus_anchor( $s ); // phpcs:ignore ?>>
	<div class="container">
		<?php if ( $s['eyebrow'] || $s['title'] || $s['lede'] ) : ?>
			<header class="section-head">
				<?php if ( $s['eyebrow'] ) : ?><p class="eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p><?php endif; ?>
				<?php if ( $s['title'] ) : ?><h2 class="section-title"><?php echo capturehaus_text( $s['title'] ); // phpcs:ignore ?></h2><?php endif; ?>
				<?php if ( $s['lede'] ) : ?><p class="section-lede"><?php echo esc_html( $s['lede'] ); ?></p><?php endif; ?>
			</header>
		<?php endif; ?>
		<div class="card-grid">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				get_template_part( 'template-parts/card' );
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
