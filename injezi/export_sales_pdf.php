<?php
require_once 'includes/db_connect.php';
require_once 'includes/auth_check.php';
require_once __DIR__ . '/dompdf/autoload.inc.php';
$user_id = $_SESSION ['user_id'];
$user = $conn->query("SELECT full_name, role FROM users WHERE id = $user_id")->fetch_assoc();



// Use Dompdf for PDF export
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$logoPath = realpath(__DIR__ . '/images/logo.png');

// Verify the file path (optional debug)
if (!file_exists($logoPath)) {
    // Fallback to an online image if local fails
    $logoPath = 'https://via.placeholder.com/100x100.png?text=INJEZI';
}




$html = '
<div style="text-align:center; margin-bottom:20px;">
    <img src="file:///' . str_replace('\\', '/', $logoPath) . '" width="100" alt="INJEZI Logo">
    <h2 style="margin-top:10px;">INJEZI Sales Report</h2>
</div>';


$html = '<h2 style="text-align:center;">INJEZI Sales Report</h2>';
$html .= '<table border="1" cellspacing="0" cellpadding="5" width="100%">
<tr style="background-color:#007bff;color:#fff;">
<th>Product</th><th>Customer</th><th>Quantity</th><th>Total Sale</th><th>Profit</th><th>Date</th>
</tr>';

$sql = "SELECT s.*, p.name AS product_name FROM sales s LEFT JOIN products p ON s.product_id = p.id ORDER BY s.created_at DESC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $html .= '<tr>
        <td>' . htmlspecialchars($row['product_name']) . '</td>
        <td>' . htmlspecialchars($row['customer_name']) . '</td>
        <td>' . (int)$row['quantity_sold'] . '</td>
        <td>' . number_format($row['total_sale'],2) . ' RWF</td>
        <td>' . number_format($row['gain'],2) . ' RWF</td>
        <td>' . date('Y-m-d H:i', strtotime($row['created_at'])) . '</td>
    </tr>';
}
$html .= '</table>';
$footer = '
<br><hr>
<p style="font-size:12px;text-align:center;color:#555;">
System built by <strong>Nguweneza Etienne</strong>.<br>
Sales report downloaded by <strong>' . htmlspecialchars($user['full_name']) . '</strong> 
(' . htmlspecialchars($user['role']) . ') on ' . date('Y-m-d H:i:s') . '.
</p>';
$html .= $footer;

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("sales_report_" . date('Y-m-d') . ".pdf");
exit;
?>
