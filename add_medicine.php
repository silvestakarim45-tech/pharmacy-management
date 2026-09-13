<?php
session_start();
include("config.php");
include("functions.php");

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

if(isset($_POST['save'])){

$name=$_POST['medicine_name'];
$category=$_POST['category'];
$quantity=$_POST['quantity'];
$buying=$_POST['buying_price'];
$selling=$_POST['selling_price'];
$expiry=$_POST['expiry_date'];
$supplier_id = isset($_POST['supplier_id']) && !empty($_POST['supplier_id']) ? $_POST['supplier_id'] : NULL;

$sql="INSERT INTO medicines
(medicine_name,category,quantity,buying_price,selling_price,expiry_date,supplier_id)
VALUES
(?,?,?,?,?,?,?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssiddsi", $name, $category, $quantity, $buying, $selling, $expiry, $supplier_id);

try {
    mysqli_stmt_execute($stmt);
    header("Location: medicine.php");
    exit();
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}
}

// Get all suppliers
$suppliers_query = "SELECT * FROM suppliers WHERE status='active' ORDER BY supplier_name ASC";
$suppliers_result = mysqli_query($conn, $suppliers_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Medicine - Pharmacy Management</title>
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
                <li><a href="medicine.php" class="active"><i class="fas fa-pills"></i> <span>Dawa</span></a></li>
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
            <h2>➕ Ongeza Dawa Mpya</h2>
            <div class="user-info">
                <span>Karibu, <?php echo isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Admin'; ?></span>
            </div>
        </div>

        <div class="container">
            <div class="header">
                <h2>Jaza taarifa zote za dawa mpya</h2>
            </div>

            <?php if(isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <div class="form-group">
                    <label>Jina la Dawa</label>
                    <input type="text" name="medicine_name" placeholder="Ingiza jina la dawa" required>
                </div>

                <div class="form-group">
                    <label>Kategori</label>
                    <input type="text" name="category" placeholder="Ingiza kategori ya dawa" required>
                </div>

                <div class="form-group">
                    <label>Idadi</label>
                    <input type="number" name="quantity" placeholder="Ingiza idadi ya dawa" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Bei ya Kununua</label>
                        <input type="number" step="0.01" name="buying_price" placeholder="TZS" required>
                    </div>

                    <div class="form-group">
                        <label>Bei ya Kuuza</label>
                        <input type="number" step="0.01" name="selling_price" placeholder="TZS" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Tarehe ya Kuisha Muda</label>
                    <input type="date" name="expiry_date" required>
                </div>

                <div class="form-group">
                    <label>Supplier (Chagua)</label>
                    <select name="supplier_id">
                        <option value="">-- Chagua Supplier --</option>
                        <?php while($supplier = mysqli_fetch_assoc($suppliers_result)): ?>
                        <option value="<?php echo $supplier['supplier_id']; ?>">
                            <?php echo $supplier['supplier_name']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <small style="color: #666;">Chagua supplier anayetoa dawa hii (optional)</small>
                </div>

                <button type="submit" name="save" class="btn btn-success">💾 Hifadhi Dawa</button>

                <a href="medicine.php" class="btn btn-warning">← Rudi</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>