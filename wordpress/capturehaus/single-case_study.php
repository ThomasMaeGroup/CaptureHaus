<?php
/**
 * Single case study: title band, live tour / 3D plan, story, gallery.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

while ( have_posts() ) :
	the_post();
	$services = (array) get_field( 'services' );
	get_template_part( 'template-parts/page-head', null, array(
		'eyebrow' => __( 'Case study', 'capturehaus' ),
		'intro'   => has_excerpt() ? get_the_excerpt() : '',
		'meta'    => array_merge( array( get_field( 'location' ), get_field( 'sector' ) ), $services ),
	) );

	$embeds = array_filter( array(
		'tour' => get_field( 'tour_url' ),
		'plan' => get_field( 'plan_url' ),
	) );
	?>

	<?php if ( has_post_thumbnail() || $embeds ) : ?>
		<section class="section case-media">
			<div class="container">
				<div class="feature__media case-media__frame">
					<?php the_post_thumbnail( 'full', array( 'sizes' => '(min-width: 1280px) 1200px, 100vw' ) ); ?>
					<?php if ( ! empty( $embeds['tour'] ) ) : ?>
						<button class="btn btn--signature embed-launch" type="button" data-embed="<?php echo esc_url( $embeds['tour'] ); ?>" data-embed-title="<?php echo esc_attr( sprintf( __( '360° virtual tour of %s', 'capturehaus' ), get_the_title() ) ); ?>"><?php esc_html_e( 'Explore the live tour', 'capturehaus' ); ?> <span aria-hidden="true">→</span></button>
					<?php endif; ?>
				</div>
				<?php if ( ! empty( $embeds['plan'] ) ) : ?>
					<p class="case-media__more"><a href="<?php echo esc_url( $embeds['plan'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Open the 3D floor plan', 'capturehaus' ); ?> <span aria-hidden="true">↗</span></a></p>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( '' !== trim( get_the_content() ) ) : ?>
		<article class="section section--prose">
			<div class="container prose"><?php the_content(); ?></div>
		</article>
	<?php endif; ?>

	<?php if ( $gallery = get_field( 'gallery' ) ) : ?>
		<section class="gallery" aria-label="<?php esc_attr_e( 'Gallery', 'capturehaus' ); ?>">
			<div class="gallery__track">
				<?php foreach ( $gallery as $img ) : ?>
					<?php echo capturehaus_image( $img, '', '(min-width: 900px) 40vw, 80vw' ); // phpcs:ignore ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
