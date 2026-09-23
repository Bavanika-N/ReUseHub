<?php
require_once __DIR__ . '/auth.php';

/* ---------- Notifications (FR-15 to FR-20 / CR-001) ---------- */

function create_notification($conn, $recipient_id, $type, $related_id, $message) {
    $stmt = $conn->prepare("INSERT INTO notifications (recipient_id, type, related_id, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $recipient_id, $type, $related_id, $message);
    $stmt->execute();
    $stmt->close();
}

function get_unread_count($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM notifications WHERE recipient_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)$res['c'];
}

function get_notifications($conn, $user_id, $limit = 30) {
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE recipient_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    $stmt->close();
    return $rows;
}

function mark_notifications_read($conn, $user_id) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE recipient_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

/* Notify all users except one (used for "new item posted") - FR-19 */
function notify_all_users_except($conn, $exclude_user_id, $type, $related_id, $message) {
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id != ? AND role = 'user'");
    $stmt->bind_param("i", $exclude_user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        create_notification($conn, $row['user_id'], $type, $related_id, $message);
    }
    $stmt->close();
}

/* ---------- Items ---------- */

function get_item($conn, $item_id) {
    $stmt = $conn->prepare("SELECT items.*, users.name AS owner_name FROM items JOIN users ON items.owner_id = users.user_id WHERE item_id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row;
}

/* ---------- Item Images (multiple photos per item) ---------- */

function add_item_image($conn, $item_id, $image) {
    $stmt = $conn->prepare("INSERT INTO item_images (item_id, image) VALUES (?, ?)");
    if (!$stmt) return false;
    $stmt->bind_param("is", $item_id, $image);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function get_item_images($conn, $item_id) {
    $stmt = $conn->prepare("SELECT * FROM item_images WHERE item_id = ? ORDER BY image_id ASC");
    if (!$stmt) return [];
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) $rows[] = $r;
    }
    $stmt->close();
    return $rows;
}

function delete_item_image($conn, $image_id, $item_id) {
    $stmt = $conn->prepare("SELECT image FROM item_images WHERE image_id = ? AND item_id = ?");
    if (!$stmt) return;
    $stmt->bind_param("ii", $image_id, $item_id);
    $stmt->execute();
    $row = $stmt->get_result() ? $stmt->get_result()->fetch_assoc() : null;
    $stmt->close();
    if ($row) {
        $path = __DIR__ . '/../assets/uploads/' . $row['image'];
        if (file_exists($path)) @unlink($path);
        $del = $conn->prepare("DELETE FROM item_images WHERE image_id = ? AND item_id = ?");
        if ($del) {
            $del->bind_param("ii", $image_id, $item_id);
            $del->execute();
            $del->close();
        }
    }
}

/* Save an uploaded image file to /assets/uploads and return its filename, or null */
function save_uploaded_image($file) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif', 'bmp', 'svg'];
    if (!in_array($ext, $allowed)) return null;
    $uploadDir = __DIR__ . '/../assets/uploads/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }
    $name = uniqid('item_') . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $name)) {
        return $name;
    }
    return null;
}

/* ---------- Misc ---------- */

function time_ago($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return "just now";
    if ($diff < 3600) return floor($diff / 60) . "m ago";
    if ($diff < 86400) return floor($diff / 3600) . "h ago";
    return floor($diff / 86400) . "d ago";
}
