<?php
include 'secure_page_template.php'; 
include 'db_connect.php'; 

// === PERMISSION CHECK ===
if (!check_permission([ROLE_ADMIN, ROLE_MANAGER])) {
    header("Location: index.php?error=permission");
    exit;
}
// ==========================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #06b6d4;
            --success: #10b981;
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

        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .form-card {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-header {
            background: linear-gradient(135deg, var(--primary), #0891b2);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .form-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
        }

        .form-header p {
            margin: 0;
            opacity: 0.9;
        }

        .form-body {
            padding: 2.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label i {
            color: var(--primary);
        }

        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.875rem 1rem;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.1);
            outline: none;
        }

        .required-mark {
            color: #ef4444;
            font-weight: 700;
        }

        .btn-enhanced {
            padding: 0.875rem 2rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            font-size: 1.05rem;
        }

        .btn-info-enhanced {
            background: linear-gradient(135deg, var(--primary), #0891b2);
            color: white;
        }

        .btn-info-enhanced:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(6, 182, 212, 0.3);
            color: white;
        }

        .btn-back {
            background: white;
            color: #64748b;
            border: 2px solid #e2e8f0;
        }

        .btn-back:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #475569;
        }

        .form-footer {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #f1f5f9;
        }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
        }

        .input-icon-wrapper .form-control {
            padding-left: 2.75rem;
        }

        .form-hint {
            font-size: 0.875rem;
            color: #64748b;
            margin-top: 0.25rem;
        }

        .input-group-text {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.1), rgba(6, 182, 212, 0.05));
            border: 2px solid #e2e8f0;
            border-right: none;
            border-radius: 0.75rem 0 0 0.75rem;
            color: var(--primary);
            font-weight: 600;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 0.75rem 0.75rem 0;
        }

        .input-group .form-control:focus {
            border-left: 2px solid var(--primary);
        }

        @media (max-width: 768px) {
            .form-body {
                padding: 1.5rem;
            }
            
            .form-footer {
                flex-direction: column;
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

    <div class="form-container">
        <div class="form-card">
            
            <div class="form-header">
                <h1><i class="fas fa-box-open me-3"></i>Add New Product</h1>
                <p>Add a new product to your inventory catalog</p>
            </div>

            <div class="form-body">
                <form action="handle_product.php" method="POST">
                    <input type="hidden" name="action" value="add">

                    <div class="form-group">
                        <label for="name" class="form-label">
                            <i class="fas fa-tag"></i>
                            <span>Product Name</span>
                            <span class="required-mark">*</span>
                        </label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-tag input-icon"></i>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Enter product name" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="price" class="form-label">
                                    <i class="fas fa-rupee-sign"></i>
                                    <span>Selling Price (per unit)</span>
                                    <span class="required-mark">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" id="price" name="price" class="form-control" step="0.01" min="0.01" placeholder="0.00" required>
                                </div>
                                <div class="form-hint">Enter the price per unit</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit" class="form-label">
                                    <i class="fas fa-balance-scale"></i>
                                    <span>Unit of Measurement</span>
                                    <span class="required-mark">*</span>
                                </label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-balance-scale input-icon"></i>
                                    <input type="text" id="unit" name="unit" class="form-control" placeholder="e.g., Pcs, Kg, Box, Ltr" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="hsn" class="form-label">
                                    <i class="fas fa-barcode"></i>
                                    <span>HSN Code</span>
                                </label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-barcode input-icon"></i>
                                    <input type="text" id="hsn" name="hsn" class="form-control" placeholder="Enter HSN code">
                                </div>
                                <div class="form-hint">Harmonized System Nomenclature code (optional)</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="stock" class="form-label">
                                    <i class="fas fa-cubes"></i>
                                    <span>Initial Stock Quantity</span>
                                    <span class="required-mark">*</span>
                                </label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-cubes input-icon"></i>
                                    <input type="number" id="stock" name="stock" class="form-control" min="0" placeholder="0" required>
                                </div>
                                <div class="form-hint">Opening stock quantity</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-info-enhanced btn-enhanced flex-grow-1">
                            <i class="fas fa-plus-circle me-2"></i>Add Product
                        </button>
                        <a href="index.php" class="btn btn-back btn-enhanced">
                            <i class="fas fa-arrow-left me-2"></i>Cancel
                        </a>
                    </div>

                </form>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>