<?php
include 'secure_page_template.php'; 
include 'db_connect.php'; 

// === PERMISSION CHECK ===
if (!check_permission([ROLE_ADMIN, ROLE_MANAGER])) {
    header("Location: index.php?error=permission");
    exit;
}
// ==========================

// Fetch all Suppliers
$supplier_result = $conn->query("SELECT Supplier_ID, Name FROM SUPPLIER ORDER BY Name ASC");

// Fetch all Products
$product_result = $conn->query("SELECT Product_ID, Name FROM PRODUCTS ORDER BY Name ASC");

// Create PHP array from product data for easy JavaScript lookup
$product_data = [];
while ($row = $product_result->fetch_assoc()) {
    $product_data[$row['Product_ID']] = [
        'name' => $row['Name'],
        'price' => '0.00'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record New Purchase</title>
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
            --purchase-primary: #6366f1;
            --purchase-dark: #4f46e5;
            --success: #10b981;
            --success-dark: #059669;
            --danger: #ef4444;
            --danger-dark: #dc2626;
            --warning: #f59e0b;
            --info: #06b6d4;
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

        .purchase-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1.5rem;
            position: relative;
            z-index: 1;
        }

        .purchase-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 2rem;
            box-shadow: 
                0 20px 60px -15px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .purchase-header {
            background: linear-gradient(135deg, var(--purchase-primary), var(--purchase-dark));
            color: white;
            padding: 3rem 2.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .purchase-header::before {
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

        .purchase-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .purchase-header p {
            margin: 1rem 0 0 0;
            opacity: 0.95;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .purchase-body {
            padding: 2.5rem;
        }

        .form-section {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-radius: 1.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 2px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.2s backwards;
        }

        .form-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .form-section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid rgba(99, 102, 241, 0.2);
        }

        .form-section-title i {
            color: var(--purchase-primary);
            font-size: 1.8rem;
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
            color: var(--purchase-primary);
        }

        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 1rem;
            padding: 0.9rem 1.2rem;
            transition: all 0.3s ease;
            font-weight: 500;
            background: white;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--purchase-primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            transform: translateY(-2px);
        }

        .line-items-section {
            margin-top: 2rem;
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.3s backwards;
        }

        .table-modern {
            background: white;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.1);
            border: 2px solid #e2e8f0;
        }

        .table-modern thead {
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        }

        .table-modern thead th {
            font-weight: 700;
            color: var(--dark);
            border: none;
            padding: 1.25rem 1.5rem;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table-modern tbody td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            border-color: #f1f5f9;
        }

        .table-modern tfoot {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        }

        .table-modern tfoot td {
            font-weight: 700;
            font-size: 1.3rem;
            color: #0c4a6e;
            padding: 1.5rem;
            border: none;
        }

        .btn-add-line {
            background: linear-gradient(135deg, var(--purchase-primary), var(--purchase-dark));
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 1rem;
            font-weight: 700;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-add-line::before {
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

        .btn-add-line:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-add-line:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(99, 102, 241, 0.4);
            color: white;
        }

        .btn-remove {
            background: linear-gradient(135deg, var(--danger), var(--danger-dark));
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .btn-remove:hover {
            background: linear-gradient(135deg, var(--danger-dark), #b91c1c);
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--success), var(--success-dark));
            color: white;
            border: none;
            padding: 1.2rem 3rem;
            border-radius: 1rem;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-submit::before {
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

        .btn-submit:hover::before {
            width: 400px;
            height: 400px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(16, 185, 129, 0.4);
            color: white;
        }

        .btn-outline-secondary {
            border: 2px solid var(--gray);
            color: var(--gray);
            border-radius: 1rem;
            padding: 1.2rem 2rem;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .btn-outline-secondary:hover {
            background: var(--gray);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(100, 116, 139, 0.3);
        }

        .grand-total-display {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2rem;
            font-weight: 800;
            display: inline-block;
            animation: pulse-subtle 2s infinite;
        }

        @keyframes pulse-subtle {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .subtotal-cell {
            font-weight: 700;
            color: var(--purchase-primary);
            font-size: 1.1rem;
        }

        .line-item-row {
            animation: slideIn 0.3s ease-out;
            transition: all 0.3s ease;
        }

        .line-item-row:hover {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.05), transparent);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideOut {
            to {
                opacity: 0;
                transform: translateX(-20px);
            }
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: var(--warning);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .empty-state h3 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        /* Number Input Styling */
        input[type="number"] {
            font-weight: 600;
        }

        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            opacity: 1;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .purchase-header h1 {
                font-size: 1.8rem;
            }

            .form-section {
                padding: 1.5rem;
            }

            .table-responsive {
                font-size: 0.875rem;
            }
            
            .form-control, .form-select {
                font-size: 0.875rem;
                padding: 0.7rem 1rem;
            }

            .btn-submit {
                padding: 1rem 2rem;
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

    <div class="purchase-container">
        <div class="purchase-card">
            
            <div class="purchase-header">
                <h1><i class="fas fa-truck-loading"></i> Record New Purchase</h1>
                <p>Log incoming inventory and update stock levels automatically</p>
            </div>

            <div class="purchase-body">
                <form action="process_purchase.php" method="POST" id="purchaseForm">
                    
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-building"></i>
                            <span>Supplier Information</span>
                        </div>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="supplier_id" class="form-label">
                                    <i class="fas fa-industry"></i>Select Supplier
                                </label>
                                <select name="supplier_id" id="supplier_id" class="form-select" required>
                                    <option value="">-- Choose a supplier --</option>
                                    <?php
                                    if ($supplier_result->num_rows > 0) {
                                        while ($row = $supplier_result->fetch_assoc()) {
                                            echo "<option value='{$row['Supplier_ID']}'>{$row['Name']}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="purchase_date" class="form-label">
                                    <i class="fas fa-calendar-days"></i>Purchase Date
                                </label>
                                <input type="date" id="purchase_date" name="purchase_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="line-items-section">
                        <div class="form-section-title mb-3">
                            <i class="fas fa-boxes-stacked"></i>
                            <span>Items Received</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-modern" id="purchase-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40%;"><i class="fas fa-box me-2"></i>Product</th>
                                        <th style="width: 15%;"><i class="fas fa-hashtag me-2"></i>Quantity</th>
                                        <th style="width: 20%;"><i class="fas fa-rupee-sign me-2"></i>Purchase Rate</th>
                                        <th style="width: 20%;"><i class="fas fa-calculator me-2"></i>Subtotal</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="line-items">
                                    <tr class="empty-state">
                                        <td colspan="5">
                                            <i class="fas fa-inbox"></i>
                                            <h3>No items added yet</h3>
                                            <p>Click "Add Product Line" below to start adding items</p>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end">
                                            <i class="fas fa-calculator me-2"></i>Purchase Total:
                                        </td>
                                        <td colspan="2" class="text-center">
                                            <span class="grand-total-display" id="grand-total-display">₹0.00</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <input type="hidden" name="total_amount" id="total_amount_input">
                        
                        <button type="button" onclick="addLineItem()" class="btn btn-add-line mt-3">
                            <i class="fas fa-plus-circle me-2"></i>Add Product Line
                        </button>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-4">
                        <a href="index.php" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-xmark me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-submit">
                            <i class="fas fa-floppy-disk me-2"></i>Record Purchase
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        const PRODUCT_DATA = <?php echo json_encode($product_data); ?>;
        let itemIndex = 0;

        function addLineItem() {
            // Remove empty state if exists
            const emptyState = document.querySelector('.empty-state');
            if (emptyState) {
                emptyState.remove();
            }

            const tableBody = document.getElementById('line-items');
            const newRow = tableBody.insertRow();
            newRow.id = 'row-' + itemIndex;
            newRow.classList.add('line-item-row');

            // Product Dropdown
            let cellProduct = newRow.insertCell();
            let selectHtml = `<select name="products[${itemIndex}][product_id]" class="form-select" onchange="updateRow(${itemIndex})" required>`;
            selectHtml += '<option value="">-- Select Product --</option>';
            for (const id in PRODUCT_DATA) {
                selectHtml += `<option value="${id}">${PRODUCT_DATA[id].name}</option>`;
            }
            selectHtml += '</select>';
            cellProduct.innerHTML = selectHtml;

            // Quantity Input
            let cellQty = newRow.insertCell();
            cellQty.innerHTML = `<input type="number" name="products[${itemIndex}][quantity]" value="1" min="1" onchange="updateRow(${itemIndex})" class="form-control qty-input" required>`;

            // Rate Input
            let cellPrice = newRow.insertCell();
            cellPrice.innerHTML = `<input type="number" name="products[${itemIndex}][rate]" value="0.00" step="0.01" min="0" onchange="updateRow(${itemIndex})" class="form-control rate-input" required>`;

            // Subtotal Display
            let cellSubtotal = newRow.insertCell();
            cellSubtotal.classList.add('text-center', 'subtotal-cell');
            cellSubtotal.innerHTML = `<span id="subtotal-${itemIndex}">₹0.00</span><input type="hidden" name="products[${itemIndex}][subtotal]" value="0.00" class="subtotal-input">`;

            // Remove Button
            let cellRemove = newRow.insertCell();
            cellRemove.classList.add('text-center');
            cellRemove.innerHTML = `<button type="button" class="btn btn-remove" onclick="removeLineItem(${itemIndex})" title="Remove"><i class="fas fa-trash"></i></button>`;
            
            itemIndex++;
        }

        function updateRow(index) {
            const row = document.getElementById(`row-${index}`);
            if (!row) return;

            const qtyInput = row.querySelector('.qty-input');
            const rateInput = row.querySelector('.rate-input');
            
            let quantity = parseFloat(qtyInput.value) || 0;
            let rate = parseFloat(rateInput.value) || 0;
            
            const subtotal = quantity * rate;
            
            document.getElementById(`subtotal-${index}`).textContent = '₹' + subtotal.toFixed(2);
            row.querySelector('.subtotal-input').value = subtotal.toFixed(2);

            updateGrandTotal();
        }

        function removeLineItem(index) {
            const row = document.getElementById(`row-${index}`);
            if (row) {
                row.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => {
                    row.remove();
                    updateGrandTotal();
                    
                    // Show empty state if no items left
                    const tbody = document.getElementById('line-items');
                    if (tbody.children.length === 0) {
                        tbody.innerHTML = `<tr class="empty-state">
                            <td colspan="5">
                                <i class="fas fa-inbox"></i>
                                <h3>No items added yet</h3>
                                <p>Click "Add Product Line" below to start adding items</p>
                            </td>
                        </tr>`;
                    }
                }, 300);
            }
        }

        function updateGrandTotal() {
            let grandTotal = 0;
            document.querySelectorAll('.subtotal-input').forEach(input => {
                grandTotal += parseFloat(input.value) || 0;
            });

            document.getElementById('grand-total-display').textContent = '₹' + grandTotal.toFixed(2);
            document.getElementById('total_amount_input').value = grandTotal.toFixed(2);
        }

        // Form submission validation
        document.getElementById('purchaseForm').addEventListener('submit', function(e) {
            const tbody = document.getElementById('line-items');
            const hasItems = tbody.querySelector('.line-item-row') !== null;
            
            if (!hasItems) {
                e.preventDefault();
                alert('⚠️ Please add at least one product line item before submitting the purchase.');
                return false;
            }
        });

        window.onload = function() {
            addLineItem();
        };
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>