<?php
require_once __DIR__ . '/includes/auth.php';
if (is_logged_in()) { header("Location: index.php"); exit; }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt2 = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt2->bind_param("sss", $name, $email, $hash);
            if ($stmt2->execute()) {
                $success = "Account created successfully! You can now log in.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}

$page_title = "Sign Up";
include __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
  <div class="card auth-card">
    <h2>Create your account</h2>
    <p class="auth-sub">Join ReUseHub and start giving items a second life ♻️</p>

    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
    </form>
    <div class="auth-switch">Already have an account? <a href="login.php">Log in</a></div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
