<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth_check.php';
$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param('i', $_GET['delete']);
    $stmt->execute();
    $stmt->close();
    header('Location: manage_stock.php');
    exit;
}

// Handle update from modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product_id'])) {
    $id = (int)$_POST['update_product_id'];
    $name = trim($_POST['update_name']);
    $cp = (float)$_POST['update_cost_price'];
    $sp = (float)$_POST['update_selling_price'];
    $qty = (int)$_POST['update_quantity'];

    $stmt = $conn->prepare("UPDATE products SET name=?, cost_price=?, selling_price=?, quantity=? WHERE id=?");
    $stmt->bind_param('sddii', $name, $cp, $sp, $qty, $id);
    $stmt->execute();
    $stmt->close();
    header('Location: manage_stock.php');
    exit;
}

// Fetch all products
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
$totalProducts = getProductsCount($conn);
$totalStockValue = getTotalStockValue($conn);
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Stock | INJEZI</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
.panel h2{margin-bottom:16px;color:#007bff}
.table-container{overflow-x:auto;}
.table{width:100%;border-collapse:collapse;font-size:14px;}
.table th, .table td{padding:10px 12px;border-bottom:1px solid #e2e8f0;}
.table th{background:#007bff;color:#fff;border-radius:6px;text-align:left;}
.table tbody tr:hover{background:#f1f5f9;}
.btn-action{padding:6px 12px;border:none;border-radius:8px;color:#fff;cursor:pointer;font-size:13px;margin-right:6px;transition:all 0.2s;}
.btn-edit{background:#17a2b8;}
.btn-edit:hover{background:#138496;}
.btn-delete{background:#dc3545;}
.btn-delete:hover{background:#c82333;}
.card-summary{display:flex;gap:18px;margin-bottom:20px;}
.card{flex:1;background:#fff;padding:18px;border-radius:14px;box-shadow:0 8px 30px rgba(11,20,40,0.06);}
.card-title{font-size:13px;color:#6c757d}
.card-value{font-size:20px;font-weight:700;margin-top:6px}
.modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);justify-content:center;align-items:center;z-index:50;}
.modal-content{background:#fff;padding:30px 28px;border-radius:16px;max-width:500px;width:90%;position:relative;}
.close-modal{position:absolute;top:12px;right:14px;font-size:20px;font-weight:bold;color:#333;cursor:pointer;}
input.modal-input{width:100%;padding:10px 12px;border-radius:10px;border:1px solid #ced4da;margin-top:6px;margin-bottom:12px;}
button.modal-save{width:100%;padding:10px;background:#007bff;color:#fff;border:none;border-radius:10px;font-size:15px;cursor:pointer;transition:all 0.2s;}
button.modal-save:hover{background:#0056b3;}
.search-box{margin-bottom:12px;}
.search-box input{width:100%;padding:10px 12px;border-radius:10px;border:1px solid #ced4da;}
/* Enhanced Edit Modal Styling */
.enhanced-modal {
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
  animation: modalPop 0.3s ease-out;
}
@keyframes modalPop {
  from { transform: scale(0.9); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
.modal-title {
  text-align: center;
  margin-bottom: 18px;
  font-size: 20px;
  font-weight: 600;
  color: #007bff;
  letter-spacing: 0.3px;
}
.modal-form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.form-group {
  display: flex;
  flex-direction: column;
}
.form-group label {
  font-size: 14px;
  font-weight: 500;
  color: #333;
  margin-bottom: 6px;
}
.modal-input {
  border: 1px solid #ced4da;
  border-radius: 10px;
  padding: 10px 12px;
  font-size: 14px;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.modal-input:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 3px rgba(0,123,255,0.15);
  outline: none;
}
.modal-actions {
  margin-top: 18px;
}
.modal-save {
  width: 100%;
  padding: 10px 0;
  background: linear-gradient(135deg, #007bff, #0056b3);
  color: #fff;
  font-weight: 600;
  border: none;
  border-radius: 10px;
  font-size: 15px;
  cursor: pointer;
  transition: background 0.3s ease;
}
.modal-save:hover {
  background: linear-gradient(135deg, #0056b3, #004085);
}

</style>
</head>
<body>
<header class="topbar">
  <div class="brand">
    <h1>INJEZI</h1>
    <p class="muted">Manage Stock</p>
  </div>
  <nav>
    <a href="index.php" class="nav-item">Dashboard</a>
    <a href="add_product.php" class="nav-item">Add Product</a>
    <a href="manage_stock.php" class="nav-item active">Stock</a>
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
</style>
</header>

<main class="container">
  <div class="card-summary">
    <div class="card">
      <div class="card-title">Total Products</div>
      <div class="card-value"><?= $totalProducts ?></div>
    </div>
    <div class="card">
      <div class="card-title">Total Stock Value (RWF)</div>
      <div class="card-value"><?= number_format($totalStockValue,2) ?></div>
    </div>
  </div>

  <div class="panel">
    <h2>Stock Items</h2>
    <div class="search-box">
      <input type="text" id="searchInput" placeholder="Search products..." onkeyup="filterTable()">
    </div>
    <div class="table-container">
      <table class="table" id="stockTable">
        <thead>
          <tr><th>Name</th><th>Cost Price</th><th>Selling Price</th><th>Quantity</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php while($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= number_format($row['cost_price'],2) ?> RWF</td>
              <td><?= number_format($row['selling_price'],2) ?> RWF</td>
              <td><?= (int)$row['quantity'] ?></td>
              <td>
                <button class="btn-action btn-edit" onclick="openModal(<?= $row['id'] ?>,'<?= addslashes($row['name']) ?>',<?= $row['cost_price'] ?>,<?= $row['selling_price'] ?>,<?= $row['quantity'] ?>)">Edit</button>
                <a href="?delete=<?= $row['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
   <footer class="footer">
      <p>© <?= date('Y') ?> INJEZI — Built by Nguweneza Etienne</p>
    </footer>
</main>


<div class="modal" id="editModal">
  <div class="modal-content enhanced-modal">
    <span class="close-modal" onclick="closeModal()">&times;</span>
    <h3 class="modal-title">✏️ Edit Product</h3>
    <form method="POST" class="modal-form">
      <input type="hidden" name="update_product_id" id="update_product_id">

      <div class="form-group">
        <label for="update_name">Product Name</label>
        <input type="text" name="update_name" id="update_name" class="modal-input" placeholder="Enter product name" required>
      </div>

      <div class="form-group">
        <label for="update_cost_price">Cost Price (RWF)</label>
        <input type="number" step="0.01" name="update_cost_price" id="update_cost_price" class="modal-input" placeholder="Enter cost price" required>
      </div>

      <div class="form-group">
        <label for="update_selling_price">Selling Price (RWF)</label>
        <input type="number" step="0.01" name="update_selling_price" id="update_selling_price" class="modal-input" placeholder="Enter selling price" required>
      </div>

      <div class="form-group">
        <label for="update_quantity">Quantity</label>
        <input type="number" name="update_quantity" id="update_quantity" class="modal-input" placeholder="Enter quantity" required>
      </div>

      <div class="modal-actions">
        <button type="submit" class="modal-save">💾 Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id,name,cp,sp,qty){
  document.getElementById('update_product_id').value = id;
  document.getElementById('update_name').value = name;
  document.getElementById('update_cost_price').value = cp;
  document.getElementById('update_selling_price').value = sp;
  document.getElementById('update_quantity').value = qty;
  document.getElementById('editModal').style.display='flex';
}
function closeModal(){
  document.getElementById('editModal').style.display='none';
}
window.onclick = function(event){
  if(event.target==document.getElementById('editModal')) closeModal();
}
function filterTable(){
  let input = document.getElementById('searchInput').value.toLowerCase();
  let rows = document.getElementById('stockTable').getElementsByTagName('tr');
  for(let i=1;i<rows.length;i++){
    let cells = rows[i].getElementsByTagName('td');
    rows[i].style.display = cells[0].innerText.toLowerCase().includes(input)?'':'none';
  }
}
</script>
</body>
</html>