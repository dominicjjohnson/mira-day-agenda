<?php
/**
 * MyAgenda Admin - top-level menu and settings.
 */

// ---------------------------------------------------------------
// Top-level admin menu: MyAgenda
// ---------------------------------------------------------------
add_action( 'admin_menu', 'mira_myagenda_register_menu' );
function mira_myagenda_register_menu() {
	add_menu_page(
		'MyAgenda Settings',          // Page title
		'MyAgenda',                   // Menu label
		'manage_options',             // Capability
		'mira-myagenda',              // Slug
		'mira_myagenda_settings_page', // Callback
		'dashicons-calendar-alt',     // Icon
		58                            // Position (below Appearance)
	);
}

// ---------------------------------------------------------------
// Colour picker assets, MyAgenda settings page only
// ---------------------------------------------------------------
add_action( 'admin_enqueue_scripts', 'mira_myagenda_settings_colour_picker_assets' );
function mira_myagenda_settings_colour_picker_assets( $hook ) {
	if ( $hook !== 'toplevel_page_mira-myagenda' ) {
		return;
	}
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	wp_add_inline_script( 'wp-color-picker', 'jQuery(function($){ $(".mira-color-field").wpColorPicker(); });' );
}

// ---------------------------------------------------------------
// Settings page
// ---------------------------------------------------------------
function mira_myagenda_settings_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Handle form submission
	if ( isset( $_POST['_mira_myagenda_nonce'] ) && wp_verify_nonce( $_POST['_mira_myagenda_nonce'], 'mira_myagenda_save' ) ) {
		$options = get_option( 'mira_agenda_settings', array() );

		$options['more_button_char_limit']  = isset( $_POST['more_button_char_limit'] ) ? absint( $_POST['more_button_char_limit'] ) : 200;
		$options['enable_my_diary']         = isset( $_POST['enable_my_diary'] ) ? 'yes' : 'no';
		$options['show_on_exhibitor_list']  = isset( $_POST['show_on_exhibitor_list'] ) ? true : false;
		$options['myagenda_btn_color']        = isset( $_POST['myagenda_btn_color'] )        ? sanitize_hex_color( $_POST['myagenda_btn_color'] )        : '';
		$options['myagenda_btn_color_active'] = isset( $_POST['myagenda_btn_color_active'] ) ? sanitize_hex_color( $_POST['myagenda_btn_color_active'] ) : '';
		$allowed_styles = array( 'grid', 'list', 'compact' );
		$options['diary_display_style']     = in_array( $_POST['diary_display_style'] ?? '', $allowed_styles ) ? $_POST['diary_display_style'] : 'grid';

		update_option( 'mira_agenda_settings', $options );
		echo '<div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>';
	}

	$options = get_option( 'mira_agenda_settings', array() );
	$char_limit           = isset( $options['more_button_char_limit'] ) ? (int) $options['more_button_char_limit'] : 200;
	$enable_my_diary      = isset( $options['enable_my_diary'] ) ? $options['enable_my_diary'] : 'yes';
	$show_on_exhibitor    = ! empty( $options['show_on_exhibitor_list'] );
	$diary_display_style  = isset( $options['diary_display_style'] ) ? $options['diary_display_style'] : 'grid';
	$myagenda_btn_color        = ! empty( $options['myagenda_btn_color'] )        ? $options['myagenda_btn_color']        : '#e67e22';
	$myagenda_btn_color_active = ! empty( $options['myagenda_btn_color_active'] ) ? $options['myagenda_btn_color_active'] : '#999999';
	?>
	<div class="wrap">
		<h1>MyAgenda Settings</h1>

		<form method="post">
			<?php wp_nonce_field( 'mira_myagenda_save', '_mira_myagenda_nonce' ); ?>

			<h2 class="title">Agenda Grid</h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="more_button_char_limit">Session content character limit</label></th>
					<td>
						<input type="number" id="more_button_char_limit" name="more_button_char_limit"
							value="<?php echo esc_attr( $char_limit ); ?>" min="50" max="2000" step="10" class="small-text" />
						<p class="description">Characters shown before a "More" button appears. Default: 200.</p>
					</td>
				</tr>
				<tr>
					<th scope="row">My Diary feature</th>
					<td>
						<label>
							<input type="checkbox" name="enable_my_diary" value="yes" <?php checked( $enable_my_diary, 'yes' ); ?> />
							Enable "Add to My Diary" buttons on session cards
						</label>
					</td>
				</tr>
			</table>

			<h2 class="title">My Personal Agenda Display</h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="diary_display_style">Display style</label></th>
				<td>
					<select id="diary_display_style" name="diary_display_style">
						<option value="grid"    <?php selected( $diary_display_style, 'grid' );    ?>>Grid</option>
						<option value="list"    <?php selected( $diary_display_style, 'list' );    ?>>List</option>
						<option value="compact" <?php selected( $diary_display_style, 'compact' ); ?>>Compact</option>
					</select>
					<p class="description">How sessions and exhibitors are displayed in <code>[display-my-diary]</code>.</p>
				</td>
			</tr>
		</table>

		<h2 class="title">Exhibitor List Integration</h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">Show on Exhibitor List</th>
					<td>
						<label>
							<input type="checkbox" name="show_on_exhibitor_list" value="1" <?php checked( $show_on_exhibitor ); ?> />
							Add a small <strong>My Itinerary</strong> button next to each exhibitor name in the list
						</label>
						<p class="description">Default: off. Requires the cw-plugin-exhibitors plugin.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="myagenda_btn_color">Button colour</label></th>
					<td>
						<input type="text" id="myagenda_btn_color" name="myagenda_btn_color" class="mira-color-field" value="<?php echo esc_attr( $myagenda_btn_color ); ?>" data-default-color="#e67e22" />
						<p class="description">Background colour of the button when the exhibitor is <strong>not</strong> in the itinerary.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="myagenda_btn_color_active">Button colour (in itinerary)</label></th>
					<td>
						<input type="text" id="myagenda_btn_color_active" name="myagenda_btn_color_active" class="mira-color-field" value="<?php echo esc_attr( $myagenda_btn_color_active ); ?>" data-default-color="#999999" />
						<p class="description">Background colour of the button once the exhibitor has been added to the itinerary.</p>
					</td>
				</tr>
			</table>

			<?php submit_button( 'Save Settings' ); ?>
		</form>

		<hr>
		<h2>Shortcode Reference</h2>
		<table class="widefat striped" style="max-width:700px">
			<thead><tr><th>Shortcode</th><th>Description</th></tr></thead>
			<tbody>
				<tr><td><code>[agenda-grid day="YYYY-MM-DD"]</code></td><td>Displays the day agenda grid</td></tr>
				<tr><td><code>[display-my-diary]</code></td><td>Displays the visitor's saved diary</td></tr>
				<tr><td><code>[mira_modal]</code></td><td>Modal popup container</td></tr>
				<tr><td><code>[debug-my-diary]</code></td><td>Developer debug panel</td></tr>
			</tbody>
		</table>
	</div>
	<?php
}

// ---------------------------------------------------------------
// Filter: let miramedia-my-agenda control the exhibitor list button
// ---------------------------------------------------------------
add_filter( 'mira_show_on_exhibitor_list', 'mira_myagenda_show_on_exhibitor_list' );
function mira_myagenda_show_on_exhibitor_list( $default ) {
	$options = get_option( 'mira_agenda_settings', array() );
	return ! empty( $options['show_on_exhibitor_list'] );
}
