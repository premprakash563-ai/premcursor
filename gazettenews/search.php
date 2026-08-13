<?php
/**
 * Search results.
 *
 * @package GazetteNews
 */

get_header();
?>

<div class="gn-container layout-with-sidebar">
	<main id="primary" class="content-area">
		<header class="archive-head">
			<h1>
				<?php
				printf(
					/* translators: %s search query */
					esc_html__( 'Search: %s', 'gazettenews' ),
					'<span>' . esc_html( get_search_query() ) . '</span>'
				);
				?>
			</h1>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="posts-list">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/content', 'list' );
				}
				?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</main>
	<?php get_sidebar(); ?>
</div>

<?php
get_footer();
