<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$s       = $args['section'];
$first   = 0 === $args['index'];
$heading = $first ? 'h1' : 'h2';
?>
<section class="hero"<?php echo $first ? ' id="top"' : ''; ?>>
	<?php if ( $s['video'] ) : ?>
		<video class="hero__media" autoplay muted loop playsinline preload="auto"<?php echo $s['poster'] ? ' poster="' . esc_url( capturehaus_image_url( $s['poster'] ) ) . '"' : ''; ?> aria-hidden="true">
			<?php if ( $s['video_mobile'] ) : ?><source src="<?php echo esc_url( $s['video_mobile'] ); ?>" type="video/mp4" media="(max-width: 767px)"><?php endif; ?>
			<source src="<?php echo esc_url( $s['video'] ); ?>" type="video/mp4">
		</video>
	<?php elseif ( $s['poster'] ) : ?>
		<?php echo capturehaus_image( $s['poster'], 'hero__media', '100vw', $first, '' ); // phpcs:ignore ?>
	<?php endif; ?>
	<div class="hero__scrim" aria-hidden="true"></div>

	<div class="container hero__content">
		<?php if ( $s['eyebrow'] ) : ?><p class="eyebrow eyebrow--light"><span class="dot"></span> <?php echo esc_html( $s['eyebrow'] ); ?></p><?php endif; ?>
		<<?php echo $heading; ?> class="hero__title"><?php echo capturehaus_text( $s['title'] ); // phpcs:ignore ?></<?php echo $heading; ?>>
		<?php if ( $s['lede'] ) : ?><p class="hero__lede"><?php echo esc_html( $s['lede'] ); ?></p><?php endif; ?>
		<div class="hero__ctas">
			<?php echo capturehaus_button( $s['button'], 'btn btn--signature btn--lg', '→' ); // phpcs:ignore ?>
			<?php if ( $s['tour_url'] ) : ?>
				<button class="btn btn--ghost btn--lg" type="button" data-tour-open><?php echo esc_html( $s['tour_label'] ?: __( 'Take a live 360° tour', 'capturehaus' ) ); ?> <span aria-hidden="true">↗</span></button>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $s['chips'] ) : ?>
		<div class="container hero__sectors">
			<?php if ( $s['chips_label'] ) : ?><span class="hero__sectors-label"><?php echo esc_html( $s['chips_label'] ); ?></span><?php endif; ?>
			<?php foreach ( $s['chips'] as $chip ) : ?>
				<?php if ( ! empty( $chip['link']['url'] ) ) : ?><a href="<?php echo esc_url( $chip['link']['url'] ); ?>"><?php echo esc_html( $chip['link']['title'] ); ?></a><?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>

<?php if ( $s['tour_url'] ) : ?>
	<dialog class="tour-modal" data-tour-modal aria-label="<?php esc_attr_e( 'Live 360° virtual tour', 'capturehaus' ); ?>">
		<button class="tour-modal__close" type="button" data-tour-close aria-label="<?php esc_attr_e( 'Close tour', 'capturehaus' ); ?>">&times;</button>
		<iframe data-src="<?php echo esc_url( $s['tour_url'] ); ?>" title="<?php echo esc_attr( $s['tour_title'] ?: __( '360° virtual tour', 'capturehaus' ) ); ?>" allow="fullscreen; xr-spatial-tracking; gyroscope; accelerometer" allowfullscreen></iframe>
	</dialog>
<?php endif; ?>
