<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
get_template_part( 'template-parts/page-head', null, array(
	'title' => __( 'Page not found', 'capturehaus' ),
	'intro' => __( 'That page has moved or no longer exists.', 'capturehaus' ),
) );
?>
<section class="section">
	<div class="container">
		<a class="btn btn--dark" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to the homepage', 'capturehaus' ); ?></a>
	</div>
</section>
<?php
get_footer();
