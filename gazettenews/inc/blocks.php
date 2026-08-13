<?php
/**
 * Gutenberg blocks for homepage modules.
 *
 * @package GazetteNews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gazettenews_register_blocks() {
	wp_register_script(
		'gazettenews-blocks',
		GAZETTENEWS_URI . '/assets/js/blocks.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ),
		GAZETTENEWS_VERSION,
		true
	);

	register_block_type( 'gazettenews/posts', array(
		'api_version'     => 2,
		'editor_script'   => 'gazettenews-blocks',
		'render_callback' => 'gazettenews_block_posts_render',
		'attributes'      => array(
			'title'  => array( 'type' => 'string', 'default' => '' ),
			'cat'    => array( 'type' => 'number', 'default' => 0 ),
			'layout' => array( 'type' => 'string', 'default' => 'grid' ),
			'count'  => array( 'type' => 'number', 'default' => 6 ),
		),
	) );

	register_block_type( 'gazettenews/mosaic', array(
		'api_version'     => 2,
		'editor_script'   => 'gazettenews-blocks',
		'render_callback' => 'gazettenews_block_mosaic_render',
		'attributes'      => array(
			'cat'   => array( 'type' => 'number', 'default' => 0 ),
			'count' => array( 'type' => 'number', 'default' => 5 ),
		),
	) );

	register_block_type( 'gazettenews/shop', array(
		'api_version'     => 2,
		'editor_script'   => 'gazettenews-blocks',
		'render_callback' => 'gazettenews_block_shop_render',
		'attributes'      => array(
			'title' => array( 'type' => 'string', 'default' => 'Shop' ),
			'count' => array( 'type' => 'number', 'default' => 4 ),
		),
	) );
}
add_action( 'init', 'gazettenews_register_blocks' );

function gazettenews_localize_blocks() {
	$cats = array( array( 'value' => 0, 'label' => __( 'Latest posts', 'gazettenews' ) ) );
	foreach ( get_categories( array( 'hide_empty' => false ) ) as $cat ) {
		$cats[] = array(
			'value' => (int) $cat->term_id,
			'label' => $cat->name,
		);
	}
	wp_localize_script( 'gazettenews-blocks', 'gazetteNewsBlocks', array( 'categories' => $cats ) );
}
add_action( 'enqueue_block_editor_assets', 'gazettenews_localize_blocks' );

function gazettenews_block_posts_render( $attrs ) {
	$used    = array();
	$section = gazettenews_sanitize_section( array(
		'type'    => 'posts',
		'enabled' => true,
		'title'   => isset( $attrs['title'] ) ? $attrs['title'] : '',
		'cat'     => isset( $attrs['cat'] ) ? $attrs['cat'] : 0,
		'layout'  => isset( $attrs['layout'] ) ? $attrs['layout'] : 'grid',
		'count'   => isset( $attrs['count'] ) ? $attrs['count'] : 6,
	) );
	ob_start();
	gazettenews_render_posts_module( $section, $used );
	return ob_get_clean();
}

function gazettenews_block_mosaic_render( $attrs ) {
	$used    = array();
	$section = gazettenews_sanitize_section( array(
		'type'    => 'mosaic',
		'enabled' => true,
		'cat'     => isset( $attrs['cat'] ) ? $attrs['cat'] : 0,
		'count'   => isset( $attrs['count'] ) ? $attrs['count'] : 5,
	) );
	ob_start();
	gazettenews_render_mosaic( $section, $used );
	return ob_get_clean();
}

function gazettenews_block_shop_render( $attrs ) {
	$title = isset( $attrs['title'] ) ? sanitize_text_field( $attrs['title'] ) : __( 'Shop', 'gazettenews' );
	$count = isset( $attrs['count'] ) ? absint( $attrs['count'] ) : 4;
	ob_start();
	gazettenews_home_products( $count, $title );
	return ob_get_clean();
}
