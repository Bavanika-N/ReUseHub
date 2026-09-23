<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$item_id = (int)($_GET['id'] ?? 0);
$item = get_item($conn, $item_id);

if (!$item || ($item['owner_id'] != current_user_id() && !is_admin())) {
    header("Location: index.php");
    exit;
}

$error = ''; $success = '';
$categories = ['Books', 'Stationery', 'Electronics', 'Furniture', 'Clothing', 'Kitchenware', 'Other'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['delete_item'])) {
        $stmt = $conn->prepare("DELETE FROM items WHERE item_id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        header("Location: dashboard.php?deleted=1");
        exit;
    }

    if (isset($_POST['update_status'])) {
        $newStatus = $_POST['status'];
        $oldStatus = $item['status'];
        $stmt = $conn->prepare("UPDATE items SET status = ? WHERE item_id = ?");
        $stmt->bind_param("si", $newStatus, $item_id);
        $stmt->execute();

        // FR-18: notify requesters when item is marked Sold Out; also hides the contact number (see item.php)
        if ($newStatus === 'Sold Out' && $oldStatus !== 'Sold Out') {
            $req = $conn->prepare("SELECT DISTINCT requester_id FROM requests WHERE item_id = ?");
            $req->bind_param("i", $item_id);
            $req->execute();
            $res = $req->get_result();
            while ($r = $res->fetch_assoc()) {
                create_notification($conn, $r['requester_id'], 'Item Sold Out', $item_id, "\"" . $item['item_name'] . "\" has been marked as sold out.");
            }
            $req->close();
        }
        $success = "Status updated to $newStatus.";
        $item = get_item($conn, $item_id);
    } elseif (isset($_POST['delete_image'])) {
        delete_item_image($conn, (int)$_POST['image_id'], $item_id);
        header("Location: edit_item.php?id=$item_id");
        exit;
    } else {
        $name = trim($_POST['item_name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $cat = trim($_POST['category'] ?? '');
        $contact = trim($_POST['contact_number'] ?? '');
        $imageName = $item['image'];

        // Additional photos uploaded here just get appended to the gallery,
        // unless there's no cover image yet, in which case the first one becomes the cover.
        $newUploads = [];
        if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $count = count($_FILES['images']['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['images']['name'][$i],
                        'tmp_name' => $_FILES['images']['tmp_name'][$i],
                        'error' => $_FILES['images']['error'][$i],
                    ];
                    $saved = save_uploaded_image($file);
                    if ($saved) $newUploads[] = $saved;
                }
            }
        }
        if (!$imageName && !empty($newUploads)) {
            $imageName = array_shift($newUploads);
        }

        if ($name === '' || $cat === '' || $contact === '') {
            $error = "Please fill in all required fields.";
        } else {
            $stmt = $conn->prepare("UPDATE items SET item_name=?, description=?, category=?, image=?, contact_number=? WHERE item_id=?");
            $stmt->bind_param("sssssi", $name, $desc, $cat, $imageName, $contact, $item_id);
            $stmt->execute();
            foreach ($newUploads as $extra) {
                add_item_image($conn, $item_id, $extra);
            }
            $success = "Item updated successfully.";
            $item = get_item($conn, $item_id);
        }
    }
}

$page_title = "Manage Item";
include __DIR__ . '/includes/header.php';
?>

<div class="container-narrow">
  <div class="card">
    <h2>Manage Item</h2>

    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

    <div class="form-group">
      <label>Update Status</label>
      <form method="POST" style="display:flex; gap:10px;">
        <select name="status">
          <?php foreach (['Available','Requested','Sold Out'] as $s): ?>
            <option value="<?= $s ?>" <?= $item['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" name="update_status" class="btn btn-primary">Update</button>
      </form>
    </div>

    <hr style="border-color:var(--border); margin:22px 0;">

    <div class="form-group">
      <label>Photos</label>
      <div class="gallery-manage">
        <?php if ($item['image']): ?>
          <div class="gallery-manage-item">
            <img src="assets/uploads/<?= e($item['image']) ?>" alt="">
            <span class="gallery-manage-tag">Cover</span>
          </div>
        <?php endif; ?>
        <?php foreach (get_item_images($conn, $item_id) as $img): ?>
          <div class="gallery-manage-item">
            <img src="assets/uploads/<?= e($img['image']) ?>" alt="">
            <form method="POST">
              <input type="hidden" name="image_id" value="<?= $img['image_id'] ?>">
              <button type="submit" name="delete_image" class="btn btn-sm btn-danger" data-confirm="Remove this photo?">✕</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <hr style="border-color:var(--border); margin:22px 0;">

    <form method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label>Item Name</label>
        <input type="text" name="item_name" required value="<?= e($item['item_name']) ?>">
      </div>
      <div class="form-group">
        <label>Category</label>
        <select name="category" required>
          <?php foreach ($categories as $c): ?>
            <option value="<?= $c ?>" <?= $item['category'] === $c ? 'selected' : '' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea name="description"><?= e($item['description']) ?></textarea>
      </div>
      <div class="form-group">
        <label>Contact Number</label>
        <input type="tel" name="contact_number" required value="<?= e($item['contact_number']) ?>">
      </div>
      <div class="form-group">
        <label>Add More Photos (optional)</label>
        <input type="file" name="images[]" accept="image/*" multiple>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
    </form>

    <form method="POST" style="margin-top:14px;">
      <button type="submit" name="delete_item" class="btn btn-danger btn-block" data-confirm="Delete this item permanently?">Delete Item</button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
