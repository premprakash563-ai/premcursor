<?php
/**
 * Static page.
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
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-article' ); ?>>
				<header class="entry-header">
					<h1 class="entry-title"><?php the_title(); ?></h1>
				</header>
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="featured-media"><?php the_post_thumbnail( 'gazettenews-featured' ); ?></figure>
				<?php endif; ?>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</article>
			<?php
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
