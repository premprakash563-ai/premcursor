<?php
/**
 * Homepage section manager (tagDiv-style modules, without tagDiv Composer).
 *
 * @package GazetteNews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allowed section types.
 */
function gazettenews_section_types() {
	return array(
		'mosaic' => __( 'Featured mosaic', 'gazettenews' ),
		'ad'     => __( 'Advertisement / widget slot', 'gazettenews' ),
		'posts'  => __( 'Posts module', 'gazettenews' ),
		'shop'   => __( 'WooCommerce products', 'gazettenews' ),
		'html'   => __( 'Custom HTML', 'gazettenews' ),
	);
}

function gazettenews_section_layouts() {
	return array(
		'split' => __( 'Split (big + list)', 'gazettenews' ),
		'grid'  => __( 'Grid', 'gazettenews' ),
		'list'  => __( 'List', 'gazettenews' ),
	);
}

function gazettenews_default_home_sections() {
	$mods = array();
	for ( $i = 1; $i <= 4; $i++ ) {
		$mods[] = array(
			'id'      => 'posts-' . $i,
			'type'    => 'posts',
			'enabled' => true,
			'title'   => (string) get_theme_mod( "gazettenews_mod_{$i}_title", '' ),
			'cat'     => absint( get_theme_mod( "gazettenews_mod_{$i}_cat", 0 ) ),
			'layout'  => array( 'split', 'grid', 'list', 'grid' )[ $i - 1 ],
			'count'   => ( 2 === $i || 4 === $i ) ? 6 : 5,
			'html'    => '',
		);
	}

	return array_merge(
		array(
			array(
				'id'      => 'mosaic',
				'type'    => 'mosaic',
				'enabled' => true,
				'title'   => '',
				'cat'     => 0,
				'layout'  => 'mosaic',
				'count'   => max( 3, min( 5, absint( get_theme_mod( 'gazettenews_featured_count', 5 ) ) ) ),
				'html'    => '',
			),
			array(
				'id'      => 'ad',
				'type'    => 'ad',
				'enabled' => true,
				'title'   => '',
				'cat'     => 0,
				'layout'  => '',
				'count'   => 0,
				'html'    => '',
			),
		),
		$mods,
		array(
			array(
				'id'      => 'shop',
				'type'    => 'shop',
				'enabled' => (bool) get_theme_mod( 'gazettenews_show_shop', true ),
				'title'   => __( 'Shop', 'gazettenews' ),
				'cat'     => 0,
				'layout'  => 'grid',
				'count'   => 4,
				'html'    => '',
			),
		)
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
	$type    = isset( $row['type'] ) ? sanitize_key( $row['type'] ) : 'posts';
	if ( ! in_array( $type, $types, true ) ) {
		$type = 'posts';
	}
	$layout = isset( $row['layout'] ) ? sanitize_key( $row['layout'] ) : 'grid';
	if ( 'posts' === $type && ! in_array( $layout, $layouts, true ) ) {
		$layout = 'grid';
	}
	return array(
		'id'      => isset( $row['id'] ) ? sanitize_key( $row['id'] ) : uniqid( 'sec-', false ),
		'type'    => $type,
		'enabled' => ! empty( $row['enabled'] ),
		'title'   => isset( $row['title'] ) ? sanitize_text_field( $row['title'] ) : '',
		'cat'     => isset( $row['cat'] ) ? absint( $row['cat'] ) : 0,
		'layout'  => $layout,
		'count'   => isset( $row['count'] ) ? min( 12, max( 1, absint( $row['count'] ) ) ) : 4,
		'html'    => isset( $row['html'] ) ? wp_kses_post( $row['html'] ) : '',
	);
}

function gazettenews_section_is_fullwidth( $type ) {
	return in_array( $type, array( 'mosaic', 'ad' ), true );
}

/**
 * Render the magazine homepage from saved sections.
 *
 * @param int[] $used_ids Post IDs already shown (passed by reference).
 */
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
	$open     = false;

	echo '<div class="magazine-wrap">';
	foreach ( $sections as $section ) {
		if ( empty( $section['enabled'] ) ) {
			continue;
		}
		$full = gazettenews_section_is_fullwidth( $section['type'] );
		if ( $full && $open ) {
			gazettenews_homepage_layout_close();
			$open = false;
		}
		if ( ! $full && ! $open ) {
			gazettenews_homepage_layout_open();
			$open = true;
		}
		gazettenews_render_section( $section, $used );
	}
	if ( $open ) {
		gazettenews_homepage_layout_close();
	}
	echo '</div>';
}

function gazettenews_homepage_layout_open() {
	echo '<div class="gn-container layout-with-sidebar magazine-body"><div class="content-area">';
}

function gazettenews_homepage_layout_close() {
	echo '</div>';
	get_sidebar();
	echo '</div>';
}

/**
 * @param array $section Section config.
 * @param int[] $used    Shown post IDs (by reference).
 */
function gazettenews_render_section( $section, &$used ) {
	$type = $section['type'];
	if ( 'mosaic' === $type ) {
		gazettenews_render_mosaic( $section, $used );
		return;
	}
	if ( 'ad' === $type ) {
		if ( ! empty( $section['html'] ) ) {
			echo '<div class="gn-container home-ad-slot">' . wp_kses_post( $section['html'] ) . '</div>';
		} elseif ( is_active_sidebar( 'home-after-hero' ) ) {
			echo '<div class="gn-container home-ad-slot">';
			dynamic_sidebar( 'home-after-hero' );
			echo '</div>';
		}
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
	if ( 'posts' === $type ) {
		gazettenews_render_posts_module( $section, $used );
	}
}

function gazettenews_render_mosaic( $section, &$used ) {
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
		$exclude = wp_list_pluck( $featured->posts, 'ID' );
		$needed  = $count - $featured->post_count;
		$fill_args = array(
			'posts_per_page' => $needed,
			'post__not_in'   => $exclude,
		);
		if ( ! empty( $section['cat'] ) ) {
			$fill_args['cat'] = absint( $section['cat'] );
		}
		$fill = gazettenews_query( $fill_args );
		$featured->posts      = array_merge( $featured->posts, $fill->posts );
		$featured->post_count = count( $featured->posts );
	}

	if ( ! $featured->posts ) {
		return;
	}

	$used = array_merge( $used, wp_list_pluck( $featured->posts, 'ID' ) );
	?>
	<section class="featured-mosaic">
		<div class="gn-container mosaic-grid mosaic-count-<?php echo esc_attr( (string) min( 5, $featured->post_count ) ); ?>">
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
							<span class="mosaic-meta"><?php echo esc_html( get_the_date() ); ?> · <?php the_author(); ?></span>
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
	if ( ! $title ) {
		$title = $cat_id ? get_cat_name( $cat_id ) : __( 'Latest News', 'gazettenews' );
	}
	$layout = $section['layout'];
	$count  = absint( $section['count'] );

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

	if ( 'split' === $layout ) {
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
