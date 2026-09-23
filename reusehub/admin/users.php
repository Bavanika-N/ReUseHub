<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$in_admin = true;

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $delId = (int)$_POST['delete_user_id'];
    if ($delId !== current_user_id()) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $delId);
        $stmt->execute();
        $stmt->close();
        $msg = "User account removed successfully.";
    }
}

$users = $conn->query("
  SELECT u.*, 
    (SELECT COUNT(*) FROM items WHERE owner_id = u.user_id) AS item_count,
    (SELECT COUNT(*) FROM requests WHERE requester_id = u.user_id) AS request_count
  FROM users u ORDER BY created_at DESC");

$page_title = "Manage Users";
include __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
  <?php include __DIR__ . '/_sidebar.php'; ?>

  <div class="admin-main-content">
    <div class="admin-header-row">
      <div>
        <h1 style="font-size:1.45rem; margin:0;">Registered Users</h1>
        <p style="margin:2px 0 0; font-size:0.82rem; color:var(--text-dim);">All students, staff, and administrators on ReUseHub.</p>
      </div>
    </div>

    <?php if ($msg): ?><div class="alert alert-success" style="padding:8px 14px; margin-bottom:12px; font-size:0.85rem;"><?= e($msg) ?></div><?php endif; ?>

    <div class="card admin-table-card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>User Name</th>
              <th>Email Address</th>
              <th>Role</th>
              <th>Items Posted</th>
              <th>Requests Made</th>
              <th>Joined Date</th>
              <th style="text-align:right;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($u = $users->fetch_assoc()): ?>
              <tr>
                <td><strong><?= e($u['name']) ?></strong></td>
                <td><?= e($u['email']) ?></td>
                <td>
                  <span class="status-pill <?= $u['role']==='admin' ? 'status-Requested' : 'status-Available' ?>">
                    <?= ucfirst($u['role']) ?>
                  </span>
                </td>
                <td><?= $u['item_count'] ?> items</td>
                <td><?= $u['request_count'] ?> requests</td>
                <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                <td style="text-align:right;">
                  <?php if ($u['user_id'] != current_user_id()): ?>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Remove this user account and all their listings?');">
                      <input type="hidden" name="delete_user_id" value="<?= $u['user_id'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm" style="padding:3px 8px; font-size:0.75rem;">Delete</button>
                    </form>
                  <?php else: ?>
                    <span style="font-size:0.75rem; color:var(--primary); font-weight:600;">(You)</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
