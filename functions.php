<?php
/**
 * RDC Custom Astra Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package RDC Custom Astra
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'rdc-custom-astra-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );


/**
 * RDC CUSTOM MENUS
 */
function rdc_register_menus() {
	register_nav_menus(
		array(
			'sidebar-menu' => __('RDC Main Menu: Sidebar Menu (Categorías)', 'rdc-custom-astra'),
			'quick-links-menu' => __('RDC Main Menu: Quick Links Menu', 'rdc-custom-astra'),
			'footer-about' => __('RDC Footer: Acerca de RDC', 'rdc-custom-astra'),
			'footer-support' => __('RDC Footer: Atención al Cliente', 'rdc-custom-astra'),
			'footer-resources' => __('RDC Footer: Recursos', 'rdc-custom-astra'),
		)
	);
} 
add_action('init', 'rdc_register_menus');



/**
 * RDC CUSTOM HEADER
 */

function rdc_custom_header_assets() {
	// custom header CSS
	wp_enqueue_style(
		'rdc-header-css',
		get_stylesheet_directory_uri() . '/assets/css/custom-header.css',
		array(),
		CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION
	);

	//custom header JS
	wp_enqueue_script(
		'rdc-header-js',
		get_stylesheet_directory_uri() . '/assets/js/custom-header.js',
		array('jquery'),
		CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION,
		true
	);

	// Localize AJAX data for header JS
	wp_localize_script(
		'rdc-header-js',
		'rdcHeader',
		array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce'   => wp_create_nonce('rdc_menu_nonce'),
		)
	);
}
add_action('wp_enqueue_scripts', 'rdc_custom_header_assets');


/**
 * Remove Astra Header
 */
function rdc_remove_astra_header() {
	remove_action('astra_header', 'astra_header_markup');
}
add_action('wp', 'rdc_remove_astra_header');


/**
 * Add RDC Custom Header
 */
function rdc_custom_header_markup() {
	get_template_part('template-parts/header-custom');
}
add_action('astra_header', 'rdc_custom_header_markup');

/**
 * END RDC CUSTOM HEADER
 */


/**
 * RDC CUSTOM FOOTER
 */

function rdc_remove_astra_footer() {
	remove_action('astra_footer', 'astra_footer_markup');
}
add_action('wp', 'rdc_remove_astra_footer');


/**
 * Add RDC Custom Footer
 */
function rdc_custom_footer_markup() {
	get_template_part('template-parts/footer-custom');
}
add_action('astra_footer', 'rdc_custom_footer_markup');


/**
 * RDC Custom Footer scripts and styles
 */
function rdc_custom_footer_assets() {
	wp_enqueue_style(
		'rdc-footer-css',
		get_stylesheet_directory_uri() . '/assets/css/custom-footer.css',
		array(),
		CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION
	);
}
add_action('wp_enqueue_scripts', 'rdc_custom_footer_assets');

/**
 * END RDC CUSTOM FOOTER
 */