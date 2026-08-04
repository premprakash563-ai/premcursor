<?php
/**
 * Scripts and styles.
 *
 * @package HardwareForge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'hardware_forge_enqueue_assets' );

/**
 * Enqueue front-end assets.
 */
function hardware_forge_enqueue_assets() {
	wp_enqueue_style(
		'hardware-forge-fonts',
		'https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Manrope:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'hardware-forge-main',
		HARDWARE_FORGE_URI . '/assets/css/main.css',
		array( 'hardware-forge-fonts' ),
		HARDWARE_FORGE_VERSION
	);

	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_style(
			'hardware-forge-woocommerce',
			HARDWARE_FORGE_URI . '/assets/css/woocommerce.css',
			array( 'hardware-forge-main' ),
			HARDWARE_FORGE_VERSION
		);
	}

	wp_enqueue_script(
		'hardware-forge-theme',
		HARDWARE_FORGE_URI . '/assets/js/theme.js',
		array(),
		HARDWARE_FORGE_VERSION,
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
