<?php
/**
 * WooCommerce integration.
 *
 * @package GazetteNews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gazettenews_is_woocommerce_active() {
	return class_exists( 'WooCommerce' );
}

function gazettenews_woocommerce_setup() {
	if ( ! gazettenews_is_woocommerce_active() ) {
		return;
	}

	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 420,
		'single_image_width'    => 720,
		'product_grid'          => array(
			'default_rows'    => 4,
			'min_rows'        => 1,
			'max_rows'        => 8,
			'default_columns' => 3,
			'min_columns'     => 2,
			'max_columns'     => 4,
		),
	) );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'gazettenews_woocommerce_setup' );

function gazettenews_woo_wrapper_start() {
	echo '<div class="site-main shop-main"><div class="gn-container gn-shop-layout">';
	echo '<div class="content-area">';
}

function gazettenews_woo_wrapper_end() {
	echo '</div>';
	if ( is_active_sidebar( 'shop-sidebar' ) && ( is_shop() || is_product_category() || is_product_tag() ) ) {
		echo '<aside class="sidebar shop-sidebar">';
		dynamic_sidebar( 'shop-sidebar' );
		echo '</aside>';
	}
	echo '</div></div>';
}

function gazettenews_woocommerce_wrappers() {
	if ( ! gazettenews_is_woocommerce_active() ) {
		return;
	}
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
	add_action( 'woocommerce_before_main_content', 'gazettenews_woo_wrapper_start', 10 );
	add_action( 'woocommerce_after_main_content', 'gazettenews_woo_wrapper_end', 10 );
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
}
add_action( 'wp', 'gazettenews_woocommerce_wrappers' );

function gazettenews_cart_count() {
	if ( ! gazettenews_is_woocommerce_active() ) {
		return '';
	}
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	return '<a class="gn-cart" href="' . esc_url( wc_get_cart_url() ) . '"><span class="cart-label">' . esc_html__( 'Cart', 'gazettenews' ) . '</span><span class="cart-count">' . esc_html( (string) $count ) . '</span></a>';
}

function gazettenews_cart_fragment( $fragments ) {
	ob_start();
	echo gazettenews_cart_count(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$fragments['a.gn-cart'] = ob_get_clean();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'gazettenews_cart_fragment' );

function gazettenews_home_products( $limit = 4, $title = '' ) {
	if ( ! gazettenews_is_woocommerce_active() ) {
		return;
	}

	$q = new WP_Query( array(
		'post_type'           => 'product',
		'posts_per_page'      => $limit,
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );

	if ( ! $q->have_posts() ) {
		return;
	}

	$shop = wc_get_page_permalink( 'shop' );
	if ( ! $title ) {
		$title = __( 'Shop', 'gazettenews' );
	}
	echo '<section class="module module-shop">';
	gazettenews_module_header( $title );
	if ( $shop ) {
		echo '<p class="module-shop-link"><a href="' . esc_url( $shop ) . '">' . esc_html__( 'Visit store', 'gazettenews' ) . '</a></p>';
	}
	echo '<div class="product-grid">';
	while ( $q->have_posts() ) {
		$q->the_post();
		global $product;
		if ( ! $product ) {
			continue;
		}
		echo '<article class="product-card">';
		echo '<a class="thumb" href="' . esc_url( get_permalink() ) . '">';
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'gazettenews-module' );
		} else {
			echo wc_placeholder_img( 'gazettenews-module' );
		}
		echo '</a>';
		echo '<h4><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h4>';
		echo '<div class="price">' . $product->get_price_html() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</article>';
	}
	echo '</div></section>';
	wp_reset_postdata();
}

function gazettenews_loop_columns() {
	return 3;
}
add_filter( 'loop_shop_columns', 'gazettenews_loop_columns' );

function gazettenews_related_products_args( $args ) {
	$args['posts_per_page'] = 3;
	$args['columns']        = 3;
	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'gazettenews_related_products_args' );
