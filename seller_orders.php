<?php
session_start();
include("config.php");

// Check if user is logged in and is seller
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller'){
    header("Location: login.php");
    exit();
}

// Handle order processing
if(isset($_POST['process_order'])){
    $order_id = $_POST['order_id'];
    $update_order = "UPDATE orders SET status='processing' WHERE order_id=?";
    $stmt = mysqli_prepare($conn, $update_order);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    header("Location: seller_orders.php");
    exit();
}

// Handle order completion
if(isset($_POST['complete_order'])){
    $order_id = $_POST['order_id'];
    $update_order = "UPDATE orders SET status='completed' WHERE order_id=?";
    $stmt = mysqli_prepare($conn, $update_order);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    header("Location: seller_orders.php");
    exit();
}

// Get all orders
$orders_query = "SELECT o.*, c.customer_name FROM orders o
                 JOIN customers c ON o.customer_id = c.customer_id
                 ORDER BY o.order_date DESC";
$orders_result = mysqli_query($conn, $orders_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Orders - Seller</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="admin-panel">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>🏥 Pharmacy</h1>
            <p>Seller Portal</p>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="seller_dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                <li><a href="seller_sales.php"><i class="fas fa-cash-register"></i> <span>Mauzo</span></a></li>
                <li><a href="seller_medicines.php"><i class="fas fa-pills"></i> <span>Dawa</span></a></li>
                <li><a href="seller_orders.php" class="active"><i class="fas fa-shopping-bag"></i> <span>Agizo Mtandaoni</span></a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-header">
            <h2>📋 Agizo Mtandaoni</h2>
            <div class="user-info">
                <span>Karibu, <?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <div class="container">
            <div class="header">
                <h2>📋 Orodha ya Agizo</h2>
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
                            <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-info" style="padding: 5px 10px; font-size: 12px;">Angalia</a>
                            <?php if($order['status'] == 'pending'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                <button type="submit" name="process_order" class="btn btn-success" style="padding: 5px 10px; font-size: 12px;">Process</button>
                            </form>
                            <?php endif; ?>
                            <?php if($order['status'] == 'processing'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                <button type="submit" name="complete_order" class="btn btn-success" style="padding: 5px 10px; font-size: 12px;">Complete</button>
                            </form>
                            <?php endif; ?>
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
