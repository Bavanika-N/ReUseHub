<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$in_admin = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = (int)$_POST['item_id'];
    if (isset($_POST['delete_item'])) {
        $stmt = $conn->prepare("DELETE FROM items WHERE item_id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
    } elseif (isset($_POST['mark_given'])) {
        $item = get_item($conn, $item_id);
        $stmt = $conn->prepare("UPDATE items SET status = 'Sold Out' WHERE item_id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $req = $conn->prepare("SELECT DISTINCT requester_id FROM requests WHERE item_id = ?");
        $req->bind_param("i", $item_id);
        $req->execute();
        $res = $req->get_result();
        while ($r = $res->fetch_assoc()) {
            create_notification($conn, $r['requester_id'], 'Item Sold Out', $item_id, "\"" . $item['item_name'] . "\" has been marked as sold out.");
        }
    }
    header("Location: items.php");
    exit;
}

$items = $conn->query("SELECT items.*, users.name AS owner_name FROM items JOIN users ON items.owner_id = users.user_id ORDER BY created_at DESC");

$page_title = "Manage Items";
include __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
  <?php include __DIR__ . '/_sidebar.php'; ?>

  <div>
    <h1 style="font-size:1.6rem;">All Posted Items</h1>
    <p>Moderate items across the platform.</p>

    <div class="card" style="padding:0;">
      <div class="table-wrap">
        <table>
          <tr><th>Item</th><th>Owner</th><th>Category</th><th>Status</th><th>Posted</th><th>Actions</th></tr>
          <?php while ($item = $items->fetch_assoc()):
            $statusClass = $item['status'] === 'Sold Out' ? 'Sold' : $item['status']; ?>
            <tr>
              <td><a href="../item.php?id=<?= $item['item_id'] ?>"><?= e($item['item_name']) ?></a></td>
              <td><?= e($item['owner_name']) ?></td>
              <td><?= e($item['category']) ?></td>
              <td><span class="status-pill status-<?= $statusClass ?>"><?= $item['status'] ?></span></td>
              <td><?= time_ago($item['created_at']) ?></td>
              <td style="display:flex; gap:6px;">
                <?php if ($item['status'] !== 'Sold Out'): ?>
                <form method="POST">
                  <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                  <button type="submit" name="mark_given" class="btn btn-sm btn-outline">Mark Sold Out</button>
                </form>
                <?php endif; ?>
                <form method="POST">
                  <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                  <button type="submit" name="delete_item" class="btn btn-sm btn-danger" data-confirm="Remove this item?">Remove</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
