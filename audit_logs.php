<?php
session_start();
include("config.php");

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

// Get filters
$action_filter = isset($_GET['action']) ? $_GET['action'] : '';
$table_filter = isset($_GET['table']) ? $_GET['table'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

// Build query
$where_conditions = [];
$params = [];
$types = "";

if(!empty($action_filter)){
    $where_conditions[] = "action = ?";
    $params[] = $action_filter;
    $types .= "s";
}

if(!empty($table_filter)){
    $where_conditions[] = "table_name = ?";
    $params[] = $table_filter;
    $types .= "s";
}

if(!empty($date_filter)){
    $where_conditions[] = "DATE(created_at) = ?";
    $params[] = $date_filter;
    $types .= "s";
}

$sql = "SELECT a.*, u.username, u.fullname FROM audit_logs a 
        LEFT JOIN users u ON a.user_id = u.user_id";

if(!empty($where_conditions)){
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}

$sql .= " ORDER BY a.created_at DESC LIMIT 100";

if(!empty($params)){
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $sql);
}

// Get unique actions and tables for filters
$actions_query = "SELECT DISTINCT action FROM audit_logs ORDER BY action ASC";
$actions_result = mysqli_query($conn, $actions_query);

$tables_query = "SELECT DISTINCT table_name FROM audit_logs ORDER BY table_name ASC";
$tables_result = mysqli_query($conn, $tables_query);

// Get statistics
$total_logs = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM audit_logs"));
$today_logs = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM audit_logs WHERE DATE(created_at) = CURDATE()"));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Audit Logs - Pharmacy</title>
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
                <li><a href="sales_analytics.php"><i class="fas fa-chart-bar"></i> <span>Analytics</span></a></li>
                <li><a href="audit_logs.php" class="active"><i class="fas fa-history"></i> <span>Audit Logs</span></a></li>
                <li><a href="admin_users.php"><i class="fas fa-users"></i> <span>Famasia</span></a></li>
                <li><a href="admin_customers.php"><i class="fas fa-user-friends"></i> <span>Wateja</span></a></li>
                <li><a href="admin_orders.php"><i class="fas fa-shopping-bag"></i> <span>Agizo</span></a></li>
                <li><a href="sales.php"><i class="fas fa-cash-register"></i> <span>Mauzo</span></a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-header">
            <h2>📋 Audit Logs</h2>
            <div class="user-info">
                <span>Admin: <?php echo $_SESSION['fullname']; ?></span>
            </div>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="icon">📊</div>
                <h3>Jumla ya Logs</h3>
                <div class="number"><?php echo $total_logs; ?></div>
            </div>

            <div class="stat-card success">
                <div class="icon">📅</div>
                <h3>Logs za Leo</h3>
                <div class="number"><?php echo $today_logs; ?></div>
            </div>
        </div>

        <div class="container">
            <div class="header">
                <h2>🔍 Chuja Audit Logs</h2>
            </div>

            <form method="GET" class="filter-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Action</label>
                        <select name="action">
                            <option value="">-- Zote --</option>
                            <?php while($action = mysqli_fetch_assoc($actions_result)): ?>
                            <option value="<?php echo $action['action']; ?>" <?php echo ($action_filter == $action['action']) ? 'selected' : ''; ?>>
                                <?php echo ucfirst($action['action']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Table</label>
                        <select name="table">
                            <option value="">-- Zote --</option>
                            <?php while($table = mysqli_fetch_assoc($tables_result)): ?>
                            <option value="<?php echo $table['table_name']; ?>" <?php echo ($table_filter == $table['table_name']) ? 'selected' : ''; ?>>
                                <?php echo ucfirst($table['table_name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tarehe</label>
                        <input type="date" name="date" value="<?php echo $date_filter; ?>">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">🔍 Chuja</button>
                    <a href="audit_logs.php" class="btn btn-warning">↻ Reset</a>
                </div>
            </form>
        </div>

        <div class="container" style="margin-top: 30px;">
            <div class="header">
                <h2>📋 Audit Logs (Mwisho 100)</h2>
            </div>

            <?php if(mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utumiaji</th>
                        <th>Action</th>
                        <th>Table</th>
                        <th>Record ID</th>
                        <th>IP Address</th>
                        <th>Tarehe</th>
                        <th>Matendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($log = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $log['log_id']; ?></td>
                        <td>
                            <?php if($log['username']): ?>
                                <strong><?php echo $log['username']; ?></strong><br>
                                <small><?php echo $log['fullname']; ?></small>
                            <?php else: ?>
                                <em>System</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="alert <?php 
                                echo match($log['action']) {
                                    'create' => 'alert-success',
                                    'update' => 'alert-info',
                                    'delete' => 'alert-danger',
                                    'login' => 'alert-warning',
                                    'logout' => 'alert-secondary',
                                    default => 'alert-info'
                                };
                            ?>" style="padding: 5px 10px; font-size: 12px;">
                                <?php echo ucfirst($log['action']); ?>
                            </span>
                        </td>
                        <td><?php echo $log['table_name'] ? ucfirst($log['table_name']) : '-'; ?></td>
                        <td><?php echo $log['record_id'] ?: '-'; ?></td>
                        <td><?php echo $log['ip_address'] ?: '-'; ?></td>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                        <td>
                            <button onclick="showLogDetails(<?php echo $log['log_id']; ?>)" class="btn btn-info" style="padding: 5px 10px; font-size: 12px;">👁️ View</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="alert alert-info">Hakuna audit logs zilizopatikana</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal for log details -->
<div id="logModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: relative; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 5px; max-width: 600px; max-height: 80vh; overflow-y: auto;">
        <h3>Log Details</h3>
        <div id="logDetails"></div>
        <button onclick="closeModal()" class="btn btn-warning" style="margin-top: 20px;">Funga</button>
    </div>
</div>

<script>
function showLogDetails(logId) {
    // This would typically make an AJAX call to get details
    // For now, we'll show a simple message
    document.getElementById('logDetails').innerHTML = '<p>Log ID: ' + logId + '</p><p>Detailed view would show old_values and new_values</p>';
    document.getElementById('logModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('logModal').style.display = 'none';
}
</script>

</body>
</html>
