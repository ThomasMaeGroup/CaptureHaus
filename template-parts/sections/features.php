<?php
/**
 * Numbered service features, alternating media left/right.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$s = $args['section'];
?>
<section class="section capture"<?php echo capturehaus_anchor( $s ); // phpcs:ignore ?>>
	<div class="container">
		<?php if ( $s['eyebrow'] || $s['title'] || $s['lede'] ) : ?>
			<header class="section-head">
				<?php if ( $s['eyebrow'] ) : ?><p class="eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p><?php endif; ?>
				<?php if ( $s['title'] ) : ?><h2 class="section-title"><?php echo capturehaus_text( $s['title'], 'em', '', true ); // phpcs:ignore ?></h2><?php endif; ?>
				<?php if ( $s['lede'] ) : ?><p class="section-lede"><?php echo esc_html( $s['lede'] ); ?></p><?php endif; ?>
			</header>
		<?php endif; ?>

		<?php foreach ( (array) $s['items'] as $i => $item ) :
			$media     = $item['media'];
			$classes   = array( 'feature__media' );
			$classes[] = 'plans' === $media ? 'feature__media--plans' : '';
			$classes[] = 'drone' === $media ? 'feature__media--drone' : '';
			$classes[] = ( 'video' === $media && $item['contain'] ) ? 'feature__media--light' : '';
			?>
			<article class="feature<?php echo $i % 2 ? ' feature--flip' : ''; ?> reveal">
				<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">
					<?php
					switch ( $media ) {
						case 'video':
							echo capturehaus_video( $item['video'], $item['poster'] ); // phpcs:ignore
							break;
						case 'image':
							echo capturehaus_image( $item['image'], '', '(min-width: 900px) 58vw, 100vw' ); // phpcs:ignore
							break;
						case 'slideshow':
							echo '<div class="slideshow" data-slideshow>';
							foreach ( (array) $item['images'] as $img ) {
								echo capturehaus_image( $img, '', '(min-width: 900px) 58vw, 100vw' ); // phpcs:ignore
							}
							echo '</div>';
							break;
						case 'plans':
							foreach ( array_slice( (array) $item['images'], 0, 2 ) as $img ) {
								echo capturehaus_image( $img, '', '(min-width: 900px) 29vw, 50vw' ); // phpcs:ignore
							}
							break;
						case 'drone':
							echo capturehaus_image( $item['image'], '', '(min-width: 900px) 58vw, 100vw' ); // phpcs:ignore
							if ( $item['vertical_video'] ) {
								echo '<div class="drone-card">' . capturehaus_video( $item['vertical_video'], $item['vertical_poster'], '', $item['title'] ) . '</div>'; // phpcs:ignore
							}
							break;
					}
					?>
					<?php if ( $item['tag'] ) : ?><span class="feature__tag"><?php echo esc_html( $item['tag'] ); ?></span><?php endif; ?>
					<?php if ( $item['embed_url'] ) : ?>
						<button class="btn btn--signature embed-launch" type="button" data-embed="<?php echo esc_url( $item['embed_url'] ); ?>" data-embed-title="<?php echo esc_attr( $item['embed_title'] ?: $item['title'] ); ?>"><?php echo esc_html( $item['embed_label'] ?: __( 'Explore it live', 'capturehaus' ) ); ?> <span aria-hidden="true">→</span></button>
					<?php endif; ?>
				</div>
				<div class="feature__body">
					<span class="feature__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
					<h3><?php echo esc_html( $item['title'] ); ?></h3>
					<?php if ( $item['text'] ) : ?><p><?php echo esc_html( $item['text'] ); ?></p><?php endif; ?>
					<?php echo capturehaus_ticks( $item['ticks'] ); // phpcs:ignore ?>
					<?php echo capturehaus_button( $item['button'] ); // phpcs:ignore ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>
