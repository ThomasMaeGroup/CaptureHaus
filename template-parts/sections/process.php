<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$s = $args['section'];
?>
<section class="section process"<?php echo capturehaus_anchor( $s ); // phpcs:ignore ?>>
	<div class="container process__inner">
		<div class="process__media reveal">
			<?php
			echo $s['video']
				? capturehaus_video( $s['video'], $s['poster'] ) // phpcs:ignore
				: capturehaus_image( $s['poster'], '', '(min-width: 900px) 45vw, 100vw' ); // phpcs:ignore
			?>
		</div>
		<div class="process__body">
			<?php if ( $s['eyebrow'] ) : ?><p class="eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p><?php endif; ?>
			<?php if ( $s['title'] ) : ?><h2 class="section-title"><?php echo capturehaus_text( $s['title'] ); // phpcs:ignore ?></h2><?php endif; ?>
			<?php if ( $s['steps'] ) : ?>
				<ol class="steps">
					<?php foreach ( $s['steps'] as $n => $step ) : ?>
						<li class="reveal">
							<span class="steps__n"><?php echo (int) $n + 1; ?></span>
							<div><h3><?php echo esc_html( $step['title'] ); ?></h3><p><?php echo esc_html( $step['text'] ); ?></p></div>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
			<?php echo capturehaus_button( $s['button'], 'btn btn--signature btn--lg', '→' ); // phpcs:ignore ?>
		</div>
	</div>
</section>
