<?php
require_once 'includes/db_connect.php';
require_once 'includes/auth_check.php';
$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
// --- Add missing functions directly ---
function getTotalSales($conn) {
    $result = $conn->query("SELECT SUM(total_sale) AS total FROM sales");
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

function getTotalGain($conn) {
    $result = $conn->query("SELECT SUM(gain) AS total FROM sales");
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// Fetch sales
$salesResult = $conn->query("SELECT s.*, p.name as product_name FROM sales s LEFT JOIN products p ON s.product_id = p.id ORDER BY s.created_at DESC");
$totalSales = getTotalSales($conn);
$totalGain = getTotalGain($conn);
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sales | INJEZI</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
.card-summary{display:flex;gap:18px;margin-bottom:20px;}
.card{flex:1;background:#fff;padding:18px;border-radius:14px;box-shadow:0 8px 30px rgba(11,20,40,0.06);}
.card-title{font-size:13px;color:#6c757d}
.card-value{font-size:20px;font-weight:700;margin-top:6px}
.panel{background:#fff;padding:25px;border-radius:16px;box-shadow:0 10px 40px rgba(11,20,40,0.06);}
.panel h2{color:#007bff;margin-bottom:15px;text-align:center}
.table-container{overflow-x:auto;margin-top:15px;}
.table{width:100%;border-collapse:collapse;font-size:14px;}
.table th, .table td{padding:10px 12px;border-bottom:1px solid #e2e8f0;}
.table th{background:#007bff;color:#fff;text-align:left;border-radius:6px;}
.search-box{margin-bottom:12px;}
.search-box input{width:100%;padding:10px 12px;border-radius:10px;border:1px solid #ced4da;}
</style>
</head>
<body>
<header class="topbar">
  <div class="brand">
    <h1>INJEZI</h1>
    <p class="muted">View Sales</p>
  </div>
  <nav>
    <a href="index.php" class="nav-item">Dashboard</a>
    <a href="add_product.php" class="nav-item">Add Product</a>
    <a href="manage_stock.php" class="nav-item">Stock</a>
    <a href="sell.php" class="nav-item">Sell</a>
    <a href="view_sales.php" class="nav-item active">Sales</a>
    

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
      <div class="card-title">Total Sales</div>
      <div class="card-value"><?= number_format($totalSales,2) ?> RWF</div>
    </div>
    <div class="card">
      <div class="card-title">Total Profit</div>
      <div class="card-value"><?= number_format($totalGain,2) ?> RWF</div>
    </div>
  </div>

  <div class="panel">
    <h2>Sales Records</h2>
    <div class="search-box">
      <input type="text" id="searchInput" placeholder="Search sales..." onkeyup="filterTable()">
    </div>
    <div style="margin-bottom:15px; display:flex; gap:10px;">
  <a href="export_sales_excel.php" class="btn" style="background:#28a745;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none;">📊 Export to Excel</a>
  <a href="export_sales_pdf.php" class="btn" style="background:#dc3545;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none;">📄 Export to PDF</a>
</div>

    <div class="table-container">
      <table class="table" id="salesTable">
        <thead>
          <tr><th>Product</th><th>Customer</th><th>Quantity</th><th>Total Sale</th><th>Profit</th><th>Date</th></tr>
        </thead>
        <tbody>
          <?php while($row=$salesResult->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['product_name']) ?></td>
            <td><?= htmlspecialchars($row['customer_name']) ?></td>
            <td><?= (int)$row['quantity_sold'] ?></td>
            <td><?= number_format($row['total_sale'],2) ?> RWF</td>
            <td><?= number_format($row['gain'],2) ?> RWF</td>
            <td><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></td>
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


<script>
function filterTable(){
  let input = document.getElementById('searchInput').value.toLowerCase();
  let rows = document.getElementById('salesTable').getElementsByTagName('tr');
  for(let i=1;i<rows.length;i++){
    let cells = rows[i].getElementsByTagName('td');
    rows[i].style.display = Array.from(cells).some(td=>td.innerText.toLowerCase().includes(input))?'':'none';
  }
}
</script>
</body>
</html>
