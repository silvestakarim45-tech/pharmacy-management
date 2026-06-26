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
$customer_query = "SELECT * FROM customers WHERE user_id=?";
$stmt = mysqli_prepare($conn, $customer_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$customer_result = mysqli_stmt_get_result($stmt);
$customer = mysqli_fetch_assoc($customer_result);

// Get all available medicines
$medicines_query = "SELECT * FROM medicines WHERE quantity > 0 ORDER BY medicine_name ASC";
$medicines_result = mysqli_query($conn, $medicines_query);

// Handle add to cart
if(isset($_POST['add_to_cart'])){
    $medicine_id = $_POST['medicine_id'];
    $quantity = $_POST['quantity'];

    if(!isset($_SESSION['cart'])){
        $_SESSION['cart'] = array();
    }

    // Check if medicine already in cart
    $found = false;
    foreach($_SESSION['cart'] as &$item){
        if($item['medicine_id'] == $medicine_id){
            $item['quantity'] += $quantity;
            $found = true;
            break;
        }
    }

    if(!$found){
        // Get medicine details
        $med_query = "SELECT * FROM medicines WHERE medicine_id=?";
        $stmt = mysqli_prepare($conn, $med_query);
        mysqli_stmt_bind_param($stmt, "i", $medicine_id);
        mysqli_stmt_execute($stmt);
        $med_result = mysqli_stmt_get_result($stmt);
        $medicine = mysqli_fetch_assoc($med_result);

        $_SESSION['cart'][] = array(
            'medicine_id' => $medicine_id,
            'medicine_name' => $medicine['medicine_name'],
            'price' => $medicine['selling_price'],
            'quantity' => $quantity
        );
    }
}

// Handle remove from cart
if(isset($_POST['remove_from_cart'])){
    $index = $_POST['cart_index'];
    unset($_SESSION['cart'][$index]);
    $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
}

// Handle place order
if(isset($_POST['place_order'])){
    if(!empty($_SESSION['cart'])){
        $customer_id = $customer['customer_id'];
        $total_amount = 0;

        // Calculate total
        foreach($_SESSION['cart'] as $item){
            $total_amount += ($item['price'] * $item['quantity']);
        }

        // Insert order
        $order_query = "INSERT INTO orders (customer_id, total_amount, status, delivery_address, phone, notes)
                        VALUES (?, ?, 'pending', ?, ?, '')";
        $stmt = mysqli_prepare($conn, $order_query);
        mysqli_stmt_bind_param($stmt, "idss", $customer_id, $total_amount, $customer['address'], $customer['phone']);
        mysqli_stmt_execute($stmt);
        $order_id = mysqli_insert_id($conn);

        // Insert order items
        foreach($_SESSION['cart'] as $item){
            $subtotal = $item['price'] * $item['quantity'];
            $item_query = "INSERT INTO order_items (order_id, medicine_id, quantity, price, subtotal)
                          VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $item_query);
            mysqli_stmt_bind_param($stmt, "iiidd", $order_id, $item['medicine_id'], $item['quantity'], $item['price'], $subtotal);
            mysqli_stmt_execute($stmt);

            // Update medicine quantity
            $update_stock = "UPDATE medicines SET quantity = quantity - ? WHERE medicine_id = ?";
            $stmt = mysqli_prepare($conn, $update_stock);
            mysqli_stmt_bind_param($stmt, "ii", $item['quantity'], $item['medicine_id']);
            mysqli_stmt_execute($stmt);
        }

            // Clear cart
            unset($_SESSION['cart']);
            $order_success = "Agizo lako limetumwa kwa mafanikio!";
        }
    }
}

// Calculate cart total
$cart_total = 0;
if(isset($_SESSION['cart'])){
    foreach($_SESSION['cart'] as $item){
        $cart_total += ($item['price'] * $item['quantity']);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Dashboard - Pharmacy</title>
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
                <li><a href="customer_dashboard.php" class="active"><i class="fas fa-shopping-cart"></i> <span>Dawa</span></a></li>
                <li><a href="customer_orders.php"><i class="fas fa-list"></i> <span>Agizo Zangu</span></a></li>
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
            <h2>🛒 Dawa za Kununua</h2>
            <div class="user-info">
                <span>Karibu, <?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <?php if(isset($order_success)): ?>
        <div class="alert alert-success">
            <?php echo $order_success; ?>
        </div>
        <?php endif; ?>

        <!-- Cart Section -->
        <div class="container" style="margin-bottom: 30px;">
            <div class="header">
                <h2>🛒 Cart Yako</h2>
            </div>

            <?php if(!empty($_SESSION['cart'])): ?>
            <table>
                <thead>
                    <tr>
                        <th>Dawa</th>
                        <th>Bei</th>
                        <th>Idadi</th>
                        <th>Jumla</th>
                        <th>Matendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($_SESSION['cart'] as $index => $item): ?>
                    <tr>
                        <td><?php echo $item['medicine_name']; ?></td>
                        <td><?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
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

            <form method="POST">
                <button type="submit" name="place_order" class="btn btn-success">📦 Tuma Agizo</button>
            </form>
            <?php else: ?>
            <div class="alert alert-info">Cart yako ni tupu</div>
            <?php endif; ?>
        </div>

        <!-- Medicines Section -->
        <div class="container">
            <div class="header">
                <h2>💊 Dawa Zinazopatikana</h2>
            </div>

            <?php if(mysqli_num_rows($medicines_result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Jina la Dawa</th>
                        <th>Kategori</th>
                        <th>Maelezo</th>
                        <th>Bei</th>
                        <th>Stock</th>
                        <th>Matendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($medicine = mysqli_fetch_assoc($medicines_result)): ?>
                    <tr>
                        <td><strong><?php echo $medicine['medicine_name']; ?></strong></td>
                        <td><?php echo $medicine['category']; ?></td>
                        <td><?php echo $medicine['description'] ? $medicine['description'] : '-'; ?></td>
                        <td>TZS <?php echo number_format($medicine['selling_price'], 2); ?></td>
                        <td><?php echo $medicine['quantity']; ?></td>
                        <td>
                            <?php if($medicine['quantity'] > 0): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="medicine_id" value="<?php echo $medicine['medicine_id']; ?>">
                                <input type="number" name="quantity" value="1" min="1" max="<?php echo $medicine['quantity']; ?>" style="width: 60px; padding: 5px;">
                                <button type="submit" name="add_to_cart" class="btn btn-success" style="padding: 5px 10px; font-size: 12px;">+ Ongeza</button>
                            </form>
                            <?php else: ?>
                            <span style="color: #e74c3c;">Haijapatikana</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="alert alert-warning">Hakuna dawa zinazopatikana kwa sasa</div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>