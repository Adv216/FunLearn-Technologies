<?php
include 'secure_page_template.php';
include 'db_connect.php';

check_permission([ROLE_ADMIN, ROLE_MANAGER]);

$message = "";

/* ======================
   ADD CATEGORY
====================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = trim($_POST['category_name']);

    if ($category_name !== "") {
        $stmt = $conn->prepare("INSERT IGNORE INTO CATEGORIES (Category_Name) VALUES (?)");
        $stmt->bind_param("s", $category_name);
        $stmt->execute();
        $message = "Category added successfully!";
    }
}

/* ======================
   DELETE CATEGORY
====================== */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM CATEGORIES WHERE Category_ID = $id");
    header("Location: categories.php");
    exit;
}

/* ======================
   FETCH CATEGORIES
====================== */
$result = $conn->query("SELECT * FROM CATEGORIES ORDER BY Category_Name");

// Count total categories
$total_categories = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Categories Management</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
    :root {
        --primary: #6366f1;
        --success: #10b981;
        --danger: #ef4444;
        --dark: #1e293b;
        --gray: #64748b;
    }

    body { 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: 
            radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3), transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(99, 102, 241, 0.3), transparent 50%);
        pointer-events: none;
        z-index: 0;
    }

    .navbar {
        background: rgba(15, 23, 42, 0.95) !important;
        backdrop-filter: blur(20px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .navbar-brand {
        font-weight: 700;
        font-size: 1.5rem;
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .nav-link {
        color: rgba(255, 255, 255, 0.85) !important;
        font-weight: 500;
        padding: 0.6rem 1.2rem !important;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
    }

    .nav-link:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateY(-2px);
    }

    .container {
        position: relative;
        z-index: 1;
    }

    .page-header {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 2rem;
        padding: 2.5rem;
        margin: 2rem 0;
        box-shadow: 0 20px 60px -15px rgba(0, 0, 0, 0.3);
        display: flex;
        justify-content: space-between;
        align-items: center;
        animation: slideDown 0.6s ease;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .page-header h3 {
        font-size: 2.5rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary), #06b6d4);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-box {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(99, 102, 241, 0.1));
        border-radius: 1rem;
        padding: 1rem 1.5rem;
        display: inline-flex;
        align-items: center;
        gap: 1rem;
        margin-top: 1rem;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 0.75rem;
        background: linear-gradient(135deg, var(--primary), #4338ca);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .stat-content {
        display: flex;
        flex-direction: column;
    }

    .stat-label {
        font-size: 0.875rem;
        color: var(--gray);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 800;
        color: var(--primary);
        line-height: 1;
    }

    .alert {
        border-radius: 1rem;
        border: none;
        padding: 1.25rem;
        animation: fadeInUp 0.4s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .card {
        background: rgba(255, 255, 255, 0.98);
        border: none;
        border-radius: 1.5rem;
        box-shadow: 0 20px 50px -15px rgba(0, 0, 0, 0.2);
        animation: fadeInUp 0.6s ease backwards;
        margin-bottom: 2rem;
    }

    .card:nth-child(3) { animation-delay: 0.1s; }
    .card:nth-child(4) { animation-delay: 0.2s; }

    .card-body {
        padding: 2rem;
    }

    .card h5 {
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .form-label {
        font-weight: 600;
        color: var(--gray);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-control, .form-select {
        border: 2px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .btn {
        border-radius: 0.75rem;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary), #4338ca);
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
    }

    .btn-secondary {
        background: linear-gradient(135deg, var(--gray), #475569);
        box-shadow: 0 4px 15px rgba(100, 116, 139, 0.3);
    }

    .btn-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(100, 116, 139, 0.4);
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger), #dc2626);
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
    }

    .table thead {
        background: linear-gradient(135deg, var(--dark), #334155);
        color: white;
    }

    .table thead th {
        padding: 1rem;
        font-weight: 600;
        border: none;
    }

    .table thead th:first-child {
        border-top-left-radius: 0.75rem;
    }

    .table thead th:last-child {
        border-top-right-radius: 0.75rem;
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background: rgba(99, 102, 241, 0.05);
        transform: scale(1.01);
    }

    .table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-color: #f1f5f9;
    }

    .category-name {
        font-weight: 600;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .category-badge {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(99, 102, 241, 0.1));
        color: var(--primary);
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 1.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--gray);
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 1rem;
        }
        
        .page-header h3 {
            font-size: 1.75rem;
        }
    }
</style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-chart-line me-2"></i>Billing System
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="invoice_form.php">
                        <i class="fas fa-file-invoice me-2"></i>New Invoice
                    </a>
                </li>
                <?php if (check_permission([ROLE_ADMIN, ROLE_MANAGER])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="purchase_form.php">
                        <i class="fas fa-truck-loading me-2"></i>New Purchase
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="view_inventory.php">
                        <i class="fas fa-boxes me-2"></i>Inventory
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item me-3 d-flex align-items-center">
                    <span class="navbar-text text-white-50">
                        <i class="fas fa-user-circle me-1"></i> 
                        <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="btn btn-sm btn-danger" href="logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">

    <div class="page-header">
        <div>
            <h3>
                <i class="fas fa-tags"></i>
                <span>Product Categories</span>
            </h3>
            <div class="stat-box">
                <div class="stat-icon">
                    <i class="fas fa-folder"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Categories</div>
                    <div class="stat-value"><?= $total_categories ?></div>
                </div>
            </div>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ADD CATEGORY -->
    <div class="card">
        <div class="card-body">
            <h5>
                <i class="fas fa-plus-circle"></i>
                Add New Category
            </h5>
            <form method="POST" class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">
                        <i class="fas fa-tag"></i>
                        Category Name
                    </label>
                    <input type="text" 
                           name="category_name" 
                           class="form-control" 
                           placeholder="e.g., Books, Toys, Flash Cards, Puzzles"
                           required>
                </div>
                <div class="col-md-4 d-grid">
                    <label class="form-label d-block">&nbsp;</label>
                    <button class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- CATEGORY LIST -->
    <div class="card">
        <div class="card-body">
            <h5>
                <i class="fas fa-list"></i>
                Categories List
            </h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag me-2"></i>ID</th>
                            <th><i class="fas fa-tag me-2"></i>Category Name</th>
                            <th class="text-center" width="120"><i class="fas fa-cog me-2"></i>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php 
                        $icons = ['fa-book', 'fa-puzzle-piece', 'fa-gamepad', 'fa-pencil', 'fa-graduation-cap', 'fa-paint-brush'];
                        $index = 0;
                        while ($row = $result->fetch_assoc()): 
                            $icon = $icons[$index % count($icons)];
                            $index++;
                        ?>
                        <tr>
                            <td>
                                <span class="category-badge">
                                    <i class="fas <?= $icon ?>"></i>
                                </span>
                            </td>
                            <td>
                                <div class="category-name">
                                    <strong><?= htmlspecialchars($row['Category_Name']) ?></strong>
                                </div>
                            </td>
                            <td class="text-center">
                                <a href="?delete=<?= $row['Category_ID'] ?>"
                                   onclick="return confirm('Are you sure you want to delete this category? This may affect related products.')"
                                   class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash-alt me-1"></i>Delete
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">
                                <div class="empty-state">
                                    <i class="fas fa-folder-open"></i>
                                    <p class="mb-0"><strong>No categories found</strong></p>
                                    <p class="text-muted">Add your first category using the form above</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>