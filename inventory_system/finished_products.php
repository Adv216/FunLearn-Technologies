<?php
include 'secure_page_template.php';
include 'db_connect.php';
check_permission([ROLE_ADMIN, ROLE_MANAGER]);

/* ---------- Add Category ---------- */
if(isset($_POST['add_category'])){
    $name = trim($_POST['category_name']);
    if($name != ''){
        $conn->query("INSERT INTO PRODUCT_CATEGORIES(Category_Name) VALUES ('$name')");
        header("Location: finished_products.php");
        exit;
    }
}

/* ---------- Delete Category (Safe) ---------- */
if(isset($_GET['del_cat'])){
    $cid = (int)$_GET['del_cat'];
    $check = $conn->query("SELECT COUNT(*) AS total FROM FINISHED_PRODUCTS WHERE Category_ID=$cid")->fetch_assoc();
    if($check['total'] > 0){
        header("Location: finished_products.php?cat_error=1");
        exit;
    }
    $conn->query("DELETE FROM PRODUCT_CATEGORIES WHERE Category_ID=$cid");
    header("Location: finished_products.php?cat_success=1");
    exit;
}

/* ---------- Add Product ---------- */
if(isset($_POST['add_product'])){
    $code = $_POST['code'];
    $name = $_POST['name'];
    $cat  = $_POST['category'];
    $qty  = $_POST['qty'];
    $price = $_POST['price'];

    $conn->query("INSERT INTO FINISHED_PRODUCTS(Product_Code,Product_Name,Category_ID,Quantity,Price)
                  VALUES('$code','$name',$cat,$qty,$price)");
}

/* ---------- Delete Product (Safe) ---------- */
if(isset($_GET['del_product'])){
    $pid = (int)$_GET['del_product'];

    $i1 = $conn->query("SELECT COUNT(*) t FROM INVOICE_ITEMS WHERE Product_ID=$pid")->fetch_assoc();
    $i2 = $conn->query("SELECT COUNT(*) t FROM DAILY_PRODUCTION WHERE Product_ID=$pid")->fetch_assoc();

    if($i1['t'] > 0 || $i2['t'] > 0){
        header("Location: finished_products.php?prod_error=1");
        exit;
    }

    $conn->query("DELETE FROM FINISHED_PRODUCTS WHERE Product_ID=$pid");
    header("Location: finished_products.php?prod_success=1");
    exit;
}

/* ---------- Search ---------- */
$search = $_GET['search'] ?? '';
$where = $search ? "WHERE Product_Name LIKE '%$search%' OR Product_Code LIKE '%$search%'" : "";

$products = $conn->query("
SELECT p.*, c.Category_Name 
FROM FINISHED_PRODUCTS p
LEFT JOIN PRODUCT_CATEGORIES c ON p.Category_ID=c.Category_ID
$where
ORDER BY p.Product_ID DESC
");

$cats = $conn->query("SELECT * FROM PRODUCT_CATEGORIES ORDER BY Category_Name");

// Calculate statistics
$stats = $conn->query("
    SELECT 
        COUNT(*) as total_products,
        SUM(Quantity) as total_quantity,
        SUM(Quantity * Price) as inventory_value,
        SUM(CASE WHEN Quantity = 0 THEN 1 ELSE 0 END) as out_of_stock
    FROM FINISHED_PRODUCTS
")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>Finished Products Inventory</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
:root {
    --primary: #4f46e5;
    --primary-dark: #4338ca;
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    --info: #3b82f6;
    --light: #f8fafc;
    --dark: #1e293b;
}

body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    font-family: 'Segoe UI', system-ui, sans-serif;
}

.glass-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.header-section {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: 16px;
    padding: 2rem;
    color: white;
    margin-bottom: 2rem;
    box-shadow: 0 8px 24px rgba(79, 70, 229, 0.3);
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    transition: transform 0.2s, box-shadow 0.2s;
    border-left: 4px solid var(--primary);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.category-item {
    background: #f8fafc;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    margin-bottom: 0.5rem;
    transition: all 0.2s;
    border: 1px solid #e2e8f0;
}

.category-item:hover {
    background: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transform: translateX(4px);
}

.btn-custom {
    border-radius: 8px;
    padding: 0.625rem 1.25rem;
    font-weight: 500;
    transition: all 0.2s;
    border: none;
}

.btn-primary-custom {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
}

.btn-primary-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
}

.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    padding: 0.625rem 1rem;
    transition: all 0.2s;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.search-wrapper {
    position: relative;
}

.search-wrapper input {
    padding-left: 2.5rem;
}

.search-wrapper i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}

.table-container {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.table {
    margin-bottom: 0;
}

.table thead th {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: none;
    padding: 1rem;
    font-weight: 600;
    color: var(--dark);
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.table tbody tr {
    transition: all 0.2s;
    border-bottom: 1px solid #f1f5f9;
}

.table tbody tr:hover {
    background: #f8fafc;
    transform: scale(1.01);
}

.badge-custom {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 500;
    font-size: 0.75rem;
}

.alert-custom {
    border-radius: 12px;
    border: none;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.section-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
}

.section-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.delete-btn {
    color: var(--danger);
    background: transparent;
    border: none;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    transition: all 0.2s;
    cursor: pointer;
}

.delete-btn:hover {
    background: rgba(239, 68, 68, 0.1);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-in {
    animation: fadeIn 0.5s ease-out;
}
</style>
</head>
<body>

<div class="container py-4">

<!-- Header Section -->
<div class="header-section animate-in">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-box-seam"></i> Finished Products Inventory</h2>
            <p class="mb-0 opacity-75">Manage your product catalog and stock levels</p>
        </div>
        <a href="index.php" class="btn btn-light btn-custom">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-3 mt-3">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Total Products</div>
                        <h3 class="mb-0"><?= $stats['total_products'] ?></h3>
                    </div>
                    <div class="stat-icon" style="background: rgba(79, 70, 229, 0.1); color: var(--primary);">
                        <i class="bi bi-box"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: var(--success);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Total Quantity</div>
                        <h3 class="mb-0"><?= number_format($stats['total_quantity']) ?></h3>
                    </div>
                    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                        <i class="bi bi-stack"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: var(--info);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Inventory Value</div>
                        <h3 class="mb-0">₹<?= number_format($stats['inventory_value'], 2) ?></h3>
                    </div>
                    <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: var(--info);">
                        <i class="bi bi-currency-rupee"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: var(--danger);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Out of Stock</div>
                        <h3 class="mb-0"><?= $stats['out_of_stock'] ?></h3>
                    </div>
                    <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: var(--danger);">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alerts -->
<?php if(isset($_GET['cat_error'])): ?>
<div class="alert alert-danger alert-custom animate-in">
    <i class="bi bi-exclamation-circle-fill"></i>
    <span>Cannot delete category — products exist in this category.</span>
</div>
<?php endif; ?>

<?php if(isset($_GET['prod_error'])): ?>
<div class="alert alert-danger alert-custom animate-in">
    <i class="bi bi-exclamation-circle-fill"></i>
    <span>Cannot delete product — it's being used in invoices or production records.</span>
</div>
<?php endif; ?>

<?php if(isset($_GET['cat_success'])): ?>
<div class="alert alert-success alert-custom animate-in">
    <i class="bi bi-check-circle-fill"></i>
    <span>Category deleted successfully!</span>
</div>
<?php endif; ?>

<?php if(isset($_GET['prod_success'])): ?>
<div class="alert alert-success alert-custom animate-in">
    <i class="bi bi-check-circle-fill"></i>
    <span>Product deleted successfully!</span>
</div>
<?php endif; ?>

<div class="row g-4">

<!-- Left Sidebar - Categories -->
<div class="col-lg-3">
    <div class="glass-card p-4 animate-in">
        <div class="section-title">
            <i class="bi bi-folder"></i>
            Add Category
        </div>
        <form method="post">
            <div class="mb-3">
                <input class="form-control" name="category_name" placeholder="Enter category name" required>
            </div>
            <button name="add_category" class="btn btn-primary-custom btn-custom w-100">
                <i class="bi bi-plus-circle"></i> Add Category
            </button>
        </form>
    </div>

    <div class="glass-card p-4 mt-3 animate-in" style="animation-delay: 0.1s;">
        <div class="section-title">
            <i class="bi bi-list-ul"></i>
            Categories
        </div>
        <div style="max-height: 400px; overflow-y: auto;">
            <?php while($c=$cats->fetch_assoc()): ?>
            <div class="category-item">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-tag-fill me-2 text-primary"></i><?= $c['Category_Name'] ?></span>
                    <button onclick="return confirm('Delete this category?')" class="delete-btn" 
                            onclick="window.location.href='?del_cat=<?= $c['Category_ID'] ?>'">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- Main Content - Products -->
<div class="col-lg-9">
    
    <!-- Add Product Form -->
    <div class="glass-card p-4 animate-in" style="animation-delay: 0.2s;">
        <div class="section-title">
            <i class="bi bi-plus-square"></i>
            Add New Product
        </div>
        <form method="post">
            <div class="row g-3">
                <div class="col-md-6 col-lg-3">
                    <label class="form-label small text-muted">Item Code</label>
                    <input class="form-control" name="code" placeholder="e.g., PRD001" required>
                </div>
                <div class="col-md-6 col-lg-3">
                    <label class="form-label small text-muted">Product Name</label>
                    <input class="form-control" name="name" placeholder="Product name" required>
                </div>
                <div class="col-md-6 col-lg-2">
                    <label class="form-label small text-muted">Category</label>
                    <select class="form-select" name="category" required>
                        <option value="">Select</option>
                        <?php 
                        $catlist=$conn->query("SELECT * FROM PRODUCT_CATEGORIES");
                        while($c=$catlist->fetch_assoc()):
                        ?>
                        <option value="<?= $c['Category_ID'] ?>"><?= $c['Category_Name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6 col-lg-2">
                    <label class="form-label small text-muted">Quantity</label>
                    <input class="form-control" name="qty" type="number" placeholder="0" required>
                </div>
                <div class="col-md-6 col-lg-2">
                    <label class="form-label small text-muted">Price (₹)</label>
                    <input class="form-control" name="price" type="number" step="0.01" placeholder="0.00" required>
                </div>
                <div class="col-12">
                    <button name="add_product" class="btn btn-success btn-custom">
                        <i class="bi bi-check-circle"></i> Add Product
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Search Bar -->
    <div class="glass-card p-3 mt-3 animate-in" style="animation-delay: 0.3s;">
        <form class="search-wrapper">
            <i class="bi bi-search"></i>
            <input class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" 
                   placeholder="Search by product name or item code...">
        </form>
    </div>

    <!-- Products Table -->
    <div class="glass-card p-4 mt-3 animate-in" style="animation-delay: 0.4s;">
        <div class="section-title mb-3">
            <i class="bi bi-table"></i>
            Product Inventory
        </div>
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ITEM CODE</th>
                            <th>PRODUCT NAME</th>
                            <th>CATEGORY</th>
                            <th>QUANTITY</th>
                            <th>STATUS</th>
                            <th>PRICE</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($p=$products->fetch_assoc()): ?>
                        <tr>
                            <td><span class="badge bg-light text-dark"><?= $p['Product_Code'] ?></span></td>
                            <td><strong><?= $p['Product_Name'] ?></strong></td>
                            <td><span class="badge" style="background: rgba(79, 70, 229, 0.1); color: var(--primary);"><?= $p['Category_Name'] ?></span></td>
                            <td><strong><?= $p['Quantity'] ?></strong></td>
                            <td>
                                <?php if($p['Quantity'] > 10): ?>
                                    <span class="badge badge-custom" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                                        <i class="bi bi-check-circle-fill"></i> In Stock
                                    </span>
                                <?php elseif($p['Quantity'] > 0): ?>
                                    <span class="badge badge-custom" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
                                        <i class="bi bi-exclamation-circle-fill"></i> Low Stock
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-custom" style="background: rgba(239, 68, 68, 0.1); color: var(--danger);">
                                        <i class="bi bi-x-circle-fill"></i> Out of Stock
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><strong>₹<?= number_format($p['Price'], 2) ?></strong></td>
                            <td>
                                <a href="?del_product=<?= $p['Product_ID'] ?>" 
                                   class="btn btn-sm btn-danger btn-custom" 
                                   onclick="return confirm('Are you sure you want to delete this product?')">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>