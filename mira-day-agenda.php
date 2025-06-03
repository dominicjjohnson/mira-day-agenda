<?php
/**
 * Plugin Name: Mira Day Agenda
 * Description: A plugin that provides a [agenda-grid] shortcode to display HTML content.
 *               Params - day = date slug from seminars > dates. defaults to 2025-10-01
 *               Displays a multi-track display for the entire day.
                
 * Version: 1.1
 * Author: Miramedia / Dominic Johnson
 * 
 * Version 1.1 - 2025-05-30 - Updated for HCE 2025
 * Version 1.0 - 2024-05-30 - Initial release for Evie
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

function print_times($time_slots) {

  $output = "";
  foreach ($time_slots as $time) {
    $value = $time;
    $time = str_replace(':', '', $time);
    $output .= '<h2 class="time-slot" style="grid-row: time-' . esc_attr($time) . ';">' . esc_html($value) . '</h2>';
  }

return $output;
}


function display_grid ($sessions) {

  foreach ($sessions as $rowID => $session) {
    echo display_one_session($sessions, $rowID);
  }

}

// Register the shortcode
function mira_agenda_grid_old_shortcode($atts) {
    // HTML content to display with the shortcode

  $inputs = get_parameters($atts);
  if (!empty($inputs['error'])) {
    echo "<script>console.error(" . json_encode($inputs['error_message']) . ");</script>";
    return $inputs;
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
  $sessions = get_grid_session_data($data,$inputs['trackslugs']);

  $time_slots = get_time_slots($data);


  echo get_css_slots($time_slots,$track_background_colour,$track_text_colour,$inputs); 

  echo '<h2 id="schedule-heading">Conference Schedule</h2>';
  echo '<div class="schedule" aria-labelledby="schedule-heading">';
    
  echo get_schedule_header($headings);

  echo print_times($time_slots);

  echo display_grid($sessions); 
    
  echo '</div>';

  return ob_get_clean();
}
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
      'solar-agenda-grid-script', // Handle for the JS file
      plugins_url( 'assets/js/solar-agenda-grid.js', __FILE__ ), // Path to the JS file
      array('jquery'), // Dependencies (jQuery)
      DEVMODE ? time() : '1.0', // Use time() as the version for cache-busting if DEVMODE is true
      true // Load in the footer
  );
}
add_action( 'wp_enqueue_scripts', 'mira_agenda_grid_old_enqueue_assets' );
