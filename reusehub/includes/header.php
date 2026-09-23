<?php
require_once __DIR__ . '/functions.php';
$unread = is_logged_in() ? get_unread_count($conn, current_user_id()) : 0;
$page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? e($page_title) . ' · ReUseHub' : 'ReUseHub' ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= isset($in_admin) ? '../assets/css/style.css' : 'assets/css/style.css' ?>">
</head>
<body>
<div class="bg-orbs" aria-hidden="true">
  <span class="orb orb1"></span>
  <span class="orb orb2"></span>
  <span class="orb orb3"></span>
</div>

<nav class="navbar">
  <div class="nav-inner">
    <a href="<?= isset($in_admin) ? '../index.php' : (is_logged_in() ? 'index.php' : 'login.php') ?>" class="brand">
      <span class="brand-icon">♻️</span> ReUse<span>Hub</span>
    </a>

    <?php if (!isset($in_admin) && is_logged_in()): ?>
    <div class="nav-links">
      <a href="index.php" class="<?= $page === 'index.php' ? 'active' : '' ?>">Browse</a>
      <a href="add_item.php" class="<?= $page === 'add_item.php' ? 'active' : '' ?>">Post Item</a>
      <a href="dashboard.php" class="<?= $page === 'dashboard.php' ? 'active' : '' ?>">My Items</a>
      <a href="requests.php" class="<?= $page === 'requests.php' ? 'active' : '' ?>">Requests</a>
      <?php if (is_admin()): ?><a href="admin/dashboard.php">Admin</a><?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="nav-actions">
      <?php if (is_logged_in()): ?>
        <div class="notif-wrap">
          <button class="notif-btn" id="notifBtn" aria-label="Notifications">
            🔔
            <?php if ($unread > 0): ?><span class="badge"><?= $unread > 9 ? '9+' : $unread ?></span><?php endif; ?>
          </button>
          <div class="notif-dropdown" id="notifDropdown">
            <div class="notif-header">Notifications</div>
            <div class="notif-list" id="notifList">
              <div class="notif-empty">Loading…</div>
            </div>
            <a href="<?= isset($in_admin) ? '../notifications.php' : 'notifications.php' ?>" class="notif-viewall">View all</a>
          </div>
        </div>
        <div class="user-chip"><?= e($_SESSION['name']) ?></div>
        <a href="<?= isset($in_admin) ? '../logout.php' : 'logout.php' ?>" class="btn btn-ghost">Logout</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-ghost">Login</a>
        <a href="register.php" class="btn btn-primary">Sign Up</a>
      <?php endif; ?>
    </div>
    <button class="hamburger" id="hamburger">☰</button>
  </div>
</nav>

<script>window.IS_ADMIN_PAGE = <?= isset($in_admin) ? 'true' : 'false' ?>;</script>

<main class="page-content">
