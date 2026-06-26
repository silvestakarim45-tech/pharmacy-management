<?php
session_start();
include("config.php");

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

// Handle status update
if(isset($_POST['update_status'])){
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $update_order = "UPDATE orders SET status='$new_status' WHERE order_id='$order_id'";
    mysqli_query($conn, $update_order);
    header("Location: admin_orders.php");
    exit();
}

// Get all orders
$orders_query = "SELECT o.*, c.customer_name FROM orders o
                 JOIN customers c ON o.customer_id = c.customer_id
                 ORDER BY o.order_date DESC";
$orders_result = mysqli_query($conn, $orders_query);

// Filter by status if specified
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
if($status_filter){
    $orders_query = "SELECT o.*, c.customer_name FROM orders o
                     JOIN customers c ON o.customer_id = c.customer_id
                     WHERE o.status='$status_filter'
                     ORDER BY o.order_date DESC";
    $orders_result = mysqli_query($conn, $orders_query);
}

// Calculate statistics
$pending_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM orders WHERE status='pending'"));
$processing_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM orders WHERE status='processing'"));
$completed_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM orders WHERE status='completed'"));
$cancelled_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM orders WHERE status='cancelled'"));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Management - Admin</title>
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
                <li><a href="suppliers.php"><i class="fas fa-truck"></i> <span>Suppliers</span></a></li>
                <li><a href="sales_analytics.php"><i class="fas fa-chart-bar"></i> <span>Analytics</span></a></li>
                <li><a href="audit_logs.php"><i class="fas fa-history"></i> <span>Audit Logs</span></a></li>
                <li><a href="database_backup.php"><i class="fas fa-database"></i> <span>Backup</span></a></li>
                <li><a href="admin_orders.php" class="active"><i class="fas fa-shopping-bag"></i> <span>Agizo</span></a></li>
                <li><a href="admin_users.php"><i class="fas fa-users"></i> <span>Famasia</span></a></li>
                <li><a href="admin_customers.php"><i class="fas fa-user-friends"></i> <span>Wateja</span></a></li>
                <li><a href="sales.php"><i class="fas fa-cash-register"></i> <span>Mauzo</span></a></li>
                <li><a href="deily_report.php"><i class="fas fa-chart-line"></i> <span>Ripoti ya Siku</span></a></li>
                <li><a href="monthly_report.php"><i class="fas fa-calendar-alt"></i> <span>Ripoti ya Mwezi</span></a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-header">
            <h2>📋 Usimamizi wa Agizo</h2>
            <div class="user-info">
                <span>Admin: <?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="icon">📋</div>
                <h3>Zinazosubiri</h3>
                <div class="number"><?php echo $pending_count; ?></div>
            </div>
            <div class="stat-card warning">
                <div class="icon">⚙️</div>
                <h3>Zinazohifadhiwa</h3>
                <div class="number"><?php echo $processing_count; ?></div>
            </div>
            <div class="stat-card success">
                <div class="icon">✅</div>
                <h3>Zimekamilika</h3>
                <div class="number"><?php echo $completed_count; ?></div>
            </div>
            <div class="stat-card danger">
                <div class="icon">❌</div>
                <h3>Zimefutwa</h3>
                <div class="number"><?php echo $cancelled_count; ?></div>
            </div>
        </div>

        <div class="container">
            <div class="header">
                <h2>🔍 shughulikia Agizo</h2>
            </div>

            <div style="margin-bottom: 20px;">
                <a href="admin_orders.php" class="btn">Yote</a>
                <a href="admin_orders.php?status=pending" class="btn btn-warning">Zinazosubiri</a>
                <a href="admin_orders.php?status=processing" class="btn btn-info">Zinachakanywa</a>
                <a href="admin_orders.php?status=completed" class="btn btn-success">Zimekamilika</a>
                <a href="admin_orders.php?status=cancelled" class="btn btn-danger">Zimefutwa</a>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Mteja</th>
                        <th>Jumla</th>
                        <th>Status</th>
                        <th>Tarehe</th>
                        <th>Matendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = mysqli_fetch_assoc($orders_result)): ?>
                    <tr>
                        <td>#<?php echo $order['order_id']; ?></td>
                        <td><?php echo $order['customer_name']; ?></td>
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
                        <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                        <td>
                            <div class="actions">
                                <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-info" style="padding: 5px 10px; font-size: 12px;">Angalia</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>