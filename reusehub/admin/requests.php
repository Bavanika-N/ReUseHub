<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$in_admin = true;

$requests = $conn->query("
  SELECT r.*, i.item_name, u.name AS requester_name
  FROM requests r
  JOIN items i ON r.item_id = i.item_id
  JOIN users u ON r.requester_id = u.user_id
  ORDER BY r.request_date DESC");

$page_title = "All Requests";
include __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
  <?php include __DIR__ . '/_sidebar.php'; ?>

  <div>
    <h1 style="font-size:1.6rem;">All Requests</h1>
    <p>Every item request raised on the platform.</p>

    <div class="card" style="padding:0;">
      <div class="table-wrap">
        <table>
          <tr><th>Item</th><th>Requested By</th><th>Status</th><th>Date</th></tr>
          <?php while ($r = $requests->fetch_assoc()): ?>
            <tr>
              <td><?= e($r['item_name']) ?></td>
              <td><?= e($r['requester_name']) ?></td>
              <td><span class="status-pill status-<?= $r['request_status'] ?>"><?= $r['request_status'] ?></span></td>
              <td><?= time_ago($r['request_date']) ?></td>
            </tr>
          <?php endwhile; ?>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
