<?php
/**
 * Account email lock controls.
 *
 * Keeps the WooCommerce account email immutable from the customer account form.
 *
 * @package RDC Custom Astra
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Removes account email from WooCommerce required fields on account details.
 *
 * The theme template renders the email as read-only text and does not submit an
 * `account_email` field, so WooCommerce should not require it during save.
 *
 * @param array<string, string> $required_fields Required account detail fields.
 * @return array<string, string>
 */
function rdc_remove_account_email_from_required_fields( $required_fields ) {
	unset( $required_fields['account_email'] );

	return $required_fields;
}
add_filter( 'woocommerce_save_account_details_required_fields', 'rdc_remove_account_email_from_required_fields', 20, 1 );

/**
 * Blocks manual POST attempts to change the WooCommerce account email.
 *
 * @param WP_Error $errors Save errors.
 * @param stdClass $user User data prepared by WooCommerce.
 */
function rdc_block_account_email_change_attempts( $errors, $user ) {
	if ( empty( $user->ID ) || ! isset( $user->user_email ) ) {
		return;
	}

	$current_user = get_user_by( 'id', $user->ID );

	if ( ! $current_user || $user->user_email === $current_user->user_email ) {
		return;
	}

	$user->user_email = $current_user->user_email;
	$errors->add(
		'rdc_account_email_locked',
		__( 'El correo electronico de la cuenta no se puede modificar.', 'rdc-custom-astra' )
	);
}
add_action( 'woocommerce_save_account_details_errors', 'rdc_block_account_email_change_attempts', 20, 2 );

