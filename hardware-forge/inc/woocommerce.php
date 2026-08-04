<?php
/**
 * WooCommerce compatibility.
 *
 * @package HardwareForge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

add_action( 'after_setup_theme', 'hardware_forge_woocommerce_setup' );

/**
 * Declare WooCommerce support and gallery features.
 */
function hardware_forge_woocommerce_setup() {
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 420,
			'single_image_width'    => 720,
			'product_grid'          => array(
				'default_rows'    => 4,
				'min_rows'        => 1,
				'default_columns' => 3,
				'min_columns'     => 2,
				'max_columns'     => 4,
			),
		)
	);

	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}

/**
 * Remove default WooCommerce wrappers; theme supplies its own.
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

add_action( 'woocommerce_before_main_content', 'hardware_forge_woo_wrapper_before' );
add_action( 'woocommerce_after_main_content', 'hardware_forge_woo_wrapper_after' );

/**
 * Open shop wrapper.
 */
function hardware_forge_woo_wrapper_before() {
	echo '<main id="primary" class="site-main hf-shop-main"><div class="hf-container">';
}

/**
 * Close shop wrapper.
 */
function hardware_forge_woo_wrapper_after() {
	echo '</div></main>';
}

/**
 * Products per page.
 *
 * @return int
 */
function hardware_forge_products_per_page() {
	return 12;
}
add_filter( 'loop_shop_per_page', 'hardware_forge_products_per_page' );

/**
 * Related products count / columns.
 *
 * @param array $args Related args.
 * @return array
 */
function hardware_forge_related_products_args( $args ) {
	$args['posts_per_page'] = 3;
	$args['columns']        = 3;
	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'hardware_forge_related_products_args' );

/**
 * Cart fragment for header mini-cart count.
 *
 * @param array $fragments Fragments.
 * @return array
 */
function hardware_forge_cart_link_fragment( $fragments ) {
	ob_start();
	hardware_forge_cart_link();
	$fragments['a.hf-cart-link'] = ob_get_clean();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'hardware_forge_cart_link_fragment' );

/**
 * Render header cart link markup.
 */
function hardware_forge_cart_link() {
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	$total = WC()->cart ? WC()->cart->get_cart_subtotal() : '';
	?>
	<a class="hf-cart-link" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php esc_attr_e( 'View cart', 'hardware-forge' ); ?>">
		<span class="hf-cart-link__icon" aria-hidden="true">
			<svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M3 5h2l2.4 10.2a2 2 0 0 0 2 1.5h7.8a2 2 0 0 0 2-1.5L21 8H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="10" cy="20" r="1.5" fill="currentColor"/><circle cx="18" cy="20" r="1.5" fill="currentColor"/></svg>
		</span>
		<span class="hf-cart-link__meta">
			<span class="hf-cart-link__label"><?php esc_html_e( 'Cart', 'hardware-forge' ); ?></span>
			<span class="hf-cart-link__total"><?php echo wp_kses_post( $total ); ?></span>
		</span>
		<span class="hf-cart-link__count" data-cart-count><?php echo absint( $count ); ?></span>
	</a>
	<?php
}

/**
 * Body class when WooCommerce is active on shop-like views.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function hardware_forge_woo_body_class( $classes ) {
	if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
		$classes[] = 'hf-woocommerce';
	}
	return $classes;
}
add_filter( 'body_class', 'hardware_forge_woo_body_class' );
