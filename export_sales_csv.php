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
header('Content-Disposition: attachment; filename="sales_report_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, ['Sale ID', 'Medicine Name', 'Quantity Sold', 'Total Amount (TZS)', 'Sale Date', 'Seller']);

// Get sales data
$sql = "SELECT s.*, m.medicine_name, u.fullname as seller_name
        FROM sales s
        JOIN medicines m ON s.medicine_id = m.medicine_id
        JOIN users u ON s.seller_id = u.user_id
        ORDER BY s.sale_date DESC";
$result = mysqli_query($conn, $sql);

// Write data to CSV
while($row = mysqli_fetch_assoc($result)){
    fputcsv($output, [
        $row['sale_id'],
        $row['medicine_name'],
        $row['quantity_sold'],
        $row['total_amount'],
        $row['sale_date'],
        $row['seller_name']
    ]);
}

fclose($output);
exit();
?>
