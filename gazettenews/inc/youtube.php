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
	return trim( (string) get_theme_mod( 'gazettenews_youtube_api_key', '' ) );
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

function gazettenews_youtube_channel() {
	$channel = trim( (string) get_option( 'gazettenews_youtube_channel', '' ) );
	if ( $channel ) {
		return $channel;
	}
	return trim( (string) get_theme_mod( 'gazettenews_social_youtube', '' ) );
}

function gazettenews_sanitize_youtube_channel( $value ) {
	return sanitize_text_field( (string) $value );
}

function gazettenews_youtube_bust_cache() {
	update_option( 'gazettenews_yt_cache_v', time(), false );
}

function gazettenews_youtube_request( $endpoint, $args ) {
	$key = gazettenews_youtube_api_key();
	if ( ! $key ) {
		return array();
	}
	$args['key'] = $key;
	$url         = add_query_arg( $args, 'https://www.googleapis.com/youtube/v3/' . $endpoint );
	$ver         = absint( get_option( 'gazettenews_yt_cache_v', 1 ) );
	$cache_key   = 'gn_yt_' . $ver . '_' . md5( $url );
	$cached      = get_transient( $cache_key );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$allow = is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ! empty( $GLOBALS['gazettenews_yt_fetch'] );
	if ( ! $allow ) {
		return array();
	}

	$response = wp_remote_get(
		$url,
		array(
			'timeout'     => 4,
			'redirection' => 2,
		)
	);
	if ( is_wp_error( $response ) ) {
		set_transient( $cache_key, array(), 10 * MINUTE_IN_SECONDS );
		return array();
	}
	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( 200 !== $code || ! is_array( $body ) ) {
		$msg = '';
		if ( is_array( $body ) && ! empty( $body['error']['message'] ) ) {
			$msg = (string) $body['error']['message'];
		} elseif ( $code ) {
			$msg = sprintf( 'YouTube API HTTP %d', absint( $code ) );
		}
		if ( $msg ) {
			update_option( 'gazettenews_youtube_last_error', $msg, false );
		}
		set_transient( $cache_key, array(), 10 * MINUTE_IN_SECONDS );
		return array();
	}

	set_transient( $cache_key, $body, 12 * HOUR_IN_SECONDS );
	return $body;
}

function gazettenews_youtube_playlist_ids( $playlist_id, $max = 50 ) {
	$ids  = array();
	$max  = min( 8, max( 1, absint( $max ) ) );
	$page = '';
	while ( count( $ids ) < $max ) {
		$args = array(
			'part'       => 'contentDetails',
			'maxResults' => min( 50, $max - count( $ids ) ),
			'playlistId' => $playlist_id,
		);
		if ( $page ) {
			$args['pageToken'] = $page;
		}
		$body = gazettenews_youtube_request( 'playlistItems', $args );
		if ( empty( $body['items'] ) || ! is_array( $body['items'] ) ) {
			break;
		}
		foreach ( $body['items'] as $item ) {
			if ( ! empty( $item['contentDetails']['videoId'] ) ) {
				$ids[] = $item['contentDetails']['videoId'];
			}
		}
		if ( empty( $body['nextPageToken'] ) ) {
			break;
		}
		$page = $body['nextPageToken'];
	}
	return array_slice( array_unique( $ids ), 0, $max );
}

function gazettenews_youtube_looks_like_channel( $raw ) {
	$raw = trim( (string) $raw );
	if ( '' === $raw ) {
		return false;
	}
	if ( preg_match( '/(?:youtube\.com\/(channel\/|@|c\/|user\/)|^[UCu]C[A-Za-z0-9_-]{20,}$|^@[A-Za-z0-9._-]+$)/', $raw ) ) {
		return true;
	}
	return false;
}

function gazettenews_youtube_resolve_channel( $raw ) {
	$raw = trim( (string) $raw );
	if ( '' === $raw || ! gazettenews_youtube_api_key() ) {
		return array();
	}

	$args = array( 'part' => 'id,snippet,contentDetails' );
	if ( preg_match( '/channel\/(UC[A-Za-z0-9_-]+)/', $raw, $m ) || preg_match( '/^(UC[A-Za-z0-9_-]{20,})$/', $raw, $m ) ) {
		$args['id'] = $m[1];
	} elseif ( preg_match( '/youtube\.com\/@([A-Za-z0-9._-]+)/', $raw, $m ) || preg_match( '/^@([A-Za-z0-9._-]+)$/', $raw, $m ) ) {
		$args['forHandle'] = $m[1];
	} elseif ( preg_match( '/youtube\.com\/user\/([A-Za-z0-9._-]+)/', $raw, $m ) ) {
		$args['forUsername'] = $m[1];
	} elseif ( preg_match( '/youtube\.com\/c\/([A-Za-z0-9._-]+)/', $raw, $m ) ) {
		$search = gazettenews_youtube_request(
			'search',
			array(
				'part'       => 'snippet',
				'type'       => 'channel',
				'q'          => $m[1],
				'maxResults' => 1,
			)
		);
		if ( ! empty( $search['items'][0]['snippet']['channelId'] ) ) {
			$args['id'] = $search['items'][0]['snippet']['channelId'];
		} elseif ( ! empty( $search['items'][0]['id']['channelId'] ) ) {
			$args['id'] = $search['items'][0]['id']['channelId'];
		} else {
			return array();
		}
	} else {
		$args['forHandle'] = ltrim( $raw, '@' );
	}

	$body = gazettenews_youtube_request( 'channels', $args );
	if ( empty( $body['items'][0] ) && ! empty( $args['forHandle'] ) ) {
		$search = gazettenews_youtube_request(
			'search',
			array(
				'part'       => 'snippet',
				'type'       => 'channel',
				'q'          => '@' . $args['forHandle'],
				'maxResults' => 1,
			)
		);
		$cid = '';
		if ( ! empty( $search['items'][0]['id']['channelId'] ) ) {
			$cid = $search['items'][0]['id']['channelId'];
		}
		if ( $cid ) {
			$body = gazettenews_youtube_request(
				'channels',
				array(
					'part' => 'id,snippet,contentDetails',
					'id'   => $cid,
				)
			);
		}
	}

	if ( empty( $body['items'][0] ) ) {
		return array();
	}
	$item = $body['items'][0];
	return array(
		'id'      => isset( $item['id'] ) ? $item['id'] : '',
		'title'   => isset( $item['snippet']['title'] ) ? $item['snippet']['title'] : '',
		'uploads' => isset( $item['contentDetails']['relatedPlaylists']['uploads'] ) ? $item['contentDetails']['relatedPlaylists']['uploads'] : '',
	);
}

function gazettenews_youtube_channel_video_ids( $channel_raw, $max = 50 ) {
	$info = gazettenews_youtube_resolve_channel( $channel_raw );
	if ( empty( $info['uploads'] ) ) {
		return array();
	}
	return gazettenews_youtube_playlist_ids( $info['uploads'], $max );
}

function gazettenews_youtube_probe() {
	if ( ! gazettenews_youtube_api_key() ) {
		return array(
			'ok'    => false,
			'count' => 0,
			'title' => '',
			'error' => __( 'Save a YouTube Data API v3 key first.', 'gazettenews' ),
		);
	}
	$channel = gazettenews_youtube_channel();
	if ( ! $channel ) {
		return array(
			'ok'    => false,
			'count' => 0,
			'title' => '',
			'error' => __( 'Paste your channel URL or @handle so videos can load automatically.', 'gazettenews' ),
		);
	}
	$info = gazettenews_youtube_resolve_channel( $channel );
	if ( empty( $info['uploads'] ) ) {
		$err = trim( (string) get_option( 'gazettenews_youtube_last_error', '' ) );
		return array(
			'ok'    => false,
			'count' => 0,
			'title' => '',
			'error' => $err ? $err : __( 'Channel not found. Use a public channel URL such as https://www.youtube.com/@YourChannel', 'gazettenews' ),
		);
	}
	$ids = gazettenews_youtube_playlist_ids( $info['uploads'], 8 );
	return array(
		'ok'    => true,
		'count' => count( $ids ),
		'title' => $info['title'],
		'error' => '',
	);
}

function gazettenews_youtube_hydrate( $items ) {
	$ids = array();
	foreach ( $items as $item ) {
		if ( ! empty( $item['id'] ) ) {
			$ids[] = $item['id'];
		}
	}
	$ids = array_slice( array_unique( $ids ), 0, 8 );
	if ( empty( $ids ) || ! gazettenews_youtube_api_key() ) {
		return $items;
	}

	$map    = array();
	$chunks = array_chunk( $ids, 50 );
	foreach ( $chunks as $chunk ) {
		$body = gazettenews_youtube_request(
			'videos',
			array(
				'part' => 'snippet,contentDetails',
				'id'   => implode( ',', $chunk ),
			)
		);
		if ( empty( $body['items'] ) || ! is_array( $body['items'] ) ) {
			continue;
		}
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
