<?php
/**
 * Page template — Elementor-friendly.
 *
 * @package HardwareForge
 */

get_header();
?>

<main id="primary" class="site-main">
	<?php
	while ( have_posts() ) :
		the_post();

		if ( hardware_forge_is_elementor_page() ) {
			echo '<div class="hf-elementor-canvas">';
			the_content();
			echo '</div>';
		} else {
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'hf-container hf-page' ); ?>>
				<header class="hf-page-header">
					<?php the_title( '<h1 class="hf-page-title">', '</h1>' ); ?>
				</header>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</article>
			<?php
		}
	endwhile;
	?>
</main>

<?php
get_footer();
