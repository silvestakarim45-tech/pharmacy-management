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

// Handle customer update
if(isset($_POST['update_customer'])){
    // Verify CSRF token
    if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
        $error = "Security token invalid. Tafadhali jaribu tena.";
    } else {
        $customer_name = sanitize_input($_POST['customer_name']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $address = sanitize_input($_POST['address']);

        // Validate inputs
        if(!validate_email($email)){
            $error = "Email batili";
        } elseif(!validate_phone($phone)){
            $error = "Namba ya simu batili";
        } else {
            // Update customer
            $sql = "UPDATE customers SET customer_name=?, email=?, phone=?, address=? WHERE customer_id=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssi", $customer_name, $email, $phone, $address, $customer_id);

            try {
                mysqli_stmt_execute($stmt);
                header("Location: admin_customers.php");
                exit();
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}

// Get customer details
$sql = "SELECT * FROM customers WHERE customer_id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$customer = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Customer - Admin</title>
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
            <h2>✏️ Edit Customer</h2>
            <div class="user-info">
                <span>Admin: <?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <div class="container">
            <div class="header">
                <h2>Hariri Mteja: <?php echo $customer['customer_name']; ?></h2>
            </div>

            <?php if(isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <div class="form-group">
                    <label>Jina la Mteja</label>
                    <input type="text" name="customer_name" value="<?php echo $customer['customer_name']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo $customer['email']; ?>">
                </div>
                <div class="form-group">
                    <label>Simu</label>
                    <input type="tel" name="phone" value="<?php echo $customer['phone']; ?>">
                </div>
                <div class="form-group">
                    <label>Anuani</label>
                    <textarea name="address" rows="3"><?php echo $customer['address']; ?></textarea>
                </div>
                <button type="submit" name="update_customer" class="btn btn-success">💾 Save Changes</button>
                <a href="admin_customers.php" class="btn btn-warning">← Cancel</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
