<?php
/**
 * Magazine homepage modules.
 *
 * @package GazetteNews
 */

$featured_n = max( 3, min( 5, absint( get_theme_mod( 'gazettenews_featured_count', 5 ) ) ) );
$sticky     = get_option( 'sticky_posts' );
$fargs = array(
	'posts_per_page'      => $featured_n,
	'ignore_sticky_posts' => true,
);
if ( ! empty( $sticky ) ) {
	$fargs['post__in'] = $sticky;
	$fargs['orderby']  = 'post__in';
}
$featured = gazettenews_query( $fargs );

if ( ! $featured->have_posts() || $featured->post_count < $featured_n ) {
	$exclude  = wp_list_pluck( $featured->posts, 'ID' );
	$needed   = $featured_n - $featured->post_count;
	$fill     = gazettenews_query( array(
		'posts_per_page' => $needed,
		'post__not_in'   => $exclude,
	) );
	$featured->posts = array_merge( $featured->posts, $fill->posts );
	$featured->post_count = count( $featured->posts );
}

$used_ids = wp_list_pluck( $featured->posts, 'ID' );
?>

<div class="magazine-wrap">
	<?php if ( $featured->have_posts() ) : ?>
		<section class="featured-mosaic">
			<div class="gn-container mosaic-grid mosaic-count-<?php echo esc_attr( (string) min( 5, $featured->post_count ) ); ?>">
				<?php
				$i = 0;
				foreach ( $featured->posts as $post ) {
					setup_postdata( $post );
					$i++;
					$thumb = $i === 1 ? 'gazettenews-featured' : 'gazettenews-mosaic';
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
	<?php endif; ?>

	<?php if ( is_active_sidebar( 'home-after-hero' ) ) : ?>
		<div class="gn-container home-ad-slot">
			<?php dynamic_sidebar( 'home-after-hero' ); ?>
		</div>
	<?php endif; ?>

	<div class="gn-container layout-with-sidebar magazine-body">
		<div class="content-area">
			<?php
			$layouts = array( 'split', 'grid', 'list', 'grid' );
			for ( $m = 1; $m <= 4; $m++ ) {
				$cat_id = absint( get_theme_mod( "gazettenews_mod_{$m}_cat", 0 ) );
				$title  = get_theme_mod( "gazettenews_mod_{$m}_title" );
				if ( ! $title ) {
					$title = $cat_id ? get_cat_name( $cat_id ) : ( 1 === $m ? __( 'Latest News', 'gazettenews' ) : sprintf( __( 'More Stories %d', 'gazettenews' ), $m ) );
				}

				$qargs = array(
					'posts_per_page' => 4 === $m || 2 === $m ? 6 : 5,
					'post__not_in'   => $used_ids,
				);
				if ( $cat_id ) {
					$qargs['cat'] = $cat_id;
				}

				$mod = gazettenews_query( $qargs );
				if ( ! $mod->have_posts() ) {
					continue;
				}

				$layout = $layouts[ $m - 1 ];
				echo '<section class="module module-' . esc_attr( $layout ) . '">';
				gazettenews_module_header( $title, $cat_id );

				if ( 'split' === $layout ) {
					echo '<div class="split-module">';
					$n = 0;
					while ( $mod->have_posts() ) {
						$mod->the_post();
						$used_ids[] = get_the_ID();
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
				} elseif ( 'grid' === $layout ) {
					echo '<div class="grid-module">';
					while ( $mod->have_posts() ) {
						$mod->the_post();
						$used_ids[] = get_the_ID();
						get_template_part( 'template-parts/content', 'grid' );
					}
					echo '</div>';
				} else {
					echo '<div class="list-module">';
					while ( $mod->have_posts() ) {
						$mod->the_post();
						$used_ids[] = get_the_ID();
						get_template_part( 'template-parts/content', 'list' );
					}
					echo '</div>';
				}

				echo '</section>';
				wp_reset_postdata();
			}

			gazettenews_home_products( 4 );
			?>
		</div>
		<?php get_sidebar(); ?>
	</div>
</div>
