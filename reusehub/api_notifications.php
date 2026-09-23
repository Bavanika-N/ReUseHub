<?php
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['error' => 'unauthenticated']);
    exit;
}

if (isset($_GET['mark_read'])) {
    mark_notifications_read($conn, current_user_id());
    echo json_encode(['ok' => true]);
    exit;
}

$rows = get_notifications($conn, current_user_id(), 15);
foreach ($rows as &$r) {
    $r['time_ago'] = time_ago($r['created_at']);
    $r['message'] = e($r['message']);
}
echo json_encode(['notifications' => $rows]);
