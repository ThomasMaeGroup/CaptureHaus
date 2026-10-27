<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$s = $args['section'];
capturehaus_faq_schema_add( $s['items'] );
?>
<section class="section faqs"<?php echo capturehaus_anchor( $s ); // phpcs:ignore ?>>
	<div class="container faqs__inner">
		<header class="faqs__head">
			<?php if ( $s['eyebrow'] ) : ?><p class="eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p><?php endif; ?>
			<?php if ( $s['title'] ) : ?><h2 class="section-title"><?php echo capturehaus_text( $s['title'] ); // phpcs:ignore ?></h2><?php endif; ?>
			<?php if ( $s['lede'] ) : ?><p class="section-lede"><?php echo esc_html( $s['lede'] ); ?></p><?php endif; ?>
			<?php echo capturehaus_button( $s['button'] ); // phpcs:ignore ?>
		</header>

		<div class="faq-list">
			<?php foreach ( (array) $s['items'] as $i => $faq ) : ?>
				<details class="faq"<?php echo 0 === $i ? ' open' : ''; ?>>
					<summary><?php echo esc_html( $faq['question'] ); ?></summary>
					<div class="faq__a"><?php echo wp_kses_post( wpautop( $faq['answer'] ) ); ?></div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
