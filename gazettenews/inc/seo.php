<?php
/**
 * Open Graph / Twitter cards and Yoast SEO compatibility.
 *
 * @package GazetteNews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gazettenews_has_yoast() {
	return defined( 'WPSEO_VERSION' ) || function_exists( 'YoastSEO' );
}

function gazettenews_yoast_handles_og() {
	if ( ! gazettenews_has_yoast() ) {
		return false;
	}
	if ( class_exists( 'WPSEO_Options' ) && method_exists( 'WPSEO_Options', 'get' ) ) {
		return (bool) WPSEO_Options::get( 'opengraph', true );
	}
	$social = get_option( 'wpseo_social', array() );
	if ( is_array( $social ) && array_key_exists( 'opengraph', $social ) ) {
		return ! empty( $social['opengraph'] );
	}
	return true;
}

function gazettenews_share_image_data( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_queried_object_id();
	if ( $post_id && has_post_thumbnail( $post_id ) ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'gazettenews-featured' );
		if ( $src && ! empty( $src[0] ) ) {
			return array(
				'url'    => $src[0],
				'width'  => (int) $src[1],
				'height' => (int) $src[2],
				'alt'    => get_the_title( $post_id ),
			);
		}
	}
	$logo = absint( get_theme_mod( 'custom_logo' ) );
	if ( $logo ) {
		$src = wp_get_attachment_image_src( $logo, 'full' );
		if ( $src && ! empty( $src[0] ) ) {
			return array(
				'url'    => $src[0],
				'width'  => (int) $src[1],
				'height' => (int) $src[2],
				'alt'    => get_bloginfo( 'name' ),
			);
		}
	}
	return array();
}

function gazettenews_share_image_url( $post_id = 0 ) {
	$data = gazettenews_share_image_data( $post_id );
	return ! empty( $data['url'] ) ? $data['url'] : '';
}

function gazettenews_plain_description( $post = null ) {
	$post = $post ? $post : get_post();
	if ( ! $post || ( empty( $post->post_content ) && empty( $post->post_excerpt ) ) ) {
		return get_bloginfo( 'description' );
	}
	$raw = ! empty( $post->post_excerpt ) ? $post->post_excerpt : $post->post_content;
	$raw = wp_strip_all_tags( strip_shortcodes( $raw ) );
	$raw = wp_trim_words( $raw, 30, '…' );
	return $raw ? $raw : get_bloginfo( 'description' );
}

function gazettenews_social_meta() {
	if ( gazettenews_yoast_handles_og() ) {
		return;
	}

	$img   = gazettenews_share_image_data();
	$title = wp_get_document_title();
	$desc  = get_bloginfo( 'description' );
	$url   = home_url( '/' );
	$type  = 'website';

	if ( is_singular() ) {
		$post = get_queried_object();
		$url  = get_permalink();
		$type = is_singular( 'post' ) ? 'article' : 'website';
		$desc = gazettenews_plain_description( $post );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		$url  = ( $term && ! is_wp_error( $term ) ) ? get_term_link( $term ) : home_url( '/' );
		if ( is_wp_error( $url ) ) {
			$url = home_url( '/' );
		} else {
			$title = single_term_title( '', false ) . ' — ' . get_bloginfo( 'name' );
		}
		if ( $term && ! empty( $term->description ) ) {
			$desc = wp_trim_words( wp_strip_all_tags( $term->description ), 30, '…' );
		}
	}

	echo '<meta property="og:locale" content="' . esc_attr( str_replace( '-', '_', get_locale() ) ) . '">' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
	if ( ! empty( $img['url'] ) ) {
		echo '<meta property="og:image" content="' . esc_url( $img['url'] ) . '">' . "\n";
		echo '<meta property="og:image:secure_url" content="' . esc_url( set_url_scheme( $img['url'], 'https' ) ) . '">' . "\n";
		if ( ! empty( $img['width'] ) ) {
			echo '<meta property="og:image:width" content="' . esc_attr( (string) $img['width'] ) . '">' . "\n";
		}
		if ( ! empty( $img['height'] ) ) {
			echo '<meta property="og:image:height" content="' . esc_attr( (string) $img['height'] ) . '">' . "\n";
		}
		if ( ! empty( $img['alt'] ) ) {
			echo '<meta property="og:image:alt" content="' . esc_attr( $img['alt'] ) . '">' . "\n";
		}
		echo '<meta name="twitter:image" content="' . esc_url( $img['url'] ) . '">' . "\n";
	}
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
}
add_action( 'wp_head', 'gazettenews_social_meta', 5 );

function gazettenews_yoast_og_image( $image ) {
	if ( $image ) {
		return $image;
	}
	$url = gazettenews_share_image_url();
	return $url ? $url : $image;
}
add_filter( 'wpseo_opengraph_image', 'gazettenews_yoast_og_image' );
add_filter( 'wpseo_twitter_image', 'gazettenews_yoast_og_image' );

function gazettenews_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	if ( function_exists( 'yoast_breadcrumb' ) ) {
		yoast_breadcrumb( '<nav class="gn-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'gazettenews' ) . '">', '</nav>' );
	}
}
