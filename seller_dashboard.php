<?php
session_start();
include("config.php");

// Check if user is logged in and is a seller
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller'){
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

// Get today's sales by this seller
$today_sales_query = "SELECT s.*, m.medicine_name FROM sales s
                      JOIN medicines m ON s.medicine_id = m.medicine_id
                      WHERE s.seller_id=?
                      AND DATE(s.sale_date) = CURDATE()
                      ORDER BY s.sale_date DESC";
$stmt = mysqli_prepare($conn, $today_sales_query);
mysqli_stmt_bind_param($stmt, "i", $seller_id);
mysqli_stmt_execute($stmt);
$today_sales_result = mysqli_stmt_get_result($stmt);

// Calculate today's total
$today_total = 0;
while($sale = mysqli_fetch_assoc($today_sales_result)){
    $today_total += $sale['total_amount'];
}
// Reset pointer for later use
mysqli_data_seek($today_sales_result, 0);

// Get medicines with low stock
$low_stock_query = "SELECT * FROM medicines WHERE quantity < 10 ORDER BY quantity ASC";
$low_stock_result = mysqli_query($conn, $low_stock_query);

// Get pending orders to process
$pending_orders_query = "SELECT o.*, c.customer_name FROM orders o
                         JOIN customers c ON o.customer_id = c.customer_id
                         WHERE o.status='pending'
                         ORDER BY o.order_date ASC";
$pending_orders_result = mysqli_query($conn, $pending_orders_query);

// Handle order processing
if(isset($_POST['process_order'])){
    $order_id = $_POST['order_id'];
    $update_order = "UPDATE orders SET status='processing' WHERE order_id=?";
    $stmt = mysqli_prepare($conn, $update_order);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    header("Location: seller_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Seller Dashboard - Pharmacy</title>
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
                <li><a href="seller_dashboard.php" class="active"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                <li><a href="seller_sales.php"><i class="fas fa-cash-register"></i> <span>Mauzo</span></a></li>
                <li><a href="seller_medicines.php"><i class="fas fa-pills"></i> <span>Dawa</span></a></li>
                <li><a href="seller_orders.php"><i class="fas fa-shopping-bag"></i> <span>Agizo Mtandaoni</span></a></li>
                <li><a href="seller_profile.php"><i class="fas fa-user"></i> <span>Profaili Yangu</span></a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-header">
            <h2>🏪 Seller Dashboard</h2>
            <div class="user-info">
                <span>Karibu, <?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="icon">💰</div>
                <h3>Mauzo ya Leo</h3>
                <div class="number">TZS <?php echo number_format($today_total, 2); ?></div>
            </div>

            <div class="stat-card warning">
                <div class="icon">📦</div>
                <h3>Dawa Zinazoisha Stock</h3>
                <div class="number"><?php echo mysqli_num_rows($low_stock_result); ?></div>
            </div>

            <div class="stat-card success">
                <div class="icon">📋</div>
                <h3>Agizo Zinazosubiri</h3>
                <div class="number"><?php echo mysqli_num_rows($pending_orders_result); ?></div>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="container" style="margin-bottom: 30px;">
            <div class="header">
                <h2>📋 Agizo Zinazosubiri</h2>
            </div>

            <?php if(mysqli_num_rows($pending_orders_result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Mteja</th>
                        <th>Jumla</th>
                        <th>Tarehe</th>
                        <th>Matendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = mysqli_fetch_assoc($pending_orders_result)): ?>
                    <tr>
                        <td>#<?php echo $order['order_id']; ?></td>
                        <td><?php echo $order['customer_name']; ?></td>
                        <td>TZS <?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                <button type="submit" name="process_order" class="btn btn-success" style="padding: 5px 10px; font-size: 12px;">Process</button>
                            </form>
                            <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-info" style="padding: 5px 10px; font-size: 12px;">Angalia</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="alert alert-success">Hakuna agizo zinazosubiri kwa sasa</div>
            <?php endif; ?>
        </div>

        <!-- Low Stock Alert -->
        <div class="container">
            <div class="header">
                <h2>⚠️ Dawa Zinazoisha Stock</h2>
            </div>

            <?php if(mysqli_num_rows($low_stock_result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Jina la Dawa</th>
                        <th>Idadi Iliyosalia</th>
                        <th>Kategori</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($medicine = mysqli_fetch_assoc($low_stock_result)): ?>
                    <tr>
                        <td><?php echo $medicine['medicine_name']; ?></td>
                        <td><span style="color: #e74c3c; font-weight: bold;"><?php echo $medicine['quantity']; ?></span></td>
                        <td><?php echo $medicine['category']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="alert alert-success">Dawa zote zina stock ya kutosha</div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>