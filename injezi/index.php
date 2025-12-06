<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth_check.php';
$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
$productsCount = getProductsCount($conn);
$lowStock = getLowStockCount($conn, 5);
$totalStockValue = getTotalStockValue($conn);
$totalGain = getTotalGain($conn);
$recentSales = getRecentSales($conn, 6);
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>INJEZI Dashboard</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <header class="topbar">
    <div class="brand">
      <h1>INJEZI</h1>
      <p class="muted">Inventory & Sales Management</p>
    </div>
    <nav>
      <a href="index.php" class="nav-item active">Dashboard</a>
      <a href="add_product.php" class="nav-item">Add Product</a>
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
  <style>.cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

/* Base Card Style (size & layout unchanged) */
.card {
  color: #ada3a3ff;
  padding: 20px;
  border-radius: 15px;
  box-shadow: 0 6px 15px rgba(0,0,0,0.1);
  text-align: center;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
  background-size: 200% 200%;
  animation: gradientMove 6s ease infinite;
}

/* Distinct animated gradient backgrounds */
.cards .card:nth-child(1) {
  background-image: linear-gradient(135deg, #007bff, #00c6ff, #007bff);
}
.cards .card:nth-child(2) {
  background-image: linear-gradient(135deg, #ff512f, #dd2476, #ff512f);
}
.cards .card:nth-child(3) {
  background-image: linear-gradient(135deg, #11998e, #38ef7d, #11998e);
}
.cards .card:nth-child(4) {
  background-image: linear-gradient(135deg, #f7971e, #ffd200, #f7971e);
}

/* Hover animation (same motion as before) */
.card:hover {
  transform: translateY(-6px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.15);
}

/* Text Styles (unchanged) */
.card-title {
  font-size: 0.9rem;
  font-weight: bold;
  margin-bottom: 8px;
  color: black;
}

.card-value {
  font-size: 1.2rem;
   font-weight: bold;
   color: Black;
}

.card-meta {
  font-size: 0.9rem;
  opacity: 0.9;
  margin-top: 5px;
  color:black;

}

/* Keyframes for animated gradient motion */
@keyframes gradientMove {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
</style>

  <main class="container">
    <section class="cards">
      <div class="card">
        <div class="card-title">Products</div>
        <div class="card-value"><?= number_format($productsCount) ?></div>
        <div class="card-meta">Total products in stock</div>
      </div>

      <div class="card">
        <div class="card-title">Low Stock</div>
        <div class="card-value"><?= number_format($lowStock) ?></div>
        <div class="card-meta">Items with ≤ 5 units</div>
      </div>

      <div class="card">
        <div class="card-title">Stock Value</div>
        <div class="card-value"><?= number_format($totalStockValue, 2) ?> RWF</div>
        <div class="card-meta">Total cost value of stock</div>
      </div>

      <div class="card highlight">
        <div class="card-title">Total Profit</div>
        <div class="card-value"><?= number_format($totalGain, 2) ?> RWF</div>
        <div class="card-meta">Profit from all sales</div>
      </div>
    </section>

    <section class="section split">
      <div class="panel">
        <h2>Recent Sales</h2>
        <table class="table">
          <thead>
            <tr><th>Date</th><th>Product</th><th>Qty</th><th>Total</th><th>Profit</th></tr>
          </thead>
          <tbody>
            <?php if (count($recentSales) === 0): ?>
              <tr><td colspan="5" class="muted">No recent sales yet.</td></tr>
            <?php else: ?>
              <?php foreach($recentSales as $r): ?>
                <tr>
                  <td><?= date('Y-m-d H:i', strtotime($r['date'])) ?></td>
                  <td><?= htmlspecialchars($r['product_name']) ?></td>
                  <td><?= (int)$r['quantity_sold'] ?></td>
                  <td><?= number_format($r['total_sale'],2) ?> RWF</td>
                  <td><?= number_format($r['gain'],2) ?> RWF</td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="panel">
        <h2>Profits This Week</h2>
        <canvas id="gainsWeekChart"></canvas>
      </div>
    </section>

    <footer class="footer">
      <p>© <?= date('Y') ?> INJEZI — Built by Nguweneza Etienne</p>
    </footer>
  </main>

<script>
// Fetch chart data via a simple inline AJAX call to an endpoint we'll create later.
// For now prepare inline labels & data from PHP (simple query aggregated by day).
<?php
// Prepare daily gains for last 7 days
$days = [];
$data = [];
$stmt = $conn->prepare("
  SELECT DATE(date) AS day, COALESCE(SUM(gain),0) AS daily_gain
  FROM sales
  WHERE date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
  GROUP BY DATE(date)
  ORDER BY DATE(date) ASC
");
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
$map = [];
while ($r = $res->fetch_assoc()) { $map[$r['day']] = (float)$r['daily_gain']; }
$stmt->close();

// build last 7 days labels
for ($i=6; $i>=0; $i--) {
  $d = date('Y-m-d', strtotime("-$i days"));
  $days[] = $d;
  $data[] = isset($map[$d]) ? $map[$d] : 0;
}
?>
const ctx = document.getElementById('gainsWeekChart').getContext('2d');
const chart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($days) ?>,
    datasets: [{
      label: 'Daily Gain (RWF)',
      data: <?= json_encode($data) ?>,
      borderWidth: 1,
      backgroundColor: 'rgba(0,123,255,0.6)',
      borderColor: 'rgba(0,123,255,1)'
    }]
  },
  options: {
    scales: { y: { beginAtZero: true } },
    plugins: { legend: { display: false } }
  }
});
</script>

</body>
</html>
