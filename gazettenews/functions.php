<?php
/**
 * Gazette News theme functions.
 *
 * @package GazetteNews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GAZETTENEWS_VERSION', '1.0.0' );
define( 'GAZETTENEWS_DIR', get_template_directory() );
define( 'GAZETTENEWS_URI', get_template_directory_uri() );

require_once GAZETTENEWS_DIR . '/inc/template-tags.php';
require_once GAZETTENEWS_DIR . '/inc/customizer.php';
require_once GAZETTENEWS_DIR . '/inc/widgets.php';
require_once GAZETTENEWS_DIR . '/inc/woocommerce.php';
require_once GAZETTENEWS_DIR . '/inc/ads.php';
require_once GAZETTENEWS_DIR . '/inc/youtube.php';
require_once GAZETTENEWS_DIR . '/inc/homepage.php';
require_once GAZETTENEWS_DIR . '/inc/blocks.php';
if ( is_admin() ) {
	require_once GAZETTENEWS_DIR . '/inc/admin-homepage.php';
}

/**
 * Theme setup.
 */
function gazettenews_setup() {
	load_theme_textdomain( 'gazettenews', GAZETTENEWS_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'post-formats', array( 'video', 'gallery', 'audio', 'quote' ) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 90,
		'width'       => 320,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'custom-background', array( 'default-color' => 'ffffff' ) );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );

	set_post_thumbnail_size( 800, 500, true );
	add_image_size( 'gazettenews-featured', 1200, 720, true );
	add_image_size( 'gazettenews-mosaic', 800, 560, true );
	add_image_size( 'gazettenews-module', 640, 420, true );
	add_image_size( 'gazettenews-thumb', 360, 240, true );
	add_image_size( 'gazettenews-small', 120, 90, true );

	register_nav_menus( array(
		'primary'   => __( 'Primary Menu', 'gazettenews' ),
		'top'       => __( 'Top Bar Menu', 'gazettenews' ),
		'footer'    => __( 'Footer Menu', 'gazettenews' ),
		'mobile'    => __( 'Mobile Menu', 'gazettenews' ),
	) );

	add_editor_style( 'assets/css/editor.css' );
}
add_action( 'after_setup_theme', 'gazettenews_setup' );

/**
 * Content width.
 */
function gazettenews_content_width() {
	$GLOBALS['content_width'] = 780;
}
add_action( 'after_setup_theme', 'gazettenews_content_width', 0 );

/**
 * Widget areas.
 */
function gazettenews_widgets_init() {
	$sidebars = array(
		'sidebar-1'       => array( __( 'Main Sidebar', 'gazettenews' ), __( 'Posts, archives, and magazine homepage.', 'gazettenews' ) ),
		'header-ad'       => array( __( 'Header Advertisement', 'gazettenews' ), __( 'Banner next to the logo (728x90).', 'gazettenews' ) ),
		'home-after-hero' => array( __( 'Home After Featured', 'gazettenews' ), __( 'Full-width slot under the mosaic.', 'gazettenews' ) ),
		'shop-sidebar'    => array( __( 'Shop Sidebar', 'gazettenews' ), __( 'WooCommerce archive and product pages.', 'gazettenews' ) ),
		'footer-1'        => array( __( 'Footer 1', 'gazettenews' ), '' ),
		'footer-2'        => array( __( 'Footer 2', 'gazettenews' ), '' ),
		'footer-3'        => array( __( 'Footer 3', 'gazettenews' ), '' ),
		'footer-4'        => array( __( 'Footer 4', 'gazettenews' ), '' ),
	);

	foreach ( $sidebars as $id => $meta ) {
		register_sidebar( array(
			'name'          => $meta[0],
			'id'            => $id,
			'description'   => $meta[1],
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title"><span>',
			'after_title'   => '</span></h3>',
		) );
	}
}
add_action( 'widgets_init', 'gazettenews_widgets_init' );

/**
 * Enqueue assets.
 */
function gazettenews_scripts() {
	wp_enqueue_style(
		'gazettenews-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;600;700;800&family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'gazettenews-style',
		get_stylesheet_uri(),
		array(),
		GAZETTENEWS_VERSION
	);

	wp_enqueue_style(
		'gazettenews-main',
		GAZETTENEWS_URI . '/assets/css/main.css',
		array( 'gazettenews-style' ),
		GAZETTENEWS_VERSION
	);

	$accent = sanitize_hex_color( get_theme_mod( 'gazettenews_accent', '#d61f26' ) );
	$menu   = sanitize_hex_color( get_theme_mod( 'gazettenews_menu_color', '#1b5e4b' ) );
	if ( ! $accent ) {
		$accent = '#d61f26';
	}
	if ( ! $menu ) {
		$menu = '#1b5e4b';
	}
	$css = ':root{--gn-accent:' . $accent . ';--gn-menu:' . $menu . ';}';
	wp_add_inline_style( 'gazettenews-main', $css );

	wp_enqueue_script(
		'gazettenews-main',
		GAZETTENEWS_URI . '/assets/js/main.js',
		array(),
		GAZETTENEWS_VERSION,
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'gazettenews_scripts' );

/**
 * Excerpt length.
 */
function gazettenews_excerpt_length( $length ) {
	return 22;
}
add_filter( 'excerpt_length', 'gazettenews_excerpt_length' );

function gazettenews_excerpt_more( $more ) {
	return '…';
}
add_filter( 'excerpt_more', 'gazettenews_excerpt_more' );

/**
 * Body classes.
 */
function gazettenews_body_classes( $classes ) {
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}
	if ( is_front_page() ) {
		$classes[] = 'magazine-home';
	}
	return $classes;
}
add_filter( 'body_class', 'gazettenews_body_classes' );

/**
 * Default menu fallback.
 */
function gazettenews_primary_fallback() {
	echo '<ul class="menu">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'gazettenews' ) . '</a></li>';
	wp_list_categories( array(
		'title_li' => '',
		'number'   => 7,
		'orderby'  => 'count',
		'order'    => 'DESC',
	) );
	echo '</ul>';
}
