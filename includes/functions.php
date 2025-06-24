<?php
// This file contains general utility functions used across the website.

/**
 * Sanitizes input data to prevent XSS and other vulnerabilities.
 * @param mixed $data The input data to sanitize.
 * @return mixed The sanitized data.
 */
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
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

?>