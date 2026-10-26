<?php
/**
 * Pages (including the homepage): optional editor content, then the ACF page sections.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

while ( have_posts() ) :
	the_post();

	if ( ! capturehaus_has_hero() ) {
		get_template_part( 'template-parts/page-head' );
	}

	if ( '' !== trim( get_the_content() ) ) : ?>
		<section class="section section--prose">
			<div class="container prose"><?php the_content(); ?></div>
		</section>
	<?php endif;

	capturehaus_render_sections();
endwhile;

get_footer();
