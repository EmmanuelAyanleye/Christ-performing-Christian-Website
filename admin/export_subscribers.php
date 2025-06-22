<?php
// Ensure clean output
while (ob_get_level()) {
    ob_end_clean();
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/partials/session_auth.php';

// Fetch subscriber data
$stmt = $pdo->query("SELECT email, name, subscribed_at, is_active FROM subscribers ORDER BY subscribed_at DESC");
$subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare download
$filename = 'subscribers_' . date('Ymd_His') . '.csv';

// Set headers
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open output stream
$output = fopen('php://output', 'w');

// Write headers
fputcsv($output, ['Email', 'Name', 'Subscribed At', 'Status']);

// Write data
foreach ($subscribers as $subscriber) {
    fputcsv($output, [
        $subscriber['email'],
        $subscriber['name'],
        date('Y-m-d H:i:s', strtotime($subscriber['subscribed_at'])),
        $subscriber['is_active'] ? 'Active' : 'Unsubscribed'
    ]);
}

fclose($output);
exit;