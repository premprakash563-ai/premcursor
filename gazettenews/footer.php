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
