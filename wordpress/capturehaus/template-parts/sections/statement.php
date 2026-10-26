<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$s = $args['section'];
?>
<section class="statement">
	<?php echo capturehaus_video( $s['video'], $s['poster'], 'statement__media' ); // phpcs:ignore ?>
	<div class="container statement__inner">
		<?php if ( $s['eyebrow'] ) : ?><p class="eyebrow eyebrow--light"><?php echo esc_html( $s['eyebrow'] ); ?></p><?php endif; ?>
		<h2 class="statement__title"><?php echo capturehaus_text( $s['title'], 'span', 'hl' ); // phpcs:ignore ?></h2>
		<?php if ( $s['stats'] ) : ?>
			<dl class="stats">
				<?php foreach ( $s['stats'] as $stat ) : ?>
					<div><dt><?php echo esc_html( $stat['value'] ); ?></dt><dd><?php echo esc_html( $stat['label'] ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
		<?php endif; ?>
	</div>
</section>
