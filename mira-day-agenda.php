<?php
/**
 * Plugin Name: Mira Day Agenda
 * Description: A plugin that provides a [agenda-grid] shortcode to display HTML content.
 *               Params - day = date slug from seminars > dates. defaults to 2025-10-01
 *               Displays a multi-track display for the entire day.
                
 * Version: 1.14.
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
 
 * Version 1.14. 2025-08-06 - Added Display My Diary shortcode and WPBakery element
    1. New [display-my-diary] shortcode to show saved sessions
    2. WPBakery "Display My Diary" element with configuration options
    3. Sessions grouped by day and ordered by time
    4. Multiple display styles: Grid, List, Compact
    5. AJAX-powered session data fetching
    6. Remove buttons to manage diary entries
    7. Empty state messaging and responsive design
     
        
 
 
 */
 
define('DEVMODE', true); // Set to false on production
define('VERSION', "1.14"); // Set to false on production


 // Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include the display & data functions file
require_once plugin_dir_path( __FILE__ ) . 'assets/lib/cpt.php';
require_once plugin_dir_path( __FILE__ ) . 'assets/lib/wp_bakery_admin.php';
require_once plugin_dir_path( __FILE__ ) . 'assets/lib/display_functions.php';
require_once plugin_dir_path( __FILE__ ) . 'assets/lib/data_functions.php';

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
    ), $atts, 'agenda-grid');

    // Debug: Show the incoming attributes (remove this after testing)
    echo '<!-- DEBUG: Shortcode Attributes: ' . print_r($atts, true) . ' -->';
    
    // Debug: Log the incoming attributes
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Agenda Grid Shortcode Attributes: ' . print_r($atts, true));
    }

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
        'show_details' => 'yes',
        'show_remove_buttons' => 'yes',
    ), $atts, 'display-my-diary');

    // Convert string values to booleans
    $show_empty_message = ($atts['show_empty_message'] === 'yes');
    $show_details = ($atts['show_details'] === 'yes');
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
    echo '<script type="text/javascript">';
    echo 'var ajaxurl = "' . admin_url('admin-ajax.php') . '";';
    echo 'var myDiaryConfig = {';
    echo '  style: "' . $style . '",';
    echo '  showDetails: ' . ($show_details ? 'true' : 'false') . ',';
    echo '  showRemoveButtons: ' . ($show_remove_buttons ? 'true' : 'false') . ',';
    echo '  showEmptyMessage: ' . ($show_empty_message ? 'true' : 'false') . '';
    echo '};';
    echo '';
    echo 'function refreshMyDiary() {';
    echo '  console.log("Manual refresh triggered");';
    echo '  if (typeof populateMyDiary === "function") {';
    echo '    populateMyDiary(myDiaryConfig);';
    echo '  }';
    echo '}';
    echo '';
    echo 'document.addEventListener("DOMContentLoaded", function() {';
    echo '  // Initial population';
    echo '  if (typeof populateMyDiary === "function") {';
    echo '    populateMyDiary(myDiaryConfig);';
    echo '  }';
    echo '  ';
    echo '  // Tab/window focus detection for tab-based interfaces';
    echo '  window.addEventListener("focus", function() {';
    echo '    console.log("Window gained focus, refreshing diary");';
    echo '    if (typeof populateMyDiary === "function") {';
    echo '      populateMyDiary(myDiaryConfig);';
    echo '    }';
    echo '  });';
    echo '  ';
    echo '  // Page visibility API for better tab detection';
    echo '  document.addEventListener("visibilitychange", function() {';
    echo '    if (!document.hidden) {';
    echo '      console.log("Tab became visible, refreshing diary");';
    echo '      if (typeof populateMyDiary === "function") {';
    echo '        setTimeout(function() {';
    echo '          populateMyDiary(myDiaryConfig);';
    echo '        }, 100);';
    echo '      }';
    echo '    }';
    echo '  });';
    echo '});';
    echo '</script>';

    return ob_get_clean();
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
    
    // JavaScript to show cookie contents
    echo '<p><strong>Cookie Contents:</strong> <span id="cookie-debug">Checking...</span></p>';
    echo '<button onclick="testAjax()">Test AJAX Call</button>';
    echo '<div id="ajax-result" style="margin-top: 10px;"></div>';
    
    echo '<script>';
    echo 'document.addEventListener("DOMContentLoaded", function() {';
    echo '  var cookie = document.cookie.split(";").find(c => c.trim().startsWith("AddToDiary="));';
    echo '  document.getElementById("cookie-debug").textContent = cookie || "No AddToDiary cookie found";';
    echo '});';
    
    echo 'function testAjax() {';
    echo '  var testIds = ["123", "456"];';
    echo '  var data = new FormData();';
    echo '  data.append("action", "get_diary_sessions");';
    echo '  data.append("session_ids", JSON.stringify(testIds));';
    echo '  ';
    echo '  fetch("' . admin_url('admin-ajax.php') . '", {';
    echo '    method: "POST",';
    echo '    body: data';
    echo '  })';
    echo '  .then(response => response.json())';
    echo '  .then(result => {';
    echo '    document.getElementById("ajax-result").innerHTML = "<pre>" + JSON.stringify(result, null, 2) + "</pre>";';
    echo '  })';
    echo '  .catch(error => {';
    echo '    document.getElementById("ajax-result").innerHTML = "<p style=\"color: red;\">Error: " + error + "</p>";';
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
            // Get session metadata
            $session_start = get_post_meta($session_id, 'session-start', true);
            $session_end = get_post_meta($session_id, 'session-end', true);
            $session_time = get_post_meta($session_id, 'session-time', true);
            
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
            
            // Extract date
            $date = '';
            if ($session_start) {
                $date = date('Y-m-d', strtotime($session_start));
            } else {
                // Try to get date from taxonomy
                $dates = get_the_terms($session_id, 'date');
                if ($dates && !is_wp_error($dates)) {
                    $date = $dates[0]->slug;
                }
            }
            
            $sessions[] = array(
                'id' => $session_id,
                'title' => $post->post_title,
                'content' => wp_strip_all_tags($post->post_content),
                'time' => $time_display,
                'date' => $date,
                'track' => $track_name,
                'speakers' => $speakers,
                'permalink' => get_permalink($session_id)
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
}
add_action( 'wp_enqueue_scripts', 'mira_agenda_grid_old_enqueue_assets' );

