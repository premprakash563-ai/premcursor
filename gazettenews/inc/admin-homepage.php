<?php
/**
 * Admin: Homepage Sections.
 *
 * @package GazetteNews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gazettenews_admin_menu() {
	add_theme_page(
		__( 'Homepage Sections', 'gazettenews' ),
		__( 'Homepage Sections', 'gazettenews' ),
		'edit_theme_options',
		'gazettenews-homepage',
		'gazettenews_admin_homepage_page'
	);
}
add_action( 'admin_menu', 'gazettenews_admin_menu' );

function gazettenews_admin_assets( $hook ) {
	if ( 'appearance_page_gazettenews-homepage' !== $hook ) {
		return;
	}
	wp_enqueue_style( 'gazettenews-admin-home', GAZETTENEWS_URI . '/assets/css/admin-homepage.css', array(), GAZETTENEWS_VERSION );
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'gazettenews-admin-home', GAZETTENEWS_URI . '/assets/js/admin-homepage.js', array( 'jquery', 'jquery-ui-sortable' ), GAZETTENEWS_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'gazettenews_admin_assets' );

function gazettenews_admin_homepage_save() {
	if ( ! isset( $_POST['gazettenews_home_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gazettenews_home_nonce'] ) ), 'gazettenews_save_home' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$mode = isset( $_POST['home_mode'] ) ? sanitize_key( wp_unslash( $_POST['home_mode'] ) ) : 'sections';
	if ( ! in_array( $mode, array( 'sections', 'gutenberg', 'both' ), true ) ) {
		$mode = 'sections';
	}
	update_option( 'gazettenews_home_mode', $mode );

	$raw = isset( $_POST['sections'] ) && is_array( $_POST['sections'] ) ? wp_unslash( $_POST['sections'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$out = array();
	foreach ( $raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$out[] = gazettenews_sanitize_section( $row );
	}
	update_option( 'gazettenews_home_sections', $out );

	add_settings_error( 'gazettenews_home', 'saved', __( 'Homepage sections saved.', 'gazettenews' ), 'updated' );
}

function gazettenews_admin_homepage_page() {
	if ( isset( $_POST['gazettenews_home_nonce'] ) ) {
		gazettenews_admin_homepage_save();
	}

	$mode     = gazettenews_get_home_mode();
	$sections = gazettenews_get_home_sections();
	$types    = gazettenews_section_types();
	$layouts  = gazettenews_section_layouts();
	$cats     = array( 0 => __( '— Latest posts —', 'gazettenews' ) );
	foreach ( get_categories( array( 'hide_empty' => false ) ) as $cat ) {
		$cats[ $cat->term_id ] = $cat->name;
	}
	?>
	<div class="wrap gn-home-admin">
		<h1><?php esc_html_e( 'Homepage Sections', 'gazettenews' ); ?></h1>
		<?php settings_errors( 'gazettenews_home' ); ?>

		<div class="notice notice-info">
			<p>
				<strong><?php esc_html_e( 'tagDiv Composer is not supported.', 'gazettenews' ); ?></strong>
				<?php esc_html_e( 'tagDiv is a paid Newspaper-theme plugin and cannot be bundled here. Manage modules below (same idea: add, drag, category, layout). For drag-and-drop on the page itself, use the WordPress block editor — or a GPL builder such as Gutenberg, Kadence, or GenerateBlocks. Elementor also works if you set homepage mode to Block editor.', 'gazettenews' ); ?>
			</p>
		</div>

		<form method="post">
			<?php wp_nonce_field( 'gazettenews_save_home', 'gazettenews_home_nonce' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th><?php esc_html_e( 'How to build the homepage', 'gazettenews' ); ?></th>
					<td>
						<fieldset>
							<label><input type="radio" name="home_mode" value="sections" <?php checked( $mode, 'sections' ); ?>> <?php esc_html_e( 'Section manager (Newspaper-style modules)', 'gazettenews' ); ?></label><br>
							<label><input type="radio" name="home_mode" value="gutenberg" <?php checked( $mode, 'gutenberg' ); ?>> <?php esc_html_e( 'Block editor / page builder on the Front Page only', 'gazettenews' ); ?></label><br>
							<label><input type="radio" name="home_mode" value="both" <?php checked( $mode, 'both' ); ?>> <?php esc_html_e( 'Page content first, then these sections', 'gazettenews' ); ?></label>
						</fieldset>
						<p class="description">
							<?php esc_html_e( 'Block editor mode needs Settings → Reading → A static page as the homepage. Insert Gazette News blocks from the block inserter.', 'gazettenews' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Modules', 'gazettenews' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Drag the handle to reorder. Toggle Enabled to hide a block without deleting it.', 'gazettenews' ); ?></p>

			<div id="gn-sections" class="gn-sections">
				<?php
				foreach ( $sections as $i => $section ) {
					gazettenews_admin_section_row( $i, $section, $types, $layouts, $cats );
				}
				?>
			</div>

			<p>
				<button type="button" class="button" id="gn-add-section"><?php esc_html_e( 'Add section', 'gazettenews' ); ?></button>
			</p>

			<?php submit_button( __( 'Save homepage', 'gazettenews' ) ); ?>
		</form>

		<template id="gn-section-tpl">
			<?php
			gazettenews_admin_section_row(
				'__i__',
				array(
					'id'      => '',
					'type'    => 'posts',
					'enabled' => true,
					'title'   => '',
					'cat'     => 0,
					'layout'  => 'grid',
					'count'   => 4,
					'html'    => '',
				),
				$types,
				$layouts,
				$cats
			);
			?>
		</template>
	</div>
	<?php
}

function gazettenews_admin_section_row( $i, $section, $types, $layouts, $cats ) {
	$i = (string) $i;
	?>
	<div class="gn-section-row" data-type="<?php echo esc_attr( $section['type'] ); ?>">
		<input type="hidden" name="sections[<?php echo esc_attr( $i ); ?>][id]" value="<?php echo esc_attr( $section['id'] ? $section['id'] : uniqid( 'sec', false ) ); ?>">
		<span class="gn-drag dashicons dashicons-menu" aria-hidden="true"></span>
		<div class="gn-section-main">
			<div class="gn-section-top">
				<label class="gn-enabled">
					<input type="hidden" name="sections[<?php echo esc_attr( $i ); ?>][enabled]" value="0">
					<input type="checkbox" name="sections[<?php echo esc_attr( $i ); ?>][enabled]" value="1" <?php checked( ! empty( $section['enabled'] ) ); ?>>
					<?php esc_html_e( 'Enabled', 'gazettenews' ); ?>
				</label>
				<label>
					<?php esc_html_e( 'Type', 'gazettenews' ); ?>
					<select class="gn-type" name="sections[<?php echo esc_attr( $i ); ?>][type]">
						<?php foreach ( $types as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $section['type'], $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<button type="button" class="button-link gn-remove"><?php esc_html_e( 'Remove', 'gazettenews' ); ?></button>
			</div>
			<div class="gn-fields">
				<label class="gn-f gn-f-title">
					<?php esc_html_e( 'Title', 'gazettenews' ); ?>
					<input type="text" name="sections[<?php echo esc_attr( $i ); ?>][title]" value="<?php echo esc_attr( $section['title'] ); ?>">
				</label>
				<label class="gn-f gn-f-cat">
					<?php esc_html_e( 'Category', 'gazettenews' ); ?>
					<select name="sections[<?php echo esc_attr( $i ); ?>][cat]">
						<?php foreach ( $cats as $id => $name ) : ?>
							<option value="<?php echo esc_attr( (string) $id ); ?>" <?php selected( (int) $section['cat'], (int) $id ); ?>><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label class="gn-f gn-f-layout">
					<?php esc_html_e( 'Layout', 'gazettenews' ); ?>
					<select name="sections[<?php echo esc_attr( $i ); ?>][layout]">
						<?php foreach ( $layouts as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $section['layout'], $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label class="gn-f gn-f-count">
					<?php esc_html_e( 'Count', 'gazettenews' ); ?>
					<input type="number" min="1" max="12" name="sections[<?php echo esc_attr( $i ); ?>][count]" value="<?php echo esc_attr( (string) $section['count'] ); ?>">
				</label>
				<label class="gn-f gn-f-html">
					<?php esc_html_e( 'HTML / ad code', 'gazettenews' ); ?>
					<textarea name="sections[<?php echo esc_attr( $i ); ?>][html]" rows="3"><?php echo esc_textarea( $section['html'] ); ?></textarea>
				</label>
			</div>
		</div>
	</div>
	<?php
}
