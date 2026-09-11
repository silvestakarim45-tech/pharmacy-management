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
header('Content-Disposition: attachment; filename="medicines_report_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, ['Medicine ID', 'Medicine Name', 'Category', 'Description', 'Quantity', 'Buying Price (TZS)', 'Selling Price (TZS)', 'Expiry Date', 'Status']);

// Get medicines data
$sql = "SELECT * FROM medicines ORDER BY medicine_name ASC";
$result = mysqli_query($conn, $sql);

// Write data to CSV
while($row = mysqli_fetch_assoc($result)){
    fputcsv($output, [
        $row['medicine_id'],
        $row['medicine_name'],
        $row['category'],
        $row['description'],
        $row['quantity'],
        $row['buying_price'],
        $row['selling_price'],
        $row['expiry_date'],
        $row['status']
    ]);
}

fclose($output);
exit();
?>
