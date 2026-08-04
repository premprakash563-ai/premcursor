<?php
/**
 * Archive template.
 *
 * @package HardwareForge
 */

get_header();
?>

<main id="primary" class="site-main">
	<div class="hf-container hf-content-grid">
		<div class="hf-content-area">
			<header class="hf-page-header">
				<?php the_archive_title( '<h1 class="hf-page-title">', '</h1>' ); ?>
				<?php the_archive_description( '<div class="hf-archive-desc">', '</div>' ); ?>
			</header>

			<?php if ( have_posts() ) : ?>
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', get_post_type() );
				endwhile;
				the_posts_pagination();
				?>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content', 'none' ); ?>
			<?php endif; ?>
		</div>
		<?php get_sidebar(); ?>
	</div>
</main>

<?php
get_footer();
