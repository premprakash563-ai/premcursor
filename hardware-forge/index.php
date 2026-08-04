<?php
/**
 * Main fallback template.
 *
 * @package HardwareForge
 */

get_header();
?>

<main id="primary" class="site-main">
	<div class="hf-container hf-content-grid">
		<div class="hf-content-area">
			<?php if ( have_posts() ) : ?>
				<?php if ( is_home() && ! is_front_page() ) : ?>
					<header class="hf-page-header">
						<h1 class="hf-page-title"><?php single_post_title(); ?></h1>
					</header>
				<?php endif; ?>

				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', get_post_type() );
				endwhile;

				the_posts_pagination(
					array(
						'prev_text' => __( 'Previous', 'hardware-forge' ),
						'next_text' => __( 'Next', 'hardware-forge' ),
					)
				);
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
