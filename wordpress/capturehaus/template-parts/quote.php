<?php
/**
 * Quote form, shown at the bottom of every page (unless hidden on that page).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$portal = capturehaus_option( 'hubspot_portal_id' );
$form   = capturehaus_option( 'hubspot_form_id' );
$region = capturehaus_option( 'hubspot_region' ) ?: 'eu1';
?>
<section class="section contact" id="contact">
	<div class="container contact__inner">
		<div class="contact__intro">
			<?php if ( $v = capturehaus_option( 'quote_eyebrow' ) ) : ?><p class="eyebrow eyebrow--light"><?php echo esc_html( $v ); ?></p><?php endif; ?>
			<?php if ( $v = capturehaus_option( 'quote_title' ) ) : ?><h2 class="section-title"><?php echo esc_html( $v ); ?></h2><?php endif; ?>
			<?php if ( $v = capturehaus_option( 'quote_lede' ) ) : ?><p class="section-lede"><?php echo esc_html( $v ); ?></p><?php endif; ?>
			<?php echo capturehaus_ticks( capturehaus_option( 'quote_ticks' ), true ); // phpcs:ignore ?>
			<?php echo capturehaus_image( capturehaus_option( 'quote_image' ), 'contact__img', '(min-width: 960px) 40vw, 0px' ); // phpcs:ignore ?>
		</div>

		<?php /* HubSpot renders the form in an iframe: field styling is set in HubSpot's form editor. */ ?>
		<div class="form form--hubspot" data-form>
			<?php if ( $portal && $form ) : ?>
				<div class="hs-form-frame" data-region="<?php echo esc_attr( $region ); ?>" data-form-id="<?php echo esc_attr( $form ); ?>" data-portal-id="<?php echo esc_attr( $portal ); ?>"></div>
			<?php endif; ?>
		</div>
	</div>
</section>
