<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth_check.php';


$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $cost_price = $_POST['cost_price'] ?? '';
    $selling_price = $_POST['selling_price'] ?? '';
    $quantity = $_POST['quantity'] ?? '';

    if ($name === '') {
        $errors[] = 'Product name is required.';
    }

    if ($cost_price === '' || !is_numeric($cost_price) || (float)$cost_price < 0) {
        $errors[] = 'Cost price must be a number greater than or equal to 0.';
    }

    if ($selling_price === '' || !is_numeric($selling_price) || (float)$selling_price < 0) {
        $errors[] = 'Selling price must be a number greater than or equal to 0.';
    }

    if ($quantity === '' || !is_numeric($quantity) || (int)$quantity < 0) {
        $errors[] = 'Quantity must be an integer greater than or equal to 0.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO products (name, cost_price, selling_price, quantity) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $cp = number_format((float)$cost_price, 2, '.', '');
            $sp = number_format((float)$selling_price, 2, '.', '');
            $qty = (int)$quantity;
            $stmt->bind_param('sddi', $name, $cp, $sp, $qty);

            if ($stmt->execute()) {
                $message = '✅ Product added successfully!';
                $name = $cost_price = $selling_price = $quantity = '';
            } else {
                $errors[] = 'Database error: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = 'Database prepare error: ' . $conn->error;
        }
    }
}

function old($key) {
    return htmlspecialchars($_POST[$key] ?? '', ENT_QUOTES);
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Add Product | INJEZI</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .profile-dropdown {
  position: relative;
  display: inline-block;
}
.dropdown-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  color: #333;
  font-weight: 500;
}
.profile-pic {
  width: 35px;
  height: 35px;
  border-radius: 50%;
  object-fit: cover;
}
.dropdown-content {
  display: none;
  position: absolute;
  right: 0;
  background: #fff;
  min-width: 150px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  border-radius: 10px;
  z-index: 100;
}
.dropdown-content a {
  display: block;
  padding: 10px;
  color: #333;
  text-decoration: none;
}
.dropdown-content a:hover {
  background: #f0f0f0;
}
.dropdown-btn:hover + .dropdown-content,
.dropdown-content:hover {
  display: block;
}
    body {
      background: linear-gradient(180deg, #f0f4f8 0%, #e8ecf3 100%);
    }
    .add-container {
      max-width: 750px;
      margin: 40px auto;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.06);
      padding: 30px 40px;
      transition: all 0.3s ease;
    }
    .add-container:hover {
      box-shadow: 0 15px 50px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      color: #007bff;
      margin-bottom: 10px;
    }
    .subtext {
      text-align: center;
      color: #6c757d;
      margin-bottom: 25px;
    }
    form label {
      font-weight: 600;
      margin-top: 10px;
      display: block;
      color: #333;
    }
    input[type="text"], input[type="number"] {
      width: 100%;
      padding: 10px 14px;
      border-radius: 10px;
      border: 1px solid #ced4da;
      font-size: 15px;
      margin-top: 6px;
      transition: all 0.2s;
    }
    input[type="text"]:focus, input[type="number"]:focus {
      outline: none;
      border-color: #007bff;
      box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
    }
    button[type="submit"] {
      width: 100%;
      background: linear-gradient(90deg, #007bff, #0056b3);
      color: #fff;
      font-weight: 600;
      border: none;
      padding: 12px;
      border-radius: 10px;
      margin-top: 20px;
      cursor: pointer;
      font-size: 16px;
      transition: all 0.2s;
    }
    button[type="submit"]:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0,123,255,0.3);
    }
    .message, .errors {
      border-radius: 10px;
      padding: 12px 15px;
      margin-bottom: 15px;
      font-size: 14px;
    }
    .message {
      background: #e7f8ef;
      border-left: 5px solid #2ecc71;
      color: #2b6e45;
    }
    .errors {
      background: #fdecec;
      border-left: 5px solid #ff6b6b;
      color: #6b1b1b;
    }
    .back-link {
      text-align: center;
      margin-top: 18px;
    }
    .back-link a {
      text-decoration: none;
      color: #007bff;
      font-weight: 600;
      transition: color 0.2s;
    }
    .back-link a:hover {
      color: #0056b3;
    }
    form {
    max-width: 600px;
    margin: 0 auto;
    background: #ffffff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.08);
}

label {
    color: #222;
    margin-bottom: 6px;
}

input.form-control {
    height: 50px;
    border-radius: 10px;
    border: 1px solid #d0d7e2;
}

input.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
}

  </style>
</head>
<body>
  <header class="topbar">
    <div class="brand">
      <h1>INJEZI</h1>
      <p class="muted">Add New Product</p>
    </div>
    <nav>
      <a href="index.php" class="nav-item">Dashboard</a>
      <a href="add_product.php" class="nav-item active">Add Product</a>
      <a href="manage_stock.php" class="nav-item">Stock</a>
      <a href="sell.php" class="nav-item">Sell</a>
      <a href="view_sales.php" class="nav-item">Sales</a>
    </nav>
    <div class="profile-dropdown">
  <button class="dropdown-btn">
    <img src="images/users/<?php echo htmlspecialchars($user['profile_photo']); ?>" alt="User" class="profile-pic">
    <span><?php echo htmlspecialchars($user['full_name']); ?></span>
  </button>
  <div class="dropdown-content">
    <a href="profile.php">👤 View Profile</a>
    <a href="logout.php">🚪 Logout</a>
  </div>
</div>
  </header>

  <main class="container">
    <div class="add-container">
      <h2>📦 Add New Product</h2>
      <p class="subtext">Fill out the form below to register a new item in your stock.</p>

      <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
        <div class="errors">
          <ul>
            <?php foreach ($errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="POST" class="mt-4" onsubmit="return validateForm();" novalidate>
      
    <div class="mb-3">
        <label for="name" class="form-label fw-semibold">Product Name</label>
        <input type="text" class="form-control form-control-lg" id="name" name="name" placeholder="e.g. Phone Case" required>
    </div>

    <div class="mb-3">
        <label for="cost_price" class="form-label fw-semibold">Cost Price (RWF)</label>
        <input type="number" step="0.01" class="form-control form-control-lg" id="cost_price" name="cost_price" placeholder="0.00" required>
    </div>

    <div class="mb-3">
        <label for="selling_price" class="form-label fw-semibold">Selling Price (RWF)</label>
        <input type="number" step="0.01" class="form-control form-control-lg" id="selling_price" name="selling_price" placeholder="0.00" required>
    </div>

    <div class="mb-3">
        <label for="quantity" class="form-label fw-semibold">Quantity</label>
        <input type="number" class="form-control form-control-lg" id="quantity" name="quantity" placeholder="0" required>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-3 mt-3 fw-bold">+ Add Product</button>
</form>

    </div>
     <footer class="footer">
      <p>© <?= date('Y') ?> INJEZI — Built by Nguweneza Etienne</p>
    </footer>
  </main>

  <script>
  function validateForm() {
    const name = document.querySelector('input[name="name"]').value.trim();
    const cp = parseFloat(document.getElementById('cost_price').value);
    const sp = parseFloat(document.getElementById('selling_price').value);
    const qty = parseInt(document.getElementById('quantity').value, 10);

    if (!name) { alert('Product name is required.'); return false; }
    if (isNaN(cp) || cp < 0) { alert('Cost price must be a number >= 0.'); return false; }
    if (isNaN(sp) || sp < 0) { alert('Selling price must be a number >= 0.'); return false; }
    if (isNaN(qty) || qty < 0) { alert('Quantity must be an integer >= 0.'); return false; }
    return true;
  }
  </script>
</body>
</html>
