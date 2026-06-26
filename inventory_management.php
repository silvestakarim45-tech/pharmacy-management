<?php
session_start();
include("config.php");
include("functions.php");

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

// Handle reorder point update
if(isset($_POST['update_reorder_point'])){
    if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
        $error = "Security token invalid";
    } else {
        $medicine_id = $_POST['medicine_id'];
        $reorder_point = $_POST['reorder_point'];
        
        $sql = "UPDATE medicines SET reorder_point=? WHERE medicine_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $reorder_point, $medicine_id);
        mysqli_stmt_execute($stmt);
        
        $success = "Reorder point updated successfully";
    }
}

// Handle restock
if(isset($_POST['restock'])){
    if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
        $error = "Security token invalid";
    } else {
        $medicine_id = $_POST['medicine_id'];
        $quantity_added = $_POST['quantity_added'];
        
        // Get current quantity
        $sql = "SELECT quantity FROM medicines WHERE medicine_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $medicine_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $medicine = mysqli_fetch_assoc($result);
        
        $new_quantity = $medicine['quantity'] + $quantity_added;
        
        // Update quantity
        $sql = "UPDATE medicines SET quantity=?, last_restocked=CURDATE() WHERE medicine_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $new_quantity, $medicine_id);
        mysqli_stmt_execute($stmt);
        
        $success = "Stock updated successfully";
    }
}

// Get items that need to be reordered
$reorder_query = "SELECT * FROM medicines WHERE quantity <= reorder_point ORDER BY quantity ASC";
$reorder_result = mysqli_query($conn, $reorder_query);

// Get all medicines
$medicines_query = "SELECT * FROM medicines ORDER BY medicine_name ASC";
$medicines_result = mysqli_query($conn, $medicines_query);

// Get inventory statistics
$total_medicines = mysqli_num_rows($medicines_result);
$low_stock_count = mysqli_num_rows($reorder_result);
$out_of_stock = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM medicines WHERE quantity = 0"));
$total_value_query = "SELECT SUM(quantity * buying_price) as total FROM medicines";
$total_value_result = mysqli_query($conn, $total_value_query);
$total_value = mysqli_fetch_assoc($total_value_result)['total'] ?: 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Management - Pharmacy</title>
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
                <li><a href="inventory_management.php" class="active"><i class="fas fa-boxes"></i> <span>Inventory</span></a></li>
                <li><a href="admin_users.php"><i class="fas fa-users"></i> <span>Famasia</span></a></li>
                <li><a href="admin_customers.php"><i class="fas fa-user-friends"></i> <span>Wateja</span></a></li>
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
            <h2>📦 Usimamizi wa Inventory</h2>
            <div class="user-info">
                <span>Admin: <?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if(isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="icon">📦</div>
                <h3>Jumla ya Dawa</h3>
                <div class="number"><?php echo $total_medicines; ?></div>
            </div>

            <div class="stat-card warning">
                <div class="icon">⚠️</div>
                <h3>Zinahitaji Kuagiza</h3>
                <div class="number"><?php echo $low_stock_count; ?></div>
            </div>

            <div class="stat-card danger">
                <div class="icon">🚫</div>
                <h3>Zimeisha Stock</h3>
                <div class="number"><?php echo $out_of_stock; ?></div>
            </div>

            <div class="stat-card success">
                <div class="icon">💰</div>
                <h3>Thamani ya Inventory</h3>
                <div class="number">TZS <?php echo number_format($total_value, 0); ?></div>
            </div>
        </div>

        <!-- Items that need reordering -->
        <div class="container" style="margin-bottom: 30px;">
            <div class="header">
                <h2>⚠️ Dawa Zinazohitaji Kuagiza</h2>
            </div>

            <?php if(mysqli_num_rows($reorder_result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Jina la Dawa</th>
                        <th>Stock Iliyopo</th>
                        <th>Reorder Point</th>
                        <th>Hali</th>
                        <th>Matendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($medicine = mysqli_fetch_assoc($reorder_result)): ?>
                    <tr>
                        <td><strong><?php echo $medicine['medicine_name']; ?></strong></td>
                        <td>
                            <span style="color: <?php echo $medicine['quantity'] == 0 ? '#e74c3c' : '#f39c12'; ?>; font-weight: bold;">
                                <?php echo $medicine['quantity']; ?>
                            </span>
                        </td>
                        <td><?php echo $medicine['reorder_point']; ?></td>
                        <td>
                            <?php if($medicine['quantity'] == 0): ?>
                                <span class="alert alert-danger" style="padding: 5px 10px; font-size: 12px;">Imeisha</span>
                            <?php else: ?>
                                <span class="alert alert-warning" style="padding: 5px 10px; font-size: 12px;">Inayokaribiaisha</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="medicine_id" value="<?php echo $medicine['medicine_id']; ?>">
                                <input type="number" name="quantity_added" placeholder="Idadi" style="width: 80px; padding: 5px;" required min="1">
                                <button type="submit" name="restock" class="btn btn-success" style="padding: 5px 10px; font-size: 12px;">+ Restock</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="alert alert-success">Hakuna dawa zinazohitaji kuagiza kwa sasa</div>
            <?php endif; ?>
        </div>

        <!-- All inventory -->
        <div class="container">
            <div class="header">
                <h2>📋 Inventory Yote</h2>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Jina la Dawa</th>
                        <th>Kategori</th>
                        <th>Stock</th>
                        <th>Reorder Point</th>
                        <th>Bei ya Kununua</th>
                        <th>Thamani</th>
                        <th>Matendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php mysqli_data_seek($medicines_result, 0); ?>
                    <?php while($medicine = mysqli_fetch_assoc($medicines_result)): ?>
                    <tr>
                        <td><strong><?php echo $medicine['medicine_name']; ?></strong></td>
                        <td><?php echo $medicine['category']; ?></td>
                        <td>
                            <span style="color: <?php echo $medicine['quantity'] <= $medicine['reorder_point'] ? '#e74c3c' : '#27ae60'; ?>; font-weight: bold;">
                                <?php echo $medicine['quantity']; ?>
                            </span>
                        </td>
                        <td><?php echo $medicine['reorder_point']; ?></td>
                        <td>TZS <?php echo number_format($medicine['buying_price'], 2); ?></td>
                        <td>TZS <?php echo number_format($medicine['quantity'] * $medicine['buying_price'], 2); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="medicine_id" value="<?php echo $medicine['medicine_id']; ?>">
                                <input type="number" name="reorder_point" value="<?php echo $medicine['reorder_point']; ?>" style="width: 60px; padding: 5px;" required min="1">
                                <button type="submit" name="update_reorder_point" class="btn btn-info" style="padding: 5px 10px; font-size: 12px;">Set Reorder</button>
                            </form>
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
