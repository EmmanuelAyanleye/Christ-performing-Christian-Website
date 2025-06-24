<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$sermon_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : ''; // 'like' or 'dislike'

if ($sermon_id <= 0 || !in_array($action, ['like', 'dislike'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

// Initialize session tracking for sermon likes/dislikes
if (!isset($_SESSION['sermon_interactions'])) {
    $_SESSION['sermon_interactions'] = []; // Stores [sermon_id => 'liked'/'disliked']
}

$current_interaction = $_SESSION['sermon_interactions'][$sermon_id] ?? null;

try {
    // Fetch current likes and dislikes
    $stmt = $pdo->prepare("SELECT likes, dislikes FROM sermons WHERE id = ?");
    $stmt->execute([$sermon_id]);
    $sermon_counts = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sermon_counts) {
        echo json_encode(['success' => false, 'message' => 'Sermon not found.']);
        exit;
    }

    $likes = (int)$sermon_counts['likes'];
    $dislikes = (int)$sermon_counts['dislikes'];

    $update_sql = "";

    if ($action === 'like') {
        if ($current_interaction === 'liked') {
            // Already liked, do nothing
        } elseif ($current_interaction === 'disliked') {
            // Was disliked, now liking: decrement dislikes, increment likes
            $update_sql = "UPDATE sermons SET likes = likes + 1, dislikes = GREATEST(0, dislikes - 1) WHERE id = ?";
            $_SESSION['sermon_interactions'][$sermon_id] = 'liked';
            $likes++;
            $dislikes = max(0, $dislikes - 1);
        } else {
            // Not interacted yet: increment likes
            $update_sql = "UPDATE sermons SET likes = likes + 1 WHERE id = ?";
            $_SESSION['sermon_interactions'][$sermon_id] = 'liked';
            $likes++;
        }
    } elseif ($action === 'dislike') {
        if ($current_interaction === 'disliked') {
            // Already disliked, do nothing
        } elseif ($current_interaction === 'liked') {
            // Was liked, now disliking: decrement likes, increment dislikes
            $update_sql = "UPDATE sermons SET dislikes = dislikes + 1, likes = GREATEST(0, likes - 1) WHERE id = ?";
            $_SESSION['sermon_interactions'][$sermon_id] = 'disliked';
            $dislikes++;
            $likes = max(0, $likes - 1);
        } else {
            // Not interacted yet: increment dislikes
            $update_sql = "UPDATE sermons SET dislikes = dislikes + 1 WHERE id = ?";
            $_SESSION['sermon_interactions'][$sermon_id] = 'disliked';
            $dislikes++;
        }
    }

    if (!empty($update_sql)) {
        $stmt = $pdo->prepare($update_sql);
        $stmt->execute([$sermon_id]);
    }

    echo json_encode([
        'success' => true,
        'newLikeCount' => $likes,
        'newDislikeCount' => $dislikes,
        'userInteraction' => $_SESSION['sermon_interactions'][$sermon_id] ?? null
    ]);

} catch (PDOException $e) {
    error_log("Sermon like/dislike error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
}
?>