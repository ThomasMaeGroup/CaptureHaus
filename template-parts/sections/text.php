<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$s = $args['section'];
?>
<section class="section section--prose"<?php echo capturehaus_anchor( $s ); // phpcs:ignore ?>>
	<div class="container prose">
		<?php if ( $s['eyebrow'] ) : ?><p class="eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p><?php endif; ?>
		<?php if ( $s['title'] ) : ?><h2 class="section-title"><?php echo capturehaus_text( $s['title'] ); // phpcs:ignore ?></h2><?php endif; ?>
		<?php echo wp_kses_post( $s['content'] ); ?>
	</div>
</section>
