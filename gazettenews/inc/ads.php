<?php
/**
 * Header advertisement helper.
 *
 * @package GazetteNews
 */

function gazettenews_header_ad() {
	$image = get_theme_mod( 'gazettenews_header_ad_image' );
	$link  = get_theme_mod( 'gazettenews_header_ad_url' );
	$code  = get_theme_mod( 'gazettenews_header_ad_code' );
	echo '<div class="header-ad">';
	echo '<div class="ad-kicker">' . esc_html__( '- Advertisement -', 'gazettenews' ) . '</div>';
	if ( $code ) {
		echo wp_kses_post( $code );
	} elseif ( $image ) {
		$html = '<img src="' . esc_url( $image ) . '" alt="">';
		if ( $link ) {
			$html = '<a href="' . esc_url( $link ) . '" rel="nofollow sponsored" target="_blank">' . $html . '</a>';
		}
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} elseif ( is_active_sidebar( 'header-ad' ) ) {
		dynamic_sidebar( 'header-ad' );
	} else {
		echo '<div class="ad-placeholder header-ad-ph">' . esc_html__( 'Header ad 728×90 — Customizer → Header advertisement', 'gazettenews' ) . '</div>';
	}
	echo '</div>';
}
