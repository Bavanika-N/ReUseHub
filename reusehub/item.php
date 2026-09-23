<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$item_id = (int)($_GET['id'] ?? 0);
$item = get_item($conn, $item_id);

if (!$item) {
    header("Location: index.php");
    exit;
}

$isOwner = is_logged_in() && current_user_id() == $item['owner_id'];
$message = '';
$error = '';

// FR-10: Send a request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_request'])) {
    require_login();
    if ($isOwner) {
        $error = "You cannot request your own item.";
    } elseif ($item['status'] !== 'Available') {
        $error = "This item is no longer available.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM requests WHERE item_id = ? AND requester_id = ? AND request_status = 'Pending'");
        $uid = current_user_id();
        $stmt->bind_param("ii", $item_id, $uid);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "You've already requested this item.";
        } else {
            $stmt2 = $conn->prepare("INSERT INTO requests (item_id, requester_id) VALUES (?, ?)");
            $stmt2->bind_param("ii", $item_id, $uid);
            $stmt2->execute();
            $stmt2->close();
            // FR-16: notify owner of new request
            create_notification($conn, $item['owner_id'], 'Request Received', $item_id, $_SESSION['name'] . " requested your item \"" . $item['item_name'] . "\"");
            $message = "Request sent! The owner will be notified.";
        }
        $stmt->close();
    }
}

$item = get_item($conn, $item_id); // refresh
$galleryImages = [];
if ($item['image']) $galleryImages[] = $item['image'];
foreach (get_item_images($conn, $item_id) as $img) $galleryImages[] = $img['image'];

$page_title = $item['item_name'];
include __DIR__ . '/includes/header.php';
?>

<?php if (isset($_GET['posted'])): ?>
  <div class="alert alert-success">🎉 Your item has been posted successfully!</div>
<?php endif; ?>
<?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<div class="item-detail">
  <div class="item-detail-img">
    <?php if (!empty($galleryImages)): ?>
      <img id="mainItemImage" class="lightbox-trigger" src="assets/uploads/<?= e($galleryImages[0]) ?>" alt="<?= e($item['item_name']) ?>">
    <?php else: ?>📦<?php endif; ?>
  </div>
  <?php if (count($galleryImages) > 1): ?>
    <div class="gallery-thumbs">
      <?php foreach ($galleryImages as $i => $img): ?>
        <img src="assets/uploads/<?= e($img) ?>" class="gallery-thumb <?= $i === 0 ? 'active' : '' ?>" data-full="assets/uploads/<?= e($img) ?>" alt="">
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="card">
    <span class="item-cat"><?= e($item['category']) ?></span>
    <h1 style="font-size:1.6rem; margin-top:6px;"><?= e($item['item_name']) ?></h1>
    <?php
      $statusClass = $item['status'] === 'Sold Out' ? 'Sold' : $item['status'];
    ?>
    <span class="status-pill status-<?= $statusClass ?>"><?= e($item['status']) ?></span>

    <p style="margin-top:16px;"><?= nl2br(e($item['description'])) ?></p>
    <p style="font-size:.85rem;">Posted by <strong><?= e($item['owner_name']) ?></strong> · <?= time_ago($item['created_at']) ?></p>

    <?php if ($item['status'] === 'Available'): ?>
      <div class="contact-box">
        📞 <strong>Contact:</strong> <?= e($item['contact_number']) ?>
        <p style="margin:6px 0 0; font-size:.8rem;">Arrange to meet and collect this item in person.</p>
      </div>
    <?php else: ?>
      <div class="contact-box hidden-contact">
        🔒 Contact number hidden — this item is no longer available.
      </div>
    <?php endif; ?>

    <div style="margin-top:20px;">
      <?php if (!is_logged_in()): ?>
        <a href="login.php" class="btn btn-primary btn-block">Log in to request this item</a>
      <?php elseif ($isOwner): ?>
        <a href="edit_item.php?id=<?= $item['item_id'] ?>" class="btn btn-outline btn-block">Manage This Item</a>
      <?php elseif ($item['status'] === 'Available'): ?>
        <form method="POST">
          <button type="submit" name="send_request" class="btn btn-primary btn-block">Send Request</button>
        </form>
      <?php else: ?>
        <button class="btn btn-ghost btn-block" disabled>Not Available</button>
      <?php endif; ?>
    </div>
  </div>
</div>

<div id="imgLightbox" class="lightbox-overlay">
  <span class="lightbox-close" id="lightboxClose">✕</span>
  <img id="lightboxImg" src="" alt="">
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
