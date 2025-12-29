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
    <title>Supplier Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --warning: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
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
            background: linear-gradient(135deg, var(--warning), #d97706);
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
            animation: scaleIn 0.5s ease-out 0.2s both;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
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
            background: #fef3c7;
            border-radius: 0.75rem;
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--warning);
        }

        .info-label {
            font-size: 0.875rem;
            color: #92400e;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 1.1rem;
            color: #78350f;
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

        .btn-warning-enhanced {
            background: linear-gradient(135deg, var(--warning), #d97706);
            color: white;
        }

        .btn-warning-enhanced:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(245, 158, 11, 0.3);
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

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $name    = $conn->real_escape_string($_POST['name']);
    $phone   = $conn->real_escape_string($_POST['phone']);
    $email   = $conn->real_escape_string($_POST['email']);
    
    $sql = "INSERT INTO SUPPLIER (Name, Phone, Email) 
            VALUES ('$name', '$phone', '$email')";
    
    if ($conn->query($sql) === TRUE) {
        ?>
        <div class="result-card">
            <div class="result-header success">
                <div class="result-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h1>Supplier Added Successfully!</h1>
            </div>
            <div class="result-body">
                <div class="info-item">
                    <div class="info-label">Supplier Name</div>
                    <div class="info-value"><?= htmlspecialchars($name) ?></div>
                </div>
                <?php if ($phone): ?>
                <div class="info-item">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value"><?= htmlspecialchars($phone) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($email): ?>
                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value"><?= htmlspecialchars($email) ?></div>
                </div>
                <?php endif; ?>
                
                <p style="color: #64748b; margin-top: 1.5rem; text-align: center;">
                    <i class="fas fa-check-circle me-2"></i>
                    The supplier record has been saved to the database
                </p>

                <div class="action-buttons">
                    <a href="supplier_form.php" class="btn btn-warning-enhanced btn-enhanced">
                        <i class="fas fa-plus"></i>
                        <span>Add Another</span>
                    </a>
                    <a href="index.php" class="btn btn-secondary-enhanced btn-enhanced">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
            </div>
        </div>
        <?php
    } else {
        ?>
        <div class="result-card">
            <div class="result-header error">
                <div class="result-icon">
                    <i class="fas fa-times"></i>
                </div>
                <h1>Error Adding Supplier</h1>
            </div>
            <div class="result-body">
                <div class="error-message">
                    <strong><i class="fas fa-exclamation-triangle me-2"></i>Error:</strong> <?= $conn->error ?>
                </div>
                
                <p style="color: #64748b; text-align: center;">
                    Please try again or contact support if the problem persists
                </p>

                <div class="action-buttons">
                    <a href="supplier_form.php" class="btn btn-warning-enhanced btn-enhanced">
                        <i class="fas fa-arrow-left"></i>
                        <span>Try Again</span>
                    </a>
                    <a href="index.php" class="btn btn-secondary-enhanced btn-enhanced">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    $conn->close();
} else {
    header("Location: supplier_form.php"); 
    exit;
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>