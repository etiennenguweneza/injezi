<?php
// includes/db_connect.php
$host = "localhost";
$user = "root";
$password = ""; // your MySQL password
$dbname = "smartbiz_db";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
