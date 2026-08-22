<?php
/**
 * Template Name: VAVA — Digital Product Checkout (AR / EN)
 * Template Post Type: page
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

$lang    = vava_current_language();
$is_en   = 'en' === $lang;
$uid     = isset( $_GET['product'] ) ? sanitize_key( wp_unslash( $_GET['product'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$product = function_exists( 'vava_digital_product_data' ) ? vava_digital_product_data( $uid, $lang ) : array();
$booking_page_id = function_exists( 'vava_booking_page_id' ) ? vava_booking_page_id() : 0;
$shared  = function_exists( 'vava_booking_shared_data' ) ? vava_booking_shared_data( $booking_page_id ) : array();
$bank    = (array) ( $shared['bank_transfer'] ?? array() );
$bank_ready = ! empty( $shared['payment_methods']['bank'] ) && function_exists( 'vava_booking_bank_is_ready' ) && vava_booking_bank_is_ready( $shared );
$countries = function_exists( 'vava_booking_country_calling_codes' ) ? vava_booking_country_calling_codes() : array();
$default_country_iso = 'SA';
$whatsapp_country_label = $is_en ? 'Country and calling code' : 'الدولة ومفتاح الاتصال';
$whatsapp_number_label = $is_en ? 'Phone number' : 'رقم الهاتف';
$whatsapp_number_placeholder = $is_en ? 'Enter number without country code' : 'اكتب الرقم بدون كود الدولة';
$whatsapp_help = $is_en ? 'The country code is added automatically when your WhatsApp number is saved.' : 'يُضاف مفتاح الدولة تلقائيًا عند حفظ رقم WhatsApp.';
$previous_label = $is_en ? 'Have you tried VAVA before?' : 'هل سبق لك تجربة VAVA؟';
$previous_yes = $is_en ? 'Yes' : 'نعم';
$previous_no = $is_en ? 'No' : 'لا';
$cover_url = (string) ( $product['cover_url'] ?? '' );
$price = (string) ( $product['price'] ?? '' );
$currency = $is_en ? 'SAR' : 'ر.س';
$back_url = function_exists( 'vava_selections_page_id' ) && vava_selections_page_id() ? vava_localized_page_url( vava_selections_page_id(), $lang ) : home_url( '/' );
$account_url = function_exists( 'vava_customer_account_url' ) ? vava_customer_account_url( $lang, array( 'view' => 'products' ) ) : home_url( '/' );
$terms_url = function_exists( 'vava_legal_page_url' ) ? vava_legal_page_url( 'terms', $lang ) : home_url( '/terms-and-conditions/' );
$privacy_url = function_exists( 'vava_legal_page_url' ) ? vava_legal_page_url( 'privacy', $lang ) : home_url( '/privacy-policy/' );

$GLOBALS['vava_internal_body_classes'] = array( 'vava-digital-checkout-page' );
$GLOBALS['vava_page_data_name'] = $is_en ? 'digital-checkout-en.html' : 'digital-checkout-ar.html';

get_header( 'page' );
?>
<main class="vava-digital-checkout" dir="<?php echo esc_attr( $is_en ? 'ltr' : 'rtl' ); ?>" data-vava-digital-checkout data-current-step="1">
	<section class="vava-digital-checkout-hero">
		<div class="container">
			<a class="vava-digital-checkout-back" href="<?php echo esc_url( $back_url ); ?>"><span aria-hidden="true">→</span><?php echo esc_html( $is_en ? 'Back to VAVA Selections' : 'العودة إلى مختارات VAVA' ); ?></a>
			<div class="vava-digital-checkout-heading">
				<span><?php echo esc_html( $is_en ? 'Protected digital product' : 'منتج رقمي محمي' ); ?></span>
				<h1><?php echo esc_html( $is_en ? 'Complete your purchase' : 'إتمام شراء المنتج الرقمي' ); ?></h1>
				<p><?php echo esc_html( $is_en ? 'Complete the transfer details. After manual approval, the product will appear in My Digital Products.' : 'استكمل بيانات التحويل، وبعد المراجعة والاعتماد اليدوي سيظهر المنتج داخل «منتجاتي الرقمية».' ); ?></p>
			</div>
		</div>
	</section>

	<section class="vava-digital-checkout-shell">
		<div class="container">
			<?php if ( ! $product ) : ?>
				<div class="vava-digital-checkout-message is-error"><h2><?php echo esc_html( $is_en ? 'Product unavailable' : 'المنتج غير متاح' ); ?></h2><p><?php echo esc_html( $is_en ? 'Return to VAVA Selections and choose another product.' : 'ارجع إلى مختارات VAVA واختر منتجًا آخر.' ); ?></p></div>
			<?php elseif ( ! $bank_ready ) : ?>
				<div class="vava-digital-checkout-message is-error"><h2><?php echo esc_html( $is_en ? 'Payment is temporarily unavailable' : 'الدفع غير متاح مؤقتًا' ); ?></h2><p><?php echo esc_html( $is_en ? 'The bank transfer details are not complete yet.' : 'بيانات التحويل البنكي غير مكتملة في لوحة التحكم.' ); ?></p></div>
			<?php else : ?>
			<div class="vava-digital-checkout-layout">
				<section class="vava-digital-checkout-main">
					<ol class="vava-digital-checkout-steps" aria-label="<?php echo esc_attr( $is_en ? 'Checkout steps' : 'خطوات الشراء' ); ?>">
						<li class="is-active" data-checkout-step-indicator="1"><b>1</b><span><?php echo esc_html( $is_en ? 'Your details' : 'بيانات العميل' ); ?></span></li>
						<li data-checkout-step-indicator="2"><b>2</b><span><?php echo esc_html( $is_en ? 'Bank transfer' : 'التحويل البنكي' ); ?></span></li>
						<li data-checkout-step-indicator="3"><b>3</b><span><?php echo esc_html( $is_en ? 'Review' : 'المراجعة والتأكيد' ); ?></span></li>
					</ol>

					<form class="vava-digital-checkout-form" enctype="multipart/form-data" novalidate>
						<input type="hidden" name="action" value="vava_digital_checkout_submit"/>
						<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'vava_digital_checkout' ) ); ?>"/>
						<input type="hidden" name="lang" value="<?php echo esc_attr( $lang ); ?>"/>
						<input type="hidden" name="product" value="<?php echo esc_attr( $uid ); ?>"/>

						<section class="vava-digital-checkout-stage is-active" data-checkout-stage="1">
							<header><small><?php echo esc_html( $is_en ? 'Step one' : 'الخطوة الأولى' ); ?></small><h2><?php echo esc_html( $is_en ? 'Your contact details' : 'بيانات العميل' ); ?></h2><p><?php echo esc_html( $is_en ? 'These details are used to create and link your VAVA customer account.' : 'تُستخدم هذه البيانات لإنشاء حساب العميل وربط المنتج به.' ); ?></p></header>
							<div class="vava-digital-fields-grid">
								<label class="is-full"><span><?php echo esc_html( $is_en ? 'Full name' : 'الاسم الكامل' ); ?> *</span><input type="text" name="customer_name" required autocomplete="name"/></label>
								<label class="is-full"><span><?php echo esc_html( $is_en ? 'Email' : 'البريد الإلكتروني' ); ?> *</span><input type="email" name="customer_email" required autocomplete="email"/></label>
								<fieldset class="vava-digital-whatsapp-group is-full" data-whatsapp-field>
									<legend>WhatsApp *</legend>
									<input data-whatsapp-combined name="whatsapp" type="hidden" value=""/>
									<div class="vava-booking-whatsapp-controls">
										<div class="vava-booking-country-field">
											<span class="vava-booking-field-label"><?php echo esc_html( $whatsapp_country_label ); ?> *</span>
											<div class="vava-booking-country-picker" data-country-picker>
												<select class="vava-booking-country-native" data-whatsapp-country name="whatsapp_country" autocomplete="country" tabindex="-1" aria-hidden="true" required>
													<?php foreach ( $countries as $country ) : $country_name = $is_en ? (string) $country['en'] : (string) $country['ar']; ?>
														<option value="<?php echo esc_attr( (string) $country['iso'] ); ?>" data-dial="<?php echo esc_attr( (string) $country['dial'] ); ?>"<?php selected( $default_country_iso, (string) $country['iso'] ); ?>><?php echo esc_html( $country_name . ' (' . (string) $country['dial'] . ')' ); ?></option>
													<?php endforeach; ?>
												</select>
												<button class="vava-booking-country-trigger" data-country-trigger type="button" aria-haspopup="listbox" aria-expanded="false">
													<img data-country-selected-flag src="<?php echo esc_url( get_theme_file_uri( 'assets/images/country-flags/' . strtolower( $default_country_iso ) . '.png' ) ); ?>" alt=""/>
													<span data-country-selected-name><?php foreach ( $countries as $country ) { if ( $default_country_iso === (string) $country['iso'] ) { echo esc_html( $is_en ? (string) $country['en'] : (string) $country['ar'] ); break; } } ?></span>
													<b data-country-selected-dial dir="ltr"><?php echo esc_html( function_exists( 'vava_booking_country_dial_code' ) ? vava_booking_country_dial_code( $default_country_iso ) : '+966' ); ?></b>
													<i aria-hidden="true">⌄</i>
												</button>
												<div class="vava-booking-country-menu" data-country-menu hidden>
													<label class="vava-booking-country-search"><span class="screen-reader-text"><?php echo esc_html( $is_en ? 'Search countries' : 'البحث في الدول' ); ?></span><input data-country-search type="search" autocomplete="off" placeholder="<?php echo esc_attr( $is_en ? 'Search country or code' : 'ابحث باسم الدولة أو المفتاح' ); ?>"/></label>
													<div class="vava-booking-country-options" data-country-options role="listbox" aria-label="<?php echo esc_attr( $whatsapp_country_label ); ?>">
														<?php foreach ( $countries as $country ) : $country_name = $is_en ? (string) $country['en'] : (string) $country['ar']; $country_iso = strtolower( (string) $country['iso'] ); ?>
															<button type="button" role="option" data-country-option data-iso="<?php echo esc_attr( (string) $country['iso'] ); ?>" data-dial="<?php echo esc_attr( (string) $country['dial'] ); ?>" data-search="<?php echo esc_attr( strtolower( $country_name . ' ' . (string) $country['iso'] . ' ' . (string) $country['dial'] ) ); ?>" aria-selected="<?php echo $default_country_iso === (string) $country['iso'] ? 'true' : 'false'; ?>">
																<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/country-flags/' . $country_iso . '.png' ) ); ?>" alt=""/>
																<span><?php echo esc_html( $country_name ); ?></span>
																<b dir="ltr"><?php echo esc_html( (string) $country['dial'] ); ?></b>
															</button>
														<?php endforeach; ?>
													</div>
												</div>
											</div>
										</div>
										<label class="vava-booking-phone-field"><span><?php echo esc_html( $whatsapp_number_label ); ?> *</span><input data-whatsapp-local name="whatsapp_local" type="tel" inputmode="numeric" autocomplete="tel-national" maxlength="15" placeholder="<?php echo esc_attr( $whatsapp_number_placeholder ); ?>" required/></label>
									</div>
									<small class="vava-booking-whatsapp-help"><?php echo esc_html( $whatsapp_help ); ?></small>
								</fieldset>
								<fieldset class="vava-digital-choice-toggle is-full" data-vava-choice-toggle>
									<legend><?php echo esc_html( $previous_label ); ?></legend>
									<div role="radiogroup" aria-label="<?php echo esc_attr( $previous_label ); ?>">
										<label><input type="radio" name="previous" value="yes" required/><span><?php echo esc_html( $previous_yes ); ?></span></label>
										<label><input type="radio" name="previous" value="no" required/><span><?php echo esc_html( $previous_no ); ?></span></label>
									</div>
								</fieldset>
								<label class="is-full"><span><?php echo esc_html( $is_en ? 'Notes (optional)' : 'ملاحظات اختيارية' ); ?></span><textarea name="customer_notes" rows="4"></textarea></label>
							</div>
							<footer><button type="button" class="vava-digital-next" data-checkout-next="2"><?php echo esc_html( $is_en ? 'Continue to payment' : 'المتابعة إلى الدفع' ); ?><span aria-hidden="true">←</span></button></footer>
						</section>

						<section class="vava-digital-checkout-stage" data-checkout-stage="2" hidden>
							<header><small><?php echo esc_html( $is_en ? 'Step two' : 'الخطوة الثانية' ); ?></small><h2><?php echo esc_html( $is_en ? 'Bank transfer and receipt' : 'التحويل البنكي وإثبات الدفع' ); ?></h2><p><?php echo esc_html( $is_en ? 'Transfer the total, then enter the transaction details and upload the receipt.' : 'حوّل قيمة المنتج، ثم استكمل بيانات العملية وارفع إيصال التحويل.' ); ?></p></header>
							<div class="vava-digital-bank-card"><div><span><?php echo esc_html( $is_en ? 'Bank' : 'اسم البنك' ); ?></span><strong><?php echo esc_html( (string) ( $bank['bank_name'] ?? '' ) ); ?></strong></div><div><span><?php echo esc_html( $is_en ? 'Beneficiary' : 'اسم المستفيد' ); ?></span><strong><?php echo esc_html( (string) ( $bank['beneficiary_name'] ?? '' ) ); ?></strong></div><?php if ( ! empty( $bank['account_number'] ) ) : ?><div><span><?php echo esc_html( $is_en ? 'Account number' : 'رقم الحساب' ); ?></span><strong dir="ltr"><?php echo esc_html( (string) $bank['account_number'] ); ?></strong></div><?php endif; ?><div class="is-wide"><span>IBAN</span><strong dir="ltr"><?php echo esc_html( (string) ( $bank['iban'] ?? '' ) ); ?></strong></div></div>
							<div class="vava-digital-fields-grid">
								<label><span><?php echo esc_html( $is_en ? 'Sender name' : 'اسم المحوّل' ); ?> *</span><input type="text" name="bank_transfer_name" required/></label>
								<label><span><?php echo esc_html( $is_en ? 'Sending bank' : 'البنك المحوّل منه' ); ?> *</span><input type="text" name="bank_from_bank" required/></label>
								<label><span><?php echo esc_html( $is_en ? 'Sender account number' : 'رقم الحساب المحوّل منه' ); ?> *</span><input type="text" name="bank_from_account" required dir="ltr"/></label>
								<label><span><?php echo esc_html( $is_en ? 'Transaction reference' : 'رقم مرجع العملية' ); ?> *</span><input type="text" name="bank_reference" required dir="ltr"/></label>
								<label><span><?php echo esc_html( $is_en ? 'Transfer date' : 'تاريخ التحويل' ); ?> *</span><input type="date" name="bank_transfer_date" required max="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>"/></label>
								<label><span><?php echo esc_html( $is_en ? 'Transfer time' : 'وقت التحويل' ); ?> *</span><input type="time" name="bank_transfer_time" required/></label>
								<label><span><?php echo esc_html( $is_en ? 'Transferred amount' : 'المبلغ المحوّل' ); ?> *</span><input type="text" name="bank_amount" required value="<?php echo esc_attr( $price ); ?>" inputmode="decimal"/></label>
								<label><span><?php echo esc_html( $is_en ? 'Transfer notes' : 'ملاحظات التحويل' ); ?></span><input type="text" name="bank_notes"/></label>
								<label class="is-full vava-digital-receipt-field"><span><?php echo esc_html( $is_en ? 'Transfer receipt' : 'إيصال التحويل' ); ?> *</span><div class="vava-digital-receipt-dropzone" data-receipt-dropzone><input type="file" name="bank_receipt" accept="image/jpeg,image/png,image/webp,application/pdf" required data-receipt-input/><i aria-hidden="true">⇧</i><strong data-receipt-name><?php echo esc_html( $is_en ? 'Choose or drop the receipt here' : 'اختر الإيصال أو اسحبه إلى هنا' ); ?></strong><small><?php echo esc_html( $is_en ? 'JPG, PNG, WEBP or PDF — maximum 5 MB' : 'JPG أو PNG أو WEBP أو PDF — بحد أقصى 5 ميجابايت' ); ?></small></div><div class="vava-upload-progress vava-digital-receipt-progress" data-receipt-progress aria-live="polite"><div class="vava-upload-progress-head"><strong data-receipt-progress-label><?php echo esc_html( $is_en ? 'Ready to upload' : 'جاهز للرفع' ); ?></strong><span data-receipt-progress-percent>0%</span></div><div class="vava-upload-progress-track"><i data-receipt-progress-bar></i></div><small data-receipt-progress-meta></small></div></label>
							</div>
							<footer><button type="button" class="vava-digital-back" data-checkout-back="1"><span aria-hidden="true">→</span><?php echo esc_html( $is_en ? 'Back' : 'رجوع' ); ?></button><button type="button" class="vava-digital-next" data-checkout-next="3"><?php echo esc_html( $is_en ? 'Review order' : 'مراجعة الطلب' ); ?><span aria-hidden="true">←</span></button></footer>
						</section>

						<section class="vava-digital-checkout-stage" data-checkout-stage="3" hidden>
							<header><small><?php echo esc_html( $is_en ? 'Step three' : 'الخطوة الثالثة' ); ?></small><h2><?php echo esc_html( $is_en ? 'Review and submit' : 'مراجعة وتأكيد الطلب' ); ?></h2><p><?php echo esc_html( $is_en ? 'The product is activated manually after the VAVA team verifies the transfer.' : 'يتم تفعيل المنتج يدويًا بعد أن يتأكد فريق VAVA من التحويل.' ); ?></p></header>
							<div class="vava-digital-review-grid"><section><h3><?php echo esc_html( $is_en ? 'Customer' : 'بيانات العميل' ); ?></h3><dl><div><dt><?php echo esc_html( $is_en ? 'Name' : 'الاسم' ); ?></dt><dd data-review="customer_name">—</dd></div><div><dt><?php echo esc_html( $is_en ? 'Email' : 'البريد' ); ?></dt><dd data-review="customer_email">—</dd></div><div><dt>WhatsApp</dt><dd data-review="whatsapp">—</dd></div><div><dt><?php echo esc_html( $is_en ? 'Previous VAVA experience' : 'تجربة VAVA السابقة' ); ?></dt><dd data-review="previous">—</dd></div></dl></section><section><h3><?php echo esc_html( $is_en ? 'Payment' : 'بيانات التحويل' ); ?></h3><dl><div><dt><?php echo esc_html( $is_en ? 'Sender' : 'اسم المحوّل' ); ?></dt><dd data-review="bank_transfer_name">—</dd></div><div><dt><?php echo esc_html( $is_en ? 'Reference' : 'مرجع العملية' ); ?></dt><dd data-review="bank_reference">—</dd></div><div><dt><?php echo esc_html( $is_en ? 'Receipt' : 'الإيصال' ); ?></dt><dd data-review="receipt">—</dd></div></dl></section></div>
							<label class="vava-digital-terms"><input type="checkbox" name="terms" value="1" required/><span><?php if ( $is_en ) : ?>I confirm that the entered transfer details are correct and agree to the <a href="<?php echo esc_url( $terms_url ); ?>" target="_blank" rel="noopener">Terms &amp; Conditions</a> and <a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener">Privacy Policy</a>.<?php else : ?>أقر بصحة بيانات التحويل وأوافق على <a href="<?php echo esc_url( $terms_url ); ?>" target="_blank" rel="noopener">الشروط والأحكام</a> و<a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener">سياسة الخصوصية</a>.<?php endif; ?></span></label>
							<footer><button type="button" class="vava-digital-back" data-checkout-back="2"><span aria-hidden="true">→</span><?php echo esc_html( $is_en ? 'Back' : 'رجوع' ); ?></button><button type="submit" class="vava-digital-submit" data-submit-label="<?php echo esc_attr( $is_en ? 'Submit order for review' : 'إرسال الطلب للمراجعة' ); ?>" data-loading-label="<?php echo esc_attr( $is_en ? 'Submitting…' : 'جارٍ إرسال الطلب…' ); ?>"><?php echo esc_html( $is_en ? 'Submit order for review' : 'إرسال الطلب للمراجعة' ); ?><span aria-hidden="true">✓</span></button></footer>
						</section>
					</form>
					<div class="vava-digital-checkout-result" hidden aria-hidden="true" data-checkout-result><span aria-hidden="true">✓</span><h2 data-result-title><?php echo esc_html( $is_en ? 'Your order was received' : 'تم استلام طلبك بنجاح' ); ?></h2><p data-result-message><?php echo esc_html( $is_en ? 'Your transfer is under manual review. The product will appear in My Digital Products after approval.' : 'التحويل قيد المراجعة اليدوية، وسيظهر المنتج داخل «منتجاتي الرقمية» بعد اعتماد فريق VAVA.' ); ?></p><div><a href="<?php echo esc_url( $account_url ); ?>" data-result-account><?php echo esc_html( $is_en ? 'Open My Digital Products' : 'فتح منتجاتي الرقمية' ); ?></a><a href="<?php echo esc_url( $back_url ); ?>"><?php echo esc_html( $is_en ? 'Back to VAVA Selections' : 'العودة إلى مختارات VAVA' ); ?></a></div></div>
				</section>

				<aside class="vava-digital-checkout-summary">
					<div class="vava-digital-summary-cover"><?php if ( $cover_url ) : ?><img src="<?php echo esc_url( $cover_url ); ?>" alt="<?php echo esc_attr( (string) ( $product['title'] ?? '' ) ); ?>"/><?php endif; ?></div>
					<span><?php echo esc_html( (string) ( $product['category'] ?? ( $is_en ? 'Digital product' : 'منتج رقمي' ) ) ); ?></span>
					<h2><?php echo esc_html( (string) ( $product['title'] ?? '' ) ); ?></h2>
					<p><?php echo esc_html( (string) ( $product['card_description'] ?? $product['description'] ?? '' ) ); ?></p>
					<div class="vava-digital-summary-price"><span><?php echo esc_html( $is_en ? 'Total' : 'الإجمالي' ); ?></span><strong><?php echo esc_html( trim( $price . ' ' . $currency ) ); ?></strong></div>
					<ul><li><?php echo esc_html( $is_en ? 'Manual payment review' : 'مراجعة يدوية للتحويل' ); ?></li><li><?php echo esc_html( $is_en ? 'Protected online viewing' : 'مشاهدة محمية داخل الموقع' ); ?></li><li><?php echo esc_html( $is_en ? 'No direct PDF download link' : 'بدون رابط تحميل مباشر للـPDF' ); ?></li></ul>
				</aside>
			</div>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php get_footer( 'page' ); ?>
