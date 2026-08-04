<?php
/**
 * Template helper tags.
 *
 * @package HardwareForge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Print the site brand (logo or name). Brand is a hero-level signal on the front page.
 */
function hardware_forge_site_brand( $extra_class = '' ) {
	$classes = trim( 'hf-brand ' . $extra_class );

	if ( has_custom_logo() ) {
		$logo_id = (int) get_theme_mod( 'custom_logo' );
		$image   = wp_get_attachment_image(
			$logo_id,
			'full',
			false,
			array(
				'class'    => 'custom-logo',
				'loading'  => 'eager',
				'decoding' => 'async',
			)
		);
		echo '<a class="' . esc_attr( $classes . ' custom-logo-link' ) . '" href="' . esc_url( home_url( '/' ) ) . '" rel="home">';
		echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image()
		echo '</a>';
		return;
	}

	echo '<a class="' . esc_attr( $classes ) . '" href="' . esc_url( home_url( '/' ) ) . '" rel="home">';
	echo '<span class="hf-brand__mark" aria-hidden="true"></span>';
	echo '<span class="hf-brand__name">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
	echo '</a>';
}

/**
 * Posted on / by meta.
 */
function hardware_forge_posted_on() {
	$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
	printf(
		'<span class="hf-posted-on">%s</span>',
		sprintf(
			$time_string,
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() )
		)
	);
}

/**
 * Fallback primary menu when none is assigned.
 */
function hardware_forge_fallback_menu() {
	echo '<ul class="hf-menu hf-menu--fallback">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'hardware-forge' ) . '</a></li>';
	if ( class_exists( 'WooCommerce' ) ) {
		echo '<li><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'Shop', 'hardware-forge' ) . '</a></li>';
	}
	echo '</ul>';
}
