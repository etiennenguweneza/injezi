<?php
// login.php
require_once 'includes/db_connect.php';
session_start();

// Optional: If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';
$errors = [];
$old_username = '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // trim & fetch
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $old_username = htmlspecialchars($username, ENT_QUOTES);

    if ($username === '' || $password === '') {
        $errors[] = 'Please enter your username and password.';
    }

    if (empty($errors)) {
        // Prepared statement to fetch user by username
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        if ($stmt === false) {
            $errors[] = 'Database error. Please try again later.';
        } else {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $res = $stmt->get_result();
            $user = $res->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($password, $user['password'])) {
                // Optional: check account status
                if (isset($user['status']) && $user['status'] !== 'inactive') {
                    $errors[] = 'Your account is not active. Please complete payment or contact admin.';
                } else {
                    // Successful login
                    session_regenerate_id(true); // prevent session fixation
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'] ?? $user['username'];
                    $_SESSION['role'] = $user['role'] ?? 'staff';

                    // If your users table contains company info (company_id/company_name), store them too.
                    if (isset($user['company_id'])) {
                        $_SESSION['company_id'] = $user['company_id'];
                    }
                    if (isset($user['company_name'])) {
                        $_SESSION['company_name'] = $user['company_name'];
                    }

                    // Redirect to dashboard
                    header('Location: index.php');
                    exit;
                }
            } else {
                $errors[] = 'Invalid username or password.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login | INJEZI</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
body{
  background: linear-gradient(135deg,#007bff,#6610f2);
  min-height:100vh;display:flex;align-items:center;justify-content:center;margin:0;
  font-family: 'Poppins', sans-serif;
}
.panel{
  background:#fff;padding:28px;border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,0.12);
  width:380px;text-align:center;
}
h2{color:#007bff;margin-bottom:18px;font-size:20px}
input{width:100%;padding:11px 12px;margin-bottom:12px;border-radius:10px;border:1px solid #ced4da;font-size:15px;outline:none}
input:focus{box-shadow:0 6px 18px rgba(2,6,23,0.08);border-color:#7aa7ff}
button{width:100%;padding:12px;background:#007bff;color:#fff;font-weight:600;border:none;border-radius:10px;cursor:pointer;transition:0.18s}
button:hover{background:#0056b3;transform:translateY(-2px)}
.message,.errors{margin-bottom:14px;padding:12px;border-radius:10px;text-align:left}
.message{background:#e7f8ef;border-left:5px solid #2ecc71;color:#1f5133}
.errors{background:#fff6f6;border-left:5px solid #ff6b6b;color:#6b1b1b}
.small{font-size:0.92rem;color:#334155}
a{color:#007bff;text-decoration:none;font-weight:600}
a:hover{text-decoration:underline}
</style>
</head>
<body>
<div class="panel" role="main" aria-labelledby="login-heading">
  <h2 id="login-heading">🔐 INJEZI Login</h2>

  <?php if ($message): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="errors">
      <ul style="margin:0 0 0 18px;padding:0;">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="POST" novalidate>
    <input type="text" name="username" placeholder="Username" required value="<?= $old_username ?>">
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" aria-label="Login to INJEZI">Login</button>
  </form>

  <p class="small" style="margin-top:12px">Don’t have an account? <a href="register.php">Register here</a></p>
</div>
</body>
</html>
