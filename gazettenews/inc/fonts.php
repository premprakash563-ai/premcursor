<?php
/**
 * Font stacks and per-section typography.
 *
 * @package GazetteNews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gazettenews_font_choices() {
	return array(
		''              => __( 'Theme default', 'gazettenews' ),
		'noto-sans'     => 'Noto Sans Devanagari',
		'noto-serif'    => 'Noto Serif Devanagari',
		'mukta'         => 'Mukta',
		'hind'          => 'Hind',
		'tiro'          => 'Tiro Devanagari Hindi',
		'inter'         => 'Inter',
		'poppins'       => 'Poppins',
		'roboto'        => 'Roboto',
		'oswald'        => 'Oswald',
		'merriweather'  => 'Merriweather',
		'source-serif'  => 'Source Serif 4',
	);
}

function gazettenews_font_stacks() {
	return array(
		'noto-sans'    => '"Noto Sans Devanagari", "Inter", "Segoe UI", sans-serif',
		'noto-serif'   => '"Noto Serif Devanagari", "Source Serif 4", Georgia, serif',
		'mukta'        => 'Mukta, "Noto Sans Devanagari", sans-serif',
		'hind'         => 'Hind, "Noto Sans Devanagari", sans-serif',
		'tiro'         => '"Tiro Devanagari Hindi", "Noto Serif Devanagari", serif',
		'inter'        => 'Inter, "Noto Sans Devanagari", "Segoe UI", sans-serif',
		'poppins'      => 'Poppins, "Noto Sans Devanagari", sans-serif',
		'roboto'       => 'Roboto, "Noto Sans Devanagari", sans-serif',
		'oswald'       => 'Oswald, "Noto Sans Devanagari", sans-serif',
		'merriweather' => 'Merriweather, "Source Serif 4", Georgia, serif',
		'source-serif' => '"Source Serif 4", Georgia, "Times New Roman", serif',
	);
}

function gazettenews_font_google_map() {
	return array(
		'noto-sans'    => 'Noto+Sans+Devanagari:wght@400;600;700;800',
		'noto-serif'   => 'Noto+Serif+Devanagari:wght@400;600;700',
		'mukta'        => 'Mukta:wght@400;600;700',
		'hind'         => 'Hind:wght@400;600;700',
		'tiro'         => 'Tiro+Devanagari+Hindi:wght@400;600;700',
		'inter'        => 'Inter:wght@400;500;600;700;800',
		'poppins'      => 'Poppins:wght@400;600;700',
		'roboto'       => 'Roboto:wght@400;500;700',
		'oswald'       => 'Oswald:wght@500;600;700',
		'merriweather' => 'Merriweather:wght@400;700',
		'source-serif' => 'Source+Serif+4:opsz,wght@8..60,600;8..60,700',
	);
}

function gazettenews_sanitize_font_key( $value ) {
	$value = sanitize_key( (string) $value );
	$ok    = gazettenews_font_choices();
	return isset( $ok[ $value ] ) ? $value : '';
}

function gazettenews_sanitize_body_size( $value ) {
	$size = gazettenews_sanitize_font_size( $value, 12, 22 );
	return $size ? $size : 15;
}

function gazettenews_sanitize_menu_size( $value ) {
	$size = gazettenews_sanitize_font_size( $value, 11, 22 );
	return $size ? $size : 14;
}

function gazettenews_sanitize_single_title_size( $value ) {
	$size = gazettenews_sanitize_font_size( $value, 22, 64 );
	return $size ? $size : 42;
}

function gazettenews_font_stack( $key ) {
	$key    = gazettenews_sanitize_font_key( $key );
	$stacks = gazettenews_font_stacks();
	return ( $key && isset( $stacks[ $key ] ) ) ? $stacks[ $key ] : '';
}

function gazettenews_used_font_keys() {
	$keys = array(
		get_theme_mod( 'gazettenews_font_body', '' ),
		get_theme_mod( 'gazettenews_font_heading', '' ),
		get_theme_mod( 'gazettenews_font_menu', '' ),
		get_theme_mod( 'gazettenews_font_logo', '' ),
		get_theme_mod( 'gazettenews_font_breaking', '' ),
		get_theme_mod( 'gazettenews_font_footer', '' ),
		get_theme_mod( 'gazettenews_font_single_title', '' ),
		get_theme_mod( 'gazettenews_font_single_body', '' ),
	);
	$sections = get_option( 'gazettenews_home_sections', array() );
	if ( is_array( $sections ) ) {
		foreach ( $sections as $row ) {
			if ( ! empty( $row['font'] ) ) {
				$keys[] = $row['font'];
			}
		}
	}
	$out = array();
	foreach ( $keys as $key ) {
		$key = gazettenews_sanitize_font_key( $key );
		if ( $key ) {
			$out[ $key ] = $key;
		}
	}
	return array_values( $out );
}

function gazettenews_google_fonts_url() {
	$map      = gazettenews_font_google_map();
	$families = array(
		'Noto+Sans+Devanagari:wght@400;600;700;800',
		'Inter:wght@400;500;600;700;800',
		'Source+Serif+4:opsz,wght@8..60,600;8..60,700',
	);
	$have = array_flip( $families );
	foreach ( gazettenews_used_font_keys() as $key ) {
		if ( isset( $map[ $key ] ) && ! isset( $have[ $map[ $key ] ] ) ) {
			$families[]        = $map[ $key ];
			$have[ $map[ $key ] ] = true;
		}
	}
	return 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $families ) . '&display=swap';
}

function gazettenews_font_css_vars() {
	$map = array(
		'--gn-sans'              => array( 'gazettenews_font_body', '"Noto Sans Devanagari", "Inter", "Segoe UI", Roboto, Arial, sans-serif' ),
		'--gn-heading-font'      => array( 'gazettenews_font_heading', 'var(--gn-serif)' ),
		'--gn-menu-font'         => array( 'gazettenews_font_menu', 'var(--gn-sans)' ),
		'--gn-logo-font'         => array( 'gazettenews_font_logo', 'var(--gn-sans)' ),
		'--gn-breaking-font'     => array( 'gazettenews_font_breaking', 'var(--gn-sans)' ),
		'--gn-footer-font'       => array( 'gazettenews_font_footer', 'var(--gn-sans)' ),
		'--gn-single-title-font' => array( 'gazettenews_font_single_title', 'var(--gn-serif)' ),
		'--gn-single-body-font'  => array( 'gazettenews_font_single_body', 'var(--gn-sans)' ),
	);
	$css = '';
	foreach ( $map as $var => $pair ) {
		$stack = gazettenews_font_stack( get_theme_mod( $pair[0], '' ) );
		$css  .= $var . ':' . ( $stack ? $stack : $pair[1] ) . ';';
	}
	$body = gazettenews_sanitize_font_size( get_theme_mod( 'gazettenews_font_body_size', 15 ), 12, 22 );
	if ( ! $body ) {
		$body = 15;
	}
	$menu = gazettenews_sanitize_font_size( get_theme_mod( 'gazettenews_font_menu_size', 14 ), 11, 22 );
	if ( ! $menu ) {
		$menu = 14;
	}
	$title = gazettenews_sanitize_font_size( get_theme_mod( 'gazettenews_font_single_title_size', 42 ), 22, 64 );
	if ( ! $title ) {
		$title = 42;
	}
	$css .= '--gn-body-size:' . $body . 'px;';
	$css .= '--gn-menu-size:' . $menu . 'px;';
	$css .= '--gn-single-title-size:' . $title . 'px;';
	return $css;
}

function gazettenews_section_font_attr( $section ) {
	$parts = array();
	$stack = gazettenews_font_stack( isset( $section['font'] ) ? $section['font'] : '' );
	if ( $stack ) {
		$parts[] = '--gn-sec-font:' . $stack;
	}
	$title = isset( $section['title_size'] ) ? absint( $section['title_size'] ) : 0;
	if ( $title >= 12 && $title <= 48 ) {
		$parts[] = '--gn-sec-title:' . $title . 'px';
	}
	$text = isset( $section['text_size'] ) ? absint( $section['text_size'] ) : 0;
	if ( $text >= 11 && $text <= 28 ) {
		$parts[] = '--gn-sec-text:' . $text . 'px';
	}
	$attr = ' class="gn-sec-font"';
	if ( $parts ) {
		$attr .= ' style="' . esc_attr( implode( ';', $parts ) ) . '"';
	}
	return $attr;
}
