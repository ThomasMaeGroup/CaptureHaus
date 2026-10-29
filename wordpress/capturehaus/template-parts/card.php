<?php
/**
 * Card for case studies, pages and blog posts in grids.
 * Optional arg: feature (wide card spanning the grid, for the newest post).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$type = get_post_type();
if ( 'case_study' === $type ) {
	$kicker = implode( ' · ', array_filter( array( get_field( 'sector' ), get_field( 'location' ) ) ) );
} elseif ( 'page' === $type ) {
	$kicker = wp_get_post_parent_id() ? get_the_title( wp_get_post_parent_id() ) : '';
} else {
	$category = capturehaus_post_category();
	$kicker   = implode( ' · ', array_filter( array( $category ? $category->name : '', get_the_date() ) ) );
}
?>
<article class="card reveal<?php echo ! empty( $args['feature'] ) ? ' card--feature' : ''; ?>">
	<a class="card__link" href="<?php the_permalink(); ?>">
		<div class="card__media">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'large', array( 'sizes' => '(min-width: 900px) 33vw, (min-width: 600px) 50vw, 100vw', 'loading' => 'lazy' ) ); ?>
			<?php endif; ?>
		</div>
		<div class="card__body">
			<?php if ( $kicker ) : ?><span class="card__kicker"><?php echo esc_html( $kicker ); ?></span><?php endif; ?>
			<h3 class="card__title"><?php the_title(); ?></h3>
			<?php if ( has_excerpt() ) : ?><p class="card__text"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
		</div>
	</a>
</article>
