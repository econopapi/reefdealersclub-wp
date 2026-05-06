<?php
/**
 * Header module.
 *
 * @package RDC Custom Astra
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue custom header assets.
 */
function rdc_custom_header_assets() {
	$header_css_rel = '/assets/css/custom-header.css';
	$header_js_rel  = '/assets/js/custom-header.js';
	$header_css_abs = get_stylesheet_directory() . $header_css_rel;
	$header_js_abs  = get_stylesheet_directory() . $header_js_rel;
	$header_css_ver = file_exists( $header_css_abs ) ? filemtime( $header_css_abs ) : CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION;
	$header_js_ver  = file_exists( $header_js_abs ) ? filemtime( $header_js_abs ) : CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION;

	wp_enqueue_style(
		'rdc-header-css',
		get_stylesheet_directory_uri() . $header_css_rel,
		array(),
		$header_css_ver
	);

	wp_enqueue_script(
		'rdc-header-js',
		get_stylesheet_directory_uri() . $header_js_rel,
		array( 'jquery' ),
		$header_js_ver,
		true
	);

	wp_localize_script(
		'rdc-header-js',
		'rdcHeader',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'rdc_menu_nonce' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'rdc_custom_header_assets' );

/**
 * Remove Astra default header markup.
 */
function rdc_remove_astra_header() {
	remove_action( 'astra_header', 'astra_header_markup' );
}
add_action( 'wp', 'rdc_remove_astra_header' );

/**
 * Render RDC custom header.
 */
function rdc_custom_header_markup() {
	get_template_part( 'template-parts/header-custom' );
}
add_action( 'astra_header', 'rdc_custom_header_markup' );

/**
 * Update cart count badge via fragments.
 *
 * @param array<string,string> $fragments Fragment map.
 * @return array<string,string>
 */
function rdc_cart_count_fragment( $fragments ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return $fragments;
	}

	$count = WC()->cart->get_cart_contents_count();
	$style = $count === 0 ? ' style="display:none;"' : '';

	$fragments['.rdc-cart-count'] = '<span class="rdc-cart-count"' . $style . '>' . esc_html( $count ) . '</span>';

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'rdc_cart_count_fragment' );

/**
 * Add category metadata to curated sidebar links.
 *
 * @param array<string,string> $atts Link attributes.
 * @param WP_Post              $item Menu item.
 * @param stdClass             $args Menu args.
 * @return array<string,string>
 */
function rdc_product_cat_menu_link_attrs( $atts, $item, $args ) {
	if ( isset( $args->theme_location ) && 'sidebar-menu' === $args->theme_location && isset( $item->object ) && 'product_cat' === $item->object ) {
		$atts['data-cat-id'] = (string) $item->object_id;
	}

	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'rdc_product_cat_menu_link_attrs', 10, 3 );

/**
 * AJAX endpoint to fetch product subcategories.
 */
function rdc_get_subcategories() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rdc_menu_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Invalid nonce' ), 403 );
	}

	$cat_id = isset( $_POST['catId'] ) ? absint( $_POST['catId'] ) : 0;
	if ( ! $cat_id ) {
		wp_send_json_error( array( 'message' => 'Invalid category' ), 400 );
	}

	$parent = get_term( $cat_id, 'product_cat' );
	if ( ! $parent || is_wp_error( $parent ) ) {
		wp_send_json_error( array( 'message' => 'Category not found' ), 404 );
	}

	$children = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => $cat_id,
		)
	);

	$groups = array();

	if ( ! is_wp_error( $children ) ) {
		foreach ( $children as $child ) {
			$grandchildren = get_terms(
				array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => true,
					'parent'     => $child->term_id,
				)
			);

			$items = array();

			if ( ! is_wp_error( $grandchildren ) && ! empty( $grandchildren ) ) {
				foreach ( $grandchildren as $gc ) {
					$gc_link = get_term_link( $gc );
					if ( is_wp_error( $gc_link ) ) {
						continue;
					}

					$items[] = array(
						'title' => html_entity_decode( wp_strip_all_tags( $gc->name ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
						'link'  => $gc_link,
					);
				}
			} else {
				$child_link = get_term_link( $child );
				if ( ! is_wp_error( $child_link ) ) {
					$items[] = array(
						'title' => html_entity_decode( wp_strip_all_tags( $child->name ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
						'link'  => $child_link,
					);
				}
			}

			if ( ! empty( $items ) ) {
				$groups[] = array(
					'title' => html_entity_decode( wp_strip_all_tags( $child->name ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
					'items' => $items,
				);
			}
		}
	}

	wp_send_json_success(
		array(
			'title' => html_entity_decode( wp_strip_all_tags( $parent->name ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
			'groups' => $groups,
		)
	);
}
add_action( 'wp_ajax_rdc_get_subcategories', 'rdc_get_subcategories' );
add_action( 'wp_ajax_nopriv_rdc_get_subcategories', 'rdc_get_subcategories' );
