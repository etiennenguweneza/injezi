<?php
// register.php
require_once 'includes/db_connect.php';

$errors = [];
$message = '';

function old($key) {
    return htmlspecialchars($_POST[$key] ?? '', ENT_QUOTES);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    // Collect + sanitize
    $full_name = trim($_POST['full_name'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $dob       = $_POST['dob'] ?? null;
    $role      = $_POST['role'] ?? 'staff';
    $password  = $_POST['password'] ?? '';

    // Basic validation
    if ($full_name === '') $errors[] = 'Full name is required.';
    if ($username === '')  $errors[] = 'Username is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if ($phone === '') $errors[] = 'Phone number is required.';
    if ($dob === '') $errors[] = 'Date of birth is required.';
    if ($password === '' || strlen($password) < 6) $errors[] = 'Password is required (min 6 chars).';

    // Check duplicates (username or email)
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $errors[] = 'Username or email already in use.';
        }
        $stmt->close();
    }

    // Handle profile photo upload
    $profile_photo = 'default.png';
    if (empty($errors) && !empty($_FILES['profile_photo']['name'])) {
        $uploadDir = __DIR__ . '/images/users';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileTmp  = $_FILES['profile_photo']['tmp_name'];
        $fileName = basename($_FILES['profile_photo']['name']);
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['png','jpg','jpeg','webp','gif'];

        if (!in_array($ext, $allowed)) {
            $errors[] = 'Profile photo must be an image (png, jpg, jpeg, webp, gif).';
        } else {
            $newName = time() . '_' . preg_replace('/[^a-z0-9\-_\.]/i', '', $fileName);
            $target = $uploadDir . '/' . $newName;
            if (move_uploaded_file($fileTmp, $target)) {
                $profile_photo = $newName;
            } else {
                $errors[] = 'Failed to upload profile photo.';
            }
        }
    }

    // Insert user
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, phone, dob, role, profile_photo, password, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'inactive')");
        if ($stmt === false) {
            $errors[] = 'Database error: ' . $conn->error;
        } else {
            $stmt->bind_param('ssssssss', $full_name, $username, $email, $phone, $dob, $role, $profile_photo, $hashed);
            if ($stmt->execute()) {
                $stmt->close();
                // Redirect to payment page (user will pay to activate account)
                header('Location: payment.php?user=' . urlencode($username));
                exit;
            } else {
                $errors[] = 'Database error: ' . $stmt->error;
                $stmt->close();
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Register - INJEZI</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    /* minimal card styles (keeps look consistent) */
    body{background:linear-gradient(135deg,#0d6efd,#6f42c1);font-family:Inter,system-ui,Arial;color:#222;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
    .card{background:#fff;padding:32px;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,0.12);width:420px;max-width:95%}
    h2{color:#0d6efd;text-align:center;margin:0 0 14px}
    form{display:flex;flex-direction:column;gap:12px}
    input,select{padding:10px;border-radius:8px;border:1px solid #d1d5db;font-size:14px}
    button{background:#0d6efd;color:#fff;padding:10px;border:none;border-radius:8px;font-weight:600;cursor:pointer;margin-top:6px}
    .errors{background:#fdecec;border-left:4px solid #ff6b6b;padding:10px;border-radius:8px;color:#6b1b1b;margin-bottom:8px}
  </style>
</head>
<body>
  <div class="card">
    <h2>Create INJEZI Account</h2>

    <?php if (!empty($errors)): ?>
      <div class="errors">
        <ul style="margin:0;padding-left:18px">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" novalidate>
      <input type="text" name="full_name" placeholder="Full Name" required value="<?= old('full_name') ?>">
      <input type="text" name="username" placeholder="Username" required value="<?= old('username') ?>">
      <input type="email" name="email" placeholder="Email Address" required value="<?= old('email') ?>">
      <input type="text" name="phone" placeholder="Phone Number" required value="<?= old('phone') ?>">
      <input type="date" name="dob" required value="<?= old('dob') ?>">
      <select name="role" required>
        <option value="">Select Role</option>
        <option value="admin" <?= (old('role') === 'admin') ? 'selected' : '' ?>>Admin</option>
        <option value="staff" <?= (old('role') === 'staff') ? 'selected' : '' ?>>Staff</option>
        <option value="developer" <?= (old('role') === 'developer') ? 'selected' : '' ?>>Developer</option>
      </select>
      <input type="file" name="profile_photo" accept="image/*">
      <input type="password" name="password" placeholder="Password (min 6 chars)" required>
      <button type="submit" name="register">Register & Proceed</button>
    </form>

    <p style="text-align:center;margin-top:12px;font-size:14px;color:#556">Already have an account? <a href="login.php">Login</a></p>
  </div>
</body>
</html>
