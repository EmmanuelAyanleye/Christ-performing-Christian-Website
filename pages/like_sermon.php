<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$sermon_id = isset($input['id']) ? (int)$input['id'] : 0;
$action = isset($input['action']) ? $input['action'] : '';

if ($sermon_id > 0 && ($action === 'like' || $action === 'unlike')) {
    // Use a session to prevent a user from liking the same sermon multiple times in one session
    if (!isset($_SESSION['liked_sermons'])) {
        $_SESSION['liked_sermons'] = [];
    }

    $already_liked = in_array($sermon_id, $_SESSION['liked_sermons']);

    if ($action === 'like' && !$already_liked) {
        $sql = "UPDATE sermons SET likes = likes + 1 WHERE id = ?";
        $_SESSION['liked_sermons'][] = $sermon_id;
    } elseif ($action === 'unlike' && $already_liked) {
        $sql = "UPDATE sermons SET likes = GREATEST(0, likes - 1) WHERE id = ?";
        $_SESSION['liked_sermons'] = array_diff($_SESSION['liked_sermons'], [$sermon_id]);
    } else {
        // If action is invalid (e.g., liking twice), just return the current count
        $stmt = $conn->prepare("SELECT likes FROM sermons WHERE id = ?");
        $stmt->execute([$sermon_id]);
        echo json_encode(['success' => true, 'likes' => (int)$stmt->fetchColumn()]);
        exit;
    }

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$sermon_id]);

        // Get the new like count to return to the frontend
        $count_stmt = $conn->prepare("SELECT likes FROM sermons WHERE id = ?");
        $count_stmt->execute([$sermon_id]);
        $new_likes = (int)$count_stmt->fetchColumn();

        echo json_encode(['success' => true, 'likes' => $new_likes]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}