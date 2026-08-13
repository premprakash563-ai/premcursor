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
		'hero'   => __( 'Big featured story', 'gazettenews' ),
		'posts'  => __( 'Posts module', 'gazettenews' ),
		'mosaic' => __( 'Featured mosaic', 'gazettenews' ),
		'ad'     => __( 'Advertisement', 'gazettenews' ),
		'shop'   => __( 'WooCommerce products', 'gazettenews' ),
		'html'   => __( 'Custom HTML', 'gazettenews' ),
	);
}

function gazettenews_section_layouts() {
	return array(
		'hero'   => __( 'Big overlay image', 'gazettenews' ),
		'thumbs' => __( 'Headline + thumb (right)', 'gazettenews' ),
		'split'  => __( 'Split (big + list)', 'gazettenews' ),
		'grid'   => __( 'Grid', 'gazettenews' ),
		'list'   => __( 'List', 'gazettenews' ),
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
		return gazettenews_default_home_sections();
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
		'count'    => isset( $row['count'] ) ? min( 12, max( 1, absint( $row['count'] ) ) ) : 4,
		'position' => $position,
		'html'     => isset( $row['html'] ) ? wp_kses_post( $row['html'] ) : '',
		'image'    => isset( $row['image'] ) ? esc_url_raw( $row['image'] ) : '',
		'link'     => isset( $row['link'] ) ? esc_url_raw( $row['link'] ) : '',
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
	gazettenews_module_header( $title, $cat_id );

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
