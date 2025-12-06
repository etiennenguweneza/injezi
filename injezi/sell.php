<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth_check.php';
$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
$message = '';
$errors = [];

// Fetch products for selection
$productsResult = $conn->query("SELECT * FROM products WHERE quantity>0 ORDER BY name ASC");
$products = [];
while($row = $productsResult->fetch_assoc()) $products[] = $row;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $customer_name = trim($_POST['customer_name'] ?? '');
    $quantity_sold = (int)($_POST['quantity_sold'] ?? 0);

    // Validate
    if ($product_id <= 0) $errors[] = 'Please select a product.';
    if ($quantity_sold <= 0) $errors[] = 'Quantity sold must be at least 1.';

    $stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $product = $res->fetch_assoc();
    $stmt->close();

    if (!$product) $errors[] = 'Selected product not found.';
    if ($quantity_sold > $product['quantity']) $errors[] = 'Not enough stock available.';

    if (empty($errors)) {
        $total_sale = $product['selling_price'] * $quantity_sold;
        $gain = ($product['selling_price'] - $product['cost_price']) * $quantity_sold;

        // Insert into sales
        $stmt = $conn->prepare("INSERT INTO sales (product_id, customer_name, quantity_sold, total_sale, gain) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('isidd', $product_id, $customer_name, $quantity_sold, $total_sale, $gain);
        $stmt->execute();
        $stmt->close();

        // Update stock
        $stmt = $conn->prepare("UPDATE products SET quantity=quantity-? WHERE id=?");
        $stmt->bind_param('ii', $quantity_sold, $product_id);
        $stmt->execute();
        $stmt->close();

        $message = '✅ Sale recorded successfully!';
    }
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sell Product | INJEZI</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
.panel{max-width:600px;margin:30px auto;background:#fff;padding:30px;border-radius:16px;box-shadow:0 10px 40px rgba(11,20,40,0.06);}
.panel h2{color:#007bff;text-align:center;margin-bottom:15px}
.form-group{margin-bottom:15px}
label{font-weight:600;color:#333;display:block;margin-bottom:6px}
input, select{width:100%;padding:10px 14px;border-radius:10px;border:1px solid #ced4da;font-size:15px;transition:all 0.2s}
input:focus, select:focus{border-color:#007bff;box-shadow:0 0 0 3px rgba(0,123,255,0.1);outline:none}
button[type="submit"]{width:100%;padding:12px;background:#007bff;color:#fff;font-size:16px;font-weight:600;border:none;border-radius:10px;cursor:pointer;transition:all 0.2s;margin-top:10px}
button[type="submit"]:hover{background:#0056b3;transform:translateY(-2px);box-shadow:0 6px 16px rgba(0,123,255,0.3)}
.message{background:#e7f8ef;border-left:5px solid #2ecc71;color:#2b6e45;padding:12px;border-radius:10px;margin-bottom:15px}
.errors{background:#fdecec;border-left:5px solid #ff6b6b;color:#6b1b1b;padding:12px;border-radius:10px;margin-bottom:15px}
.back-link{text-align:center;margin-top:18px}
.back-link a{text-decoration:none;color:#007bff;font-weight:600;transition:color 0.2s}
.back-link a:hover{color:#0056b3}
</style>
</head>
<body>
<header class="topbar">
  <div class="brand">
    <h1>INJEZI</h1>
    <p class="muted">Record a Sale</p>
  </div>
  <nav>
    <a href="index.php" class="nav-item">Dashboard</a>
    <a href="add_product.php" class="nav-item">Add Product</a>
    <a href="manage_stock.php" class="nav-item">Stock</a>
    <a href="sell.php" class="nav-item active">Sell</a>
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
.sale-form {
  max-width: 600px;
  margin: 0 auto;
  background: #fff;
  padding: 35px;
  border-radius: 15px;
  box-shadow: 0 6px 25px rgba(0,0,0,0.08);
}

.sale-form label {
  color: #222;
  margin-bottom: 6px;
}

.sale-form input.form-control,
.sale-form select.form-select {
  height: 50px;
  border-radius: 10px;
  border: 1px solid #d0d7e2;
}

.sale-form input:focus,
.sale-form select:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
}

.sale-form button {
  font-size: 1rem;
  border-radius: 10px;
}

</style>
</header>

<main class="container">
  <div class="panel">
    <h2>💰 Record a Sale</h2>
    <?php if ($message): ?><div class="message"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="errors"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

    <form method="POST" class="sale-form mt-4" onsubmit="return validateForm()">
       <div class="mb-4 text-center">
    <h4 class="fw-bold text-primary mb-1">💰 Record a Sale</h4>
    <p class="text-muted">Select a product and record the sale details below.</p>
  </div>

  <div class="mb-3">
    <label for="product" class="form-label fw-semibold">Product</label>
    <select id="product" name="product_id" class="form-select form-select-lg" required>
      <option value="">-- Select Product --</option>
      <?php
    
    $products = $conn->query("SELECT id, name, quantity FROM products ORDER BY name ASC");
    while ($row = $products->fetch_assoc()) {
        $qty = (int)$row['quantity'];
        $label = htmlspecialchars($row['name']);
        echo "<option value='{$row['id']}' data-stock='{$qty}'>{$label} ({$qty} in stock)</option>";
    }
    ?>

    </select>
  </div>

  <div class="mb-3">
    <label for="customer" class="form-label fw-semibold">Customer Name (optional)</label>
    <input type="text" id="customer" name="customer_name" class="form-control form-control-lg" placeholder="e.g. John Doe">
  </div>

  <div class="mb-3">
    <label for="quantity" class="form-label fw-semibold">Quantity Sold</label>
    <input type="number" id="quantity_sold" name="quantity_sold" class="form-control form-control-lg" placeholder="Enter quantity" required>
  </div>

  <button type="submit" class="btn btn-primary w-100 py-3 fw-bold mt-3">Record Sale</button>

  <div class="text-center mt-3">
      <center><a href= "manage_stock.php"><p style ="background: orange;
  color: white;
  padding: 10px 25px;
  border: none;
  width: 475px;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s;
">See the stock remaining</p>
    </a></center></div>
</form>
  </div>
  <footer class="footer">
      <p>© <?= date('Y') ?> INJEZI — Built by Nguweneza Etienne</p>
    </footer>
</main>
<!-- includes/footer.php -->
 

<style>
.INJEZI-footer {
  background: #ffffff;
  border-top: 1px solid #e5e7eb;
  text-align: center;
  padding: 8px 0;
  font-family: "Poppins", sans-serif;
  font-size: 0.85rem;
  color: #6b7280;
  width: 100%;
  margin-top: auto;
  box-shadow: 0 -1px 4px rgba(0, 0, 0, 0.03);
}

.INJEZI-footer .brand {
  color: #2563eb;
  font-weight: 600;
}

.INJEZI-footer strong {
  color: #2563eb;
  font-weight: 500;
}

html, body {
  height: 100%;
}

body {
  display: flex;
  flex-direction: column;
}
</style>

<script>
function validateForm(){
  let product = document.querySelector('select[name="product_id"]').value;
  let qty = parseInt(document.querySelector('input[name="quantity_sold"]').value);
  if(!product){alert('Please select a product.'); return false;}
  if(isNaN(qty) || qty<1){alert('Quantity sold must be at least 1.'); return false;}
  return true;
}
</script>
</body>
</html>