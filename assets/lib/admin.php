<?php
// Add submenu page under Seminars menu
add_action('admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=seminars', // Parent slug for Seminars CPT
        'Day Agenda Settings',         // Page title
        'Day Agenda Settings',         // Menu title
        'manage_options',              // Capability
        'day-agenda-settings',         // Menu slug
        'mira_agenda_settings_page'    // Callback function
    );
});

// Settings page callback
function mira_agenda_settings_page() {
    // Handle form submission
    if (isset($_POST['mira_agenda_get_char_limit_value'])) {
        $value = intval($_POST['mira_agenda_get_char_limit_value']);
        // Get existing options or initialize
        $options = get_option('mira_agenda_settings', []);
        $options['more_button_char_limit'] = $value;
        update_option('mira_agenda_settings', $options);
        echo '<div class="updated"><p>Character limit saved.</p></div>';
        }
        $options = get_option('mira_agenda_settings', []);
        $current_value = isset($options['more_button_char_limit']) ? (int)$options['more_button_char_limit'] : 200;
        ?>
        <div class="wrap">
        <h1>Day Agenda Settings</h1>
        <form method="post">
            <label for="mira_agenda_get_char_limit_value">Character Limit for Session Content:</label>
            <input type="number" id="mira_agenda_get_char_limit_value" name="mira_agenda_get_char_limit_value" value="<?php echo esc_attr($current_value); ?>" min="1" />
            <p class="description">Set the maximum character length for session content before truncation.</p>
            <input type="submit" class="button button-primary" value="Save Changes" />
        </form>
        </div>
        <?php
}
