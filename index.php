<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$search = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');

$sql = "SELECT items.*, users.name AS owner_name FROM items JOIN users ON items.owner_id = users.user_id WHERE status = 'Available' ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$items = $stmt->get_result();

$cats = $conn->query("SELECT DISTINCT category FROM items WHERE status = 'Available' ORDER BY category");

$page_title = "Browse Items";
include __DIR__ . '/includes/header.php';
?>

<div class="browse-top-bar">
  <form class="browse-search-form" id="browseSearchForm" method="GET" onsubmit="return false;">
    <div class="search-input-wrap">
      <span class="search-icon">🔍</span>
      <input type="search" id="browseSearchInput" name="q" placeholder="Type to search items instantly (e.g. books, lab, cycle)…" value="<?= e($search) ?>" autocomplete="off">
      <button type="button" id="clearSearchBtn" class="btn-clear-search" style="display:<?= $search !== '' ? 'flex' : 'none' ?>;" title="Clear search">✕</button>
    </div>
    <select name="category" id="browseCategorySelect">
      <option value="">All Categories</option>
      <?php while ($c = $cats->fetch_assoc()): ?>
        <option value="<?= e($c['category']) ?>" <?= $category === $c['category'] ? 'selected' : '' ?>><?= e($c['category']) ?></option>
      <?php endwhile; ?>
    </select>
  </form>

  <div class="browse-top-action">
    <a href="add_item.php" class="btn btn-primary">+ Post Item</a>
  </div>
</div>

<?php if ($items->num_rows === 0): ?>
  <div class="empty-state">
    <div class="icon">📦</div>
    <h3>No items available yet</h3>
    <p>Be the first one to post an item on ReUseHub!</p>
    <a href="add_item.php" class="btn btn-primary" style="margin-top:10px;">+ Post an Item</a>
  </div>
<?php else: ?>
  <div class="grid grid-items" id="itemsGrid">
    <?php while ($item = $items->fetch_assoc()): ?>
      <a href="item.php?id=<?= $item['item_id'] ?>" class="item-card"
         data-name="<?= e(strtolower($item['item_name'])) ?>" 
         data-desc="<?= e(strtolower($item['description'] ?? '')) ?>" 
         data-cat="<?= e(strtolower($item['category'])) ?>" 
         data-owner="<?= e(strtolower($item['owner_name'])) ?>">
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

  <div class="empty-state" id="noMatchState" style="display:none; padding:40px 20px;">
    <div class="icon" style="font-size:2.6rem; margin-bottom:8px;">🔍</div>
    <h3 style="margin-bottom:6px;">No matching items found</h3>
    <p style="margin-bottom:14px;">No items match "<span id="searchKeyword" style="color:var(--primary); font-weight:600;"></span>".</p>
    <button type="button" class="btn btn-ghost" id="resetSearchBtn">✕ Clear Search & Show All Items</button>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>

