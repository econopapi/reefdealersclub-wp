<?php
/**
 * RDC MSI Promotions - Admin Page
 *
 * @package RDC Custom Astra
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the top-level admin menu.
 */
function rdc_msi_register_admin_menu() {
	add_menu_page(
		'RDC Promociones',
		'RDC Promociones',
		'manage_woocommerce',
		'rdc-promociones',
		'rdc_msi_render_mercadopago_page',
		'dashicons-tickets-alt',
		56
	);

	add_submenu_page(
		'rdc-promociones',
		'MSI MercadoPago',
		'MSI MercadoPago',
		'manage_woocommerce',
		'rdc-promociones',
		'rdc_msi_render_mercadopago_page'
	);
}
add_action( 'admin_menu', 'rdc_msi_register_admin_menu' );

/**
 * Register settings for MSI MercadoPago.
 */
function rdc_msi_register_settings() {
	register_setting(
		'rdc_msi_mercadopago',
		'rdc_msi_mp_enabled',
		array(
			'sanitize_callback' => 'rdc_msi_sanitize_checkbox',
			'default'           => '0',
		)
	);

	register_setting(
		'rdc_msi_mercadopago',
		'rdc_msi_mp_products',
		array(
			'sanitize_callback' => 'rdc_msi_sanitize_products',
			'default'           => array(),
		)
	);

	register_setting(
		'rdc_msi_mercadopago',
		'rdc_msi_mp_mixed_cart_message',
		array(
			'sanitize_callback' => 'sanitize_textarea_field',
			'default'           => 'Algunos productos de tu carrito no son elegibles para Meses Sin Intereses. Para comprar a MSI, retira del carrito los productos que no participan en esta promocion.',
		)
	);
}
add_action( 'admin_init', 'rdc_msi_register_settings' );

/**
 * Sanitize checkbox values.
 *
 * @param mixed $input Checkbox input value.
 * @return string
 */
function rdc_msi_sanitize_checkbox( $input ) {
	return ( ! empty( $input ) && '1' === (string) $input ) ? '1' : '0';
}

/**
 * Sanitize MSI products configuration.
 *
 * @param mixed $input Raw products configuration.
 * @return array<int, array{months: array<int>}>
 */
function rdc_msi_sanitize_products( $input ) {
	if ( ! is_array( $input ) ) {
		return array();
	}

	$sanitized = array();
	foreach ( $input as $product_id => $data ) {
		$pid = absint( $product_id );
		if ( $pid <= 0 ) {
			continue;
		}

		$months = array();
		if ( isset( $data['months'] ) && is_array( $data['months'] ) ) {
			$allowed_months = array( 3, 6, 9, 12 );
			foreach ( $data['months'] as $month ) {
				$month = absint( $month );
				if ( in_array( $month, $allowed_months, true ) ) {
					$months[] = $month;
				}
			}
		}

		if ( ! empty( $months ) ) {
			sort( $months );
			$sanitized[ $pid ] = array( 'months' => $months );
		}
	}

	return $sanitized;
}

/**
 * Enqueue admin assets for the MSI page.
 *
 * @param string $hook Current admin hook suffix.
 */
function rdc_msi_admin_assets( $hook ) {
	if ( 'toplevel_page_rdc-promociones' !== $hook ) {
		return;
	}

	wp_enqueue_script( 'wc-enhanced-select' );
	wp_enqueue_style( 'woocommerce_admin_styles' );

	wp_enqueue_style(
		'rdc-msi-admin',
		get_stylesheet_directory_uri() . '/assets/css/msi-admin.css',
		array( 'woocommerce_admin_styles' ),
		CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION
	);

	wp_enqueue_script(
		'rdc-msi-admin',
		get_stylesheet_directory_uri() . '/assets/js/msi-admin.js',
		array( 'jquery', 'wc-enhanced-select' ),
		CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'rdc_msi_admin_assets' );

/**
 * Render MercadoPago MSI admin page.
 */
function rdc_msi_render_mercadopago_page() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}

	$enabled  = get_option( 'rdc_msi_mp_enabled', '0' );
	$products = get_option( 'rdc_msi_mp_products', array() );
	$cart_msg = get_option( 'rdc_msi_mp_mixed_cart_message', 'Algunos productos de tu carrito no son elegibles para Meses Sin Intereses. Para comprar a MSI, retira del carrito los productos que no participan en esta promocion.' );

	if ( ! is_array( $products ) ) {
		$products = array();
	}

	if ( isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'] ) {
		echo '<div class="notice notice-success is-dismissible"><p><strong>Configuracion de MSI guardada correctamente.</strong></p></div>';
	}
	?>
	<div class="wrap rdc-msi-wrap">
		<div class="rdc-msi-header">
			<span class="dashicons dashicons-tickets-alt"></span>
			<h1>MSI MercadoPago</h1>
			<span class="rdc-msi-status-badge <?php echo '1' === $enabled ? 'active' : 'inactive'; ?>">
				<?php echo '1' === $enabled ? 'Activo' : 'Inactivo'; ?>
			</span>
		</div>

		<p class="description">
			Gestiona que productos pueden ser comprados a Meses Sin Intereses (MSI) con tarjeta de credito
			a traves de MercadoPago. Los productos que no esten en esta lista se bloquearan a pago de contado (1 mensualidad).
		</p>

		<div class="rdc-msi-info-box">
			<strong>Como funciona?</strong><br>
			Cuando un cliente llega al checkout con tarjeta de credito, el sistema verifica los productos del carrito.
			Si <strong>todos</strong> los productos estan en la whitelist, se muestran las cuotas configuradas.
			Si hay <strong>productos mixtos</strong> (algunos con MSI y otros sin), se bloquea a 1 mensualidad y se muestra un aviso al cliente.
			Si <strong>ningun producto</strong> tiene MSI, se bloquea automaticamente a 1 mensualidad.
		</div>

		<form action="options.php" method="post" id="rdc-msi-form">
			<?php settings_fields( 'rdc_msi_mercadopago' ); ?>

			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="rdc_msi_mp_enabled">Activar control de MSI</label>
					</th>
					<td>
						<label>
							<input type="checkbox"
								id="rdc_msi_mp_enabled"
								name="rdc_msi_mp_enabled"
								value="1"
								<?php checked( $enabled, '1' ); ?> />
							<strong>Habilitar filtro de MSI en el checkout</strong>
						</label>
						<p class="description">
							Si esta desactivado, el comportamiento de MercadoPago sera el predeterminado
							(todos los productos podran usar MSI segun la configuracion del plugin).
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="rdc_msi_mp_mixed_cart_message">Mensaje de carrito mixto</label>
					</th>
					<td>
						<textarea id="rdc_msi_mp_mixed_cart_message"
							name="rdc_msi_mp_mixed_cart_message"
							rows="3"
							class="large-text"><?php echo esc_textarea( $cart_msg ); ?></textarea>
						<p class="description">
							Mensaje que se mostrara cuando el carrito tenga una combinacion de productos
							elegibles y no elegibles para MSI.
						</p>
					</td>
				</tr>
			</table>

			<hr>
			<h2>Productos elegibles para MSI</h2>

			<div class="rdc-msi-add-product-row">
				<select id="rdc-msi-product-search"
					class="wc-product-search"
					data-placeholder="Buscar producto por nombre o SKU..."
					data-action="woocommerce_json_search_products"
					data-exclude_type="grouped"
					style="min-width: 350px;">
				</select>
				<button type="button" id="rdc-msi-add-product" class="button button-primary">
					<span class="dashicons dashicons-plus-alt2" style="margin-top: 3px;"></span> Agregar producto
				</button>
			</div>

			<div id="rdc-msi-select-all-months" style="display: <?php echo ! empty( $products ) ? 'block' : 'none'; ?>;">
				<strong>Seleccionar meses para todos:</strong>
				<label><input type="checkbox" class="bulk-month" value="3"> 3 meses</label>
				<label><input type="checkbox" class="bulk-month" value="6"> 6 meses</label>
				<label><input type="checkbox" class="bulk-month" value="9"> 9 meses</label>
				<label><input type="checkbox" class="bulk-month" value="12"> 12 meses</label>
			</div>

			<table class="rdc-msi-product-table" id="rdc-msi-products-table">
				<thead>
					<tr>
						<th style="width: 45%;">Producto</th>
						<th>Meses disponibles</th>
						<th style="width: 50px;"></th>
					</tr>
				</thead>
				<tbody id="rdc-msi-products-body">
					<?php if ( empty( $products ) ) : ?>
						<tr class="rdc-msi-empty-state" id="rdc-msi-empty-row">
							<td colspan="3">
								<span class="dashicons dashicons-cart"></span>
								<p>No hay productos configurados para MSI.<br>Usa el buscador de arriba para agregar productos.</p>
							</td>
						</tr>
					<?php else : ?>
						<?php foreach ( $products as $product_id => $config ) :
							$product = wc_get_product( $product_id );
							if ( ! $product ) {
								continue;
							}
							$thumb_url = wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' );
							$months    = isset( $config['months'] ) ? $config['months'] : array();
							?>
							<tr data-product-id="<?php echo esc_attr( $product_id ); ?>">
								<td>
									<div class="product-info">
										<?php if ( $thumb_url ) : ?>
											<img src="<?php echo esc_url( $thumb_url ); ?>" class="product-thumb" alt="">
										<?php endif; ?>
										<div>
											<div class="product-name"><?php echo esc_html( $product->get_name() ); ?></div>
											<?php if ( $product->get_sku() ) : ?>
												<div class="product-sku">SKU: <?php echo esc_html( $product->get_sku() ); ?></div>
											<?php endif; ?>
										</div>
									</div>
								</td>
								<td>
									<div class="month-checks">
										<?php foreach ( array( 3, 6, 9, 12 ) as $month ) :
											$checked = in_array( $month, $months, true );
											?>
											<label class="<?php echo $checked ? 'checked' : ''; ?>">
												<input type="checkbox"
													name="rdc_msi_mp_products[<?php echo esc_attr( $product_id ); ?>][months][]"
													value="<?php echo esc_attr( $month ); ?>"
													<?php checked( $checked ); ?>
													onchange="this.parentElement.classList.toggle('checked', this.checked);" />
												<?php echo esc_html( $month ); ?>m
											</label>
										<?php endforeach; ?>
									</div>
								</td>
								<td>
									<button type="button" class="remove-product" title="Quitar producto" onclick="rdcRemoveMsiProduct(this);">
										<span class="dashicons dashicons-trash"></span>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<?php submit_button( 'Guardar configuracion de MSI' ); ?>
		</form>
	</div>
	<?php
}
