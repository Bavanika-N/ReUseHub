<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$error = '';
$categories = ['Books', 'Stationery', 'Electronics', 'Furniture', 'Clothing', 'Kitchenware', 'Other'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['item_name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $cat = trim($_POST['category'] ?? '');
    $contact = trim($_POST['contact_number'] ?? '');

    if ($name === '' || $cat === '' || $contact === '') {
        $error = "Please fill in all required fields.";
    } else {
        // Handle multiple image uploads (name="images[]")
        $uploadedNames = [];
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
                    if ($saved) $uploadedNames[] = $saved;
                }
            }
        }
        $imageName = $uploadedNames[0] ?? null; // first photo becomes the cover image

        $stmt = $conn->prepare("INSERT INTO items (owner_id, item_name, description, category, image, contact_number, status) VALUES (?, ?, ?, ?, ?, ?, 'Available')");
        $owner_id = current_user_id();
        $stmt->bind_param("isssss", $owner_id, $name, $desc, $cat, $imageName, $contact);
        if ($stmt->execute()) {
            $newItemId = $conn->insert_id;
            // Save any additional photos beyond the cover image
            foreach (array_slice($uploadedNames, 1) as $extra) {
                add_item_image($conn, $newItemId, $extra);
            }
            // FR-19: notify registered users of new item posted
            notify_all_users_except($conn, $owner_id, 'New Item', $newItemId, $_SESSION['name'] . " posted a new item: \"$name\"");
            header("Location: item.php?id=$newItemId&posted=1");
            exit;
        } else {
            $error = "Something went wrong while saving the item.";
        }
        $stmt->close();
    }
}

$page_title = "Post an Item";
include __DIR__ . '/includes/header.php';
?>

<div class="post-item-screen-wrap">
  <div class="post-item-container">
    <div class="card post-item-card">
      <div class="post-item-header">
        <div>
          <h2>Post an item</h2>
          <p>Give something a second life — fill details below.</p>
        </div>
        <a href="index.php" class="btn btn-outline btn-sm">✕ Cancel</a>
      </div>

      <?php if ($error): ?><div class="alert alert-error" style="padding:8px 12px; margin-bottom:12px; font-size:.85rem;"><?= e($error) ?></div><?php endif; ?>

      <form method="POST" enctype="multipart/form-data">
        <div class="post-grid-2col">
          <!-- Left column: Item info -->
          <div class="form-col-left">
            <div class="form-group compact">
              <label>Item Name *</label>
              <input type="text" name="item_name" required value="<?= e($_POST['item_name'] ?? '') ?>" placeholder="e.g. Calculus Textbook (3rd Edition)">
            </div>

            <div class="form-row-2col">
              <div class="form-group compact">
                <label>Category *</label>
                <select name="category" required>
                  <option value="">Select category…</option>
                  <?php foreach ($categories as $c): ?>
                    <option value="<?= $c ?>" <?= (isset($_POST['category']) && $_POST['category'] === $c) ? 'selected' : '' ?>><?= $c ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group compact">
                <label>Contact Number *</label>
                <input type="tel" name="contact_number" required placeholder="e.g. 077 123 4567" value="<?= e($_POST['contact_number'] ?? '') ?>">
              </div>
            </div>

            <div class="form-group compact" style="margin-bottom:0;">
              <label>Description</label>
              <textarea name="description" placeholder="Condition, details, pickup notes…"><?= e($_POST['description'] ?? '') ?></textarea>
            </div>
          </div>

          <!-- Right column: Photos & Submit -->
          <div class="form-col-right" style="display:flex; flex-direction:column; justify-content:space-between;">
            <div class="form-group compact" style="flex:1; display:flex; flex-direction:column; margin-bottom:12px;">
              <label>Photos (optional, select multiple)</label>
              <label class="file-drop-compact" for="itemImage">
                <div id="imgPreviewWrap" class="multi-img-preview"></div>
                <div id="fileLabel" style="font-size:0.88rem; font-weight:600;">📷 Click to upload photos</div>
                <span style="font-size:0.75rem; color:var(--text-dim); margin-top:3px;">Supports multiple JPG, PNG, WebP</span>
              </label>
              <input type="file" id="itemImage" name="images[]" accept="image/*" multiple style="display:none;">
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding:11px 20px; font-size:0.95rem;">🚀 Post Item Now</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Helpful community highlights to balance the screen -->
    <div class="post-item-perks">
      <div class="perk-item">
        <span class="perk-icon">📸</span>
        <div>
          <strong>Photos help</strong>
          <span>Items with photos get claimed 3x faster</span>
        </div>
      </div>
      <div class="perk-item">
        <span class="perk-icon">♻️</span>
        <div>
          <strong>100% Free</strong>
          <span>Encouraging campus reuse & zero waste</span>
        </div>
      </div>
      <div class="perk-item">
        <span class="perk-icon">🔔</span>
        <div>
          <strong>Instant Notification</strong>
          <span>Users get notified of newly posted items</span>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
