<?php
/**
 * Search results template.
 *
 * @package RDC Custom Astra
 */

get_header();

$search_term   = function_exists( 'rdc_get_search_term' ) ? rdc_get_search_term() : trim( (string) get_search_query() );
$product_ids   = function_exists( 'rdc_get_search_product_ids' ) ? rdc_get_search_product_ids( $search_term ) : array();
$total_products = count( $product_ids );
$display_ids   = array_slice( $product_ids, 0, 12 );
$product_query = function_exists( 'rdc_get_search_products_query' ) ? rdc_get_search_products_query( $display_ids, 12 ) : new WP_Query( array( 'post__in' => array( 0 ) ) );
$post_query    = function_exists( 'rdc_get_search_posts_query' ) ? rdc_get_search_posts_query( $search_term, 10 ) : new WP_Query( array( 'post__in' => array( 0 ) ) );

$has_product_results = ! empty( $display_ids ) && $product_query->have_posts();
$has_post_results    = $post_query->have_posts();
?>

<div id="primary" class="content-area rdc-search-page">
	<main id="main" class="site-main rdc-search-template">
		<header class="page-header rdc-search-header">
			<h1 class="page-title rdc-search-title">
				<?php
				/* translators: %s: search query. */
				printf( esc_html__( 'Resultados de busqueda para: %s', 'rdc-custom-astra' ), '<span>' . esc_html( $search_term ) . '</span>' );
				?>
			</h1>
		</header>

		<?php if ( $has_product_results ) : ?>
			<section class="rdc-search-section rdc-search-products-section" aria-labelledby="rdc-search-products-title">
				<div class="rdc-search-section-header">
					<h2 id="rdc-search-products-title" class="rdc-search-section-title"><?php esc_html_e( 'Productos', 'rdc-custom-astra' ); ?></h2>
					<?php
					$showing_count = min( max( 0, (int) $product_query->post_count ), 12 );
					?>
					<span class="rdc-search-results-count">
						<?php
						printf(
							esc_html__( 'Mostrando %1$d de %2$d productos', 'rdc-custom-astra' ),
							esc_html( $showing_count ),
							esc_html( $total_products )
						);
						?>
					</span>
				</div>

				<div class="woocommerce rdc-search-products-wrap">
					<ul class="products columns-4 rdc-search-products-grid">
						<?php
						$has_custom_product_template = file_exists( get_stylesheet_directory() . '/woocommerce/content-product.php' );
						
						while ( $product_query->have_posts() ) :
							$product_query->the_post();

							if ( function_exists( 'wc_get_product' ) ) {
								global $product;
								$product = wc_get_product( get_the_ID() );

								if ( ! $product || ! $product->is_visible() ) {
									continue;
								}
							}

							if ( $has_custom_product_template ) {
								include get_stylesheet_directory() . '/woocommerce/content-product.php';
							} elseif ( function_exists( 'wc_get_template_part' ) ) {
								wc_get_template_part( 'content', 'product' );
							}
						endwhile;
						wp_reset_postdata();
						?>
					</ul>
				</div>

				<?php if ( $total_products > 12 ) : ?>
					<div class="rdc-search-view-all-products">
						<?php
						$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
						$shop_url = add_query_arg( 's', $search_term, $shop_url );
						?>
						<a href="<?php echo esc_url( $shop_url ); ?>" class="rdc-view-all-products-btn">
							<?php
							printf(
								esc_html__( 'Ver todos los productos (%d)', 'rdc-custom-astra' ),
								esc_html( $total_products )
							);
							?>
						</a>
					</div>
				<?php endif; ?>
			</section>
		<?php endif; ?>

		<?php if ( $has_post_results ) : ?>
			<section class="rdc-search-section rdc-search-posts-section" aria-labelledby="rdc-search-posts-title">
				<h2 id="rdc-search-posts-title" class="rdc-search-section-title"><?php esc_html_e( 'Articulos del blog', 'rdc-custom-astra' ); ?></h2>

				<div class="rdc-search-posts-grid">
					<?php
					while ( $post_query->have_posts() ) :
						$post_query->the_post();
						?>
						<article id="post-<?php the_ID(); ?>" <?php post_class( 'rdc-search-post-item' ); ?>>
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="rdc-search-post-thumbnail">
									<a href="<?php the_permalink(); ?>">
										<?php the_post_thumbnail( 'medium_large' ); ?>
									</a>
								</div>
							<?php endif; ?>

							<div class="rdc-search-post-content">
								<div class="rdc-search-post-meta">
									<?php
									$categories = get_the_category();
									if ( ! empty( $categories ) ) {
										echo '<span class="rdc-search-post-category">' . esc_html( $categories[0]->name ) . '</span>';
									}
									?>
								</div>

								<h3 class="rdc-search-post-title">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h3>

								<div class="rdc-search-post-meta-info">
									<span class="rdc-search-post-author"><?php echo esc_html( get_the_author() ); ?></span>
									<span class="rdc-search-post-date"><?php echo esc_html( get_the_date() ); ?></span>
								</div>

								<div class="rdc-search-post-excerpt">
									<?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 20, '...' ) ); ?>
								</div>
							</div>
						</article>
					<?php endwhile; ?>
				</div>
			</section>
			<?php wp_reset_postdata(); ?>
		<?php endif; ?>

		<?php if ( ! $has_product_results && ! $has_post_results ) : ?>
			<section class="no-results not-found rdc-search-empty">
				<header class="page-header">
					<h2 class="page-title"><?php esc_html_e( 'Nada por aqui', 'rdc-custom-astra' ); ?></h2>
				</header>

				<div class="page-content">
					<p>
						<?php esc_html_e( 'No encontramos coincidencias para tu busqueda. Intenta con palabras clave diferentes, nombre de producto o SKU.', 'rdc-custom-astra' ); ?>
					</p>
				</div>
			</section>
		<?php endif; ?>
	</main>
</div>

<?php
get_footer();
