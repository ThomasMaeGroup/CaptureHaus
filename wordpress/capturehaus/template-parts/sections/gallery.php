<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$images = (array) $args['section']['images'];
if ( ! $images ) {
	return;
}
?>
<section class="gallery" aria-label="<?php esc_attr_e( 'Example captures', 'capturehaus' ); ?>">
	<div class="gallery__track">
		<?php foreach ( $images as $img ) : ?>
			<?php echo capturehaus_image( $img, '', '(min-width: 900px) 40vw, 80vw' ); // phpcs:ignore ?>
		<?php endforeach; ?>
	</div>
</section>
