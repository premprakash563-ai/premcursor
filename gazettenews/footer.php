<?php
/**
 * Theme footer.
 *
 * @package GazetteNews
 */
?>
	</div><!-- #content -->

	<footer class="site-footer">
		<?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) || is_active_sidebar( 'footer-4' ) ) : ?>
			<div class="footer-widgets">
				<div class="gn-container footer-grid">
					<?php
					for ( $i = 1; $i <= 4; $i++ ) {
						if ( is_active_sidebar( 'footer-' . $i ) ) {
							echo '<div class="footer-col">';
							dynamic_sidebar( 'footer-' . $i );
							echo '</div>';
						}
					}
					?>
				</div>
			</div>
		<?php endif; ?>

		<?php
		$about = get_theme_mod( 'gazettenews_about' );
		$soc   = gazettenews_social_links();
		if ( $about || $soc ) :
			?>
			<div class="footer-brand">
				<div class="gn-container footer-brand-inner">
					<div class="footer-logo">
						<?php
						if ( has_custom_logo() ) {
							the_custom_logo();
						} else {
							echo '<a class="brand-lockup" href="' . esc_url( home_url( '/' ) ) . '">';
							$line1 = get_theme_mod( 'gazettenews_logo_line1' );
							$line2 = get_theme_mod( 'gazettenews_logo_line2', 'NEWS 24x7' );
							if ( ! $line1 ) {
								$line1 = get_bloginfo( 'name' );
							}
							echo '<span class="brand-hi">' . esc_html( $line1 ) . '</span>';
							if ( $line2 ) {
								echo '<span class="brand-en">' . esc_html( $line2 ) . '</span>';
							}
							echo '</a>';
						}
						$tag = get_theme_mod( 'gazettenews_tagline' );
						if ( $tag ) {
							echo '<p class="site-tagline">' . esc_html( $tag ) . '</p>';
						}
						?>
					</div>
					<?php if ( $about ) : ?>
						<div class="footer-about">
							<h3><?php esc_html_e( 'About us', 'gazettenews' ); ?></h3>
							<?php echo wp_kses_post( wpautop( $about ) ); ?>
						</div>
					<?php endif; ?>
					<?php if ( $soc ) : ?>
						<div class="footer-follow">
							<h3><?php esc_html_e( 'Follow us', 'gazettenews' ); ?></h3>
							<div class="follow-icons"><?php echo $soc; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="footer-bottom">
			<div class="gn-container footer-bottom-inner">
				<div class="copyright">
					<?php
					$copy = get_theme_mod( 'gazettenews_copyright' );
					if ( $copy ) {
						echo wp_kses_post( $copy );
					} else {
						echo '&copy; ' . esc_html( wp_date( 'Y' ) ) . ' <a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( get_bloginfo( 'name' ) ) . '</a>. ';
						esc_html_e( 'All rights reserved.', 'gazettenews' );
					}
					?>
				</div>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'footer-menu',
					'depth'          => 1,
					'fallback_cb'    => false,
				) );
				?>
			</div>
		</div>
	</footer>
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
