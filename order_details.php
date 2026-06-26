<?php
session_start();
include("config.php");
include("functions.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$order_id = $_GET['id'];

// Get order details
$order_query = "SELECT o.*, c.customer_name, c.phone, c.email, c.address
                FROM orders o
                JOIN customers c ON o.customer_id = c.customer_id
                WHERE o.order_id=?";
$stmt = mysqli_prepare($conn, $order_query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($order_result);

// Get order items
$items_query = "SELECT oi.*, m.medicine_name
               FROM order_items oi
               JOIN medicines m ON oi.medicine_id = m.medicine_id
               WHERE oi.order_id=?";
$stmt = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$items_result = mysqli_stmt_get_result($stmt);

// Handle status update (admin and seller only)
if(isset($_POST['update_status']) && in_array($_SESSION['role'], ['admin', 'seller'])){
    // Verify CSRF token
    if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
        $error = "Security token invalid";
    } else {
        $new_status = $_POST['status'];
        $update_order = "UPDATE orders SET status=? WHERE order_id=?";
        $stmt = mysqli_prepare($conn, $update_order);
        mysqli_stmt_bind_param($stmt, "si", $new_status, $order_id);
        mysqli_stmt_execute($stmt);
        header("Location: order_details.php?id=$order_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details - Pharmacy</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="admin-panel">
    <!-- Sidebar -->
    <?php if($_SESSION['role'] == 'admin'): ?>
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>🏥 Pharmacy</h1>
            <p>Admin Portal</p>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                <li><a href="admin_orders.php"><i class="fas fa-shopping-bag"></i> <span>Agizo</span></a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <?php elseif($_SESSION['role'] == 'seller'): ?>
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>🏥 Pharmacy</h1>
            <p>Seller Portal</p>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="seller_dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                <li><a href="seller_orders.php"><i class="fas fa-shopping-bag"></i> <span>Agizo</span></a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <?php else: ?>
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>🏥 Pharmacy</h1>
            <p>Customer Portal</p>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="customer_dashboard.php"><i class="fas fa-shopping-cart"></i> <span>Dawa</span></a></li>
                <li><a href="customer_orders.php"><i class="fas fa-list"></i> <span>Agizo Zangu</span></a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-header">
            <h2>📋 Order #<?php echo $order['order_id']; ?></h2>
            <div class="user-info">
                <span><?php echo ucfirst($_SESSION['role']); ?>: <?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <div class="container">
            <div class="header">
                <h2>📋 Maelezo ya Agizo</h2>
            </div>

            <div class="dashboard-stats" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="stat-card">
                    <h3>Mteja</h3>
                    <div><?php echo $order['customer_name']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Simu</h3>
                    <div><?php echo $order['phone']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Email</h3>
                    <div><?php echo $order['email']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Tarehe</h3>
                    <div><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Jumla</h3>
                    <div>TZS <?php echo number_format($order['total_amount'], 2); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Status</h3>
                    <div><?php echo ucfirst($order['status']); ?></div>
                </div>
            </div>

            <div style="margin: 30px 0;">
                <h3>Anuani ya Kupelekea:</h3>
                <p><?php echo $order['delivery_address']; ?></p>
            </div>

            <div>
                <h3>Vitengo vya Agizo:</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Dawa</th>
                            <th>Idadi</th>
                            <th>Bei</th>
                            <th>Jumla</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                        <tr>
                            <td><?php echo $item['medicine_name']; ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo number_format($item['subtotal'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <?php if(in_array($_SESSION['role'], ['admin', 'seller'])): ?>
            <div style="margin-top: 30px;">
                <h3>Badilisha Status ya Agizo:</h3>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="form-group">
                        <select name="status" required>
                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-success">Sasisha Status</button>
                </form>
            </div>
            <?php endif; ?>

            <div style="margin-top: 30px;">
                <a href="<?php echo $_SESSION['role'] == 'customer' ? 'customer_orders.php' : 'admin_orders.php'; ?>" class="btn">← Rudi</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>