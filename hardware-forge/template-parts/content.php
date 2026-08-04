<?php
/**
 * Content excerpt / loop item.
 *
 * @package HardwareForge
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'hf-entry' ); ?>>
	<header class="hf-entry__header">
		<?php the_title( sprintf( '<h2 class="hf-entry__title"><a href="%s">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
		<div class="hf-entry__meta"><?php hardware_forge_posted_on(); ?></div>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<a class="hf-entry__thumb" href="<?php the_permalink(); ?>">
			<?php the_post_thumbnail( 'hardware-forge-card' ); ?>
		</a>
	<?php endif; ?>

	<div class="hf-entry__excerpt">
		<?php the_excerpt(); ?>
	</div>
</article>
