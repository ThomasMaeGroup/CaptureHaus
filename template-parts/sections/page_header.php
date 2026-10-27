<?php
/**
 * Compact header for inner pages (services, sectors, areas): breadcrumbs, headline, intro, and an
 * optional image with a live tour / 3D plan button. Lighter than the full-screen video Hero.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$s       = $args['section'];
$first   = 0 === $args['index'];
$heading = $first ? 'h1' : 'h2';
$image   = $s['image'] ?? 0;
$classes = 'page-header' . ( $first ? ' page-header--first' : '' ) . ( $image ? ' page-header--media' : '' );
?>
<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container page-header__inner">
		<div class="page-header__text">
			<?php if ( $first ) { echo capturehaus_breadcrumbs(); } // phpcs:ignore ?>
			<?php if ( $s['eyebrow'] ) : ?><p class="eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p><?php endif; ?>
			<<?php echo $heading; ?> class="page-header__title"><?php echo capturehaus_text( $s['title'] ?: get_the_title() ); // phpcs:ignore ?></<?php echo $heading; ?>>
			<?php if ( $s['lede'] ) : ?><p class="page-header__lede"><?php echo esc_html( $s['lede'] ); ?></p><?php endif; ?>
			<?php echo capturehaus_ticks( $s['ticks'] ); // phpcs:ignore ?>
			<?php echo capturehaus_button( $s['button'], 'btn btn--signature btn--lg', '→' ); // phpcs:ignore ?>
		</div>

		<?php if ( $image ) : ?>
			<div class="feature__media page-header__media">
				<?php echo capturehaus_image( $image, '', '(min-width: 900px) 50vw, 100vw', $first ); // phpcs:ignore ?>
				<?php if ( $s['embed_url'] ) : ?>
					<button class="btn btn--signature embed-launch" type="button" data-embed="<?php echo esc_url( $s['embed_url'] ); ?>" data-embed-title="<?php echo esc_attr( $s['embed_title'] ?: wp_strip_all_tags( $s['title'] ) ); ?>"><?php echo esc_html( $s['embed_label'] ?: __( 'Explore it live', 'capturehaus' ) ); ?> <span aria-hidden="true">→</span></button>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
