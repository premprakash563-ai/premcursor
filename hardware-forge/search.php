<?php
/**
 * Search results.
 *
 * @package HardwareForge
 */

get_header();
?>

<main id="primary" class="site-main">
	<div class="hf-container hf-content-grid">
		<div class="hf-content-area">
			<header class="hf-page-header">
				<h1 class="hf-page-title">
					<?php
					printf(
						/* translators: %s: search query */
						esc_html__( 'Search: %s', 'hardware-forge' ),
						'<span>' . esc_html( get_search_query() ) . '</span>'
					);
					?>
				</h1>
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
