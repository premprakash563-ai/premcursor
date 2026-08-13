<?php
/**
 * Archives, categories, tags, dates.
 *
 * @package GazetteNews
 */

get_header();
?>

<div class="gn-container layout-with-sidebar">
	<main id="primary" class="content-area">
		<header class="archive-head">
			<?php
			the_archive_title( '<h1>', '</h1>' );
			the_archive_description( '<div class="archive-desc">', '</div>' );
			?>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="grid-module archive-grid">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/content', 'grid' );
				}
				?>
			</div>
			<?php the_posts_pagination( array( 'mid_size' => 2 ) ); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</main>
	<?php get_sidebar(); ?>
</div>

<?php
get_footer();
