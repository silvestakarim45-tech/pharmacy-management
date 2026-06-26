<?php
session_start();
include("config.php");

// Check if user is logged in and is a customer
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer'){
    header("Location: customer_login.php");
    exit();
}

// Get customer info
$user_id = $_SESSION['user_id'];
$customer_query = "SELECT * FROM customers WHERE user_id='$user_id'";
$customer_result = mysqli_query($conn, $customer_query);
$customer = mysqli_fetch_assoc($customer_result);

// Get customer orders
$orders_query = "SELECT * FROM orders WHERE customer_id='".$customer['customer_id']."' ORDER BY order_date DESC";
$orders_result = mysqli_query($conn, $orders_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders - Pharmacy</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="admin-panel">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>🏥 Pharmacy</h1>
            <p>Customer Portal</p>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="customer_dashboard.php"><i class="fas fa-shopping-cart"></i> <span>Dawa</span></a></li>
                <li><a href="customer_orders.php" class="active"><i class="fas fa-list"></i> <span>Agizo Zangu</span></a></li>
                <li><a href="customer_profile.php"><i class="fas fa-user"></i> <span>Profaili Yangu</span></a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-header">
            <h2>📋 Agizo Zangu</h2>
            <div class="user-info">
                <span>Karibu, <?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <div class="container">
            <?php if(mysqli_num_rows($orders_result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Tarehe</th>
                        <th>Jumla</th>
                        <th>Status</th>
                        <th>Anuani</th>
                        <th>Matendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = mysqli_fetch_assoc($orders_result)): ?>
                    <tr>
                        <td>#<?php echo $order['order_id']; ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
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
                        <td><?php echo substr($order['delivery_address'], 0, 30) . '...'; ?></td>
                        <td>
                            <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-info" style="padding: 5px 10px; font-size: 12px;">Angalia</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="alert alert-info">
                <h3>Huna agizo bado</h3>
                <p>Anza kuagiza  dawa kutoka pharmacy kwetu<a href="customer_dashboard.php">hapa</a></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>