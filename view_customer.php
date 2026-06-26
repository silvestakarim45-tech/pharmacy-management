<?php
session_start();
include("config.php");
include("functions.php");

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

$customer_id = $_GET['id'];

// Get customer details
$sql = "SELECT c.*, u.username, u.email as user_email, u.phone as user_phone
        FROM customers c
        JOIN users u ON c.user_id = u.user_id
        WHERE c.customer_id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$customer = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Customer - Admin</title>
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
                <li><a href="admin_users.php"><i class="fas fa-users"></i> <span>Famasia</span></a></li>
                <li><a href="admin_customers.php" class="active"><i class="fas fa-user-friends"></i> <span>Wateja</span></a></li>
                <li><a href="admin_orders.php"><i class="fas fa-shopping-bag"></i> <span>Agizo</span></a></li>
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
            <h2>👁️ View Customer</h2>
            <div class="user-info">
                <span>Admin: <?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <div class="container">
            <div class="header">
                <h2>Maelezo ya Mteja</h2>
            </div>

            <div class="customer-details">
                <div class="detail-row">
                    <strong>Customer ID:</strong> <?php echo $customer['customer_id']; ?>
                </div>
                <div class="detail-row">
                    <strong>Jina la Mteja:</strong> <?php echo $customer['customer_name']; ?>
                </div>
                <div class="detail-row">
                    <strong>Username:</strong> <?php echo $customer['username']; ?>
                </div>
                <div class="detail-row">
                    <strong>Email:</strong> <?php echo $customer['email'] ? $customer['email'] : $customer['user_email']; ?>
                </div>
                <div class="detail-row">
                    <strong>Simu:</strong> <?php echo $customer['phone'] ? $customer['phone'] : $customer['user_phone']; ?>
                </div>
                <div class="detail-row">
                    <strong>Anuani:</strong> <?php echo $customer['address'] ? $customer['address'] : '-'; ?>
                </div>
                <div class="detail-row">
                    <strong>Imeandikwa:</strong> <?php echo date('Y-m-d H:i', strtotime($customer['created_at'])); ?>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <a href="admin_customers.php" class="btn btn-warning">← Rudi kwenye Orodha</a>
                <a href="edit_customer.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-info">✏️ Hariri</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
