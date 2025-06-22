<?php
// Get author name by ID
function get_author_name($author_id) {
    global $conn;
    $sql = "SELECT username FROM users WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $author_id, PDO::PARAM_INT);
    $stmt->execute();
    $author = $stmt->fetch(PDO::FETCH_ASSOC);
    return $author ? $author['username'] : 'Unknown Author';
}

// Sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Upload file with validation
function upload_file($file, $target_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'gif']) {
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if file already exists
    if (file_exists($target_file)) {
        return ['success' => false, 'message' => 'File already exists.'];
    }
    
    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        return ['success' => false, 'message' => 'File is too large.'];
    }
    
    // Allow certain file formats
    if (!in_array($imageFileType, $allowed_types)) {
        return ['success' => false, 'message' => 'Only ' . implode(', ', $allowed_types) . ' files are allowed.'];
    }
    
    // Try to upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'path' => $target_file];
    } else {
        return ['success' => false, 'message' => 'Error uploading file.'];
    }
}

// Format date for display
function format_date($date_string, $format = 'F j, Y') {
    return date($format, strtotime($date_string));
}

// Get excerpt from content
function get_excerpt($content, $length = 150) {
    $excerpt = strip_tags($content);
    if (strlen($excerpt) > $length) {
        $excerpt = substr($excerpt, 0, $length) . '...';
    }
    return $excerpt;
}

/**
 * Extracts a YouTube video ID from a URL.
 * @param string|null $url The full YouTube URL.
 * @return string|null The video ID or null if not found.
 */
function get_youtube_id_from_url(?string $url): ?string
{
    if (empty($url)) {
        return null;
    }
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
    return $match[1] ?? null;
}

/**
 * Generates a YouTube embed URL from a video ID or full URL.
 * @param string|null $url The full YouTube URL or video ID.
 * @return string The embed URL or an empty string if not found.
 */
function get_youtube_embed_url(?string $url): string
{
    $video_id = get_youtube_id_from_url($url) ?? $url;
    return $video_id ? 'https://www.youtube.com/embed/' . $video_id : '';
}

function get_youtube_thumbnail_url(?string $url): string {
    $video_id = get_youtube_id_from_url($url);
    return $video_id ? 'https://i.ytimg.com/vi/' . $video_id . '/hqdefault.jpg' : '';
}
?>