<?php
include 'secure_page_template.php'; 
include 'db_connect.php'; 

// === PERMISSION CHECK ===
if (!check_permission([ROLE_ADMIN, ROLE_MANAGER])) {
    header("Location: index.php?error=permission");
    exit;
}
// ==========================

// --- Utility Function: Find Product ID by Name or Extracted ID ---
function get_product_id_by_name_or_id($conn, $product_string) {
    // Escape string for security
    $safe_string = $conn->real_escape_string(trim($product_string));
    
    // Attempt a direct match on Name, Product_ID, or HsnCode (case-insensitive)
    $sql = "SELECT Product_ID FROM PRODUCTS 
            WHERE Name = '$safe_string' 
            OR Product_ID = '$safe_string' 
            OR HsnCode = '$safe_string'
            LIMIT 1";
            
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        return (int)$result->fetch_assoc()['Product_ID'];
    }

    // Attempt a fuzzy match (e.g., using LIKE for partial match on Name)
    $sql_fuzzy = "SELECT Product_ID FROM PRODUCTS 
                  WHERE Name LIKE '%$safe_string%'
                  LIMIT 1";
    $result_fuzzy = $conn->query($sql_fuzzy);

    if ($result_fuzzy && $result_fuzzy->num_rows > 0) {
        return (int)$result_fuzzy->fetch_assoc()['Product_ID'];
    }

    return 0; // Return 0 if no product is found
}
// --- End Utility Function ---


if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: purchase_form.php");
    exit;
}

// Extract and sanitize header data
$supplier_id    = $conn->real_escape_string($_POST['supplier_id']);
$purchase_date  = $conn->real_escape_string($_POST['purchase_date']);
$total_amount   = (float)$_POST['total_amount'];
$products       = $_POST['products'];

// Transaction flags
$success = true;
$purchase_id = null;
$error_message = '';
$item_count = 0;

// --- START: DATABASE TRANSACTION ---
$conn->begin_transaction();

try {
    // 1. Insert into PURCHASE table (Header)
    $sql_purchase = "INSERT INTO PURCHASE (Supplier_ID, Date, TotalAmount) 
                     VALUES ('$supplier_id', '$purchase_date', '$total_amount')";
    
    if (!$conn->query($sql_purchase)) {
        throw new Exception("Error inserting Purchase header: " . $conn->error);
    }

    $purchase_id = $conn->insert_id; 

    // 2. Loop through products to insert details and update stock
    foreach ($products as $item) {
        $quantity   = (int)$item['quantity'];
        $rate       = (float)$item['rate'];
        $subtotal   = (float)$item['subtotal'];
        
        // Determine Product ID (Handles both regular form and OCR form submissions)
        $product_id = 0;
        
        if (isset($item['product_id'])) {
            // Case A: Standard Form Submission (Product ID is already known)
            $product_id = (int)$item['product_id'];
        } elseif (isset($item['product_id_name'])) {
            // Case B: OCR Review Submission (We need to look up the ID)
            $product_name_string = $item['product_id_name'];
            $product_id = get_product_id_by_name_or_id($conn, $product_name_string);
            
            // Critical Check: If OCR product string couldn't be matched
            if ($product_id === 0) {
                throw new Exception("Product match failed for item: '$product_name_string'. Item must be manually added to inventory or mapped.");
            }
        }
        
        if ($product_id <= 0 || $quantity <= 0) continue;
        $item_count++;
        
        // a. Insert into PURCHASE_DETAILS table
        $sql_detail = "INSERT INTO PURCHASE_DETAILS (Purchase_ID, Product_ID, Quantity, Rate, Subtotal) 
                       VALUES ('$purchase_id', '$product_id', '$quantity', '$rate', '$subtotal')";
        
        if (!$conn->query($sql_detail)) {
            throw new Exception("Error inserting Purchase detail: " . $conn->error);
        }

        // b. Update PRODUCTS table (INCREASE stock)
        $sql_stock_update = "UPDATE PRODUCTS 
                             SET Stock_Quantity = Stock_Quantity + $quantity 
                             WHERE Product_ID = '$product_id'";
        
        if (!$conn->query($sql_stock_update)) {
            throw new Exception("Error updating product stock: " . $conn->error);
        }
    }

    // 3. Commit the transaction if all steps succeeded
    $conn->commit();

} catch (Exception $e) {
    // 4. Rollback the transaction if any step failed
    $conn->rollback();
    $success = false;
    $error_message = $e->getMessage();
}
// --- END: DATABASE TRANSACTION ---

// Fetch Supplier name for the success/error page
$supplier_name = "Unknown Supplier";
$supplier_q = $conn->query("SELECT Name FROM SUPPLIER WHERE Supplier_ID = '$supplier_id'");
if ($supplier_q && $supplier_q->num_rows > 0) {
    $supplier_name = $supplier_q->fetch_assoc()['Name'];
}

// Display result
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reusing style from add_customer.php/add_supplier.php for consistency */
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .result-card {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .result-header {
            padding: 2.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .result-header.success {
            /* Using Primary/Purple for Purchase Success */
            background: linear-gradient(135deg, var(--primary), #4338ca);
        }

        .result-header.error {
            background: linear-gradient(135deg, var(--danger), #dc2626);
        }

        .result-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .result-icon i {
            font-size: 2.5rem;
            color: white;
        }

        .result-header h1 {
            color: white;
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
        }

        .result-body {
            padding: 2.5rem;
        }

        .info-item {
            background: #f0faff; /* Light Blue/Primary tint for info */
            border-radius: 0.75rem;
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary);
        }

        .info-label {
            font-size: 0.875rem;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 1.1rem;
            color: #1e293b;
            font-weight: 600;
        }

        .error-message {
            background: #fee2e2;
            border: 2px solid #fecaca;
            border-radius: 0.75rem;
            padding: 1rem;
            color: #991b1b;
            margin-bottom: 1.5rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-enhanced {
            padding: 0.875rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary-enhanced {
            background: linear-gradient(135deg, var(--primary), #4338ca);
            color: white;
        }

        .btn-primary-enhanced:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(79, 70, 229, 0.3);
            color: white;
        }

        .btn-secondary-enhanced {
            background: white;
            color: #64748b;
            border: 2px solid #e2e8f0;
        }

        .btn-secondary-enhanced:hover {
            background: #f8fafc;
            color: #475569;
        }
    </style>
</head>
<body>

<div class="result-card">
    <?php if ($success): ?>
    <div class="result-header success">
        <div class="result-icon">
            <i class="fas fa-check"></i>
        </div>
        <h1>Purchase Recorded Successfully!</h1>
    </div>
    <div class="result-body">
        <div class="info-item">
            <div class="info-label">Purchase ID</div>
            <div class="info-value">#<?= htmlspecialchars($purchase_id) ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Supplier</div>
            <div class="info-value"><?= htmlspecialchars($supplier_name) ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Total Amount</div>
            <div class="info-value">₹<?= number_format($total_amount, 2) ?></div>
        </div>
        
        <p style="color: #64748b; margin-top: 1.5rem; text-align: center;">
            <i class="fas fa-check-circle me-2"></i>
            Inventory has been updated with **<?= $item_count ?>** line item(s).
        </p>

        <div class="action-buttons">
            <a href="purchase_form.php" class="btn btn-primary-enhanced btn-enhanced">
                <i class="fas fa-plus"></i>
                <span>Record Another</span>
            </a>
            <a href="index.php" class="btn btn-secondary-enhanced btn-enhanced">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="result-header error">
        <div class="result-icon">
            <i class="fas fa-times"></i>
        </div>
        <h1>Error Recording Purchase</h1>
    </div>
    <div class="result-body">
        <div class="error-message">
            <strong><i class="fas fa-exclamation-triangle me-2"></i>Transaction Error:</strong> <?= htmlspecialchars($error_message) ?>
        </div>
        
        <p style="color: #64748b; text-align: center;">
            The transaction was **rolled back**. No changes were saved to the database.
        </p>

        <div class="action-buttons">
            <a href="purchase_form.php" class="btn btn-primary-enhanced btn-enhanced">
                <i class="fas fa-arrow-left"></i>
                <span>Try Again</span>
            </a>
            <a href="index.php" class="btn btn-secondary-enhanced btn-enhanced">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>