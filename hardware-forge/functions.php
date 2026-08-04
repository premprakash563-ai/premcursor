<?php
/**
 * Hardware Forge theme bootstrap.
 *
 * @package HardwareForge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HARDWARE_FORGE_VERSION', '1.2.0' );
define( 'HARDWARE_FORGE_DIR', get_template_directory() );
define( 'HARDWARE_FORGE_URI', get_template_directory_uri() );

require_once HARDWARE_FORGE_DIR . '/inc/setup.php';
require_once HARDWARE_FORGE_DIR . '/inc/enqueue.php';
require_once HARDWARE_FORGE_DIR . '/inc/template-tags.php';
require_once HARDWARE_FORGE_DIR . '/inc/elementor.php';
require_once HARDWARE_FORGE_DIR . '/inc/elementor-starter.php';
require_once HARDWARE_FORGE_DIR . '/inc/woocommerce.php';
