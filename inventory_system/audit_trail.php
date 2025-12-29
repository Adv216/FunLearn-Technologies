<?php
include 'secure_page_template.php'; 
include 'db_connect.php'; 

// === PERMISSION CHECK: ADMIN ONLY for Audit Trail ===
if (!check_permission([ROLE_ADMIN])) {
    header("Location: index.php?error=permission");
    exit;
}
// ==========================

// --- CORE UNION QUERY: COMBINING ALL STOCK MOVEMENTS ---
// We combine Sales (negative), Purchases (positive), and Adjustments (+/-)
$sql_audit_trail = "
    -- 1. SALES (INVOICE) LOG
    SELECT 
        I.Invoice_ID AS Ref_ID,
        I.Date AS Date,
        P.Name AS ProductName,
        -(ID.Quantity) AS Quantity_Change, -- Sales decrease stock, so it's negative
        'SALE' AS Transaction_Type,
        C.Name AS Reference_Name,
        CONCAT('Auto (Sale to ', C.Name, ')') AS Recorded_By
    FROM 
        INVOICE_DETAILS ID
    JOIN 
        INVOICE I ON ID.Invoice_ID = I.Invoice_ID
    LEFT JOIN
        CUSTOMER C ON I.customer_ID = C.customer_ID
    JOIN
        PRODUCTS P ON ID.Product_ID = P.Product_ID
    
    UNION ALL
    
    -- 2. PURCHASES (STOCK IN) LOG
    SELECT 
        PU.Purchase_ID AS Ref_ID,
        PU.Date AS Date,
        P.Name AS ProductName,
        PD.Quantity AS Quantity_Change, -- Purchases increase stock, so it's positive
        'PURCHASE' AS Transaction_Type,
        S.Name AS Reference_Name,
        'Auto (Purchase)' AS Recorded_By
    FROM 
        PURCHASE_DETAILS PD
    JOIN 
        PURCHASE PU ON PD.Purchase_ID = PU.Purchase_ID
    LEFT JOIN
        SUPPLIER S ON PU.Supplier_ID = S.Supplier_ID
    JOIN
        PRODUCTS P ON PD.Product_ID = P.Product_ID

    UNION ALL
    
    -- 3. ADJUSTMENTS (MANUAL) LOG
    SELECT 
        SA.Adjustment_ID AS Ref_ID,
        SA.Date AS Date,
        P.Name AS ProductName,
        SA.Adjustment_Quantity AS Quantity_Change, -- Already signed +/-
        'ADJUSTMENT' AS Transaction_Type,
        SA.Reason AS Reference_Name,
        SA.Recorded_By AS Recorded_By
    FROM 
        STOCK_ADJUSTMENT SA
    JOIN
        PRODUCTS P ON SA.Product_ID = P.Product_ID

    ORDER BY Date DESC, Ref_ID DESC
    LIMIT 500
";
        
$result_audit = $conn->query($sql_audit_trail);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full Inventory Audit Trail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
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
        .container-main { max-width: 1400px; margin: 2rem auto; padding: 0 1rem; }
        .page-header { background: white; border-radius: 1.5rem; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .page-header h1 { color: var(--danger); font-weight: 700; margin: 0 0 0.5rem 0; display: flex; align-items: center; gap: 1rem; }
        .table-card { background: white; border-radius: 1.5rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); overflow: hidden; }
        .table-modern thead th { background: #f1f5f9; color: #475569; font-weight: 700; padding: 1.25rem 1.5rem; }
        .table-modern td { vertical-align: middle; }
        
        /* Transaction Type Badges */
        .badge-sale { background: #fee2e2; color: var(--danger); }
        .badge-purchase { background: #dbeafe; color: var(--primary); }
        .badge-adjustment { background: #fef3c7; color: var(--warning); }

        /* Quantity Change Styling */
        .qty-loss { color: var(--danger); font-weight: 700; }
        .qty-gain { color: var(--success); font-weight: 700; }
        .qty-zero { color: #64748b; font-style: italic; }
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
    
    <div class="container-main">
        <div class="page-header">
            <h1><i class="fas fa-history"></i> Full Inventory Audit Trail</h1>
            <p class="text-muted">Consolidated log of all sales, purchases, and manual inventory adjustments. </p>
        </div>
        
        <div class="table-card table-responsive">
            <?php if ($result_audit && $result_audit->num_rows > 0): ?>
            <table class="table table-modern table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width: 10%;">Date</th>
                        <th style="width: 12%;">Type</th>
                        <th style="width: 30%;">Product</th>
                        <th style="width: 15%;">Qty Change</th>
                        <th style="width: 25%;">Reference/Reason</th>
                        <th style="width: 8%;">By User</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result_audit->fetch_assoc()): 
                        $qty_change = (int)$row['Quantity_Change'];
                        $qty_class = ($qty_change < 0) ? 'qty-loss' : 'qty-gain';
                        $qty_prefix = ($qty_change > 0) ? '+' : '';
                        $type_class = strtolower(str_replace(['SALE', 'PURCHASE', 'ADJUSTMENT'], ['sale', 'purchase', 'adjustment'], $row['Transaction_Type']));
                    ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($row['Date'])) ?></td>
                        <td><span class="badge rounded-pill badge-<?= $type_class ?>"><?= htmlspecialchars($row['Transaction_Type']) ?></span></td>
                        <td><?= htmlspecialchars($row['ProductName']) ?></td>
                        <td class="<?= $qty_class ?>">
                            <?= $qty_prefix . number_format($qty_change) ?>
                        </td>
                        <td><?= htmlspecialchars($row['Reference_Name']) ?> (Ref ID: #<?= $row['Ref_ID'] ?>)</td>
                        <td><?= htmlspecialchars($row['Recorded_By']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="p-5 text-center text-muted">
                    <i class="fas fa-lock fa-3x mb-3 text-secondary"></i>
                    <p>No inventory movements found in the system yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>