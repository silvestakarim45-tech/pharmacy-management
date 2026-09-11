<?php
session_start();
include("config.php");

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

// Get low stock items (quantity < 10)
$low_stock = mysqli_query($conn, "SELECT * FROM medicines WHERE quantity < 10 ORDER BY quantity ASC");

// Get expiring medicines (within 30 days)
$expiring = mysqli_query($conn, "SELECT * FROM medicines WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND expiry_date >= CURDATE() ORDER BY expiry_date ASC");

// Get expired medicines
$expired = mysqli_query($conn, "SELECT * FROM medicines WHERE expiry_date < CURDATE() ORDER BY expiry_date DESC");

// Get out of stock items
$out_of_stock = mysqli_query($conn, "SELECT * FROM medicines WHERE quantity = 0 ORDER BY medicine_name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Stock Alerts - Pharmacy Management</title>
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
                <li><a href="stock_alerts.php" class="active"><i class="fas fa-bell"></i> <span>Stock Alerts</span></a></li>
                <li><a href="admin_users.php"><i class="fas fa-users"></i> <span>Famasia</span></a></li>
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
            <h2>🔔 Stock Alerts</h2>
            <div class="user-info">
                <span>Admin: <?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <!-- Out of Stock -->
        <div class="container" style="margin-bottom: 30px;">
            <div class="header">
                <h2>🚫 Dawa Zimeisha Stock</h2>
            </div>

            <?php if(mysqli_num_rows($out_of_stock) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Jina la Dawa</th>
                        <th>Kategori</th>
                        <th>Bei ya Kuuza</th>
                        <th>Tarehe ya Kuisha</th>
                        <th>Matendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($medicine = mysqli_fetch_assoc($out_of_stock)): ?>
                    <tr>
                        <td><strong><?php echo $medicine['medicine_name']; ?></strong></td>
                        <td><?php echo $medicine['category']; ?></td>
                        <td>TZS <?php echo number_format($medicine['selling_price'], 2); ?></td>
                        <td><?php echo $medicine['expiry_date']; ?></td>
                        <td>
                            <a href="edit_medicine.php?id=<?php echo $medicine['medicine_id']; ?>" class="btn btn-info" style="padding: 5px 10px; font-size: 12px;">+ Restock</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="alert alert-success">Hakuna dawa zimeisha stock</div>
            <?php endif; ?>
        </div>

        <!-- Low Stock -->
        <div class="container" style="margin-bottom: 30px;">
            <div class="header">
                <h2>⚠️ Dawa Zinazokaribia Kuisha Stock</h2>
            </div>

            <?php if(mysqli_num_rows($low_stock) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Jina la Dawa</th>
                        <th>Stock Iliyopo</th>
                        <th>Kategori</th>
                        <th>Bei ya Kuuza</th>
                        <th>Matendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($medicine = mysqli_fetch_assoc($low_stock)): ?>
                    <tr>
                        <td><strong><?php echo $medicine['medicine_name']; ?></strong></td>
                        <td>
                            <span style="color: #e74c3c; font-weight: bold;">
                                <?php echo $medicine['quantity']; ?>
                            </span>
                        </td>
                        <td><?php echo $medicine['category']; ?></td>
                        <td>TZS <?php echo number_format($medicine['selling_price'], 2); ?></td>
                        <td>
                            <a href="edit_medicine.php?id=<?php echo $medicine['medicine_id']; ?>" class="btn btn-info" style="padding: 5px 10px; font-size: 12px;">+ Restock</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="alert alert-success">Hakuna dawa zinazokaribia kuisha stock</div>
            <?php endif; ?>
        </div>

        <!-- Expired Medicines -->
        <div class="container" style="margin-bottom: 30px;">
            <div class="header">
                <h2>❌ Dawa Zimeisha Muda</h2>
            </div>

            <?php if(mysqli_num_rows($expired) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Jina la Dawa</th>
                        <th>Kategori</th>
                        <th>Tarehe ya Kuisha</th>
                        <th>Siku Zilizopita</th>
                        <th>Matendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($medicine = mysqli_fetch_assoc($expired)): ?>
                    <tr>
                        <td><strong><?php echo $medicine['medicine_name']; ?></strong></td>
                        <td><?php echo $medicine['category']; ?></td>
                        <td style="color: #e74c3c; font-weight: bold;"><?php echo $medicine['expiry_date']; ?></td>
                        <td>
                            <?php
                            $expiry_date = new DateTime($medicine['expiry_date']);
                            $today = new DateTime();
                            $days_expired = $today->diff($expiry_date)->format('%a');
                            echo $days_expired . ' siku';
                            ?>
                        </td>
                        <td>
                            <a href="edit_medicine.php?id=<?php echo $medicine['medicine_id']; ?>" class="btn btn-warning" style="padding: 5px 10px; font-size: 12px;">Update</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="alert alert-success">Hakuna dawa zimeisha muda</div>
            <?php endif; ?>
        </div>

        <!-- Expiring Soon -->
        <div class="container">
            <div class="header">
                <h2>⏰ Dawa Zinazokaribia Kuisha Muda</h2>
            </div>

            <?php if(mysqli_num_rows($expiring) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Jina la Dawa</th>
                        <th>Kategori</th>
                        <th>Tarehe ya Kuisha</th>
                        <th>Siku Zilizosalia</th>
                        <th>Matendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($medicine = mysqli_fetch_assoc($expiring)): ?>
                    <tr>
                        <td><strong><?php echo $medicine['medicine_name']; ?></strong></td>
                        <td><?php echo $medicine['category']; ?></td>
                        <td style="color: #f39c12; font-weight: bold;"><?php echo $medicine['expiry_date']; ?></td>
                        <td>
                            <?php
                            $expiry_date = new DateTime($medicine['expiry_date']);
                            $today = new DateTime();
                            $days_remaining = $today->diff($expiry_date)->format('%a');
                            echo $days_remaining . ' siku';
                            ?>
                        </td>
                        <td>
                            <a href="edit_medicine.php?id=<?php echo $medicine['medicine_id']; ?>" class="btn btn-info" style="padding: 5px 10px; font-size: 12px;">Update</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="alert alert-success">Hakuna dawa zinazokaribia kuisha muda</div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
