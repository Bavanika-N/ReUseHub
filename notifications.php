<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
mark_notifications_read($conn, current_user_id());
$notifs = get_notifications($conn, current_user_id(), 100);

$page_title = "Notifications";
include __DIR__ . '/includes/header.php';
?>

<h1 style="font-size:1.6rem;">Notifications</h1>
<p>Everything relevant to your items and requests.</p>

<?php if (empty($notifs)): ?>
  <div class="empty-state"><div class="icon">🔔</div><p>You're all caught up!</p></div>
<?php else: ?>
  <div class="card" style="padding:0;">
    <?php foreach ($notifs as $n): ?>
      <div class="notif-item" style="padding:16px 20px;">
        <strong style="font-size:.75rem; color:var(--accent2); text-transform:uppercase;"><?= e($n['type']) ?></strong>
        <div><?= e($n['message']) ?></div>
        <span class="notif-time"><?= time_ago($n['created_at']) ?></span>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
