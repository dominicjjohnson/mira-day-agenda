<?php
/**
 * Plugin Name: Mira Day Agenda
 * Description: A plugin that provides a [agenda-grid] shortcode to display HTML content.
 *               Params - day = date slug from seminars > dates. defaults to 2025-10-01
 *               Displays a multi-track display for the entire day.
                
 * Version: 1.29
 * Author: Miramedia / Dominic Johnson
 * 
 * Version 1.1 - 2025-05-30 - Updated for HCE 2025
 * Version 1.0 - 2024-05-30 - Initial release for Evie
 * Version 1.2. 2025-06-02 - Finalised version, working on hce.
 * Version 1.3. 2025-06-03 - Fixed a bug in All Tracks
 * Version 1.4. 2025-06-03 - Fixed bug with this track colour names
 * Version 1.5. 2025-06-04 - Changes to get right on solar
 * Version 1.7. 2025-06-09 - Fixed heading bar.  
 * Version 1.8. 2025-06-25 - Added Param .  
 * Version 1.9. 2025-06-25 - HCE Header updates. I had to re-apply changes from version on GitHub. 
 * Version 1.10. 2025-07-03 - Added params:
   - time_slot_side - true / false. True = display it
   - show_end_time - true /false. True = display it
   - Show_session duration - true /false. True = display it

   1. if time_slot_side is False: Move the time next to the title & put a clock icon - add css class
     If true - display on the right as now.
     If false - set css rule grid-template-rows - remove the [times] 0em and set time-slot to display:none
   2. Add css class to Border to set the border colour fill...
   3. Check the font of the session titles
   4. add icon to the session type
   5. Colour. Set border or fill colour as per param. Also colour in the block around time:
        If the session has a colour then use that.
        If not display the track colour
        If not display white
   6. Round the corners of the borders and time blocks
   7. Add switch to show details yes / no
   
 * Version 1.11. 2025-07-07 - Changed for the solar media grid  
 
 * Version 1.12. 2025-07-18 - New changes, requested by David Solar in email 17-07-2025
    1. Added a new parameter "link_title_to_details". If true then link to the details page. Default - link to popup."
 
 * Version 1.13. 2025-08-06 - Added MyDiary functionality
    1. Added "Add to MyDiary" button to each seminar
    2. Clicking button adds seminar ID to "AddToDiary" cookie
    3. Button changes to "In Diary" (grey) when added
    4. Clicking "In Diary" removes seminar from cookie and resets button
    5. Cookie persists for 30 days
    6. Responsive button design with yellow/orange and grey states
 
 * Version 1.19. 2025-08-07 - Fixed browser permissions policy violation
    1. Added fix for "Permissions policy violation: unload is not allowed" error
    2. Created JavaScript fix to prevent WP Bakery beforeunload event listeners
    3. Added Permissions Policy headers for admin/editor pages
    4. Implemented override for vc.setDataChanged to avoid browser restrictions
    5. Enhanced compatibility with modern browser security policies
        
 
 
 */
 
define('DEVMODE', true); // Set to false on production
define('VERSION', "1.21"); // Updated version - refined unload policy fix


 // Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include the display & data functions file
require_once plugin_dir_path( __FILE__ ) . 'assets/lib/cpt.php';
require_once plugin_dir_path( __FILE__ ) . 'assets/lib/wp_bakery_admin_simple.php';
require_once plugin_dir_path( __FILE__ ) . 'assets/lib/display_functions.php';
require_once plugin_dir_path( __FILE__ ) . 'assets/lib/data_functions.php';

// Clear WP Bakery cache on plugin update
add_action('admin_init', function() {
    if (get_option('mira_agenda_version') !== VERSION) {
        // Clear any WP Bakery caches
        if (function_exists('vc_flush_templates_cache')) {
            vc_flush_templates_cache();
        }
        if (function_exists('vc_flush_template_cache')) {
            vc_flush_template_cache();
        }
        if (function_exists('vc_editor_post_types')) {
            vc_editor_post_types();
        }
        // Clear any WP caches
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        delete_option('wpb_js_composer_cache');
        delete_transient('vc_license');
        
        // Update version
        update_option('mira_agenda_version', VERSION);
        error_log('Mira Day Agenda: Plugin updated to version ' . VERSION . ', caches cleared');
    }
});

// Add settings page
add_action('admin_menu', 'mira_agenda_add_admin_menu');
add_action('admin_init', 'mira_agenda_settings_init');

function mira_agenda_add_admin_menu() {
    add_options_page(
        'Mira Day Agenda Settings',
        'Day Agenda',
        'manage_options',
        'mira_day_agenda',
        'mira_agenda_options_page'
    );
}

function mira_agenda_settings_init() {
    register_setting('mira_agenda_settings', 'mira_agenda_settings');

    add_settings_section(
        'mira_agenda_settings_section',
        __('Display Settings', 'mira-day-agenda'),
        'mira_agenda_settings_section_callback',
        'mira_agenda_settings'
    );

    add_settings_field(
        'more_button_char_limit',
        __('More Button Character Limit', 'mira-day-agenda'),
        'mira_agenda_more_button_char_limit_render',
        'mira_agenda_settings',
        'mira_agenda_settings_section'
    );
}

function mira_agenda_more_button_char_limit_render() {
    $options = get_option('mira_agenda_settings');
    $value = isset($options['more_button_char_limit']) ? $options['more_button_char_limit'] : 200;
    ?>
    <input type='number' name='mira_agenda_settings[more_button_char_limit]' value='<?php echo esc_attr($value); ?>' min='50' max='1000' step='10'>
    <p class="description">Number of characters to display before showing the "More" button. Default: 200</p>
    <?php
}

function mira_agenda_settings_section_callback() {
    echo __('Configure how the agenda grid displays content.', 'mira-day-agenda');
}

function mira_agenda_options_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action='options.php' method='post'>
            <?php
            settings_fields('mira_agenda_settings');
            do_settings_sections('mira_agenda_settings');
            submit_button();
            ?>
        </form>
        
        <div style="margin-top: 30px; padding: 15px; background: #f9f9f9; border-left: 4px solid #0073aa;">
            <h3>Plugin Information</h3>
            <p><strong>Version:</strong> <?php echo VERSION; ?></p>
            <p><strong>Development Mode:</strong> <?php echo DEVMODE ? 'Enabled' : 'Disabled'; ?></p>
            <p><strong>Shortcode:</strong> <code>[agenda-grid day="2025-10-01"]</code></p>
            <p><strong>WP Bakery Element:</strong> Available in Visual Composer as "Agenda Grid"</p>
        </div>
    </div>
    <?php
}

// Helper function to get the character limit setting
function mira_agenda_get_char_limit() {
    $options = get_option('mira_agenda_settings');
    return isset($options['more_button_char_limit']) ? (int)$options['more_button_char_limit'] : 200;
}

function is_mobile() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $mobile_agents = ['Android', 'iPhone', 'iPad', 'iPod', 'Windows Phone'];

    foreach ($mobile_agents as $agent) {
        if (stripos($user_agent, $agent) !== false) {
            return true; // Detected a mobile device
        }
    }
    return false; // Not a mobile device
}

function print_times($time_slots) {

  $output = "";
  foreach ($time_slots as $time) {
    $value = $time;
    $time = str_replace(':', '', $time);
    $output .= '<h2 class="time-slot" style="grid-row: time-' . esc_attr($time) . ';">' . esc_html($value) . '</h2>';
  }

return $output;
}


function display_grid ($sessions,$inputs,$headings, $track_background_colour = array()) {
  
  // this put the headings at the top of the page. We're removing this functionality for now. 
    
  $display_heading_bar_page = $inputs['display_heading_bar_page'];
  if ($display_heading_bar_page) {
    // Display the headings at the top of the page - might need to be a param.
    echo get_schedule_header($headings); // Display headings once
  }
  
  // Look to see if you are to display headings
    
  if ($inputs['display_heading_bar']) {
    $lastTrackID = null;
    $display_headings = false;
    $seenTracks = []; // Track which IDs have been processed
    
    foreach ($sessions as $rowID => $session) {
        $currentTrackID = $session['trackID'];
    
        // Reset when encountering "track-all"
        if ($currentTrackID == "track-all") {
            $seenTracks = []; // Clear seen tracks
            $display_headings = false;
        } 
        // Set display_headings to true for first occurrence of a new track after "track-all"
        elseif ($lastTrackID == "track-all" || !in_array($currentTrackID, $seenTracks)) {
          // Check that 
          if ($inputs['display_heading_bar']){
            $display_headings = true;
          }
          else {
            $display_headings = false;

          }
          $seenTracks[] = $currentTrackID; // Mark track as seen
        } else {
            $display_headings = false; // Prevent re-display for already seen tracks
        }
    
        echo display_one_session($sessions, $rowID, $inputs, $headings, $display_headings, $track_background_colour);
    
        // Store last track ID for next iteration
        $lastTrackID = $currentTrackID;
    }
  }
  else {
    // do not display the headings
    foreach ($sessions as $rowID => $session) {
        echo display_one_session($sessions, $rowID, $inputs, $headings,false, $track_background_colour);
    }
  }
}

// Register the shortcode
function mira_agenda_grid_old_shortcode($atts) {
    // Start output buffering
    ob_start();

    // Set default values for missing parameters
    $atts = shortcode_atts(array(
        'day' => '',
        'all-tracks' => '',
        'track1' => '',
        'track2' => '',
        'track3' => '',
        'track4' => '',
        'track5' => '',
        'track6' => '',
        'track7' => '',
        'track8' => '',
        'border' => 'yes',
        'display_heading_bar' => 'yes',
        'show_end_time' => 'false',
        'time_slot_side' => 'false',
        'display_seminar_type' => 'no',
        'display_seminar_duration' => 'no',
    ), $atts, 'agenda-grid');

    // Debug: Show the incoming attributes (remove this after testing)
    echo '<!-- DEBUG: Shortcode Attributes: ' . print_r($atts, true) . ' -->';
    
    // Debug: Log the incoming attributes
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Agenda Grid Shortcode Attributes: ' . print_r($atts, true));
        error_log('SHORTCODE DEBUG: Raw display_seminar_type: ' . (isset($atts['display_seminar_type']) ? $atts['display_seminar_type'] : 'NOT SET'));
        error_log('SHORTCODE DEBUG: Raw display_seminar_duration: ' . (isset($atts['display_seminar_duration']) ? $atts['display_seminar_duration'] : 'NOT SET'));
    }
    // ALWAYS LOG for testing
    error_log('=== SHORTCODE DEBUG ===');
    error_log('Raw display_seminar_type: ' . (isset($atts['display_seminar_type']) ? $atts['display_seminar_type'] : 'NOT SET'));
    error_log('Raw display_seminar_duration: ' . (isset($atts['display_seminar_duration']) ? $atts['display_seminar_duration'] : 'NOT SET'));

    // Convert WPBakery string values to proper boolean types
    if (isset($atts['display_heading_bar']) && in_array($atts['display_heading_bar'], ['yes', 'no'])) {
        $atts['display_heading_bar'] = ($atts['display_heading_bar'] === 'yes');
    }
    if (isset($atts['show_end_time']) && in_array($atts['show_end_time'], ['true', 'false'])) {
        $atts['show_end_time'] = ($atts['show_end_time'] === 'true');
    }
    if (isset($atts['time_slot_side']) && in_array($atts['time_slot_side'], ['true', 'false'])) {
        $atts['time_slot_side'] = ($atts['time_slot_side'] === 'true');
    }
    
    // Convert our new display parameters
    if (isset($atts['display_seminar_type']) && in_array($atts['display_seminar_type'], ['yes', 'no'])) {
        $atts['display_seminar_type'] = ($atts['display_seminar_type'] === 'yes');
        error_log('SHORTCODE DEBUG: display_seminar_type converted to: ' . ($atts['display_seminar_type'] ? 'true' : 'false'));
    }
    if (isset($atts['display_seminar_duration']) && in_array($atts['display_seminar_duration'], ['yes', 'no'])) {
        $atts['display_seminar_duration'] = ($atts['display_seminar_duration'] === 'yes');
        error_log('SHORTCODE DEBUG: display_seminar_duration converted to: ' . ($atts['display_seminar_duration'] ? 'true' : 'false'));
    }
    error_log('=== END SHORTCODE DEBUG ===');

    // HTML content to display with the shortcode
    $inputs = get_parameters($atts);
    if (!empty($inputs['error'])) {
        echo "<script>console.error(" . json_encode($inputs['error_message']) . ");</script>";
        return ob_get_clean();
    }

    // Fetch the arguments for the query
    // get only sessions with session-start meta value a match to the date entered YYYY-MM-DD format
    $args = get_args($inputs); // need to upadate with the date

    // Get the data for the agenda - just runs the query. Returns false if no data
    $result = get_raw_agenda_data($args);

    if ($result['error']) {
        die($result['error_message']); // Abort script execution
    }

    $data = $result['data'];
    $headings_data = get_headings($data, $inputs);
    
    $headings = $headings_data['headings'];
    $track_background_colour = $headings_data['track_background_colour'];
        
    $track_text_colour = $headings_data['track_text_colour'];

    // Get the session data - for a single day
    $sessions = get_grid_session_data($data,$inputs['trackslugs'],$inputs['all-tracks']);

    $time_slots = get_time_slots($data);

    echo get_css_slots($time_slots,$track_background_colour,$track_text_colour,$inputs); 

    echo '<div class="schedule" aria-labelledby="schedule-heading">';
        
    echo print_times($time_slots);

    echo display_grid($sessions,$inputs,$headings, $track_background_colour); 
        
    echo '</div>';

    return ob_get_clean();
}

// Register the shortcode outside the function
add_shortcode( 'agenda-grid', 'mira_agenda_grid_old_shortcode' );

// Register the Display My Diary shortcode
function display_my_diary_shortcode($atts) {
    // Start output buffering
    ob_start();

    // Set default values for parameters
    $atts = shortcode_atts(array(
        'style' => 'grid',
        'show_empty_message' => 'yes',
        'empty_message' => '',
        'show_details' => 'yes', // Always default to showing details
        'show_remove_buttons' => 'yes',
    ), $atts, 'display-my-diary');

    // Convert string values to booleans
    $show_empty_message = ($atts['show_empty_message'] === 'yes');
    $show_details = ($atts['show_details'] !== 'no'); // Show details unless explicitly set to 'no'
    $show_remove_buttons = ($atts['show_remove_buttons'] === 'yes');
    $style = esc_attr($atts['style']);
    $empty_message = !empty($atts['empty_message']) ? esc_html($atts['empty_message']) : 'Your diary is empty. Add sessions from the agenda to see them here.';

    // Generate the diary display HTML
    echo '<div class="my-diary-container my-diary-' . $style . '">';
    
    // Add refresh button header
    echo '<div class="my-diary-header">';
    echo '<h3 class="my-diary-title">My Personal Agenda</h3>';
    echo '<button class="my-diary-refresh-btn" title="Refresh diary" onclick="refreshMyDiary();">';
    echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
    echo '<path d="M23 4v6h-6M1 20v-6h6M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.64A9 9 0 0 1 3.51 15"/>';
    echo '</svg>';
    echo '</button>';
    echo '</div>';
    
    echo '<div id="my-diary-sessions">';
    
    // Empty message div - always include if show_empty_message is true
    if ($show_empty_message) {
        echo '<div class="my-diary-empty" style="display: block;">';
        echo '<p class="empty-message">' . $empty_message . '</p>';
        echo '</div>';
    }
    
    echo '<div class="my-diary-content" style="display: none;"></div>';
    echo '</div>';
    echo '</div>';

    // Add JavaScript to populate the diary
    $script = '
<script type="text/javascript">
var ajaxurl = "' . admin_url('admin-ajax.php') . '";
var myDiaryConfig = {
  style: "' . $style . '",
  showDetails: ' . ($show_details ? 'true' : 'false') . ',
  showRemoveButtons: ' . ($show_remove_buttons ? 'true' : 'false') . ',
  showEmptyMessage: ' . ($show_empty_message ? 'true' : 'false') . '
};

function refreshMyDiary() {
  console.log("Manual refresh triggered");
  if (typeof populateMyDiary === "function") {
    populateMyDiary(myDiaryConfig);
  }
}

document.addEventListener("DOMContentLoaded", function() {
  // Initial population
  if (typeof populateMyDiary === "function") {
    populateMyDiary(myDiaryConfig);
  }
  
  // Tab/window focus detection for tab-based interfaces
  window.addEventListener("focus", function() {
    console.log("Window gained focus, refreshing diary");
    if (typeof populateMyDiary === "function") {
      populateMyDiary(myDiaryConfig);
    }
  });
  
  // Page visibility API for better tab detection
  document.addEventListener("visibilitychange", function() {
    if (!document.hidden) {
      console.log("Tab became visible, refreshing diary");
      if (typeof populateMyDiary === "function") {
        setTimeout(function() {
          populateMyDiary(myDiaryConfig);
        }, 100);
      }
    }
  });
});
</script>';

    echo $script;

    // Debug: Ensure we got this far
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('display_my_diary_shortcode completed successfully');
    }

    $output = ob_get_clean();
    
    // Additional debug: check if script tag is in output
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Output contains </script>: ' . (strpos($output, '</script>') !== false ? 'YES' : 'NO'));
        error_log('Output length: ' . strlen($output));
    }
    
    return $output;
}

add_shortcode('display-my-diary', 'display_my_diary_shortcode');

// Debug shortcode to test diary functionality
function debug_my_diary_shortcode($atts) {
    ob_start();
    
    echo '<div style="background: #f9f9f9; padding: 20px; border: 1px solid #ddd; margin: 20px 0;">';
    echo '<h3>MyDiary Debug Information</h3>';
    
    // Check if we have seminar posts
    $seminars = get_posts(array(
        'post_type' => 'seminars',
        'posts_per_page' => 5,
        'post_status' => 'publish'
    ));
    
    echo '<p><strong>Available Seminars:</strong> ' . count($seminars) . '</p>';
    if (!empty($seminars)) {
        echo '<ul>';
        foreach ($seminars as $seminar) {
            echo '<li>ID: ' . $seminar->ID . ' - ' . $seminar->post_title . '</li>';
        }
        echo '</ul>';
    }
    
    // JavaScript to show cookie contents and test functionality
    echo '<p><strong>Cookie Contents:</strong> <span id="cookie-debug">Checking...</span></p>';
    echo '<p><strong>AJAX URL:</strong> ' . admin_url('admin-ajax.php') . '</p>';
    echo '<button onclick="testAjax()">Test AJAX Call</button>';
    echo '<button onclick="addTestSessionToCookie()">Add Test Session to Cookie</button>';
    echo '<button onclick="clearCookie()">Clear Cookie</button>';
    echo '<button onclick="checkScripts()">Check Script Loading</button>';
    echo '<div id="ajax-result" style="margin-top: 10px; background: white; padding: 10px; border: 1px solid #ccc;"></div>';
    
    echo '<script>';
    echo 'document.addEventListener("DOMContentLoaded", function() {';
    echo '  updateCookieDisplay();';
    echo '});';
    
    echo 'function updateCookieDisplay() {';
    echo '  var cookie = document.cookie.split(";").find(c => c.trim().startsWith("AddToDiary="));';
    echo '  document.getElementById("cookie-debug").textContent = cookie || "No AddToDiary cookie found";';
    echo '}';
    
    echo 'function addTestSessionToCookie() {';
    echo '  if (!window.setCookie) {';
    echo '    document.getElementById("ajax-result").innerHTML = "<p style=\"color: red;\">MyDiary scripts not loaded!</p>";';
    echo '    return;';
    echo '  }';
    if (!empty($seminars)) {
        echo '  var testId = "' . $seminars[0]->ID . '";';
    } else {
        echo '  var testId = "999";';
    }
    echo '  var existing = window.getCookie("AddToDiary");';
    echo '  var sessions = existing ? JSON.parse(existing) : [];';
    echo '  if (!sessions.includes(testId)) sessions.push(testId);';
    echo '  window.setCookie("AddToDiary", JSON.stringify(sessions), 30);';
    echo '  updateCookieDisplay();';
    echo '  document.getElementById("ajax-result").innerHTML = "<p style=\"color: green;\">Added session " + testId + " to cookie</p>";';
    echo '}';
    
    echo 'function clearCookie() {';
    echo '  document.cookie = "AddToDiary=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";';
    echo '  updateCookieDisplay();';
    echo '  document.getElementById("ajax-result").innerHTML = "<p>Cookie cleared</p>";';
    echo '}';
    
    echo 'function checkScripts() {';
    echo '  var results = [];';
    echo '  results.push("MyDiary setCookie: " + (typeof window.setCookie !== "undefined" ? "✓ Loaded" : "✗ Missing"));';
    echo '  results.push("MyDiary getCookie: " + (typeof window.getCookie !== "undefined" ? "✓ Loaded" : "✗ Missing"));';
    echo '  results.push("populateMyDiary: " + (typeof window.populateMyDiary !== "undefined" ? "✓ Loaded" : "✗ Missing"));';
    echo '  results.push("refreshMyDiary: " + (typeof window.refreshMyDiary !== "undefined" ? "✓ Loaded" : "✗ Missing"));';
    echo '  results.push("AJAX URL: " + (typeof ajaxurl !== "undefined" ? ajaxurl : "✗ Missing"));';
    echo '  document.getElementById("ajax-result").innerHTML = "<pre>" + results.join("\\n") + "</pre>";';
    echo '}';
    
    echo 'function testAjax() {';
    echo '  var cookie = window.getCookie ? window.getCookie("AddToDiary") : null;';
    echo '  var testIds = cookie ? JSON.parse(cookie) : ["' . (!empty($seminars) ? $seminars[0]->ID : '999') . '"];';
    echo '  var data = new FormData();';
    echo '  data.append("action", "get_diary_sessions");';
    echo '  data.append("session_ids", JSON.stringify(testIds));';
    echo '  ';
    echo '  document.getElementById("ajax-result").innerHTML = "<p>Testing AJAX with IDs: " + JSON.stringify(testIds) + "</p>";';
    echo '  ';
    echo '  fetch("' . admin_url('admin-ajax.php') . '", {';
    echo '    method: "POST",';
    echo '    body: data';
    echo '  })';
    echo '  .then(response => response.json())';
    echo '  .then(result => {';
    echo '    document.getElementById("ajax-result").innerHTML = "<h4>AJAX Response:</h4><pre>" + JSON.stringify(result, null, 2) + "</pre>";';
    echo '  })';
    echo '  .catch(error => {';
    echo '    document.getElementById("ajax-result").innerHTML = "<p style=\"color: red;\">AJAX Error: " + error + "</p>";';
    echo '  });';
    echo '}';
    echo '</script>';
    
    echo '</div>';
    
    return ob_get_clean();
}

add_shortcode('debug-my-diary', 'debug_my_diary_shortcode');

// AJAX handler for fetching diary sessions
add_action('wp_ajax_get_diary_sessions', 'handle_get_diary_sessions');
add_action('wp_ajax_nopriv_get_diary_sessions', 'handle_get_diary_sessions');

function handle_get_diary_sessions() {
    // Log for debugging
    error_log('handle_get_diary_sessions called');
    error_log('POST data: ' . print_r($_POST, true));
    
    // Verify nonce if you want extra security
    // if (!wp_verify_nonce($_POST['nonce'], 'diary_nonce')) {
    //     wp_die('Security check failed');
    // }

    if (!isset($_POST['session_ids'])) {
        error_log('No session_ids in POST data');
        wp_send_json_error('No session IDs provided');
        return;
    }

    $session_ids = json_decode(stripslashes($_POST['session_ids']), true);
    
    if (!is_array($session_ids) || empty($session_ids)) {
        error_log('Invalid session_ids: ' . print_r($session_ids, true));
        wp_send_json_error('No valid session IDs provided');
        return;
    }

    error_log('Processing session IDs: ' . implode(', ', $session_ids));
    $sessions = array();
    
    foreach ($session_ids as $session_id) {
        $post = get_post($session_id);
        
        if ($post && $post->post_type === 'seminars' && $post->post_status === 'publish') {
            // Get session metadata - use the correct meta field names
            $session_start = get_post_meta($session_id, 'session-start', true);
            $session_end = get_post_meta($session_id, 'session-end', true);
            $session_time = get_post_meta($session_id, 'session-time', true);
            
            // Also try the meta fields used by the main grid
            if (empty($session_start)) {
                $time_start = get_post_meta($session_id, 'time_start', true);
                $time_end = get_post_meta($session_id, 'time_end', true);
                if ($time_start && $time_end) {
                    $session_start = $time_start;
                    $session_end = $time_end;
                }
            }
            
            // Get track information
            $tracks = get_the_terms($session_id, 'track');
            $track_name = '';
            if ($tracks && !is_wp_error($tracks)) {
                $track_name = $tracks[0]->name;
            }
            
            // Get speakers
            $speakers = get_diary_session_speakers($session_id);
            
            // Format time display
            $time_display = '';
            if ($session_time) {
                $time_display = $session_time;
            } elseif ($session_start && $session_end) {
                $start_time = date('H:i', strtotime($session_start));
                $end_time = date('H:i', strtotime($session_end));
                $time_display = $start_time . ' - ' . $end_time;
            }
            
            // Extract date - try multiple approaches for better date detection
            $date = '';
            $date_title = '';
            
            // Always try to get the date title from taxonomy first
            $dates = get_the_terms($session_id, 'date');
            if ($dates && !is_wp_error($dates)) {
                $date_term = $dates[0];
                $date_title = $date_term->name; // Store the actual day title from taxonomy
                
                // Try to get date from taxonomy slug or name
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_term->slug)) {
                    $date = $date_term->slug;
                } else {
                    // Try to parse the term name as a date
                    $parsed_date = strtotime($date_term->name);
                    if ($parsed_date) {
                        $date = date('Y-m-d', $parsed_date);
                    } else {
                        $date = $date_term->slug;
                    }
                }
            }
            
            // If we have session_start but no date from taxonomy, use session_start for date
            if (empty($date) && $session_start) {
                $date = date('Y-m-d', strtotime($session_start));
            }
            
            // If still no date, try to extract from other meta fields
            if (empty($date)) {
                $custom_date = get_post_meta($session_id, 'event_date', true);
                if ($custom_date) {
                    $parsed_date = strtotime($custom_date);
                    if ($parsed_date) {
                        $date = date('Y-m-d', $parsed_date);
                    }
                }
            }
            
            // Fallback to post date if no specific date found
            if (empty($date)) {
                $date = date('Y-m-d', strtotime($post->post_date));
            }
            
            $sessions[] = array(
                'id' => $session_id,
                'title' => $post->post_title,
                'content' => apply_filters('the_content', $post->post_content), // Keep full formatted content
                'content_plain' => wp_strip_all_tags($post->post_content), // Also provide plain text version
                'time' => $time_display,
                'date' => $date,
                'date_title' => $date_title, // Add the day title from taxonomy
                'track' => $track_name,
                'speakers' => $speakers,
                'permalink' => get_permalink($session_id),
                'session_start' => $session_start,
                'session_end' => $session_end
            );
        } else {
            error_log("Session $session_id not found or not published");
        }
    }
    
    error_log('Returning ' . count($sessions) . ' sessions: ' . print_r($sessions, true));
    wp_send_json_success($sessions);
}

function get_diary_session_speakers($session_id) {
    // Try to get speakers via P2P relationship (if using Posts 2 Posts plugin)
    if (function_exists('p2p_get_connected')) {
        $connected_speakers = p2p_get_connected('seminars_to_speakers', $session_id);
        if ($connected_speakers->have_posts()) {
            $speaker_names = array();
            while ($connected_speakers->have_posts()) {
                $connected_speakers->the_post();
                $speaker_names[] = get_the_title();
            }
            wp_reset_postdata();
            return implode(', ', $speaker_names);
        }
    }
    
    // Fallback: try to get from meta field or content
    $speaker_meta = get_post_meta($session_id, 'speakers', true);
    if ($speaker_meta) {
        return $speaker_meta;
    }
    
    // Try to extract from session presenter meta
    $presenter_meta = get_post_meta($session_id, 'session-presenter', true);
    if ($presenter_meta) {
        return $presenter_meta;
    }
    
    return '';
}

// Enqueue the CSS and JS files
function mira_agenda_grid_old_enqueue_assets() {
  // Define the DEVMODE constant
  if ( ! defined( 'DEVMODE' ) ) {
      define( 'DEVMODE', true );
  }

  // Only enqueue assets if DEVMODE is true
  // Enqueue the CSS file
  
  if (DEVMODE) {
    $css_url = 'assets/css/solar-agenda-grid.css?'.time();
  }
  else {
    $css_url = 'assets/css/solar-agenda-grid.css?'.VERSION;
  }
  
  wp_enqueue_style(
      'solar-agenda-grid-style', // Handle for the CSS file
      plugins_url( $css_url, __FILE__ ), // Path to the CSS file
      array(), // Dependencies (none)
      DEVMODE ? time() : '2.0', // Use time() as the version for cache-busting if DEVMODE is true
      'all' // Media type
  );

  // Enqueue the JS file
    wp_enqueue_script(
      'solar-agenda-grid',
      plugin_dir_url(__FILE__) . 'assets/js/solar-agenda-grid.js',
      array(),
      null,
      true
  );

  // Enqueue MyDiary CSS
  wp_enqueue_style(
      'mira-mydiary-style',
      plugins_url('assets/css/mydiary.css', __FILE__),
      array(),
      DEVMODE ? time() : VERSION,
      'all'
  );

  // Enqueue MyDiary JS
  wp_enqueue_script(
      'mira-mydiary-script',
      plugin_dir_url(__FILE__) . 'assets/js/mydiary.js',
      array(),
      DEVMODE ? time() : VERSION,
      true
  );

  // Enqueue unload policy fix (loads early to fix WP Bakery beforeunload issues)
  wp_enqueue_script(
      'mira-unload-policy-fix',
      plugin_dir_url(__FILE__) . 'assets/js/fix-unload-policy.js',
      array(),
      DEVMODE ? time() : VERSION,
      false // Load in head to ensure it runs before other scripts
  );
}
add_action( 'wp_enqueue_scripts', 'mira_agenda_grid_old_enqueue_assets', 5 ); // Higher priority (earlier execution)

// Enqueue unload policy fix for admin/WP Bakery backend
function mira_agenda_admin_enqueue_assets() {
    // Only load on admin pages and WP Bakery editor
    if (is_admin() || (isset($_GET['vc_editable']) && $_GET['vc_editable'])) {
        wp_enqueue_script(
            'mira-unload-policy-fix-admin',
            plugin_dir_url(__FILE__) . 'assets/js/fix-unload-policy.js',
            array(),
            DEVMODE ? time() : VERSION,
            false // Load in head
        );
    }
}
add_action( 'admin_enqueue_scripts', 'mira_agenda_admin_enqueue_assets', 5 );
add_action( 'wp_enqueue_scripts', 'mira_agenda_admin_enqueue_assets', 1 ); // Very early priority

// Add Permissions Policy header to allow unload events (for WP Bakery compatibility)
function mira_agenda_set_permissions_policy() {
    // Only apply on admin pages or when WP Bakery editor is active
    if (is_admin() || (isset($_GET['vc_editable']) && $_GET['vc_editable']) || 
        (isset($_GET['vc_action']) && $_GET['vc_action']) ||
        (function_exists('vc_mode') && vc_mode())) {
        
        // Note: beforeunload is not a valid Permissions Policy feature
        // The browser restriction is handled by our JavaScript fix instead
        // header('Permissions-Policy: unload=*');
        
        // Instead, we'll use a more targeted approach via JavaScript
        echo "<script>console.log('Mira Unload Fix: Script enqueued for WP Bakery context');</script>\n";
    }
}
add_action( 'init', 'mira_agenda_set_permissions_policy', 1 );

// Console logging for debugging the unload policy fix
function mira_agenda_add_console_debug() {
    if (DEVMODE && (is_admin() || (isset($_GET['vc_editable']) && $_GET['vc_editable']))) {
        echo "<script>\n";
        echo "console.log('Mira Day Agenda: Unload policy fix is active');\n";
        if (is_admin()) {
            echo "console.log('Mira: WP Bakery backend detected - applying unload fix');\n";
        }
        echo "</script>\n";
    }
}
add_action( 'wp_head', 'mira_agenda_add_console_debug' );
add_action( 'admin_head', 'mira_agenda_add_console_debug' );

