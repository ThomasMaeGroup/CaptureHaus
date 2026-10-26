<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$s = $args['section'];
?>
<section class="section sectors"<?php echo capturehaus_anchor( $s ); // phpcs:ignore ?>>
	<div class="container">
		<header class="section-head">
			<?php if ( $s['eyebrow'] ) : ?><p class="eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p><?php endif; ?>
			<?php if ( $s['title'] ) : ?><h2 class="section-title"><?php echo capturehaus_text( $s['title'] ); // phpcs:ignore ?></h2><?php endif; ?>
			<?php if ( $s['lede'] ) : ?><p class="section-lede"><?php echo esc_html( $s['lede'] ); ?></p><?php endif; ?>
		</header>

		<?php if ( $s['feature_title'] ) : ?>
			<article class="sector sector--feature reveal">
				<?php echo capturehaus_image( $s['feature_image'], 'sector__img', '100vw' ); // phpcs:ignore ?>
				<div class="sector__body">
					<?php if ( $s['feature_kicker'] ) : ?><span class="sector__kicker"><?php echo esc_html( $s['feature_kicker'] ); ?></span><?php endif; ?>
					<h3><?php echo esc_html( $s['feature_title'] ); ?></h3>
					<?php if ( $s['feature_text'] ) : ?><p><?php echo esc_html( $s['feature_text'] ); ?></p><?php endif; ?>
					<?php echo capturehaus_ticks( $s['feature_ticks'], true ); // phpcs:ignore ?>
					<?php echo capturehaus_button( $s['feature_button'], 'btn btn--signature' ); // phpcs:ignore ?>
				</div>
			</article>
		<?php endif; ?>

		<?php if ( $s['items'] ) : ?>
			<div class="sectors__grid">
				<?php foreach ( $s['items'] as $item ) : ?>
					<article class="sector reveal">
						<?php echo capturehaus_image( $item['image'], 'sector__img', '(min-width: 900px) 50vw, 100vw' ); // phpcs:ignore ?>
						<div class="sector__body">
							<?php if ( $item['kicker'] ) : ?><span class="sector__kicker"><?php echo esc_html( $item['kicker'] ); ?></span><?php endif; ?>
							<h3><?php echo esc_html( $item['title'] ); ?></h3>
							<?php if ( $item['text'] ) : ?><p><?php echo esc_html( $item['text'] ); ?></p><?php endif; ?>
							<?php if ( ! empty( $item['link']['url'] ) ) : ?>
								<a class="sector__link" href="<?php echo esc_url( $item['link']['url'] ); ?>"><?php echo esc_html( $item['link']['title'] ); ?> <span aria-hidden="true">→</span></a>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $s['more'] ) : ?>
			<div class="sectors__more"><?php echo wp_kses_post( $s['more'] ); ?></div>
		<?php endif; ?>
	</div>
</section>
