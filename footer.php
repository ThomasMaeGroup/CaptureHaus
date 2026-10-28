<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$phone = capturehaus_option( 'phone' );
$small = capturehaus_option( 'footer_small' );
$legal = capturehaus_option( 'footer_legal' );

// "Areas we cover": links to the published area pages, then the towns from Site settings as plain text.
$areas_hub   = get_page_by_path( 'areas' );
$area_pages  = $areas_hub && 'publish' === $areas_hub->post_status ? get_pages( array( 'parent' => $areas_hub->ID, 'sort_column' => 'menu_order,post_title' ) ) : array();
$towns       = capturehaus_lines( capturehaus_option( 'footer_towns' ) );

if ( capturehaus_show_quote() ) {
	get_template_part( 'template-parts/quote' );
}
?>
	</main>

	<footer class="site-footer">
		<div class="container site-footer__inner">
			<a class="brand brand--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> home">
				<img class="brand__logo" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/logo/capturehaus-powered-white.svg' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>, powered by Bread &amp; Butter Studio" width="843" height="156">
			</a>
			<nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Footer', 'capturehaus' ); ?>">
				<?php echo capturehaus_menu_links( 'footer' ); // phpcs:ignore ?>
			</nav>
			<div class="site-footer__contact">
				<?php if ( $phone ) : ?>
					<a class="site-footer__phone" href="<?php echo esc_url( capturehaus_tel( $phone ) ); ?>"><?php echo capturehaus_phone_icon(); // phpcs:ignore ?><?php echo esc_html( $phone ); ?></a>
				<?php endif; ?>
				<?php echo capturehaus_social_icons(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?>
			</div>
			<?php if ( $area_pages || $towns ) : ?>
				<p class="site-footer__areas">
					<?php if ( $areas_hub && 'publish' === $areas_hub->post_status ) : ?>
						<a class="site-footer__areas-title" href="<?php echo esc_url( get_permalink( $areas_hub ) ); ?>"><?php esc_html_e( 'Areas we cover', 'capturehaus' ); ?></a>
					<?php else : ?>
						<span class="site-footer__areas-title"><?php esc_html_e( 'Areas we cover', 'capturehaus' ); ?></span>
					<?php endif; ?>
					<?php foreach ( $area_pages as $page ) : ?>
						<a href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php echo esc_html( get_the_title( $page ) ); ?></a>
					<?php endforeach; ?>
					<?php foreach ( $towns as $town ) : ?>
						<span><?php echo esc_html( $town ); ?></span>
					<?php endforeach; ?>
				</p>
			<?php endif; ?>
			<?php if ( $small ) : ?>
				<p class="site-footer__small"><?php echo esc_html( str_replace( '{year}', wp_date( 'Y' ), $small ) ); ?></p>
			<?php endif; ?>
			<?php if ( $legal ) : ?>
				<p class="site-footer__legal"><?php echo esc_html( $legal ); ?></p>
			<?php endif; ?>
		</div>
	</footer>

	<?php if ( capturehaus_show_quote() ) : ?>
		<a class="sticky-cta" href="#contact" data-sticky-cta><?php esc_html_e( 'Get a quote', 'capturehaus' ); ?> <span aria-hidden="true">→</span></a>
	<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
