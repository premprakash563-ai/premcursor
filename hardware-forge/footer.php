<?php
/**
 * Footer — modern black footer with quick links / support.
 *
 * @package HardwareForge
 */
?>

</div><!-- #page -->

<?php
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) :
	?>
	<footer class="hf-site-footer">
		<div class="hf-container hf-site-footer__grid">
			<div class="hf-site-footer__brand">
				<?php hardware_forge_site_brand( 'hf-brand--footer' ); ?>
				<p class="hf-site-footer__tagline">
					<?php esc_html_e( 'Build it tough — power tools, generators, and hardware for every jobsite.', 'hardware-forge' ); ?>
				</p>
				<ul class="hf-social" aria-label="<?php esc_attr_e( 'Social media', 'hardware-forge' ); ?>">
					<li><a href="#"><?php esc_html_e( 'Facebook', 'hardware-forge' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Instagram', 'hardware-forge' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'YouTube', 'hardware-forge' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'TikTok', 'hardware-forge' ); ?></a></li>
				</ul>
			</div>

			<div class="hf-site-footer__col">
				<h2 class="hf-site-footer__heading"><?php esc_html_e( 'Quick links', 'hardware-forge' ); ?></h2>
				<ul class="hf-footer-links">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Search', 'hardware-forge' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About us', 'hardware-forge' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/return-policy/' ) ); ?>"><?php esc_html_e( 'Return policy', 'hardware-forge' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/shipping-policy/' ) ); ?>"><?php esc_html_e( 'Shipping policy', 'hardware-forge' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php esc_html_e( 'Terms of service', 'hardware-forge' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>"><?php esc_html_e( 'Privacy policy', 'hardware-forge' ); ?></a></li>
				</ul>
			</div>

			<div class="hf-site-footer__col">
				<h2 class="hf-site-footer__heading"><?php esc_html_e( 'Support', 'hardware-forge' ); ?></h2>
				<ul class="hf-footer-links">
					<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact us', 'hardware-forge' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/franchising/' ) ); ?>"><?php esc_html_e( 'Franchising', 'hardware-forge' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>"><?php esc_html_e( 'FAQs', 'hardware-forge' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/after-sales/' ) ); ?>"><?php esc_html_e( 'After-sales', 'hardware-forge' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/catalogue/' ) ); ?>"><?php esc_html_e( 'Catalogue', 'hardware-forge' ); ?></a></li>
				</ul>
			</div>

			<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
				<div class="hf-site-footer__col">
					<?php dynamic_sidebar( 'footer-1' ); ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="hf-site-footer__bar">
			<div class="hf-container">
				<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'Elementor & WooCommerce ready.', 'hardware-forge' ); ?></p>
			</div>
		</div>
	</footer>

	<button type="button" class="hf-back-top" data-hf-back-top hidden aria-label="<?php esc_attr_e( 'Back to top', 'hardware-forge' ); ?>">↑</button>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
