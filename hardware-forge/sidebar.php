<?php
/**
 * Sidebar.
 *
 * @package HardwareForge
 */

$sidebar_id = ( class_exists( 'WooCommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() ) ) ? 'shop' : 'sidebar-1';

if ( ! is_active_sidebar( $sidebar_id ) ) {
	return;
}
?>

<aside id="secondary" class="hf-sidebar widget-area" role="complementary">
	<?php dynamic_sidebar( $sidebar_id ); ?>
</aside>
