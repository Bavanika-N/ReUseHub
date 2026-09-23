<?php $adminPage = basename($_SERVER['PHP_SELF']); ?>
<div class="admin-sidebar">
  <div class="admin-nav-group">
    <div class="sidebar-label">Admin Management</div>
    <a href="dashboard.php" class="<?= $adminPage === 'dashboard.php' ? 'active' : '' ?>">📊 Dashboard</a>
    <a href="users.php" class="<?= $adminPage === 'users.php' ? 'active' : '' ?>">👥 Registered Users</a>
  </div>

  <div class="admin-sidebar-foot">
    <a href="../index.php" class="view-site-link">🌐 View Campus Site</a>
  </div>
</div>
