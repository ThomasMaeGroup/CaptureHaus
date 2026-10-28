<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$phone = capturehaus_option( 'phone' );
$small = capturehaus_option( 'footer_small' );
$legal = capturehaus_option( 'footer_legal' );

// "Areas we cover": links to the published area pages, then the towns from Site settings as plain text.
$areas_hub  = get_page_by_path( 'areas' );
$area_pages = $areas_hub && 'publish' === $areas_hub->post_status ? get_pages( array( 'parent' => $areas_hub->ID, 'sort_column' => 'menu_order,post_title' ) ) : array();
$towns      = capturehaus_lines( capturehaus_option( 'footer_towns' ) );
$area_count = count( $area_pages ) + count( $towns );

// The footer menu as list items (the helper returns bare links).
$menu = preg_replace( '#(<a\b.*?</a>)#s', '<li>$1</li>', capturehaus_menu_links( 'footer' ) );

if ( capturehaus_show_quote() ) {
	get_template_part( 'template-parts/quote' );
}
?>
	</main>

	<footer class="site-footer">
		<div class="container">
			<div class="site-footer__grid">
				<div class="site-footer__brand">
					<a class="brand brand--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> home">
						<img class="brand__logo" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/logo/capturehaus-powered-white.svg' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>, powered by Bread &amp; Butter Studio" width="843" height="156">
					</a>
				</div>

				<?php if ( $menu ) : ?>
					<nav class="site-footer__col" aria-label="<?php esc_attr_e( 'Footer', 'capturehaus' ); ?>">
						<p class="site-footer__label"><?php esc_html_e( 'Explore', 'capturehaus' ); ?></p>
						<ul class="site-footer__list site-footer__list--explore" style="--rows: <?php echo (int) ceil( substr_count( $menu, '<li>' ) / 2 ); ?>"><?php echo $menu; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?></ul>
					</nav>
				<?php endif; ?>

				<?php if ( $area_count ) : ?>
					<div class="site-footer__col">
						<p class="site-footer__label"><?php esc_html_e( 'Areas we cover', 'capturehaus' ); ?></p>
						<ul class="site-footer__list site-footer__list--areas" style="--rows: <?php echo (int) ceil( $area_count / 2 ); ?>">
							<?php foreach ( $area_pages as $page ) : ?>
								<li><a href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php echo esc_html( get_the_title( $page ) ); ?></a></li>
							<?php endforeach; ?>
							<?php foreach ( $towns as $town ) : ?>
								<li><?php echo esc_html( $town ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<div class="site-footer__contact">
					<p class="site-footer__label"><?php esc_html_e( 'Get in touch', 'capturehaus' ); ?></p>
					<?php if ( $phone ) : ?>
						<a class="site-footer__phone" href="<?php echo esc_url( capturehaus_tel( $phone ) ); ?>"><?php echo capturehaus_phone_icon(); // phpcs:ignore ?><?php echo esc_html( $phone ); ?></a>
					<?php endif; ?>
					<?php echo capturehaus_social_icons(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?>
				</div>
			</div>

			<?php if ( $small || $legal ) : ?>
				<div class="site-footer__bottom">
					<?php if ( $small ) : ?>
						<p class="site-footer__small"><?php echo esc_html( str_replace( '{year}', wp_date( 'Y' ), $small ) ); ?></p>
					<?php endif; ?>
					<?php if ( $legal ) : ?>
						<p class="site-footer__legal"><?php echo esc_html( $legal ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</footer>

	<?php if ( capturehaus_show_quote() ) : ?>
		<a class="sticky-cta" href="#contact" data-sticky-cta><?php esc_html_e( 'Get a quote', 'capturehaus' ); ?> <span aria-hidden="true">→</span></a>
	<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
