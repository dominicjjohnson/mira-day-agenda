<?php


function get_unique_start_and_end_times($data, $time_slots) {
	
	$start_time = get_post_meta($data->ID, 'time_start', true);
	if (!in_array($start_time, $time_slots)) {
		$time_slots[] = $start_time;
	}

	$end_time = get_post_meta($data->ID, 'time_end', true);
	if (!in_array($end_time, $time_slots)) {
		$time_slots[] = $end_time;
	}

	return $time_slots;
}

// Get the time slots for the agenda

