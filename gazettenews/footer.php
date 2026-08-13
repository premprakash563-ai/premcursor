<?php
/**
 * Theme footer.
 *
 * @package GazetteNews
 */
?>
	</div><!-- #content -->

	<footer class="site-footer">
		<div class="footer-inner">
			<div class="footer-widgets">
				<div class="gn-container footer-grid">
					<?php
					$has_footer = is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) || is_active_sidebar( 'footer-4' );
					if ( $has_footer ) {
						for ( $i = 1; $i <= 4; $i++ ) {
							if ( is_active_sidebar( 'footer-' . $i ) ) {
								echo '<div class="footer-col">';
								dynamic_sidebar( 'footer-' . $i );
								echo '</div>';
							}
						}
					} else {
						$wargs = array(
							'before_widget' => '<section class="widget">',
							'after_widget'  => '</section>',
							'before_title'  => '<h3 class="widget-title"><span>',
							'after_title'   => '</span></h3>',
						);
						echo '<div class="footer-col">';
						the_widget( 'GazetteNews_Category_Posts', array( 'title' => __( 'Editor Picks', 'gazettenews' ), 'count' => 3 ), $wargs );
						echo '</div><div class="footer-col">';
						the_widget( 'GazetteNews_Popular_Posts', array( 'title' => __( 'Popular Posts', 'gazettenews' ), 'count' => 3 ), $wargs );
						echo '</div><div class="footer-col">';
						the_widget( 'GazetteNews_Categories', array( 'title' => __( 'Popular Category', 'gazettenews' ) ), $wargs );
						echo '</div>';
					}
					?>
				</div>
			</div>

			<?php
			$about   = get_theme_mod( 'gazettenews_about' );
			$contact = get_theme_mod( 'gazettenews_contact_email' );
			$soc     = gazettenews_social_links();
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
					<div class="footer-about">
						<h3><?php esc_html_e( 'About us', 'gazettenews' ); ?></h3>
						<?php
						if ( $about ) {
							echo wp_kses_post( wpautop( $about ) );
						}
						if ( $contact ) {
							echo '<p class="footer-contact">' . esc_html__( 'Contact us:', 'gazettenews' ) . ' <a href="mailto:' . esc_attr( antispambot( $contact ) ) . '">' . esc_html( $contact ) . '</a></p>';
						}
						?>
					</div>
					<div class="footer-follow">
						<h3><?php esc_html_e( 'Follow us', 'gazettenews' ); ?></h3>
						<?php if ( $soc ) : ?>
							<div class="follow-icons"><?php echo $soc; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<?php else : ?>
							<p class="footer-follow-hint"><?php esc_html_e( 'Add social URLs in Customize → Gazette News → Social Links.', 'gazettenews' ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="footer-bottom">
				<div class="gn-container footer-bottom-inner">
					<div class="copyright">
						<?php
						$copy = get_theme_mod( 'gazettenews_copyright' );
						if ( $copy ) {
							echo wp_kses_post( $copy );
						} else {
							echo '&copy; ' . esc_html( wp_date( 'Y' ) ) . ' ' . esc_html__( 'All Rights Reserved.', 'gazettenews' ) . ' ' . esc_html( get_bloginfo( 'name' ) );
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
		</div>
	</footer>
	<a class="gn-top" href="#page" aria-label="<?php esc_attr_e( 'Back to top', 'gazettenews' ); ?>">↑</a>
</div><!-- #page -->

<div id="gn-search-modal" class="gn-search-modal" hidden>
	<button type="button" class="gn-search-backdrop" data-gn-search-close aria-label="<?php esc_attr_e( 'Close search', 'gazettenews' ); ?>"></button>
	<div class="gn-search-panel" role="dialog" aria-modal="true" aria-labelledby="gn-search-title">
		<button type="button" class="gn-search-close" data-gn-search-close>
			<?php echo gazettenews_svg_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Close', 'gazettenews' ); ?></span>
		</button>
		<p id="gn-search-title" class="gn-search-kicker"><?php esc_html_e( 'Search news', 'gazettenews' ); ?></p>
		<?php get_search_form(); ?>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
