<?php
/**
 * Mira Day Agenda: Sponsors to Seminars Relationship
 *
 * This file registers a post-to-post relationship between sponsors (from cw-plugin-sponsors)
 * and seminars (from cw-plugin-seminars) using the Posts 2 Posts plugin (or similar).
 * The relationship is editable from the admin screens for sponsors.
 *
 * Place this file in the mira-day-agenda plugin directory.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// Register the P2P relationship on init
add_action( 'p2p_init', function() {
    if ( ! function_exists( 'p2p_register_connection_type' ) ) {
        return; // Posts 2 Posts not active
    }

    p2p_register_connection_type( array(
        'name' => 'sponsors_to_seminars',
        'from' => 'sponsors', // post type from cw-plugin-sponsors
        'to'   => 'seminars', // post type from cw-plugin-seminars
        'admin_box' => array(
            'show' => 'to', // Edit relationship from seminar admin screen
            'context' => 'side',
        ),
        'title' => array(
            'from' => __( 'Related Seminars', 'mira-day-agenda' ),
            'to'   => __( 'Related Sponsors', 'mira-day-agenda' ),
        ),
        'sortable' => 'any',
    ) );
});

// Display sponsor IDs in the seminars edit screen

// Add a meta box to allow adding/editing sponsor_id in seminar edit screen
add_action('add_meta_boxes', function() {
    global $post;
    if ($post && $post->post_type === 'seminars') {
        add_meta_box(
            'mira_sponsor_id_edit_box',
            __('Sponsor ID', 'mira-day-agenda'),
            function($post) {
                $sponsor_id = get_post_meta($post->ID, 'sponsor_id', true);
                echo '<label for="mira_sponsor_id_field">Sponsor ID:</label>';
                echo '<input type="text" id="mira_sponsor_id_field" name="mira_sponsor_id_field" value="' . esc_attr($sponsor_id) . '" style="width:100%;font-size:1.2em;">';
                echo '<p style="margin:5px 0 0 0;font-size:11px;color:#666;">Enter the Sponsor ID to associate with this seminar. This will be saved as a custom field.</p>';
            },
            'seminars',
            'side',
            'high'
        );
    }
});

// Save sponsor_id when seminar is saved
add_action('save_post_seminars', function($post_id) {
    if (isset($_POST['mira_sponsor_id_field'])) {
        update_post_meta($post_id, 'sponsor_id', sanitize_text_field($_POST['mira_sponsor_id_field']));
    }
});

// Helper: Get seminars for a sponsor
function mira_get_sponsor_seminars( $sponsor_id ) {
    if ( ! function_exists( 'p2p_get_connected' ) ) return array();
    $connected = p2p_get_connected( 'sponsors_to_seminars', $sponsor_id );
    $seminars = array();
    if ( $connected->have_posts() ) {
        while ( $connected->have_posts() ) {
            $connected->the_post();
            $seminars[] = get_post();
        }
        wp_reset_postdata();
    }
    return $seminars;
}

// Helper: Get sponsors for a seminar
function mira_get_seminar_sponsors( $seminar_id ) {
    if ( ! function_exists( 'p2p_get_connected' ) ) return array();
    $connected = p2p_get_connected( 'sponsors_to_seminars', $seminar_id );
    $sponsors = array();
    if ( $connected->have_posts() ) {
        while ( $connected->have_posts() ) {
            $connected->the_post();
            $sponsors[] = get_post();
        }
        wp_reset_postdata();
    }
    return $sponsors;
}
