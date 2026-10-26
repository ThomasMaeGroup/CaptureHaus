<?php
/**
 * Blog index, archives and search results.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$title = is_home() ? get_the_title( (int) get_option( 'page_for_posts' ) ) : wp_strip_all_tags( get_the_archive_title() );
if ( is_search() ) {
	/* translators: %s: search term */
	$title = sprintf( __( 'Search: %s', 'capturehaus' ), get_search_query() );
}
get_template_part( 'template-parts/page-head', null, array( 'title' => $title ?: __( 'Blog', 'capturehaus' ) ) );
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
			<p class="section-lede"><?php esc_html_e( 'Nothing here yet. Check back soon.', 'capturehaus' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
