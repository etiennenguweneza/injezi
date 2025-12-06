<?php
require_once 'includes/db_connect.php';
require_once 'includes/auth_check.php';

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile - INJEZI</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    body {
      background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
      font-family: "Poppins", sans-serif;
      margin: 0;
      padding: 0;
      color: #333;
    }

    .profile-container {
      max-width: 700px;
      margin: 80px auto;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      overflow: hidden;
      animation: fadeIn 0.5s ease;
    }

    .profile-header {
      background: linear-gradient(135deg, #007bff, #6f42c1);
      color: #fff;
      text-align: center;
      padding: 50px 20px;
      position: relative;
    }

    .profile-header img {
      width: 130px;
      height: 130px;
      border-radius: 50%;
      border: 5px solid #fff;
      object-fit: cover;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    .profile-header h2 {
      margin-top: 15px;
      font-size: 24px;
      font-weight: 600;
    }

    .profile-header p {
      margin: 5px 0;
      font-size: 15px;
      opacity: 0.9;
    }

    .profile-body {
      padding: 30px 40px;
    }

    .profile-body h3 {
      border-left: 4px solid #0d6efd;
      padding-left: 10px;
      color: #0d6efd;
      margin-bottom: 15px;
    }

    .profile-detail {
      margin: 10px 0;
      display: flex;
      justify-content: space-between;
      border-bottom: 1px solid #eee;
      padding-bottom: 8px;
    }

    .profile-detail strong {
      color: #444;
    }

    .back-btn {
      display: inline-block;
      margin: 20px auto 40px;
      background: #0d6efd;
      color: white;
      padding: 10px 25px;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s;
    }

    .back-btn:hover {
      background: #0056b3;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* 📱 Responsive */
    @media (max-width: 768px) {
      .profile-container {
        margin: 40px 10px;
      }
      .profile-body {
        padding: 20px;
      }
      .profile-header h2 {
        font-size: 20px;
      }
    }
  </style>
</head>
<body>

  <div class="profile-container">
    <div class="profile-header">
      <img src="images/users/<?php echo htmlspecialchars($user['profile_photo']); ?>" alt="Profile Photo">
      <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
      <p><?php echo htmlspecialchars($user['role']); ?></p>
    </div>

    <div class="profile-body">
      <h3>Personal Information</h3>
      <div class="profile-detail"><strong>Email:</strong> <span><?php echo htmlspecialchars($user['email']); ?></span></div>
      <div class="profile-detail"><strong>Phone:</strong> <span><?php echo htmlspecialchars($user['phone']); ?></span></div>
      <div class="profile-detail"><strong>Date of Birth:</strong> <span><?php echo htmlspecialchars($user['dob']); ?></span></div>
      <div class="profile-detail"><strong>Role:</strong> <span><?php echo htmlspecialchars($user['role']); ?></span></div>

      <a href="index.php" class="back-btn">⬅ Back to Dashboard</a>
    </div>
  </div>
 
</body>
</html>
