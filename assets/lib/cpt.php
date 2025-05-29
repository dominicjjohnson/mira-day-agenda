<?php
// CPT 

// Add custom fields for highlight color and text color to the 'track' taxonomy
function add_track_color_fields($term) {
	$highlight_color = get_term_meta($term->term_id, 'highlight_color', true);
	$text_color = get_term_meta($term->term_id, 'text_color', true);
	?>
	<tr class="form-field">
		<th scope="row" valign="top">
			<label for="highlight_color"><?php _e('Background Colour', 'textdomain'); ?></label>
		</th>
		<td>
			<input type="color" name="highlight_color" id="highlight_color" value="<?php echo esc_attr($highlight_color); ?>" />
			<p class="description"><?php _e('Select a highlight colour for this track.', 'textdomain'); ?></p>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top">
			<label for="text_color"><?php _e('Text Colour', 'textdomain'); ?></label>
		</th>
		<td>
			<input type="color" name="text_color" id="text_color" value="<?php echo esc_attr($text_color); ?>" />
			<p class="description"><?php _e('Select a text colour for this track.', 'textdomain'); ?></p>
		</td>
	</tr>
	<?php
}
add_action('track_edit_form_fields', 'add_track_color_fields');

// Save the custom field values for highlight color and text color
function save_track_color_fields($term_id) {
	if (isset($_POST['highlight_color'])) {
		update_term_meta($term_id, 'highlight_color', sanitize_hex_color($_POST['highlight_color']));
	}
	if (isset($_POST['text_color'])) {
		update_term_meta($term_id, 'text_color', sanitize_hex_color($_POST['text_color']));
	}
}
add_action('edited_track', 'save_track_color_fields');

// Add highlight and text colors to seminars linked to the track
function get_seminar_colors($seminar_id) {
	$tracks = wp_get_post_terms($seminar_id, 'track');
	if (!empty($tracks) && !is_wp_error($tracks)) {
		$highlight_color = get_term_meta($tracks[0]->term_id, 'highlight_color', true);
		$text_color = get_term_meta($tracks[0]->term_id, 'text_color', true);
		return [
			'highlight_color' => $highlight_color ? $highlight_color : '#ffffff', // Default to white
			'text_color' => $text_color ? $text_color : '#000000', // Default to black
		];
	}
	return [
		'highlight_color' => '#ffffff',
		'text_color' => '#000000',
	];
}

