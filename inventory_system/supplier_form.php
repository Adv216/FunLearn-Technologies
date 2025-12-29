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
    <title>Inventory System - Add Supplier</title>
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

        .page-wrapper {
            display: flex;
            min-height: calc(100vh - 70px);
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .form-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.25);
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

        .form-header {
            margin-bottom: 40px;
            animation: fadeIn 0.6s ease-out 0.2s both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .form-icon {
            font-size: 3rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 15px;
        }

        .form-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 10px;
        }

        .form-subtitle {
            font-size: 0.95rem;
            color: #6b7280;
        }

        .form-group-wrapper {
            margin-bottom: 25px;
            animation: fadeIn 0.6s ease-out 0.3s both;
        }

        .form-group-wrapper:nth-child(2) {
            animation-delay: 0.4s;
        }

        .form-group-wrapper:nth-child(3) {
            animation-delay: 0.5s;
        }

        .form-group-wrapper:nth-child(4) {
            animation-delay: 0.6s;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
        }

        .form-label-icon {
            font-size: 1.1rem;
            color: #667eea;
        }

        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .form-control:focus {
            background: white;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .form-control:hover {
            border-color: #667eea;
        }

        .form-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #e5e7eb, transparent);
            margin: 30px 0;
        }

        .button-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 35px;
            animation: fadeIn 0.6s ease-out 0.7s both;
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

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .btn-submit:active {
            transform: translateY(-1px);
        }

        .btn-back {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-back:hover {
            background: #667eea;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(102, 126, 234, 0.2);
            text-decoration: none;
        }

        .required-indicator {
            color: #ef4444;
            font-weight: 700;
        }

        .helper-text {
            font-size: 0.8rem;
            color: #9ca3af;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .info-box {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-left: 4px solid #3b82f6;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            animation: fadeIn 0.6s ease-out 0.25s both;
        }

        .info-box-text {
            color: #1e40af;
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            font-size: 1rem;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .form-control:focus ~ .input-icon {
            opacity: 1;
        }

        @media (max-width: 600px) {
            .form-container {
                padding: 30px 20px;
            }

            .form-title {
                font-size: 1.5rem;
            }

            .form-icon {
                font-size: 2.5rem;
            }

            .button-group {
                grid-template-columns: 1fr;
            }

            .page-wrapper {
                padding: 20px 15px;
            }
        }

        .form-control::placeholder {
            color: #d1d5db;
        }

        .input-feedback {
            display: none;
            font-size: 0.8rem;
            color: #667eea;
            margin-top: 4px;
            animation: fadeIn 0.3s ease;
        }

        .form-control:valid ~ .input-feedback {
            display: block;
            color: #10b981;
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
    
    <div class="page-wrapper">
        <div class="form-container">
            <div class="form-header">
                <div class="form-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h1 class="form-title">Add New Supplier</h1>
                <p class="form-subtitle">Expand your supplier network</p>
            </div>

            <div class="info-box">
                <div class="info-box-text">
                    <i class="fas fa-info-circle"></i>
                    <span>Fill in the supplier details to add them to your system</span>
                </div>
            </div>

            <form action="add_supplier.php" method="POST">
                
                <div class="form-group-wrapper">
                    <label for="name" class="form-label">
                        <span class="form-label-icon"><i class="fas fa-user"></i></span>
                        Supplier Name
                        <span class="required-indicator">*</span>
                    </label>
                    <div class="form-input-group">
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-control" 
                            placeholder="Enter supplier name"
                            required
                        >
                        <span class="input-icon"><i class="fas fa-check-circle"></i></span>
                    </div>
                    <div class="helper-text">
                        <i class="fas fa-lightbulb"></i>
                        <span>e.g., ABC Supplies Pvt Ltd</span>
                    </div>
                </div>

                <div class="form-group-wrapper">
                    <label for="phone" class="form-label">
                        <span class="form-label-icon"><i class="fas fa-phone"></i></span>
                        Phone Number
                    </label>
                    <div class="form-input-group">
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            class="form-control"
                            placeholder="Enter phone number"
                        >
                        <span class="input-icon"><i class="fas fa-check-circle"></i></span>
                    </div>
                    <div class="helper-text">
                        <i class="fas fa-lightbulb"></i>
                        <span>e.g., +91-9876543210</span>
                    </div>
                </div>

                <div class="form-group-wrapper">
                    <label for="email" class="form-label">
                        <span class="form-label-icon"><i class="fas fa-envelope"></i></span>
                        Email Address
                    </label>
                    <div class="form-input-group">
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control"
                            placeholder="Enter email address"
                        >
                        <span class="input-icon"><i class="fas fa-check-circle"></i></span>
                    </div>
                    <div class="helper-text">
                        <i class="fas fa-lightbulb"></i>
                        <span>e.g., contact@supplier.com</span>
                    </div>
                </div>

                <div class="form-divider"></div>

                <div class="button-group">
                    <button type="submit" class="btn-custom btn-submit">
                        <i class="fas fa-save"></i> Save Supplier
                    </button>
                    <a href="index.php" class="btn-custom btn-back">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation feedback
        const form = document.querySelector('form');
        const inputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"]');

        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                }
            });

            input.addEventListener('blur', function() {
                if (!this.value.trim() && this.hasAttribute('required')) {
                    this.classList.add('is-invalid');
                }
            });

            input.addEventListener('focus', function() {
                this.classList.remove('is-invalid');
            });
        });

        // Add smooth form submission
        form.addEventListener('submit', function(e) {
            let isValid = true;
            inputs.forEach(input => {
                if (input.hasAttribute('required') && !input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                console.log('Form validation failed');
            }
        });
    </script>
</body>
</html>