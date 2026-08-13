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

function gazettenews_comments_count() {
	$n = get_comments_number();
	echo '<span class="meta-comments"><a href="' . esc_url( get_comments_link() ) . '">' . esc_html( sprintf( _n( '%s comment', '%s comments', $n, 'gazettenews' ), number_format_i18n( $n ) ) ) . '</a></span>';
}

function gazettenews_views_count() {
	$n = gazettenews_get_views();
	echo '<span class="meta-views">' . esc_html( sprintf( _n( '%s view', '%s views', $n, 'gazettenews' ), number_format_i18n( $n ) ) ) . '</span>';
}

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

function gazettenews_share_links( $context = 'default' ) {
	$url   = rawurlencode( get_permalink() );
	$title = rawurlencode( get_the_title() );
	$plain = get_permalink();
	$links = array(
		'facebook' => array( __( 'Facebook', 'gazettenews' ), 'https://www.facebook.com/sharer/sharer.php?u=' . $url ),
		'twitter'  => array( __( 'Twitter', 'gazettenews' ), 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title ),
		'whatsapp' => array( __( 'WhatsApp', 'gazettenews' ), 'https://api.whatsapp.com/send?text=' . $title . '%20' . $url ),
		'telegram' => array( __( 'Telegram', 'gazettenews' ), 'https://t.me/share/url?url=' . $url . '&text=' . $title ),
		'linkedin' => array( __( 'LinkedIn', 'gazettenews' ), 'https://www.linkedin.com/shareArticle?mini=true&url=' . $url . '&title=' . $title ),
		'email'    => array( __( 'Email', 'gazettenews' ), 'mailto:?subject=' . $title . '&body=' . $url ),
	);
	echo '<div class="gn-share gn-share-' . esc_attr( $context ) . '">';
	echo '<span>' . esc_html__( 'Share', 'gazettenews' ) . '</span>';
	foreach ( $links as $network => $item ) {
		echo '<a class="share-' . esc_attr( $network ) . '" href="' . esc_url( $item[1] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $item[0] ) . '</a>';
	}
	echo '<button type="button" class="share-copy" data-url="' . esc_attr( $plain ) . '">' . esc_html__( 'Copy link', 'gazettenews' ) . '</button>';
	echo '</div>';
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
	$class = $in_column ? 'home-ad-slot ad-in-col' : 'gn-container home-ad-slot';
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
