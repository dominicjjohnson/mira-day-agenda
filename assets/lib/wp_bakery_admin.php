<?php

// Helper function to get date taxonomy terms for dropdown
function get_date_taxonomy_options() {
    $options = array('Select Date' => '');
    
    // Check if taxonomy exists first
    if (!taxonomy_exists('date')) {
        $options['No dates available'] = '';
        return $options;
    }
    
    $terms = get_terms(array(
        'taxonomy' => 'date',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC'
    ));
    
    if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $term) {
            $options[$term->name] = $term->slug;
        }
    } else {
        $options['No dates available'] = '';
    }
    
    return $options;
}

// Helper function to get track taxonomy terms for dropdown
function get_track_taxonomy_options() {
    $options = array('Select Track' => '');
    
    // Check if taxonomy exists first
    if (!taxonomy_exists('track')) {
        $options['No tracks available'] = '';
        return $options;
    }
    
    $terms = get_terms(array(
        'taxonomy' => 'track',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC'
    ));
    
    if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $term) {
            $options[$term->name] = $term->slug;
        }
    } else {
        $options['No tracks available'] = '';
    }
    
    return $options;
}

// Helper function to get all tracks options including special "All Tracks" option
function get_all_tracks_options() {
    $options = array('Select All Tracks Option' => '');
    
    // Add a default "All Tracks" option
    $options['All Tracks'] = 'all-tracks';
    
    // Check if taxonomy exists first
    if (!taxonomy_exists('track')) {
        return $options;
    }
    
    // Add existing track terms as potential "all tracks" options
    $terms = get_terms(array(
        'taxonomy' => 'track',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC'
    ));
    
    if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $term) {
            $options['All ' . $term->name] = $term->slug;
        }
    }
    
    return $options;
}

// Force WP Bakery to reload element by removing and re-registering
if (function_exists('vc_remove_element')) {
    vc_remove_element('agenda-grid');
}

// Register WP Bakery element after taxonomies are loaded
add_action('vc_before_init', function() {
    error_log('WP Bakery: vc_before_init hook fired');
    error_log('WP Bakery: vc_map function exists: ' . (function_exists('vc_map') ? 'YES' : 'NO'));
    error_log('WP Bakery: user can edit posts: ' . (current_user_can('edit_posts') ? 'YES' : 'NO'));
    
    if (function_exists('vc_map') && current_user_can('edit_posts')) {
        error_log('WP Bakery: Using vc_before_init hook to register SIMPLE Agenda Grid');
        
        // Simple version that should definitely work
        vc_map(array(
            'name'     => 'Agenda Grid',
            'base'     => 'agenda-grid',
            'category' => 'Content',
            'icon'     => 'icon-wpb-ui-separator',
            'description' => 'Display agenda for a conference date',
            'params'   => array(
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Conference Date',
                    'param_name'  => 'day',
                    'description' => 'Select the conference date to display.',
                    'value'       => get_date_taxonomy_options(),
                    'std'         => '2025-10-01',
                    'admin_label' => true,
                    'save_always' => true,
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'All Tracks Option',
                    'param_name'  => 'all-tracks',
                    'description' => 'Select the "All Tracks" option or a specific track for the all-tracks display.',
                    'value'       => get_all_tracks_options(),
                    'admin_label' => true,
                    'group'       => 'Tracks',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Track 1',
                    'param_name'  => 'track1',
                    'description' => 'Select Track 1 to display.',
                    'value'       => get_track_taxonomy_options(),
                    'admin_label' => true,
                    'group'       => 'Tracks',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Track 2',
                    'param_name'  => 'track2',
                    'description' => 'Select Track 2 to display.',
                    'value'       => get_track_taxonomy_options(),
                    'admin_label' => true,
                    'group'       => 'Tracks',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Track 3',
                    'param_name'  => 'track3',
                    'description' => 'Select Track 3 to display.',
                    'value'       => get_track_taxonomy_options(),
                    'admin_label' => true,
                    'group'       => 'Tracks',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Track 4',
                    'param_name'  => 'track4',
                    'description' => 'Select Track 4 to display.',
                    'value'       => get_track_taxonomy_options(),
                    'admin_label' => true,
                    'group'       => 'Tracks',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Track 5',
                    'param_name'  => 'track5',
                    'description' => 'Select Track 5 to display.',
                    'value'       => get_track_taxonomy_options(),
                    'admin_label' => true,
                    'group'       => 'Tracks',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Track 6',
                    'param_name'  => 'track6',
                    'description' => 'Select Track 6 to display.',
                    'value'       => get_track_taxonomy_options(),
                    'admin_label' => true,
                    'group'       => 'Tracks',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Track 7',
                    'param_name'  => 'track7',
                    'description' => 'Select Track 7 to display.',
                    'value'       => get_track_taxonomy_options(),
                    'admin_label' => true,
                    'group'       => 'Tracks',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Track 8',
                    'param_name'  => 'track8',
                    'description' => 'Select Track 8 to display.',
                    'value'       => get_track_taxonomy_options(),
                    'admin_label' => true,
                    'group'       => 'Tracks',
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
                    'group'       => 'Display Options',
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
                    'group'       => 'Display Options',
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
                    'group'       => 'Display Options',
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
                    'group'       => 'Display Options',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Display Seminar Type',
                    'param_name'  => 'display_seminar_type',
                    'description' => 'Show seminar types (taxonomy "type") in the agenda.',
                    'value'       => array(
                        'Yes' => 'yes',
                        'No'  => 'no',
                    ),
                    'std'         => 'no',
                    'admin_label' => true,
                    'save_always' => true,
                    'group'       => 'Display Options',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Display Seminar Duration',
                    'param_name'  => 'display_seminar_duration',
                    'description' => 'Show session duration/time in the agenda.',
                    'value'       => array(
                        'Yes' => 'yes',
                        'No'  => 'no',
                    ),
                    'std'         => 'no',
                    'admin_label' => true,
                    'save_always' => true,
                    'group'       => 'Display Options',
                ),
            ),
        ));
        
        error_log('WP Bakery: MM Agenda Grid element successfully registered');
        
        // Debug: Check WP Bakery version
        if (defined('WPB_VC_VERSION')) {
            error_log('WP Bakery Version: ' . WPB_VC_VERSION);
        }
        
        // Test immediate retrieval
        if (function_exists('vc_get_all_shared_templates')) {
            error_log('WP Bakery: vc_get_all_shared_templates available');
        }
        
        // Debug: List all registered elements after a delay
        wp_schedule_single_event(time() + 2, 'check_vc_elements');
        
        // Debug: List all registered elements
        if (function_exists('vc_map_get_all')) {
            $all_elements = vc_map_get_all();
            if (isset($all_elements['agenda-grid'])) {
                error_log('WP Bakery: agenda-grid found in registered elements');
            } else {
                error_log('WP Bakery: agenda-grid NOT found in registered elements');
                error_log('WP Bakery: Available elements count: ' . count($all_elements));
                $element_names = array_keys($all_elements);
                error_log('WP Bakery: First 10 elements: ' . implode(', ', array_slice($element_names, 0, 10)));
            }
        }
    } else {
        error_log('WP Bakery: vc_map function not available or user cannot edit posts');
    }
}, 20); // Priority 20 to ensure taxonomies are registered first

// Alternative registration hook for newer WP Bakery versions
add_action('init', function() {
    if (function_exists('vc_map') && !did_action('vc_before_init')) {
        error_log('WP Bakery: Using init hook as fallback for MM Agenda Grid registration');
        // Same registration code as above...
        // We'll keep this simple for now and just log that we tried
    }
}, 25);

// Try a more direct approach - register on init with higher priority
add_action('init', function() {
    if (function_exists('vc_map')) {
        error_log('WP Bakery: Attempting direct registration on init hook');
        vc_map(array(
            'name'     => 'TEST Agenda Grid',
            'base'     => 'test-agenda-grid',
            'category' => 'Content',
            'icon'     => 'icon-wpb-ui-separator',
            'description' => 'Test agenda element',
            'params'   => array(
                array(
                    'type'        => 'textfield',
                    'heading'     => 'Test Parameter',
                    'param_name'  => 'test_param',
                    'value'       => 'test',
                ),
            ),
        ));
        error_log('WP Bakery: TEST element registered on init');
    }
}, 15);

// Also try after plugins loaded
add_action('plugins_loaded', function() {
    if (function_exists('vc_map')) {
        error_log('WP Bakery: vc_map available after plugins_loaded');
    } else {
        error_log('WP Bakery: vc_map NOT available after plugins_loaded');
    }
}, 30);

// Scheduled event to check WP Bakery elements after full load
add_action('check_vc_elements', function() {
    if (function_exists('vc_map_get_all')) {
        $all_elements = vc_map_get_all();
        if (isset($all_elements['agenda-grid'])) {
            error_log('WP Bakery DELAYED CHECK: agenda-grid found in registered elements');
        } else {
            error_log('WP Bakery DELAYED CHECK: agenda-grid NOT found');
            error_log('WP Bakery DELAYED CHECK: Total elements: ' . count($all_elements));
        }
    }
});

// Also check immediately after WP Bakery admin init
add_action('vc_admin_init', function() {
    error_log('WP Bakery: vc_admin_init hook fired');
    if (function_exists('vc_map_get_all')) {
        $all_elements = vc_map_get_all();
        if (isset($all_elements['agenda-grid'])) {
            error_log('WP Bakery ADMIN INIT: agenda-grid found');
        } else {
            error_log('WP Bakery ADMIN INIT: agenda-grid NOT found');
        }
    }
});

// WPBakery element for Display My Diary
if (function_exists('vc_map')) {
    vc_map(array(
        'name'     => 'Display My Diary',
        'base'     => 'display-my-diary',
        'category' => 'Content',
        'params'   => array(
            array(
                'type'        => 'dropdown',
                'heading'     => 'Layout Style',
                'param_name'  => 'layout',
                'description' => 'Choose how to display the diary entries.',
                'value'       => array(
                    'Grid'    => 'grid',
                    'List'    => 'list',
                    'Compact' => 'compact',
                ),
                'std'         => 'list',
                'admin_label' => true,
                'save_always' => true,
            ),
            array(
                'type'        => 'textfield',
                'heading'     => 'Empty Message Text',
                'param_name'  => 'empty_message',
                'description' => 'Custom message when diary is empty (leave blank for default).',
                'admin_label' => false,
                'maxlength'   => 100,
            ),
            array(
                'type'        => 'dropdown',
                'heading'     => 'Show Session Details',
                'param_name'  => 'show_details',
                'description' => 'Include session descriptions and speaker information.',
                'value'       => array(
                    'Yes' => 'yes',
                    'No'  => 'no',
                ),
                'std'         => 'yes',
                'admin_label' => true,
                'save_always' => true,
            ),
        ),
    ));
}

?>
