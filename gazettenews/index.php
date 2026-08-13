<?php
/**
 * Default index / blog.
 *
 * @package GazetteNews
 */

get_header();
?>

<div class="gn-container layout-with-sidebar">
	<main id="primary" class="content-area">
		<?php gazettenews_breadcrumbs(); ?>
		<?php if ( is_home() && ! is_front_page() ) : ?>
			<header class="archive-head">
				<h1><?php echo esc_html( get_the_title( get_option( 'page_for_posts' ) ) ); ?></h1>
			</header>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="posts-list">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/content', 'list' );
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
