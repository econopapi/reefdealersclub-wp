# RDC Custom Astra Theme

Tema hijo personalizado de Astra para Reef Dealers Club.

Este repositorio contiene el tema hijo `RDC Custom Astra` diseñado para trabajar junto con el tema padre `Astra` y WooCommerce. El objetivo principal del tema es ofrecer componentes a la medida para la presentación de marcas, categorías de producto y un header/footer customizados con una navegación lateral dinámica.

**Autor:** Daniel Limón
**Contacto:** dani@dlimon.net
**Versión:** 1.1.0
**Licencia:** GNU General Public License v2 o posterior (ver `style.css`)

Tabla de contenido
- Novedades recientes
- Visión general
- Requisitos
- Instalación
- Estructura del tema (resumen de archivos)
- Componentes principales
	- `functions.php` (principal)
	- Header personalizado (`template-parts/header-custom.php`, `assets/css/custom-header.css`, `assets/js/custom-header.js`)
	- Footer personalizado (`template-parts/footer-custom.php`, `assets/css/custom-footer.css`)
	- Bloques personalizados (Gutenberg): `blocks/product-categories`, `blocks/featured-brands`, `blocks/all-brands`
- Hooks, filtros y comportamientos importantes
- Notas de desarrollo y recomendaciones
- Contacto

--

Novedades recientes
----------

- Se modularizo la logica del header en [includes/header.php](includes/header.php) para mantener [functions.php](functions.php) como entrypoint.
- Se mejoro el header con el mismo comportamiento funcional solicitado:
	- Buscador movil expandible con boton limpiar.
	- Contador de carrito en tiempo real via fragments de WooCommerce.
	- Render mas seguro del panel de submenu y enlace rapido Ver todo.
	- Endpoint AJAX para cargar subcategorias y compatibilidad con menus curados.
- Se agrego una busqueda avanzada en frontend con plantilla dedicada:
	- Productos buscados por texto y SKU (priorizando coincidencias de SKU).
	- Seccion de articulos de blog en la misma vista de resultados.
	- Modulo desacoplado en `includes/search.php` y estilos en `assets/css/search-results.css`.
- Se agrego un modulo de control granular de MSI para MercadoPago:
	- Configuracion en admin desde `RDC Promociones -> MSI MercadoPago`.
	- Whitelist de productos con meses permitidos por producto (3, 6, 9, 12).
	- Control de checkout para bloquear a 1 mensualidad en carritos mixtos o sin MSI.
	- Modulo desacoplado en `includes/msi-promotions/` y assets en `assets/css/msi-*.css` y `assets/js/msi-*.js`.
- Se agrego override de email de WooCommerce para reset de contrasena:
	- Plantilla en `woocommerce/emails/customer-reset-password.php`.
	- Copy en espanol adaptado al tono de Reef Dealers Club.
	- CTA de restablecimiento con color principal de marca (`#009fe3`).
- Se agrego un modulo para forzar cotizacion de envio en checkout:
	- En carrito se ocultan metodos/calculadora de envio y se muestra mensaje informativo.
	- Se reemplaza visualmente la fila nativa de envio en carrito por una fila controlada por el tema para evitar labels inconsistentes y garantizar el mensaje.
	- En checkout se mantiene el flujo normal de cotizacion de WooCommerce.
	- Se deshabilita y oculta la seccion "Enviar a una direccion diferente" en checkout.
	- Se remueve la opcion "Descargas" del menu de Mi Cuenta.
	- Modulo desacoplado en `includes/checkout-shipping.php` con estilos en `assets/css/cart-shipping-controls.css`.

--

Visión general
----------

`RDC Custom Astra` es un tema hijo que:
- Reemplaza el header y footer de Astra con plantillas personalizadas.
- Añade tres bloques dinámicos renderizados por PHP para trabajar con taxonomías relacionadas a productos y marcas.
- Provee estilos y scripts específicos para la UI (menú lateral, carrusel de marcas, filtros alfabéticos).

Requisitos
----------

- WordPress (versión moderna; testar con WordPress 5.8+ y Gutenberg moderno).
- Tema padre `Astra` (el tema es un child theme; ver `style.css`: `Template: astra`).
- WooCommerce: muchos componentes (términos `product_cat`, `product_brand`, enlaces a carrito/mi cuenta) requieren WooCommerce y/o una taxonomía de marcas disponible en la instalación.
- Recomendado: PHP 7.4+ y soporte para HTTPS en el sitio.

Instalación
------------

1. Copiar la carpeta del tema a `wp-content/themes/`.
2. Asegurarse de que el tema padre `Astra` esté instalado y activo.
3. Activar `RDC Custom Astra` como tema hijo desde el admin de WordPress.
4. En `Apariencia → Menús`, crear/asignar las ubicaciones de menú:
	 - `sidebar-menu`
	 - `quick-links-menu`
	 - `footer-about`
	 - `footer-support`
	 - `footer-resources`
5. Configurar el `Custom Logo` en `Apariencia → Personalizar` si se desea mostrar logo.
6. Verificar que WooCommerce esté activo y que la taxonomía `product_brand` exista (plugin de marcas o implementación propia).

Estructura del tema (resumen)
-----------------------------

Estructura relevante (paths relativos a la raíz del tema):

- `functions.php` — Entrypoint del tema hijo; registra menús, carga modulos en `includes/` y registra bloques.
- `includes/header.php` — Modulo del header (enqueue, hooks de Astra, AJAX de subcategorias, fragments del carrito y metadatos de menu).
- `includes/search.php` — Modulo de busqueda (consultas por texto/SKU, query de blog y encolado de estilos de resultados).
- `includes/checkout-shipping.php` — Modulo de WooCommerce para carrito/checkout/mi-cuenta: oculta cotizacion en carrito, deshabilita calculadora en carrito, desactiva direccion de envio alterna en checkout y remueve "Descargas" del menu de Mi Cuenta.
- `includes/account-email-lock.php` — Modulo de WooCommerce para mantener inmutable el correo electronico en Detalles de la cuenta.
- `includes/checkout-labels.php` — Modulo de WooCommerce para ajustar textos del checkout: "Detalles de pedido", "Alcaldía/Municipio" y "Estado".
- `includes/msi-promotions/`
	- `init.php` — Bootstrap del modulo MSI.
	- `admin-page.php` — UI de administracion para MSI MercadoPago (settings y whitelist por producto).
	- `checkout-control.php` — Calculo de elegibilidad MSI por carrito y encolado de control en checkout.
- `style.css` — Cabecera del tema (meta: nombre, autor, `Template: astra`) y variables CSS globales (colores).
- `assets/css/custom-header.css` — Estilos del header y del menú lateral.
- `assets/css/custom-footer.css` — Estilos del footer y sección de newsletter.
- `assets/css/search-results.css` — Estilos de la plantilla de resultados de busqueda.
- `assets/css/msi-admin.css` — Estilos del panel de administracion MSI.
- `assets/css/msi-checkout-control.css` — Estilos del aviso y estado visual de MSI en checkout.
- `assets/css/cart-shipping-controls.css` — Estilos para ocultar UI de envio en carrito, mostrar mensaje de cotizacion y ocultar la seccion de direccion de envio alterna en checkout.
- `assets/js/custom-header.js` — Lógica JS del header: toggle del sidebar, submenú dinámico, llamadas AJAX para subcategorías.
- `assets/js/msi-admin.js` — Interacciones del panel MSI (agregar/quitar productos, seleccion masiva de meses).
- `assets/js/msi-checkout-control.js` — Control frontend de cuotas de MercadoPago segun reglas MSI del carrito.
- `blocks/`
	- `product-categories/` — Editor + frontend para bloque de categorías (archivo principal `block.js`, `editor.css`, `style.css`).
	- `featured-brands/` — Bloque de marcas destacadas con soporte carousel (`block.js`, `carousel.js`, `editor.css`, `style.css`).
	- `all-brands/` — Bloque con filtro alfabético (`block.js`, `frontend.js`, `editor.css`, `style.css`).
- `template-parts/header-custom.php` — Markup del header personalizado y menú lateral (incluye fallbacks `rdc_default_quick_links`, `rdc_default_sidebar_menu`).
- `template-parts/footer-custom.php` — Markup del footer personalizado (newsletter, columnas de enlaces, contacto).
- `search.php` — Plantilla de resultados unificada para productos (texto + SKU) y blog.
- `woocommerce/emails/customer-reset-password.php` — Override del correo de recuperacion de contrasena de WooCommerce adaptado a branding RDC.
- `woocommerce/myaccount/form-edit-account.php` — Override de Detalles de la cuenta para mostrar el correo electronico como dato no editable.

Componentes y comportamiento (análisis detallado)
-----------------------------------------------

1) `functions.php`
- Define la constante `CHILD_THEME_RDC_CUSTOM_ASTRA_VERSION`.
- Encola `style.css` del child con dependencia de `astra-theme-css`.
- Registra ubicaciones de menú: `sidebar-menu`, `quick-links-menu`, `footer-about`, `footer-support`, `footer-resources`.
- Header/footer: elimina las acciones por defecto de Astra (`remove_action('astra_header', 'astra_header_markup')` y similar para footer) y añade las plantillas propias con `get_template_part('template-parts/header-custom')` / `footer-custom` mediante las acciones `astra_header` y `astra_footer`.
- Encola assets específicos del header/footer (`assets/css/custom-header.css`, `assets/js/custom-header.js`, `assets/css/custom-footer.css`). El script del header es localizado con `rdcHeader` que contiene `ajaxUrl` y `nonce`.

Bloques registrados (por `functions.php`):

- `rdc/product-categories`:
	- Scripts: `blocks/product-categories/block.js` (editor), `blocks/product-categories/editor.css` (editor), `blocks/product-categories/style.css` (frontend).
	- Atributos: `selectedCategories` (array), `title` (string), `subtitle` (string).
	- Render callback: `rdc_render_product_categories_block($attributes)` — genera markup con `rdc-product-categories`, muestra imagen (meta `thumbnail_id`) y nombre de categoría.
	- Comportamiento: retorna vacío si no hay categorías seleccionadas; utiliza `get_term` y `get_term_meta` para thumbnails.

- `rdc/featured-brands`:
	- Scripts: `blocks/featured-brands/block.js`, `blocks/featured-brands/editor.css`, `blocks/featured-brands/style.css`, y `blocks/featured-brands/carousel.js` para la lógica del carrusel.
	- Atributos: `selectedBrands` (array), `title` (string), `displayMode` (carousel|grid), `autoplaySpeed` (number).
	- Render callback: `rdc_render_featured_brands_block($attributes)` — genera el carrusel o grid; encola `rdc-brands-carousel` cuando el modo es `carousel`.
	- Notes: `carousel.js` realiza clonados para bucle infinito, maneja responsive y autoplay.

- `rdc/all-brands`:
	- Scripts: `blocks/all-brands/block.js`, `blocks/all-brands/editor.css`, `blocks/all-brands/style.css`, `blocks/all-brands/frontend.js`.
	- Atributos: `title`, `showAlphabetFilter`, `columns`, `displayStyle`, `showBrandCount`, `brandImageSize`.
	- Render callback: `rdc_render_all_brands_block($attributes)` — obtén marcas con `get_terms('product_brand')`, organiza por letra, imprime filtro alfabético y conteo; inyecta `window.RDCAllBrandsData` con datos iniciales.
	- `frontend.js` implementa filtrado alfabético y animaciones; usa `IntersectionObserver` y ofrece accesibilidad básica (navegación por teclado para botones filtro).

2) `template-parts/header-custom.php`
- Markup del header con: logo, botón hamburguesa `.rdc-menu-toggle`, barra de búsqueda, acciones (login/cart) y estructura del menú lateral (`.rdc-sidebar-menu`) y panel de submenú `.rdc-submenu-panel`.
- Usa `wp_nav_menu` para dos menús (`quick-links-menu` y `sidebar-menu`) y provee `rdc_default_quick_links()` y `rdc_default_sidebar_menu()` como fallbacks que generan contenido dinámico de `product_cat` (categorías top-level) si no hay menú definido.

3) `assets/js/custom-header.js` (comportamiento clave)
- Controla apertura/cierre del sidebar y panel de submenú, gestión de estados CSS (`.active`), bloqueo de scroll del body al abrir overlay.
- Al hacer click en un enlace de la lista principal intenta usar hijos del menú (si existen) o, si no hay hijos, realiza una petición AJAX con `action: 'rdc_get_subcategories'` y `nonce` para obtener subcategorías dinámicamente.
- El handler AJAX `rdc_get_subcategories` esta implementado en `includes/header.php`.

4) `template-parts/footer-custom.php` y `assets/css/custom-footer.css`
- Contiene la sección de newsletter (`.rdc-newsletter-boxed`) y el footer principal con 4 columnas: logo/contacto, acerca de, atención al cliente y recursos.
- Usa las ubicaciones de menú `footer-about`, `footer-support`, `footer-resources` con fallbacks codificados.

5) Comportamientos adicionales en `functions.php`
- `rdc_delete_product_images($post_id)` enlazada a `before_delete_post`: borra attachments (thumbnail y galería) asociados a un producto cuando se borra el post — esto elimina físicamente archivos del media library, por lo que hay que usarlo con precaución.
- `rdc_require_login_for_woocommerce_content()` enlazada a `template_redirect`: fuerza login para ver contenido relacionado con WooCommerce (tienda, producto singular, taxonomías de producto, búsquedas de producto). Redirige a `myaccount` o a la URL de login con parámetro `redirect`.

Hooks y menús registrados
------------------------

- Menús registrados (usar en Apariencia → Menús):
	- `sidebar-menu` — menú principal de categorías (sidebar float)
	- `quick-links-menu` — enlaces rápidos en columna izquierda del sidebar
	- `footer-about` — columna "Acerca de"
	- `footer-support` — columna "Atención al cliente"
	- `footer-resources` — columna "Recursos"

Notas y recomendaciones (puntos detectados durante el análisis)
------------------------------------------------------------

1. Taxonomía `product_brand`
- El tema asume la existencia de la taxonomía `product_brand` (usada por los bloques de marcas). Asegúrese de que exista (plugin de marcas o código que registre la taxonomía). Si no existe, los bloques no mostrarán marcas.

2. Eliminación de imágenes al borrar productos
- `rdc_delete_product_images` borra archivos adjuntos del servidor. Revisar políticas de data-retention y backups si se activa en producción. Si no desea este comportamiento eliminar o comentar el hook.

3. Forzar login para WooCommerce
- `rdc_require_login_for_woocommerce_content` limita el acceso público a la tienda. Revisar su lógica si el sitio requiere accesibilidad pública a ciertas páginas (excepciones ya incluidas: admin, AJAX, REST, pagina de cuenta).

4. Scripts y build
- Los scripts de bloques están en JS plano usando las APIs globales de WordPress (`window.wp`). No se detectó un proceso de build (`package.json`) en este tema: editar los archivos JS directamente es viable, pero si desea usar ESNext/JSX/Tooling recomendamos introducir un proceso de bundling y compilar los assets a `blocks/*` antes de desplegar.

Buenas prácticas y mejoras sugeridas
----------------------------------

- Añadir manejo de focus y roles ARIA en el menú lateral para mejorar accesibilidad (focus trap, atributos `aria-expanded` en el toggle, `aria-controls` hacia el panel).
- Validar y sanitizar datos en los render callbacks (ya se usan `esc_html`, `esc_url` en muchos lugares — mantener y revisar cada salida).
- Añadir tests básicos o un entorno local con WP + WooCommerce para probar bloques dinámicos.
- Revisar la política de eliminación de imágenes y considerar uso de trash en lugar de borrado permanente si se desea reversibilidad.

Contacto
--------

Si quieres que implemente las mejoras o que agregue el handler AJAX directamente, puedo hacerlo: indícame si quieres que lo agregue a `functions.php` (o mejor: a un archivo dentro de `includes/`) y lo aplico.

--

Archivo actualizado: `README.md`
