<?php
session_start();
include("config.php");

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$sale_id = $_GET['id'] ?? 0;

// Get sale details
$sql = "SELECT s.*, m.medicine_name, m.selling_price, u.fullname as seller_name
        FROM sales s
        JOIN medicines m ON s.medicine_id = m.medicine_id
        JOIN users u ON s.seller_id = u.user_id
        WHERE s.sale_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $sale_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) == 0){
    die("Sale not found");
}

$sale = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Receipt #<?php echo $sale_id; ?> - Pharmacy</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .receipt {
            background: white;
            padding: 30px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .receipt-header h1 {
            margin: 0;
            color: #3498db;
            font-size: 24px;
        }
        .receipt-header p {
            margin: 5px 0;
            color: #666;
        }
        .receipt-details {
            margin-bottom: 20px;
        }
        .receipt-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .receipt-details th, .receipt-details td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .receipt-details th {
            background: #f8f8f8;
            font-weight: bold;
        }
        .receipt-total {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px dashed #333;
        }
        .receipt-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px dashed #333;
            color: #666;
            font-size: 12px;
        }
        .print-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background: #3498db;
            color: white;
            text-align: center;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            margin-top: 20px;
            cursor: pointer;
            font-size: 16px;
        }
        .print-btn:hover {
            background: #2980b9;
        }
        @media print {
            body {
                background: white;
                margin: 0;
                padding: 0;
            }
            .receipt {
                box-shadow: none;
                border: none;
            }
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="receipt">
    <div class="receipt-header">
        <h1>🏥 PHARMACY</h1>
        <p>Management System</p>
        <p>Dar es Salaam, Tanzania</p>
        <p>Tel: +255 123 456 789</p>
    </div>

    <div class="receipt-details">
        <p><strong>Receipt No:</strong> #<?php echo str_pad($sale_id, 6, '0', STR_PAD_LEFT); ?></p>
        <p><strong>Date:</strong> <?php echo date('Y-m-d H:i', strtotime($sale['sale_date'])); ?></p>
        <p><strong>Sold By:</strong> <?php echo $sale['seller_name']; ?></p>
    </div>

    <div class="receipt-details">
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo $sale['medicine_name']; ?></td>
                    <td><?php echo $sale['quantity_sold']; ?></td>
                    <td>TZS <?php echo number_format($sale['selling_price'], 2); ?></td>
                    <td>TZS <?php echo number_format($sale['total_amount'], 2); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="receipt-total">
        <p>TOTAL: TZS <?php echo number_format($sale['total_amount'], 2); ?></p>
    </div>

    <div class="receipt-footer">
        <p>Thank you for your purchase!</p>
        <p>Please keep this receipt for warranty purposes</p>
        <p><?php echo date('Y-m-d H:i'); ?></p>
    </div>

    <button class="print-btn" onclick="window.print()">🖨️ Print Receipt</button>
</div>

</body>
</html>
