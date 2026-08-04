<?php
/**
 * Elementor compatibility.
 *
 * @package HardwareForge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'hardware_forge_elementor_support' );

/**
 * Declare Elementor-friendly theme features.
 */
function hardware_forge_elementor_support() {
	// Lets Elementor control page layouts without fighting theme chrome.
	add_theme_support( 'elementor' );
}

add_action( 'elementor/theme/register_locations', 'hardware_forge_register_elementor_locations' );

/**
 * Register Elementor Pro Theme Builder locations (header, footer, single, archive).
 *
 * @param \ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager Locations manager.
 */
function hardware_forge_register_elementor_locations( $elementor_theme_manager ) {
	if ( method_exists( $elementor_theme_manager, 'register_all_core_location' ) ) {
		$elementor_theme_manager->register_all_core_location();
	}
}

/**
 * Whether the current post/page is built with Elementor.
 *
 * @param int|null $post_id Optional post ID.
 * @return bool
 */
function hardware_forge_is_elementor_page( $post_id = null ) {
	if ( ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}

	$post_id = $post_id ? (int) $post_id : get_the_ID();
	if ( ! $post_id ) {
		return false;
	}

	return \Elementor\Plugin::$instance->db->is_built_with_elementor( $post_id );
}

/**
 * Body class when Elementor is rendering the page canvas.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function hardware_forge_elementor_body_class( $classes ) {
	if ( hardware_forge_is_elementor_page() ) {
		$classes[] = 'hf-elementor-page';
	}
	return $classes;
}
add_filter( 'body_class', 'hardware_forge_elementor_body_class' );

/**
 * On theme activation, prefer theme CSS variables over Elementor default
 * color/typography schemes (user can still set Site Settings in Elementor).
 */
add_action( 'after_switch_theme', 'hardware_forge_elementor_disable_defaults' );

/**
 * Opt into Elementor color/font disable flags once.
 */
function hardware_forge_elementor_disable_defaults() {
	update_option( 'elementor_disable_color_schemes', 'yes' );
	update_option( 'elementor_disable_typography_schemes', 'yes' );
}

/**
 * Whether the front/static page has Elementor or editor content worth rendering.
 *
 * @param int|null $post_id Optional post ID.
 * @return bool
 */
function hardware_forge_front_page_has_builder_content( $post_id = null ) {
	$post = $post_id ? get_post( $post_id ) : get_post();
	if ( ! $post ) {
		return false;
	}

	if ( hardware_forge_is_elementor_page( $post->ID ) ) {
		$data = get_post_meta( $post->ID, '_elementor_data', true );
		return ! empty( $data ) && '[]' !== $data;
	}

	$content = trim( (string) $post->post_content );
	return '' !== $content;
}
