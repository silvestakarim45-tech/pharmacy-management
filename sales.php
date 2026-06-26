<?php
session_start();
include("config.php");
include("functions.php");

// Check if user is logged in and is admin or seller
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'seller'])){
    header("Location: login.php");
    exit();
}

$medicines=mysqli_query($conn,
"SELECT * FROM medicines");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales - Pharmacy Management</title>
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
                <li><a href="dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                <li><a href="medicine.php"><i class="fas fa-pills"></i> <span>Dawa</span></a></li>
                <li><a href="sales.php" class="active"><i class="fas fa-cash-register"></i> <span>Mauzo</span></a></li>
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
            <h2>💰 Kuuza Dawa</h2>
            <div class="user-info">
                <span>Karibu, <?php echo isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Admin'; ?></span>
            </div>
        </div>

        <div class="container">
            <?php
            if(isset($_POST['sell'])){
                // Verify CSRF token
                if(!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])){
                    echo "<div class='alert alert-danger'>⚠️ Security token invalid. Tafadhali jaribu tena.</div>";
                } else {
                $medicine_id=$_POST['medicine_id'];
                $quantity_sold=$_POST['quantity_sold'];

                $sql = "SELECT * FROM medicines WHERE medicine_id=?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $medicine_id);
                mysqli_stmt_execute($stmt);
                $medicine = mysqli_stmt_get_result($stmt);
                $data=mysqli_fetch_assoc($medicine);

                $current_stock=$data['quantity'];

                if($quantity_sold > $current_stock){
                    echo "<div class='alert alert-danger'>⚠️ Stock haitoshi! Idadi iliyopo: " . $current_stock . "</div>";
                } else {
                    $new_stock=$current_stock-$quantity_sold;
                    $total_amount=$data['selling_price']*$quantity_sold;

                    $sql = "INSERT INTO sales (medicine_id,quantity_sold,total_amount) VALUES (?,?,?)";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "iid", $medicine_id, $quantity_sold, $total_amount);
                    mysqli_stmt_execute($stmt);

                    $sql = "UPDATE medicines SET quantity=? WHERE medicine_id=?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "ii", $new_stock, $medicine_id);
                    mysqli_stmt_execute($stmt);

                    echo "<div class='alert alert-success'>✅ Mauzo yameerekodiwa kwa mafanikio! Jumla: TZS " . number_format($total_amount, 2) . "</div>";
                }
                }
            }
            ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <div class="form-group">
                    <label>Chagua Dawa</label>
                    <select name="medicine_id" required>
                        <option value="">-- Chagua dawa --</option>
                        <?php
                        while($row=mysqli_fetch_assoc($medicines)){
                        ?>
                        <option value="<?php echo $row['medicine_id']; ?>">
                            <?php echo $row['medicine_name']; ?> (Stock: <?php echo $row['quantity']; ?>) - TZS <?php echo number_format($row['selling_price'], 2); ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Idadi ya Kuuza</label>
                    <input type="number" name="quantity_sold" placeholder="Ingiza idadi" required min="1">
                </div>

                <button type="submit" name="sell" class="btn btn-success">💰 Euza</button>
            </form>

            <div class="report-section" style="margin-top: 30px;">
                <h3>📊 Historia ya Mauzo za Leo</h3>
                <?php
                $today_sales = mysqli_query($conn,"
                SELECT s.*, m.medicine_name
                FROM sales s
                JOIN medicines m ON s.medicine_id = m.medicine_id
                WHERE DATE(sale_date) = CURDATE()
                ORDER BY sale_date DESC
                ");

                if(mysqli_num_rows($today_sales) > 0){
                ?>
                <table>
                    <thead>
                        <tr>
                            <th>Dawa</th>
                            <th>Idadi</th>
                            <th>Jumla (TZS)</th>
                            <th>Saa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while($row=mysqli_fetch_assoc($today_sales)){
                        ?>
                        <tr>
                            <td><?php echo $row['medicine_name']; ?></td>
                            <td><?php echo $row['quantity_sold']; ?></td>
                            <td><?php echo number_format($row['total_amount'], 2); ?></td>
                            <td><?php echo date('H:i', strtotime($row['sale_date'])); ?></td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
                <?php
                } else {
                    echo "<div class='alert alert-info'>Hakuna mauzo yaliyorekodiwa leo</div>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>