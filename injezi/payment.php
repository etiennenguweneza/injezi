<?php
session_start();
include('includes/db_connect.php');

// Get logged in user
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$query = $conn->prepare("SELECT * FROM users WHERE username=?");
$query->bind_param('s', $username);
$query->execute();
$user = $query->get_result()->fetch_assoc();

if (!$user) {
    die("User not found");
}

$ref = strtoupper(substr(md5(time() . $username), 0, 8)); // unique reference
$amount = 5000; // RWF fixed payment
$conn->query("UPDATE users SET payment_reference='$ref' WHERE username='$username'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>INJEZI | Payment</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
  body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #007bff, #00aaff);
    color: #333;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
  }
  .container {
    background: white;
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    width: 400px;
    text-align: center;
  }
  h2 {
    color: #007bff;
    margin-bottom: 20px;
  }
  .info-box {
    background: #f7f9fc;
    border-radius: 10px;
    padding: 15px;
    margin: 15px 0;
  }
  .info-box strong {
    display: block;
    color: #007bff;
    font-size: 18px;
  }
  .btn {
    background: #007bff;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    cursor: pointer;
    transition: 0.3s;
  }
  .btn:hover {
    background: #0056b3;
  }
  footer {
    margin-top: 15px;
    font-size: 13px;
    color: gray;
  }
</style>
</head>
<body>
  <div class="container">
    <h2>Activate Your INJEZI Account</h2>
    <p>Please make a payment of <strong>RWF <?php echo $amount; ?></strong> to continue.</p>
    <div class="info-box">
      <p><strong>Pay To:</strong> MTN MoMo: <b>078XXXXXXXX</b></p>
      <p><strong>Account Name:</strong> Nguweneza Etienne</p>
      <p><strong>Payment Reference:</strong> <?php echo $ref; ?></p>
    </div>
    <form action="payment_confirm.php" method="POST">
      <input type="hidden" name="ref" value="<?php echo $ref; ?>">
      <button type="submit" class="btn">I’ve Paid</button>
    </form>
    <footer>INJEZI Secure Payment Portal</footer>
  </div>
</body>
</html>
