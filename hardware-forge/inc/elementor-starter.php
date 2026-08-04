<?php
/**
 * Elementor starter home layout — injectable & fully editable in Elementor.
 *
 * @package HardwareForge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate a short Elementor element id.
 *
 * @return string
 */
function hardware_forge_el_id() {
	return substr( md5( uniqid( (string) wp_rand(), true ) ), 0, 7 );
}

/**
 * Build a basic Elementor widget node.
 *
 * @param string $widget_type Widget type.
 * @param array  $settings    Settings.
 * @return array
 */
function hardware_forge_el_widget( $widget_type, $settings = array() ) {
	return array(
		'id'         => hardware_forge_el_id(),
		'elType'     => 'widget',
		'widgetType' => $widget_type,
		'settings'   => $settings,
		'elements'   => array(),
	);
}

/**
 * Build an Elementor column.
 *
 * @param array $widgets  Child widgets/sections.
 * @param array $settings Column settings.
 * @return array
 */
function hardware_forge_el_column( $widgets, $settings = array() ) {
	$defaults = array(
		'_column_size' => 100,
		'_inline_size' => null,
	);
	return array(
		'id'       => hardware_forge_el_id(),
		'elType'   => 'column',
		'settings' => array_merge( $defaults, $settings ),
		'elements' => $widgets,
		'isInner'  => false,
	);
}

/**
 * Build an Elementor section.
 *
 * @param array $columns  Columns.
 * @param array $settings Section settings.
 * @return array
 */
function hardware_forge_el_section( $columns, $settings = array() ) {
	return array(
		'id'         => hardware_forge_el_id(),
		'elType'     => 'section',
		'isInner'    => ! empty( $settings['isInner'] ),
		'settings'   => $settings,
		'elements'   => $columns,
	);
}

/**
 * Theme image URL helper.
 *
 * @param string $file File name in assets/images.
 * @return string
 */
function hardware_forge_el_img( $file ) {
	return HARDWARE_FORGE_URI . '/assets/images/' . ltrim( $file, '/' );
}

/**
 * Full Elementor document structure for the hardware home page.
 *
 * @return array
 */
function hardware_forge_get_elementor_home_document() {
	$shop = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$name = get_bloginfo( 'name' );

	$hero = hardware_forge_el_section(
		array(
			hardware_forge_el_column(
				array(
					hardware_forge_el_widget(
						'heading',
						array(
							'title'       => $name ? $name : 'Hardware Forge',
							'header_size' => 'p',
							'align'       => 'left',
							'title_color' => '#FFCC00',
							'typography_typography' => 'custom',
							'typography_font_family' => 'Outfit',
							'typography_font_weight' => '800',
							'typography_font_size'   => array( 'unit' => 'px', 'size' => 48, 'sizes' => array() ),
							'typography_text_transform' => 'uppercase',
						)
					),
					hardware_forge_el_widget(
						'heading',
						array(
							'title'       => __( 'Register your tools. Lock in warranty that lasts.', 'hardware-forge' ),
							'header_size' => 'h1',
							'align'       => 'left',
							'title_color' => '#FFFFFF',
							'typography_typography' => 'custom',
							'typography_font_family' => 'Outfit',
							'typography_font_weight' => '700',
							'typography_font_size'   => array( 'unit' => 'px', 'size' => 40, 'sizes' => array() ),
						)
					),
					hardware_forge_el_widget(
						'text-editor',
						array(
							'editor'     => '<p>' . esc_html__( 'Online product warranty for power tools, generators, and jobsite gear — built for crews who work hard every day.', 'hardware-forge' ) . '</p>',
							'text_color' => '#DDDDDD',
						)
					),
					hardware_forge_el_widget(
						'button',
						array(
							'text'            => __( 'Register warranty', 'hardware-forge' ),
							'link'            => array( 'url' => home_url( '/warranty/' ) ),
							'background_color'=> '#FFFFFF',
							'button_text_color'=> '#0A0A0A',
							'border_radius'   => array( 'unit' => 'px', 'top' => 40, 'right' => 40, 'bottom' => 40, 'left' => 40, 'isLinked' => true ),
						)
					),
					hardware_forge_el_widget(
						'button',
						array(
							'text'            => __( 'Shop catalogue', 'hardware-forge' ),
							'link'            => array( 'url' => $shop ),
							'background_color'=> 'rgba(0,0,0,0)',
							'button_text_color'=> '#FFFFFF',
							'border_border'   => 'solid',
							'border_width'    => array( 'unit' => 'px', 'top' => 2, 'right' => 2, 'bottom' => 2, 'left' => 2, 'isLinked' => true ),
							'border_color'    => '#FFFFFF',
							'border_radius'   => array( 'unit' => 'px', 'top' => 40, 'right' => 40, 'bottom' => 40, 'left' => 40, 'isLinked' => true ),
						)
					),
				),
				array( '_column_size' => 100 )
			),
		),
		array(
			'layout'              => 'full_width',
			'content_width'       => array( 'unit' => 'px', 'size' => 1240, 'sizes' => array() ),
			'height'              => 'min-height',
			'custom_height'       => array( 'unit' => 'vh', 'size' => 78, 'sizes' => array() ),
			'background_background' => 'classic',
			'background_image'    => array(
				'url' => hardware_forge_el_img( 'hero-power.jpg' ),
				'id'  => '',
			),
			'background_position' => 'center center',
			'background_size'     => 'cover',
			'background_overlay_background' => 'classic',
			'background_overlay_color'      => '#0A0A0A',
			'background_overlay_opacity'    => array( 'unit' => 'px', 'size' => 0.55, 'sizes' => array() ),
			'padding' => array(
				'unit'     => 'px',
				'top'      => 120,
				'right'    => 24,
				'bottom'   => 80,
				'left'     => 24,
				'isLinked' => false,
			),
		)
	);

	$categories = array(
		array( 'Power Tools', 'cat-power-tools.jpg' ),
		array( 'Welding Machines & Accessories', 'cat-welding.jpg' ),
		array( 'Generators', 'cat-generators.jpg' ),
		array( 'Pumps & Motors', 'cat-pumps.jpg' ),
		array( 'Hand Tools', 'cat-hand-tools.jpg' ),
		array( 'Lighting & Electrical', 'cat-electrical.jpg' ),
		array( 'Water Systems', 'cat-water.jpg' ),
		array( 'Accessories & Abrasives', 'cat-accessories.jpg' ),
	);

	$cat_widgets = array();
	foreach ( $categories as $cat ) {
		$cat_widgets[] = hardware_forge_el_column(
			array(
				hardware_forge_el_widget(
					'image-box',
					array(
						'image'           => array( 'url' => hardware_forge_el_img( $cat[1] ), 'id' => '' ),
						'title_text'      => $cat[0],
						'description_text'=> '',
						'link'            => array( 'url' => $shop ),
						'position'        => 'top',
						'title_color'     => '#0A0A0A',
					)
				),
			),
			array( '_column_size' => 25, '_inline_size' => 25 )
		);
	}

	// Split categories into two rows of 4.
	$row1 = array_slice( $cat_widgets, 0, 4 );
	$row2 = array_slice( $cat_widgets, 4, 4 );

	$cat_header = hardware_forge_el_section(
		array(
			hardware_forge_el_column(
				array(
					hardware_forge_el_widget(
						'heading',
						array(
							'title'       => __( 'Our Products', 'hardware-forge' ),
							'header_size' => 'h2',
							'align'       => 'center',
							'title_color' => '#0A0A0A',
							'typography_typography' => 'custom',
							'typography_font_family' => 'Outfit',
							'typography_font_weight' => '800',
						)
					),
					hardware_forge_el_widget(
						'text-editor',
						array(
							'editor' => '<p style="text-align:center">' . esc_html__( 'Catalogue staples for the jobsite — edit titles, images, and links freely in Elementor.', 'hardware-forge' ) . '</p>',
						)
					),
				)
			),
		),
		array(
			'background_background' => 'classic',
			'background_color'      => '#F6F6F6',
			'padding' => array( 'unit' => 'px', 'top' => 70, 'right' => 20, 'bottom' => 20, 'left' => 20, 'isLinked' => false ),
		)
	);

	$cat_row_1 = hardware_forge_el_section(
		$row1,
		array(
			'background_background' => 'classic',
			'background_color'      => '#F6F6F6',
			'gap'                   => 'extended',
			'padding' => array( 'unit' => 'px', 'top' => 10, 'right' => 20, 'bottom' => 10, 'left' => 20, 'isLinked' => false ),
		)
	);

	$cat_row_2 = hardware_forge_el_section(
		$row2,
		array(
			'background_background' => 'classic',
			'background_color'      => '#F6F6F6',
			'gap'                   => 'extended',
			'padding' => array( 'unit' => 'px', 'top' => 10, 'right' => 20, 'bottom' => 40, 'left' => 20, 'isLinked' => false ),
		)
	);

	$cat_cta = hardware_forge_el_section(
		array(
			hardware_forge_el_column(
				array(
					hardware_forge_el_widget(
						'button',
						array(
							'text'             => __( 'View more', 'hardware-forge' ),
							'align'            => 'center',
							'link'             => array( 'url' => $shop ),
							'background_color' => '#FFCC00',
							'button_text_color'=> '#0A0A0A',
							'border_radius'    => array( 'unit' => 'px', 'top' => 40, 'right' => 40, 'bottom' => 40, 'left' => 40, 'isLinked' => true ),
						)
					),
				)
			),
		),
		array(
			'background_background' => 'classic',
			'background_color'      => '#F6F6F6',
			'padding' => array( 'unit' => 'px', 'top' => 0, 'right' => 20, 'bottom' => 70, 'left' => 20, 'isLinked' => false ),
		)
	);

	$story = hardware_forge_el_section(
		array(
			hardware_forge_el_column(
				array(
					hardware_forge_el_widget(
						'image',
						array(
							'image' => array( 'url' => hardware_forge_el_img( 'storefront.jpg' ), 'id' => '' ),
						)
					),
				),
				array( '_column_size' => 50, '_inline_size' => 50 )
			),
			hardware_forge_el_column(
				array(
					hardware_forge_el_widget(
						'heading',
						array(
							'title'       => __( 'A hardware brand built for real work.', 'hardware-forge' ),
							'header_size' => 'h2',
							'title_color' => '#0A0A0A',
							'typography_typography' => 'custom',
							'typography_font_family' => 'Outfit',
							'typography_font_weight' => '800',
						)
					),
					hardware_forge_el_widget(
						'text-editor',
						array(
							'editor' => '<p>' . esc_html__( 'From power tools to generators, we supply tradespeople and serious DIYers with dependable equipment — stocked nationwide and backed by service centers.', 'hardware-forge' ) . '</p>',
						)
					),
					hardware_forge_el_widget(
						'button',
						array(
							'text'             => __( 'About us', 'hardware-forge' ),
							'link'             => array( 'url' => home_url( '/about/' ) ),
							'background_color' => '#FFCC00',
							'button_text_color'=> '#0A0A0A',
							'border_radius'    => array( 'unit' => 'px', 'top' => 40, 'right' => 40, 'bottom' => 40, 'left' => 40, 'isLinked' => true ),
						)
					),
				),
				array( '_column_size' => 50, '_inline_size' => 50 )
			),
		),
		array(
			'gap'     => 'extended',
			'padding' => array( 'unit' => 'px', 'top' => 80, 'right' => 20, 'bottom' => 80, 'left' => 20, 'isLinked' => false ),
		)
	);

	$featured = hardware_forge_el_section(
		array(
			hardware_forge_el_column(
				array(
					hardware_forge_el_widget(
						'heading',
						array(
							'title'       => __( 'Featured Products', 'hardware-forge' ),
							'header_size' => 'h2',
							'align'       => 'center',
							'title_color' => '#0A0A0A',
							'typography_typography' => 'custom',
							'typography_font_family' => 'Outfit',
							'typography_font_weight' => '800',
						)
					),
					hardware_forge_el_widget(
						'shortcode',
						array(
							'shortcode' => '[products limit="6" columns="3" visibility="featured" orderby="popularity"]',
						)
					),
					hardware_forge_el_widget(
						'text-editor',
						array(
							'editor' => '<p style="text-align:center;color:#5c5c5c">' . esc_html__( 'Tip: mark WooCommerce products as Featured — or replace this shortcode widget with an Elementor Loop Grid / Products widget.', 'hardware-forge' ) . '</p>',
						)
					),
				)
			),
		),
		array(
			'padding' => array( 'unit' => 'px', 'top' => 70, 'right' => 20, 'bottom' => 70, 'left' => 20, 'isLinked' => false ),
		)
	);

	$locate = hardware_forge_el_section(
		array(
			hardware_forge_el_column(
				array(
					hardware_forge_el_widget(
						'heading',
						array(
							'title'       => __( 'Find a store near you', 'hardware-forge' ),
							'header_size' => 'h2',
							'title_color' => '#0A0A0A',
							'typography_typography' => 'custom',
							'typography_font_family' => 'Outfit',
							'typography_font_weight' => '800',
						)
					),
					hardware_forge_el_widget(
						'text-editor',
						array(
							'editor' => '<p>' . esc_html__( 'Visit a branch for hands-on demos, spare parts, and after-sales support.', 'hardware-forge' ) . '</p>',
						)
					),
					hardware_forge_el_widget(
						'button',
						array(
							'text'             => __( 'Store locator', 'hardware-forge' ),
							'link'             => array( 'url' => home_url( '/store-locator/' ) ),
							'background_color' => '#0A0A0A',
							'button_text_color'=> '#FFFFFF',
							'border_radius'    => array( 'unit' => 'px', 'top' => 40, 'right' => 40, 'bottom' => 40, 'left' => 40, 'isLinked' => true ),
						)
					),
				),
				array( '_column_size' => 50, '_inline_size' => 50 )
			),
			hardware_forge_el_column(
				array(
					hardware_forge_el_widget(
						'heading',
						array(
							'title'       => __( 'You may also shop online', 'hardware-forge' ),
							'header_size' => 'h3',
							'title_color' => '#0A0A0A',
							'typography_typography' => 'custom',
							'typography_font_family' => 'Outfit',
							'typography_font_weight' => '700',
						)
					),
					hardware_forge_el_widget(
						'text-editor',
						array(
							'editor' => '<p><strong>Lazada</strong> &nbsp;·&nbsp; <strong>Shopee</strong> &nbsp;·&nbsp; <strong>TikTok Shop</strong></p>',
						)
					),
				),
				array( '_column_size' => 50, '_inline_size' => 50 )
			),
		),
		array(
			'background_background' => 'classic',
			'background_color'      => '#F6F6F6',
			'gap'                   => 'extended',
			'padding' => array( 'unit' => 'px', 'top' => 70, 'right' => 20, 'bottom' => 70, 'left' => 20, 'isLinked' => false ),
		)
	);

	$club = hardware_forge_el_section(
		array(
			hardware_forge_el_column(
				array(
					hardware_forge_el_widget(
						'heading',
						array(
							'title'       => __( 'Join the Tough Club', 'hardware-forge' ),
							'header_size' => 'h2',
							'align'       => 'center',
							'title_color' => '#0A0A0A',
							'typography_typography' => 'custom',
							'typography_font_family' => 'Outfit',
							'typography_font_weight' => '800',
						)
					),
					hardware_forge_el_widget(
						'text-editor',
						array(
							'editor' => '<p style="text-align:center">' . esc_html__( 'Get new releases, franchise openings, and exclusive tool drops — replace this section with an Elementor Form widget.', 'hardware-forge' ) . '</p>',
						)
					),
					hardware_forge_el_widget(
						'button',
						array(
							'text'             => __( 'Open form settings in Elementor', 'hardware-forge' ),
							'align'            => 'center',
							'link'             => array( 'url' => '#' ),
							'background_color' => '#FFCC00',
							'button_text_color'=> '#0A0A0A',
							'border_radius'    => array( 'unit' => 'px', 'top' => 40, 'right' => 40, 'bottom' => 40, 'left' => 40, 'isLinked' => true ),
						)
					),
				)
			),
		),
		array(
			'padding' => array( 'unit' => 'px', 'top' => 70, 'right' => 20, 'bottom' => 70, 'left' => 20, 'isLinked' => false ),
		)
	);

	$community = hardware_forge_el_section(
		array(
			hardware_forge_el_column(
				array(
					hardware_forge_el_widget(
						'heading',
						array(
							'title'       => __( 'When the crew shows up, the job gets done.', 'hardware-forge' ),
							'header_size' => 'h2',
							'title_color' => '#FFFFFF',
							'typography_typography' => 'custom',
							'typography_font_family' => 'Outfit',
							'typography_font_weight' => '800',
						)
					),
					hardware_forge_el_widget(
						'text-editor',
						array(
							'editor'     => '<p>' . esc_html__( 'See how builders, partners, and training academies put durable tools to work — from woodshop demos to full jobsite builds.', 'hardware-forge' ) . '</p>',
							'text_color' => '#CCCCCC',
						)
					),
					hardware_forge_el_widget(
						'button',
						array(
							'text'             => __( 'Watch highlights', 'hardware-forge' ),
							'link'             => array( 'url' => home_url( '/campaigns/' ) ),
							'background_color' => '#FFCC00',
							'button_text_color'=> '#0A0A0A',
							'border_radius'    => array( 'unit' => 'px', 'top' => 40, 'right' => 40, 'bottom' => 40, 'left' => 40, 'isLinked' => true ),
						)
					),
				),
				array( '_column_size' => 50, '_inline_size' => 50 )
			),
			hardware_forge_el_column(
				array(
					hardware_forge_el_widget(
						'image',
						array(
							'image' => array( 'url' => hardware_forge_el_img( 'community.jpg' ), 'id' => '' ),
						)
					),
				),
				array( '_column_size' => 50, '_inline_size' => 50 )
			),
		),
		array(
			'background_background' => 'classic',
			'background_color'      => '#0A0A0A',
			'gap'                   => 'extended',
			'padding' => array( 'unit' => 'px', 'top' => 80, 'right' => 20, 'bottom' => 80, 'left' => 20, 'isLinked' => false ),
		)
	);

	$franchise = hardware_forge_el_section(
		array(
			hardware_forge_el_column(
				array(
					hardware_forge_el_widget(
						'image',
						array(
							'image' => array( 'url' => hardware_forge_el_img( 'storefront.jpg' ), 'id' => '' ),
						)
					),
				),
				array( '_column_size' => 50, '_inline_size' => 50 )
			),
			hardware_forge_el_column(
				array(
					hardware_forge_el_widget(
						'heading',
						array(
							'title'       => __( 'Be your own boss.', 'hardware-forge' ),
							'header_size' => 'h2',
							'title_color' => '#0A0A0A',
							'typography_typography' => 'custom',
							'typography_font_family' => 'Outfit',
							'typography_font_weight' => '800',
						)
					),
					hardware_forge_el_widget(
						'text-editor',
						array(
							'editor' => '<p>' . esc_html__( 'Own a hardware franchise with proven branding, training, and supply lines. Build a business that powers local builders.', 'hardware-forge' ) . '</p><p><strong>(+63) 000 000 0000</strong></p>',
						)
					),
					hardware_forge_el_widget(
						'button',
						array(
							'text'             => __( 'Franchise now', 'hardware-forge' ),
							'link'             => array( 'url' => home_url( '/franchising/' ) ),
							'background_color' => '#0A0A0A',
							'button_text_color'=> '#FFFFFF',
							'border_radius'    => array( 'unit' => 'px', 'top' => 40, 'right' => 40, 'bottom' => 40, 'left' => 40, 'isLinked' => true ),
						)
					),
				),
				array( '_column_size' => 50, '_inline_size' => 50 )
			),
		),
		array(
			'background_background' => 'classic',
			'background_color'      => '#FFCC00',
			'gap'                   => 'no',
			'padding' => array( 'unit' => 'px', 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0, 'isLinked' => true ),
		)
	);

	return array(
		$hero,
		$cat_header,
		$cat_row_1,
		$cat_row_2,
		$cat_cta,
		$story,
		$featured,
		$locate,
		$club,
		$community,
		$franchise,
	);
}

/**
 * Ensure a static Home page exists and return its ID.
 *
 * @return int
 */
function hardware_forge_ensure_home_page() {
	$front_id = (int) get_option( 'page_on_front' );
	if ( $front_id && get_post( $front_id ) ) {
		return $front_id;
	}

	$existing = get_page_by_path( 'home' );
	if ( $existing ) {
		$front_id = (int) $existing->ID;
	} else {
		$front_id = wp_insert_post(
			array(
				'post_title'   => __( 'Home', 'hardware-forge' ),
				'post_name'    => 'home',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			)
		);
	}

	if ( $front_id && ! is_wp_error( $front_id ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $front_id );
		return (int) $front_id;
	}

	return 0;
}

/**
 * Inject Elementor starter layout onto the Home page.
 *
 * @param bool $force Overwrite existing Elementor data.
 * @return true|WP_Error
 */
function hardware_forge_apply_elementor_home_starter( $force = false ) {
	if ( ! class_exists( '\Elementor\Plugin' ) ) {
		return new WP_Error( 'no_elementor', __( 'Activate Elementor first, then load the starter layout.', 'hardware-forge' ) );
	}

	$page_id = hardware_forge_ensure_home_page();
	if ( ! $page_id ) {
		return new WP_Error( 'no_page', __( 'Could not create the Home page.', 'hardware-forge' ) );
	}

	$existing = get_post_meta( $page_id, '_elementor_data', true );
	if ( ! $force && ! empty( $existing ) && '[]' !== $existing ) {
		return true; // Already built — do not clobber edits.
	}

	$data = hardware_forge_get_elementor_home_document();
	$json = wp_json_encode( $data );

	update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $page_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.20.0' );
	update_post_meta( $page_id, '_elementor_data', wp_slash( $json ) );
	update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );

	// Clear compiled CSS so Elementor regenerates.
	delete_post_meta( $page_id, '_elementor_css' );
	if ( class_exists( '\Elementor\Plugin' ) ) {
		$files = \Elementor\Plugin::$instance->files_manager ?? null;
		if ( $files && method_exists( $files, 'clear_cache' ) ) {
			$files->clear_cache();
		}
	}

	update_option( 'hardware_forge_elementor_home_seeded', 1 );

	return true;
}

/**
 * Auto-seed once when Elementor becomes available and home is empty.
 */
add_action( 'elementor/init', 'hardware_forge_maybe_seed_elementor_home' );
add_action( 'after_switch_theme', 'hardware_forge_maybe_seed_elementor_home' );

/**
 * Seed starter if needed.
 */
function hardware_forge_maybe_seed_elementor_home() {
	if ( ! class_exists( '\Elementor\Plugin' ) ) {
		return;
	}
	if ( get_option( 'hardware_forge_elementor_home_seeded' ) ) {
		return;
	}
	hardware_forge_apply_elementor_home_starter( false );
}

/**
 * Admin UI: load / reload Elementor starter.
 */
add_action( 'admin_menu', 'hardware_forge_register_elementor_admin_page' );

/**
 * Register theme tools page.
 */
function hardware_forge_register_elementor_admin_page() {
	add_theme_page(
		__( 'Hardware Forge', 'hardware-forge' ),
		__( 'Hardware Forge', 'hardware-forge' ),
		'edit_theme_options',
		'hardware-forge-setup',
		'hardware_forge_render_setup_page'
	);
}

/**
 * Render setup page.
 */
function hardware_forge_render_setup_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$message = '';
	if ( isset( $_POST['hardware_forge_load_starter'] ) && check_admin_referer( 'hardware_forge_load_starter' ) ) {
		$force  = ! empty( $_POST['hardware_forge_force'] );
		$result = hardware_forge_apply_elementor_home_starter( $force );
		if ( is_wp_error( $result ) ) {
			$message = '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
		} else {
			$page_id = (int) get_option( 'page_on_front' );
			$link    = admin_url( 'post.php?post=' . $page_id . '&action=elementor' );
			$message = '<div class="notice notice-success"><p>' .
				esc_html__( 'Elementor starter layout loaded on Home.', 'hardware-forge' ) .
				' <a href="' . esc_url( $link ) . '">' . esc_html__( 'Edit with Elementor', 'hardware-forge' ) . '</a>' .
				'</p></div>';
		}
	}

	$page_id = (int) get_option( 'page_on_front' );
	$edit    = $page_id ? admin_url( 'post.php?post=' . $page_id . '&action=elementor' ) : '';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Hardware Forge — Elementor setup', 'hardware-forge' ); ?></h1>
		<?php echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<p><?php esc_html_e( 'Homepage content is meant to be edited in Elementor (headings, images, buttons, products, forms). Header / footer can also be replaced with Elementor Pro Theme Builder.', 'hardware-forge' ); ?></p>
		<?php if ( $edit ) : ?>
			<p><a class="button button-primary button-hero" href="<?php echo esc_url( $edit ); ?>"><?php esc_html_e( 'Edit Home with Elementor', 'hardware-forge' ); ?></a></p>
		<?php endif; ?>
		<hr>
		<form method="post">
			<?php wp_nonce_field( 'hardware_forge_load_starter' ); ?>
			<p><?php esc_html_e( 'Load the Powerhouse-style starter sections into the Home page as native Elementor widgets.', 'hardware-forge' ); ?></p>
			<label>
				<input type="checkbox" name="hardware_forge_force" value="1">
				<?php esc_html_e( 'Overwrite existing Elementor content on Home', 'hardware-forge' ); ?>
			</label>
			<?php submit_button( __( 'Load Elementor starter layout', 'hardware-forge' ), 'secondary', 'hardware_forge_load_starter' ); ?>
		</form>
	</div>
	<?php
}

/**
 * Admin notice pointing editors to Elementor.
 */
add_action( 'admin_notices', 'hardware_forge_elementor_admin_notice' );

/**
 * Show notice on dashboard / themes screen.
 */
function hardware_forge_elementor_admin_notice() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! in_array( $screen->id, array( 'themes', 'dashboard', 'toplevel_page_elementor' ), true ) ) {
		return;
	}
	$url = admin_url( 'themes.php?page=hardware-forge-setup' );
	echo '<div class="notice notice-info"><p><strong>Hardware Forge:</strong> ';
	esc_html_e( 'Homepage content is Elementor-editable.', 'hardware-forge' );
	echo ' <a href="' . esc_url( $url ) . '">' . esc_html__( 'Open setup', 'hardware-forge' ) . '</a></p></div>';
}
