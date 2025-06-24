<?php
require_once __DIR__ . '/config.php'; // Assuming config.php is in the same directory or accessible

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    // parent_id will be null for top-level comments, or an integer for replies
    $parent_id = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $content = sanitize_input($_POST['content'] ?? '');

    // Basic validation
    if ($post_id > 0 && !empty($name) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($content)) {
        try {
            // user_id is set to NULL as comments are currently anonymous.
            // If you implement user authentication, you would fetch $_SESSION['user_id'] here.
            $user_id = null; 

            $sql = "INSERT INTO blog_comments (post_id, parent_id, name, email, content, status, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$post_id, $parent_id, $name, $email, $content, 'approved', $user_id]); // Changed 'pending' to 'approved'

            echo json_encode(['success' => true, 'message' => 'Your comment has been published!']);
        } catch (PDOException $e) {
            error_log("Blog comment submission error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'A database error occurred. Please try again.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Please fill out all fields correctly.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}