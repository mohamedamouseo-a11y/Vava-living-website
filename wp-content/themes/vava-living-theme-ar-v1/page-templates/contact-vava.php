<?php
/**
 * Template Name: VAVA — Contact (AR / EN)
 * Template Post Type: page
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

$page_id = get_queried_object_id();
$lang    = vava_current_language();
$text    = vava_contact_text_data( $page_id, $lang );
$shared  = vava_contact_shared_data( $page_id );
$hero    = (array) ( $text['hero'] ?? array() );
$form    = (array) ( $text['form'] ?? array() );
$guide   = (array) ( $text['guide'] ?? array() );
$fields  = (array) ( $form['field_texts'] ?? array() );
$status      = isset( $_GET['vava_contact'] ) ? sanitize_key( wp_unslash( $_GET['vava_contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$guide_cards = vava_contact_guide_cards_for_display( $text, $shared );
$guide_map   = array();
foreach ( $guide_cards as $guide_card ) {
	foreach ( (array) $guide_card['field_ids'] as $guide_field_id ) {
		$guide_map[ $guide_field_id ][] = 'contact-guide-' . sanitize_html_class( (string) $guide_card['id'] );
	}
}

$GLOBALS['vava_page_data_name']        = 'en' === $lang ? 'contact-en.html' : 'contact.html';
$GLOBALS['vava_active_nav']            = 'contact';
$GLOBALS['vava_internal_body_classes'] = array( 'contact-page', 'vava-contact-page' );
get_header( 'page' );
?>
<main>
	<span class="blob sage"></span><span class="blob cream"></span><span class="leaf-line vava-inline-contact-1"></span>
	<section class="section vava-contact-hero" id="contact-hero">
		<div class="container hero-grid">
			<div class="hero-copy"><div class="eyebrow"><?php echo esc_html( (string) ( $hero['eyebrow'] ?? '' ) ); ?></div><h1><?php echo esc_html( (string) ( $hero['title'] ?? '' ) ); ?></h1><p><?php echo esc_html( (string) ( $hero['intro'] ?? '' ) ); ?></p><p class="small"><?php echo esc_html( (string) ( $hero['note'] ?? '' ) ); ?></p></div>
			<div class="visual-card vava-contact-hero-visual" style="background-image:url('<?php echo esc_url( vava_contact_image_url( (int) $shared['hero_image_id'] ) ); ?>')"></div>
		</div>
	</section>

	<section class="section contact-form-section vava-contact-form-section" id="contact-form">
		<div class="container contact-form-grid">
			<div class="text-panel contact-form-card">
				<h2><?php echo esc_html( (string) ( $form['title'] ?? '' ) ); ?></h2>
				<?php if ( 'success' === $status ) : ?><div class="vava-contact-status is-success" role="status"><?php echo esc_html( (string) ( $form['success'] ?? '' ) ); ?></div><?php elseif ( 'error' === $status ) : ?><div class="vava-contact-status is-error" role="alert"><?php echo esc_html( (string) ( $form['error'] ?? '' ) ); ?></div><?php endif; ?>
				<form class="form-grid vava-contact-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-contact-form data-email-invalid="<?php echo esc_attr( (string) ( $form['email_invalid'] ?? '' ) ); ?>" method="post">
					<input name="action" type="hidden" value="vava_contact_submit"/>
					<input name="vava_contact_page_id" type="hidden" value="<?php echo esc_attr( (string) $page_id ); ?>"/>
					<input name="vava_contact_lang" type="hidden" value="<?php echo esc_attr( $lang ); ?>"/>
					<input name="vava_contact_loaded_at" type="hidden" value="<?php echo esc_attr( (string) time() ); ?>"/>
					<input data-contact-hold-token name="vava_contact_hold_token" type="hidden" value=""/>
					<?php wp_nonce_field( 'vava_contact_form_' . $page_id, 'vava_contact_form_nonce' ); ?>
					<div class="vava-contact-honeypot" aria-hidden="true"><label>Website<input autocomplete="off" name="company_website" tabindex="-1" type="text"/></label></div>
					<?php foreach ( (array) $shared['field_schema'] as $field ) :
						if ( empty( $field['visible'] ) ) { continue; }
						$id          = (string) $field['id'];
						$type        = (string) $field['type'];
						$field_text  = (array) ( $fields[ $id ] ?? array() );
						$label       = (string) ( $field_text['label'] ?? '' );
						$placeholder = (string) ( $field_text['placeholder'] ?? '' );
						$required    = ! empty( $field['required'] );
						$class       = 'field ' . ( 'full' === $field['width'] ? 'full' : 'half' );
						$input_id    = 'contact_field_' . sanitize_html_class( $id );
					$describedby = isset( $guide_map[ $id ] ) ? implode( ' ', array_unique( $guide_map[ $id ] ) ) : '';
						?>
						<div class="<?php echo esc_attr( $class ); ?>" data-contact-field="<?php echo esc_attr( $id ); ?>">
							<label for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $label ); ?><?php if ( $required ) : ?><span aria-hidden="true"> *</span><?php endif; ?></label>
							<?php if ( 'textarea' === $type ) : ?>
								<textarea id="<?php echo esc_attr( $input_id ); ?>" maxlength="4000" name="vava_field[<?php echo esc_attr( $id ); ?>]" placeholder="<?php echo esc_attr( $placeholder ); ?>"<?php echo $required ? ' required' : ''; ?><?php echo $describedby ? ' aria-describedby="' . esc_attr( $describedby ) . '"' : ''; ?>></textarea>
							<?php elseif ( 'select' === $type ) : ?>
								<select id="<?php echo esc_attr( $input_id ); ?>" name="vava_field[<?php echo esc_attr( $id ); ?>]"<?php echo $required ? ' required' : ''; ?><?php echo $describedby ? ' aria-describedby="' . esc_attr( $describedby ) . '"' : ''; ?>><option value=""><?php echo esc_html( $placeholder ?: ( 'en' === $lang ? 'Choose an option' : 'اختر من القائمة' ) ); ?></option><?php foreach ( (array) ( $field_text['options'] ?? array() ) as $option ) : ?><option value="<?php echo esc_attr( (string) $option ); ?>"><?php echo esc_html( (string) $option ); ?></option><?php endforeach; ?></select>
							<?php else : ?>
								<input id="<?php echo esc_attr( $input_id ); ?>" maxlength="300" name="vava_field[<?php echo esc_attr( $id ); ?>]" placeholder="<?php echo esc_attr( $placeholder ); ?>" type="<?php echo esc_attr( in_array( $type, array( 'email', 'tel' ), true ) ? $type : 'text' ); ?>"<?php echo $required ? ' required' : ''; ?><?php echo 'email' === $type ? ' autocomplete="email"' : ''; ?><?php echo $describedby ? ' aria-describedby="' . esc_attr( $describedby ) . '"' : ''; ?>/>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
					<div class="field full vava-contact-action-area">
						<?php if ( ! empty( $shared['hold_enabled'] ) ) : ?>
							<button aria-live="polite" class="vava-contact-hold" data-contact-hold data-duration="<?php echo esc_attr( (string) $shared['hold_duration'] ); ?>" data-idle="<?php echo esc_attr( (string) ( $form['hold_idle'] ?? '' ) ); ?>" data-active="<?php echo esc_attr( (string) ( $form['hold_active'] ?? '' ) ); ?>" data-verified="<?php echo esc_attr( (string) ( $form['hold_verified'] ?? '' ) ); ?>" data-error="<?php echo esc_attr( (string) ( $form['hold_error'] ?? '' ) ); ?>" type="button"><span class="vava-contact-hold-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="10" rx="3"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span><span class="vava-contact-hold-copy"><strong data-contact-hold-label><?php echo esc_html( (string) ( $form['hold_idle'] ?? '' ) ); ?></strong><small><?php echo esc_html( sprintf( 'en' === $lang ? 'Hold for %d seconds' : 'استمر في الضغط لمدة %d ثوانٍ', (int) $shared['hold_duration'] ) ); ?></small></span><span class="vava-contact-hold-percent" data-contact-hold-percent>0%</span><span class="vava-contact-hold-progress" aria-hidden="true"></span></button>
						<?php endif; ?>
						<button class="btn coral vava-contact-submit<?php echo ! empty( $shared['hold_enabled'] ) ? ' is-locked' : ''; ?>" data-contact-submit type="submit"<?php echo ! empty( $shared['hold_enabled'] ) ? ' hidden disabled' : ''; ?>><?php echo esc_html( (string) ( $form['submit_label'] ?? '' ) ); ?></button>
					</div>
				</form>
				<div class="contact-social-row"><div class="eyebrow"><?php echo esc_html( (string) ( $form['social_eyebrow'] ?? '' ) ); ?></div><div class="social vava-contact-social-links"><?php vava_contact_render_social_icons( true ); ?></div></div>
			</div>
			<aside class="message-guide"><div class="eyebrow"><?php echo esc_html( (string) ( $guide['eyebrow'] ?? '' ) ); ?></div><h3><?php echo esc_html( (string) ( $guide['title'] ?? '' ) ); ?></h3><p><?php echo esc_html( (string) ( $guide['intro'] ?? '' ) ); ?></p><div class="guide-list"><?php foreach ( $guide_cards as $card ) : ?><div class="guide-item" id="contact-guide-<?php echo esc_attr( sanitize_html_class( (string) $card['id'] ) ); ?>" data-guide-card data-guide-fields="<?php echo esc_attr( implode( ' ', (array) $card['field_ids'] ) ); ?>"><span><?php echo esc_html( (string) $card['title'] ); ?></span><p><?php echo esc_html( (string) $card['body'] ); ?></p></div><?php endforeach; ?></div></aside>
		</div>
	</section>
</main>
<?php get_footer( 'page' ); ?>
