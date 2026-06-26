<?php
session_start();
include("config.php");
include("functions.php");

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

// Handle user deletion
if(isset($_POST['delete_user'])){
    // Verify CSRF token
    if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
        $error = "Security token invalid";
    } else {
        $user_id = $_POST['user_id'];
        $sql = "DELETE FROM users WHERE user_id=? AND user_id!=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        header("Location: admin_users.php");
        exit();
    }
}

// Handle user creation
if(isset($_POST['add_user'])){
    // Verify CSRF token
    if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
        $error = "Security token invalid";
    } else {
        $username = sanitize_input($_POST['username']);
        $fullname = sanitize_input($_POST['fullname']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $password = $_POST['password'];
        $role = $_POST['role'];

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
            $password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (username, password, fullname, email, phone, role)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssss", $username, $password, $fullname, $email, $phone, $role);
            mysqli_stmt_execute($stmt);
            header("Location: admin_users.php");
            exit();
        }
    }
}

// Get all users except current admin
$users_query = "SELECT * FROM users WHERE user_id!=? ORDER BY user_id DESC";
$stmt = mysqli_prepare($conn, $users_query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$users_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - Admin</title>
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
                <li><a href="admin_users.php" class="active"><i class="fas fa-users"></i> <span>Famasia</span></a></li>
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
            <h2>👥 Manage Famasia</h2>
            <div class="user-info">
                <span>Admin: <?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <div class="container">
            <div class="header">
                <h2>➕ Ongeza Famasia Mpya</h2>
            </div>

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
                        <label>Role</label>
                        <select name="role" required>
                            <option value="seller">Seller</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="Email">
                    </div>
                    <div class="form-group">
                        <label>Simu</label>
                        <input type="tel" name="phone" placeholder="Namba ya simu">
                    </div>
                </div>
                <button type="submit" name="add_user" class="btn btn-success">➕ Ongeza Famasia</button>
            </form>
        </div>

        <div class="container" style="margin-top: 30px;">
            <div class="header">
                <h2>📋 Orodha ya wa famasia</h2>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Jina Kamili</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Matendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = mysqli_fetch_assoc($users_result)): ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['fullname']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td>
                            <span class="alert <?php echo $user['role'] == 'admin' ? 'alert-warning' : 'alert-info'; ?>" style="padding: 5px 10px; font-size: 12px;">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <button type="submit" name="delete_user" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="return confirm('Una hakika unataka kufuta famasia huu?');">🗑️ Delete</button>
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
