<?php
session_start();
include("config.php");

// Check if user is logged in and is a seller
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller'){
    header("Location: login.php");
    exit();
}

// Get seller info
$user_id = $_SESSION['user_id'];
$seller_query = "SELECT * FROM users WHERE user_id='$user_id'";
$seller_result = mysqli_query($conn, $seller_query);
$seller = mysqli_fetch_assoc($seller_result);

// Handle profile update
if(isset($_POST['update_profile'])){
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Update users table
    $update_user = "UPDATE users SET fullname='$fullname', email='$email', phone='$phone' WHERE user_id='$user_id'";
    mysqli_query($conn, $update_user);

    // Refresh seller data
    $seller_result = mysqli_query($conn, $seller_query);
    $seller = mysqli_fetch_assoc($seller_result);

    // Update session
    $_SESSION['fullname'] = $fullname;

    $profile_updated = "Profaili yako imesasishwa kwa mafanikio!";
}

// Handle password change
if(isset($_POST['change_password'])){
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password
    if(password_verify($current_password, $seller['password'])){
        // Check if new passwords match
        if($new_password === $confirm_password){
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $update_password = "UPDATE users SET password='$hashed_password' WHERE user_id='$user_id'";
            mysqli_query($conn, $update_password);
            
            $password_updated = "Password imebadilishwa kwa mafanikio!";
        } else {
            $password_error = "Password mpya hazilingani!";
        }
    } else {
        $password_error = "Password ya sasa si sahihi!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile - Seller</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="admin-panel">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>🏥 Pharmacy</h1>
            <p>Seller Portal</p>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="seller_dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                <li><a href="seller_sales.php"><i class="fas fa-cash-register"></i> <span>Mauzo</span></a></li>
                <li><a href="seller_medicines.php"><i class="fas fa-pills"></i> <span>Dawa</span></a></li>
                <li><a href="seller_orders.php"><i class="fas fa-shopping-bag"></i> <span>Agizo Mtandaoni</span></a></li>
                <li><a href="seller_profile.php" class="active"><i class="fas fa-user"></i> <span>Profaili Yangu</span></a></li>
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

            <?php if(isset($password_updated)): ?>
            <div class="alert alert-success">
                <?php echo $password_updated; ?>
            </div>
            <?php endif; ?>

            <?php if(isset($password_error)): ?>
            <div class="alert alert-danger">
                <?php echo $password_error; ?>
            </div>
            <?php endif; ?>

            <div class="header">
                <h2>📝 Maelezo ya Kibinafsi</h2>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Jina Kamili</label>
                    <input type="text" name="fullname" value="<?php echo $seller['fullname']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" value="<?php echo $seller['username']; ?>" disabled style="background: #f8f9fa;">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo $seller['email']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Namba ya Simu</label>
                    <input type="tel" name="phone" value="<?php echo $seller['phone']; ?>" required>
                </div>

                <button type="submit" name="update_profile" class="btn btn-success">Sasisha Profaili</button>
            </form>

            <div class="header" style="margin-top: 40px;">
                <h2>🔐 Badilisha Password</h2>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Password ya Sasa</label>
                    <input type="password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label>Password Mpya</label>
                    <input type="password" name="new_password" required minlength="6">
                </div>

                <div class="form-group">
                    <label>Rudia Password Mpya</label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>

                <button type="submit" name="change_password" class="btn btn-warning">Badilisha Password</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
