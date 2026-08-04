<?php
/**
 * Single post content.
 *
 * @package HardwareForge
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'hf-entry hf-entry--single' ); ?>>
	<header class="hf-entry__header">
		<?php the_title( '<h1 class="hf-page-title">', '</h1>' ); ?>
		<div class="hf-entry__meta"><?php hardware_forge_posted_on(); ?></div>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<div class="hf-entry__thumb hf-entry__thumb--wide">
			<?php the_post_thumbnail( 'large' ); ?>
		</div>
	<?php endif; ?>

	<div class="entry-content">
		<?php
		the_content();
		wp_link_pages();
		?>
	</div>
</article>
