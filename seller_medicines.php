<?php
session_start();
include("config.php");

// Check if user is logged in and is seller
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller'){
    header("Location: login.php");
    exit();
}

// Handle search
if(isset($_GET['search'])){
    $search = $_GET['search'];
    $search_param = "%$search%";
    $sql = "SELECT * FROM medicines WHERE medicine_name LIKE ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $search_param);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, "SELECT * FROM medicines");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Medicines - Seller</title>
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
                <li><a href="seller_medicines.php" class="active"><i class="fas fa-pills"></i> <span>Dawa</span></a></li>
                <li><a href="seller_orders.php"><i class="fas fa-shopping-bag"></i> <span>Agizo Mtandaoni</span></a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-header">
            <h2>📦 Orodha ya Dawa</h2>
            <div class="user-info">
                <span>Karibu, <?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <div class="container">
            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="Tafuta dawa...">
                <button type="submit">🔍 Tafuta</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Jina la Dawa</th>
                        <th>Kategori</th>
                        <th>Idadi</th>
                        <th>Bei ya Kuuza</th>
                        <th>Tarehe ya Kuisha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while($row = mysqli_fetch_assoc($result)){
                    ?>
                    <tr>
                        <td><?php echo $row['medicine_id']; ?></td>
                        <td><?php echo $row['medicine_name']; ?></td>
                        <td><?php echo $row['category']; ?></td>
                        <td>
                            <span style="color: <?php echo $row['quantity'] < 10 ? '#e74c3c' : '#27ae60'; ?>; font-weight: bold;">
                                <?php echo $row['quantity']; ?>
                            </span>
                        </td>
                        <td>TZS <?php echo number_format($row['selling_price'], 2); ?></td>
                        <td><?php echo $row['expiry_date']; ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
