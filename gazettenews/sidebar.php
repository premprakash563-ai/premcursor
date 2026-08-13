<?php
/**
 * Sidebar.
 *
 * @package GazetteNews
 */
?>
<aside id="secondary" class="sidebar widget-area">
	<?php
	if ( is_active_sidebar( 'sidebar-1' ) ) {
		dynamic_sidebar( 'sidebar-1' );
	} else {
		$wargs = array(
			'before_widget' => '<section class="widget">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title"><span>',
			'after_title'   => '</span></h3>',
		);
		the_widget( 'GazetteNews_Popular_Posts', array( 'title' => __( 'Popular Posts', 'gazettenews' ), 'count' => 5 ), $wargs );
		the_widget( 'GazetteNews_Categories', array( 'title' => __( 'Categories', 'gazettenews' ) ), $wargs );
	}
	?>
</aside>
