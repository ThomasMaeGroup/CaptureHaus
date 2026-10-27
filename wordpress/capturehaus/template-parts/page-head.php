<?php
/**
 * Title band for pages that don't open with a Hero section.
 * Optional args: eyebrow, title, intro, meta (array of strings).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$args    = wp_parse_args( $args ?? array(), array( 'eyebrow' => '', 'title' => '', 'intro' => '', 'meta' => array() ) );
$title   = $args['title'] ?: ( is_singular() ? get_the_title() : '' );
$intro   = $args['intro'] ?: ( is_singular() && function_exists( 'get_field' ) ? get_field( 'intro' ) : '' );
?>
<section class="page-head">
	<div class="container">
		<?php echo capturehaus_breadcrumbs(); // phpcs:ignore ?>
		<?php if ( $args['eyebrow'] ) : ?><p class="eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></p><?php endif; ?>
		<h1 class="page-head__title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $intro ) : ?><p class="page-head__lede"><?php echo esc_html( $intro ); ?></p><?php endif; ?>
		<?php if ( $args['meta'] ) : ?>
			<ul class="page-head__meta">
				<?php foreach ( array_filter( $args['meta'] ) as $item ) : ?><li><?php echo esc_html( $item ); ?></li><?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
