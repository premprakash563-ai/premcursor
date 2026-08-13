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
		'title'    => __( 'Logo, Colors & Branding', 'gazettenews' ),
		'panel'    => 'gazettenews_panel',
		'priority' => 1,
	) );

	$wp_customize->add_setting( 'gazettenews_accent', array(
		'default'           => '#d61f26',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'gazettenews_accent', array(
		'label'   => __( 'Accent / Breaking / category color', 'gazettenews' ),
		'section' => 'gazettenews_branding',
	) ) );

	$wp_customize->add_setting( 'gazettenews_menu_color', array(
		'default'           => '#1b5e4b',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'gazettenews_menu_color', array(
		'label'   => __( 'Menu & overlay color', 'gazettenews' ),
		'section' => 'gazettenews_branding',
	) ) );

	$wp_customize->add_setting( 'gazettenews_logo_line1', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'gazettenews_logo_line1', array(
		'label'       => __( 'Logo line 1 (red Hindi/title)', 'gazettenews' ),
		'description' => __( 'Leave empty to use the site name. Upload a logo in Site Identity to replace this lockup.', 'gazettenews' ),
		'section'     => 'gazettenews_branding',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'gazettenews_logo_line2', array(
		'default'           => 'NEWS 24x7',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'gazettenews_logo_line2', array(
		'label'   => __( 'Logo line 2 (black box)', 'gazettenews' ),
		'section' => 'gazettenews_branding',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'gazettenews_tagline', array(
		'default'           => __( 'News & Magazine', 'gazettenews' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'gazettenews_tagline', array(
		'label'   => __( 'Header tagline', 'gazettenews' ),
		'section' => 'gazettenews_branding',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'gazettenews_container_width', array(
		'type'              => 'option',
		'default'           => 1240,
		'sanitize_callback' => 'gazettenews_sanitize_container_width',
	) );
	$wp_customize->add_control( 'gazettenews_container_width', array(
		'label'       => __( 'Site container width (px)', 'gazettenews' ),
		'description' => __( 'Max width for header, homepage modules, and footer. 720–1920.', 'gazettenews' ),
		'section'     => 'gazettenews_branding',
		'type'        => 'number',
		'input_attrs' => array(
			'min'  => 720,
			'max'  => 1920,
			'step' => 10,
		),
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

	$wp_customize->add_setting( 'gazettenews_youtube_api_key', array(
		'type'              => 'option',
		'default'           => '',
		'sanitize_callback' => 'gazettenews_sanitize_youtube_api_key',
	) );
	$wp_customize->add_control( 'gazettenews_youtube_api_key', array(
		'label'       => __( 'YouTube Data API key', 'gazettenews' ),
		'description' => __( 'Leave blank to keep the saved key. Saving other Customizer settings will not erase it. Restrict this key to YouTube Data API v3 and your domain.', 'gazettenews' ),
		'section'     => 'gazettenews_header',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'gazettenews_breaking_label', array(
		'default'           => __( 'BREAKING NEWS', 'gazettenews' ),
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

	$wp_customize->add_setting( 'gazettenews_header_ad_image', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'gazettenews_header_ad_image', array(
		'label'   => __( 'Header advertisement image (728×90)', 'gazettenews' ),
		'section' => 'gazettenews_header',
	) ) );

	$wp_customize->add_setting( 'gazettenews_header_ad_url', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( 'gazettenews_header_ad_url', array(
		'label'   => __( 'Header ad click URL', 'gazettenews' ),
		'section' => 'gazettenews_header',
		'type'    => 'url',
	) );

	$wp_customize->add_setting( 'gazettenews_header_ad_code', array(
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'gazettenews_header_ad_code', array(
		'label'   => __( 'Or header ad HTML', 'gazettenews' ),
		'section' => 'gazettenews_header',
		'type'    => 'textarea',
	) );

	$wp_customize->add_section( 'gazettenews_home', array(
		'title'       => __( 'Magazine Homepage', 'gazettenews' ),
		'description' => __( 'For full control (add, drag, hide modules) use Appearance → Homepage Sections. These Customizer fields are used only as defaults before you save that screen.', 'gazettenews' ),
		'panel'       => 'gazettenews_panel',
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

	foreach ( array( 'facebook', 'twitter', 'instagram', 'youtube', 'linkedin', 'telegram', 'whatsapp' ) as $network ) {
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

	$wp_customize->add_setting( 'gazettenews_about', array(
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'gazettenews_about', array(
		'label'   => __( 'Footer About us', 'gazettenews' ),
		'section' => 'gazettenews_footer',
		'type'    => 'textarea',
	) );
}
add_action( 'customize_register', 'gazettenews_customize_register' );

function gazettenews_customize_logo_control( $wp_customize ) {
	if ( ! $wp_customize->get_section( 'gazettenews_branding' ) ) {
		return;
	}
	if ( ! $wp_customize->get_setting( 'custom_logo' ) ) {
		$wp_customize->add_setting( 'custom_logo', array(
			'theme_supports'    => array( 'custom-logo' ),
			'transport'         => 'refresh',
			'sanitize_callback' => 'absint',
		) );
	}
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'gazettenews_header_logo', array(
		'label'     => __( 'Header logo', 'gazettenews' ),
		'description' => __( 'Upload PNG or JPG. Replaces the text logo.', 'gazettenews' ),
		'section'   => 'gazettenews_branding',
		'settings'  => 'custom_logo',
		'mime_type' => 'image',
		'priority'  => 1,
	) ) );
}
add_action( 'customize_register', 'gazettenews_customize_logo_control', 20 );

function gazettenews_social_links( $class = '' ) {
	$out   = '';
	$wrap  = $class ? ' class="' . esc_attr( $class ) . '"' : '';
	$items = array( 'facebook', 'twitter', 'instagram', 'youtube', 'linkedin', 'telegram', 'whatsapp' );
	foreach ( $items as $network ) {
		$url = get_theme_mod( "gazettenews_social_{$network}" );
		if ( $url ) {
			$out .= '<a class="soc-' . esc_attr( $network ) . '" href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( ucfirst( $network ) ) . '</a>';
		}
	}
	if ( $class && $out ) {
		return '<div' . $wrap . '>' . $out . '</div>';
	}
	return $out;
}
