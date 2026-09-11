<?php
session_start();
include("config.php");

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

$customer_id = $_GET['id'] ?? 0;

// Get customer details
$sql = "SELECT * FROM customers WHERE customer_id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$customer_result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($customer_result) == 0){
    die("Customer not found");
}

$customer = mysqli_fetch_assoc($customer_result);

// Get purchase history
$sql = "SELECT o.*, COUNT(oi.order_item_id) as total_items
        FROM orders o
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        WHERE o.customer_id = ?
        GROUP BY o.order_id
        ORDER BY o.order_date DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$orders_result = mysqli_stmt_get_result($stmt);

// Get total spent
$sql = "SELECT SUM(total_amount) as total_spent FROM orders WHERE customer_id = ? AND status = 'completed'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$spent_result = mysqli_stmt_get_result($stmt);
$spent_data = mysqli_fetch_assoc($spent_result);
$total_spent = $spent_data['total_spent'] ?: 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Purchase History - Pharmacy Management</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="admin-panel">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>🏥 Pharmacy</h1>
            <p>Admin Portal</p>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                <li><a href="medicine.php"><i class="fas fa-pills"></i> <span>Dawa</span></a></li>
                <li><a href="inventory_management.php"><i class="fas fa-boxes"></i> <span>Inventory</span></a></li>
                <li><a href="stock_alerts.php"><i class="fas fa-bell"></i> <span>Stock Alerts</span></a></li>
                <li><a href="admin_customers.php"><i class="fas fa-user-friends"></i> <span>Wateja</span></a></li>
                <li><a href="admin_orders.php"><i class="fas fa-shopping-bag"></i> <span>Agizo</span></a></li>
                <li><a href="sales.php"><i class="fas fa-cash-register"></i> <span>Mauzo</span></a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-header">
            <h2>📋 Historia ya Ununuzi</h2>
            <div class="user-info">
                <span>Admin: <?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="container" style="margin-bottom: 30px;">
            <div class="header">
                <h2>👤 Maelezo ya Mteja</h2>
            </div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                <div class="alert alert-info">
                    <h3>Jina</h3>
                    <p style="font-size: 18px; font-weight: bold;"><?php echo $customer['customer_name']; ?></p>
                </div>
                <div class="alert alert-success">
                    <h3>Jumla Yaliyotumwa</h3>
                    <p style="font-size: 24px; font-weight: bold;">TZS <?php echo number_format($total_spent, 2); ?></p>
                </div>
                <div class="alert alert-warning">
                    <h3>Simu</h3>
                    <p style="font-size: 18px; font-weight: bold;"><?php echo $customer['phone'] ?: '-'; ?></p>
                </div>
                <div class="alert alert-info">
                    <h3>Email</h3>
                    <p style="font-size: 18px; font-weight: bold;"><?php echo $customer['email'] ?: '-'; ?></p>
                </div>
            </div>
        </div>

        <!-- Purchase History -->
        <div class="container">
            <div class="header">
                <h2>🛒 Historia ya Agizo</h2>
            </div>

            <?php if(mysqli_num_rows($orders_result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Tarehe</th>
                        <th>Idadi ya Vitu</th>
                        <th>Jumla</th>
                        <th>Status</th>
                        <th>Matendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = mysqli_fetch_assoc($orders_result)): ?>
                    <tr>
                        <td>#<?php echo $order['order_id']; ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                        <td><?php echo $order['total_items']; ?></td>
                        <td>TZS <?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <?php
                            $status_class = '';
                            switch($order['status']){
                                case 'pending': $status_class = 'alert-warning'; break;
                                case 'processing': $status_class = 'alert-info'; break;
                                case 'completed': $status_class = 'alert-success'; break;
                                case 'cancelled': $status_class = 'alert-danger'; break;
                            }
                            ?>
                            <span class="alert <?php echo $status_class; ?>" style="display: inline-block; padding: 5px 10px; font-size: 12px;">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-info" style="padding: 5px 10px; font-size: 12px;">Angalia</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="alert alert-info">Hakuna historia ya ununuzi kwa mteja huu</div>
            <?php endif; ?>

            <div style="margin-top: 20px;">
                <a href="admin_customers.php" class="btn btn-warning">← Rudi kwenye Orodha ya Wateja</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
