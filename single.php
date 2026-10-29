<?php
/**
 * Blog post: title band, lead image, article with an "In this article" list (3+ H2s),
 * then related pages and more posts. The quote form follows from the footer.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

while ( have_posts() ) :
	the_post();
	$category = capturehaus_post_category();
	$updated  = get_the_modified_time( 'U' ) - get_the_time( 'U' ) > DAY_IN_SECONDS;
	$date     = $updated
		/* translators: %s: date */
		? sprintf( __( 'Updated %s', 'capturehaus' ), get_the_modified_date() )
		: get_the_date();

	get_template_part( 'template-parts/page-head', null, array(
		'eyebrow' => $category ? $category->name : '',
		'intro'   => ( function_exists( 'get_field' ) ? get_field( 'intro' ) : '' ) ?: ( has_excerpt() ? get_the_excerpt() : '' ),
		/* translators: %d: minutes */
		'meta'    => array( $date, sprintf( _n( '%d min read', '%d min read', capturehaus_reading_time(), 'capturehaus' ), capturehaus_reading_time() ) ),
	) );

	list( $content, $contents ) = capturehaus_heading_anchors( apply_filters( 'the_content', get_the_content() ) );
	$contents = count( $contents ) >= 3 ? $contents : array();
	?>
	<article class="section article<?php echo $contents ? ' article--has-contents' : ''; ?>">
		<div class="container">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="article__lead"><?php the_post_thumbnail( 'full', array( 'sizes' => '(min-width: 1280px) 1200px, 100vw', 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?></figure>
			<?php endif; ?>
			<div class="article__grid">
				<?php if ( $contents ) : ?>
					<nav class="article__contents" aria-labelledby="article-contents">
						<p class="article__contents-title" id="article-contents"><?php esc_html_e( 'In this article', 'capturehaus' ); ?></p>
						<ol>
							<?php foreach ( $contents as $item ) : ?>
								<li><a href="#<?php echo esc_attr( $item['id'] ); ?>"><?php echo esc_html( $item['text'] ); ?></a></li>
							<?php endforeach; ?>
						</ol>
					</nav>
				<?php endif; ?>
				<div class="prose article__body"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output. ?></div>
			</div>
		</div>
	</article>

	<?php
	$related = array_filter( array_map( 'intval', (array) ( function_exists( 'get_field' ) ? get_field( 'related_pages', false, false ) : array() ) ) );
	$cards   = array(
		array( __( 'Related services and areas', 'capturehaus' ), $related ? get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'post__in' => $related, 'orderby' => 'post__in', 'fields' => 'ids' ) ) : array() ),
		array( __( 'Keep reading', 'capturehaus' ), capturehaus_related_posts() ),
	);
	foreach ( $cards as list( $title, $ids ) ) :
		if ( ! $ids ) {
			continue;
		}
		?>
		<section class="section links article-more">
			<div class="container">
				<header class="section-head"><h2 class="section-title section-title--sm"><?php echo esc_html( $title ); ?></h2></header>
				<div class="card-grid">
					<?php
					foreach ( $ids as $id ) {
						$GLOBALS['post'] = get_post( $id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
						setup_postdata( $GLOBALS['post'] );
						get_template_part( 'template-parts/card' );
					}
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
		<?php
	endforeach;
endwhile;

get_footer();
