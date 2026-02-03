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


/**
 * RDC CUSTOM PRODUCT CATEGORIES BLOCK
 */

/**
 * Register RDC Product Category Block
 */
function rdc_register_product_categories_block() {
	// register block script
	wp_register_script(
		'rdc-product-categories-block',
		get_stylesheet_directory_uri() . '/blocks/product-categories/block.js',
		array('wp-blocks', 'wp-element', 'wp-components', 'wp-data'),
		CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION
	);

	// register block styles
	wp_register_style(
		'rdc-product-categories-block-editor',
		get_stylesheet_directory_uri() . '/blocks/product-categories/editor.css',
		array('wp-edit-blocks'),
		CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION
	);

	wp_register_style(
		'rdc-product-categories-block',
		get_stylesheet_directory_uri() . '/blocks/product-categories/style.css',
		array(),
		CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION
	);

	// register the block
	register_block_type(
		'rdc/product-categories',
		array(
			'editor_script' => 'rdc-product-categories-block',
			'editor_style' => 'rdc-product-categories-block-editor',
			'style' => 'rdc-product-categories-block',
			'render_callback' => 'rdc_render_product_categories_block',
			'attributes' => array(
				'selectedCategories' => array(
					'type' => 'array',
					'default' => array(),
				),
				'title' => array(
					'type' => 'string',
					'default' => 'Categorías Top',
				),
				'subtitle' => array(
					'type' => 'string',
					'default' => '',
				),
			),
		)
	);
}
add_action('init', 'rdc_register_product_categories_block');


/**
 * Render RDC Product Categories Block
 */
function rdc_render_product_categories_block($attributes) {
	$selected_categories = isset($attributes['selectedCategories']) ? $attributes['selectedCategories']:array();
	$title = isset($attributes['title']) ? $attributes['title']: 'Categorías Top';
	$subtitle = isset($attributes['subtitle']) ? $attributes['subtitle']: '';

	if (empty($selected_categories)) {
		return '';
	}

	ob_start();
	?>

	<div class="rdc-product-categories">
		<?php if (!empty($title)): ?>
			<h2 class="rdc-product-categories-title"><?php echo esc_html($title); ?></h2>
		<?php endif; ?>
		<div class="categories-wrapper">
			<div class="categories-container">
				<?php foreach ($selected_categories as $cat_id):
					$category = get_term($cat_id, 'product_cat');
					if (!$category || is_wp_error($category)) {
						continue;
					}

					$thumbnail_id = get_term_meta($cat_id, 'thumbnail_id', true);
					$image_url = $thumbnail_id? wp_get_attachment_url($thumbnail_id): wc_placeholder_img_src();
					$category_link = get_term_link($category);
				?>
				<div class="category-item">
					<a href="<?php echo esc_url($category_link); ?>" class="category-link">
						<div class="category-image-wrapper">
							<div class="category-circle"></div>
							<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($category->name); ?>" class="category-image">
						</div>
						<h3 class="category-name"><?php echo esc_html($category->name); ?></h3>
					</a>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php if(!empty($subtitle)): ?>
			<h4 class="rdc-product-categories-subtitle"><?php echo esc_html($subtitle); ?></h4>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * END RDC CUSTOM PRODUCT CATEGORIES BLOCK
 */


/**
 * RDC CUSTOM FEATURED BRANDS BLOCK
 */

/**
 * Register RDC Featured Brands Block
 */
function rdc_register_featured_brands_block() {
	// register block script
	wp_register_script(
		'rdc-featured-brands-block',
		get_stylesheet_directory_uri() . '/blocks/featured-brands/block.js',
		array('wp-blocks', 'wp-element', 'wp-components', 'wp-data'),
		CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION
	);

	// register block styles
	wp_register_style(
		'rdc-featured-brands-block-editor',
		get_stylesheet_directory_uri() . '/blocks/featured-brands/editor.css',
		array('wp-edit-blocks'),
		CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION
	);

	wp_register_style(
		'rdc-featured-brands-block',
		get_stylesheet_directory_uri() . '/blocks/featured-brands/style.css',
		array(),
		CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION
	);

	// register carousel script
	wp_register_script(
		'rdc-brands-carousel',
		get_stylesheet_directory_uri() . '/blocks/featured-brands/carousel.js',
		array('jquery'),
		CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION,
		true
	);

	// register the block
	register_block_type(
		'rdc/featured-brands',
		array(
			'editor_script' => 'rdc-featured-brands-block',
			'editor_style' => 'rdc-featured-brands-block-editor',
			'style' => 'rdc-featured-brands-block',
			'render_callback' => 'rdc_render_featured_brands_block',
			'attributes' => array(
				'selectedBrands' => array(
					'type' => 'array',
					'default' => array(),
				),
				'title' => array(
					'type' => 'string',
					'default' => 'Marcas Destacadas',
				),
				'autoplaySpeed' => array(
					'type' => 'number',
					'default' => 3000, // en milisegundos
				),
			),
		)
	);
}
add_action('init', 'rdc_register_featured_brands_block');

/**
 * Render RDC Featured Brands Block
 */
function rdc_render_featured_brands_block($attributes) {
	$selected_brands = isset($attributes['selectedBrands']) ? $attributes['selectedBrands']:array();
	$title = isset($attributes['title'])? $attributes['title']: 'Marcas Destacadas';
	$display_mode = isset($attributes['displayMode'])? $attributes['displayMode']: 'carousel';
	$autoplay_speed = isset($attributes['autoplaySpeed'])? $attributes['autoplaySpeed']: 3000;

	if(empty($selected_brands)) {
		return '';
	}

	// enqueue carousel script only for carousel mode
	if($display_mode === 'carousel') {
		wp_enqueue_script('rdc-brands-carousel');
	}

	ob_start();
	?>

	<div class="rdc-featured-brands <?php echo esc_attr('mode-' . $display_mode); ?>" <?php if($display_mode === 'carousel'): ?>data-autoplay-speed="<?php echo esc_attr($autoplay_speed); ?>"<?php endif; ?>>
		<?php if (!empty($title)): ?>
			<h2 class="brands-title"><?php echo esc_html($title); ?></h2>
		<?php endif; ?>
		
		<?php if($display_mode === 'carousel'): ?>
			<div class="brands-carousel-wrapper">
				<button class="carousel-nav carousel-prev" aria-label="Anterior">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
						<path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>		
				</button>
				<div class="brands-carousel-container">
					<div class="brands-carousel">
						<?php foreach ($selected_brands as $brand_id):
							$brand = get_term($brand_id, 'product_brand');
							if(!$brand || is_wp_error($brand)) {
								continue;
							}
							$thumbnail_id = get_term_meta($brand_id, 'thumbnail_id', true);
							$image_url = $thumbnail_id? wp_get_attachment_url($thumbnail_id) : '';
							$brand_link = get_term_link($brand);
						?>
						<div class="brand-item">
							<a href="<?php echo esc_url($brand_link); ?>" class="brand-link">
								<?php if($image_url): ?>
									<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($brand->name); ?>" class="brand-logo">
								<?php else: ?>
									<span class="brand-name-text"><?php echo esc_html($brand->name); ?></span>
								<?php endif; ?>
							</a>
						</div>
						<?php endforeach; ?>
					</div>
				</div>
				<button class="carousel-nav carousel-next" aria-label="Siguiente">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
						<path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>				
				</button>
			</div>
		<?php else: ?>
			<div class="brands-grid">
				<?php foreach ($selected_brands as $brand_id):
					$brand = get_term($brand_id, 'product_brand');
					if(!$brand || is_wp_error($brand)) {
						continue;
					}
					$thumbnail_id = get_term_meta($brand_id, 'thumbnail_id', true);
					$image_url = $thumbnail_id? wp_get_attachment_url($thumbnail_id) : '';
					$brand_link = get_term_link($brand);
				?>
				<div class="brand-item">
					<a href="<?php echo esc_url($brand_link); ?>" class="brand-link">
						<?php if($image_url): ?>
							<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($brand->name); ?>" class="brand-logo">
						<?php else: ?>
							<span class="brand-name-text"><?php echo esc_html($brand->name); ?></span>
						<?php endif; ?>
					</a>
				</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * END RDC CUSTOM FEATURED BRANDS BLOCK
 */