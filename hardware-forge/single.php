<?php
/**
 * Single post template.
 *
 * @package HardwareForge
 */

get_header();
?>

<main id="primary" class="site-main">
	<div class="hf-container hf-content-grid">
		<div class="hf-content-area">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', 'single' );
			endwhile;
			?>
		</div>
		<?php get_sidebar(); ?>
	</div>
</main>

<?php
get_footer();
