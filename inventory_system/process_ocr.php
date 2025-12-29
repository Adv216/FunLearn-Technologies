<?php
include 'secure_page_template.php'; 
include 'db_connect.php'; 

// === PERMISSION CHECK: ADMIN ONLY ===
if (!check_permission([ROLE_ADMIN])) {
    die("Permission Denied.");
}
// ==========================

$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir);
}

$status_message = '';
$uploaded_file_path = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["invoice_file"])) {
    $file = $_FILES["invoice_file"];
    $filename = basename($file["name"]);
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $new_filename = uniqid('ocr_') . '.' . $ext;
    $uploaded_file_path = $upload_dir . $new_filename;

    if (move_uploaded_file($file["tmp_name"], $uploaded_file_path)) {
        
        // --- STEP 1: SIMULATE RAW TEXT EXTRACTION ---
        // Placeholder text snippet based on the simple bill template for consistent testing.
        $raw_invoice_text = "Supplier: BETA SUPPLIES LTD. P.O. #19034 Date: 2025-11-07
                             Items:
                             1. PROD-C11 - 25 units @ 12.00 ea Total 300.00
                             2. BATT-LIT - 10 units @ 85.50 ea Total 855.00
                             3. CASE-SM - 40 units @ 15.00 ea Total 600.00
                             Subtotal: 1755.00. Shipping: 45.00.
                             GRAND TOTAL DUE: 1800.00";

        // --- STEP 2: LLM PROMPT FOR DATA EXTRACTION ---
        $llm_prompt = "You are an expert data extraction API. Your task is to extract structured JSON data from the following raw invoice text. Only output the JSON object.";
                       
        // --- STEP 3: SIMULATE OLLAMA/AI API CALL ---
        // This JSON object contains the CORRECT data your system should process.
        // It matches the requirements of the ocr_review.php and the process_purchase.php.
        $simulated_llm_response = '{
          "supplier_name": "BETA SUPPLIES LTD.",
          "invoice_date": "2025-11-07",
          "grand_total": 1800.00,
          "items": [
            {
              "product_id": "PROD-C11",
              "quantity": 25,
              "rate": 12.00
            },
            {
              "product_id": "BATT-LIT",
              "quantity": 10,
              "rate": 85.50
            },
            {
              "product_id": "CASE-SM",
              "quantity": 40,
              "rate": 15.00
            }
          ]
        }';
        
        $extracted_data = json_decode($simulated_llm_response, true);
        
        if ($extracted_data) {
            // Success: Display Review Page
            header("Location: ocr_review.php?data=" . urlencode($simulated_llm_response));
            exit;
        } else {
            $status_message = "Error: AI extraction failed to return valid JSON. Check LLM setup.";
        }

    } else {
        $status_message = "Error uploading file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OCR Processing Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Processing OCR...</h1>
        <?php if ($status_message): ?>
            <div class="alert alert-danger"><?= $status_message ?></div>
            <a href="ocr_upload_form.php" class="btn btn-primary">Try Again</a>
        <?php endif; ?>
    </div>
</body>
</html>