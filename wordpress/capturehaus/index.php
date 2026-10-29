<?php
/**
 * Blog index, category archives and search results. On the blog's first page
 * the newest post is shown as a wide feature card.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$blog_id    = (int) get_option( 'page_for_posts' );
$blog_title = $blog_id ? get_the_title( $blog_id ) : __( 'Blog', 'capturehaus' );
$head       = array( 'title' => $blog_title );

if ( is_home() ) {
	$head['intro'] = $blog_id && function_exists( 'get_field' ) ? get_field( 'intro', $blog_id ) : '';
} elseif ( is_category() ) {
	$head = array( 'eyebrow' => $blog_title, 'title' => single_cat_title( '', false ), 'intro' => wp_strip_all_tags( category_description() ) );
} elseif ( is_search() ) {
	/* translators: %s: search term */
	$head['title'] = sprintf( __( 'Search: %s', 'capturehaus' ), get_search_query() );
} else {
	$head = array( 'eyebrow' => $blog_title, 'title' => wp_strip_all_tags( get_the_archive_title() ) );
}
get_template_part( 'template-parts/page-head', null, $head );

// Category filter: only on the blog and its category archives, only categories with posts.
$categories = ( is_home() || is_category() ) ? get_categories( array( 'hide_empty' => true, 'exclude' => array( (int) get_option( 'default_category' ) ) ) ) : array();
$featured   = is_home() && ! is_paged();
?>
<section class="section blog">
	<div class="container">
		<?php if ( count( $categories ) > 1 ) : ?>
			<nav class="chips" aria-label="<?php esc_attr_e( 'Topics', 'capturehaus' ); ?>">
				<a class="chip"<?php echo is_home() ? ' aria-current="page"' : ''; ?> href="<?php echo esc_url( $blog_id ? get_permalink( $blog_id ) : home_url( '/' ) ); ?>"><?php esc_html_e( 'All', 'capturehaus' ); ?></a>
				<?php foreach ( $categories as $cat ) : ?>
					<a class="chip"<?php echo is_category( $cat->term_id ) ? ' aria-current="page"' : ''; ?> href="<?php echo esc_url( get_category_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="card-grid">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/card', null, array( 'feature' => $featured ) );
					$featured = false;
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
