<?php
include 'secure_page_template.php'; 
include 'db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Collect POST data
    $customer_id  = (int)$_POST['customer_id'];
    $invoice_date = $conn->real_escape_string($_POST['invoice_date']);
    $total_amount = (float)$_POST['total_amount'];
    $products     = $_POST['products']; 

    // Flag for transaction success
    $success = true;
    $invoice_id = null;
    $item_count = 0;

    // ==========================================================
    // START TRANSACTION: Ensures all or nothing is committed
    // ==========================================================
    $conn->begin_transaction();
    
    try {
        // 2. INSERT into INVOICE table (Header)
        $sql_invoice = "INSERT INTO INVOICE (customer_ID, Date, TotalAmount) 
                        VALUES ('$customer_id', '$invoice_date', $total_amount)";
        
        if (!$conn->query($sql_invoice)) {
            throw new Exception("Error inserting Invoice header: " . $conn->error);
        }
        
        $invoice_id = $conn->insert_id;

        // 3. Loop through Line Items and insert into INVOICE_DETAILS and update PRODUCTS
        foreach ($products as $item) {
            $product_id = (int)$item['product_id'];
            $quantity   = (int)$item['quantity'];
            $rate       = (float)$item['rate'];
            $subtotal   = (float)$item['subtotal'];
            
            if ($product_id <= 0 || $quantity <= 0) continue;
            
            $item_count++;

            // A. INSERT into INVOICE_DETAILS (Line Item)
            $sql_detail = "INSERT INTO INVOICE_DETAILS (Invoice_ID, Product_ID, Quantity, Rate, Subtotal) 
                           VALUES ($invoice_id, $product_id, $quantity, $rate, $subtotal)";
                           
            if (!$conn->query($sql_detail)) {
                throw new Exception("Error inserting Invoice detail: " . $conn->error);
            }

            // B. CRITICAL: UPDATE Stock Quantity in PRODUCTS
            $sql_stock_update = "UPDATE PRODUCTS 
                                 SET Stock_Quantity = Stock_Quantity - $quantity 
                                 WHERE Product_ID = $product_id";
            
            if (!$conn->query($sql_stock_update)) {
                throw new Exception("Error updating inventory stock: " . $conn->error);
            }
        }

        // 4. If all queries succeeded, commit the transaction
        $conn->commit();
        
    } catch (Exception $e) {
        // 5. If any query failed, rollback the entire transaction
        $conn->rollback();
        $success = false;
        $error_message = $e->getMessage();
    }

    $conn->close();

    // 6. Display result
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $success ? 'Invoice Created' : 'Invoice Failed'; ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                padding: 40px 20px;
            }

            .navbar {
                background: rgba(0, 0, 0, 0.85) !important;
                backdrop-filter: blur(10px);
                border-bottom: 2px solid #667eea;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                margin-bottom: 40px;
            }

            .navbar-brand {
                font-weight: 700;
                font-size: 1.3rem;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .nav-link {
                transition: all 0.3s ease;
                position: relative;
                margin: 0 5px;
            }

            .nav-link:hover {
                color: #667eea !important;
                transform: translateY(-2px);
            }

            .nav-link::after {
                content: '';
                position: absolute;
                bottom: -5px;
                left: 0;
                width: 0;
                height: 2px;
                background: #667eea;
                transition: width 0.3s ease;
            }

            .nav-link:hover::after {
                width: 100%;
            }

            .result-container {
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: calc(100vh - 100px);
            }

            .result-card {
                background: white;
                border-radius: 20px;
                box-shadow: 0 25px 70px rgba(0, 0, 0, 0.25);
                padding: 50px;
                max-width: 600px;
                width: 100%;
                animation: slideUp 0.6s ease-out;
            }

            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(40px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .result-header {
                text-align: center;
                margin-bottom: 40px;
                animation: fadeIn 0.6s ease-out 0.2s both;
            }

            .success-icon {
                font-size: 5rem;
                color: #10b981;
                margin-bottom: 20px;
                animation: scaleIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            }

            .error-icon {
                font-size: 5rem;
                color: #ef4444;
                margin-bottom: 20px;
                animation: shake 0.5s ease-out;
            }

            @keyframes scaleIn {
                from {
                    transform: scale(0);
                    opacity: 0;
                }
                to {
                    transform: scale(1);
                    opacity: 1;
                }
            }

            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-10px); }
                75% { transform: translateX(10px); }
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }
                to {
                    opacity: 1;
                }
            }

            .result-title {
                font-size: 2rem;
                font-weight: 700;
                margin-bottom: 10px;
            }

            .result-title.success {
                color: #065f46;
            }

            .result-title.error {
                color: #7f1d1d;
            }

            .result-subtitle {
                font-size: 0.95rem;
                color: #6b7280;
                margin-bottom: 30px;
            }

            .details-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                margin: 30px 0;
                padding: 30px;
                background: #f9fafb;
                border-radius: 15px;
                animation: fadeIn 0.6s ease-out 0.4s both;
            }

            .detail-item {
                padding: 15px;
                background: white;
                border-radius: 10px;
                border-left: 4px solid #667eea;
                transition: all 0.3s ease;
            }

            .detail-item:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 16px rgba(102, 126, 234, 0.15);
            }

            .detail-label {
                font-size: 0.75rem;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                font-weight: 700;
                margin-bottom: 8px;
            }

            .detail-value {
                font-size: 1.3rem;
                color: #1f2937;
                font-weight: 700;
                word-break: break-word;
            }

            .detail-icon {
                display: inline-block;
                width: 32px;
                height: 32px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                margin-bottom: 10px;
                font-size: 1rem;
            }

            .success-badge {
                display: inline-block;
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                color: white;
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 600;
                margin-bottom: 20px;
                animation: fadeIn 0.6s ease-out 0.3s both;
            }

            .error-message {
                background: #fef2f2;
                border: 2px solid #ef4444;
                border-radius: 12px;
                padding: 20px;
                margin: 25px 0;
                animation: fadeIn 0.6s ease-out 0.4s both;
            }

            .error-message-title {
                color: #7f1d1d;
                font-weight: 700;
                margin-bottom: 10px;
                font-size: 0.95rem;
            }

            .error-message-text {
                color: #991b1b;
                font-family: 'Courier New', monospace;
                font-size: 0.85rem;
                word-break: break-word;
                background: white;
                padding: 12px;
                border-radius: 8px;
                border-left: 4px solid #ef4444;
            }

            .info-banner {
                background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
                border-left: 4px solid #3b82f6;
                padding: 15px;
                border-radius: 10px;
                margin: 20px 0;
                animation: fadeIn 0.6s ease-out 0.5s both;
            }

            .info-banner-text {
                color: #1e40af;
                font-size: 0.9rem;
                font-weight: 500;
            }

            .divider {
                height: 2px;
                background: linear-gradient(90deg, transparent, #e5e7eb, transparent);
                margin: 30px 0;
            }

            .button-group {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px;
                margin-top: 30px;
                animation: fadeIn 0.6s ease-out 0.6s both;
            }

            .btn-custom {
                padding: 12px 24px;
                border-radius: 10px;
                font-weight: 600;
                border: none;
                cursor: pointer;
                transition: all 0.3s ease;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                font-size: 0.95rem;
            }

            .btn-success-custom {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                color: white;
            }

            .btn-success-custom:hover {
                transform: translateY(-3px);
                box-shadow: 0 12px 24px rgba(16, 185, 129, 0.3);
                color: white;
                text-decoration: none;
            }

            .btn-primary-custom {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }

            .btn-primary-custom:hover {
                transform: translateY(-3px);
                box-shadow: 0 12px 24px rgba(102, 126, 234, 0.3);
                color: white;
                text-decoration: none;
            }

            .btn-secondary-custom {
                background: white;
                color: #667eea;
                border: 2px solid #667eea;
            }

            .btn-secondary-custom:hover {
                background: #667eea;
                color: white;
                transform: translateY(-3px);
                box-shadow: 0 12px 24px rgba(102, 126, 234, 0.2);
                text-decoration: none;
            }

            .btn-warning-custom {
                background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
                color: white;
            }

            .btn-warning-custom:hover {
                transform: translateY(-3px);
                box-shadow: 0 12px 24px rgba(245, 158, 11, 0.3);
                color: white;
                text-decoration: none;
            }

            .button-group-full {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 12px;
                margin-top: 30px;
                animation: fadeIn 0.6s ease-out 0.6s both;
            }

            @media (max-width: 768px) {
                .result-card {
                    padding: 30px 20px;
                }

                .details-grid {
                    grid-template-columns: 1fr;
                    padding: 20px;
                }

                .result-title {
                    font-size: 1.5rem;
                }

                .success-icon,
                .error-icon {
                    font-size: 4rem;
                }

                .button-group,
                .button-group-full {
                    grid-template-columns: 1fr;
                }

                .button-group-full {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <i class="fas fa-chart-pie"></i> Billing System
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item"><a class="nav-link" href="invoice_form.php"><i class="fas fa-file-invoice"></i> New Invoice</a></li>
                        <?php if (check_permission([ROLE_ADMIN, ROLE_MANAGER])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="purchase_form.php">
                                <i class="fas fa-truck-loading me-2"></i>New Purchase
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="view_inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                        <li class="nav-item"><a class="nav-link" href="invoice_history.php"><i class="fas fa-history"></i> History</a></li>
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

        <div class="result-container">
            <div class="result-card">
                <?php if ($success): ?>
                    <div class="result-header">
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h1 class="result-title success">Invoice Created Successfully!</h1>
                        <p class="result-subtitle">Your invoice has been processed and saved to the system</p>
                        <div class="success-badge">
                            <i class="fas fa-lock"></i> Transaction Committed
                        </div>
                    </div>

                    <div class="details-grid">
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="detail-label">Invoice ID</div>
                            <div class="detail-value">#<?php echo $invoice_id; ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-rupee-sign"></i>
                            </div>
                            <div class="detail-label">Total Amount</div>
                            <div class="detail-value">₹<?php echo number_format($total_amount, 2); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="detail-label">Invoice Date</div>
                            <div class="detail-value"><?php echo date('d M Y', strtotime($invoice_date)); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="detail-label">Items Added</div>
                            <div class="detail-value"><?php echo $item_count; ?> Item(s)</div>
                        </div>
                    </div>

                    <div class="info-banner">
                        <i class="fas fa-info-circle"></i>
                        <span class="info-banner-text">Inventory stock has been automatically updated for all products.</span>
                    </div>

                    <div class="divider"></div>

                    <div class="button-group-full">
                        <a href="invoice_form.php" class="btn-custom btn-success-custom">
                            <i class="fas fa-plus-circle"></i> New Invoice
                        </a>
                        <a href="invoice_history.php" class="btn-custom btn-warning-custom">
                            <i class="fas fa-history"></i> View History
                        </a>
                        <a href="index.php" class="btn-custom btn-secondary-custom">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </div>

                <?php else: ?>
                    <div class="result-header">
                        <div class="error-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <h1 class="result-title error">Invoice Processing Failed</h1>
                        <p class="result-subtitle">An error occurred while creating your invoice</p>
                    </div>

                    <div class="error-message">
                        <div class="error-message-title">
                            <i class="fas fa-shield-alt"></i> Transaction Rolled Back
                        </div>
                        <p class="result-subtitle" style="margin: 10px 0 15px 0;">All changes have been safely reverted to maintain data integrity.</p>
                        <div class="error-message-text">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    </div>

                    <div class="info-banner" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left-color: #f59e0b;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span class="info-banner-text" style="color: #92400e;">Please review the error details and try again. Contact support if the issue persists.</span>
                    </div>

                    <div class="divider"></div>

                    <div class="button-group-full">
                        <a href="invoice_form.php" class="btn-custom btn-primary-custom">
                            <i class="fas fa-redo"></i> Try Again
                        </a>
                        <a href="invoice_history.php" class="btn-custom btn-warning-custom">
                            <i class="fas fa-history"></i> View History
                        </a>
                        <a href="index.php" class="btn-custom btn-secondary-custom">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </div>

                <?php endif; ?>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php

} else {
    header("Location: invoice_form.php");
    exit;
}
?>