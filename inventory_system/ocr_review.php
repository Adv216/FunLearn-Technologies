<?php
include 'secure_page_template.php'; 
include 'db_connect.php'; 

// === PERMISSION CHECK: ADMIN ONLY ===
if (!check_permission([ROLE_ADMIN])) {
    header("Location: index.php?error=permission");
    exit;
}
// ==========================

// Decode data passed from process_ocr.php
$encoded_data = $_GET['data'] ?? '';
$extracted_data = json_decode(urldecode($encoded_data), true);

if (!$extracted_data) {
    die("Error: No valid data received for review.");
}

// Fetch Suppliers for dropdown
$supplier_result = $conn->query("SELECT Supplier_ID, Name FROM SUPPLIER ORDER BY Name ASC");

$grand_total = $extracted_data['grand_total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Extracted Purchase Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --ai-color: #4f46e5; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; font-family: 'Inter', sans-serif; }
        .container-main { max-width: 1000px; margin: 2rem auto; }
        .card-custom { background: white; border-radius: 1.5rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); padding: 3rem; }
        .card-custom h1 { color: var(--ai-color); font-weight: 700; margin-bottom: 0.5rem; }
        .kpi-value { font-size: 1.5rem; font-weight: 700; color: #10b981; }
        .table-extracted th { background: #f1f5f9; }
    </style>
</head>
<body>
    <div class="container-main">
        <div class="card-custom">
            <h1><i class="fas fa-check-circle me-2"></i> Review AI Extracted Data</h1>
            <p class="text-muted mb-4">Verify the data extracted by the LLM from the uploaded invoice before finalizing the purchase record.</p>

            <form action="process_purchase.php" method="POST">
                <input type="hidden" name="action" value="finalize_ocr">
                <input type="hidden" name="total_amount" value="<?= $grand_total ?>">

                <div class="row mb-4 bg-light p-3 rounded">
                    <div class="col-md-6 mb-3">
                        <label for="supplier_id" class="form-label"><i class="fas fa-truck me-2"></i>Match Supplier</label>
                        <select name="supplier_id" id="supplier_id" class="form-select" required>
                            <option value="">-- Select Supplier --</option>
                            <?php while ($row = $supplier_result->fetch_assoc()): ?>
                                <option value="<?= $row['Supplier_ID'] ?>" 
                                    <?= (stripos($row['Name'], $extracted_data['supplier_name']) !== false || stripos($extracted_data['supplier_name'], $row['Name']) !== false) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['Name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <small class="text-muted">LLM Extracted: <strong><?= htmlspecialchars($extracted_data['supplier_name']) ?></strong></small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="purchase_date" class="form-label"><i class="fas fa-calendar-alt me-2"></i>Invoice Date</label>
                        <input type="date" name="purchase_date" class="form-control" value="<?= htmlspecialchars($extracted_data['invoice_date']) ?>" required>
                    </div>
                    <div class="col-md-3 mb-3 text-end">
                        <label class="form-label text-muted">Grand Total (LLM)</label>
                        <div class="kpi-value">₹<?= number_format($grand_total, 2) ?></div>
                    </div>
                </div>

                <h4 class="mt-4 mb-3">Extracted Line Items:</h4>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped table-extracted">
                        <thead>
                            <tr>
                                <th>Product ID (Extracted)</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Rate (Cost)</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($extracted_data['items'] as $index => $item): ?>
                            <tr>
                                <td>
                                    <input type="text" name="products[<?= $index ?>][product_id_name]" class="form-control form-control-sm" value="<?= htmlspecialchars($item['product_id']) ?>" required>
                                </td>
                                <td>
                                    <input type="number" name="products[<?= $index ?>][quantity]" class="form-control form-control-sm text-center" value="<?= $item['quantity'] ?>" min="1" required>
                                </td>
                                <td>
                                    <input type="number" name="products[<?= $index ?>][rate]" class="form-control form-control-sm text-end" value="<?= number_format($item['rate'], 2, '.', '') ?>" step="0.01" min="0" required>
                                </td>
                                <td class="text-end">
                                    ₹<?= number_format($item['quantity'] * $item['rate'], 2) ?>
                                    <input type="hidden" name="products[<?= $index ?>][subtotal]" value="<?= number_format($item['quantity'] * $item['rate'], 2, '.', '') ?>">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save me-2"></i> Confirm & Create Purchase Record
                    </button>
                    <a href="ocr_upload_form.php" class="btn btn-outline-secondary">
                        <i class="fas fa-upload me-2"></i> Cancel & Upload New File
                    </a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>