<?php
/**
 * Homepage section manager.
 *
 * @package GazetteNews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gazettenews_section_types() {
	return array(
		'hero'     => __( 'Big featured story', 'gazettenews' ),
		'posts'    => __( 'Posts module', 'gazettenews' ),
		'mosaic'   => __( 'Featured mosaic', 'gazettenews' ),
		'ad'       => __( 'Advertisement', 'gazettenews' ),
		'video'    => __( 'YouTube video playlist', 'gazettenews' ),
		'facebook' => __( 'Facebook page feed', 'gazettenews' ),
		'shop'     => __( 'WooCommerce products', 'gazettenews' ),
		'html'     => __( 'Custom HTML', 'gazettenews' ),
	);
}

function gazettenews_section_layouts() {
	return array(
		'slider'    => __( 'Slider / carousel', 'gazettenews' ),
		'grid4'     => __( '4-column cards', 'gazettenews' ),
		'columns3'  => __( '3 category columns', 'gazettenews' ),
		'hero'      => __( 'Big overlay image', 'gazettenews' ),
		'thumbs'    => __( 'Headline + thumb (right)', 'gazettenews' ),
		'split'     => __( 'Split (big + list)', 'gazettenews' ),
		'grid'      => __( 'Grid', 'gazettenews' ),
		'list'      => __( 'List', 'gazettenews' ),
	);
}

function gazettenews_header_styles() {
	return array(
		'default' => __( 'Default title bar', 'gazettenews' ),
		'bar'     => __( 'Full red/green banner (centered)', 'gazettenews' ),
		'line'    => __( 'Underline title', 'gazettenews' ),
	);
}

function gazettenews_section_positions() {
	return array(
		'full'  => __( 'Full width', 'gazettenews' ),
		'left'  => __( 'Left column', 'gazettenews' ),
		'right' => __( 'Right column', 'gazettenews' ),
	);
}

function gazettenews_default_home_sections() {
	return array(
		array(
			'id'       => 'hero',
			'type'     => 'hero',
			'enabled'  => true,
			'title'    => '',
			'cat'      => 0,
			'layout'   => 'hero',
			'count'    => 1,
			'position' => 'left',
			'html'     => '',
			'image'    => '',
			'link'     => '',
		),
		array(
			'id'       => 'side-latest',
			'type'     => 'posts',
			'enabled'  => true,
			'title'    => '',
			'cat'      => 0,
			'layout'   => 'thumbs',
			'count'    => 6,
			'position' => 'right',
			'html'     => '',
			'image'    => '',
			'link'     => '',
		),
		array(
			'id'       => 'ad-full',
			'type'     => 'ad',
			'enabled'  => true,
			'title'    => __( '- Advertisement -', 'gazettenews' ),
			'cat'      => 0,
			'layout'   => '',
			'count'    => 1,
			'position' => 'full',
			'html'     => '',
			'image'    => '',
			'link'     => '',
		),
		array(
			'id'       => 'posts-1',
			'type'     => 'posts',
			'enabled'  => true,
			'title'    => (string) get_theme_mod( 'gazettenews_mod_1_title', __( 'Latest News', 'gazettenews' ) ),
			'cat'      => absint( get_theme_mod( 'gazettenews_mod_1_cat', 0 ) ),
			'layout'   => 'split',
			'count'    => 5,
			'position' => 'left',
			'html'     => '',
			'image'    => '',
			'link'     => '',
		),
		array(
			'id'       => 'ad-side',
			'type'     => 'ad',
			'enabled'  => true,
			'title'    => __( '- Advertisement -', 'gazettenews' ),
			'cat'      => 0,
			'layout'   => '',
			'count'    => 1,
			'position' => 'right',
			'html'     => '',
			'image'    => '',
			'link'     => '',
		),
		array(
			'id'       => 'posts-2',
			'type'     => 'posts',
			'enabled'  => true,
			'title'    => (string) get_theme_mod( 'gazettenews_mod_2_title', '' ),
			'cat'      => absint( get_theme_mod( 'gazettenews_mod_2_cat', 0 ) ),
			'layout'   => 'grid',
			'count'    => 6,
			'position' => 'left',
			'html'     => '',
			'image'    => '',
			'link'     => '',
		),
		array(
			'id'       => 'shop',
			'type'     => 'shop',
			'enabled'  => (bool) get_theme_mod( 'gazettenews_show_shop', true ),
			'title'    => __( 'Shop', 'gazettenews' ),
			'cat'      => 0,
			'layout'   => 'grid',
			'count'    => 4,
			'position' => 'left',
			'html'     => '',
			'image'    => '',
			'link'     => '',
		),
	);
}

function gazettenews_get_home_mode() {
	$mode = get_option( 'gazettenews_home_mode', 'sections' );
	return in_array( $mode, array( 'sections', 'gutenberg', 'both' ), true ) ? $mode : 'sections';
}

function gazettenews_get_home_sections() {
	$saved = get_option( 'gazettenews_home_sections', null );
	if ( ! is_array( $saved ) ) {
		$saved = gazettenews_default_home_sections();
	}
	$clean = array();
	foreach ( $saved as $row ) {
		$clean[] = gazettenews_sanitize_section( $row );
	}
	return $clean;
}

function gazettenews_sanitize_section( $row ) {
	$types   = array_keys( gazettenews_section_types() );
	$layouts = array_keys( gazettenews_section_layouts() );
	$places  = array_keys( gazettenews_section_positions() );
	$type    = isset( $row['type'] ) ? sanitize_key( $row['type'] ) : 'posts';
	if ( ! in_array( $type, $types, true ) ) {
		$type = 'posts';
	}
	$layout = isset( $row['layout'] ) ? sanitize_key( $row['layout'] ) : 'grid';
	if ( 'posts' === $type && ! in_array( $layout, $layouts, true ) ) {
		$layout = 'grid';
	}
	if ( 'hero' === $type ) {
		$layout = 'hero';
	}
	$heads = array_keys( gazettenews_header_styles() );
	$head  = isset( $row['headstyle'] ) ? sanitize_key( $row['headstyle'] ) : 'default';
	if ( ! in_array( $head, $heads, true ) ) {
		$head = 'default';
	}
	$position = isset( $row['position'] ) ? sanitize_key( $row['position'] ) : '';
	if ( ! in_array( $position, $places, true ) ) {
		$position = ( 'mosaic' === $type || 'ad' === $type ) ? 'full' : 'left';
	}
	return array(
		'id'       => isset( $row['id'] ) ? sanitize_key( $row['id'] ) : uniqid( 'sec-', false ),
		'type'     => $type,
		'enabled'  => ! empty( $row['enabled'] ),
		'title'    => isset( $row['title'] ) ? sanitize_text_field( $row['title'] ) : '',
		'cat'      => isset( $row['cat'] ) ? absint( $row['cat'] ) : 0,
		'layout'   => $layout,
		'count'     => isset( $row['count'] ) ? min( 16, max( 1, absint( $row['count'] ) ) ) : 4,
		'position'  => $position,
		'headstyle' => $head,
		'html'      => isset( $row['html'] ) ? wp_kses_post( $row['html'] ) : '',
		'image'     => isset( $row['image'] ) ? esc_url_raw( $row['image'] ) : '',
		'link'      => isset( $row['link'] ) ? esc_url_raw( $row['link'] ) : '',
		'extra'     => isset( $row['extra'] ) ? sanitize_text_field( $row['extra'] ) : '',
	);
}

function gazettenews_render_homepage() {
	$mode = gazettenews_get_home_mode();
	$used = array();

	if ( in_array( $mode, array( 'gutenberg', 'both' ), true ) && is_front_page() && is_page() ) {
		while ( have_posts() ) {
			the_post();
			if ( get_the_content() ) {
				echo '<div class="gn-container front-page-content">';
				the_content();
				echo '</div>';
			}
		}
		rewind_posts();
		if ( 'gutenberg' === $mode ) {
			return;
		}
	}

	$sections = gazettenews_get_home_sections();
	$left     = array();
	$right    = array();

	echo '<div class="magazine-wrap">';
	foreach ( $sections as $section ) {
		if ( empty( $section['enabled'] ) ) {
			continue;
		}
		$pos = $section['position'];
		if ( 'full' === $pos || 'mosaic' === $section['type'] && 'full' === $pos ) {
			gazettenews_flush_home_columns( $left, $right, $used );
			$left  = array();
			$right = array();
			gazettenews_render_section( $section, $used, false );
			continue;
		}
		if ( 'right' === $pos ) {
			$right[] = $section;
		} else {
			$left[] = $section;
		}
	}
	gazettenews_flush_home_columns( $left, $right, $used );
	echo '</div>';
}

function gazettenews_flush_home_columns( $left, $right, &$used ) {
	if ( empty( $left ) && empty( $right ) ) {
		return;
	}
	echo '<div class="gn-container home-columns">';
	echo '<div class="home-col home-col-left">';
	foreach ( $left as $section ) {
		gazettenews_render_section( $section, $used, true );
	}
	echo '</div><div class="home-col home-col-right">';
	foreach ( $right as $section ) {
		gazettenews_render_section( $section, $used, true );
	}
	if ( empty( $right ) && is_active_sidebar( 'sidebar-1' ) ) {
		echo '<aside class="sidebar widget-area">';
		dynamic_sidebar( 'sidebar-1' );
		echo '</aside>';
	}
	echo '</div></div>';
}

function gazettenews_render_section( $section, &$used, $in_column = false ) {
	$type = $section['type'];
	if ( 'mosaic' === $type ) {
		gazettenews_render_mosaic( $section, $used, $in_column );
		return;
	}
	if ( 'ad' === $type ) {
		gazettenews_render_ad_unit( $section, $in_column );
		return;
	}
	if ( 'html' === $type && ! empty( $section['html'] ) ) {
		echo '<section class="module module-html">' . wp_kses_post( $section['html'] ) . '</section>';
		return;
	}
	if ( 'shop' === $type ) {
		gazettenews_home_products( $section['count'], $section['title'] );
		return;
	}
	if ( 'video' === $type ) {
		gazettenews_render_video_playlist( $section );
		return;
	}
	if ( 'facebook' === $type ) {
		gazettenews_render_facebook( $section );
		return;
	}
	if ( 'hero' === $type || 'posts' === $type ) {
		if ( 'hero' === $type ) {
			$section['layout'] = 'hero';
		}
		gazettenews_render_posts_module( $section, $used );
	}
}

function gazettenews_render_mosaic( $section, &$used, $in_column = false ) {
	$count  = max( 3, min( 5, absint( $section['count'] ) ) );
	$sticky = get_option( 'sticky_posts' );
	$fargs  = array(
		'posts_per_page'      => $count,
		'ignore_sticky_posts' => true,
	);
	if ( ! empty( $section['cat'] ) ) {
		$fargs['cat'] = absint( $section['cat'] );
	}
	if ( ! empty( $sticky ) && empty( $section['cat'] ) ) {
		$fargs['post__in'] = $sticky;
		$fargs['orderby']  = 'post__in';
	}
	$featured = gazettenews_query( $fargs );

	if ( ! $featured->have_posts() || $featured->post_count < $count ) {
		$exclude   = wp_list_pluck( $featured->posts, 'ID' );
		$needed    = $count - $featured->post_count;
		$fill_args = array(
			'posts_per_page' => $needed,
			'post__not_in'   => $exclude,
		);
		if ( ! empty( $section['cat'] ) ) {
			$fill_args['cat'] = absint( $section['cat'] );
		}
		$fill                     = gazettenews_query( $fill_args );
		$featured->posts          = array_merge( $featured->posts, $fill->posts );
		$featured->post_count     = count( $featured->posts );
	}

	if ( ! $featured->posts ) {
		return;
	}

	$used  = array_merge( $used, wp_list_pluck( $featured->posts, 'ID' ) );
	$wrap  = $in_column ? '' : 'gn-container ';
	?>
	<section class="featured-mosaic">
		<?php if ( $section['title'] ) : ?>
			<div class="<?php echo esc_attr( $wrap ); ?>"><?php gazettenews_module_header( $section['title'], absint( $section['cat'] ) ); ?></div>
		<?php endif; ?>
		<div class="<?php echo esc_attr( $wrap ); ?>mosaic-grid mosaic-count-<?php echo esc_attr( (string) min( 5, $featured->post_count ) ); ?>">
			<?php
			$i = 0;
			foreach ( $featured->posts as $post ) {
				setup_postdata( $post );
				$i++;
				$thumb = 1 === $i ? 'gazettenews-featured' : 'gazettenews-mosaic';
				?>
				<article class="mosaic-item mosaic-<?php echo esc_attr( (string) $i ); ?>">
					<a class="mosaic-link" href="<?php the_permalink(); ?>">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( $thumb ); ?>
						<?php else : ?>
							<span class="thumb-fallback"></span>
						<?php endif; ?>
						<span class="mosaic-overlay">
							<?php
							$cats = get_the_category();
							if ( $cats ) {
								echo '<span class="gn-cat">' . esc_html( $cats[0]->name ) . '</span>';
							}
							?>
							<h2><?php the_title(); ?></h2>
							<span class="mosaic-meta"><?php echo esc_html( get_the_author() ); ?> · <?php echo esc_html( get_the_date() ); ?> · <?php echo esc_html( get_comments_number() ); ?></span>
						</span>
					</a>
				</article>
				<?php
			}
			wp_reset_postdata();
			?>
		</div>
	</section>
	<?php
}

function gazettenews_render_posts_module( $section, &$used ) {
	$cat_id = absint( $section['cat'] );
	$title  = $section['title'];
	$layout = $section['layout'];
	$count  = absint( $section['count'] );
	$hstyle = isset( $section['headstyle'] ) ? $section['headstyle'] : 'default';

	if ( 'columns3' === $layout ) {
		echo '<section class="module module-columns3">';
		gazettenews_module_header( $title, $cat_id, $hstyle );
		gazettenews_render_three_columns( $section, $used );
		echo '</section>';
		return;
	}

	if ( 'hero' === $layout && $count < 1 ) {
		$count = 1;
	}

	$qargs = array(
		'posts_per_page' => $count,
		'post__not_in'   => $used,
	);
	if ( $cat_id ) {
		$qargs['cat'] = $cat_id;
	}
	$mod = gazettenews_query( $qargs );
	if ( ! $mod->have_posts() ) {
		return;
	}

	echo '<section class="module module-' . esc_attr( $layout ) . '">';
	gazettenews_module_header( $title, $cat_id, $hstyle );

	if ( 'hero' === $layout ) {
		while ( $mod->have_posts() ) {
			$mod->the_post();
			$used[] = get_the_ID();
			get_template_part( 'template-parts/content', 'overlay' );
		}
	} elseif ( 'thumbs' === $layout ) {
		echo '<div class="thumbs-module">';
		while ( $mod->have_posts() ) {
			$mod->the_post();
			$used[] = get_the_ID();
			get_template_part( 'template-parts/content', 'thumbs' );
		}
		echo '</div>';
	} elseif ( 'split' === $layout ) {
		echo '<div class="split-module">';
		$n = 0;
		while ( $mod->have_posts() ) {
			$mod->the_post();
			$used[] = get_the_ID();
			$n++;
			if ( 1 === $n ) {
				get_template_part( 'template-parts/content', 'hero' );
				if ( $mod->post_count > 1 ) {
					echo '<div class="split-side">';
				}
			} else {
				get_template_part( 'template-parts/content', 'list-small' );
			}
		}
		if ( $n > 1 ) {
			echo '</div>';
		}
		echo '</div>';
	} elseif ( 'list' === $layout ) {
		echo '<div class="list-module">';
		while ( $mod->have_posts() ) {
			$mod->the_post();
			$used[] = get_the_ID();
			get_template_part( 'template-parts/content', 'list' );
		}
		echo '</div>';
	} elseif ( 'slider' === $layout ) {
		$pos     = isset( $section['position'] ) ? $section['position'] : 'full';
		$visible = ( 'full' === $pos ) ? 4 : ( 'right' === $pos ? 1 : 2 );
		$visible = min( $visible, max( 1, $count ) );
		echo '<div class="gn-slider" data-visible="' . esc_attr( (string) $visible ) . '">';
		echo '<button type="button" class="gn-slide-btn prev" aria-label="' . esc_attr__( 'Previous', 'gazettenews' ) . '">&lsaquo;</button>';
		echo '<div class="gn-slider-viewport"><div class="gn-slider-track">';
		while ( $mod->have_posts() ) {
			$mod->the_post();
			$used[] = get_the_ID();
			echo '<div class="gn-slide">';
			get_template_part( 'template-parts/content', 'round' );
			echo '</div>';
		}
		echo '</div></div>';
		echo '<button type="button" class="gn-slide-btn next" aria-label="' . esc_attr__( 'Next', 'gazettenews' ) . '">&rsaquo;</button>';
		echo '</div>';
	} elseif ( 'grid4' === $layout ) {
		echo '<div class="grid-module grid-4">';
		while ( $mod->have_posts() ) {
			$mod->the_post();
			$used[] = get_the_ID();
			get_template_part( 'template-parts/content', 'round' );
		}
		echo '</div>';
	} else {
		echo '<div class="grid-module">';
		while ( $mod->have_posts() ) {
			$mod->the_post();
			$used[] = get_the_ID();
			get_template_part( 'template-parts/content', 'grid' );
		}
		echo '</div>';
	}

	echo '</section>';
	wp_reset_postdata();
}

function gazettenews_render_three_columns( $section, &$used ) {
	$ids   = array( absint( $section['cat'] ) );
	$extra = isset( $section['extra'] ) ? $section['extra'] : '';
	if ( $extra ) {
		foreach ( explode( ',', $extra ) as $bit ) {
			$ids[] = absint( trim( $bit ) );
		}
	}
	$ids = array_slice( array_pad( $ids, 3, 0 ), 0, 3 );
	echo '<div class="triple-cols">';
	foreach ( $ids as $cid ) {
		$qargs = array(
			'posts_per_page' => 4,
			'post__not_in'   => $used,
		);
		if ( $cid ) {
			$qargs['cat'] = $cid;
		}
		$col = gazettenews_query( $qargs );
		$ttl = $cid ? get_cat_name( $cid ) : __( 'Latest', 'gazettenews' );
		echo '<div class="triple-col">';
		echo '<h4 class="triple-title">' . esc_html( $ttl ) . '</h4>';
		if ( $col->have_posts() ) {
			while ( $col->have_posts() ) {
				$col->the_post();
				$used[] = get_the_ID();
				get_template_part( 'template-parts/content', 'thumbs' );
			}
			wp_reset_postdata();
		}
		echo '</div>';
	}
	echo '</div>';
}

function gazettenews_youtube_items( $raw ) {
	$items = array();
	if ( ! $raw ) {
		return $items;
	}
	$playlists = array();
	$lines     = preg_split( '/\r\n|\r|\n/', $raw );
	foreach ( $lines as $line ) {
		$line = trim( wp_strip_all_tags( $line ) );
		if ( ! $line ) {
			continue;
		}
		$title = '';
		if ( strpos( $line, '|' ) !== false ) {
			$parts = array_map( 'trim', explode( '|', $line, 2 ) );
			$line  = $parts[0];
			$title = isset( $parts[1] ) ? $parts[1] : '';
		}
		if ( preg_match( '/[?&]list=([A-Za-z0-9_-]+)/', $line, $pm ) ) {
			$playlists[] = $pm[1];
			continue;
		}
		$id = '';
		if ( preg_match( '/(?:youtu\.be\/|v=|\/embed\/|\/shorts\/)([A-Za-z0-9_-]{11})/', $line, $m ) ) {
			$id = $m[1];
		} elseif ( preg_match( '/^[A-Za-z0-9_-]{11}$/', $line ) ) {
			$id = $line;
		} elseif ( preg_match( '/^(PL|UU|LL|FL)[A-Za-z0-9_-]{10,}$/', $line ) ) {
			$playlists[] = $line;
			continue;
		}
		if ( $id ) {
			$items[] = array(
				'id'       => $id,
				'title'    => $title,
				'duration' => '',
				'thumb'    => '',
			);
		}
	}

	foreach ( $playlists as $pid ) {
		foreach ( gazettenews_youtube_playlist_ids( $pid, 15 ) as $vid ) {
			$items[] = array(
				'id'       => $vid,
				'title'    => '',
				'duration' => '',
				'thumb'    => '',
			);
		}
	}

	return gazettenews_youtube_hydrate( $items );
}

function gazettenews_render_video_playlist( $section ) {
	$items = gazettenews_youtube_items( $section['html'] );
	if ( empty( $items ) ) {
		return;
	}
	$first = $items[0];
	$style = isset( $section['headstyle'] ) ? $section['headstyle'] : 'bar';
	echo '<section class="module module-video">';
	gazettenews_module_header( $section['title'] ? $section['title'] : __( 'Videos', 'gazettenews' ), 0, $style );
	echo '<div class="video-playlist">';
	echo '<div class="video-main">';
	echo '<div class="video-frame"><iframe class="gn-yt-player" src="https://www.youtube.com/embed/' . esc_attr( $first['id'] ) . '" title="YouTube" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe></div>';
	echo '<div class="video-now"><span class="play-ico">▶</span><span class="now-title">' . esc_html( $first['title'] ? $first['title'] : __( 'Now playing', 'gazettenews' ) ) . '</span>';
	if ( ! empty( $first['duration'] ) ) {
		echo '<span class="now-dur">' . esc_html( $first['duration'] ) . '</span>';
	}
	echo '</div></div><ul class="video-list">';
	foreach ( $items as $item ) {
		$thumb = ! empty( $item['thumb'] ) ? $item['thumb'] : 'https://i.ytimg.com/vi/' . rawurlencode( $item['id'] ) . '/mqdefault.jpg';
		echo '<li><button type="button" class="video-item" data-id="' . esc_attr( $item['id'] ) . '" data-title="' . esc_attr( $item['title'] ) . '">';
		echo '<img src="' . esc_url( $thumb ) . '" alt="">';
		echo '<span><strong>' . esc_html( $item['title'] ? $item['title'] : $item['id'] ) . '</strong>';
		if ( ! empty( $item['duration'] ) ) {
			echo '<em>' . esc_html( $item['duration'] ) . '</em>';
		}
		echo '</span></button></li>';
	}
	echo '</ul></div></section>';
}

function gazettenews_render_facebook( $section ) {
	$url = $section['link'] ? $section['link'] : $section['html'];
	if ( ! $url ) {
		$url = get_theme_mod( 'gazettenews_social_facebook' );
	}
	if ( ! $url ) {
		return;
	}
	$src = 'https://www.facebook.com/plugins/page.php?href=' . rawurlencode( $url ) . '&tabs=timeline&width=340&height=500&small_header=false&adapt_container_width=true&hide_cover=false&show_facepile=true';
	$style = isset( $section['headstyle'] ) ? $section['headstyle'] : 'bar';
	echo '<section class="module module-facebook">';
	gazettenews_module_header( $section['title'] ? $section['title'] : __( 'Follow us', 'gazettenews' ), 0, $style );
	echo '<div class="fb-embed"><iframe src="' . esc_url( $src ) . '" width="340" height="500" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allow="encrypted-media" title="Facebook"></iframe></div>';
	echo '</section>';
}
