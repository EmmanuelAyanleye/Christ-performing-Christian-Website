<?php
// This file contains general utility functions used across the website.

/**
 * Sanitizes input data to prevent XSS and other vulnerabilities.
 * @param mixed $data The input data to sanitize.
 * @return mixed The sanitized data.
 */
function sanitize_input($data) {
    return strip_tags(trim($data));
}

/**
 * Formats a date string into a more readable format.
 * @param string $date_string The date string to format (e.g., from database).
 * @param string $format The desired date format (e.g., 'M j, Y').
 * @return string The formatted date.
 */
function format_date($date_string, $format = 'M j, Y') {
    return date($format, strtotime($date_string));
}

// Function to extract YouTube video ID from a URL
function get_youtube_id($url) {
    preg_match("/(?:youtube\.com.*(?:\\?|&)v=|youtu\.be\/)([^&]+)/", $url, $matches);
    return $matches[1] ?? '';
}

// Function to embed a YouTube video URL
function embedYouTubeUrl($url) {
    $id = get_youtube_id($url);
    return "https://www.youtube.com/embed/" . $id;
}

/**
 * Creates a short excerpt from a string.
 * @param string $text The input string.
 * @param int $length The maximum length of the excerpt.
 * @return string The truncated string with an ellipsis.
 */
function get_excerpt($text, $length = 100) {
    $text = strip_tags($text);
    if (mb_strlen($text) > $length) {
        $text = mb_substr($text, 0, $length);
        $text = mb_substr($text, 0, mb_strrpos($text, ' ')); // Cut to the last word to avoid breaking words
        $text .= '...';
    }
    return $text;
}

/**
 * Calculates the next occurrence of a recurring event.
 * If the event's start date is in the future, it returns that date.
 * If it's in the past, it calculates the next future occurrence based on the rule.
 *
 * @param string $start_date_str The initial start date of the event.
 * @param string $recurrence The recurrence rule ('none', 'weekly', 'monthly').
 * @return string The next upcoming date in 'Y-m-d H:i:s' format.
 */
function calculate_next_occurrence($start_date_str, $recurrence) {
    if ($recurrence === 'none' || $recurrence === null || empty($recurrence)) {
        return $start_date_str;
    }

    $now = new DateTime();
    $next_occurrence = new DateTime($start_date_str);

    // If the original start date is still in the future, that's the next one.
    if ($next_occurrence > $now) {
        return $next_occurrence->format('Y-m-d H:i:s');
    }

    // If the original start date has passed, calculate the next one.
    $interval = ($recurrence === 'weekly') ? '+1 week' : '+1 month';
    while ($next_occurrence <= $now) {
        $next_occurrence->modify($interval);
    }

    return $next_occurrence->format('Y-m-d H:i:s');
}
?>