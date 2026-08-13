<?php
/**
 * Single post.
 *
 * @package GazetteNews
 */

get_header();
?>

<div class="gn-container layout-with-sidebar">
	<main id="primary" class="content-area">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-article' ); ?>>
				<header class="entry-header">
					<?php gazettenews_first_category(); ?>
					<h1 class="entry-title"><?php the_title(); ?></h1>
					<div class="entry-meta">
						<?php gazettenews_posted_by(); ?>
						<span class="meta-sep">—</span>
						<?php gazettenews_posted_on(); ?>
						<span class="meta-sep">—</span>
						<?php gazettenews_reading_time(); ?>
					</div>
					<?php gazettenews_share_links(); ?>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="featured-media">
						<?php the_post_thumbnail( 'gazettenews-featured' ); ?>
					</figure>
				<?php endif; ?>

				<div class="entry-content">
					<?php
					the_content();
					wp_link_pages( array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'gazettenews' ),
						'after'  => '</div>',
					) );
					?>
				</div>

				<footer class="entry-footer">
					<?php the_tags( '<div class="tag-list"><span>' . esc_html__( 'Tags', 'gazettenews' ) . '</span> ', '', '</div>' ); ?>
				</footer>
			</article>

			<div class="author-box">
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 80 ); ?>
				<div>
					<h3><?php the_author(); ?></h3>
					<p><?php echo esc_html( get_the_author_meta( 'description' ) ); ?></p>
					<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php esc_html_e( 'More from this author', 'gazettenews' ); ?></a>
				</div>
			</div>

			<?php
			the_post_navigation( array(
				'prev_text' => '<span>' . esc_html__( 'Previous', 'gazettenews' ) . '</span>%title',
				'next_text' => '<span>' . esc_html__( 'Next', 'gazettenews' ) . '</span>%title',
			) );

			$related = gazettenews_query( array(
				'posts_per_page' => 3,
				'post__not_in'   => array( get_the_ID() ),
				'category__in'   => wp_get_post_categories( get_the_ID() ),
			) );
			if ( $related->have_posts() ) {
				echo '<section class="related-posts"><h3 class="module-title"><span>' . esc_html__( 'Related Articles', 'gazettenews' ) . '</span></h3><div class="grid-module">';
				while ( $related->have_posts() ) {
					$related->the_post();
					get_template_part( 'template-parts/content', 'grid' );
				}
				echo '</div></section>';
				wp_reset_postdata();
			}

			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</main>
	<?php get_sidebar(); ?>
</div>

<?php
get_footer();
