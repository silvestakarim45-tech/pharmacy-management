<?php
session_start();
include("config.php");

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

// Get system statistics
$medicines_count=mysqli_num_rows(mysqli_query($conn,"SELECT * FROM medicines"));
$customers_count=mysqli_num_rows(mysqli_query($conn,"SELECT * FROM customers"));
$sellers_count=mysqli_num_rows(mysqli_query($conn,"SELECT * FROM users WHERE role='seller'"));
$sales_count=mysqli_num_rows(mysqli_query($conn,"SELECT * FROM sales"));
$orders_count=mysqli_num_rows(mysqli_query($conn,"SELECT * FROM orders"));

// Get total sales amount
$total_sales_query = "SELECT SUM(total_amount) as total FROM sales";
$total_sales_result = mysqli_query($conn, $total_sales_query);
$total_sales = mysqli_fetch_assoc($total_sales_result);
$total_sales_amount = $total_sales['total'] ? $total_sales['total'] : 0;

// Get total orders amount
$total_orders_query = "SELECT SUM(total_amount) as total FROM orders WHERE status='completed'";
$total_orders_result = mysqli_query($conn, $total_orders_query);
$total_orders = mysqli_fetch_assoc($total_orders_result);
$total_orders_amount = $total_orders['total'] ? $total_orders['total'] : 0;

// Get low stock items
$low_stock=mysqli_query($conn,"SELECT * FROM medicines WHERE quantity < 10");

// Get expiring medicines
$expiry = mysqli_query($conn,"SELECT * FROM medicines WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)");

// Get all medicines for the toggle view
$all_medicines = mysqli_query($conn,"SELECT * FROM medicines ORDER BY medicine_name ASC");

// Get all customers for the toggle view
$all_customers = mysqli_query($conn,"SELECT * FROM customers ORDER BY customer_name ASC");

// Get all sellers for the toggle view
$all_sellers = mysqli_query($conn,"SELECT * FROM users WHERE role='seller' ORDER BY fullname ASC");

// Get all sales for the toggle view
$all_sales = mysqli_query($conn,"SELECT s.*, m.medicine_name FROM sales s JOIN medicines m ON s.medicine_id = m.medicine_id ORDER BY s.sale_date DESC LIMIT 20");

// Get all orders for the toggle view
$all_orders = mysqli_query($conn,"SELECT o.*, c.customer_name FROM orders o JOIN customers c ON o.customer_id = c.customer_id ORDER BY o.order_date DESC LIMIT 20");

// Get recent orders
$recent_orders = mysqli_query($conn,"SELECT o.*, c.customer_name FROM orders o
                                    JOIN customers c ON o.customer_id = c.customer_id
                                    ORDER BY o.order_date DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pharmacy Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }

        .admin-panel {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: #1e293b;
            color: white;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 30px 20px;
            background: #0f172a;
            border-bottom: 1px solid #334155;
        }

        .sidebar-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #3b82f6;
        }

        .sidebar-header p {
            font-size: 12px;
            color: #94a3b8;
        }

        .sidebar-menu {
            flex: 1;
            padding: 20px 0;
        }

        .sidebar-menu ul {
            list-style: none;
        }

        .sidebar-menu ul li {
            margin-bottom: 5px;
        }

        .sidebar-menu ul li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar-menu ul li a:hover {
            background: #334155;
            color: white;
            border-left-color: #3b82f6;
        }

        .sidebar-menu ul li a.active {
            background: #3b82f6;
            color: white;
            border-left-color: #60a5fa;
        }

        .sidebar-menu ul li a i {
            width: 25px;
            margin-right: 10px;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid #334155;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
            background: #ef4444;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .logout-btn i {
            margin-right: 8px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }

        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .top-header h2 {
            color: #1e293b;
            font-size: 28px;
            font-weight: 700;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info span {
            color: #64748b;
            font-weight: 500;
        }

        .user-info i {
            color: #3b82f6;
            font-size: 20px;
        }

        /* Stats Cards */
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-card .icon {
            font-size: 40px;
            margin-bottom: 15px;
        }

        .stat-card h3 {
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .stat-card small {
            color: #94a3b8;
            font-size: 12px;
        }

        .stat-card .btn {
            display: inline-block;
            padding: 8px 15px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 12px;
            margin-top: 10px;
            transition: all 0.3s;
        }

        .stat-card .btn:hover {
            background: #2563eb;
        }

        /* Container */
        .container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .container .header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }

        .container .header h2 {
            color: #1e293b;
            font-size: 20px;
            font-weight: 600;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background: #f8fafc;
        }

        table th {
            padding: 15px;
            text-align: left;
            color: #64748b;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
        }

        table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }

        table tbody tr:hover {
            background: #f8fafc;
        }

        /* Alert Styles */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border-left: 4px solid #3b82f6;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid #22c55e;
        }

        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        /* Button Styles */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-success {
            background: #22c55e;
            color: white;
        }

        .btn-success:hover {
            background: #16a34a;
        }

        .btn-info {
            background: #3b82f6;
            color: white;
        }

        .btn-info:hover {
            background: #2563eb;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .dashboard-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="admin-panel">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h1><i class="fas fa-hospital"></i> Pharmacy</h1>
            <p>Admin Portal</p>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="admin_dashboard.php" class="active"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                <li><a href="medicine.php"><i class="fas fa-pills"></i> <span>Dawa</span></a></li>
                <li><a href="inventory_management.php"><i class="fas fa-boxes"></i> <span>Inventory</span></a></li>
                <li><a href="stock_alerts.php"><i class="fas fa-bell"></i> <span>Stock Alerts</span></a></li>
                <li><a href="suppliers.php"><i class="fas fa-truck"></i> <span>Suppliers</span></a></li>
                <li><a href="sales_analytics.php"><i class="fas fa-chart-bar"></i> <span>Analytics</span></a></li>
                <li><a href="audit_logs.php"><i class="fas fa-history"></i> <span>Audit Logs</span></a></li>
                <li><a href="database_backup.php"><i class="fas fa-database"></i> <span>Backup</span></a></li>
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
            <h2><i class="fas fa-tachometer-alt" style="color: #3b82f6; margin-right: 10px;"></i> Admin Dashboard</h2>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span><?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card" onclick="toggleMedicines()" style="cursor: pointer;">
                <div class="icon">📦</div>
                <h3>Jumla ya Dawa</h3>
                <div class="number"><?php echo $medicines_count; ?></div>
                <small style="color: #7f8c8d; font-size: 12px;">Bonyeza kuona dawa</small>
                <div style="margin-top: 10px;">
                    <a href="export_medicines_csv.php" class="btn btn-info" style="padding: 5px 10px; font-size: 11px;">📥 Export CSV</a>
                </div>
            </div>

            <!-- Medicines List (Hidden by default) -->
            <div id="medicinesList" style="display: none; grid-column: 1 / -1; margin-top: 20px;">
                <div class="container">
                    <div class="header">
                        <h2>📋 Orodha ya Dawa Zote</h2>
                    </div>

                    <?php if(mysqli_num_rows($all_medicines) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Jina la Dawa</th>
                                <th>Kategori</th>
                                <th>Stock</th>
                                <th>Bei ya Kuuza</th>
                                <th>Tarehe ya Kuisha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($medicine = mysqli_fetch_assoc($all_medicines)): ?>
                            <tr>
                                <td><?php echo $medicine['medicine_id']; ?></td>
                                <td><strong><?php echo $medicine['medicine_name']; ?></strong></td>
                                <td><?php echo $medicine['category']; ?></td>
                                <td>
                                    <span style="color: <?php echo $medicine['quantity'] < 10 ? '#e74c3c' : '#27ae60'; ?>; font-weight: bold;">
                                        <?php echo $medicine['quantity']; ?>
                                    </span>
                                </td>
                                <td>TZS <?php echo number_format($medicine['selling_price'], 2); ?></td>
                                <td><?php echo $medicine['expiry_date']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-info">Hakuna dawa bado</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Customers List (Hidden by default) -->
            <div id="customersList" style="display: none; grid-column: 1 / -1; margin-top: 20px;">
                <div class="container">
                    <div class="header">
                        <h2>👥 Orodha ya Wateja Wote</h2>
                    </div>

                    <?php if(mysqli_num_rows($all_customers) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Jina la Mteja</th>
                                <th>Simu</th>
                                <th>Anuani</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($customer = mysqli_fetch_assoc($all_customers)): ?>
                            <tr>
                                <td><?php echo $customer['customer_id']; ?></td>
                                <td><strong><?php echo $customer['customer_name']; ?></strong></td>
                                <td><?php echo $customer['phone'] ?: '-'; ?></td>
                                <td><?php echo $customer['address'] ?: '-'; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-info">Hakuna wateja bado</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stat-card success" onclick="toggleCustomers()" style="cursor: pointer;">
                <div class="icon">👥</div>
                <h3>Jumla ya Wateja</h3>
                <div class="number"><?php echo $customers_count; ?></div>
                <small style="color: #7f8c8d; font-size: 12px;">Bonyeza kuona wateja</small>
            </div>

            <!-- Sellers List (Hidden by default) -->
            <div id="sellersList" style="display: none; grid-column: 1 / -1; margin-top: 20px;">
                <div class="container">
                    <div class="header">
                        <h2>🛒 Orodha ya Famasia Wote</h2>
                    </div>

                    <?php if(mysqli_num_rows($all_sellers) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Jina Kamili</th>
                                <th>Username</th>
                                <th>Simu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($seller = mysqli_fetch_assoc($all_sellers)): ?>
                            <tr>
                                <td><?php echo $seller['user_id']; ?></td>
                                <td><strong><?php echo $seller['fullname']; ?></strong></td>
                                <td><?php echo $seller['username']; ?></td>
                                <td><?php echo $seller['phone'] ?: '-'; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-info">Hakuna famasia bado</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stat-card warning" onclick="toggleSellers()" style="cursor: pointer;">
                <div class="icon">🛒</div>
                <h3>Famasia</h3>
                <div class="number"><?php echo $sellers_count; ?></div>
                <small style="color: #7f8c8d; font-size: 12px;">Bonyeza kuona famasia</small>
            </div>

            <!-- Sales List (Hidden by default) -->
            <div id="salesList" style="display: none; grid-column: 1 / -1; margin-top: 20px;">
                <div class="container">
                    <div class="header">
                        <h2>💰 Orodha ya Mauzo (POS)</h2>
                    </div>

                    <?php if(mysqli_num_rows($all_sales) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Dawa</th>
                                <th>Idadi</th>
                                <th>Jumla (TZS)</th>
                                <th>Tarehe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($sale = mysqli_fetch_assoc($all_sales)): ?>
                            <tr>
                                <td><?php echo $sale['sale_id']; ?></td>
                                <td><strong><?php echo $sale['medicine_name']; ?></strong></td>
                                <td><?php echo $sale['quantity_sold']; ?></td>
                                <td><?php echo number_format($sale['total_amount'], 2); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($sale['sale_date'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-info">Hakuna mauzo bado</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stat-card info" onclick="toggleSales()" style="cursor: pointer;">
                <div class="icon">💰</div>
                <h3>Mauzo (POS)</h3>
                <div class="number"><?php echo $sales_count; ?></div>
                <small style="color: #7f8c8d; font-size: 12px;">Bonyeza kuona mauzo</small>
                <div style="margin-top: 10px;">
                    <a href="export_sales_csv.php" class="btn btn-info" style="padding: 5px 10px; font-size: 11px;">📥 Export CSV</a>
                </div>
            </div>

            <!-- Orders List (Hidden by default) -->
            <div id="ordersList" style="display: none; grid-column: 1 / -1; margin-top: 20px;">
                <div class="container">
                    <div class="header">
                        <h2>📋 Orodha ya Agizo Mtandaoni</h2>
                    </div>

                    <?php if(mysqli_num_rows($all_orders) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Mteja</th>
                                <th>Jumla</th>
                                <th>Status</th>
                                <th>Tarehe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($order = mysqli_fetch_assoc($all_orders)): ?>
                            <tr>
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td><?php echo $order['customer_name']; ?></td>
                                <td>TZS <?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><?php echo ucfirst($order['status']); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-info">Hakuna agizo bado</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stat-card" onclick="toggleOrders()" style="cursor: pointer;">
                <div class="icon">📋</div>
                <h3>Agizo Mtandaoni</h3>
                <div class="number"><?php echo $orders_count; ?></div>
                <small style="color: #7f8c8d; font-size: 12px;">Bonyeza kuona agizo</small>
                <div style="margin-top: 10px;">
                    <a href="export_orders_csv.php" class="btn btn-info" style="padding: 5px 10px; font-size: 11px;">📥 Export CSV</a>
                </div>
            </div>

            <!-- Revenue Info (Hidden by default) -->
            <div id="revenueInfo" style="display: none; grid-column: 1 / -1; margin-top: 20px;">
                <div class="container">
                    <div class="header">
                        <h2>💵 Maelezo ya Mapato</h2>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <div class="alert alert-success">
                            <h3>Jumla ya Mauzo (POS)</h3>
                            <p style="font-size: 24px; font-weight: bold;">TZS <?php echo number_format($total_sales_amount, 2); ?></p>
                        </div>
                        <div class="alert alert-info">
                            <h3>Jumla ya Agizo Mtandaoni</h3>
                            <p style="font-size: 24px; font-weight: bold;">TZS <?php echo number_format($total_orders_amount, 2); ?></p>
                        </div>
                    </div>

                    <div class="alert alert-warning" style="margin-top: 20px;">
                        <h3>Jumla ya Mapato Yote</h3>
                        <p style="font-size: 28px; font-weight: bold;">TZS <?php echo number_format($total_sales_amount + $total_orders_amount, 2); ?></p>
                    </div>
                </div>
            </div>

            <div class="stat-card success" onclick="toggleRevenue()" style="cursor: pointer;">
                <div class="icon">💵</div>
                <h3>Jumla ya Mauzo</h3>
                <div class="number"><?php echo number_format($total_sales_amount, 0); ?></div>
                <small style="color: #7f8c8d; font-size: 12px;">Bonyeza kuona mapato</small>
            </div>
        </div>

        <div class="container" style="margin-bottom: 30px;">
            <div class="header">
                <h2>📋 Agizo za Hivi Karibuni</h2>
            </div>

            <?php if(mysqli_num_rows($recent_orders) > 0): ?>
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
                    <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
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
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="alert alert-info">Hakuna agizo bado</div>
            <?php endif; ?>
        </div>

        <div class="container">
            <div class="header">
                <h2>⚠️ Dawa Zilizopo Stoo </h2>
            </div>
            <?php
            if(mysqli_num_rows($low_stock) > 0){
                echo "<div class='alert alert-danger'>";
                while($row=mysqli_fetch_assoc($low_stock)){
                    echo "<strong>" . $row['medicine_name'] . "</strong> - Stock inabaki: " . $row['quantity'] . "<br>";
                }
                echo "</div>";
            } else {
                echo "<div class='alert alert-success'>Dawa zote zina stock ya kutosha</div>";
            }
            ?>
        </div>

        <div class="container" style="margin-top: 30px;">
            <div class="header">
                <h2>⏰ Dawa Zinazokaribia Kuisha Muda wake</h2>
            </div>
            <?php
            if(mysqli_num_rows($expiry) > 0){
                echo "<div class='alert alert-warning'>";
                while($row = mysqli_fetch_assoc($expiry)){
                    echo "<strong>" . $row['medicine_name'] . "</strong> - Itaisha muda: " . $row['expiry_date'] . "<br>";
                }
                echo "</div>";
            } else {
                echo "<div class='alert alert-success'>Hakuna dawa zinazokaribia kuisha muda</div>";
            }
            ?>
        </div>
    </div>
</div>

<script>
function toggleMedicines() {
    var medicinesList = document.getElementById('medicinesList');
    if (medicinesList.style.display === 'none') {
        medicinesList.style.display = 'block';
    } else {
        medicinesList.style.display = 'none';
    }
}

function toggleCustomers() {
    var customersList = document.getElementById('customersList');
    if (customersList.style.display === 'none') {
        customersList.style.display = 'block';
    } else {
        customersList.style.display = 'none';
    }
}

function toggleSellers() {
    var sellersList = document.getElementById('sellersList');
    if (sellersList.style.display === 'none') {
        sellersList.style.display = 'block';
    } else {
        sellersList.style.display = 'none';
    }
}

function toggleSales() {
    var salesList = document.getElementById('salesList');
    if (salesList.style.display === 'none') {
        salesList.style.display = 'block';
    } else {
        salesList.style.display = 'none';
    }
}

function toggleOrders() {
    var ordersList = document.getElementById('ordersList');
    if (ordersList.style.display === 'none') {
        ordersList.style.display = 'block';
    } else {
        ordersList.style.display = 'none';
    }
}

function toggleRevenue() {
    var revenueInfo = document.getElementById('revenueInfo');
    if (revenueInfo.style.display === 'none') {
        revenueInfo.style.display = 'block';
    } else {
        revenueInfo.style.display = 'none';
    }
}
</script>

</body>
</html>