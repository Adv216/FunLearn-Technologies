<?php
include 'secure_page_template.php'; 
include 'db_connect.php'; 

// === PERMISSION CHECK ===
if (!check_permission([ROLE_ADMIN, ROLE_MANAGER])) {
    header("Location: index.php?error=permission");
    exit;
}
// ==========================

// Fetch all Products for the dropdown
$product_result = $conn->query("SELECT Product_ID, Name, Stock_Quantity, Unit FROM PRODUCTS ORDER BY Name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Adjustment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --warning: #f59e0b;
            --warning-dark: #d97706;
            --primary: #6366f1;
            --danger: #ef4444;
            --danger-dark: #dc2626;
            --success: #10b981;
            --success-dark: #059669;
            --dark: #1e293b;
            --gray: #64748b;
            --light-gray: #f1f5f9;
        }

        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            position: relative;
            overflow-x: hidden;
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
                radial-gradient(circle at 80% 80%, rgba(99, 102, 241, 0.3), transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(167, 139, 250, 0.3), transparent 50%);
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
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500;
            padding: 0.6rem 1.2rem !important;
            border-radius: 0.75rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .nav-link:hover::before {
            left: 100%;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .navbar-text strong {
            color: #fff;
            font-weight: 600;
        }

        .form-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1.5rem;
            position: relative;
            z-index: 1;
        }

        .form-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 2rem;
            box-shadow: 
                0 20px 60px -15px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            animation: slideUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
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
            background: linear-gradient(135deg, var(--warning), var(--warning-dark));
            color: white;
            padding: 3rem 2.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .form-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .form-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .form-header p {
            margin: 0;
            opacity: 0.95;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .form-body {
            padding: 3rem 2.5rem;
        }

        .adjustment-info {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
            font-size: 0.95rem;
            color: #92400e;
            font-weight: 600;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border: 2px solid #fbbf24;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.2);
            animation: fadeInDown 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.2s backwards;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .adjustment-info i {
            font-size: 1.5rem;
            color: var(--warning);
        }

        .form-group {
            margin-bottom: 2rem;
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) backwards;
        }

        .form-group:nth-child(2) { animation-delay: 0.1s; }
        .form-group:nth-child(3) { animation-delay: 0.15s; }
        .form-group:nth-child(4) { animation-delay: 0.2s; }
        .form-group:nth-child(5) { animation-delay: 0.25s; }
        .form-group:nth-child(6) { animation-delay: 0.3s; }
        .form-group:nth-child(7) { animation-delay: 0.35s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-label {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1rem;
        }

        .form-label i {
            color: var(--warning);
            font-size: 1.2rem;
        }

        .required-mark {
            color: var(--danger);
            font-weight: 800;
            font-size: 1.1rem;
            margin-left: 0.25rem;
        }

        .form-control, .form-select, textarea {
            border: 2px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1rem 1.25rem;
            transition: all 0.3s ease;
            font-size: 1rem;
            font-weight: 500;
            background: white;
        }

        .form-control:focus, .form-select:focus, textarea:focus {
            border-color: var(--warning);
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15);
            outline: none;
            transform: translateY(-2px);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Stock Display */
        .stock-display {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border: 2px solid var(--warning);
            border-radius: 1.25rem;
            padding: 1.5rem;
            text-align: center;
            font-weight: 700;
            margin-bottom: 2rem;
            display: none;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.2);
            animation: scaleIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .stock-display label {
            font-size: 1rem;
            color: #92400e;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stock-display span {
            font-size: 2rem;
            color: #78350f;
            background: white;
            padding: 0.5rem 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Radio Buttons */
        .radio-container {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .radio-option {
            flex: 1;
            min-width: 200px;
        }

        .form-check {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border: 2px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .form-check:hover {
            border-color: var(--warning);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .form-check-input:checked + .form-check-label {
            color: var(--warning-dark);
        }

        .form-check:has(.form-check-input:checked) {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border-color: var(--warning);
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.2);
        }

        .form-check-input {
            width: 1.5rem;
            height: 1.5rem;
            margin-top: 0;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: var(--warning);
            border-color: var(--warning);
        }

        .radio-label {
            font-weight: 600;
            color: var(--dark);
            font-size: 1rem;
            margin-left: 0.75rem;
            cursor: pointer;
        }

        .radio-label i {
            font-size: 1.2rem;
        }

        /* Buttons */
        .btn-enhanced {
            padding: 1.2rem 2.5rem;
            border-radius: 1rem;
            font-weight: 700;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            font-size: 1.1rem;
            position: relative;
            overflow: hidden;
        }

        .btn-enhanced::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }

        .btn-enhanced:hover::before {
            width: 400px;
            height: 400px;
        }

        .btn-warning-enhanced {
            background: linear-gradient(135deg, var(--warning), var(--warning-dark));
            color: white;
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3);
        }

        .btn-warning-enhanced:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(245, 158, 11, 0.4);
            color: white;
        }

        .btn-back {
            background: white;
            color: var(--gray);
            border: 2px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .btn-back:hover {
            background: var(--light-gray);
            border-color: #cbd5e1;
            color: var(--dark);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .form-footer {
            display: flex;
            gap: 1rem;
            margin-top: 2.5rem;
            padding-top: 2.5rem;
            border-top: 3px solid var(--light-gray);
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.4s backwards;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-header h1 {
                font-size: 1.8rem;
            }

            .form-body {
                padding: 2rem 1.5rem;
            }

            .radio-container {
                flex-direction: column;
            }

            .radio-option {
                min-width: 100%;
            }

            .form-footer {
                flex-direction: column-reverse;
            }

            .stock-display {
                flex-direction: column;
                gap: 1rem;
            }

            .stock-display span {
                font-size: 1.5rem;
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
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar me-2"></i>Reports
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
                <h1><i class="fas fa-sliders"></i> Stock Adjustment</h1>
                <p>Manually modify inventory levels for audit, loss, or damage</p>
            </div>

            <div class="form-body">
                <div class="adjustment-info">
                    <i class="fas fa-shield-halved"></i>
                    <span>This action creates an audit trail. Use only for non-sale/non-purchase stock changes.</span>
                </div>
                
                <form action="process_adjustment.php" method="POST" id="adjustmentForm">
                    
                    <div class="form-group">
                        <label for="product_id" class="form-label">
                            <i class="fas fa-box-open"></i>
                            <span>Select Product</span>
                            <span class="required-mark">*</span>
                        </label>
                        <select name="product_id" id="product_id" class="form-select" onchange="updateStockDisplay()" required>
                            <option value="">-- Choose a product to adjust --</option>
                            <?php 
                            $product_array = [];
                            if ($product_result->num_rows > 0) {
                                while ($row = $product_result->fetch_assoc()) {
                                    $product_array[$row['Product_ID']] = [
                                        'stock' => $row['Stock_Quantity'],
                                        'unit' => $row['Unit']
                                    ];
                                    echo "<option value='{$row['Product_ID']}'>{$row['Name']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="stock-display" id="stock-display">
                        <label>
                            <i class="fas fa-warehouse"></i>
                            Current Stock:
                        </label>
                        <span id="current-stock-value">0</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-code-branch"></i>
                            <span>Adjustment Type</span>
                            <span class="required-mark">*</span>
                        </label>
                        <div class="radio-container">
                            <div class="radio-option">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="adjustment_type" id="type_add" value="add" required checked>
                                    <label class="form-check-label radio-label" for="type_add">
                                        <i class="fas fa-plus-circle text-success"></i> Add Stock
                                        <div style="font-size: 0.85rem; color: #64748b; margin-top: 0.25rem; font-weight: 500;">Found items, Audit correction</div>
                                    </label>
                                </div>
                            </div>
                            <div class="radio-option">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="adjustment_type" id="type_remove" value="remove" required>
                                    <label class="form-check-label radio-label" for="type_remove">
                                        <i class="fas fa-minus-circle text-danger"></i> Remove Stock
                                        <div style="font-size: 0.85rem; color: #64748b; margin-top: 0.25rem; font-weight: 500;">Damage, Loss, Theft</div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="quantity" class="form-label">
                            <i class="fas fa-hashtag"></i>
                            <span>Quantity to Adjust (<span id="unit-label">Units</span>)</span>
                            <span class="required-mark">*</span>
                        </label>
                        <input type="number" id="quantity" name="quantity" class="form-control" min="1" placeholder="Enter amount to add or remove" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reason" class="form-label">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Reason for Adjustment</span>
                            <span class="required-mark">*</span>
                        </label>
                        <select name="reason" id="reason" class="form-select" required>
                            <option value="">-- Select reason for adjustment --</option>
                            <option value="Damage/Breakage">Damage/Breakage</option>
                            <option value="Theft/Loss">Theft/Loss</option>
                            <option value="Audit Correction (Increase)">Audit Correction (Increase)</option>
                            <option value="Audit Correction (Decrease)">Audit Correction (Decrease)</option>
                            <option value="Return (Non-Invoice Related)">Return (Non-Invoice Related)</option>
                            <option value="Other">Other (Explain in Notes)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="notes" class="form-label">
                            <i class="fas fa-file-lines"></i>
                            <span>Additional Notes</span>
                        </label>
                        <textarea id="notes" name="notes" class="form-control" placeholder="Optional: Provide more details about the adjustment (who, what, when, why)" rows="4"></textarea>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-warning-enhanced btn-enhanced flex-grow-1">
                            <i class="fas fa-check-circle me-2"></i>Process Adjustment
                        </button>
                        <a href="index.php" class="btn btn-back btn-enhanced">
                            <i class="fas fa-xmark me-2"></i>Cancel
                        </a>
                    </div>

                </form>
            </div>

        </div>
    </div>

    <script>
        const PRODUCT_STOCK_DATA = <?php echo json_encode($product_array); ?>;

        function updateStockDisplay() {
            const productId = document.getElementById('product_id').value;
            const stockDisplay = document.getElementById('stock-display');
            const stockValueSpan = document.getElementById('current-stock-value');
            const unitLabelSpan = document.getElementById('unit-label');

            if (productId && PRODUCT_STOCK_DATA[productId]) {
                const data = PRODUCT_STOCK_DATA[productId];
                stockValueSpan.textContent = `${data.stock} ${data.unit}`;
                unitLabelSpan.textContent = data.unit;
                stockDisplay.style.display = 'flex';
            } else {
                stockDisplay.style.display = 'none';
                unitLabelSpan.textContent = 'Units';
            }
        }

        // Form submission confirmation
        document.getElementById('adjustmentForm').addEventListener('submit', function(e) {
            const product = document.getElementById('product_id');
            const quantity = document.getElementById('quantity').value;
            const adjustmentType = document.querySelector('input[name="adjustment_type"]:checked').value;
            const reason = document.getElementById('reason').value;
            
            const productName = product.options[product.selectedIndex].text;
            const action = adjustmentType === 'add' ? 'ADD' : 'REMOVE';
            const actionColor = adjustmentType === 'add' ? '✅' : '⚠️';
            
            const confirmMessage = `${actionColor} CONFIRM STOCK ADJUSTMENT\n\n` +
                `Product: ${productName}\n` +
                `Action: ${action} ${quantity} units\n` +
                `Reason: ${reason}\n\n` +
                `This action will be recorded in the audit trail.\n` +
                `Do you want to proceed?`;
            
            if (!confirm(confirmMessage)) {
                e.preventDefault();
            }
        });

        // Initialize stock display on page load
        window.onload = updateStockDisplay;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>