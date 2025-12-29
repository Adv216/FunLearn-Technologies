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
    <title>Product Result</title>
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
        }

        .navbar {
            background: rgba(0, 0, 0, 0.85) !important;
            backdrop-filter: blur(10px);
            border-bottom: 2px solid #667eea;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
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

        .container-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 70px);
            padding: 40px 20px;
        }

        .result-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 50px;
            max-width: 500px;
            width: 100%;
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon {
            font-size: 4rem;
            color: #10b981;
            margin-bottom: 20px;
            animation: bounce 0.8s ease-out;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px solid #10b981;
            border-radius: 12px;
            padding: 20px;
            color: #065f46;
            font-weight: 500;
            margin-bottom: 30px;
            animation: fadeIn 0.6s ease-out 0.3s both;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 2px solid #ef4444;
            border-radius: 12px;
            padding: 20px;
            color: #7f1d1d;
            font-weight: 500;
            margin-bottom: 30px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .result-content {
            margin: 30px 0;
            animation: fadeIn 0.6s ease-out 0.5s both;
        }

        .result-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin: 15px 0;
            background: #f9fafb;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }

        .result-item:hover {
            background: #f3f4f6;
            transform: translateX(5px);
        }

        .result-icon {
            font-size: 1.5rem;
            color: #667eea;
            margin-right: 15px;
            min-width: 30px;
            text-align: center;
        }

        .result-text {
            flex: 1;
        }

        .result-label {
            font-size: 0.85rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .result-value {
            font-size: 1.1rem;
            color: #1f2937;
            font-weight: 600;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 40px;
            flex-wrap: wrap;
            animation: fadeIn 0.6s ease-out 0.7s both;
        }

        .btn-custom {
            flex: 1;
            min-width: 150px;
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

        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
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
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.2);
            text-decoration: none;
        }

        .error-section {
            text-align: center;
        }

        .error-icon {
            font-size: 4rem;
            color: #ef4444;
            margin-bottom: 20px;
        }

        .error-text {
            font-size: 0.95rem;
            color: #7f1d1d;
            word-break: break-word;
            font-family: 'Courier New', monospace;
            background: #fef2f2;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }

        @media (max-width: 600px) {
            .result-card {
                padding: 30px 20px;
            }

            .success-icon,
            .error-icon {
                font-size: 3rem;
            }

            .button-group {
                flex-direction: column;
            }

            .btn-custom {
                width: 100%;
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

    <div class="container-wrapper">
        <div class="result-card">
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action'] == 'add') {
                $name = $conn->real_escape_string($_POST['name']);
                $price = (float)$_POST['price'];
                $hsn = $conn->real_escape_string($_POST['hsn']);
                $unit = $conn->real_escape_string($_POST['unit']);
                $stock = (int)$_POST['stock'];
                
                $sql = "INSERT INTO PRODUCTS (Name, Price, HsnCode, Unit, Stock_Quantity) VALUES ('$name', $price, '$hsn', '$unit', $stock)";
                
                if ($conn->query($sql) === TRUE) {
                    echo "<div style='text-align: center;'>";
                    echo "<div class='success-icon'><i class='fas fa-check-circle'></i></div>";
                    echo "</div>";
                    echo "<div class='alert-success'><strong><i class='fas fa-check'></i> Success!</strong> Product Added Successfully</div>";
                    
                    echo "<div class='result-content'>";
                    echo "<div class='result-item'>";
                    echo "<div class='result-icon'><i class='fas fa-tag'></i></div>";
                    echo "<div class='result-text'>";
                    echo "<div class='result-label'>Product Name</div>";
                    echo "<div class='result-value'>" . htmlspecialchars($name) . "</div>";
                    echo "</div></div>";
                    
                    echo "<div class='result-item'>";
                    echo "<div class='result-icon'><i class='fas fa-rupee-sign'></i></div>";
                    echo "<div class='result-text'>";
                    echo "<div class='result-label'>Price</div>";
                    echo "<div class='result-value'>₹" . number_format($price, 2) . "</div>";
                    echo "</div></div>";
                    
                    echo "<div class='result-item'>";
                    echo "<div class='result-icon'><i class='fas fa-barcode'></i></div>";
                    echo "<div class='result-text'>";
                    echo "<div class='result-label'>HSN Code</div>";
                    echo "<div class='result-value'>" . htmlspecialchars($hsn) . "</div>";
                    echo "</div></div>";
                    
                    echo "<div class='result-item'>";
                    echo "<div class='result-icon'><i class='fas fa-cube'></i></div>";
                    echo "<div class='result-text'>";
                    echo "<div class='result-label'>Initial Stock</div>";
                    echo "<div class='result-value'>" . htmlspecialchars($stock) . " " . htmlspecialchars($unit) . "</div>";
                    echo "</div></div>";
                    echo "</div>";
                    
                } else {
                    echo "<div style='text-align: center;'>";
                    echo "<div class='error-icon'><i class='fas fa-times-circle'></i></div>";
                    echo "</div>";
                    echo "<div class='alert-danger'><strong><i class='fas fa-exclamation-triangle'></i> Error!</strong> Failed to Add Product</div>";
                    echo "<div class='error-section'>";
                    echo "<div class='error-text'>" . htmlspecialchars($conn->error) . "</div>";
                    echo "</div>";
                }
                
                $conn->close();
            } else {
                header("Location: product_form.php");
                exit;
            }
            ?>

            <div class="button-group">
                <a href="product_form.php" class="btn-custom btn-primary-custom">
                    <i class="fas fa-plus-circle"></i> Add Another
                </a>
                <a href="index.php" class="btn-custom btn-secondary-custom">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>