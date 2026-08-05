<?php
/**
 * Plugin Name: Mira Day Agenda
 * Description: A plugin that provides a [agenda-grid] shortcode to display HTML content.
 *               Params - day = date slug from seminars > dates. defaults to 2025-10-01
 *               Displays a multi-track display for the entire day.
                
 * Version: 1.37

 * Author: Miramedia / Dominic Johnson
 *
 * Version 1.37 - 2026-08-05 - Configurable My Itinerary button colours (default + in-itinerary) in MyAgenda settings
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
        
 * Version 1.29. 2025-08-07 - Fixed My Diary AJAX functionality and session details display
    1. Resolved AJAX response contamination from script output during requests
    2. Fixed JSON parsing errors that caused "Session 1, Session 2" fallback display
    3. Added safe AJAX URL detection to prevent JavaScript reference errors
    4. Enhanced AJAX handler to accept both 'diary_sessions' and 'session_ids' parameters
    5. Implemented clean output buffer management for all AJAX endpoints
    6. Added comprehensive debugging and error reporting for AJAX functionality
    7. Fixed WP Bakery element registration with proper parameter handling
    8. My Diary now displays real session titles, times, dates, and track information
    
 * Version 1.31 2025-10-29 - Fixed so it works with 2 tabs - 2 schedules   
    
 * Version 1.32 2025-12-30 - Changed the modal code. Create a shortcode called "mira_modal" 
 
 * Version 1.33 2026-01-06 - Fixing issue with media grid and added an option to MyAgenda button

 * Version 1.34 2026-01-12 - DEV VERSION - Merged
 * 
 * Version 1.35. 2026-01-30. Fixed issues with 8 tracks - mainly css.
 *
 * Version 1.36. 2026-01-30. Responsive improvements for multi-column layouts
 *    1. Fixed grid overflow - schedule now stays within screen width
 *    2. Dynamic all-tracks span based on actual track count
 *    3. Fixed undefined $use_myagenda variable warning
 *    4. Reduced padding and font sizes for narrow columns
 *    5. Stack speaker photo above name in session columns
 *    6. Hide blank speaker photo placeholder in stacked layout
 *    7. Stack multiple speakers vertically in narrow columns
 *    8. Smaller event-details headings to match title size
 *    9. Handle all-tracks-only shortcode with no individual tracks

 */

 define('DEVMODE', true); // Set to false on production
 define('VERSION', "1.37"); // Updated version - matches plugin header
 
 // Add at the very top of your plugin file, right after the opening <?php tag
 add_action('admin_enqueue_scripts', function() {
     global $pagenow;
     if ($pagenow === 'upload.php') {
         wp_dequeue_script('mira-unload-policy-fix');
         wp_dequeue_script('mira-unload-policy-fix-admin');
     }
 }, 999);
 
 
 // Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Error logging for debugging
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Mira Day Agenda: Starting plugin load');
}

// Include the display & data functions file
$plugin_files = array(
    'assets/lib/cpt.php',
    'assets/lib/admin.php',
    'assets/lib/wp_bakery_admin_simple.php',
    'assets/lib/display_functions.php',
    'assets/lib/data_functions.php',
    'assets/lib/sponsors_seminars_relationship.php'
);

foreach ($plugin_files as $file) {
    $file_path = plugin_dir_path( __FILE__ ) . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Mira Day Agenda: Loaded ' . $file);
        }
    } else {
        error_log('Mira Day Agenda: MISSING FILE - ' . $file_path);
        wp_die('Mira Day Agenda: Required file missing - ' . $file);
    }
}

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

// Admin menu and settings are handled in assets/lib/admin.php

// Helper function to get the character limit setting
function mira_agenda_get_char_limit() {
    $options = get_option('mira_agenda_settings');
    return isset($options['more_button_char_limit']) ? (int)$options['more_button_char_limit'] : 200;
}

// Helper function to check if My Diary is enabled
function mira_agenda_is_my_diary_enabled() {
    $options = get_option('mira_agenda_settings');
    return isset($options['enable_my_diary']) ? ($options['enable_my_diary'] === 'yes') : true; // Default to enabled
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


function display_grid ($sessions,$inputs,$headings, $track_background_colour = array(), $sponsored_sessions = array() ) {
  
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
    
        echo display_one_session($sessions, $rowID, $inputs, $headings, $display_headings, $sponsored_sessions, $track_background_colour );
    
        // Store last track ID for next iteration
        $lastTrackID = $currentTrackID;
    }
  }
  else {
    // do not display the headings
    foreach ($sessions as $rowID => $session) {

        //print_r($session);

        echo display_one_session($sessions, $rowID, $inputs, $headings,false, $sponsored_sessions, $track_background_colour);
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
        'border' => 'no',
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
    // Helper to convert various truthy/falsy strings to boolean
    if (!function_exists('mira_agenda_bool')) {
        function mira_agenda_bool($val) {
            if (is_bool($val)) return $val;
            $val = strtolower(trim($val));
            if (in_array($val, ['true', '1', 'yes'])) return true;
            if (in_array($val, ['false', '0', 'no'])) return false;
            return false;
        }
    }

    if (isset($atts['display_heading_bar'])) {
        $atts['display_heading_bar'] = mira_agenda_bool($atts['display_heading_bar']);
    }
    if (isset($atts['show_end_time'])) {
        $atts['show_end_time'] = mira_agenda_bool($atts['show_end_time']);
    }
    if (isset($atts['time_slot_side'])) {
        $atts['time_slot_side'] = mira_agenda_bool($atts['time_slot_side']);
    }
    if (isset($atts['display_seminar_type'])) {
        $atts['display_seminar_type'] = mira_agenda_bool($atts['display_seminar_type']);
        error_log('SHORTCODE DEBUG: display_seminar_type converted to: ' . ($atts['display_seminar_type'] ? 'true' : 'false'));
    }
    if (isset($atts['display_seminar_duration'])) {
        $atts['display_seminar_duration'] = mira_agenda_bool($atts['display_seminar_duration']);
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

    $sponsored_sessions = get_sponsored_sessions($args);

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

    $day = $inputs['day'];
    echo '<div class="schedule_'.$day.'" aria-labelledby="schedule-heading">';
        
    echo print_times($time_slots);

    echo display_grid($sessions,$inputs,$headings, $track_background_colour, $sponsored_sessions); 
        
    echo '</div>';

    return ob_get_clean();
}

// Register the shortcode outside the function
add_shortcode( 'agenda-grid', 'mira_agenda_grid_old_shortcode' );

// Register the Display My Diary shortcode
function display_my_diary_shortcode($atts) {
    // Check if My Diary is enabled
    if (!mira_agenda_is_my_diary_enabled()) {
        return '<div class="my-diary-disabled"><p>My Diary functionality is currently disabled.</p></div>';
    }

    // Start output buffering
    ob_start();

    // Use admin setting as default for style (can still be overridden by shortcode attribute)
    $admin_options   = get_option( 'mira_agenda_settings', array() );
    $admin_style     = isset( $admin_options['diary_display_style'] ) ? $admin_options['diary_display_style'] : 'grid';

    // Set default values for parameters
    $atts = shortcode_atts(array(
        'style'              => $admin_style,
        'show_empty_message' => 'yes',
        'empty_message'      => '',
        'show_details'       => 'yes',
        'show_remove_buttons'=> 'yes',
    ), $atts, 'display-my-diary');

    // Convert string values to booleans
    $show_empty_message = ($atts['show_empty_message'] === 'yes');
    $show_details = ($atts['show_details'] !== 'no'); // Show details unless explicitly set to 'no'
    $show_remove_buttons = ($atts['show_remove_buttons'] === 'yes');
    $style = esc_attr($atts['style']);
    $empty_message = !empty($atts['empty_message']) ? esc_html($atts['empty_message']) : 'Your diary is empty. Add sessions from the agenda to see them here.';

    // Read ExhibitorItinerary cookie server-side
    $exhibitor_ids = array();
    if ( ! empty( $_COOKIE['ExhibitorItinerary'] ) ) {
        $raw     = sanitize_text_field( wp_unslash( $_COOKIE['ExhibitorItinerary'] ) );
        $decoded = json_decode( $raw, true );
        if ( is_array( $decoded ) ) {
            $exhibitor_ids = array_values( array_filter( array_map( 'absint', $decoded ) ) );
        }
    }

    $exhibitors = array();
    if ( ! empty( $exhibitor_ids ) ) {
        $exhibitors = get_posts( array(
            'post_type'      => 'exhibitors',
            'post__in'       => $exhibitor_ids,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ) );
    }

    $has_sessions   = ! empty( $_COOKIE['AddToDiary'] ) && $_COOKIE['AddToDiary'] !== '[]';
    $has_exhibitors = ! empty( $exhibitors );

    // Generate the diary display HTML
    echo '<div class="my-diary-container my-diary-' . $style . '">';

    // Header
    echo '<div class="my-diary-header">';
    echo '<h3 class="my-diary-title">My Personal Agenda</h3>';
    if ( $has_sessions ) {
        echo '<button class="my-diary-refresh-btn" title="Refresh sessions" onclick="refreshMyDiary();">';
        echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
        echo '<path d="M23 4v6h-6M1 20v-6h6M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.64A9 9 0 0 1 3.51 15"/>';
        echo '</svg>';
        echo '</button>';
    }
    echo '</div>';

    // Empty state
    if ( ! $has_sessions && ! $has_exhibitors ) {
        if ( $show_empty_message ) {
            echo '<div class="my-diary-empty"><p class="empty-message">' . esc_html( $empty_message ) . '</p></div>';
        }
        echo '</div>'; // close .my-diary-container
    } else {

        // Sessions section (JS-populated via AJAX)
        if ( $has_sessions ) {
            echo '<div id="my-diary-sessions">';
            if ( $has_exhibitors ) {
                echo '<h3 class="diary-section-heading">My Sessions</h3>';
            }
            if ( $show_empty_message ) {
                echo '<div class="my-diary-empty" style="display:none;"><p class="empty-message">' . esc_html( $empty_message ) . '</p></div>';
            }
            echo '<div class="my-diary-content" style="display:none;"></div>';
            echo '</div>';
        }

        // Exhibitors section (server-side rendered)
        if ( $has_exhibitors ) {
            echo '<div class="my-diary-exhibitors">';
            if ( $has_sessions ) {
                echo '<h3 class="diary-section-heading">My Exhibitors</h3>';
            }
            echo '<div class="diary-day-sessions">';
            foreach ( $exhibitors as $exhibitor ) {
                $permalink = get_permalink( $exhibitor->ID );
                $stand     = get_post_meta( $exhibitor->ID, 'stand_number', true );
                $website   = get_post_meta( $exhibitor->ID, 'website_url', true );
                $logo      = has_post_thumbnail( $exhibitor->ID )
                    ? ( get_the_post_thumbnail_url( $exhibitor->ID, 'sponsor_detail_photo' ) ?: get_the_post_thumbnail_url( $exhibitor->ID, 'medium' ) )
                    : '';

                echo '<div class="diary-session diary-exhibitor" data-exhibitor-id="' . esc_attr( $exhibitor->ID ) . '">';
                echo '<div class="diary-session-header">';
                echo '<div><span class="diary-session-track">Exhibitor</span></div>';
                if ( $show_remove_buttons ) {
                    echo '<button class="diary-remove-exhibitor-btn" data-exhibitor-id="' . esc_attr( $exhibitor->ID ) . '">Remove</button>';
                }
                echo '</div>';
                if ( $logo ) {
                    echo '<div class="diary-exhibitor-logo"><img src="' . esc_url( $logo ) . '" alt="' . esc_attr( $exhibitor->post_title ) . '"></div>';
                }
                echo '<div class="diary-session-title"><a href="' . esc_url( $permalink ) . '">' . esc_html( $exhibitor->post_title ) . '</a></div>';
                if ( $stand )   { echo '<div class="diary-session-details">Stand: ' . esc_html( $stand ) . '</div>'; }
                if ( $website ) { echo '<div class="diary-session-details"><a href="' . esc_url( $website ) . '" target="_blank" rel="noopener">' . esc_html( $website ) . '</a></div>'; }
                echo '</div>';
            }
            echo '</div>';
            echo '</div>';
        }

        echo '</div>'; // close .my-diary-container
    }

    // Add JavaScript to populate sessions and handle exhibitor remove buttons
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
  if (typeof populateMyDiary === "function") { populateMyDiary(myDiaryConfig); }
}

// Exhibitor remove button handler
function miraRemoveExhibitor(id) {
  var raw = document.cookie.split(";").map(function(c){ return c.trim(); })
              .find(function(c){ return c.startsWith("ExhibitorItinerary="); });
  if (!raw) return;
  try {
    var ids = JSON.parse(decodeURIComponent(raw.split("=")[1])).filter(function(i){ return String(i) !== String(id); });
    var exp = new Date(Date.now() + 30 * 864e5).toUTCString();
    document.cookie = "ExhibitorItinerary=" + encodeURIComponent(JSON.stringify(ids)) + "; expires=" + exp + "; path=/";
    window.location.reload();
  } catch(e) {}
}

document.addEventListener("DOMContentLoaded", function() {
  if (typeof populateMyDiary === "function") { populateMyDiary(myDiaryConfig); }

  // Wire exhibitor remove buttons
  document.querySelectorAll(".diary-remove-exhibitor-btn").forEach(function(btn) {
    btn.addEventListener("click", function() {
      miraRemoveExhibitor(this.getAttribute("data-exhibitor-id"));
    });
  });

  document.addEventListener("visibilitychange", function() {
    if (!document.hidden && typeof populateMyDiary === "function") {
      setTimeout(function(){ populateMyDiary(myDiaryConfig); }, 100);
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

    // Debug diary status shortcode
    function debug_diary_status_shortcode($atts) {
        // Check if My Diary is enabled
        if (!mira_agenda_is_my_diary_enabled()) {
            return '<div class="debug-diary-status"><p><strong>My Diary Status:</strong> Disabled in settings</p></div>';
        }

        ob_start();
        echo '<div class="debug-diary-status" style="background: #f0f8ff; border: 1px solid #0073aa; padding: 15px; margin: 10px 0; font-family: monospace;">';
        echo '<h4 style="margin-top: 0;">My Diary Debug Status</h4>';
        echo '<p><strong>My Diary Feature:</strong> ✅ Enabled</p>';
        echo '<p><strong>AJAX URL:</strong> ' . admin_url('admin-ajax.php') . '</p>';
        echo '<p><strong>Current Time:</strong> ' . current_time('Y-m-d H:i:s') . '</p>';
        
        // Check if posts exist
        $seminars = get_posts(array(
            'post_type' => 'seminars',
            'posts_per_page' => 3,
            'post_status' => 'publish'
        ));
        echo '<p><strong>Available Seminars:</strong> ' . count($seminars) . ' found</p>';
        
        if (count($seminars) > 0) {
            echo '<p><strong>Sample Session IDs:</strong> ';
            $sample_ids = array();
            foreach (array_slice($seminars, 0, 3) as $seminar) {
                $sample_ids[] = $seminar->ID;
            }
            echo implode(', ', $sample_ids) . '</p>';
        }
        
        echo '<script>';
        echo 'console.log("Debug Diary Status - AJAX URL available:", typeof ajaxurl !== "undefined" ? ajaxurl : "NOT AVAILABLE");';
        echo 'console.log("Debug Diary Status - Current diary cookie:", document.cookie.split(";").find(c => c.trim().startsWith("AddToDiary=")));';
        
        // Define ajaxurl globally for the test functions
        echo 'const ajaxurl = "' . admin_url('admin-ajax.php') . '";';
        
        // Add a test button to manually test AJAX
        echo 'function testDiaryAjax() {';
        echo '    console.log("=== AJAX TEST START ===");';
        echo '    console.log("Testing AJAX manually...");';
        echo '    const testIds = [' . implode(',', $sample_ids) . '];';
        echo '    console.log("Test IDs:", testIds);';
        echo '    console.log("Using AJAX URL:", ajaxurl);';
        echo '    ';
        echo '    document.getElementById("ajax-test-result").innerHTML = "Testing AJAX... please wait";';
        echo '    ';
        echo '    const data = new FormData();';
        echo '    data.append("action", "get_diary_sessions");';
        echo '    data.append("session_ids", JSON.stringify(testIds));';
        echo '    console.log("FormData created with action:", data.get("action"));';
        echo '    console.log("FormData created with session_ids:", data.get("session_ids"));';
        echo '    ';
        echo '    console.log("Starting fetch request...");';
        echo '    fetch(ajaxurl, { ';
        echo '        method: "POST", ';
        echo '        body: data,';
        echo '        credentials: "same-origin"';
        echo '    })';
        echo '    .then(response => {';
        echo '        console.log("Fetch response received:", response.status, response.statusText);';
        echo '        console.log("Response headers:", response.headers);';
        echo '        if (!response.ok) {';
        echo '            throw new Error(`HTTP error! status: ${response.status}`);';
        echo '        }';
        echo '        return response.text();';
        echo '    })';
        echo '    .then(text => {';
        echo '        console.log("Raw response text:", text);';
        echo '        try {';
        echo '            const result = JSON.parse(text);';
        echo '            console.log("Parsed JSON result:", result);';
        echo '            document.getElementById("ajax-test-result").innerHTML = "<pre>" + JSON.stringify(result, null, 2) + "</pre>";';
        echo '        } catch (e) {';
        echo '            console.error("JSON parse error:", e);';
        echo '            document.getElementById("ajax-test-result").innerHTML = "Response is not JSON:<br><pre>" + text + "</pre>";';
        echo '        }';
        echo '    })';
        echo '    .catch(error => {';
        echo '        console.error("AJAX Test Error:", error);';
        echo '        document.getElementById("ajax-test-result").innerHTML = "Error: " + error.message;';
        echo '    });';
        echo '    console.log("=== AJAX TEST REQUEST SENT ===");';
        echo '}';
        echo '</script>';
        
        echo '<button onclick="testDiaryAjax()" style="background: #0073aa; color: white; padding: 10px; border: none; margin: 10px 0;">Test AJAX Call</button>';
        echo '<div id="ajax-test-result" style="background: #f9f9f9; padding: 10px; margin: 10px 0; white-space: pre-wrap;"></div>';
        
        // Add button to create test diary data
        if (count($seminars) > 0) {
            echo '<button onclick="createTestDiary()" style="background: #00a32a; color: white; padding: 10px; border: none; margin: 10px 0;">Add Sample Sessions to Diary</button>';
            echo '<script>';
            echo 'function createTestDiary() {';
            echo '    const testIds = [' . implode(',', $sample_ids) . '];';
            echo '    document.cookie = "AddToDiary=" + JSON.stringify(testIds) + "; path=/";';
            echo '    console.log("Created test diary with IDs:", testIds);';
            echo '    alert("Test diary created! Now try viewing your My Diary display.");';
            echo '}';
            echo '</script>';
        }
        
        echo '</div>';
        
        return ob_get_clean();
    }

    add_shortcode('debug-diary-status', 'debug_diary_status_shortcode');// Debug shortcode to test diary functionality
function debug_my_diary_shortcode($atts) {
    // Check if My Diary is enabled
    if (!mira_agenda_is_my_diary_enabled()) {
        return '<div class="my-diary-debug-disabled"><p><strong>My Diary Debug:</strong> My Diary functionality is currently disabled in settings.</p></div>';
    }

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

// AJAX handler for fetching exhibitor itinerary items
add_action( 'wp_ajax_get_itinerary_exhibitors',        'handle_get_itinerary_exhibitors' );
add_action( 'wp_ajax_nopriv_get_itinerary_exhibitors', 'handle_get_itinerary_exhibitors' );

function handle_get_itinerary_exhibitors() {
    while ( ob_get_level() ) ob_end_clean();

    if ( empty( $_POST['exhibitor_ids'] ) ) {
        wp_send_json_error( 'No exhibitor IDs provided' );
    }

    $ids = json_decode( stripslashes( $_POST['exhibitor_ids'] ), true );
    if ( ! is_array( $ids ) || empty( $ids ) ) {
        wp_send_json_error( 'Invalid exhibitor IDs' );
    }

    $exhibitors = array();
    foreach ( $ids as $id ) {
        $id   = absint( $id );
        $post = get_post( $id );
        if ( ! $post || $post->post_type !== 'exhibitors' || $post->post_status !== 'publish' ) {
            continue;
        }
        $logo = '';
        if ( has_post_thumbnail( $id ) ) {
            $logo = get_the_post_thumbnail_url( $id, 'sponsor_detail_photo' ) ?: get_the_post_thumbnail_url( $id, 'medium' );
        }
        $exhibitors[] = array(
            'id'        => $id,
            'title'     => $post->post_title,
            'permalink' => get_permalink( $id ),
            'website'   => get_post_meta( $id, 'website_url', true ),
            'stand'     => get_post_meta( $id, 'stand_number', true ),
            'logo'      => $logo,
        );
    }

    wp_send_json_success( $exhibitors );
}

// AJAX handler for fetching diary sessions
add_action('wp_ajax_get_diary_sessions', 'handle_get_diary_sessions');
add_action('wp_ajax_nopriv_get_diary_sessions', 'handle_get_diary_sessions');

function handle_get_diary_sessions() {
    // Clean output buffer to ensure clean JSON response
    while (ob_get_level()) {
        ob_end_clean();
    }
    ob_start();
    
    // Force enable error logging for this function
    ini_set('log_errors', 1);
    ini_set('error_log', WP_CONTENT_DIR . '/debug.log');
    
    // Log for debugging
    error_log('=== DIARY AJAX HANDLER CALLED ===');
    error_log('Request method: ' . $_SERVER['REQUEST_METHOD']);
    error_log('POST data: ' . print_r($_POST, true));
    error_log('Raw input: ' . file_get_contents('php://input'));
    
    // Verify nonce if you want extra security
    // if (!wp_verify_nonce($_POST['nonce'], 'diary_nonce')) {
    //     wp_die('Security check failed');
    // }

    if (!isset($_POST['diary_sessions']) && !isset($_POST['session_ids'])) {
        error_log('No diary_sessions or session_ids in POST data');
        ob_clean(); // Clean output before JSON response
        wp_send_json_error('No session IDs provided');
        return;
    }

    // Accept both parameter names for compatibility
    $session_data = isset($_POST['diary_sessions']) ? $_POST['diary_sessions'] : $_POST['session_ids'];
    $session_ids = json_decode(stripslashes($session_data), true);
    
    if (!is_array($session_ids) || empty($session_ids)) {
        error_log('Invalid session_ids: ' . print_r($session_ids, true));
        ob_clean(); // Clean output before JSON response
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
    
    // Clean any output that might have been generated
    ob_clean();
    
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
  
  // Enqueue the CSS file
  if (DEVMODE) {
      $css_url = 'assets/css/mira-day-agenda.css?'.time();
  }
  else {
      $css_url = 'assets/css/mira-day-agenda.css?'.VERSION;
  }
  
  wp_enqueue_style(
      'mira-day-agenda-style',
      plugins_url( $css_url, __FILE__ ),
      array(),
      DEVMODE ? time() : '2.0',
      'all'
  );
  
  // Enqueue the JS file
  wp_enqueue_script(
      'mira-day-agenda',
      plugin_dir_url(__FILE__) . 'assets/js/mira-day-agenda.js',
      array(),
      null,
      true
  );
  
  // Conditionally enqueue MyDiary assets only if enabled
  if (mira_agenda_is_my_diary_enabled()) {
    wp_enqueue_style(
        'mira-mydiary-style',
        plugins_url('assets/css/mydiary.css', __FILE__),
        array(),
        DEVMODE ? time() : VERSION,
        'all'
    );
   
    wp_enqueue_script(
        'mira-mydiary-script',
        plugin_dir_url(__FILE__) . 'assets/js/mydiary.js',
        array(),
        DEVMODE ? time() : VERSION,
        true
    );
    
    wp_enqueue_style(
        'mira-modal-style',
        plugins_url('assets/css/mira-modal.css', __FILE__),
        array(),
        DEVMODE ? time() : VERSION,
        'all'
    );
    
    wp_enqueue_script(
        'mira-modal-script',
        plugin_dir_url(__FILE__) . 'assets/js/mira-modal.js',
        array(),
        DEVMODE ? time() : VERSION,
        true
    );
    
    wp_localize_script('mira-mydiary-script', 'mira_diary_ajax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('diary_nonce')
    ));
    
    wp_add_inline_script('mira-mydiary-script', 'console.log("MyDiary JS loaded successfully"); console.log("AJAX URL:", mira_diary_ajax.ajaxurl);');
  }
  
  // Exhibitor list itinerary button JS (matches the display_my_agenda() filter condition)
  if ( (bool) apply_filters( 'mira_show_on_exhibitor_list', false ) ) {
      wp_enqueue_script(
          'mira-myagenda-exhibitor',
          plugin_dir_url( __FILE__ ) . 'assets/js/myagenda-exhibitor.js',
          array(),
          DEVMODE ? time() : VERSION,
          true
      );

      // Custom button colours from MyAgenda settings (overrides mydiary.css defaults).
      $agenda_options        = get_option( 'mira_agenda_settings', array() );
      $myagenda_color        = ! empty( $agenda_options['myagenda_btn_color'] )        ? $agenda_options['myagenda_btn_color']        : '#e67e22';
      $myagenda_color_active = ! empty( $agenda_options['myagenda_btn_color_active'] ) ? $agenda_options['myagenda_btn_color_active'] : '#999999';

      wp_register_style( 'mira-myagenda-exhibitor-style', false );
      wp_enqueue_style( 'mira-myagenda-exhibitor-style' );
      wp_add_inline_style( 'mira-myagenda-exhibitor-style',
          'span.myagenda { background: ' . esc_html( $myagenda_color ) . '; }' .
          'span.myagenda.in-itinerary { background: ' . esc_html( $myagenda_color_active ) . '; }'
      );
  }

  // Enqueue unload policy fix for frontend only
  wp_enqueue_script(
      'mira-unload-policy-fix',
      plugin_dir_url(__FILE__) . 'assets/js/fix-unload-policy.js',
      array(),
      DEVMODE ? time() : VERSION,
      false
  );
}
add_action( 'wp_enqueue_scripts', 'mira_agenda_grid_old_enqueue_assets', 5 );

// Enqueue unload policy fix ONLY for WP Bakery backend editor
function mira_agenda_admin_enqueue_assets() {
    global $pagenow;
    
    // Exclude media library pages
    if ($pagenow === 'upload.php' || $pagenow === 'media-upload.php') {
        return;
    }
    
    // Only load on WP Bakery editor pages
    if (isset($_GET['vc_editable']) && $_GET['vc_editable']) {
        wp_enqueue_script(
            'mira-unload-policy-fix-admin',
            plugin_dir_url(__FILE__) . 'assets/js/fix-unload-policy.js',
            array(),
            DEVMODE ? time() : VERSION,
            false
        );
    }
}
add_action( 'admin_enqueue_scripts', 'mira_agenda_admin_enqueue_assets', 5 );
// REMOVED: The duplicate wp_enqueue_scripts hook

// Add Permissions Policy header to allow unload events (for WP Bakery compatibility)
 function mira_agenda_set_permissions_policy() {
     global $pagenow;
     
     // Skip on media library and other non-WP Bakery pages
     if ($pagenow === 'upload.php' || $pagenow === 'media-upload.php' || $pagenow === 'media-new.php') {
         return;
     }
     
     // Only apply when WP Bakery editor is actually active
     if ((isset($_GET['vc_editable']) && $_GET['vc_editable']) || 
         (isset($_GET['vc_action']) && $_GET['vc_action']) ||
         (function_exists('vc_mode') && vc_mode())) {
         
         // Only output this during normal page loads, not AJAX requests
         if (!wp_doing_ajax()) {
             echo "<script>console.log('Mira Unload Fix: Script enqueued for WP Bakery context');</script>\n";
         }
     }
 }
 add_action( 'init', 'mira_agenda_set_permissions_policy', 1 );

// Console logging for debugging the unload policy fix
/* V 1.33
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

*/

/**
 * Modal Popup Shortcode for Mira Day Agenda
 * Add this to your mira-day-agenda plugin
 */

// Register and enqueue scripts and styles


// Shortcode function
function mira_modal_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => 'modalPopup'
    ), $atts);
    
    ob_start();
    ?>
    <div id="<?php echo esc_attr($atts['id']); ?>" class="mira-modal-overlay">
        <div class="mira-modal-container">
            <span class="mira-modal-close">&times;</span>
            <div class="mira-modal-content">
                <!-- Content will be inserted here via jQuery -->
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('mira_modal', 'mira_modal_shortcode');

