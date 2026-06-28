<?php
session_start();
include("config.php");

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

// Advanced search with filters
$where_conditions = [];
$params = [];
$types = "";

if(isset($_GET['search']) && !empty($_GET['search'])){
    $search = $_GET['search'];
    $where_conditions[] = "(medicine_name LIKE ? OR category LIKE ? OR description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if(isset($_GET['category']) && !empty($_GET['category'])){
    $category = $_GET['category'];
    $where_conditions[] = "category = ?";
    $params[] = $category;
    $types .= "s";
}

if(isset($_GET['min_price']) && !empty($_GET['min_price'])){
    $min_price = $_GET['min_price'];
    $where_conditions[] = "selling_price >= ?";
    $params[] = $min_price;
    $types .= "d";
}

if(isset($_GET['max_price']) && !empty($_GET['max_price'])){
    $max_price = $_GET['max_price'];
    $where_conditions[] = "selling_price <= ?";
    $params[] = $max_price;
    $types .= "d";
}

if(isset($_GET['stock_status']) && !empty($_GET['stock_status'])){
    $stock_status = $_GET['stock_status'];
    if($stock_status == 'low'){
        $where_conditions[] = "quantity < 10";
    } elseif($stock_status == 'out'){
        $where_conditions[] = "quantity = 0";
    } elseif($stock_status == 'available'){
        $where_conditions[] = "quantity > 0";
    }
}

if(isset($_GET['expiry_status']) && !empty($_GET['expiry_status'])){
    $expiry_status = $_GET['expiry_status'];
    if($expiry_status == 'expiring'){
        $where_conditions[] = "expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
    } elseif($expiry_status == 'expired'){
        $where_conditions[] = "expiry_date < CURDATE()";
    }
}

$sql = "SELECT * FROM medicines";
if(!empty($where_conditions)){
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}
$sql .= " ORDER BY medicine_name ASC";

if(!empty($params)){
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $sql);
}

// Get unique categories for filter dropdown
$categories_query = "SELECT DISTINCT category FROM medicines ORDER BY category ASC";
$categories_result = mysqli_query($conn, $categories_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Medicine List - Pharmacy Management</title>
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
                <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                <li><a href="medicine.php" class="active"><i class="fas fa-pills"></i> <span>Dawa</span></a></li>
                <li><a href="inventory_management.php"><i class="fas fa-boxes"></i> <span>Inventory</span></a></li>
                <li><a href="suppliers.php"><i class="fas fa-truck"></i> <span>Suppliers</span></a></li>
                <li><a href="sales_analytics.php"><i class="fas fa-chart-bar"></i> <span>Analytics</span></a></li>
                <li><a href="audit_logs.php"><i class="fas fa-history"></i> <span>Audit Logs</span></a></li>
                <li><a href="database_backup.php"><i class="fas fa-database"></i> <span>Backup</span></a></li>
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
            <h2>📦 Orodha ya Dawa</h2>
            <div class="user-info">
                <span>Karibu, <?php echo isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Admin'; ?></span>
            </div>
        </div>

        <div class="container">
            <div class="header">
                <h2>🔍 Tafuta na Chuja Dawa</h2>
            </div>

            <form method="GET" class="advanced-search-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Tafuta (Jina, Kategori, Maelezo)</label>
                        <input type="text" name="search" placeholder="Ingiza neno la kutafuta..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="category">
                            <option value="">-- Zote --</option>
                            <?php while($cat = mysqli_fetch_assoc($categories_result)): ?>
                            <option value="<?php echo $cat['category']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['category']) ? 'selected' : ''; ?>>
                                <?php echo $cat['category']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Bei ya Chini (TZS)</label>
                        <input type="number" name="min_price" placeholder="0" value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Bei ya Juu (TZS)</label>
                        <input type="number" name="max_price" placeholder="999999" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Hali ya Stock</label>
                        <select name="stock_status">
                            <option value="">-- Zote --</option>
                            <option value="available" <?php echo (isset($_GET['stock_status']) && $_GET['stock_status'] == 'available') ? 'selected' : ''; ?>>Inapatikana</option>
                            <option value="low" <?php echo (isset($_GET['stock_status']) && $_GET['stock_status'] == 'low') ? 'selected' : ''; ?>>Inayokaribiaisha</option>
                            <option value="out" <?php echo (isset($_GET['stock_status']) && $_GET['stock_status'] == 'out') ? 'selected' : ''; ?>>Imeisha</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Hali ya Muda</label>
                        <select name="expiry_status">
                            <option value="">-- Zote --</option>
                            <option value="expiring" <?php echo (isset($_GET['expiry_status']) && $_GET['expiry_status'] == 'expiring') ? 'selected' : ''; ?>>Inayokaribia Kuisha</option>
                            <option value="expired" <?php echo (isset($_GET['expiry_status']) && $_GET['expiry_status'] == 'expired') ? 'selected' : ''; ?>>Imeisha Muda</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">🔍 Tafuta</button>
                    <a href="medicine.php" class="btn btn-warning">↻ Reset</a>
                    <a href="add_medicine.php" class="btn btn-success">➕ Ongeza Dawa Mpya</a>
                </div>
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
                        <th>Matendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while($row=mysqli_fetch_assoc($result)){
                    ?>
                    <tr>
                        <td><?php echo $row['medicine_id']; ?></td>
                        <td><?php echo $row['medicine_name']; ?></td>
                        <td><?php echo $row['category']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td>TZS <?php echo number_format($row['selling_price'], 2); ?></td>
                        <td><?php echo $row['expiry_date']; ?></td>
                        <td>
                            <div class="actions">
                                <a href="edit_medicine.php?id=<?php echo $row['medicine_id']; ?>" class="edit-btn">✏️ Edit</a>
                                <a href="delete_medicine.php?id=<?php echo $row['medicine_id']; ?>" class="delete-btn">🗑️ Delete</a>
                            </div>
                        </td>
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