<?php
/**
 * Gazette News theme functions.
 *
 * @package GazetteNews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GAZETTENEWS_VERSION', '1.1.5' );
define( 'GAZETTENEWS_DIR', get_template_directory() );
define( 'GAZETTENEWS_URI', get_template_directory_uri() );

require_once GAZETTENEWS_DIR . '/inc/template-tags.php';
require_once GAZETTENEWS_DIR . '/inc/fonts.php';
require_once GAZETTENEWS_DIR . '/inc/customizer.php';
require_once GAZETTENEWS_DIR . '/inc/widgets.php';
require_once GAZETTENEWS_DIR . '/inc/woocommerce.php';
require_once GAZETTENEWS_DIR . '/inc/ads.php';
require_once GAZETTENEWS_DIR . '/inc/youtube.php';
require_once GAZETTENEWS_DIR . '/inc/homepage.php';
require_once GAZETTENEWS_DIR . '/inc/blocks.php';
require_once GAZETTENEWS_DIR . '/inc/admin-homepage.php';

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
		'height'      => 120,
		'width'       => 400,
		'flex-height' => true,
		'flex-width'  => true,
		'header-text' => array( 'site-title', 'site-tagline' ),
		'unlink-homepage-logo' => false,
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

function gazettenews_sanitize_container_width( $value ) {
	$width = absint( $value );
	if ( $width < 720 ) {
		$width = 720;
	}
	if ( $width > 1920 ) {
		$width = 1920;
	}
	return $width;
}

function gazettenews_container_width() {
	return gazettenews_sanitize_container_width( get_option( 'gazettenews_container_width', 1240 ) );
}

function gazettenews_sanitize_slider_speed( $value ) {
	$sec = absint( $value );
	if ( $sec < 2 ) {
		$sec = 2;
	}
	if ( $sec > 20 ) {
		$sec = 20;
	}
	return $sec;
}

function gazettenews_slider_speed_ms() {
	return gazettenews_sanitize_slider_speed( get_theme_mod( 'gazettenews_slider_speed', 5 ) ) * 1000;
}

function gazettenews_sanitize_ticker_speed( $value ) {
	$sec = absint( $value );
	if ( $sec < 8 ) {
		$sec = 8;
	}
	if ( $sec > 120 ) {
		$sec = 120;
	}
	return $sec;
}

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
	$ver_css = GAZETTENEWS_VERSION;
	$ver_js  = GAZETTENEWS_VERSION;
	$css     = get_template_directory() . '/assets/css/main.css';
	$js      = get_template_directory() . '/assets/js/main.js';
	if ( file_exists( $css ) ) {
		$ver_css = (string) filemtime( $css );
	}
	if ( file_exists( $js ) ) {
		$ver_js = (string) filemtime( $js );
	}

	wp_enqueue_style(
		'gazettenews-fonts',
		gazettenews_google_fonts_url(),
		array(),
		null
	);

	wp_enqueue_style(
		'gazettenews-style',
		get_stylesheet_uri(),
		array(),
		$ver_css
	);

	wp_enqueue_style(
		'gazettenews-main',
		get_template_directory_uri() . '/assets/css/main.css',
		array( 'gazettenews-style' ),
		$ver_css
	);

	$accent = sanitize_hex_color( get_theme_mod( 'gazettenews_accent', '#d61f26' ) );
	$menu   = sanitize_hex_color( get_theme_mod( 'gazettenews_menu_color', '#1b5e4b' ) );
	if ( ! $accent ) {
		$accent = '#d61f26';
	}
	if ( ! $menu ) {
		$menu = '#1b5e4b';
	}
	$footer_bg  = sanitize_hex_color( get_theme_mod( 'gazettenews_footer_bg', '#111111' ) );
	$footer_bar = sanitize_hex_color( get_theme_mod( 'gazettenews_footer_bar', '#3498db' ) );
	$footer_hi  = sanitize_hex_color( get_theme_mod( 'gazettenews_footer_highlight', '#ffe14a' ) );
	if ( ! $footer_bg ) {
		$footer_bg = '#111111';
	}
	if ( ! $footer_bar ) {
		$footer_bar = '#3498db';
	}
	if ( ! $footer_hi ) {
		$footer_hi = '#ffe14a';
	}
	$ticker   = gazettenews_sanitize_ticker_speed( get_theme_mod( 'gazettenews_breaking_speed', 40 ) );
	$css_vars = ':root{--gn-accent:' . $accent . ';--gn-menu:' . $menu . ';--gn-max:' . gazettenews_container_width() . 'px;--gn-footer-bg:' . $footer_bg . ';--gn-footer-bar:' . $footer_bar . ';--gn-footer-hi:' . $footer_hi . ';--gn-ticker:' . $ticker . 's;' . gazettenews_font_css_vars() . '}';
	$footer_img = get_theme_mod( 'gazettenews_footer_image' );
	if ( $footer_img ) {
		$css_vars .= '.site-footer{background-image:url(' . esc_url( $footer_img ) . ');}';
	}
	wp_add_inline_style( 'gazettenews-main', $css_vars );
	wp_add_inline_style( 'gazettenews-style', $css_vars );

	wp_enqueue_script(
		'gazettenews-main',
		get_template_directory_uri() . '/assets/js/main.js',
		array(),
		$ver_js,
		true
	);
	wp_localize_script(
		'gazettenews-main',
		'gnTheme',
		array(
			'sliderSpeed' => gazettenews_slider_speed_ms(),
		)
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
	if ( ! is_active_sidebar( 'sidebar-1' ) && ! is_singular( 'post' ) && ! is_home() && ! is_archive() ) {
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

function gazettenews_upgrade_layout() {
	$ver = absint( get_option( 'gazettenews_sections_ver', 1 ) );
	if ( $ver >= 5 ) {
		return;
	}
	if ( $ver < 3 ) {
		$saved = get_option( 'gazettenews_home_sections', null );
		if ( ! is_array( $saved ) || count( $saved ) < 8 ) {
			update_option( 'gazettenews_home_sections', gazettenews_default_home_sections() );
		} else {
			$types   = wp_list_pluck( $saved, 'type' );
			$layouts = wp_list_pluck( $saved, 'layout' );
			$extra   = array();
			if ( ! in_array( 'slider', $layouts, true ) ) {
				$extra[] = array(
					'id' => 'slider-1', 'type' => 'posts', 'enabled' => true, 'title' => __( 'Trending', 'gazettenews' ),
					'cat' => 0, 'layout' => 'slider', 'count' => 8, 'position' => 'full', 'headstyle' => 'bar', 'html' => '', 'image' => '', 'link' => '', 'extra' => '',
				);
			}
			if ( ! in_array( 'video', $types, true ) ) {
				$extra[] = array(
					'id' => 'video-1', 'type' => 'video', 'enabled' => true, 'title' => __( 'Videos', 'gazettenews' ),
					'cat' => 0, 'layout' => '', 'count' => 5, 'position' => 'left', 'headstyle' => 'bar', 'html' => '', 'image' => '', 'link' => '', 'extra' => '',
				);
			}
			if ( ! in_array( 'facebook', $types, true ) ) {
				$extra[] = array(
					'id' => 'fb-1', 'type' => 'facebook', 'enabled' => true, 'title' => __( 'Follow us', 'gazettenews' ),
					'cat' => 0, 'layout' => '', 'count' => 1, 'position' => 'right', 'headstyle' => 'bar', 'html' => '', 'image' => '', 'link' => '', 'extra' => '',
				);
			}
			if ( $extra ) {
				update_option( 'gazettenews_home_sections', array_merge( $saved, $extra ) );
			}
		}
	}
	if ( $ver < 4 ) {
		$saved = get_option( 'gazettenews_home_sections', array() );
		if ( is_array( $saved ) ) {
			foreach ( $saved as &$row ) {
				if ( isset( $row['type'] ) && 'hero' === $row['type'] && absint( $row['count'] ) < 4 ) {
					$row['count'] = 5;
				}
			}
			unset( $row );
			update_option( 'gazettenews_home_sections', $saved );
		}
	}
	if ( $ver < 5 ) {
		$saved = get_option( 'gazettenews_home_sections', array() );
		if ( is_array( $saved ) ) {
			foreach ( $saved as &$row ) {
				if ( isset( $row['type'] ) && 'video' === $row['type'] && absint( $row['count'] ) < 20 ) {
					$row['count'] = 50;
				}
			}
			unset( $row );
			update_option( 'gazettenews_home_sections', $saved );
		}
	}
	update_option( 'gazettenews_sections_ver', 5 );
}
add_action( 'after_switch_theme', 'gazettenews_upgrade_layout' );
add_action( 'admin_init', 'gazettenews_upgrade_layout' );
