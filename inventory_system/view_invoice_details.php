<?php
include 'secure_page_template.php'; 
include 'db_connect.php'; 

$invoice_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($invoice_id <= 0) {
    die("Invalid Invoice ID provided.");
}

$sql_header = "SELECT 
                    I.Invoice_ID, 
                    I.Date, 
                    I.TotalAmount, 
                    C.Name AS CustomerName,
                    C.Phone,
                    C.Email,
                    C.Address 
               FROM 
                    INVOICE I
               JOIN 
                    CUSTOMER C ON I.customer_ID = C.customer_ID
               WHERE 
                    I.Invoice_ID = $invoice_id";
                    
$header_result = $conn->query($sql_header);

if (!$header_result || $header_result->num_rows == 0) {
    die("Invoice ID $invoice_id not found.");
}
$invoice_header = $header_result->fetch_assoc();

$sql_details = "SELECT 
                    ID.Quantity, 
                    ID.Rate, 
                    ID.Subtotal, 
                    P.Name AS ProductName, 
                    P.Unit 
                FROM 
                    INVOICE_DETAILS ID
                JOIN 
                    PRODUCTS P ON ID.Product_ID = P.Product_ID
                WHERE 
                    ID.Invoice_ID = $invoice_id";

$details_result = $conn->query($sql_details);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= $invoice_id ?> Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --success: #10b981;
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

        .invoice-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .invoice-card {
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

        .invoice-header {
            background: linear-gradient(135deg, var(--primary), #4338ca);
            color: white;
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .invoice-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .invoice-title {
            display: flex;
            justify-content: space-between;
            align-items: start;
            position: relative;
            z-index: 1;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .invoice-title h1 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }

        .invoice-number {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 1.25rem;
            backdrop-filter: blur(10px);
        }

        .invoice-body {
            padding: 2.5rem;
        }

        .info-section {
            background: #f8fafc;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            align-items: start;
            gap: 1rem;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(79, 70, 229, 0.05));
            color: var(--primary);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 0.875rem;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 1rem;
            color: #1e293b;
            font-weight: 600;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title i {
            color: var(--success);
        }

        .table-modern {
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .table-modern thead {
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        }

        .table-modern thead th {
            font-weight: 700;
            color: #475569;
            border: none;
            padding: 1rem 1.5rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-modern tbody td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            border-color: #f1f5f9;
            color: #334155;
        }

        .product-name {
            font-weight: 600;
            color: #1e293b;
        }

        .table-modern tfoot {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
        }

        .table-modern tfoot td {
            font-weight: 700;
            font-size: 1.5rem;
            padding: 1.5rem;
            border: none;
        }

        .grand-total {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn-enhanced {
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-outline-enhanced {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline-enhanced:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(79, 70, 229, 0.3);
        }

        .btn-warning-enhanced {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .btn-warning-enhanced:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(245, 158, 11, 0.3);
            color: white;
        }

        .btn-print {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
        }

        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(16, 185, 129, 0.3);
            color: white;
        }

        @media print {
            .navbar, .action-buttons, .no-print {
                display: none !important;
            }
            
            body {
                background: white;
            }
            
            .invoice-card {
                box-shadow: none;
            }
        }

        @media (max-width: 768px) {
            .invoice-title {
                flex-direction: column;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .table-modern {
                font-size: 0.875rem;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark no-print">
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

    <div class="invoice-container">
        <div class="invoice-card">
            
            <div class="invoice-header">
                <div class="invoice-title">
                    <div>
                        <h1><i class="fas fa-file-invoice me-3"></i>Invoice Details</h1>
                        <p style="opacity: 0.9; margin-top: 0.5rem;">Complete transaction information</p>
                    </div>
                    <div class="invoice-number">
                        #<?= $invoice_header['Invoice_ID'] ?>
                    </div>
                </div>
            </div>

            <div class="invoice-body">
                
                <div class="info-section">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Customer Name</div>
                                <div class="info-value"><?= htmlspecialchars($invoice_header['CustomerName']) ?></div>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Invoice Date</div>
                                <div class="info-value"><?= date('d M, Y', strtotime($invoice_header['Date'])) ?></div>
                            </div>
                        </div>

                        <?php if ($invoice_header['Phone']): ?>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Phone</div>
                                <div class="info-value"><?= htmlspecialchars($invoice_header['Phone']) ?></div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($invoice_header['Email']): ?>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?= htmlspecialchars($invoice_header['Email']) ?></div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($invoice_header['Address']): ?>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Address</div>
                                <div class="info-value"><?= nl2br(htmlspecialchars($invoice_header['Address'])) ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="section-title">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Products Sold</span>
                </div>

                <?php if ($details_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Unit</th>
                                    <th>Quantity</th>
                                    <th>Rate</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($item = $details_result->fetch_assoc()): ?>
                                <tr>
                                    <td class="product-name"><?= htmlspecialchars($item['ProductName']) ?></td>
                                    <td><?= htmlspecialchars($item['Unit']) ?></td>
                                    <td><strong><?= htmlspecialchars($item['Quantity']) ?></strong></td>
                                    <td>₹<?= number_format($item['Rate'], 2) ?></td>
                                    <td>₹<?= number_format($item['Subtotal'], 2) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end">
                                        <i class="fas fa-calculator me-2"></i>Grand Total:
                                    </td>
                                    <td class="grand-total">
                                        ₹<?= number_format($invoice_header['TotalAmount'], 2) ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No line items found for this invoice.
                    </div>
                <?php endif; ?>
                
                <div class="action-buttons no-print">
                    <button onclick="window.print()" class="btn btn-print btn-enhanced">
                        <i class="fas fa-print"></i>
                        <span>Print Invoice</span>
                    </button>
                    <a href="invoice_history.php" class="btn btn-warning-enhanced btn-enhanced">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to History</span>
                    </a>
                    <a href="index.php" class="btn btn-outline-enhanced btn-enhanced">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>