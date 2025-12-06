<?php
// includes/functions.php
require_once 'db_connect.php';

function getProductsCount($conn) {
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM products");
    $r = $res ? $res->fetch_assoc() : null;
    return $r ? (int)$r['cnt'] : 0;
}

function getLowStockCount($conn, $threshold = 5) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM products WHERE quantity <= ?");
    $stmt->bind_param('i', $threshold);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $res ? (int)$res['cnt'] : 0;
}

function getTotalStockValue($conn) {
    // sum(quantity * cost_price)
    $res = $conn->query("SELECT SUM(quantity * cost_price) AS total_value FROM products");
    $r = $res ? $res->fetch_assoc() : null;
    return $r && $r['total_value'] !== null ? (float)$r['total_value'] : 0.00;
}

function getTotalGain($conn) {
    $res = $conn->query("SELECT SUM(gain) AS total_gain FROM sales");
    $r = $res ? $res->fetch_assoc() : null;
    return $r && $r['total_gain'] !== null ? (float)$r['total_gain'] : 0.00;
}

function getRecentSales($conn, $limit = 6) {
    $stmt = $conn->prepare("SELECT s.*, p.name AS product_name FROM sales s JOIN products p ON s.product_id = p.id ORDER BY s.date DESC LIMIT ?");
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}
?>
