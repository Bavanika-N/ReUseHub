<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$uid = current_user_id();

// FR-11: Accept / Reject a request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $rid = (int)$_POST['request_id'];
    $decision = $_POST['decision'] === 'Accepted' ? 'Accepted' : 'Rejected';

    $stmt = $conn->prepare("SELECT r.*, i.owner_id, i.item_name FROM requests r JOIN items i ON r.item_id = i.item_id WHERE r.request_id = ?");
    $stmt->bind_param("i", $rid);
    $stmt->execute();
    $req = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($req && $req['owner_id'] == $uid) {
        $upd = $conn->prepare("UPDATE requests SET request_status = ? WHERE request_id = ?");
        $upd->bind_param("si", $decision, $rid);
        $upd->execute();
        $upd->close();

        if ($decision === 'Accepted') {
            $updItem = $conn->prepare("UPDATE items SET status = 'Requested' WHERE item_id = ?");
            $updItem->bind_param("i", $req['item_id']);
            $updItem->execute();
        }
        // FR-17: notify requester of decision
        create_notification($conn, $req['requester_id'], 'Request Decision', $req['item_id'],
            "Your request for \"" . $req['item_name'] . "\" was " . strtolower($decision) . ".");
    }
    header("Location: requests.php");
    exit;
}

$received = $conn->prepare("
  SELECT r.*, i.item_name, i.item_id, u.name AS requester_name
  FROM requests r JOIN items i ON r.item_id = i.item_id JOIN users u ON r.requester_id = u.user_id
  WHERE i.owner_id = ? ORDER BY r.request_date DESC");
$received->bind_param("i", $uid);
$received->execute();
$receivedResult = $received->get_result();

$sent = $conn->prepare("
  SELECT r.*, i.item_name, i.item_id, u.name AS owner_name
  FROM requests r JOIN items i ON r.item_id = i.item_id JOIN users u ON i.owner_id = u.user_id
  WHERE r.requester_id = ? ORDER BY r.request_date DESC");
$sent->bind_param("i", $uid);
$sent->execute();
$sentResult = $sent->get_result();

$page_title = "Requests";
include __DIR__ . '/includes/header.php';
?>

<h1 style="font-size:1.6rem;">Requests</h1>

<div class="tabs">
  <div class="tab active" data-tab="received">Received (<?= $receivedResult->num_rows ?>)</div>
  <div class="tab" data-tab="sent">Sent (<?= $sentResult->num_rows ?>)</div>
</div>

<div id="tab-received" class="tab-content">
  <?php if ($receivedResult->num_rows === 0): ?>
    <div class="empty-state"><div class="icon">📨</div><p>No requests received yet.</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <tr><th>Item</th><th>Requested By</th><th>Date</th><th>Status</th><th>Action</th></tr>
        <?php while ($r = $receivedResult->fetch_assoc()): ?>
          <tr>
            <td><a href="item.php?id=<?= $r['item_id'] ?>"><?= e($r['item_name']) ?></a></td>
            <td><?= e($r['requester_name']) ?></td>
            <td><?= time_ago($r['request_date']) ?></td>
            <td><span class="status-pill status-<?= $r['request_status'] ?>"><?= $r['request_status'] ?></span></td>
            <td>
              <?php if ($r['request_status'] === 'Pending'): ?>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
                  <button type="submit" name="decision" value="Accepted" class="btn btn-sm btn-primary">Accept</button>
                  <button type="submit" name="decision" value="Rejected" class="btn btn-sm btn-danger">Reject</button>
                </form>
              <?php else: ?> — <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>
  <?php endif; ?>
</div>

<div id="tab-sent" class="tab-content" style="display:none;">
  <?php if ($sentResult->num_rows === 0): ?>
    <div class="empty-state"><div class="icon">📤</div><p>You haven't requested any items yet.</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <tr><th>Item</th><th>Owner</th><th>Date</th><th>Status</th></tr>
        <?php while ($r = $sentResult->fetch_assoc()): ?>
          <tr>
            <td><a href="item.php?id=<?= $r['item_id'] ?>"><?= e($r['item_name']) ?></a></td>
            <td><?= e($r['owner_name']) ?></td>
            <td><?= time_ago($r['request_date']) ?></td>
            <td><span class="status-pill status-<?= $r['request_status'] ?>"><?= $r['request_status'] ?></span></td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>
  <?php endif; ?>
</div>

<script>
document.querySelectorAll('.tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
    document.getElementById('tab-' + tab.dataset.tab).style.display = 'block';
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
