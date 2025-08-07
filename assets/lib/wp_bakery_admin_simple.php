<?php
/**
 * WP Bakery Page Builder Elements for Mira Day Agenda
 * 
 * This file registers the following WP Bakery elements:
 * 1. Agenda Grid - [agenda-grid] shortcode with visual editor
 * 2. My Diary - [display-my-diary] shortcode with visual editor
 * 
 * Simple, clean implementation
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Function to get date taxonomy options
function get_date_taxonomy_options() {
    $options = array('Select Date' => '');
    
    $terms = get_terms(array(
        'taxonomy' => 'date',
        'hide_empty' => false,
    ));
    
    if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $term) {
            $options[$term->name] = $term->slug;
        }
    }
    
    return $options;
}

// Function to get track taxonomy options
function get_track_taxonomy_options() {
    $options = array('Select Track' => '', 'All Tracks' => 'allcolumns');
    
    $terms = get_terms(array(
        'taxonomy' => 'track',
        'hide_empty' => false,
    ));
    
    if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $term) {
            $options[$term->name] = $term->slug;
        }
    }
    
    return $options;
}

// Register WP Bakery element
add_action('vc_before_init', function() {
    if (function_exists('vc_map') && current_user_can('edit_posts')) {
        
        $date_options = get_date_taxonomy_options();
        $track_options = get_track_taxonomy_options();
        
        vc_map(array(
            'name'     => 'Agenda Grid',
            'base'     => 'agenda-grid',
            'category' => 'Content',
            'icon'     => 'icon-wpb-ui-separator',
            'description' => 'Display conference agenda for a specific date',
            'params'   => array(
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Conference Date',
                    'param_name'  => 'day',
                    'description' => 'Select the conference date to display',
                    'value'       => $date_options,
                    'admin_label' => true,
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Track Selection',
                    'param_name'  => 'all-tracks',
                    'description' => 'Choose which tracks to display',
                    'value'       => $track_options,
                    'std'         => '',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Track 1',
                    'param_name'  => 'track1',
                    'value'       => $track_options,
                    'std'         => '',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Track 2',
                    'param_name'  => 'track2',
                    'value'       => $track_options,
                    'std'         => '',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Track 3',
                    'param_name'  => 'track3',
                    'value'       => $track_options,
                    'std'         => '',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Track 4',
                    'param_name'  => 'track4',
                    'value'       => $track_options,
                    'std'         => '',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Track 5',
                    'param_name'  => 'track5',
                    'value'       => $track_options,
                    'std'         => '',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Track 6',
                    'param_name'  => 'track6',
                    'value'       => $track_options,
                    'std'         => '',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Track 7',
                    'param_name'  => 'track7',
                    'value'       => $track_options,
                    'std'         => '',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Track 8',
                    'param_name'  => 'track8',
                    'value'       => $track_options,
                    'std'         => '',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Show Border',
                    'param_name'  => 'border',
                    'value'       => array('Yes' => 'yes', 'No' => 'no'),
                    'std'         => 'yes',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Show Heading Bar',
                    'param_name'  => 'display_heading_bar',
                    'value'       => array('Yes' => 'yes', 'No' => 'no'),
                    'std'         => 'yes',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Show End Times',
                    'param_name'  => 'show_end_time',
                    'value'       => array('Yes' => 'true', 'No' => 'false'),
                    'std'         => 'false',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Time Slots on Side',
                    'param_name'  => 'time_slot_side',
                    'value'       => array('Yes' => 'true', 'No' => 'false'),
                    'std'         => 'true',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Show Session Types',
                    'param_name'  => 'display_seminar_type',
                    'value'       => array('Yes' => 'yes', 'No' => 'no'),
                    'std'         => 'no',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Show Session Duration',
                    'param_name'  => 'display_seminar_duration',
                    'value'       => array('Yes' => 'yes', 'No' => 'no'),
                    'std'         => 'no',
                ),
            ),
        ));

        // Register My Diary element
        vc_map(array(
            'name'     => 'My Diary',
            'base'     => 'display-my-diary',
            'category' => 'Content',
            'icon'     => 'icon-wpb-ui-accordion',
            'description' => 'Display user\'s personal agenda diary',
            'params'   => array(
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Display Style',
                    'param_name'  => 'style',
                    'description' => 'How to display diary items',
                    'value'       => array('Grid' => 'grid', 'List' => 'list'),
                    'std'         => 'grid',
                    'admin_label' => true,
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Show Empty Message',
                    'param_name'  => 'show_empty_message',
                    'description' => 'Display message when diary is empty',
                    'value'       => array('Yes' => 'yes', 'No' => 'no'),
                    'std'         => 'yes',
                ),
                array(
                    'type'        => 'textfield',
                    'heading'     => 'Custom Empty Message',
                    'param_name'  => 'empty_message',
                    'description' => 'Custom text to show when diary is empty (optional)',
                    'value'       => 'Your diary is empty. Add sessions from the agenda to see them here.',
                    'dependency'  => array(
                        'element' => 'show_empty_message',
                        'value'   => array('yes'),
                    ),
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Show Session Details',
                    'param_name'  => 'show_details',
                    'description' => 'Display session descriptions and details',
                    'value'       => array('Yes' => 'yes', 'No' => 'no'),
                    'std'         => 'yes',
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Show Remove Buttons',
                    'param_name'  => 'show_remove_buttons',
                    'description' => 'Allow users to remove items from diary',
                    'value'       => array('Yes' => 'yes', 'No' => 'no'),
                    'std'         => 'yes',
                ),
            ),
        ));
    }
});

// Filter to ensure proper defaults for blank parameters
add_filter('shortcode_atts_agenda-grid', function($out, $pairs, $atts) {
    // Ensure display_seminar_type defaults to 'no' when blank or not set
    if (empty($out['display_seminar_type']) || $out['display_seminar_type'] === '') {
        $out['display_seminar_type'] = 'no';
    }
    
    // Ensure display_seminar_duration defaults to 'no' when blank or not set
    if (empty($out['display_seminar_duration']) || $out['display_seminar_duration'] === '') {
        $out['display_seminar_duration'] = 'no';
    }
    
    return $out;
}, 10, 3);

// Filter to ensure proper defaults for My Diary shortcode
add_filter('shortcode_atts_display-my-diary', function($out, $pairs, $atts) {
    // Ensure style defaults to 'grid' when blank or not set
    if (empty($out['style']) || $out['style'] === '') {
        $out['style'] = 'grid';
    }
    
    // Ensure show_empty_message defaults to 'yes' when blank or not set
    if (empty($out['show_empty_message']) || $out['show_empty_message'] === '') {
        $out['show_empty_message'] = 'yes';
    }
    
    // Ensure show_details defaults to 'yes' when blank or not set
    if (empty($out['show_details']) || $out['show_details'] === '') {
        $out['show_details'] = 'yes';
    }
    
    // Ensure show_remove_buttons defaults to 'yes' when blank or not set
    if (empty($out['show_remove_buttons']) || $out['show_remove_buttons'] === '') {
        $out['show_remove_buttons'] = 'yes';
    }
    
    // Set default empty message if not provided
    if (empty($out['empty_message']) || $out['empty_message'] === '') {
        $out['empty_message'] = 'Your diary is empty. Add sessions from the agenda to see them here.';
    }
    
    return $out;
}, 10, 3);