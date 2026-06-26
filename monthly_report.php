<?php
session_start();
include("config.php");

$result=mysqli_query($conn,"
SELECT s.*, m.medicine_name
FROM sales s
JOIN medicines m ON s.medicine_id = m.medicine_id
WHERE MONTH(sale_date)=MONTH(CURDATE())
AND YEAR(sale_date)=YEAR(CURDATE())
ORDER BY sale_date DESC
");

$total=0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Monthly Report - Pharmacy Management</title>
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
                <li><a href="sales.php"><i class="fas fa-cash-register"></i> <span>Mauzo</span></a></li>
                <li><a href="deily_report.php"><i class="fas fa-chart-line"></i> <span>Ripoti ya Siku</span></a></li>
                <li><a href="monthly_report.php" class="active"><i class="fas fa-calendar-alt"></i> <span>Ripoti ya Mwezi</span></a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-header">
            <h2>📊 Ripoti ya Mauzo ya Mwezi</h2>
            <div class="user-info">
                <span>Karibu, <?php echo isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Admin'; ?></span>
            </div>
        </div>

        <div class="container">
            <div class="header">
                <h2>Mauzo yote ya mwezi wa: <?php echo date('F Y'); ?></h2>
            </div>

            <?php if(mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Dawa</th>
                        <th>Idadi</th>
                        <th>Jumla (TZS)</th>
                        <th>Tarehe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while($row=mysqli_fetch_assoc($result)){
                        $total += $row['total_amount'];
                    ?>
                    <tr>
                        <td><?php echo $row['sale_id']; ?></td>
                        <td><?php echo $row['medicine_name']; ?></td>
                        <td><?php echo $row['quantity_sold']; ?></td>
                        <td><?php echo number_format($row['total_amount'], 2); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($row['sale_date'])); ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>

            <div class="report-section">
                <h3>💰 Jumla ya Mauzo ya Mwezi</h3>
                <div class="total-amount">TZS <?php echo number_format($total, 2); ?></div>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                Hakuna mauzo yaliyorekodiwa mwezi huu.
            </div>
            <?php endif; ?>

            <div style="margin-top: 30px;">
                <a href="dashboard.php" class="btn">← Rudi kwenye Dashboard</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>