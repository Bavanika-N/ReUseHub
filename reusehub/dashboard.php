<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$uid = current_user_id();
$stmt = $conn->prepare("SELECT * FROM items WHERE owner_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $uid);
$stmt->execute();
$items = $stmt->get_result();

$page_title = "My Items";
include __DIR__ . '/includes/header.php';
?>

<h1 style="font-size:1.6rem;">My Posted Items</h1>
<p>Manage the items you've listed on ReUseHub.</p>

<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Item deleted.</div><?php endif; ?>

<?php if ($items->num_rows === 0): ?>
  <div class="empty-state">
    <div class="icon">📭</div>
    <h3>You haven't posted anything yet</h3>
    <p>Got something you no longer need?</p>
    <a href="add_item.php" class="btn btn-primary">Post Your First Item</a>
  </div>
<?php else: ?>
  <div class="grid grid-items">
    <?php while ($item = $items->fetch_assoc()):
      $statusClass = $item['status'] === 'Sold Out' ? 'Sold' : $item['status'];
    ?>
      <a href="edit_item.php?id=<?= $item['item_id'] ?>" class="item-card">
        <div class="item-thumb">
          <?php if ($item['image']): ?><img src="assets/uploads/<?= e($item['image']) ?>" alt=""><?php else: ?>📦<?php endif; ?>
        </div>
        <div class="item-body">
          <span class="item-cat"><?= e($item['category']) ?></span>
          <h3><?= e($item['item_name']) ?></h3>
          <div class="item-foot">
            <span class="status-pill status-<?= $statusClass ?>"><?= e($item['status']) ?></span>
            <span style="font-size:.78rem;color:var(--text-dim);"><?= time_ago($item['created_at']) ?></span>
          </div>
        </div>
      </a>
    <?php endwhile; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
