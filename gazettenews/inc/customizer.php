<?php
/**
 * Theme Customizer.
 *
 * @package GazetteNews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gazettenews_customize_register( $wp_customize ) {
	$wp_customize->add_panel( 'gazettenews_panel', array(
		'title'    => __( 'Gazette News', 'gazettenews' ),
		'priority' => 30,
	) );

	$wp_customize->add_section( 'gazettenews_branding', array(
		'title' => __( 'Colors & Branding', 'gazettenews' ),
		'panel' => 'gazettenews_panel',
	) );

	$wp_customize->add_setting( 'gazettenews_accent', array(
		'default'           => '#4db2ec',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'gazettenews_accent', array(
		'label'   => __( 'Accent color', 'gazettenews' ),
		'section' => 'gazettenews_branding',
	) ) );

	$wp_customize->add_setting( 'gazettenews_tagline', array(
		'default'           => __( 'News & Magazine', 'gazettenews' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'gazettenews_tagline', array(
		'label'   => __( 'Header tagline', 'gazettenews' ),
		'section' => 'gazettenews_branding',
		'type'    => 'text',
	) );

	$wp_customize->add_section( 'gazettenews_header', array(
		'title' => __( 'Header & Ticker', 'gazettenews' ),
		'panel' => 'gazettenews_panel',
	) );

	$wp_customize->add_setting( 'gazettenews_show_date', array(
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
	) );
	$wp_customize->add_control( 'gazettenews_show_date', array(
		'label'   => __( 'Show date in top bar', 'gazettenews' ),
		'section' => 'gazettenews_header',
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'gazettenews_breaking_label', array(
		'default'           => __( 'BREAKING', 'gazettenews' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'gazettenews_breaking_label', array(
		'label'   => __( 'Breaking news label', 'gazettenews' ),
		'section' => 'gazettenews_header',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'gazettenews_breaking_count', array(
		'default'           => 8,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'gazettenews_breaking_count', array(
		'label'   => __( 'Breaking ticker post count', 'gazettenews' ),
		'section' => 'gazettenews_header',
		'type'    => 'number',
	) );

	$wp_customize->add_section( 'gazettenews_home', array(
		'title' => __( 'Magazine Homepage', 'gazettenews' ),
		'panel' => 'gazettenews_panel',
	) );

	$wp_customize->add_setting( 'gazettenews_featured_count', array(
		'default'           => 5,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'gazettenews_featured_count', array(
		'label'       => __( 'Featured mosaic posts', 'gazettenews' ),
		'description' => __( 'Uses sticky posts first, then latest posts.', 'gazettenews' ),
		'section'     => 'gazettenews_home',
		'type'        => 'number',
	) );

	$cats = array( 0 => __( '— Latest posts —', 'gazettenews' ) );
	foreach ( get_categories( array( 'hide_empty' => false ) ) as $cat ) {
		$cats[ $cat->term_id ] = $cat->name;
	}

	for ( $i = 1; $i <= 4; $i++ ) {
		$wp_customize->add_setting( "gazettenews_mod_{$i}_cat", array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		) );
		$wp_customize->add_control( "gazettenews_mod_{$i}_cat", array(
			'label'   => sprintf( __( 'Module %d category', 'gazettenews' ), $i ),
			'section' => 'gazettenews_home',
			'type'    => 'select',
			'choices' => $cats,
		) );

		$wp_customize->add_setting( "gazettenews_mod_{$i}_title", array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( "gazettenews_mod_{$i}_title", array(
			'label'   => sprintf( __( 'Module %d title (optional)', 'gazettenews' ), $i ),
			'section' => 'gazettenews_home',
			'type'    => 'text',
		) );
	}

	$wp_customize->add_setting( 'gazettenews_show_shop', array(
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
	) );
	$wp_customize->add_control( 'gazettenews_show_shop', array(
		'label'   => __( 'Show WooCommerce products on homepage', 'gazettenews' ),
		'section' => 'gazettenews_home',
		'type'    => 'checkbox',
	) );

	$wp_customize->add_section( 'gazettenews_social', array(
		'title' => __( 'Social Links', 'gazettenews' ),
		'panel' => 'gazettenews_panel',
	) );

	foreach ( array( 'facebook', 'twitter', 'instagram', 'youtube', 'linkedin' ) as $network ) {
		$wp_customize->add_setting( "gazettenews_social_{$network}", array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wp_customize->add_control( "gazettenews_social_{$network}", array(
			'label'   => ucfirst( $network ) . ' URL',
			'section' => 'gazettenews_social',
			'type'    => 'url',
		) );
	}

	$wp_customize->add_section( 'gazettenews_footer', array(
		'title' => __( 'Footer', 'gazettenews' ),
		'panel' => 'gazettenews_panel',
	) );

	$wp_customize->add_setting( 'gazettenews_copyright', array(
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'gazettenews_copyright', array(
		'label'   => __( 'Copyright text', 'gazettenews' ),
		'section' => 'gazettenews_footer',
		'type'    => 'textarea',
	) );
}
add_action( 'customize_register', 'gazettenews_customize_register' );

function gazettenews_social_links() {
	$out = '';
	foreach ( array( 'facebook', 'twitter', 'instagram', 'youtube', 'linkedin' ) as $network ) {
		$url = get_theme_mod( "gazettenews_social_{$network}" );
		if ( $url ) {
			$out .= '<a class="soc-' . esc_attr( $network ) . '" href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( ucfirst( $network ) ) . '</a>';
		}
	}
	return $out;
}
