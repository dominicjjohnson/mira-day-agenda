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
    if (isset($_POST['mira_agenda_get_char_limit_value']) || isset($_POST['mira_agenda_use_myagenda'])) {
        // Get existing options or initialize
        $options = get_option('mira_agenda_settings', []);
        
        // Save character limit
        if (isset($_POST['mira_agenda_get_char_limit_value'])) {
            $options['more_button_char_limit'] = intval($_POST['mira_agenda_get_char_limit_value']);
        }
        
        // Save Use MyAgenda setting
        if (isset($_POST['mira_agenda_use_myagenda'])) {
            $options['use_myagenda'] = ($_POST['mira_agenda_use_myagenda'] === 'true');
        }
        
        update_option('mira_agenda_settings', $options);
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }
    
    // Get current settings
    $options = get_option('mira_agenda_settings', []);
    $current_value = isset($options['more_button_char_limit']) ? (int)$options['more_button_char_limit'] : 200;
    $use_myagenda = isset($options['use_myagenda']) ? $options['use_myagenda'] : false;
    ?>
    <div class="wrap">
        <h1>Day Agenda Settings</h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="mira_agenda_get_char_limit_value">Character Limit for Session Content:</label>
                    </th>
                    <td>
                        <input type="number" id="mira_agenda_get_char_limit_value" name="mira_agenda_get_char_limit_value" value="<?php echo esc_attr($current_value); ?>" min="1" />
                        <p class="description">Set the maximum character length for session content before truncation.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Use MyAgenda:</th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio" name="mira_agenda_use_myagenda" value="true" <?php checked($use_myagenda, true); ?> />
                                Yes
                            </label>
                            <br>
                            <label>
                                <input type="radio" name="mira_agenda_use_myagenda" value="false" <?php checked($use_myagenda, false); ?> />
                                No
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" class="button button-primary" value="Save Changes" />
            </p>
        </form>
    </div>
    <?php
}
