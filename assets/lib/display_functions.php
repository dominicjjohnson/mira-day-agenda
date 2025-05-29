<?php
function get_time_slots($data){

    // This function should return the time slots for the schedule
    // For now, we will return an empty array
        $time_slots = array();
    /*
        $time_slots['time-0800'] = '8:00am';
        $time_slots['time-0830'] = '8:30am';
        $time_slots['time-0900'] = '9:00am';
        $time_slots['time-0930'] = '9:30am';
        $time_slots['time-1000'] = '10:00am';
        $time_slots['time-1030'] = '10:30am';
        $time_slots['time-1100'] = '11:00am';
        $time_slots['time-1130'] = '11:30am';
        $time_slots['time-1200'] = '12:00pm';
    */
// Harde coded time
// 0900, 0910, 0925, 0940, 0950, 1020, 1100, 1130, 1230, 1400, 1420, 1440, 1500, 1520, 1600,1630, 1710, 1750, 1845

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
  outputs:
  day: the date of the conference used in the search
  debug: true or false
  track1: the slug of the first track
  track2: the slug of the second track
  track3: the slug of the third track
  track4: the slug of the fourth track
  all-tracks: the slug of the all-tracks track

    */


  extract( shortcode_atts( array (
  'day' => '2025-10-02',
  'debug' => false,
  'track1' => 'track-1',
  'track2' => 'track-2',
  'track3' => 'track-3',
  'track4' => 'track-4',
  'alltracks' => 'all-tracks'
  ), $atts ) );

  $track1 = 'stream-three-energy';
  $track2 = 'consumer-confidence';
  $track3 = 'stream-two-finance';
  $track4 = 'stream-one-fleet-transition-case-studies';

  // For now, we will return an empty array
  $inputs = array();
  $inputs['day'] = $day;
  $inputs['debug'] = $debug;
  $inputs['trackslugs'] = array(
    1 => $track1,
    2 => $track2,
    3 => $track3,
    4 => $track4,
  );
  $inputs['all-tracks'] = $alltracks;

  return $inputs;
    
} // End Functions

function get_css_slots ($time_slots, $track_background_colour, $track_text_colour) {

  $output = "<style>\n";
  $output .= "\n  @media screen and (min-width:700px) {\n";
  $output .= "    .schedule {\n";
  $output .= "      display: grid;\n";
  $output .= "      grid-gap: 1em;\n";
  $output .= "      grid-template-rows:\n";
  $output .= "        [tracks] auto\n";

  $keys = array_keys($time_slots);
  $last_key = end($keys);
  foreach ($time_slots as $key => $value) {
    if ($key === $last_key) {
      $output .= "        [{$key}] 1fr;\n";
    } else {
      $output .= "        [{$key}] 1fr\n";
    }
  }

  $output .= "      grid-template-columns:\n";
  $output .= "        [times] 4em\n";
  $output .= "        [track-1-start] 1fr\n";
  $output .= "        [track-1-end track-2-start] 1fr\n";
  $output .= "        [track-2-end track-3-start] 1fr\n";
  $output .= "        [track-3-end track-4-start] 1fr\n";
  $output .= "        [track-4-end];\n";
  $output .= "    }\n";
  $output .= "  }\n";
  $output .= "  \n";
  $output .= "  /*************************\n";
  $output .= "   * VISUAL STYLES\n";
  $output .= "   * Design-y stuff ot particularly important to the demo\n";
  $output .= "   *************************/\n";
  $output .= "  \n";
  $output .= "  .track-1 {\n";
  $output .= "    background-color: {$track_background_colour['track-1']};\n";
  $output .= "    color: {$track_text_colour['track-1']};\n";
  $output .= "  }\n";
  $output .= "  \n";
  $output .= "  .track-2 {\n";
  $output .= "    background-color: {$track_background_colour['track-2']};\n";
  $output .= "    color: {$track_text_colour['track-2']};\n";
  $output .= "  }\n";
  $output .= "  \n";
  $output .= "  .track-3 {\n";
  $output .= "    background-color: {$track_background_colour['track-3']};\n";
  $output .= "    color: {$track_text_colour['track-3']};\n";
  $output .= "  }\n";
  $output .= "  \n";
  $output .= "  .track-4 {\n";
  $output .= "    background-color: {$track_background_colour['track-4']};\n";
  $output .= "    color: {$track_text_colour['track-4']};\n";
  $output .= "  }\n";
  $output .= "\n";
  $output .= "  .track-1 .session-time,\n";
  $output .= "  .track-1 .session-track,\n";
  $output .= "  .track-1 .session-presenter,\n";
  $output .= "  .track-1 .session-title a {\n";
  $output .= "    color: {$track_text_colour['track-1']};\n";
  $output .= "  }\n";
  $output .= "\n";
  $output .= "  .track-2 .session-time,\n";
  $output .= "  .track-2 .session-track,\n";
  $output .= "  .track-2 .session-presenter,\n";
  $output .= "  .track-2 .session-title a {\n";
  $output .= "    color: {$track_text_colour['track-2']};\n";
  $output .= "  }\n";
  $output .= "\n";
  $output .= "  .track-3 .session-time,\n";
  $output .= "  .track-3 .session-track,\n";
  $output .= "  .track-3 .session-presenter,\n";
  $output .= "  .track-3 .session-title a {\n";
  $output .= "    color: {$track_text_colour['track-3']};\n";
  $output .= "  }\n";
  $output .= "\n";
  $output .= "  .track-4 .session-time,\n";
  $output .= "  .track-4 .session-track,\n";
  $output .= "  .track-4 .session-presenter,\n";
  $output .= "  .track-4 .session-title a {\n";
  $output .= "    color: {$track_text_colour['track-4']};\n";
  $output .= "  }\n";
  $output .= "\n";
  $output .= "  .track-all {\n";
  $output .= "    display: flex;\n";
  $output .= "    background: {$track_background_colour['track-all']};\n";
  $output .= "    color: {$track_text_colour['track-all']};\n";
  $output .= "    box-shadow: none;\n";
  $output .= "  }\n";
  $output .= "  \n";
  $output .= "  .track-all .session-time,\n";
  $output .= "  .track-all .session-track,\n";
  $output .= "  .track-all .session-presenter,\n";
  $output .= "  .track-all .session-title a {\n";
  $output .= "    color: {$track_text_colour['track-all']} !important;\n";
  $output .= "  }\n";
  $output .= "\n</style>\n";

  return $output;
}


function get_schedule_header($headings) {

    $output = '';
    foreach ($headings as $key => $value) {
        if ($key !== 'track-all') {
            $output .= <<<HTML
            <span class="track-slot" aria-hidden="true" style="grid-column: {$key}; grid-row: tracks;">{$value}</span>
            HTML;
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
  Return an array of track names with the key being the track slug
  This is used to create the grid header
  $tracknames = array(
    'track-1' => 'Track 1',
    'track-2' => 'Track 2',
    'track-3' => 'Track 3',
    'track-4' => 'Track 4',
    'track-all' => 'All Tracks'
  );  

*/

$headings = array();
$track_background_colour = array();
$track_text_colour = array();

$unique_tracks = array(); // Declare the array outside the loop

if ($data->have_posts()) {
  while ($data->have_posts()) {
    $data->the_post();
    $tracks = get_the_terms(get_the_ID(), 'track');

    if (!empty($tracks) && !is_wp_error($tracks)) {
      foreach ($tracks as $track) {
        // Check if the track term ID is not already in the array
        if (!isset($unique_tracks[$track->term_id])) {
          $unique_tracks[$track->slug] = $track->name;
          if (!isset($track_background_colour[$track->slug])) {
            $track_background_colour[$track->slug] = get_term_meta($track->term_id, 'highlight_color', true);
          }
          if (!isset($track_text_colour[$track->slug])) {
            $track_text_colour[$track->slug] = get_term_meta($track->term_id, 'text_color', true);
          }
        }
      }
    }
  }
}

foreach ($unique_tracks as $slug => $track_name) {

  // Get the background colour of the track - using the slug
  

  if ($slug != "all-tracks") {
    $key = array_search($slug, $inputs['trackslugs']);
    if ($key !== false) {
      $headings['track-' . $key] = $track_name;
      // Set the colours - going to keep the slugs and colours in addition to the track-n as we might need later.
      $track_background_colour['track-' . $key] = $track_background_colour[$slug];
      $track_text_colour['track-' . $key] =       $track_text_colour[$slug];
    }
  }
  else {
    $headings['track-all'] = $track_name;
    $track_background_colour['track-all'] = $track_background_colour[$slug];
    $track_text_colour['track-all'] =       $track_text_colour[$slug];
  }
}

$headings['track-all'] = 'All Tracks';

return array(
  'headings' => $headings,
  'track_background_colour' => $track_background_colour,
  'track_text_colour' => $track_text_colour,
);

}

function get_args ($inputs) {


  $day = "2025-10-01"; // This should be the date entered by the user in YYYY-MM-DD format

  	// get only sessions with session-start meta value a match to the date entered YYYY-MM-DD format

// dsplay all tracks only

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
      )
    ),
  );


  return $args;
} 



function get_raw_agenda_data($args) {
  // This function should return the raw agenda data
  // For now, we will return an empty array

  $data = new WP_Query($args);
  if (!$data->have_posts()) {
    return false; // No posts found
  }
  return $data;
}

function remove_comma_from_time($time) {

  // Ensure the input is in the format HH:MM
  if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
    return false; // Invalid input
  }
  // Remove the colon to return HHMM
  return str_replace(':', '', $time);
}



function get_grid_session_data($data, $trackslugs) {
  // This function should return the session data for the grid

  $sessions = array();

  if ($data->have_posts()) {
    $i = 0;
    while ($data->have_posts()) {
      $data->the_post();

      $tracks = get_the_terms(get_the_ID(), 'track');

      if (!empty($tracks) && !is_wp_error($tracks) && $tracks[0]->slug == "all-tracks") {
        $track_cols = "track-1-start / track-4-end";

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

function make_themes_types_html($sessionID) {


  // This function should return the themes and types HTML
  // For now, we will return an empty array
  $types = get_the_terms($sessionID, 'type');

  $type = "";
  if (is_array($types)) {
    $type = "<div class='themes'>\n";
    foreach ($types as $typeObj) {
      $type .= "<span>".$typeObj->name . "</span>\n";
    }
    $type .= "</div>";
  }
  return $type;  
  

}

function get_one_speaker_html($speaker_post) {



  return $output;
}


function get_speaker_block_html ($postid) {

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

  // Start a wrapper for all roles
  $output .= '<div class="roles-grid" style="display: flex; gap: 2em;">';

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

    // Only display the role column if there are speakers
    if (!empty($speakers)) {
      $output .= '<div class="role-column" style="flex: 1 1 0; min-width: 180px;">';
      $output .= '<h3 style="text-align:left; font-size:1.1em; margin-bottom:0.7em;">' . esc_html($role->post_title) . '</h3>';

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
          $output .= '<br>' . esc_html($speaker_company);
        }
        $output .= '</p>';
        $output .= '</div>';
        // Modal HTML (hidden by default)
        $output .= '
        <div id="' . esc_attr($modal_id) . '" class="speaker-modal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);">
          <div style="background:#fff;max-width:350px;margin:10vh auto;padding:2em;position:relative;border-radius:8px;">
            <span class="close-speaker-modal" data-modal="' . esc_attr($modal_id) . '" style="position:absolute;top:10px;right:15px;font-size:1.5em;cursor:pointer;">&times;</span>
            <h4 style="margin-top:0;">' . esc_html($speaker_name) . '</h4>
            <p style="margin-bottom:0.7em;">' . esc_html($speaker_bio) . '</p>
          </div>
        </div>
        ';
        // Add JS and CSS only once per page (outside the loop)
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

  $output .= '</div>'; // Close roles-grid

  return $output;
}



function display_one_session ($sessions, $rowID) {


  //echo "RowID: " . $rowID . "<br>"; 
  //echo "SessionID: " . $sessions[$rowID]['sessionID'] . "<br>";

  $session_id = str_replace('session-', '', $sessions[$rowID]['sessionID']);

  // Check if the session is a special case

  if ($sessions[$rowID]['trackID'] == "track-all") {

    $type_html = make_themes_types_html($session_id);
    $speaker_html = '<span class="session-presenter">'.$sessions[$rowID]['sessionPresenter'].'</span>';
    $post_content = apply_filters('the_content', get_post_field('post_content', $session_id));
    $ALL_TRACKS_CONTENT_LENGTH = 20;
    $full_content = strip_tags($post_content);
    if (mb_strlen($full_content) > $ALL_TRACKS_CONTENT_LENGTH) {
      $short_content = mb_substr($full_content, 0, $ALL_TRACKS_CONTENT_LENGTH) . '...';
      $modal_id = 'modal-' . $session_id;
      $post_content = $short_content . ' <a href="#" class="more-details-link" data-modal="' . $modal_id . '">More details</a>';
      // Modal HTML (hidden by default)
      $post_content .= '
      <div id="' . $modal_id . '" class="modal" style="display:none;">
        <div class="modal-content">
          <span class="close-modal" data-modal="' . $modal_id . '">&times;</span>
          <div class="modal-body" style="margin: 20px;">' . apply_filters('the_content', get_post_field('post_content', $session_id)) . '</div>
        </div>
      </div>
      <script>
      document.addEventListener("DOMContentLoaded", function() {
        var link = document.querySelector(\'a.more-details-link[data-modal="' . $modal_id . '"]\');
        var modal = document.getElementById("' . $modal_id . '");
        var close = modal ? modal.querySelector(".close-modal") : null;
        if(link && modal && close) {
          link.addEventListener("click", function(e) {
        e.preventDefault();
        modal.style.display = "block";
          });
          close.addEventListener("click", function(e) {
        e.preventDefault();
        modal.style.display = "none";
          });
          window.addEventListener("click", function(event) {
        if(event.target === modal) {
          modal.style.display = "none";
        }
          });
        }
      });
      </script>
      ';
    }

    // This is a special case for the all-tracks session
    // We need to display the session details in a different format
    // For now, we will return an empty array

    $speaker_html = get_speaker_block_html($session_id);
    if (empty($speaker_html)) {
     // $speaker_html = '<span class="session-presenter">No speakers</span>';
    }

  $output = <<<HTML
    <div class="session {$sessions[$rowID]['sessionID']} {$sessions[$rowID]['trackID']}" style="grid-column: {$sessions[$rowID]['gridColumn']}; grid-row: {$sessions[$rowID]['gridRowStartTime']} / {$sessions[$rowID]['gridRowEndTime']}; text-align: left;">

    <div class="banner">
          {$type_html}
        <h3><a href="#">{$sessions[$rowID]['sessionsTitle']}</a></h3>
        <div class="event-details">
            <p><span class="icon">⏰</span> {$sessions[$rowID]['sessionTime']} - {$sessions[$rowID]['sessionTime']}</p>
            <p>{$post_content}</p>
        </div>

      {$speaker_html}


    </div>


    </div>
    HTML; 
  }
  else {
    $output = <<<HTML
    <div class="session {$sessions[$rowID]['sessionID']} {$sessions[$rowID]['trackID']}" style="grid-column: {$sessions[$rowID]['gridColumn']}; grid-row: {$sessions[$rowID]['gridRowStartTime']} / {$sessions[$rowID]['gridRowEndTime']};">
      <h5 class="session-title"><a href="#">{$sessions[$rowID]['sessionsTitle']}</a></h5>
      <span class="session-time">{$sessions[$rowID]['sessionTime']}</span>
      <span class="session-track">{$sessions[$rowID]['trackString']}</span>
      <span class="session-presenter">{$sessions[$rowID]['sessionPresenter']}</span>
    </div>
    HTML;
  }
  return $output;
}


/*
    <div class="content-wrapper">
        <div class="speakers">
            <h2>Speakers</h2>
            <div class="speaker-grid">
                <div class="speaker">
                    <img src="/evie/wp-content/uploads/2025/04/Matthew-Lumsden-CEO-Connected-Energy-400x400.jpg" alt="Adam Field">
                    {$speaker_html}
                    <p><strong>Adam Field</strong><br>Head of Marketing<br>Reiss</p>
                </div>
                <div class="speaker">
                    <img src="/evie/wp-content/uploads/2025/04/Matthew-Lumsden-CEO-Connected-Energy-400x400.jpg" alt="Lou McEwen">
                    <p><strong>Lou McEwen</strong><br>CMO<br>McLaren Racing</p>
                </div>
                <!-- Additional speakers go here -->
            </div>
        </div>
        <div class="moderator">
            <h2>Moderator</h2>
            <div class="moderator-details">
                <img src="/evie/wp-content/uploads/2025/04/Matthew-Lumsden-CEO-Connected-Energy-400x400.jpg" alt="Kerry Flynn">
                <p><strong>Kerry Flynn</strong><br>Media Reporter<br>Axios</p>
            </div>
        </div>
    </div>
    */