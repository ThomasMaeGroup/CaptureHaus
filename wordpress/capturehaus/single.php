<?php
/**
 * Blog post.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-head', null, array( 'meta' => array( get_the_date() ) ) );
	?>
	<article class="section section--prose">
		<div class="container prose">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="prose__lead"><?php the_post_thumbnail( 'large', array( 'sizes' => '(min-width: 800px) 760px, 100vw' ) ); ?></figure>
			<?php endif; ?>
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
