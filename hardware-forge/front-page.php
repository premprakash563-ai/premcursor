<?php
/**
 * Front page — Elementor-first.
 *
 * Edit the static front page with Elementor to change all homepage content.
 * PHP demo markup is only a temporary fallback when the page is empty.
 *
 * @package HardwareForge
 */

get_header();
?>

<main id="primary" class="site-main hf-elementor-canvas">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();

			$is_elementor = function_exists( 'hardware_forge_is_elementor_page' ) && hardware_forge_is_elementor_page();
			$has_content  = hardware_forge_front_page_has_builder_content();

			if ( $is_elementor || $has_content ) {
				the_content();
			} else {
				get_template_part( 'template-parts/home', 'fallback' );

				if ( current_user_can( 'edit_theme_options' ) ) {
					$page_id        = get_the_ID();
					$elementor_edit = admin_url( 'post.php?post=' . $page_id . '&action=elementor' );
					$setup_url      = admin_url( 'themes.php?page=hardware-forge-setup' );
					?>
					<div class="hf-container" style="padding:1.5rem 0 3rem;text-align:center;">
						<p class="hf-note">
							<?php esc_html_e( 'Demo layout only. Load the Elementor starter, then edit every section visually.', 'hardware-forge' ); ?>
						</p>
						<p>
							<a class="hf-btn hf-btn--primary" href="<?php echo esc_url( $setup_url ); ?>">
								<?php esc_html_e( 'Load Elementor starter', 'hardware-forge' ); ?>
							</a>
							<?php if ( class_exists( '\Elementor\Plugin' ) ) : ?>
								<a class="hf-btn hf-btn--dark" href="<?php echo esc_url( $elementor_edit ); ?>">
									<?php esc_html_e( 'Edit with Elementor', 'hardware-forge' ); ?>
								</a>
							<?php endif; ?>
						</p>
					</div>
					<?php
				}
			}
		endwhile;
	else :
		get_template_part( 'template-parts/home', 'fallback' );
	endif;
	?>
</main>

<?php
get_footer();
