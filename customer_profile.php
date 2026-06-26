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
$customer_query = "SELECT u.*, c.* FROM users u JOIN customers c ON u.user_id = c.user_id WHERE u.user_id='$user_id'";
$customer_result = mysqli_query($conn, $customer_query);
$customer = mysqli_fetch_assoc($customer_result);

// Handle profile update
if(isset($_POST['update_profile'])){
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Update users table
    $update_user = "UPDATE users SET fullname='$fullname', email='$email', phone='$phone' WHERE user_id='$user_id'";
    mysqli_query($conn, $update_user);

    // Update customers table
    $update_customer = "UPDATE customers SET customer_name='$fullname', phone='$phone', email='$email', address='$address' WHERE user_id='$user_id'";
    mysqli_query($conn, $update_customer);

    // Refresh customer data
    $customer_result = mysqli_query($conn, $customer_query);
    $customer = mysqli_fetch_assoc($customer_result);

    $profile_updated = "Profaili yako imesasishwa kwa mafanikio!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile - Pharmacy</title>
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
                <li><a href="customer_dashboard.php"><i class="fas fa-shopping-cart"></i> <span>Dawa</span></a></li>
                <li><a href="customer_orders.php"><i class="fas fa-list"></i> <span>Agizo Zangu</span></a></li>
                <li><a href="customer_profile.php" class="active"><i class="fas fa-user"></i> <span>Profaili Yangu</span></a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-header">
            <h2>👤 Profaili Yangu</h2>
            <div class="user-info">
                <span>Karibu, <?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <div class="container">
            <?php if(isset($profile_updated)): ?>
            <div class="alert alert-success">
                <?php echo $profile_updated; ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Jina Kamili</label>
                    <input type="text" name="fullname" value="<?php echo $customer['fullname']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" value="<?php echo $customer['username']; ?>" disabled style="background: #f8f9fa;">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo $customer['email']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Namba ya Simu</label>
                    <input type="tel" name="phone" value="<?php echo $customer['phone']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Anuani</label>
                    <textarea name="address" rows="3" required><?php echo $customer['address']; ?></textarea>
                </div>

                <button type="submit" name="update_profile" class="btn btn-success">Sasisha Profaili</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>