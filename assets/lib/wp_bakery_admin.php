<?php

if (function_exists('vc_map')) {
    vc_map(array(
        'name'     => 'MM Agenda Grid',
        'base'     => 'agenda-grid',
        'category' => 'Content',
        'params'   => array(
            array(
                "type"        => "textfield",
                "heading"     => __("Date Slug", 'vc_extend'),
                "param_name"  => "day",
                "description" => __("Enter the date slug (max 25 chars)", 'vc_extend'),
                "admin_label" => true,
                "maxlength"   => 25,
            ),
            array(
                'type'        => 'textfield',
                'heading'     => 'All Tracks Slug',
                'param_name'  => 'all-tracks',
                'description' => 'Enter the slug for "All Tracks" (max 25 chars).',
                'admin_label' => true,
                'maxlength'   => 25,
            ),
            array(
                'type'        => 'textfield',
                'heading'     => 'Track 1 Slug',
                'param_name'  => 'track1',
                'description' => 'Enter the slug for Track 1 (max 25 chars).',
                'admin_label' => true,
                'maxlength'   => 25,
            ),
            array(
                'type'        => 'textfield',
                'heading'     => 'Track 2 Slug',
                'param_name'  => 'track2',
                'description' => 'Enter the slug for Track 2 (max 25 chars).',
                'admin_label' => true,
                'maxlength'   => 25,
            ),
            array(
                'type'        => 'textfield',
                'heading'     => 'Track 3 Slug',
                'param_name'  => 'track3',
                'description' => 'Enter the slug for Track 3 (max 25 chars).',
                'admin_label' => true,
                'maxlength'   => 25,
            ),
            array(
                'type'        => 'textfield',
                'heading'     => 'Track 4 Slug',
                'param_name'  => 'track4',
                'description' => 'Enter the slug for Track 4 (max 25 chars).',
                'admin_label' => true,
                'maxlength'   => 25,
            ),
            array(
                'type'        => 'textfield',
                'heading'     => 'Track 5 Slug',
                'param_name'  => 'track5',
                'description' => 'Enter the slug for Track 5 (max 25 chars).',
                'admin_label' => true,
                'maxlength'   => 25,
            ),
            array(
                'type'        => 'textfield',
                'heading'     => 'Track 6 Slug',
                'param_name'  => 'track6',
                'description' => 'Enter the slug for Track 6 (max 25 chars).',
                'admin_label' => true,
                'maxlength'   => 25,
            ),
            array(
                'type'        => 'textfield',
                'heading'     => 'Track 7 Slug',
                'param_name'  => 'track7',
                'description' => 'Enter the slug for Track 7 (max 25 chars).',
                'admin_label' => true,
                'maxlength'   => 25,
            ),
            array(
                'type'        => 'textfield',
                'heading'     => 'Track 8 Slug',
                'param_name'  => 'track8',
                'description' => 'Enter the slug for Track 8 (max 25 chars).',
                'admin_label' => true,
                'maxlength'   => 25,
            ),
            array(
                'type'        => 'dropdown',
                'heading'     => 'Border',
                'param_name'  => 'border',
                'description' => 'Display border around the agenda grid.',
                'value'       => array(
                    'Yes' => 'yes',
                    'No'  => 'no',
                ),
                'std'         => 'yes',
                'admin_label' => true,
                'save_always' => true,
            ),
            array(
                'type'        => 'dropdown',
                'heading'     => 'Display Heading Bar',
                'param_name'  => 'display_heading_bar',
                'description' => 'Show the heading bar at the top of the agenda.',
                'value'       => array(
                    'Yes' => 'yes',
                    'No'  => 'no',
                ),
                'std'         => 'yes',
                'admin_label' => true,
                'save_always' => true,
            ),
            array(
                'type'        => 'dropdown',
                'heading'     => 'Show End Time',
                'param_name'  => 'show_end_time',
                'description' => 'Display the end time for each session.',
                'value'       => array(
                    'Yes' => 'true',
                    'No'  => 'false',
                ),
                'std'         => 'false',
                'admin_label' => true,
                'save_always' => true,
            ),
            array(
                'type'        => 'dropdown',
                'heading'     => 'Time Slot Side',
                'param_name'  => 'time_slot_side',
                'description' => 'Display time slots on the side.',
                'value'       => array(
                    'Yes' => 'true',
                    'No'  => 'false',
                ),
                'std'         => 'false',
                'admin_label' => true,
                'save_always' => true,
            ),
            // Additional fields...

        ),
    ));
}


add_action('wp_ajax_save_agenda_grid', 'save_agenda_grid');
add_action('wp_ajax_nopriv_save_agenda_grid', 'save_agenda_grid');

function save_agenda_grid() {
    if (!isset($_POST['track_data']) || !is_array($_POST['track_data'])) {
        wp_send_json_error('Invalid data.');
    }

    // Save the track and date values
    update_option('mm_agenda_tracks', $_POST['track_data']);
    update_option('mm_agenda_date', $_POST['date_slug']);

    // Retrieve values to return in response
    $tracks = get_option('mm_agenda_tracks', array());
    $date   = get_option('mm_agenda_date', '');

    // Send JSON response back
    wp_send_json_success(array(
        'tracks' => $tracks,
        'date'   => $date
    ));
}

// Inline admin JS for saving agenda grid
add_action('admin_footer', function () {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('.vc_ui-button.save-agenda').on('click', function () {
                var trackValues = [];
                
                $('.vc_param_group[data-param-type="textfield"]').each(function () {
                    trackValues.push($(this).val());
                });

                $.post(ajaxurl, {
                    action: 'save_agenda_grid',
                    track_data: trackValues,
                    date_slug: dateSlug
                }, function (response) {
                    if (response.success) {
                        $('#saved-data').html('<h3>Saved Agenda</h3><p><strong>Date:</strong> ' + response.data.date + '</p><ul></ul>');
                        
                        response.data.tracks.forEach(function(track) {
                            $('#saved-data ul').append('<li>' + track + '</li>');
                        });

                        alert('Saved successfully.');
                    } else {
                        alert('Error saving data.');
                    }
                });
            });
        });

    </script>
    <?php
});





/*
$terms = get_terms(array(
    'taxonomy'   => 'track',
    'hide_empty' => false,
));

if (!is_wp_error($terms)) {
    foreach ($terms as $term) {
        echo '<p>' . esc_html($term->name) . '</p>';
    }
} else {
    echo 'Error: ' . esc_html($terms->get_error_message());
}
*/
/* This doesn't work, so commented out - f' knows why
    //Day array
    $date_args = array(
		'hide_empty' => false
	);
	$dates = get_terms( 'date', $date_args);

    //Track array
    $terms_args = array(
		'hide_empty' => false
	);
	$tracks = get_terms( 'track', $terms_args);


    // Prepare dropdown options

   //Date array
    $termsdate_args = array(
		'hide_empty' => false
	);
	$termsdate = get_terms( 'date', $termsdate_args);

		
	if ( ! empty( $termsdate ) && ! is_wp_error( $termsdate ) ){
		foreach ( $termsdate as $termdate ) {
			$speaker_group_id = $termdate->term_id;
			$speaker_group_display = $termdate->name . ' ('.$termdate->count.')'; 
		}
	}


    $get_termsdate = get_terms('date');

    $date_categories = array();
    $date_categories[' '] = 'empty';
    $date_categories['All'] = 'all';

	foreach($get_termsdate as $termdate) {   
	  $date_categories[$termdate->name] = $termdate->slug;
	}

    $track_options = array(
        array('label' => 'Select Track', 'value' => ''),
        array('label' => 'ALL Tracks', 'value' => 'all-tracks'),
    );
    if (!is_wp_error($tracks)) {
        foreach ($tracks as $track) {
            $track_options[] = array(
                'label' => $track->name,
                'value' => $track->slug,
            );
        }
    }
    */

?>