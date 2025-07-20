<?php
/**
 * Plugin Name: Mira Day Agenda
 * Description: A plugin that provides a [agenda-grid] shortcode to display HTML content.
 *               Params - day = date slug from seminars > dates. defaults to 2025-10-01
 *               Displays a multi-track display for the entire day.
                
 * Version: 1.9.
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
     
        
 
 
 */

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


function display_grid ($sessions,$inputs,$headings) {

  $display_heading_bar_page = false;
  if ($display_heading_bar_page) {
    // Display the headings at the top of the page - might need to be a param.
    echo get_schedule_header($headings); // Display headings once
  }
  
  // Look to see if you are to display headings
  if ($inputs['display_heading_bar'] === "yes") {
      $display_headings_param = true;
  }
  
  if ($display_headings_param) {
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
            $display_headings = true;
            $seenTracks[] = $currentTrackID; // Mark track as seen
        } else {
            $display_headings = false; // Prevent re-display for already seen tracks
        }
    
        echo display_one_session($sessions, $rowID, $inputs, $headings, $display_headings);
    
        // Store last track ID for next iteration
        $lastTrackID = $currentTrackID;
    }
  }
  else {
    // do not display the headings
    foreach ($sessions as $rowID => $session) {
        echo display_one_session($sessions, $rowID, $inputs, $headings,false);
    }
  }
}

// Register the shortcode
function mira_agenda_grid_old_shortcode($atts) {
    // Start output buffering
    ob_start();

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

    echo display_grid($sessions,$inputs,$headings); 
        
    echo '</div>';

    return ob_get_clean();
}

// Register the shortcode outside the function
add_shortcode( 'agenda-grid', 'mira_agenda_grid_old_shortcode' );

// Enqueue the CSS and JS files
function mira_agenda_grid_old_enqueue_assets() {
  // Define the DEVMODE constant
  if ( ! defined( 'DEVMODE' ) ) {
      define( 'DEVMODE', true );
  }

  // Only enqueue assets if DEVMODE is true
  // Enqueue the CSS file
  wp_enqueue_style(
      'solar-agenda-grid-style', // Handle for the CSS file
      plugins_url( 'assets/css/solar-agenda-grid.css', __FILE__ ), // Path to the CSS file
      array(), // Dependencies (none)
      DEVMODE ? time() : '1.0', // Use time() as the version for cache-busting if DEVMODE is true
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
}
add_action( 'wp_enqueue_scripts', 'mira_agenda_grid_old_enqueue_assets' );

