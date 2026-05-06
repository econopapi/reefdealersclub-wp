<?php
/**
 * Search module.
 *
 * @package RDC Custom Astra
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue dedicated search results styles.
 */
function rdc_search_assets() {
	if ( ! is_search() ) {
		return;
	}

	$search_css_rel = '/assets/css/search-results.css';
	$search_css_abs = get_stylesheet_directory() . $search_css_rel;
	$search_css_ver = file_exists( $search_css_abs ) ? filemtime( $search_css_abs ) : CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION;

	wp_enqueue_style(
		'rdc-search-results-css',
		get_stylesheet_directory_uri() . $search_css_rel,
		array(),
		$search_css_ver
	);
}
add_action( 'wp_enqueue_scripts', 'rdc_search_assets' );

/**
 * Returns normalized current search term.
 *
 * @return string
 */
function rdc_get_search_term() {
	return trim( (string) get_search_query() );
}

/**
 * Search products by text and SKU, merged and deduplicated.
 * SKU matches are prioritized.
 *
 * @param string $search_term Search term.
 * @return int[]
 */
function rdc_get_search_product_ids( $search_term ) {
	$search_term = trim( (string) $search_term );
	if ( '' === $search_term || ! post_type_exists( 'product' ) ) {
		return array();
	}

	$text_query = new WP_Query(
		array(
			'post_type'      => 'product',
			's'              => $search_term,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	$text_product_ids = $text_query->posts;
	wp_reset_postdata();

	$sku_query = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => '_sku',
					'value'   => $search_term,
					'compare' => 'LIKE',
				),
			),
		)
	);
	$sku_product_ids = $sku_query->posts;
	wp_reset_postdata();

	return array_values( array_unique( array_merge( $sku_product_ids, $text_product_ids ) ) );
}

/**
 * Build products query from selected IDs preserving order.
 *
 * @param int[] $display_ids Product IDs for display.
 * @param int   $limit       Maximum products.
 * @return WP_Query
 */
function rdc_get_search_products_query( $display_ids, $limit = 12 ) {
	$display_ids = array_map( 'absint', (array) $display_ids );
	$display_ids = array_values( array_filter( $display_ids ) );
	$limit       = max( 1, absint( $limit ) );

	if ( empty( $display_ids ) ) {
		return new WP_Query(
			array(
				'post_type'      => 'product',
				'post__in'       => array( 0 ),
				'posts_per_page' => 0,
			)
		);
	}

	return new WP_Query(
		array(
			'post_type'      => 'product',
			'post__in'       => $display_ids,
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => 'post__in',
		)
	);
}

/**
 * Build blog posts search query.
 *
 * @param string $search_term Search term.
 * @param int    $limit       Maximum posts.
 * @return WP_Query
 */
function rdc_get_search_posts_query( $search_term, $limit = 10 ) {
	$search_term = trim( (string) $search_term );
	$limit       = max( 1, absint( $limit ) );

	if ( '' === $search_term ) {
		return new WP_Query(
			array(
				'post_type'      => 'post',
				'post__in'       => array( 0 ),
				'posts_per_page' => 0,
			)
		);
	}

	return new WP_Query(
		array(
			'post_type'      => 'post',
			's'              => $search_term,
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
		)
	);
}
