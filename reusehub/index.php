<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$search = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');

$sql = "SELECT items.*, users.name AS owner_name FROM items JOIN users ON items.owner_id = users.user_id WHERE status = 'Available'";
$params = [];
$types = "";

if ($search !== '') {
    $sql .= " AND item_name LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}
if ($category !== '') {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$items = $stmt->get_result();

$cats = $conn->query("SELECT DISTINCT category FROM items ORDER BY category");

$page_title = "Browse Items";
include __DIR__ . '/includes/header.php';
?>

<div class="browse-top-bar">
  <form class="browse-search-form" method="GET">
    <div class="search-input-wrap">
      <span class="search-icon">🔍</span>
      <input type="search" name="q" placeholder="Search items by keyword, name, or description…" value="<?= e($search) ?>">
    </div>
    <select name="category" onchange="this.form.submit()">
      <option value="">All Categories</option>
      <?php while ($c = $cats->fetch_assoc()): ?>
        <option value="<?= e($c['category']) ?>" <?= $category === $c['category'] ? 'selected' : '' ?>><?= e($c['category']) ?></option>
      <?php endwhile; ?>
    </select>
    <button type="submit" class="btn btn-primary">Search</button>
    <?php if ($search !== '' || $category !== ''): ?>
      <a href="index.php" class="btn btn-ghost" title="Clear search">✕</a>
    <?php endif; ?>
  </form>

  <div class="browse-top-action">
    <a href="add_item.php" class="btn btn-primary">+ Post Item</a>
  </div>
</div>

<?php if ($items->num_rows === 0): ?>
  <div class="empty-state">
    <div class="icon">📦</div>
    <h3>No items found</h3>
    <p>Try a different search, or be the first to post something!</p>
  </div>
<?php else: ?>
  <div class="grid grid-items">
    <?php while ($item = $items->fetch_assoc()): ?>
      <a href="item.php?id=<?= $item['item_id'] ?>" class="item-card">
        <div class="item-thumb">
          <?php if ($item['image']): ?>
            <img src="assets/uploads/<?= e($item['image']) ?>" alt="<?= e($item['item_name']) ?>">
          <?php else: ?>📦<?php endif; ?>
        </div>
        <div class="item-body">
          <span class="item-cat"><?= e($item['category']) ?></span>
          <h3><?= e($item['item_name']) ?></h3>
          <p class="item-desc"><?= e(mb_strimwidth($item['description'], 0, 80, '…')) ?></p>
          <div class="item-foot">
            <span class="status-pill status-Available">Available</span>
            <span style="font-size:.78rem;color:var(--text-dim);">by <?= e($item['owner_name']) ?></span>
          </div>
        </div>
      </a>
    <?php endwhile; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
