<?php
session_start();
include("config.php");

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

// Get date range filters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get sales statistics
$total_sales_query = "SELECT SUM(total_amount) as total, COUNT(*) as count FROM sales 
                      WHERE DATE(sale_date) BETWEEN '$start_date' AND '$end_date'";
$total_sales_result = mysqli_query($conn, $total_sales_query);
$total_sales = mysqli_fetch_assoc($total_sales_result);

// Get orders statistics
$total_orders_query = "SELECT SUM(total_amount) as total, COUNT(*) as count FROM orders 
                       WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date' AND status='completed'";
$total_orders_result = mysqli_query($conn, $total_orders_query);
$total_orders = mysqli_fetch_assoc($total_orders_result);

// Get top selling medicines
$top_medicines_query = "SELECT m.medicine_name, SUM(s.quantity_sold) as total_sold, SUM(s.total_amount) as revenue 
                        FROM sales s 
                        JOIN medicines m ON s.medicine_id = m.medicine_id 
                        WHERE DATE(s.sale_date) BETWEEN '$start_date' AND '$end_date'
                        GROUP BY m.medicine_id, m.medicine_name 
                        ORDER BY total_sold DESC LIMIT 10";
$top_medicines_result = mysqli_query($conn, $top_medicines_query);

// Get daily sales for chart
$daily_sales_query = "SELECT DATE(sale_date) as date, SUM(total_amount) as total 
                     FROM sales 
                     WHERE DATE(sale_date) BETWEEN '$start_date' AND '$end_date'
                     GROUP BY DATE(sale_date) 
                     ORDER BY date ASC";
$daily_sales_result = mysqli_query($conn, $daily_sales_query);

// Get category sales
$category_sales_query = "SELECT m.category, SUM(s.total_amount) as total 
                        FROM sales s 
                        JOIN medicines m ON s.medicine_id = m.medicine_id 
                        WHERE DATE(s.sale_date) BETWEEN '$start_date' AND '$end_date'
                        GROUP BY m.category 
                        ORDER BY total DESC";
$category_sales_result = mysqli_query($conn, $category_sales_query);

// Get profit calculation
$profit_query = "SELECT SUM(s.total_amount - (s.quantity_sold * m.buying_price)) as profit 
                 FROM sales s 
                 JOIN medicines m ON s.medicine_id = m.medicine_id 
                 WHERE DATE(s.sale_date) BETWEEN '$start_date' AND '$end_date'";
$profit_result = mysqli_query($conn, $profit_query);
$profit = mysqli_fetch_assoc($profit_result)['profit'] ?: 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales Analytics - Pharmacy</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <li><a href="sales_analytics.php" class="active"><i class="fas fa-chart-bar"></i> <span>Analytics</span></a></li>
                <li><a href="admin_users.php"><i class="fas fa-users"></i> <span>Famasia</span></a></li>
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
            <h2>📊 Sales Analytics</h2>
            <div class="user-info">
                <span>Admin: <?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="container" style="margin-bottom: 20px;">
            <form method="GET" class="date-filter-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Tarehe ya Kuanza</label>
                        <input type="date" name="start_date" value="<?php echo $start_date; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Tarehe ya Kumaliza</label>
                        <input type="date" name="end_date" value="<?php echo $end_date; ?>" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-success">🔍 Filter</button>
                <a href="sales_analytics.php" class="btn btn-warning">↻ Reset</a>
            </form>
        </div>

        <!-- Key Statistics -->
        <div class="dashboard-stats">
            <div class="stat-card success">
                <div class="icon">💰</div>
                <h3>Jumla ya Mauzo (POS)</h3>
                <div class="number">TZS <?php echo number_format($total_sales['total'] ?: 0, 0); ?></div>
                <div class="sub-text"><?php echo $total_sales['count'] ?: 0; ?> mauzo</div>
            </div>

            <div class="stat-card info">
                <div class="icon">📋</div>
                <h3>Jumla ya Agizo Mtandaoni</h3>
                <div class="number">TZS <?php echo number_format($total_orders['total'] ?: 0, 0); ?></div>
                <div class="sub-text"><?php echo $total_orders['count'] ?: 0; ?> agizo</div>
            </div>

            <div class="stat-card warning">
                <div class="icon">📈</div>
                <h3>Profit</h3>
                <div class="number">TZS <?php echo number_format($profit, 0); ?></div>
                <div class="sub-text">Kutoka mauzo</div>
            </div>

            <div class="stat-card">
                <div class="icon">💵</div>
                <h3>Jumla ya Mapato</h3>
                <div class="number">TZS <?php echo number_format(($total_sales['total'] ?: 0) + ($total_orders['total'] ?: 0), 0); ?></div>
                <div class="sub-text">POS + Online</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="container" style="margin-bottom: 30px;">
            <div class="header">
                <h2>📈 Mauzo ya Kila Siku</h2>
            </div>
            <div class="chart-container">
                <canvas id="dailySalesChart"></canvas>
            </div>
        </div>

        <div class="container" style="margin-bottom: 30px;">
            <div class="header">
                <h2>📊 Mauzo kwa Kategori</h2>
            </div>
            <div class="chart-container">
                <canvas id="categorySalesChart"></canvas>
            </div>
        </div>

        <!-- Top Selling Medicines -->
        <div class="container">
            <div class="header">
                <h2>🏆 Dawa Zinazouza Zaidi</h2>
            </div>

            <?php if(mysqli_num_rows($top_medicines_result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Jina la Dawa</th>
                        <th>Idadi Iliyouzwa</th>
                        <th>Mapato</th>
                        <th>% ya Jumla</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    $total_revenue = $total_sales['total'] ?: 1;
                    while($medicine = mysqli_fetch_assoc($top_medicines_result)): 
                    ?>
                    <tr>
                        <td>
                            <?php if($rank <= 3): ?>
                                <span style="font-size: 20px;">🥇🥈🥉</span>[$rank-1]
                            <?php else: ?>
                                <?php echo $rank; ?>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo $medicine['medicine_name']; ?></strong></td>
                        <td><?php echo $medicine['total_sold']; ?></td>
                        <td>TZS <?php echo number_format($medicine['revenue'], 2); ?></td>
                        <td><?php echo round(($medicine['revenue'] / $total_revenue) * 100, 1); ?>%</td>
                    </tr>
                    <?php 
                    $rank++;
                    endwhile; 
                    ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="alert alert-info">Hakuna data ya mauzo kwa kipindi hiki</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Daily Sales Chart
const dailySalesCtx = document.getElementById('dailySalesChart').getContext('2d');
const dailySalesData = <?php
    $data = [];
    $labels = [];
    while($row = mysqli_fetch_assoc($daily_sales_result)){
        $labels[] = $row['date'];
        $data[] = $row['total'];
    }
    echo json_encode(['labels' => $labels, 'data' => $data]);
?>;

new Chart(dailySalesCtx, {
    type: 'line',
    data: {
        labels: dailySalesData.labels,
        datasets: [{
            label: 'Mauzo (TZS)',
            data: dailySalesData.data,
            borderColor: '#3498db',
            backgroundColor: 'rgba(52, 152, 219, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Category Sales Chart
const categorySalesCtx = document.getElementById('categorySalesChart').getContext('2d');
const categorySalesData = <?php
    $data = [];
    $labels = [];
    while($row = mysqli_fetch_assoc($category_sales_result)){
        $labels[] = $row['category'];
        $data[] = $row['total'];
    }
    echo json_encode(['labels' => $labels, 'data' => $data]);
?>;

new Chart(categorySalesCtx, {
    type: 'doughnut',
    data: {
        labels: categorySalesData.labels,
        datasets: [{
            data: categorySalesData.data,
            backgroundColor: [
                '#3498db',
                '#e74c3c',
                '#2ecc71',
                '#f39c12',
                '#9b59b6',
                '#1abc9c',
                '#34495e',
                '#e67e22'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'right'
            }
        }
    }
});
</script>

</body>
</html>
