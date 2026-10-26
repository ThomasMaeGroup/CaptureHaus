<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$phone = capturehaus_option( 'phone' );
$small = capturehaus_option( 'footer_small' );
$legal = capturehaus_option( 'footer_legal' );

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
			<?php if ( $phone ) : ?>
				<a class="site-footer__phone" href="<?php echo esc_url( capturehaus_tel( $phone ) ); ?>"><?php echo capturehaus_phone_icon(); // phpcs:ignore ?><?php echo esc_html( $phone ); ?></a>
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
