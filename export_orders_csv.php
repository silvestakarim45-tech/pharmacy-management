<?php
session_start();
include("config.php");

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="orders_report_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, ['Order ID', 'Customer Name', 'Total Amount (TZS)', 'Order Date', 'Status', 'Delivery Address', 'Phone']);

// Get orders data
$sql = "SELECT o.*, c.customer_name
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        ORDER BY o.order_date DESC";
$result = mysqli_query($conn, $sql);

// Write data to CSV
while($row = mysqli_fetch_assoc($result)){
    fputcsv($output, [
        $row['order_id'],
        $row['customer_name'],
        $row['total_amount'],
        $row['order_date'],
        $row['status'],
        $row['delivery_address'],
        $row['phone']
    ]);
}

fclose($output);
exit();
?>
