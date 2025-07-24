<?php
function get_time_slots($data){

    // This function should return the time slots for the schedule

$time_slots = [];
$rtn =[];
if ( !empty($data->posts) ) {
  foreach ( $data->posts as $post ) {
    $time_slots = get_unique_start_and_end_times($post, $time_slots);
  }
}
// order the time slots
sort($time_slots);

foreach ($time_slots as $time) {
  // Remove the colon from the time
  $rtn['time-'.remove_comma_from_time($time)] = $time;
}

return $rtn;
}

function get_parameters($atts) {
    /* This function should return the parameters for the shortcode
  Params:
  day: the date of the conference used in the search
  debug: true or false
  track1: the slug of the first track
  track2: the slug of the second track
  track3: the slug of the third track
  track4: the slug of the fourth track
  all-tracks: the slug of the all-tracks track
  border: yes - add additional css classes to display the border and not the background colour
  display_heading_bar: yes - display the bar at the top of the track columns. 
  link_title_to_details: true then link to details page false / default link to the pop-up.
  default_border_color: set to #dedede by default. Needs some work to be able to switch between set to track color  
  
    */


$atts = shortcode_atts([
        'day' => '',
        'all-tracks' => '',
        'border' => '',
        'display_heading_bar' => false,
        'display_heading_bar_page' => false,
        'track1' => '',
        'track2' => '',
        'track3' => '',
        'track4' => '',
        'track5' => '',
        'track6' => '',
        'track7' => '',
        'track8' => '',
        'time_slot_side' => false,
        'show_end_time' => false,
        'show_session_duration' => false,
        'link_title_to_details' => false,
        'default_border_color' => '#dedede'
  ], $atts);
  
  // Extract values into individual variables
  $day = esc_html($atts['day'] ?? '');
  $alltracks = esc_html($atts['all-tracks'] ?? 'all-tracks');
  $track1 = esc_html($atts['track1'] ?? '');
  $track2 = esc_html($atts['track2'] ?? '');
  $track3 = esc_html($atts['track3'] ?? '');
  $track4 = esc_html($atts['track4'] ?? '');
  $track5 = esc_html($atts['track5'] ?? '');
  $track6 = esc_html($atts['track6'] ?? '');
  $track7 = esc_html($atts['track7'] ?? '');
  $track8 = esc_html($atts['track8'] ?? '');
  $border = esc_html($atts['border'] ?? '');
  $display_heading_bar = esc_html($atts['display_heading_bar'] ?? '');
  $display_heading_bar_page = esc_html($atts['display_heading_bar_page'] ?? '');
  
  // New flags
  $time_slot_side = filter_var($atts['time_slot_side'], FILTER_VALIDATE_BOOLEAN);
  $show_end_time = filter_var($atts['show_end_time'], FILTER_VALIDATE_BOOLEAN);
  $show_session_duration = filter_var($atts['show_session_duration'], FILTER_VALIDATE_BOOLEAN);
  $link_title_to_details = filter_var($atts['link_title_to_details'], FILTER_VALIDATE_BOOLEAN);
  
  $default_border_color = "#dddddd";
  
  // set some default values
  $inputs['time_slot_side'] =  false;
  $inputs['show_session_duration'] = true;
  $inputs['link_title_to_details'] = false;

  $inputs = array();

  if (empty($day)) {
      $inputs['error'] = true;
      $inputs['error_message'] = "No day value set.";
  } else {
      $inputs['error'] = false;
      $inputs['day'] = $day;
      $inputs['trackslugs'] = array(
          1 => $track1,
          2 => $track2,
          3 => $track3,
          4 => $track4,
          5 => $track5,
          6 => $track6,
          7 => $track7,
          8 => $track8,
      );
      
      $inputs['all-tracks'] = $alltracks;
      $inputs['border'] = $border;
      $inputs['display_heading_bar'] = $display_heading_bar;
      $inputs['display_heading_bar_page'] = $display_heading_bar_page;
      $inputs['time_slot_side'] = $time_slot_side;
      $inputs['show_end_time'] = $show_end_time;
      $inputs['show_session_duration'] = $show_session_duration;
      $inputs['link_title_to_details'] = $link_title_to_details;
      $inputs['default_border_color'] = $default_border_color;

      $track_count = 0;
      foreach ($inputs['trackslugs'] as $slug) {
        if (!empty($slug)) {
          $track_count++;
        }
      }
      $inputs['number_of_tracks'] = $track_count;
  }

  return $inputs;
    
} // End Functions

function get_css_slots ($time_slots, $track_background_colour, $track_text_colour,$inputs) {
    
  $output = "<style>\n";
  
  // Turn off display of times on the left hand side if flag set.
  if (!$inputs['time_slot_side']) {
    $output .= ".time-slot { display: none; } \n";
  }
  
  $output .= "\n  @media screen and (min-width:700px) {\n";
  $output .= "    .schedule {\n";
  $output .= "      display: grid;\n";
  $output .= "      grid-gap: 0.25em;\n";
  $output .= "      grid-template-rows:\n";
  $output .= "        [tracks] auto\n";

  $keys = array_keys($time_slots);
  $last_key = end($keys);
  foreach ($time_slots as $key => $value) {
    if ($key === $last_key) {
      $output .= "        [{$key}] auto;\n";
    } else {
      $output .= "        [{$key}] auto\n";
    }
  }

  $output .= "      grid-template-columns:\n";
  
  if ($inputs['time_slot_side']) {
    $output .= "        [times] 4em\n";
  }
  else {
    $output .= "        [times] 0em\n";
  }
  
  $number_of_tracks = $inputs['number_of_tracks'];
  // Dynamically generate grid columns based on $number_of_tracks
  for ($i = 1; $i <= $number_of_tracks; $i++) {
    if ($i === 1) {
      $output .= "        [track-1-start] 1fr\n";
    } else {
      $output .= "        [track-" . ($i - 1) . "-end track-{$i}-start] 1fr\n";
    }
  }
  $output .= "        [track-{$number_of_tracks}-end];\n";

  $output .= "    }\n";
  $output .= "  }\n";
  $output .= "  \n";
  $output .= "  /*************************\n";
  $output .= "   * VISUAL STYLES\n";
  $output .= "   * Design-y stuff ot particularly important to the demo\n";
  $output .= "   *************************/\n";
  $output .= "  \n";

  // Helper for safe value
  function _safe_val($arr, $key, $default = '') {
    return isset($arr[$key]) && $arr[$key] !== '' ? $arr[$key] : $default;
  }

  for ($i = 1; $i <= 7; $i++) {
    $bg = _safe_val($track_background_colour, "track-$i");
    $txt = _safe_val($track_text_colour, "track-$i");

    if ($bg !== '' || $txt !== '') {
      $output .= "  .track-{$i} {\n";
      if ($bg !== ''){
        $output .= "    background-color: {$bg};\n";
        $output .= "    border-color: {$bg};\n";
      }
      if ($txt !== '') $output .= "    color: {$txt} !important;\n";
      
      $output .= "  }\n";
      
      // setup the border colour - same as track: track-n-border
      $output .= "  .track-{$i}-border {\n";
      if ($bg !== ''){
        $output .= "    border-color: {$bg};\n";
      }
      else {
        $output .= "    border-color: #dedede;\n";
      }
      $output .= "  }\n";     
      
      // Setup the track headings
      $output .= "  .track-{$i}-slot {\n";
      if ($bg !== ''){
        $output .= "    background-color: {$bg};\n";
      }
      else {
        $output .= "    background-color: #dedede;\n";
      }

      if ($txt !== ''){
        $output .= "    color: {$txt};\n";
      } 
      else {
        $output .= "    color: #000000;\n";
      }

      $output .= "  }\n";        
      
      
      // Set the text color in the a. Used to be background too
      $output .= "  .track-{$i} a {\n";
      if ($txt !== '') $output .= "    color: {$txt} !important;\n";
      $output .= "  }\n";
    }
  }

  $output .= "\n";
  for ($i = 1; $i <= 7; $i++) {
    $txt = _safe_val($track_text_colour, "track-$i");
    if ($txt !== '') {
      $output .= "  .track-{$i}, \n";
      $output .= "  .track-{$i} .session-time,\n";
      $output .= "  .track-{$i} .session-track,\n";
      $output .= "  .track-{$i} .session-presenter p,\n";
      $output .= "  .track-{$i} .session-title a {\n";
      $output .= "    color: {$txt} !important;\n";
      $output .= "  }\n";
      $output .= "  .track-{$i} .speaker-role-title {\n";
      $output .= "    color: {$txt} !important;\n";
      $output .= "    text-align: left;\n";
      $output .= "    font-size: 1.1em;\n";
      $output .= "    margin-bottom: 0.7em;\n";
      $output .= "    font-weight: bold;\n";
      $output .= "    padding-top: 0.5em;\n";
      $output .= "  }\n";
      $output .= ".border-track-{$i} {";
      $output .= "    background-color: white;";
      $output .= "    border-color: grey;";
      $output .= "    border-style: solid;";
      $output .= "  }";
      
      $output .= "\n";
    }
  }

  // Track-all
  $bg_all =  $track_background_colour['allcolumns'] ?? '#ffffff'; // white fallback
  $txt_all = $track_text_colour['allcolumns'] ?? '#000000';
  
  if ($bg_all !== '' || $txt_all !== '') {
    $output .= "  .track-all {\n";
    $output .= "    display: flex;\n";
    $output .= "    border-color: ".$inputs['default_border_color'].";\n";
    
    // if the border is set to true set the border color. If false then set the bacgrund color.
    if ( (!$inputs['border']) && ($bg_all !== '') ) {
      $output .= "    background: {$bg_all};\n";
    }
    else {
      $output .= " .bg_color_alltracks { border-color: {$bg_all}; }\n";
    }
    
    if ($txt_all !== '') $output .= "    color: {$txt_all};\n";
    $output .= "    box-shadow: none;\n";
    $output .= "  }\n";
    $output .= "  \n";
    $output .= "  .track-all .session-time,\n";
    $output .= "  .track-all .session-track,\n";
    $output .= "  .track-all .session-presenter,\n";
    $output .= "  .track-all .session-title a {\n";
    if ($txt_all !== '') $output .= "    color: {$txt_all} !important;\n";
    $output .= "  }\n";
    
    $output .= "  .track-all .speaker-role-title {\n";
    if ($txt_all !== '') $output .= "    color: {$txt_all} !important;\n";
    $output .= "    text-align: left;\n";
    $output .= "    font-size: 1.1em;\n";
    $output .= "    margin-bottom: 0.7em;\n";
    $output .= "  }\n";
    
  }

  $output .= "\n</style>\n";

  return $output;
}

function get_single_heading_html($key,$value,$add_bottom_margin) {
  
  if ($add_bottom_margin) { 
    $add_bottom_margin_html = " margin-bottom:10px; ";
  }
  else { $add_bottom_margin_html = ""; }
  
  $output = <<<HTML
  <span class="track-slot {$key}-slot" aria-hidden="true" style="grid-column: {$key}; grid-row: tracks; $add_bottom_margin_html ">{$value}</span>
  HTML;
  
  return $output;
}

function get_schedule_header($headings) {

    $output = '';

   // <span class="track-slot {$key}" aria-hidden="true" style="grid-column: {$key}; grid-row: tracks;">{$value}</span>

    
    foreach ($headings as $key => $value) {
        if ($key !== 'track-all') {
          $output .= get_single_heading_html($key,$value,false);
        }
    }
    // Return the HTML to be rendered 
    return $output;

}

function add_session(&$sessions, $rowID, $sessionID, $trackID, $gridColumn, $gridRowStartTime, $gridRowEndTime, $sessionsTitle, $sessionTime, $trackString, $sessionPresenter) {
    $sessions[$rowID] = array(
        'sessionID' => $sessionID,
        'trackID' => $trackID,
        'gridColumn' => $gridColumn,
        'gridRowStartTime' => $gridRowStartTime,
        'gridRowEndTime' => $gridRowEndTime,
        'sessionsTitle' => $sessionsTitle,
        'sessionTime' => $sessionTime,
        'trackString' => $trackString,
        'sessionPresenter' => $sessionPresenter
    );
}

function get_headings($data,$inputs) {
  /*
    get all the tracks and their names. 

    RETURNS:
    $headings: an array of headings with the key being the track slug, e.g. [track-3] =&gt; Finance
    $track_background_colour: an array of track background colours with the key being the track slug
    $track_text_colour: an array of track text colours with the key being the track slug  
  */
  
  $headings = array();
  $track_background_colour = array();
  $track_text_colour = array();
  
  $unique_tracks = array(); // Declare the array outside the loop
  
if ($data instanceof WP_Query && $data->have_posts()) {
      // Loop through posts
  while ($data->have_posts()) {
      $data->the_post();
      $tracks = get_the_terms(get_the_ID(), 'track');
  
      if (!empty($tracks) && !is_wp_error($tracks)) {
        foreach ($tracks as $track) {
          // Check if the track term ID is not already in the array
          if (!isset($unique_tracks[$track->term_id])) {
            $unique_tracks[$track->slug] = $track->name;
            // The old seminar system used the meta_key 'color'. The new one - 'term-color' for the track background colour
            if (!isset($track_background_colour[$track->slug])) {
                $track_background_colour[$track->slug] = get_term_meta($track->term_id, 'term-color', true);
                if (empty($track_background_colour[$track->slug])) {
                  $track_background_colour[$track->slug] = get_term_meta($track->term_id, 'color', true);
                  if (empty($track_background_colour[$track->slug])) { 
                     $track_background_colour[$track->slug] = get_term_meta($track->term_id, 'highlight_color', true);
                   }
                }
                
            }
            
            
            // here we are either setting text_color or we have track_text_colour which is 
            if (!isset($track_text_colour[$track->slug])) {
              $track_text_colour[$track->slug] = get_term_meta($track->term_id, 'text_color', true);
              if (empty($track_text_colour[$track->slug])) {
                $track_text_colour[$track->slug] = get_term_meta($track->term_id, 'color', true);
                if (empty($track_text_colour[$track->slug])) { 
                  $track_text_colour[$track->slug] = get_term_meta($track->term_id, 'highlight_color', true);
                 }
              }   
            }
            
            // look to see if using field track_text_colour
            $track_text_colour_in = get_term_meta($track->term_id, 'track_text_colour', true);
                        
            if (isset($track_text_colour_in) && !empty($track_text_colour_in)) {
              if ($track_text_colour_in == "lighttext") {
                $track_text_colour[$track->slug] = "#ffffff";
              } else {
                $track_text_colour[$track->slug] = "#000000";
              }
            }

            
            
/*
?? Need to work out what to do here as we have 2 ways to define the colour. Get it working for solar and see.


echo "xxx3".get_term_meta($track->term_id, 'text_color', true)."\n";


            if (!isset($track_text_colour[$track->slug])) {
              $track_text_colour[$track->slug] = get_term_meta($track->term_id, 'text_color', true);
            }

            $meta_text_colour = get_term_meta($track->term_id, 'track_text_colour', true);
            if ($meta_text_colour === 'lighttext') {
                $track_text_colour[$track->slug] = '#ffffff'; // White text for lighttext
            } else {
                $track_text_colour[$track->slug] = '#000000'; // Black text otherwise
            }
            
*/            
            
            
          }
        }
      }
    }
  }
  else {
      echo "No posts found or query failed.";
  }

  foreach ($unique_tracks as $slug => $track_name) {
    // Get the background colour of the track - using the slug
    
    //if ($slug != "all-tracks") {
      $key = array_search($slug, $inputs['trackslugs']);
      if ($key !== false) {
        $headings['track-' . $key] = $track_name;
        // Set the colours - going to keep the slugs and colours in addition to the track-n as we might need later.
        $track_background_colour['track-' . $key] = $track_background_colour[$slug];
        $track_text_colour['track-' . $key] =       $track_text_colour[$slug];
      }
      else {
        $key = "all";
        // Here with all tracks - set the all tracks colours
        $headings['track-' . $key] = $track_name;
        // Set the colours - going to keep the slugs and colours in addition to the track-n as we might need later.
        // We're setting both the slug for all tracks and track-all. Might not need this but it was driving me mad so kept it in.
        $track_background_colour['track-' . $key] = $track_background_colour[$slug];
        $track_text_colour['track-' . $key] =       $track_text_colour[$slug];
        $track_background_colour['track-all'] = $track_background_colour[$slug];
        $track_text_colour['track-all'] =       $track_text_colour[$slug];
                
      }
    //}
  }
  return array(
    'headings' => $headings,
    'track_background_colour' => $track_background_colour,
    'track_text_colour' => $track_text_colour,
  );

}

function get_args ($inputs) {

  $day = $inputs['day']; // This should be the date entered by the user in YYYY-MM-DD format

  // Collect non-empty track slugs
  $track_terms = array_filter($inputs['trackslugs']);
  
  // Check if "all-tracks" is selected and remove filtering
  if ($inputs['all-tracks'] != '') {
      $track_terms[] = $inputs['all-tracks'];
  }
  
  $args = array(
      'post_type' => 'seminars',
      'posts_per_page' => -1,
      'meta_key' => 'time_start',
      'orderby' => 'meta_value',
      'order' => 'ASC',
      'tax_query' => array(
          'relation' => 'AND',
          array(
              'taxonomy' => 'date',
              'field' => 'slug',
              'terms' => $day,
          ),
          // Only add the track taxonomy filter if there are tracks selected
          !empty($track_terms) ? array(
              'taxonomy' => 'track',
              'field' => 'slug',
              'terms' => $track_terms,
          ) : null,
      ),
  );
  
  // Remove null values from tax_query (in case no tracks are selected)
  $args['tax_query'] = array_values(array_filter($args['tax_query']));


  return $args;
} 

function get_raw_agenda_data($args) {
  // This function should return the raw agenda data
  // For now, we will return an empty array

  $args['post_status'] = 'publish';
  $data = new WP_Query($args);
  if (!$data->have_posts()) {
    return [
        'error' => true,
        'error_message' => 'No posts found.',
    ];
  }

  return [
    'error' => false,
    'data'  => $data,
  ];
}

function remove_comma_from_time($time) {

  // Ensure the input is in the format HH:MM
  if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
    return false; // Invalid input
  }
  // Remove the colon to return HHMM
  return str_replace(':', '', $time);
}

function get_grid_session_data($data, $trackslugs, $alltracks) {
  // This function should return the session data for the grid

  $sessions = array();

  if ($data->have_posts()) {
    $i = 0;
    while ($data->have_posts()) {
      $data->the_post();

      $tracks = get_the_terms(get_the_ID(), 'track');

      if (!empty($tracks) && !is_wp_error($tracks) && $tracks[0]->slug == $alltracks) {

        $track_cols = "track-1-start / track-6-end";

        add_session(
          $sessions,
          $i,
          'session-' . get_the_ID(),
          'track-all',
          $track_cols,
          'time-' . remove_comma_from_time(get_post_meta(get_the_ID(), 'time_start', true)),
          'time-' . remove_comma_from_time(get_post_meta(get_the_ID(), 'time_end', true)),
          get_the_title(),
          get_post_meta(get_the_ID(), 'time_start', true) . ' - ' . get_post_meta(get_the_ID(), 'time_end', true),
          'Track: All Tracks',
          'Dom J',
          apply_filters('the_content', get_post_field('post_content', get_the_ID()))
        );
      } else {
        // here when the track is not all-tracks and needs to go into a column
        if (is_array($tracks) && isset($tracks[0])) {
          $key = array_search($tracks[0]->slug, $trackslugs);
          if ($key !== false) {
            $track_cols = "track-{$key}";
            $track_name = $tracks[0]->name;
          } else {
            $track_cols = "track-1"; // Default to track-1 if not found
            $track_name = "";
          }
        } else {
          // If there are no tracks, default to all-tracks
          $track_cols = "all-tracks";
          $track_name = "";
        }

        add_session(
          $sessions,
          $i,
          'session-' . get_the_ID(),
          $track_cols,
          $track_cols,
          'time-' . remove_comma_from_time(get_post_meta(get_the_ID(), 'time_start', true)),
          'time-' . remove_comma_from_time(get_post_meta(get_the_ID(), 'time_end', true)),
          get_the_title(),
          get_post_meta(get_the_ID(), 'time_start', true) . ' - ' . get_post_meta(get_the_ID(), 'time_end', true),
          $track_name,
          'Dom J',
          apply_filters('the_content', get_post_field('post_content', get_the_ID())),

        );
      }
      $i++;

      if ($i == 3) {
        //break; // Limit to 100 sessions for performance
      }

    }
    wp_reset_postdata();
  }

  return $sessions;
}

function make_themes_types_html($sessionID, $minutes, $show_end_time, $start_time, $end_time) {

  // This function should return the themes and types HTML
  // For now, we will return an empty array
  $types = get_the_terms($sessionID, 'type');

  $type = "";
  if (is_array($types)) {
    $type = "<div class='themes'>\n";
    foreach ($types as $typeObj) {
      $type .= "<span>".$typeObj->name . "</span>\n";
    }
    if ($show_end_time) {
      $type .= '<i class="fa-solid fa-clock"></i> ' . $start_time . ' - ' . $end_time;
    }
    else {
      $type .= '<i class="fa-solid fa-clock"></i> ' . $minutes. "m";
    }
    
    $type .= "</div>";
  }
  return $type;  
  

}

function get_speaker_block_html ($postid, $track, $all_tracks) {

  $output = '';
  // This function should return the speaker block HTML
  // For now, we will return an empty array
  if (empty($postid)) {
    return $output; // No post ID provided
  }
  if (!is_numeric($postid)) {
    return $output; // Invalid post ID
  }
  if (!function_exists('miramedia_get_speaker_roles_ordered')) {
    return $output; // Function not defined
  }
  if (!function_exists('miramedia_p2p_get_seminar_speakers_by_role')) {
    return $output; // Function not defined
  }
    //  * Get speaker roles, in order.
  $roles = miramedia_get_speaker_roles_ordered();
  if (empty($roles)) {
    return $output; // No roles found
  }

  // Start a wrapper for all roles - only for all tracks, not for columns
  if ($all_tracks) {
    $output .= '<div class="roles-grid">';
  }

  foreach( $roles as $roleslug ){

    $role_posts = get_posts( array(
      'name'           => $roleslug,
      'post_type'      => 'speakerrole',
      'post_status'    => 'publish',
      'posts_per_page' => 1
    ) );
    if( empty($role_posts) ){
      continue;
    }

    $role = reset($role_posts);
    $speakers = miramedia_p2p_get_seminar_speakers_by_role( $postid, $roleslug );
    
    // Set the singluar, plural value depending on the number of speakers returned.
    if ( count( $speakers ) > 1 ) {
      $role_title = get_post_meta( $role->ID, 'plural', true );
    }
    else {
      $role_title = get_post_meta( $role->ID, 'singluar', true );
    }
    
    // Set a default
    if ( $role_title == "" ) {
      $role_title = esc_html($role->post_title);
    }

    // Only display the role column if there are speakers
    if (!empty($speakers)) {
      $output .= '<div class="role-column" style="flex: 1">';
      $output .= '<p class="speaker-role-title">' . $role_title . '</p>';

      foreach ($speakers as $speaker_post) {
        if (empty($speaker_post) || !is_numeric($speaker_post->ID)) {
          continue;
        }
        $speaker_name = get_the_title($speaker_post->ID);
        $speaker_image = get_the_post_thumbnail_url($speaker_post->ID, 'thumbnail');
        $speaker_job = get_post_meta($speaker_post->ID, 'speaker_speaker_job_title', true);
        $speaker_company = get_post_meta($speaker_post->ID, 'speaker_company_name', true);
        $SPEAKER_BIO_SUMMERY_LENGTH = 40;
        $speaker_bio_full = strip_tags(get_post_field('post_content', $speaker_post->ID));
        $speaker_bio = mb_strlen($speaker_bio_full) > $SPEAKER_BIO_SUMMERY_LENGTH
            ? mb_substr($speaker_bio_full, 0, $SPEAKER_BIO_SUMMERY_LENGTH) . '...'
            : $speaker_bio_full;

        // Unique modal ID for this speaker
        $modal_id = 'speaker-modal-' . $speaker_post->ID;

        $output .= '<div class="speaker" style="display: flex; align-items: flex-start; gap: 0.7em; margin-bottom: 0.6em;">';
        if ($speaker_image) {
          $output .= '<img src="' . esc_url($speaker_image) . '" alt="' . esc_attr($speaker_name) . '" '
            . 'style="width:50px;height:50px;object-fit:cover;border-radius:50%;cursor:pointer;transition:transform 0.2s;" '
            . 'class="speaker-img-clickable" data-modal="' . esc_attr($modal_id) . '">';
        }
        $output .= '<div style="display: flex; flex-direction: column; justify-content: flex-start;">';
        $output .= '<p style="margin:0;"><strong>' . esc_html($speaker_name) . '</strong>';
        if ($speaker_job) {
          $output .= '<br>' . esc_html($speaker_job);
        }
        if ($speaker_company) {
          $output .= ', <i>' . esc_html($speaker_company) . "</i>";
        }
        $output .= '</p>';
        $output .= '</div>';
        // Modal HTML (hidden by default)
        $output .= '
        <div id="' . esc_attr($modal_id) . '" class="speaker-modal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);">
          <div style="background:#fff;max-width:350px;margin:10vh auto;padding:2em;position:relative">
            <span class="close-speaker-modal" data-modal="' . esc_attr($modal_id) . '" style="position:absolute;top:10px;right:15px;font-size:1.5em;cursor:pointer;">&times;</span>
            <h4 style="margin-top:0;">' . esc_html($speaker_name) . '</h4>
            <p style="margin-bottom:0.7em;">' . esc_html($speaker_bio) . '</p>
          </div>
        </div>
        ';
        // Add JS and only once per page (outside the loop)
        static $speaker_modal_script_output = false;
        if (!$speaker_modal_script_output) {
          $output .= '
          <style>
            .speaker-img-clickable:hover {
              transform: scale(1.05);
              box-shadow: 0 0 0 2px #0073aa33;
            }
            .speaker-modal { animation: fadeInSpeakerModal 0.2s; }
            @keyframes fadeInSpeakerModal { from { opacity: 0; } to { opacity: 1; } }
          </style>
          <script>
            document.addEventListener("DOMContentLoaded", function() {
              document.querySelectorAll(".speaker-img-clickable").forEach(function(img) {
          img.addEventListener("click", function() {
            var modal = document.getElementById(img.getAttribute("data-modal"));
            if (modal) modal.style.display = "block";
          });
              });
              document.querySelectorAll(".close-speaker-modal").forEach(function(btn) {
          btn.addEventListener("click", function() {
            var modal = document.getElementById(btn.getAttribute("data-modal"));
            if (modal) modal.style.display = "none";
          });
              });
              window.addEventListener("click", function(event) {
          if (event.target.classList && event.target.classList.contains("speaker-modal")) {
            event.target.style.display = "none";
          }
              });
            });
          </script>
          ';
          $speaker_modal_script_output = true;
        }
        $output .= '</div>';
      }

      $output .= '</div>'; // Close role-column
    }
    // If no speakers, do not output the role column or title
  }

  if ($all_tracks) {
    $output .= '</div>'; // Close roles-grid
  }

  

  return $output;
}

function display_one_session ($sessions, $rowID,$inputs,$headings, $display_heading) {

  // Final check to make sure you are to display track headings. Shouldn't be needed but there is an issue with the logic.
  
  if ($inputs['display_heading_bar'] == "false") {
    $display_heading = false;
  }



  // set a border class. at the moment I'm only using in tracks. Might need it in all-tracks too
  $border = "yes";
  if ($inputs['border'] == "yes") {
      $border = "border-" . $sessions[$rowID]['trackID'];
  }
  
  $session_id = str_replace('session-', '', $sessions[$rowID]['sessionID']);

  // if show_end_time then leave as it is.
  $time_split = parse_session_time_details($sessions[$rowID]['sessionTime']);

  // Display Type and duration if show_end_time = false OR start end time if true
  $type_html = make_themes_types_html($session_id, $time_split['duration_minutes'], $inputs['show_end_time'], $time_split['start_time'], $time_split['end_time']);
  
  $speaker_html = '<span class="session-presenter">'.$sessions[$rowID]['sessionPresenter'].'</span>';
  $post_content = apply_filters('the_content', get_post_field('post_content', $session_id));

  $link_on_title = "";

  $ALL_TRACKS_CONTENT_LENGTH = 100;
  $full_content = strip_tags($post_content);
  if (mb_strlen($full_content) > $ALL_TRACKS_CONTENT_LENGTH) {
    
    $short_content = mb_substr($full_content, 0, $ALL_TRACKS_CONTENT_LENGTH) . '...';
    $modal_id = 'modal-' . $session_id;
    $post_content = $short_content . ' <i class="fas fa-info-circle" aria-hidden="true"></i> <a href="#" class="more-details-link" data-modal="' . $modal_id . '">  Full Description</a>';
    
    /*
      if link_title_to_details param is true then set $link_on_title to the details page
      if not then link to the modal popup.
    */
    if (!$input['link_title_to_details']) {
      $link_on_title = '<a href="#" class="more-details-link" data-modal="' . $modal_id . '">';
    }   
    
    // Modal HTML (hidden by default)
  $post_content .= '
      <div id="' . $modal_id . '" class="modal" style="display:none;">
        <div class="modal-content">
          <span class="close-modal" data-modal="' . $modal_id . '">&times;</span>
          <div class="modal-body" style="margin: 20px;">
          <span class="title"><b>'.esc_html($sessions[$rowID]['sessionsTitle']).'</b></span>
          ' . apply_filters('the_content', get_post_field('post_content', $session_id)) . '</div>
        </div>
      </div>';

  }

  // This is a special case for the all-tracks session
  // We need to display the session details in a different format
  // For now, we will return an empty array

  // Only display the speakers in a grid if it is all-tracks. Contsruct a variable to pass in and set a class
  if ($sessions[$rowID]['trackID'] == "track-all") {
    $all_tracks = true;
  }
  else {
    $all_tracks = false;
  }

  $speaker_html = get_speaker_block_html($session_id, $sessions[$rowID]['trackID'], $all_tracks);
  if (empty($speaker_html)) {
   // $speaker_html = '<span class="session-presenter">No speakers</span>';
  }

  if (!empty($full_content)) {
    if ($link_on_title == "") {
      $details_link = get_permalink($session_id);
      $session_title_link = '<a href="' . esc_url($details_link) . '">' . esc_html($sessions[$rowID]['sessionsTitle']) . '</a>';
    }
    else {
      $session_title_link = $link_on_title . esc_html($sessions[$rowID]['sessionsTitle']) . '</a>';
    }
  } else {
    $session_title_link = esc_html($sessions[$rowID]['sessionsTitle']);
  }

  if ($sessions[$rowID]['trackID'] == "track-all") {
    $output = <<<HTML
      <div class="session {$sessions[$rowID]['sessionID']} {$sessions[$rowID]['trackID']}" style="grid-column: {$sessions[$rowID]['gridColumn']}; grid-row: {$sessions[$rowID]['gridRowStartTime']} / {$sessions[$rowID]['gridRowEndTime']}; text-align: left;">
        <div class="banner">
          <div class="title_time_display title_time_display_alltracks bg_color_alltracks">
            <span class="time">{$time_split['start_time']}</span>
            <span class="title">{$session_title_link}</span>
          </div>
          {$type_html}
          <div class="event-details">
            <p>{$post_content}</p>
          </div>
          {$speaker_html}
        </div>
      </div>
      HTML; 
      
      // removed <span class="icon">⏰</span>
      
  }
  else {
    
    //$track_heading = <span class="track-slot {$key}" aria-hidden="true" style="grid-column: {$key}; grid-row: tracks;">{$value}</span>
    $track_heading ="";
    if ($display_heading) {
      $track_number = $sessions[$rowID]['trackID'];
      
      $track_heading = get_single_heading_html($track_number,$headings[$track_number],true);
      
      //$track_heading = '<span class="track-slot 1" aria-hidden="true" style="grid-column: he; grid-row: tracks;">'.$headings[$track_number].'</span>';
    }
    
    // removed       <span class="session-track">{$sessions[$rowID]['trackString']}</span>
    
    $output = <<<HTML
    
    <div class="session {$sessions[$rowID]['sessionID']} {$sessions[$rowID]['gridColumn']}-border" style="grid-column: {$sessions[$rowID]['gridColumn']}; grid-row: {$sessions[$rowID]['gridRowStartTime']} / {$sessions[$rowID]['gridRowEndTime']}; border-width: 1px;">

      {$track_heading}
      <div class="title_time_display title_time_display_cols">
        <span class="time">{$time_split['start_time']}</span>
        <span class="title">{$session_title_link}</span>
      </div>
      <div class="event-details">
        <p>{$post_content}</p>
      </div>
      {$type_html}

      <span class="session-presenter">{$speaker_html}</span>
    </div>
    HTML;
  }
  return $output;
}

function parse_session_time_details($timeRange) {
    // Expecting format: "HH:MM - HH:MM"
    list($start_time, $end_time) = array_map('trim', explode('-', $timeRange));

    $start_dt = DateTime::createFromFormat('H:i', $start_time);
    $end_dt = DateTime::createFromFormat('H:i', $end_time);

    if ($end_dt < $start_dt) {
        $end_dt->modify('+1 day');
    }

    $interval = $start_dt->diff($end_dt);
    $duration_minutes = ($interval->h * 60) + $interval->i;

    return [
        'start_time' => $start_time,
        'end_time' => $end_time,
        'duration_minutes' => $duration_minutes
    ];
}


