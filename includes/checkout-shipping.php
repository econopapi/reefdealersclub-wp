<?php
/**
 * Checkout shipping controls.
 *
 * Fuerza que el costo de envio se cotice en checkout y no en carrito.
 *
 * @package RDC Custom Astra
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determina si estamos en contexto de carrito de WooCommerce.
 *
 * @return bool
 */
function rdc_is_woocommerce_cart_page() {
	return function_exists( 'is_cart' ) && is_cart();
}

/**
 * Encola estilos para ocultar elementos de envio en carrito.
 */
function rdc_enqueue_cart_shipping_controls_assets() {
	if ( ! rdc_is_woocommerce_cart_page() ) {
		return;
	}

	wp_enqueue_style(
		'rdc-cart-shipping-controls',
		get_stylesheet_directory_uri() . '/assets/css/cart-shipping-controls.css',
		array(),
		defined( 'CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION' ) ? CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION : null
	);
}
add_action( 'wp_enqueue_scripts', 'rdc_enqueue_cart_shipping_controls_assets', 30 );

/**
 * Reemplaza el bloque de envio del carrito por un mensaje informativo.
 *
 * @param string $shipping_html HTML original de envio.
 * @return string
 */
function rdc_replace_cart_shipping_html_with_checkout_notice( $shipping_html ) {
	if ( ! rdc_is_woocommerce_cart_page() ) {
		return $shipping_html;
	}

	return '<span class="rdc-shipping-notice">Los costos de envio se calculan en el Checkout de pago.</span>';
}
add_filter( 'woocommerce_cart_totals_shipping_html', 'rdc_replace_cart_shipping_html_with_checkout_notice', 10, 1 );

/**
 * Deshabilita el calculador de envio unicamente en carrito.
 *
 * @param bool $show_calculator Estado original.
 * @return bool
 */
function rdc_disable_shipping_calculator_on_cart( $show_calculator ) {
	if ( rdc_is_woocommerce_cart_page() ) {
		return false;
	}

	return $show_calculator;
}
add_filter( 'woocommerce_shipping_show_shipping_calculator', 'rdc_disable_shipping_calculator_on_cart', 10, 1 );
