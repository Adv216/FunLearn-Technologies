<?php
include 'secure_page_template.php';
include 'db_connect.php';

check_permission([ROLE_ADMIN, ROLE_MANAGER]);

$message = "";
$message_type = "";

/* =========================
   HANDLE PRODUCTION ENTRY
   ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_id = intval($_POST['product_id']);
    $quantity   = intval($_POST['quantity']);
    $date       = $_POST['date'];

    $conn->begin_transaction();

    try {
        // Log production
        $stmt = $conn->prepare("INSERT INTO DAILY_PRODUCTION (Product_ID, Quantity, Production_Date) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $product_id, $quantity, $date);
        $stmt->execute();

        // Update finished product stock
        $stmt = $conn->prepare("UPDATE FINISHED_PRODUCTS SET Quantity = Quantity + ? WHERE Product_ID = ?");
        $stmt->bind_param("ii", $quantity, $product_id);
        $stmt->execute();

        // Deduct raw materials
        $materials = $conn->query("SELECT * FROM RAW_MATERIALS");

        while ($m = $materials->fetch_assoc()) {
            $use = ceil($quantity * 0.5); // simple usage rule (customize later)

            if ($m['Quantity'] < $use) {
                throw new Exception("Not enough raw material: " . $m['Material_Name']);
            }

            $stmt = $conn->prepare("UPDATE RAW_MATERIALS SET Quantity = Quantity - ? WHERE Material_ID = ?");
            $stmt->bind_param("ii", $use, $m['Material_ID']);
            $stmt->execute();
        }

        $conn->commit();
        $message = "Production recorded successfully! Stock updated and materials deducted.";
        $message_type = "success";

    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

/* =========================
   FETCH DATA
   ========================= */
$products = $conn->query("SELECT Product_ID, Product_Name FROM FINISHED_PRODUCTS ORDER BY Product_Name");

$history = $conn->query("
    SELECT d.*, p.Product_Name 
    FROM DAILY_PRODUCTION d
    JOIN FINISHED_PRODUCTS p ON d.Product_ID = p.Product_ID
    ORDER BY d.Production_Date DESC
    LIMIT 50
");

// Get today's production stats
$today_stats = $conn->query("
    SELECT 
        COUNT(*) as entries,
        SUM(Quantity) as total_quantity
    FROM DAILY_PRODUCTION
    WHERE Production_Date = CURDATE()
")->fetch_assoc();

// Get this week's stats
$week_stats = $conn->query("
    SELECT 
        COUNT(DISTINCT Product_ID) as unique_products,
        SUM(Quantity) as total_quantity
    FROM DAILY_PRODUCTION
    WHERE YEARWEEK(Production_Date, 1) = YEARWEEK(CURDATE(), 1)
")->fetch_assoc();

// Get this month's stats
$month_stats = $conn->query("
    SELECT 
        SUM(Quantity) as total_quantity
    FROM DAILY_PRODUCTION
    WHERE MONTH(Production_Date) = MONTH(CURDATE())
    AND YEAR(Production_Date) = YEAR(CURDATE())
")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>Daily Production | Manufacturing System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    padding: 20px 0;
}

.page-header {
    text-align: center;
    margin-bottom: 40px;
    color: white;
}

.page-header h1 {
    font-size: 42px;
    font-weight: 700;
    margin-bottom: 8px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.page-header p {
    color: rgba(255,255,255,0.9);
    font-size: 16px;
}

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
}

.stat-card.today::before {
    background: var(--success-gradient);
}

.stat-card.week::before {
    background: var(--info-gradient);
}

.stat-card.month::before {
    background: var(--warning-gradient);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.18);
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 16px;
    color: white;
}

.stat-card.today .stat-icon {
    background: var(--success-gradient);
}

.stat-card.week .stat-icon {
    background: var(--info-gradient);
}

.stat-card.month .stat-icon {
    background: var(--warning-gradient);
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 4px;
}

.stat-label {
    color: #718096;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-sublabel {
    color: #a0aec0;
    font-size: 12px;
    margin-top: 4px;
}

/* Main Cards */
.main-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    overflow: hidden;
    margin-bottom: 24px;
}

.card-header-custom {
    background: var(--primary-gradient);
    color: white;
    padding: 24px 28px;
    display: flex;
    align-items: center;
    gap: 12px;
    border: none;
}

.card-header-custom h4 {
    margin: 0;
    font-weight: 600;
    font-size: 20px;
}

.card-header-custom i {
    font-size: 24px;
}

.card-body-custom {
    padding: 28px;
}

/* Alert Styles */
.alert-custom {
    border-radius: 12px;
    border: none;
    padding: 16px 20px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.alert-custom i {
    font-size: 20px;
}

.alert-success {
    background: linear-gradient(135deg, rgba(17, 153, 142, 0.15) 0%, rgba(56, 239, 125, 0.15) 100%);
    border-left: 4px solid #38ef7d;
    color: #047857;
}

.alert-danger {
    background: linear-gradient(135deg, rgba(238, 9, 121, 0.15) 0%, rgba(255, 106, 0, 0.15) 100%);
    border-left: 4px solid #ff6a00;
    color: #c53030;
}

/* Form Styles */
.form-section {
    background: #f8f9fa;
    border-radius: 16px;
    padding: 28px;
}

.form-label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
    font-size: 14px;
}

.form-control, .form-select {
    border-radius: 10px;
    border: 2px solid #e2e8f0;
    padding: 12px 16px;
    transition: all 0.3s ease;
    font-size: 15px;
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn {
    border-radius: 10px;
    padding: 12px 28px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
}

.btn-primary {
    background: var(--primary-gradient);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
}

.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(74, 85, 104, 0.4);
}

/* Table Styles */
.table-wrapper {
    overflow-x: auto;
    border-radius: 12px;
}

.table {
    margin: 0;
}

.table thead th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 16px;
    font-weight: 600;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.table thead th:first-child {
    border-radius: 10px 0 0 0;
}

.table thead th:last-child {
    border-radius: 0 10px 0 0;
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background: #f7fafc;
    transform: scale(1.005);
}

.table tbody td {
    padding: 18px 16px;
    vertical-align: middle;
    border-color: #e2e8f0;
    font-size: 15px;
}

.product-name-cell {
    font-weight: 600;
    color: #2d3748;
}

.product-name-cell i {
    color: #667eea;
    margin-right: 8px;
}

.quantity-badge {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
    padding: 6px 14px;
    border-radius: 8px;
    font-weight: 600;
    display: inline-block;
    font-size: 14px;
}

.date-cell {
    color: #4a5568;
    font-weight: 500;
}

.date-cell i {
    color: #a0aec0;
    margin-right: 6px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #718096;
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.3;
}

/* Production Form Special Styles */
.production-form-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    margin-bottom: 16px;
}

.form-helper-text {
    font-size: 12px;
    color: #718096;
    margin-top: 4px;
}

@media (max-width: 768px) {
    .page-header h1 {
        font-size: 32px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .card-body-custom {
        padding: 20px;
    }
    
    .form-section {
        padding: 20px;
    }
}
</style>
</head>
<body>

<div class="container py-4">

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-industry"></i> Daily Production</h1>
    <p>Track and manage your daily manufacturing output</p>
</div>

<!-- Statistics Dashboard -->
<div class="stats-grid">
    <div class="stat-card today">
        <div class="stat-icon">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="stat-value"><?= number_format($today_stats['total_quantity'] ?? 0) ?></div>
        <div class="stat-label">Today's Production</div>
        <div class="stat-sublabel"><?= $today_stats['entries'] ?? 0 ?> entries recorded</div>
    </div>
    
    <div class="stat-card week">
        <div class="stat-icon">
            <i class="fas fa-calendar-week"></i>
        </div>
        <div class="stat-value"><?= number_format($week_stats['total_quantity'] ?? 0) ?></div>
        <div class="stat-label">This Week</div>
        <div class="stat-sublabel"><?= $week_stats['unique_products'] ?? 0 ?> different products</div>
    </div>
    
    <div class="stat-card month">
        <div class="stat-icon">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="stat-value"><?= number_format($month_stats['total_quantity'] ?? 0) ?></div>
        <div class="stat-label">This Month</div>
        <div class="stat-sublabel">Total units produced</div>
    </div>
</div>

<!-- Alert Message -->
<?php if ($message): ?>
<div class="alert-custom alert-<?= $message_type ?>">
    <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
    <span><?= htmlspecialchars($message) ?></span>
</div>
<?php endif; ?>

<!-- Production Entry Form -->
<div class="main-card">
    <div class="card-header-custom">
        <i class="fas fa-plus-circle"></i>
        <h4>Record New Production</h4>
    </div>
    <div class="card-body-custom">
        <div class="form-section">
            <div class="production-form-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            
            <form method="POST" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">
                        <i class="fas fa-box me-1"></i> Product
                    </label>
                    <select name="product_id" class="form-select" required>
                        <option value="">Choose product to manufacture</option>
                        <?php 
                        $products->data_seek(0);
                        while ($p = $products->fetch_assoc()): 
                        ?>
                        <option value="<?= $p['Product_ID'] ?>"><?= htmlspecialchars($p['Product_Name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                    <div class="form-helper-text">Select the finished product being manufactured</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        <i class="fas fa-cubes me-1"></i> Quantity Produced
                    </label>
                    <input type="number" name="quantity" class="form-control" placeholder="0" required min="1">
                    <div class="form-helper-text">Number of units manufactured</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        <i class="fas fa-calendar me-1"></i> Production Date
                    </label>
                    <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    <div class="form-helper-text">Date when production occurred</div>
                </div>

                <div class="col-md-2">
                    <label class="form-label d-block">&nbsp;</label>
                    <button class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i>Save Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Production History -->
<div class="main-card">
    <div class="card-header-custom">
        <i class="fas fa-history"></i>
        <h4>Production History</h4>
    </div>
    <div class="card-body-custom">
        <?php if ($history->num_rows > 0): ?>
        <div class="table-wrapper">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><i class="fas fa-calendar me-2"></i>Date</th>
                        <th><i class="fas fa-box me-2"></i>Product</th>
                        <th><i class="fas fa-layer-group me-2"></i>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $history->fetch_assoc()): ?>
                    <tr>
                        <td class="date-cell">
                            <i class="far fa-calendar"></i>
                            <?= date('M d, Y', strtotime($row['Production_Date'])) ?>
                        </td>
                        <td class="product-name-cell">
                            <i class="fas fa-box-open"></i>
                            <?= htmlspecialchars($row['Product_Name']) ?>
                        </td>
                        <td>
                            <span class="quantity-badge">
                                <?= number_format($row['Quantity']) ?> units
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-clipboard-list"></i>
            <h5 class="mt-3">No Production Records Yet</h5>
            <p>Start recording your daily production using the form above.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Back Button -->
<div class="text-center mt-4">
    <a href="index.php" class="btn btn-secondary btn-lg px-5">
        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>