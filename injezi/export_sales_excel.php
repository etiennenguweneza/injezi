<?php
require_once 'includes/db_connect.php';
require_once 'includes/auth_check.php';

// Set headers for Excel file download
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=sales_report_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Output table header
echo "Product\tCustomer\tQuantity\tTotal Sale\tProfit\tDate\n";

// Fetch data
$sql = "SELECT s.*, p.name AS product_name FROM sales s LEFT JOIN products p ON s.product_id = p.id ORDER BY s.created_at DESC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    echo $row['product_name'] . "\t" .
         $row['customer_name'] . "\t" .
         $row['quantity_sold'] . "\t" .
         number_format($row['total_sale'],2) . "\t" .
         number_format($row['gain'],2) . "\t" .
         $row['created_at'] . "\n";
}
exit;
?>
