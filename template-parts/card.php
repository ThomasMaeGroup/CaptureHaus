<?php
/**
 * Card for case studies and blog posts in grids.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$is_case = 'case_study' === get_post_type();
$kicker  = $is_case ? implode( ' · ', array_filter( array( get_field( 'sector' ), get_field( 'location' ) ) ) ) : get_the_date();
?>
<article class="card reveal">
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
