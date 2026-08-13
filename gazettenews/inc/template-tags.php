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

function gazettenews_posted_by() {
	echo '<span class="meta-author"><a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>';
}

function gazettenews_entry_meta( $show_comments = true ) {
	echo '<div class="entry-meta">';
	gazettenews_posted_by();
	echo '<span class="meta-sep">—</span>';
	gazettenews_posted_on();
	if ( $show_comments && comments_open() ) {
		echo '<span class="meta-sep">—</span><span class="meta-comments"><a href="' . esc_url( get_comments_link() ) . '">' . esc_html( get_comments_number() ) . '</a></span>';
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

function gazettenews_share_links() {
	$url   = rawurlencode( get_permalink() );
	$title = rawurlencode( get_the_title() );
	$links = array(
		'twitter'  => 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title,
		'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . $url,
		'linkedin' => 'https://www.linkedin.com/shareArticle?mini=true&url=' . $url . '&title=' . $title,
		'whatsapp' => 'https://api.whatsapp.com/send?text=' . $title . '%20' . $url,
	);
	echo '<div class="gn-share"><span>' . esc_html__( 'Share', 'gazettenews' ) . '</span>';
	foreach ( $links as $network => $href ) {
		echo '<a class="share-' . esc_attr( $network ) . '" href="' . esc_url( $href ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( ucfirst( $network ) ) . '</a>';
	}
	echo '</div>';
}

/**
 * Query posts for a module.
 *
 * @param array $args WP_Query args.
 * @return WP_Query
 */
function gazettenews_query( $args = array() ) {
	$defaults = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	return new WP_Query( wp_parse_args( $args, $defaults ) );
}

function gazettenews_module_header( $title, $cat_id = 0 ) {
	$link = $cat_id ? get_category_link( $cat_id ) : '';
	echo '<div class="module-header">';
	echo '<h3 class="module-title"><span>' . esc_html( $title ) . '</span></h3>';
	if ( $link ) {
		echo '<a class="module-more" href="' . esc_url( $link ) . '">' . esc_html__( 'More', 'gazettenews' ) . '</a>';
	}
	echo '</div>';
}

function gazettenews_card( $layout = 'grid' ) {
	get_template_part( 'template-parts/content', $layout );
}
