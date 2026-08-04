<?php
/**
 * Footer template.
 *
 * @package HardwareForge
 */
?>

</div><!-- #page -->

<?php
// Elementor Pro Theme Builder can replace the footer entirely.
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) :
	?>
	<footer class="hf-site-footer">
		<div class="hf-site-footer__grid hf-container">
			<div class="hf-site-footer__brand">
				<?php hardware_forge_site_brand( 'hf-brand--footer' ); ?>
				<p class="hf-site-footer__tagline">
					<?php
					$desc = get_bloginfo( 'description', 'display' );
					echo esc_html( $desc ? $desc : __( 'Tools, fasteners, and industrial supplies — ready when the job is.', 'hardware-forge' ) );
					?>
				</p>
			</div>

			<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
				<?php if ( is_active_sidebar( 'footer-' . $i ) ) : ?>
					<div class="hf-site-footer__col">
						<?php dynamic_sidebar( 'footer-' . $i ); ?>
					</div>
				<?php endif; ?>
			<?php endfor; ?>

			<nav class="hf-site-footer__nav" aria-label="<?php esc_attr_e( 'Footer', 'hardware-forge' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'menu_class'     => 'hf-menu hf-menu--footer',
						'container'      => false,
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>
		</div>

		<div class="hf-site-footer__bar">
			<div class="hf-container">
				<p>
					&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
					<?php bloginfo( 'name' ); ?>.
					<?php esc_html_e( 'Built for Elementor & WooCommerce.', 'hardware-forge' ); ?>
				</p>
			</div>
		</div>
	</footer>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
