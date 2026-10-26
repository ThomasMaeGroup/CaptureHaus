<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$items = capturehaus_lines( $args['section']['items'] );
if ( ! $items ) {
	return;
}
$row = '';
foreach ( $items as $item ) {
	$row .= '<span>' . esc_html( $item ) . '</span>';
}
?>
<div class="ticker" aria-hidden="true">
	<div class="ticker__track"><?php echo $row . $row; // phpcs:ignore -- escaped above; doubled for a seamless loop. ?></div>
</div>
