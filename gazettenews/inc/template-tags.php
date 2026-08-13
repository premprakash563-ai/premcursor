<?php
/**
 * Template helper functions.
 *
 * @package GazetteNews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gazettenews_posted_on() {
	$time = '<time class="entry-date published" datetime="' . esc_attr( get_the_date( DATE_W3C ) ) . '">' . esc_html( get_the_date() ) . '</time>';
	echo '<span class="meta-date">' . $time . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

function gazettenews_posted_by( $avatar = false ) {
	echo '<span class="meta-author">';
	if ( $avatar ) {
		echo get_avatar( get_the_author_meta( 'ID' ), 22 );
	}
	echo '<a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>';
}

function gazettenews_get_views( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return absint( get_post_meta( $post_id, '_gn_views', true ) );
}

function gazettenews_count_view() {
	if ( ! is_singular( 'post' ) || is_preview() ) {
		return;
	}
	$id = get_queried_object_id();
	if ( ! $id ) {
		return;
	}
	$cookie = 'gn_viewed_' . $id;
	if ( isset( $_COOKIE[ $cookie ] ) ) {
		return;
	}
	update_post_meta( $id, '_gn_views', gazettenews_get_views( $id ) + 1 );
	if ( ! headers_sent() ) {
		setcookie( $cookie, '1', time() + DAY_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
	}
}
add_action( 'template_redirect', 'gazettenews_count_view' );

function gazettenews_comments_count( $compact = false ) {
	$n = get_comments_number();
	if ( $compact ) {
		echo '<span class="meta-comments">' . esc_html( number_format_i18n( $n ) ) . '</span>';
		return;
	}
	echo '<span class="meta-comments"><a href="' . esc_url( get_comments_link() ) . '">' . esc_html( sprintf( _n( '%s comment', '%s comments', $n, 'gazettenews' ), number_format_i18n( $n ) ) ) . '</a></span>';
}

function gazettenews_views_count( $compact = false ) {
	$n = gazettenews_get_views();
	$label = $compact
		? number_format_i18n( $n )
		: sprintf( _n( '%s view', '%s views', $n, 'gazettenews' ), number_format_i18n( $n ) );
	echo '<span class="meta-views">' . gazettenews_svg_icon( 'eye' ) . ' ' . esc_html( $label ) . '</span>';
}

function gazettenews_svg_icon( $name ) {
	$icons = array(
		'facebook'  => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M14 8h3V4h-3c-2.8 0-5 2.2-5 5v2H7v4h2v8h4v-8h3l1-4h-4V9c0-.6.4-1 1-1z"/></svg>',
		'twitter'   => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M18.9 2H22l-6.8 7.8L23 22h-6.5l-5.1-7.1L5.7 22H2.6l7.3-8.4L1 2h6.6l4.6 6.5L18.9 2zm-1.1 18h1.8L6.3 3.9H4.4L17.8 20z"/></svg>',
		'instagram' => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5zm5 5.2A4.8 4.8 0 1 0 16.8 12 4.8 4.8 0 0 0 12 7.2zm0 7.9A3.1 3.1 0 1 1 15.1 12 3.1 3.1 0 0 1 12 15.1zM18.4 6.1a1.2 1.2 0 1 0 1.2 1.2 1.2 1.2 0 0 0-1.2-1.2z"/></svg>',
		'youtube'   => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M23 12.2s0-3.2-.4-4.6c-.2-.8-.8-1.4-1.6-1.6C19.4 5.6 12 5.6 12 5.6s-7.4 0-9 .4c-.8.2-1.4.8-1.6 1.6C1 9 1 12.2 1 12.2s0 3.2.4 4.6c.2.8.8 1.4 1.6 1.6 1.6.4 9 .4 9 .4s7.4 0 9-.4c.8-.2 1.4-.8 1.6-1.6.4-1.4.4-4.6.4-4.6zM9.8 15.6V8.8l6.2 3.4-6.2 3.4z"/></svg>',
		'linkedin'  => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M6.5 9H3.7v11.3h2.8V9zM5.1 3.3A1.8 1.8 0 1 0 5.1 7a1.8 1.8 0 0 0 0-3.7zM20.3 13.2c0-3.3-1.8-4.8-4.1-4.8-1.9 0-2.7 1-3.2 1.8V9H10.2c0 1.6 0 11.3 0 11.3h2.8v-6.3c0-.3 0-.7.1-1 .3-.7.9-1.4 2-1.4 1.4 0 2 1.1 2 2.6v6.1h2.8V13.2z"/></svg>',
		'telegram'  => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M21.5 4.4 18.7 20c-.2.9-.8 1.1-1.6.7l-4.4-3.2-2.1 2c-.2.3-.5.5-1 .5l.3-4.5 8.2-7.4c.4-.3-.1-.5-.6-.2l-10.1 6.4-4.4-1.4c-1-.3-1-.9.2-1.3L20.2 3.6c.8-.3 1.5.2 1.3.8z"/></svg>',
		'whatsapp'  => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.6 14.3c-.2.7-1.3 1.2-1.8 1.3-.5.1-1 .2-3.3-.7-2.8-1.1-4.6-4-4.7-4.2-.2-.2-1.3-1.7-1.3-3.3 0-1.5.8-2.3 1.1-2.6.3-.3.7-.4 1-.4h.7c.2 0 .5 0 .7.6.3.7 1 2.4 1.1 2.5.1.2.1.4 0 .6-.1.2-.2.4-.4.6l-.5.6c-.2.2-.3.4-.1.7.2.4.8 1.4 1.8 2.2 1.2 1 2.2 1.4 2.6 1.5.3.1.6.1.8-.1.2-.2.9-1 1.1-1.4.2-.3.5-.3.8-.2.3.1 2 .9 2.3 1.1.3.2.5.2.6.4.1.1.1.8-.1 1.5z"/></svg>',
		'email'     => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M3 5h18a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1zm9 8 8-5H4l8 5zm0 2L4 10v8h16v-8l-8 5z"/></svg>',
		'website'   => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm7.4 9h-3.2a15 15 0 0 0-1.3-5.2A8.1 8.1 0 0 1 19.4 11zM12 4c.7 0 2.2 2.1 2.8 6H9.2C9.8 6.1 11.3 4 12 4zM4.6 13h3.2a15 15 0 0 0 1.3 5.2A8.1 8.1 0 0 1 4.6 13zm3.2-2H4.6A8.1 8.1 0 0 1 9.1 5.8 15 15 0 0 0 7.8 11zM12 20c-.7 0-2.2-2.1-2.8-6h5.6c-.6 3.9-2.1 6-2.8 6zm2.9-1.8A15 15 0 0 0 16.2 13h3.2a8.1 8.1 0 0 1-4.5 5.2z"/></svg>',
		'eye'       => '<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path fill="currentColor" d="M12 5c5.5 0 9.5 4.5 10.5 7-1 2.5-5 7-10.5 7S2.5 14.5 1.5 12C2.5 9.5 6.5 5 12 5zm0 3.5A3.5 3.5 0 1 0 15.5 12 3.5 3.5 0 0 0 12 8.5z"/></svg>',
		'copy'      => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M8 7V4h12v12h-3V7H8zm-2 2h12v12H6V9z"/></svg>',
	);
	return isset( $icons[ $name ] ) ? $icons[ $name ] : '';
}

function gazettenews_share_links( $context = 'default' ) {
	$url   = rawurlencode( get_permalink() );
	$title = rawurlencode( get_the_title() );
	$plain = get_permalink();
	$links = array(
		'facebook' => array( __( 'Facebook', 'gazettenews' ), 'https://www.facebook.com/sharer/sharer.php?u=' . $url ),
		'twitter'  => array( __( 'X', 'gazettenews' ), 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title ),
		'whatsapp' => array( __( 'WhatsApp', 'gazettenews' ), 'https://api.whatsapp.com/send?text=' . $title . '%20' . $url ),
		'telegram' => array( __( 'Telegram', 'gazettenews' ), 'https://t.me/share/url?url=' . $url . '&text=' . $title ),
		'linkedin' => array( __( 'LinkedIn', 'gazettenews' ), 'https://www.linkedin.com/shareArticle?mini=true&url=' . $url . '&title=' . $title ),
		'email'    => array( __( 'Email', 'gazettenews' ), 'mailto:?subject=' . $title . '&body=' . $url ),
	);
	echo '<div class="gn-share gn-share-' . esc_attr( $context ) . '">';
	echo '<span>' . esc_html__( 'Share', 'gazettenews' ) . '</span>';
	foreach ( $links as $network => $item ) {
		echo '<a class="share-' . esc_attr( $network ) . '" href="' . esc_url( $item[1] ) . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr( $item[0] ) . '">' . gazettenews_svg_icon( $network ) . '</a>';
	}
	echo '<button type="button" class="share-copy" data-url="' . esc_attr( $plain ) . '" data-done="' . esc_attr__( 'Copied', 'gazettenews' ) . '" aria-label="' . esc_attr__( 'Copy link', 'gazettenews' ) . '">' . gazettenews_svg_icon( 'copy' ) . '</button>';
	echo '</div>';
}

function gazettenews_reporter_name() {
	$custom = get_post_meta( get_the_ID(), '_gn_reporter', true );
	if ( $custom ) {
		return $custom;
	}
	return get_the_author();
}

function gazettenews_publisher_name() {
	$custom = get_post_meta( get_the_ID(), '_gn_publisher', true );
	if ( $custom ) {
		return $custom;
	}
	$def = get_theme_mod( 'gazettenews_publisher_name' );
	return $def ? $def : get_bloginfo( 'name' );
}

function gazettenews_single_byline() {
	echo '<div class="byline-row">';
	echo '<span><strong>' . esc_html__( 'Report By :', 'gazettenews' ) . '</strong> ' . esc_html( gazettenews_reporter_name() ) . '</span>';
	echo '<span><strong>' . esc_html__( 'Published By :', 'gazettenews' ) . '</strong> ' . esc_html( gazettenews_publisher_name() ) . '</span>';
	echo '<span>' . esc_html( get_the_date() ) . '</span>';
	echo '</div>';
}

function gazettenews_add_byline_metabox() {
	add_meta_box(
		'gazettenews_byline',
		__( 'Reporter & publisher', 'gazettenews' ),
		'gazettenews_byline_metabox',
		'post',
		'side'
	);
}
add_action( 'add_meta_boxes', 'gazettenews_add_byline_metabox' );

function gazettenews_byline_metabox( $post ) {
	wp_nonce_field( 'gazettenews_byline', 'gazettenews_byline_nonce' );
	$reporter  = get_post_meta( $post->ID, '_gn_reporter', true );
	$publisher = get_post_meta( $post->ID, '_gn_publisher', true );
	echo '<p><label>' . esc_html__( 'Report By', 'gazettenews' ) . '</label>';
	echo '<input class="widefat" name="gn_reporter" value="' . esc_attr( $reporter ) . '" placeholder="' . esc_attr__( 'Leave empty to use author', 'gazettenews' ) . '"></p>';
	echo '<p><label>' . esc_html__( 'Published By', 'gazettenews' ) . '</label>';
	echo '<input class="widefat" name="gn_publisher" value="' . esc_attr( $publisher ) . '" placeholder="' . esc_attr__( 'Leave empty for default', 'gazettenews' ) . '"></p>';
}

function gazettenews_save_byline_metabox( $post_id ) {
	if ( ! isset( $_POST['gazettenews_byline_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gazettenews_byline_nonce'] ) ), 'gazettenews_byline' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['gn_reporter'] ) ) {
		update_post_meta( $post_id, '_gn_reporter', sanitize_text_field( wp_unslash( $_POST['gn_reporter'] ) ) );
	}
	if ( isset( $_POST['gn_publisher'] ) ) {
		update_post_meta( $post_id, '_gn_publisher', sanitize_text_field( wp_unslash( $_POST['gn_publisher'] ) ) );
	}
}
add_action( 'save_post_post', 'gazettenews_save_byline_metabox' );

function gazettenews_entry_meta( $show_comments = true, $show_views = true ) {
	echo '<div class="entry-meta">';
	gazettenews_posted_by();
	echo '<span class="meta-sep">—</span>';
	gazettenews_posted_on();
	if ( $show_comments ) {
		echo '<span class="meta-sep">—</span>';
		gazettenews_comments_count();
	}
	if ( $show_views ) {
		echo '<span class="meta-sep">—</span>';
		gazettenews_views_count();
	}
	echo '</div>';
}

function gazettenews_first_category() {
	$cats = get_the_category();
	if ( empty( $cats ) ) {
		return;
	}
	$cat = $cats[0];
	echo '<a class="gn-cat" href="' . esc_url( get_category_link( $cat->term_id ) ) . '">' . esc_html( $cat->name ) . '</a>';
}

function gazettenews_reading_time() {
	$content = get_post_field( 'post_content', get_the_ID() );
	$words   = str_word_count( wp_strip_all_tags( $content ) );
	$mins    = max( 1, (int) ceil( $words / 200 ) );
	printf(
		'<span class="meta-read">%s</span>',
		esc_html( sprintf( _n( '%d min read', '%d min read', $mins, 'gazettenews' ), $mins ) )
	);
}

function gazettenews_query( $args = array() ) {
	$defaults = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	return new WP_Query( wp_parse_args( $args, $defaults ) );
}

function gazettenews_module_header( $title, $cat_id = 0, $style = 'default' ) {
	if ( '' === (string) $title ) {
		return;
	}
	$link  = $cat_id ? get_category_link( $cat_id ) : '';
	$style = $style ? $style : 'default';
	echo '<div class="module-header is-' . esc_attr( $style ) . '">';
	echo '<h3 class="module-title"><span>' . esc_html( $title ) . '</span></h3>';
	if ( $link && 'bar' !== $style ) {
		echo '<a class="module-more" href="' . esc_url( $link ) . '">' . esc_html__( 'More', 'gazettenews' ) . '</a>';
	}
	echo '</div>';
}

function gazettenews_render_ad_unit( $section, $in_column = false ) {
	$image = isset( $section['image'] ) ? $section['image'] : '';
	$link  = isset( $section['link'] ) ? $section['link'] : '';
	$html  = isset( $section['html'] ) ? $section['html'] : '';
	$class = $in_column ? 'home-ad-slot ad-in-col' : 'home-ad-slot';
	echo '<div class="' . esc_attr( $class ) . '">';
	if ( $section['title'] ) {
		echo '<div class="ad-kicker">' . esc_html( $section['title'] ) . '</div>';
	} else {
		echo '<div class="ad-kicker">' . esc_html__( '- Advertisement -', 'gazettenews' ) . '</div>';
	}
	if ( $html ) {
		echo '<div class="ad-body">' . wp_kses_post( $html ) . '</div>';
	} elseif ( $image ) {
		$img = '<img src="' . esc_url( $image ) . '" alt="">';
		if ( $link ) {
			$img = '<a href="' . esc_url( $link ) . '" rel="nofollow sponsored" target="_blank">' . $img . '</a>';
		}
		echo '<div class="ad-body">' . $img . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} elseif ( ! $in_column && is_active_sidebar( 'home-after-hero' ) ) {
		dynamic_sidebar( 'home-after-hero' );
	} else {
		$w = $in_column ? '300×250' : '728×90';
		echo '<div class="ad-placeholder">' . esc_html( sprintf( __( 'Ad slot %s — add image or HTML in Homepage Sections', 'gazettenews' ), $w ) ) . '</div>';
	}
	echo '</div>';
}
