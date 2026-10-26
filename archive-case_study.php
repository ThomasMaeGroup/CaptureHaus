<?php
/**
 * Case studies listing (/case-studies/).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
get_template_part( 'template-parts/page-head', null, array(
	'eyebrow' => __( 'Our work', 'capturehaus' ),
	'title'   => __( 'Case studies', 'capturehaus' ),
	'intro'   => __( 'Homes, venues and developments we\'ve captured: tours, floor plans, photography and aerials.', 'capturehaus' ),
) );
?>
<section class="section">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="card-grid">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/card' );
				}
				?>
			</div>
			<?php the_posts_pagination( array( 'class' => 'pager', 'prev_text' => '←', 'next_text' => '→' ) ); ?>
		<?php else : ?>
			<p class="section-lede"><?php esc_html_e( 'Case studies are coming soon.', 'capturehaus' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
