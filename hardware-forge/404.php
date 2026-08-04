<?php
/**
 * 404 template.
 *
 * @package HardwareForge
 */

get_header();
?>

<main id="primary" class="site-main">
	<div class="hf-container hf-404">
		<h1 class="hf-page-title"><?php esc_html_e( 'Aisle not found.', 'hardware-forge' ); ?></h1>
		<p><?php esc_html_e( 'That page is not on the rack. Try the shop or head home.', 'hardware-forge' ); ?></p>
		<p class="hf-section__cta">
			<a class="hf-btn hf-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Back to home', 'hardware-forge' ); ?>
			</a>
			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<a class="hf-btn hf-btn--ghost" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
					<?php esc_html_e( 'Open shop', 'hardware-forge' ); ?>
				</a>
			<?php endif; ?>
		</p>
	</div>
</main>

<?php
get_footer();
