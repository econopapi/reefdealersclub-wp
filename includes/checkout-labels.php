<?php
/**
 * Checkout labels and copy adjustments.
 *
 * Tropicalizes WooCommerce checkout wording for Mexican customers.
 *
 * @package RDC Custom Astra
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customizes default address field labels.
 *
 * @param array<string, array<string, mixed>> $fields Default address fields.
 * @return array<string, array<string, mixed>>
 */
function rdc_custom_checkout_address_field_labels( $fields ) {
	if ( isset( $fields['city']['label'] ) ) {
		$fields['city']['label'] = 'Alcaldía/Municipio';
	}

	if ( isset( $fields['state']['label'] ) ) {
		$fields['state']['label'] = 'Estado';
	}

	return $fields;
}
add_filter( 'woocommerce_default_address_fields', 'rdc_custom_checkout_address_field_labels', 20, 1 );

/**
 * Adjusts WooCommerce checkout section titles.
 *
 * @param string $translated_text Translated text.
 * @param string $text Original text.
 * @param string $domain Text domain.
 * @return string
 */
function rdc_translate_checkout_woocommerce_texts( $translated_text, $text, $domain ) {
	if ( 'woocommerce' !== $domain ) {
		return $translated_text;
	}

	$checkout_titles = array(
		'Billing details',
		'Billing &amp; Shipping',
		'Detalles de facturación',
	);

	if ( in_array( $text, $checkout_titles, true ) || in_array( $translated_text, $checkout_titles, true ) ) {
		return 'Detalles de pedido';
	}

	return $translated_text;
}
add_filter( 'gettext', 'rdc_translate_checkout_woocommerce_texts', 20, 3 );
