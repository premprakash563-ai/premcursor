<?php
/**
 * Theme setup.
 *
 * @package HardwareForge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'hardware_forge_setup' );

/**
 * Register theme features, menus, and image sizes.
 */
function hardware_forge_setup() {
	load_theme_textdomain( 'hardware-forge', HARDWARE_FORGE_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
		'navigation-widgets',
	) );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'custom-background', array(
		'default-color' => 'f3f1ec',
	) );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );

	// Elementor / page builders expect a defined content width.
	$GLOBALS['content_width'] = 1200;

	register_nav_menus( array(
		'primary'   => __( 'Primary Menu', 'hardware-forge' ),
		'footer'    => __( 'Footer Menu', 'hardware-forge' ),
		'catalogue' => __( 'Catalogue Menu', 'hardware-forge' ),
	) );

	add_image_size( 'hardware-forge-hero', 1920, 1080, true );
	add_image_size( 'hardware-forge-card', 640, 480, true );
}

add_action( 'widgets_init', 'hardware_forge_widgets_init' );

/**
 * Register widget areas.
 */
function hardware_forge_widgets_init() {
	$sidebars = array(
		'sidebar-1' => __( 'Blog Sidebar', 'hardware-forge' ),
		'shop'      => __( 'Shop Sidebar', 'hardware-forge' ),
		'footer-1'  => __( 'Footer Column 1', 'hardware-forge' ),
		'footer-2'  => __( 'Footer Column 2', 'hardware-forge' ),
		'footer-3'  => __( 'Footer Column 3', 'hardware-forge' ),
	);

	foreach ( $sidebars as $id => $name ) {
		register_sidebar( array(
			'name'          => $name,
			'id'            => $id,
			'description'   => sprintf(
				/* translators: %s: sidebar name */
				__( 'Widgets in %s.', 'hardware-forge' ),
				$name
			),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		) );
	}
}
