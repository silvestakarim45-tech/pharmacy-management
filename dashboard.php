<?php
session_start();
include("config.php");

$medicines=mysqli_num_rows(
mysqli_query($conn,"SELECT * FROM medicines")
);

$customers=mysqli_num_rows(
mysqli_query($conn,"SELECT * FROM customers")
);

$sales=mysqli_num_rows(
mysqli_query($conn,"SELECT * FROM sales")
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Pharmacy Management</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="admin-panel">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>🏥 Pharmacy</h1>
            <p>Management System</p>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                <li><a href="medicine.php"><i class="fas fa-pills"></i> <span>Dawa</span></a></li>
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
            <h2>Dashboard</h2>
            <div class="user-info">
                <span>Karibu, <?php echo isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Admin'; ?></span>
            </div>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="icon">📦</div>
                <h3>jumla ya  Dawa</h3>
                <div class="number"><?php echo $medicines; ?></div>
            </div>

            <div class="stat-card success">
                <div class="icon">👥</div>
                <h3>jumla ya Wateja</h3>
                <div class="number"><?php echo $customers; ?></div>
            </div>

            <div class="stat-card warning">
                <div class="icon">💰</div>
                <h3>jumla ya Mauzo</h3>
                <div class="number"><?php echo $sales; ?></div>
            </div>
        </div>

        <div class="container">
            <div class="header">
                <h2>⚠️ Dawa Zilizopo  Stoo</h2>
            </div>
            <?php
            $low_stock=mysqli_query($conn,
            "SELECT * FROM medicines
            WHERE quantity < 10");

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

        <div class="container">
            <div class="header">
                <h2>⏰ Dawa Zinazokaribia Kuisha Muda</h2>
            </div>
            <?php
            $expiry = mysqli_query($conn,"
            SELECT * FROM medicines
            WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            ");

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

</body>
</html>