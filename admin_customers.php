<?php
session_start();
include("config.php");
include("functions.php");

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

// Handle customer deletion
if(isset($_POST['delete_customer'])){
    // Verify CSRF token
    if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
        $error = "Security token invalid";
    } else {
        $customer_id = $_POST['customer_id'];
        $sql = "DELETE FROM customers WHERE customer_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $customer_id);
        mysqli_stmt_execute($stmt);
        header("Location: admin_customers.php");
        exit();
    }
}

// Handle customer creation
if(isset($_POST['add_customer'])){
    // Verify CSRF token
    if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
        $error = "Security token invalid";
    } else {
        $username = sanitize_input($_POST['username']);
        $fullname = sanitize_input($_POST['fullname']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $address = sanitize_input($_POST['address']);
        $password = $_POST['password'];

        // Validate inputs
        if(!validate_username($username)){
            $error = "Username format batili";
        } elseif(!validate_email($email)){
            $error = "Email batili";
        } elseif(!validate_phone($phone)){
            $error = "Namba ya simu batili";
        } elseif(!validate_password($password)){
            $error = "Password inapaswa kuwa angalau characters 6";
        } else {
            // Check if username already exists
            $check_username = "SELECT * FROM users WHERE username=?";
            $stmt = mysqli_prepare($conn, $check_username);
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if(mysqli_num_rows($result) > 0){
                $error = "Username tayari ipo!";
            } else {
                $password = password_hash($password, PASSWORD_DEFAULT);

                // Insert into users table
                $sql = "INSERT INTO users (username, password, fullname, email, phone, role)
                        VALUES (?, ?, ?, ?, ?, 'customer')";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sssss", $username, $password, $fullname, $email, $phone);
                mysqli_stmt_execute($stmt);
                $user_id = mysqli_insert_id($conn);

                // Insert into customers table
                $customer_sql = "INSERT INTO customers (user_id, customer_name, phone, email, address)
                                VALUES (?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $customer_sql);
                mysqli_stmt_bind_param($stmt, "issss", $user_id, $fullname, $phone, $email, $address);
                if(mysqli_stmt_execute($stmt)){
                    header("Location: admin_customers.php");
                    exit();
                } else {
                    $error = "Error creating customer profile: " . mysqli_error($conn);
                }
            }
        }
    }
}

// Get all customers with user info
$customers_query = "SELECT c.*, u.username, u.email FROM customers c
                   JOIN users u ON c.user_id = u.user_id
                   ORDER BY c.customer_id DESC";
$customers_result = mysqli_query($conn, $customers_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Customers - Admin</title>
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
                <li><a href="suppliers.php"><i class="fas fa-truck"></i> <span>Suppliers</span></a></li>
                <li><a href="sales_analytics.php"><i class="fas fa-chart-bar"></i> <span>Analytics</span></a></li>
                <li><a href="audit_logs.php"><i class="fas fa-history"></i> <span>Audit Logs</span></a></li>
                <li><a href="database_backup.php"><i class="fas fa-database"></i> <span>Backup</span></a></li>
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
            <h2>👥 Manage Customers</h2>
            <div class="user-info">
                <span>Admin: <?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <div class="container">
            <div class="header">
                <h2>➕ Ongeza Mteja Mpya</h2>
            </div>

            <?php if(isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" placeholder="Username" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Jina Kamili</label>
                        <input type="text" name="fullname" placeholder="Jina kamili" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="Email">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Simu</label>
                        <input type="tel" name="phone" placeholder="Namba ya simu">
                    </div>
                    <div class="form-group">
                        <label>Anuani</label>
                        <input type="text" name="address" placeholder="Anuani">
                    </div>
                </div>
                <button type="submit" name="add_customer" class="btn btn-success">➕ Ongeza Mteja</button>
            </form>
        </div>

        <div class="container" style="margin-top: 30px;">
            <div class="header">
                <h2>📋 Orodha ya Wateja</h2>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Jina la Mteja</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Simu</th>
                        <th>Anuani</th>
                        <th>Matendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($customer = mysqli_fetch_assoc($customers_result)): ?>
                    <tr>
                        <td><?php echo $customer['customer_id']; ?></td>
                        <td><?php echo $customer['customer_name']; ?></td>
                        <td><?php echo $customer['username']; ?></td>
                        <td><?php echo $customer['email']; ?></td>
                        <td><?php echo $customer['phone']; ?></td>
                        <td><?php echo $customer['address'] ? $customer['address'] : '-'; ?></td>
                        <td>
                            <a href="view_customer.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-info" style="padding: 5px 10px; font-size: 12px;">👁️ View</a>
                            <a href="edit_customer.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-warning" style="padding: 5px 10px; font-size: 12px;">✏️ Edit</a>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="customer_id" value="<?php echo $customer['customer_id']; ?>">
                                <button type="submit" name="delete_customer" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="return confirm('Una hakika unataka kufuta mteja huu?');">🗑️ Delete</button>
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
