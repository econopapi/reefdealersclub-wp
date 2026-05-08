<?php
/**
 * RDC MSI Promotions - Checkout Control
 *
 * @package RDC Custom Astra
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve MSI lookup IDs for a cart item.
 *
 * Variation ID has priority, but parent product ID is also included as fallback.
 *
 * @param array $cart_item WooCommerce cart item.
 * @return array<int>
 */
function rdc_msi_get_cart_item_lookup_ids( $cart_item ) {
	$lookup_ids   = array();
	$variation_id = isset( $cart_item['variation_id'] ) ? absint( $cart_item['variation_id'] ) : 0;
	$product_id   = isset( $cart_item['product_id'] ) ? absint( $cart_item['product_id'] ) : 0;

	if ( $variation_id > 0 ) {
		$lookup_ids[] = $variation_id;
	}

	if ( $product_id > 0 ) {
		$lookup_ids[] = $product_id;
	}

	return array_values( array_unique( $lookup_ids ) );
}

/**
 * Determine MSI status for the current cart.
 *
 * @return array{msi_status:string,allowed_months:array<int>}
 */
function rdc_msi_get_checkout_status() {
	$products_config = get_option( 'rdc_msi_mp_products', array() );
	if ( ! is_array( $products_config ) ) {
		$products_config = array();
	}

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return array(
			'msi_status'     => 'none_msi',
			'allowed_months' => array(),
		);
	}

	$cart = WC()->cart->get_cart();
	if ( empty( $cart ) ) {
		return array(
			'msi_status'     => 'none_msi',
			'allowed_months' => array(),
		);
	}

	$total_items    = 0;
	$eligible_items = 0;
	$common_months  = null;

	foreach ( $cart as $cart_item ) {
		$lookup_ids = rdc_msi_get_cart_item_lookup_ids( $cart_item );
		if ( empty( $lookup_ids ) ) {
			continue;
		}

		++$total_items;

		$config_months = array();
		foreach ( $lookup_ids as $lookup_id ) {
			if ( isset( $products_config[ $lookup_id ]['months'] ) && is_array( $products_config[ $lookup_id ]['months'] ) ) {
				$config_months = array_map( 'absint', $products_config[ $lookup_id ]['months'] );
				$config_months = array_values( array_filter( $config_months ) );
				break;
			}
		}

		if ( ! empty( $config_months ) ) {
			++$eligible_items;

			if ( null === $common_months ) {
				$common_months = $config_months;
			} else {
				$common_months = array_values( array_intersect( $common_months, $config_months ) );
			}
		}
	}

	if ( 0 === $total_items || 0 === $eligible_items ) {
		return array(
			'msi_status'     => 'none_msi',
			'allowed_months' => array(),
		);
	}

	if ( $eligible_items < $total_items ) {
		return array(
			'msi_status'     => 'mixed',
			'allowed_months' => array(),
		);
	}

	$common_months = is_array( $common_months ) ? array_values( array_unique( array_map( 'absint', $common_months ) ) ) : array();
	sort( $common_months );

	if ( empty( $common_months ) ) {
		return array(
			'msi_status'     => 'none_msi',
			'allowed_months' => array(),
		);
	}

	return array(
		'msi_status'     => 'all_msi',
		'allowed_months' => $common_months,
	);
}

/**
 * Enqueue checkout controls for MSI rules.
 */
function rdc_msi_enqueue_checkout_control_assets() {
	if ( is_admin() || ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return;
	}

	$enabled = get_option( 'rdc_msi_mp_enabled', '0' );
	if ( '1' !== $enabled ) {
		return;
	}

	$status_data = rdc_msi_get_checkout_status();
	$cart_msg    = get_option( 'rdc_msi_mp_mixed_cart_message', 'Algunos productos de tu carrito no son elegibles para Meses Sin Intereses. Para comprar a MSI, retira del carrito los productos que no participan en esta promocion.' );

	wp_enqueue_style(
		'rdc-msi-checkout-control',
		get_stylesheet_directory_uri() . '/assets/css/msi-checkout-control.css',
		array(),
		CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION
	);

	wp_enqueue_script(
		'rdc-msi-checkout-control',
		get_stylesheet_directory_uri() . '/assets/js/msi-checkout-control.js',
		array( 'jquery' ),
		CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION,
		true
	);

	wp_localize_script(
		'rdc-msi-checkout-control',
		'RDCMSI',
		array(
			'enabled'          => true,
			'msiStatus'        => $status_data['msi_status'],
			'allowedMonths'    => array_values( array_map( 'absint', $status_data['allowed_months'] ) ),
			'mixedCartMessage' => wp_strip_all_tags( $cart_msg ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'rdc_msi_enqueue_checkout_control_assets', 40 );
