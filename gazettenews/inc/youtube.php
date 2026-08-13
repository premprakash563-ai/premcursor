<?php
/**
 * YouTube Data API v3 helpers.
 *
 * @package GazetteNews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gazettenews_youtube_api_key() {
	$key = trim( (string) get_option( 'gazettenews_youtube_api_key', '' ) );
	if ( $key ) {
		return $key;
	}
	$mod = trim( (string) get_theme_mod( 'gazettenews_youtube_api_key', '' ) );
	if ( $mod ) {
		update_option( 'gazettenews_youtube_api_key', $mod, false );
		return $mod;
	}
	return '';
}

function gazettenews_save_youtube_api_key( $key ) {
	$key = preg_replace( '/\s+/', '', sanitize_text_field( (string) $key ) );
	update_option( 'gazettenews_youtube_api_key', $key, false );
	if ( $key ) {
		set_theme_mod( 'gazettenews_youtube_api_key', $key );
	} else {
		remove_theme_mod( 'gazettenews_youtube_api_key' );
	}
}

function gazettenews_mask_api_key( $key ) {
	$key = (string) $key;
	$len = strlen( $key );
	if ( $len < 8 ) {
		return $len ? str_repeat( '•', $len ) : '';
	}
	return substr( $key, 0, 4 ) . str_repeat( '•', max( 6, $len - 8 ) ) . substr( $key, -4 );
}

function gazettenews_sanitize_youtube_api_key( $value ) {
	$value = preg_replace( '/\s+/', '', sanitize_text_field( (string) $value ) );
	if ( '' === $value ) {
		return gazettenews_youtube_api_key();
	}
	return $value;
}

add_action( 'init', 'gazettenews_youtube_api_key' );

function gazettenews_youtube_duration( $iso ) {
	if ( ! $iso || ! preg_match( '/^PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?$/', $iso, $m ) ) {
		return '';
	}
	$h = isset( $m[1] ) ? absint( $m[1] ) : 0;
	$i = isset( $m[2] ) ? absint( $m[2] ) : 0;
	$s = isset( $m[3] ) ? absint( $m[3] ) : 0;
	if ( $h ) {
		return sprintf( '%d:%02d:%02d', $h, $i, $s );
	}
	return sprintf( '%02d:%02d', $i, $s );
}

function gazettenews_youtube_request( $endpoint, $args ) {
	$key = gazettenews_youtube_api_key();
	if ( ! $key ) {
		return array();
	}
	$args['key'] = $key;
	$url         = add_query_arg( $args, 'https://www.googleapis.com/youtube/v3/' . $endpoint );
	$cache_key   = 'gn_yt_' . md5( $url );
	$cached      = get_transient( $cache_key );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$response = wp_remote_get( $url, array( 'timeout' => 12 ) );
	if ( is_wp_error( $response ) ) {
		return array();
	}
	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( 200 !== $code || ! is_array( $body ) ) {
		return array();
	}

	set_transient( $cache_key, $body, 6 * HOUR_IN_SECONDS );
	return $body;
}

function gazettenews_youtube_playlist_ids( $playlist_id, $max = 15 ) {
	$body = gazettenews_youtube_request(
		'playlistItems',
		array(
			'part'       => 'contentDetails',
			'maxResults' => min( 50, max( 1, absint( $max ) ) ),
			'playlistId' => $playlist_id,
		)
	);
	$ids = array();
	if ( empty( $body['items'] ) || ! is_array( $body['items'] ) ) {
		return $ids;
	}
	foreach ( $body['items'] as $item ) {
		if ( ! empty( $item['contentDetails']['videoId'] ) ) {
			$ids[] = $item['contentDetails']['videoId'];
		}
	}
	return $ids;
}

function gazettenews_youtube_hydrate( $items ) {
	$ids = array();
	foreach ( $items as $item ) {
		if ( ! empty( $item['id'] ) ) {
			$ids[] = $item['id'];
		}
	}
	$ids = array_slice( array_unique( $ids ), 0, 50 );
	if ( empty( $ids ) || ! gazettenews_youtube_api_key() ) {
		return $items;
	}

	$body = gazettenews_youtube_request(
		'videos',
		array(
			'part' => 'snippet,contentDetails',
			'id'   => implode( ',', $ids ),
		)
	);
	$map = array();
	if ( ! empty( $body['items'] ) && is_array( $body['items'] ) ) {
		foreach ( $body['items'] as $vid ) {
			$id = isset( $vid['id'] ) ? $vid['id'] : '';
			if ( ! $id ) {
				continue;
			}
			$map[ $id ] = array(
				'title'    => isset( $vid['snippet']['title'] ) ? $vid['snippet']['title'] : '',
				'duration' => isset( $vid['contentDetails']['duration'] ) ? gazettenews_youtube_duration( $vid['contentDetails']['duration'] ) : '',
				'thumb'    => isset( $vid['snippet']['thumbnails']['medium']['url'] ) ? $vid['snippet']['thumbnails']['medium']['url'] : '',
			);
		}
	}

	foreach ( $items as $i => $item ) {
		$id = $item['id'];
		if ( isset( $map[ $id ] ) ) {
			if ( empty( $item['title'] ) || $item['title'] === $id ) {
				$items[ $i ]['title'] = $map[ $id ]['title'];
			}
			$items[ $i ]['duration'] = $map[ $id ]['duration'];
			if ( ! empty( $map[ $id ]['thumb'] ) ) {
				$items[ $i ]['thumb'] = $map[ $id ]['thumb'];
			}
		}
	}
	return $items;
}
