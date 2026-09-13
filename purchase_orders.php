<?php
session_start();
include("config.php");
include("functions.php");

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

// Handle purchase order creation
if(isset($_POST['create_order'])){
    if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
        $error = "Security token invalid";
    } else {
        $supplier_id = $_POST['supplier_id'];
        $expected_delivery_date = $_POST['expected_delivery_date'];
        $notes = $_POST['notes'];
        
        // Get cart items from session
        if(isset($_SESSION['purchase_cart']) && !empty($_SESSION['purchase_cart'])){
            $total_amount = 0;
            foreach($_SESSION['purchase_cart'] as $item){
                $total_amount += ($item['buying_price'] * $item['quantity']);
            }
            
            // Insert purchase order
            $sql = "INSERT INTO purchase_orders (supplier_id, expected_delivery_date, total_amount, status, notes, created_by)
                    VALUES (?, ?, ?, 'ordered', ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "isdii", $supplier_id, $expected_delivery_date, $total_amount, $notes, $_SESSION['user_id']);
            mysqli_stmt_execute($stmt);
            $purchase_order_id = mysqli_insert_id($conn);
            
            // Insert purchase order items
            foreach($_SESSION['purchase_cart'] as $item){
                $subtotal = $item['buying_price'] * $item['quantity'];
                $sql = "INSERT INTO purchase_order_items (purchase_order_id, medicine_id, quantity, buying_price, subtotal)
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "iiidd", $purchase_order_id, $item['medicine_id'], $item['quantity'], $item['buying_price'], $subtotal);
                mysqli_stmt_execute($stmt);
            }
            
            // Clear cart
            unset($_SESSION['purchase_cart']);
            $success = "Purchase order created successfully!";
        } else {
            $error = "Cart is empty. Please add medicines to the cart.";
        }
    }
}

// Handle add to cart
if(isset($_POST['add_to_cart'])){
    $medicine_id = $_POST['medicine_id'];
    $quantity = $_POST['quantity'];
    
    if(!isset($_SESSION['purchase_cart'])){
        $_SESSION['purchase_cart'] = array();
    }
    
    // Get medicine details
    $med_query = "SELECT * FROM medicines WHERE medicine_id=?";
    $stmt = mysqli_prepare($conn, $med_query);
    mysqli_stmt_bind_param($stmt, "i", $medicine_id);
    mysqli_stmt_execute($stmt);
    $med_result = mysqli_stmt_get_result($stmt);
    $medicine = mysqli_fetch_assoc($med_result);
    
    if($medicine){
        $_SESSION['purchase_cart'][] = array(
            'medicine_id' => $medicine_id,
            'medicine_name' => $medicine['medicine_name'],
            'buying_price' => $medicine['buying_price'],
            'quantity' => $quantity
        );
    }
}

// Handle remove from cart
if(isset($_POST['remove_from_cart'])){
    $index = $_POST['cart_index'];
    unset($_SESSION['purchase_cart'][$index]);
    $_SESSION['purchase_cart'] = array_values($_SESSION['purchase_cart']);
}

// Handle order status update
if(isset($_POST['update_status'])){
    if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
        $error = "Security token invalid";
    } else {
        $purchase_order_id = $_POST['purchase_order_id'];
        $status = $_POST['status'];
        
        if($status == 'received'){
            // Update medicine quantities
            $items_query = "SELECT * FROM purchase_order_items WHERE purchase_order_id=?";
            $stmt = mysqli_prepare($conn, $items_query);
            mysqli_stmt_bind_param($stmt, "i", $purchase_order_id);
            mysqli_stmt_execute($stmt);
            $items_result = mysqli_stmt_get_result($stmt);
            
            while($item = mysqli_fetch_assoc($items_result)){
                $update_stock = "UPDATE medicines SET quantity = quantity + ? WHERE medicine_id=?";
                $stmt = mysqli_prepare($conn, $update_stock);
                mysqli_stmt_bind_param($stmt, "ii", $item['quantity'], $item['medicine_id']);
                mysqli_stmt_execute($stmt);
            }
        }
        
        $sql = "UPDATE purchase_orders SET status=? WHERE purchase_order_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $status, $purchase_order_id);
        mysqli_stmt_execute($stmt);
        
        $success = "Order status updated successfully!";
    }
}

// Get all suppliers
$suppliers_query = "SELECT * FROM suppliers WHERE status='active' ORDER BY supplier_name ASC";
$suppliers_result = mysqli_query($conn, $suppliers_query);

// Get all medicines
$medicines_query = "SELECT * FROM medicines ORDER BY medicine_name ASC";
$medicines_result = mysqli_query($conn, $medicines_query);

// Get all purchase orders
$orders_query = "SELECT po.*, s.supplier_name, u.fullname as created_by_name
                 FROM purchase_orders po
                 LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id
                 LEFT JOIN users u ON po.created_by = u.user_id
                 ORDER BY po.order_date DESC";
$orders_result = mysqli_query($conn, $orders_query);

// Calculate cart total
$cart_total = 0;
if(isset($_SESSION['purchase_cart'])){
    foreach($_SESSION['purchase_cart'] as $item){
        $cart_total += ($item['buying_price'] * $item['quantity']);
    }
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Orders - Pharmacy Management</title>
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

        .sidebar-menu ul {
            list-style: none;
            padding: 20px 0;
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

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid #22c55e;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

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

        .btn-info {
            background: #3b82f6;
            color: white;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #334155;
            font-weight: 500;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
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
                <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                <li><a href="medicine.php"><i class="fas fa-pills"></i> <span>Dawa</span></a></li>
                <li><a href="inventory_management.php"><i class="fas fa-boxes"></i> <span>Inventory</span></a></li>
                <li><a href="suppliers.php"><i class="fas fa-truck"></i> <span>Suppliers</span></a></li>
                <li><a href="purchase_orders.php" class="active"><i class="fas fa-shopping-cart"></i> <span>Purchase Orders</span></a></li>
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
            <h2><i class="fas fa-shopping-cart" style="color: #3b82f6; margin-right: 10px;"></i> Purchase Orders</h2>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span><?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if(isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Create Purchase Order -->
        <div class="container" style="margin-bottom: 30px;">
            <div class="header">
                <h2>➕ Agiza Dawa kutoka Supplier</h2>
            </div>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label>Chagua Supplier</label>
                        <select name="supplier_id" required>
                            <option value="">-- Chagua Supplier --</option>
                            <?php while($supplier = mysqli_fetch_assoc($suppliers_result)): ?>
                            <option value="<?php echo $supplier['supplier_id']; ?>">
                                <?php echo $supplier['supplier_name']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tarehe ya Kupeleka (Expected)</label>
                        <input type="date" name="expected_delivery_date" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Maelezo</label>
                    <textarea name="notes" rows="2"></textarea>
                </div>

                <button type="submit" name="create_order" class="btn btn-success">📦 Tuma Agizo</button>
            </form>
        </div>

        <!-- Cart Section -->
        <div class="container" style="margin-bottom: 30px;">
            <div class="header">
                <h2>🛒 Cart Yako</h2>
            </div>

            <?php if(!empty($_SESSION['purchase_cart'])): ?>
            <table>
                <thead>
                    <tr>
                        <th>Dawa</th>
                        <th>Bei ya Kununua</th>
                        <th>Idadi</th>
                        <th>Jumla</th>
                        <th>Matendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($_SESSION['purchase_cart'] as $index => $item): ?>
                    <tr>
                        <td><?php echo $item['medicine_name']; ?></td>
                        <td><?php echo number_format($item['buying_price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo number_format($item['buying_price'] * $item['quantity'], 2); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="cart_index" value="<?php echo $index; ?>">
                                <button type="submit" name="remove_from_cart" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">Ondoa</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="text-align: right; margin: 20px 0;">
                <h3>Jumla: TZS <?php echo number_format($cart_total, 2); ?></h3>
            </div>
            <?php else: ?>
            <div class="alert alert-danger">Cart yako ni tupu</div>
            <?php endif; ?>
        </div>

        <!-- Add Medicine to Cart -->
        <div class="container" style="margin-bottom: 30px;">
            <div class="header">
                <h2>➕ Ongeza Dawa kwenye Cart</h2>
            </div>

            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Chagua Dawa</label>
                        <select name="medicine_id" required>
                            <option value="">-- Chagua Dawa --</option>
                            <?php while($medicine = mysqli_fetch_assoc($medicines_result)): ?>
                            <option value="<?php echo $medicine['medicine_id']; ?>">
                                <?php echo $medicine['medicine_name']; ?> (TZS <?php echo number_format($medicine['buying_price'], 2); ?>)
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Idadi</label>
                        <input type="number" name="quantity" min="1" value="1" required>
                    </div>
                </div>

                <button type="submit" name="add_to_cart" class="btn btn-info">➕ Ongeza kwenye Cart</button>
            </form>
        </div>

        <!-- Purchase Orders List -->
        <div class="container">
            <div class="header">
                <h2>📋 Orodha ya Purchase Orders</h2>
            </div>

            <?php if(mysqli_num_rows($orders_result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Supplier</th>
                        <th>Tarehe</th>
                        <th>Expected Delivery</th>
                        <th>Jumla</th>
                        <th>Status</th>
                        <th>Matendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = mysqli_fetch_assoc($orders_result)): ?>
                    <tr>
                        <td>#<?php echo $order['purchase_order_id']; ?></td>
                        <td><?php echo $order['supplier_name'] ?: '-'; ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                        <td><?php echo $order['expected_delivery_date'] ?: '-'; ?></td>
                        <td>TZS <?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <?php
                            $status_class = '';
                            switch($order['status']){
                                case 'pending': $status_class = 'alert-warning'; break;
                                case 'ordered': $status_class = 'alert-info'; break;
                                case 'received': $status_class = 'alert-success'; break;
                                case 'cancelled': $status_class = 'alert-danger'; break;
                            }
                            ?>
                            <span class="alert <?php echo $status_class; ?>" style="display: inline-block; padding: 5px 10px; font-size: 12px;">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if($order['status'] == 'ordered'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="purchase_order_id" value="<?php echo $order['purchase_order_id']; ?>">
                                <input type="hidden" name="status" value="received">
                                <button type="submit" name="update_status" class="btn btn-success" style="padding: 5px 10px; font-size: 12px;">Receive</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="alert alert-danger">Hakuna purchase orders bado</div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
