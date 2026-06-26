<?php
session_start();
include("config.php");
include("functions.php");

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

// Handle supplier creation
if(isset($_POST['add_supplier'])){
    if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
        $error = "Security token invalid";
    } else {
        $supplier_name = sanitize_input($_POST['supplier_name']);
        $contact_person = sanitize_input($_POST['contact_person']);
        $phone = sanitize_input($_POST['phone']);
        $email = sanitize_input($_POST['email']);
        $address = sanitize_input($_POST['address']);
        $city = sanitize_input($_POST['city']);
        $country = sanitize_input($_POST['country']);
        $notes = sanitize_input($_POST['notes']);

        $sql = "INSERT INTO suppliers (supplier_name, contact_person, phone, email, address, city, country, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssss", $supplier_name, $contact_person, $phone, $email, $address, $city, $country, $notes);
        mysqli_stmt_execute($stmt);
        
        $success = "Supplier added successfully";
    }
}

// Handle supplier update
if(isset($_POST['update_supplier'])){
    if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
        $error = "Security token invalid";
    } else {
        $supplier_id = $_POST['supplier_id'];
        $supplier_name = sanitize_input($_POST['supplier_name']);
        $contact_person = sanitize_input($_POST['contact_person']);
        $phone = sanitize_input($_POST['phone']);
        $email = sanitize_input($_POST['email']);
        $address = sanitize_input($_POST['address']);
        $city = sanitize_input($_POST['city']);
        $country = sanitize_input($_POST['country']);
        $notes = sanitize_input($_POST['notes']);
        $status = $_POST['status'];

        $sql = "UPDATE suppliers SET supplier_name=?, contact_person=?, phone=?, email=?, address=?, city=?, country=?, notes=?, status=?
                WHERE supplier_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssssi", $supplier_name, $contact_person, $phone, $email, $address, $city, $country, $notes, $status, $supplier_id);
        mysqli_stmt_execute($stmt);
        
        $success = "Supplier updated successfully";
    }
}

// Handle supplier deletion
if(isset($_POST['delete_supplier'])){
    if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
        $error = "Security token invalid";
    } else {
        $supplier_id = $_POST['supplier_id'];
        $sql = "DELETE FROM suppliers WHERE supplier_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $supplier_id);
        mysqli_stmt_execute($stmt);
        
        $success = "Supplier deleted successfully";
    }
}

// Get all suppliers
$suppliers_query = "SELECT * FROM suppliers ORDER BY supplier_name ASC";
$suppliers_result = mysqli_query($conn, $suppliers_query);

// Get supplier for editing
$edit_supplier = null;
if(isset($_GET['edit'])){
    $supplier_id = $_GET['edit'];
    $sql = "SELECT * FROM suppliers WHERE supplier_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $supplier_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $edit_supplier = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Supplier Management - Pharmacy</title>
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
                <li><a href="suppliers.php" class="active"><i class="fas fa-truck"></i> <span>Suppliers</span></a></li>
                <li><a href="sales_analytics.php"><i class="fas fa-chart-bar"></i> <span>Analytics</span></a></li>
                <li><a href="audit_logs.php"><i class="fas fa-history"></i> <span>Audit Logs</span></a></li>
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
            <h2>🚚 Usimamizi wa Suppliers</h2>
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

        <!-- Add/Edit Supplier Form -->
        <div class="container" style="margin-bottom: 30px;">
            <div class="header">
                <h2><?php echo $edit_supplier ? '✏️ Hariri Supplier' : '➕ Ongeza Supplier Mpya'; ?></h2>
            </div>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <?php if($edit_supplier): ?>
                <input type="hidden" name="supplier_id" value="<?php echo $edit_supplier['supplier_id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Jina la Supplier</label>
                        <input type="text" name="supplier_name" value="<?php echo $edit_supplier['supplier_name'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Mtumiaji wa Mawasiliano</label>
                        <input type="text" name="contact_person" value="<?php echo $edit_supplier['contact_person'] ?? ''; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Simu</label>
                        <input type="tel" name="phone" value="<?php echo $edit_supplier['phone'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo $edit_supplier['email'] ?? ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Anuani</label>
                    <textarea name="address" rows="2"><?php echo $edit_supplier['address'] ?? ''; ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Jiji</label>
                        <input type="text" name="city" value="<?php echo $edit_supplier['city'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Nchi</label>
                        <input type="text" name="country" value="<?php echo $edit_supplier['country'] ?? 'Tanzania'; ?>">
                    </div>
                </div>
                
                <?php if($edit_supplier): ?>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="active" <?php echo $edit_supplier['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $edit_supplier['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Maelezo</label>
                    <textarea name="notes" rows="2"><?php echo $edit_supplier['notes'] ?? ''; ?></textarea>
                </div>
                
                <div class="form-actions">
                    <?php if($edit_supplier): ?>
                    <button type="submit" name="update_supplier" class="btn btn-success">💾 Save Changes</button>
                    <a href="suppliers.php" class="btn btn-warning">Cancel</a>
                    <?php else: ?>
                    <button type="submit" name="add_supplier" class="btn btn-success">➕ Ongeza Supplier</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Suppliers List -->
        <div class="container">
            <div class="header">
                <h2>📋 Orodha ya Suppliers</h2>
            </div>

            <?php if(mysqli_num_rows($suppliers_result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Jina la Supplier</th>
                        <th>Mtumiaji</th>
                        <th>Simu</th>
                        <th>Email</th>
                        <th>Jiji</th>
                        <th>Status</th>
                        <th>Matendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($supplier = mysqli_fetch_assoc($suppliers_result)): ?>
                    <tr>
                        <td><?php echo $supplier['supplier_id']; ?></td>
                        <td><strong><?php echo $supplier['supplier_name']; ?></strong></td>
                        <td><?php echo $supplier['contact_person'] ?: '-'; ?></td>
                        <td><?php echo $supplier['phone'] ?: '-'; ?></td>
                        <td><?php echo $supplier['email'] ?: '-'; ?></td>
                        <td><?php echo $supplier['city'] ?: '-'; ?></td>
                        <td>
                            <span class="alert <?php echo $supplier['status'] == 'active' ? 'alert-success' : 'alert-danger'; ?>" style="padding: 5px 10px; font-size: 12px;">
                                <?php echo ucfirst($supplier['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="suppliers.php?edit=<?php echo $supplier['supplier_id']; ?>" class="btn btn-info" style="padding: 5px 10px; font-size: 12px;">✏️ Edit</a>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="supplier_id" value="<?php echo $supplier['supplier_id']; ?>">
                                <button type="submit" name="delete_supplier" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="return confirm('Una hakika unataka kufuta supplier huu?');">🗑️ Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="alert alert-info">Hakuna suppliers bado</div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
