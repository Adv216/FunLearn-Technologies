<?php
include 'secure_page_template.php'; 
include 'db_connect.php'; 

// === PERMISSION CHECK: ADMIN ONLY for OCR Upload ===
if (!check_permission([ROLE_ADMIN])) {
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
    <title>AI Invoice OCR Upload</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --ai-color: #4f46e5; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; font-family: 'Inter', sans-serif; }
        .container-main { max-width: 700px; margin: 5rem auto; padding: 0 1rem; }
        .card-custom { background: white; border-radius: 1.5rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); padding: 3rem; }
        .card-custom h1 { color: var(--ai-color); font-weight: 700; margin-bottom: 0.5rem; }
        .file-upload-box { border: 2px dashed #ccc; padding: 2rem; border-radius: 1rem; text-align: center; cursor: pointer; transition: border-color 0.3s; }
        .file-upload-box:hover { border-color: var(--ai-color); }
        .btn-ai-process { background-color: var(--ai-color); color: white; }
    </style>
</head>
<body>
    <div class="container-main">
        <div class="card-custom">
            <h1><i class="fas fa-robot me-2"></i>AI Invoice OCR</h1>
            <p class="text-muted mb-4">Upload a supplier invoice/bill image (JPG, PNG, PDF) to automatically extract data using Ollama.</p>

            <form action="process_ocr.php" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="invoice_file" class="form-label">Select Invoice File</label>
                    <input class="form-control" type="file" id="invoice_file" name="invoice_file" accept=".jpg, .jpeg, .png, .pdf" required>
                </div>
                
                <button type="submit" class="btn btn-lg btn-ai-process w-100">
                    <i class="fas fa-brain me-2"></i> Process with Ollama
                </button>
            </form>

            <div class="alert alert-warning mt-4">
                <i class="fas fa-exclamation-triangle me-2"></i>Note: This feature requires external Python services (Ollama/Tesseract) to be running on your system for full functionality.
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>