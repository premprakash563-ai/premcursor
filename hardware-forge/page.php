<?php
/**
 * Page template — always renders editor / Elementor content.
 *
 * @package HardwareForge
 */

get_header();
?>

<main id="primary" class="site-main">
	<?php
	while ( have_posts() ) :
		the_post();

		// Elementor (and the block editor) own the page content.
		if ( hardware_forge_is_elementor_page() || trim( (string) get_the_content() ) !== '' ) {
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
					<p><?php esc_html_e( 'This page is empty. Click “Edit with Elementor” to build it.', 'hardware-forge' ); ?></p>
					<?php if ( class_exists( '\Elementor\Plugin' ) && current_user_can( 'edit_post', get_the_ID() ) ) : ?>
						<p>
							<a class="hf-btn hf-btn--primary" href="<?php echo esc_url( admin_url( 'post.php?post=' . get_the_ID() . '&action=elementor' ) ); ?>">
								<?php esc_html_e( 'Edit with Elementor', 'hardware-forge' ); ?>
							</a>
						</p>
					<?php endif; ?>
				</div>
			</article>
			<?php
		}
	endwhile;
	?>
</main>

<?php
get_footer();
