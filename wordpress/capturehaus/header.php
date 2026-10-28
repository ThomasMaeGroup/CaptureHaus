<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$phone = capturehaus_option( 'phone' );
$cta   = capturehaus_option( 'header_cta' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

	<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'capturehaus' ); ?></a>

	<header class="site-header" data-header>
		<div class="container site-header__inner">
			<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> home">
				<img class="brand__logo brand__logo--dark" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/logo/capturehaus-white.svg' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="843" height="156">
				<img class="brand__logo brand__logo--light" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/logo/capturehaus-black.svg' ) ); ?>" alt="" aria-hidden="true" width="843" height="156">
			</a>

			<nav class="site-nav" id="site-nav" aria-label="<?php esc_attr_e( 'Main', 'capturehaus' ); ?>">
				<?php echo capturehaus_menu_links( 'primary', true ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?>
				<?php echo capturehaus_button( $cta, 'btn btn--signature site-nav__cta' ); // phpcs:ignore ?>
			</nav>

			<?php if ( $phone ) : ?>
				<a class="btn site-header__call" href="<?php echo esc_url( capturehaus_tel( $phone ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Call us on %s', 'capturehaus' ), $phone ) ); ?>">
					<?php echo capturehaus_phone_icon(); // phpcs:ignore ?><span class="site-header__call-text"><?php echo esc_html( sprintf( __( 'Call us: %s', 'capturehaus' ), $phone ) ); ?></span>
				</a>
			<?php endif; ?>

			<button class="nav-toggle" aria-expanded="false" aria-controls="site-nav" data-nav-toggle>
				<span class="nav-toggle__bars" aria-hidden="true"></span>
				<span class="sr-only"><?php esc_html_e( 'Menu', 'capturehaus' ); ?></span>
			</button>
		</div>
	</header>

	<main id="main">
