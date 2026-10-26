<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$s = $args['section'];
?>
<section class="cta-band">
	<div class="container cta-band__inner">
		<h2><?php echo capturehaus_text( $s['title'] ); // phpcs:ignore ?></h2>
		<?php echo capturehaus_button( $s['button'], 'btn btn--dark btn--lg', '→' ); // phpcs:ignore ?>
	</div>
</section>
