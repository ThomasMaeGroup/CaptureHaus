<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$s    = $args['section'];
$meta = array();
if ( ! empty( $s['sector'] ) ) {
	$meta[] = array( 'key' => 'sector', 'value' => $s['sector'] );
}
if ( ! empty( $s['service'] ) ) {
	// "Services delivered" is a checkbox, stored as a serialised list.
	$meta[] = array( 'key' => 'services', 'value' => '"' . $s['service'] . '"', 'compare' => 'LIKE' );
}
$query = new WP_Query( array(
	'post_type'           => 'case_study',
	'posts_per_page'      => max( 1, (int) $s['count'] ),
	'meta_query'          => $meta, // phpcs:ignore WordPress.DB.SlowDBQuery
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
) );
if ( ! $query->have_posts() ) {
	return;
}
?>
<section class="section">
	<div class="container">
		<header class="section-head section-head--split">
			<div>
				<?php if ( $s['eyebrow'] ) : ?><p class="eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p><?php endif; ?>
				<?php if ( $s['title'] ) : ?><h2 class="section-title"><?php echo capturehaus_text( $s['title'] ); // phpcs:ignore ?></h2><?php endif; ?>
			</div>
			<a class="btn btn--dark" href="<?php echo esc_url( get_post_type_archive_link( 'case_study' ) ); ?>"><?php esc_html_e( 'All case studies', 'capturehaus' ); ?></a>
		</header>
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
