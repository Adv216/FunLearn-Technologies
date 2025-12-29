<?php
include 'secure_page_template.php'; 
include 'db_connect.php'; 

$sql = "SELECT Product_ID, Name, Price, HsnCode, Unit, Stock_Quantity FROM PRODUCTS ORDER BY Name ASC";
$result = $conn->query($sql);

if (!$result) {
    die("Error fetching products: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Stock Levels</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #06b6d4;
        }

        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }

        .navbar {
            background: rgba(30, 41, 59, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #fff !important;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .inventory-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .page-header {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-header-content h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-header-content h1 i {
            color: var(--info);
        }

        .page-header-content p {
            color: #64748b;
            margin: 0;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
        }

        .stat-card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.primary {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(79, 70, 229, 0.05));
            color: var(--primary);
        }

        .stat-icon.success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
            color: var(--success);
        }

        .stat-icon.danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05));
            color: var(--danger);
        }

        .stat-label {
            font-size: 0.875rem;
            color: #64748b;
            font-weight: 500;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
        }

        .table-card {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table-header {
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            padding: 1.5rem 2rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .table-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .search-box {
            position: relative;
            max-width: 400px;
        }

        .search-box input {
            padding: 0.75rem 1rem 0.75rem 3rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            width: 100%;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            outline: none;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .table-modern {
            width: 100%;
            margin: 0;
        }

        .table-modern thead th {
            background: white;
            color: #475569;
            font-weight: 700;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1.25rem 1.5rem;
            border-bottom: 2px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table-modern tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-modern tbody tr:hover {
            background: #f8fafc;
            transform: scale(1.01);
        }

        .table-modern tbody td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            color: #334155;
        }

        .product-name {
            font-weight: 600;
            color: #1e293b;
        }

        .price-badge {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 700;
            display: inline-block;
        }

        .stock-badge {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stock-good {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
        }

        .stock-medium {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: #92400e;
        }

        .stock-low {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
        }

        .stock-out {
            background: linear-gradient(135deg, #e5e7eb, #d1d5db);
            color: #374151;
        }

        .btn-enhanced {
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary-enhanced {
            background: linear-gradient(135deg, var(--primary), #4338ca);
            color: white;
        }

        .btn-primary-enhanced:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .table-modern {
                font-size: 0.875rem;
            }
            
            .table-modern thead th,
            .table-modern tbody td {
                padding: 0.75rem;
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
                    <li class="nav-item">
                        <a class="nav-link" href="invoice_history.php">
                            <i class="fas fa-history me-2"></i>History
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item me-3 d-flex align-items-center">
                        <span class="navbar-text text-white-50">
                            <i class="fas fa-user-circle me-1"></i> Logged in as: 
                            <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                            (<?= htmlspecialchars($_SESSION['role']) ?>)
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

    <div class="inventory-container">
        
        <div class="page-header">
            <div class="page-header-content">
                <h1>
                    <i class="fas fa-warehouse"></i>
                    <span>Inventory Management</span>
                </h1>
                <p>Monitor and manage your product stock levels</p>
            </div>
            <?php if (check_permission([ROLE_ADMIN, ROLE_MANAGER])): ?>
            <a href="product_form.php" class="btn btn-primary-enhanced btn-enhanced">
                <i class="fas fa-plus-circle me-2"></i>Add New Product
            </a>
            <?php endif; ?>
        </div>

        <?php
        $total_products = $result->num_rows;
        $low_stock_count = 0;
        $out_of_stock = 0;
        $total_value = 0;
        
        $result->data_seek(0);
        while($row = $result->fetch_assoc()) {
            if ($row['Stock_Quantity'] == 0) $out_of_stock++;
            else if ($row['Stock_Quantity'] < 5) $low_stock_count++;
            $total_value += $row['Price'] * $row['Stock_Quantity'];
        }
        $result->data_seek(0);
        ?>

        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon primary">
                        <i class="fas fa-box"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Products</div>
                        <div class="stat-value"><?= $total_products ?></div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="stat-label">Low Stock</div>
                        <div class="stat-value"><?= $low_stock_count ?></div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon success">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <div>
                        <div class="stat-label">Inventory Value</div>
                        <div class="stat-value">₹<?= number_format($total_value, 0) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h2>
                        <i class="fas fa-list"></i>
                        <span>Product Inventory</span>
                    </h2>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search products..." onkeyup="searchTable()">
                    </div>
                </div>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-modern" id="inventoryTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Unit Price</th>
                                <th>Unit</th>
                                <th>HSN Code</th>
                                <th>Stock Quantity</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): 
                                $stock = $row['Stock_Quantity'];
                                $status_class = '';
                                $status_text = '';
                                $status_icon = '';
                                
                                if ($stock == 0) {
                                    $status_class = 'stock-out';
                                    $status_text = 'Out of Stock';
                                    $status_icon = 'fas fa-times-circle';
                                } else if ($stock < 5) {
                                    $status_class = 'stock-low';
                                    $status_text = 'Low Stock';
                                    $status_icon = 'fas fa-exclamation-triangle';
                                } else if ($stock < 20) {
                                    $status_class = 'stock-medium';
                                    $status_text = 'Medium';
                                    $status_icon = 'fas fa-minus-circle';
                                } else {
                                    $status_class = 'stock-good';
                                    $status_text = 'In Stock';
                                    $status_icon = 'fas fa-check-circle';
                                }
                            ?>
                            <tr>
                                <td><strong>#<?= htmlspecialchars($row['Product_ID']) ?></strong></td>
                                <td class="product-name"><?= htmlspecialchars($row['Name']) ?></td>
                                <td><span class="price-badge">₹<?= number_format($row['Price'], 2) ?></span></td>
                                <td><?= htmlspecialchars($row['Unit']) ?></td>
                                <td><?= htmlspecialchars($row['HsnCode']) ?></td>
                                <td><strong><?= htmlspecialchars($row['Stock_Quantity']) ?></strong></td>
                                <td>
                                    <span class="stock-badge <?= $status_class ?>">
                                        <i class="<?= $status_icon ?>"></i>
                                        <span><?= $status_text ?></span>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="p-4">
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Products with stock quantity below 5 units are marked as low stock
                    </p>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>No Products Found</h3>
                    <p>Start by adding your first product to the inventory</p>
                    <a href="product_form.php" class="btn btn-primary-enhanced btn-enhanced mt-3">
                        <i class="fas fa-plus-circle me-2"></i>Add Product
                    </a>
                </div>
            <?php endif; ?>

            <?php $conn->close(); ?>
        </div>

    </div>

    <script>
        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('inventoryTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let found = false;
                const td = tr[i].getElementsByTagName('td');
                
                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                tr[i].style.display = found ? '' : 'none';
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>