<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sermon_id = isset($_POST['sermon_id']) ? (int)$_POST['sermon_id'] : 0;
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $content = sanitize_input($_POST['content'] ?? '');

    if ($sermon_id > 0 && !empty($name) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($content)) {
        try {
            $sql = "INSERT INTO sermon_comments (sermon_id, name, email, content, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$sermon_id, $name, $email, $content]);
            echo json_encode(['success' => true, 'message' => 'Thank you for your comment! It has been submitted for review.']);
        } catch (PDOException $e) {
            error_log("Comment submission error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'A database error occurred. Please try again.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Please fill out all fields correctly.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}