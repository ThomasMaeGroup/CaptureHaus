<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$s = $args['section'];
?>
<section class="section extras">
	<div class="container">
		<header class="section-head section-head--split">
			<div>
				<?php if ( $s['eyebrow'] ) : ?><p class="eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p><?php endif; ?>
				<?php if ( $s['title'] ) : ?><h2 class="section-title"><?php echo capturehaus_text( $s['title'] ); // phpcs:ignore ?></h2><?php endif; ?>
			</div>
			<?php if ( $s['lede'] ) : ?><p class="section-lede"><?php echo esc_html( $s['lede'] ); ?></p><?php endif; ?>
		</header>

		<div class="extras__grid">
			<?php if ( $s['before'] && $s['after'] ) : ?>
				<figure class="compare reveal" data-compare>
					<?php echo capturehaus_image( $s['after'], '', '(min-width: 900px) 60vw, 100vw' ); // phpcs:ignore ?>
					<div class="compare__before">
						<?php echo capturehaus_image( $s['before'], '', '(min-width: 900px) 60vw, 100vw' ); // phpcs:ignore ?>
					</div>
					<span class="compare__label compare__label--l"><?php esc_html_e( 'Before', 'capturehaus' ); ?></span>
					<span class="compare__label compare__label--r"><?php esc_html_e( 'After', 'capturehaus' ); ?></span>
					<div class="compare__handle" aria-hidden="true"></div>
					<input class="compare__range" type="range" min="0" max="100" value="50" aria-label="<?php esc_attr_e( 'Compare before and after', 'capturehaus' ); ?>">
				</figure>
			<?php endif; ?>

			<?php if ( $s['tiles'] ) : ?>
				<div class="extras__tiles">
					<?php foreach ( $s['tiles'] as $tile ) : ?>
						<div class="tile reveal">
							<?php
							echo 'video' === $tile['type']
								? capturehaus_video( $tile['video'], $tile['poster'] ) // phpcs:ignore
								: capturehaus_image( $tile['image'], '', '(min-width: 900px) 30vw, 50vw' ); // phpcs:ignore
							?>
							<?php if ( $tile['caption'] ) : ?><p class="tile__cap"><?php echo esc_html( $tile['caption'] ); ?></p><?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
