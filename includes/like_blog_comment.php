<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

// Ensure session is started (it should be by config.php, but good to be explicit)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;

if ($comment_id > 0) {
    // Initialize liked comments array in session if it doesn't exist
    if (!isset($_SESSION['liked_blog_comments'])) {
        $_SESSION['liked_blog_comments'] = [];
    }

    // Check if the user (session) has already liked this comment
    if (in_array($comment_id, $_SESSION['liked_blog_comments'])) {
        // Already liked, do nothing or return current count
        $stmt = $conn->prepare("SELECT likes FROM blog_comments WHERE id = ?");
        $stmt->execute([$comment_id]);
        $current_likes = (int)$stmt->fetchColumn();
        echo json_encode(['success' => true, 'message' => 'You have already liked this comment.', 'like_count' => $current_likes]);
        exit;
    }

    try {
        // Increment like count in the database
        $sql = "UPDATE blog_comments SET likes = likes + 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$comment_id]);

        // Add comment ID to session to prevent future likes from this user
        $_SESSION['liked_blog_comments'][] = $comment_id;

        // Get the new like count to return to the frontend
        $count_stmt = $conn->prepare("SELECT likes FROM blog_comments WHERE id = ?");
        $count_stmt->execute([$comment_id]);
        $new_likes = (int)$count_stmt->fetchColumn();

        echo json_encode(['success' => true, 'message' => 'Comment liked!', 'like_count' => $new_likes]);
    } catch (PDOException $e) {
        error_log("Blog comment like error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid comment ID.']);
}