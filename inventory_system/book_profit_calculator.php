<?php
include 'secure_page_template.php';
include 'db_connect.php';

check_permission([ROLE_ADMIN, ROLE_MANAGER]);

$products = $conn->query("SELECT Product_ID, Product_Name FROM FINISHED_PRODUCTS");
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $material = floatval($_POST['material']);
    $labour   = floatval($_POST['labour']);
    $extra    = floatval($_POST['extra']);
    $profit   = floatval($_POST['profit']);

    $total_cost = $material + $labour + $extra;
    $selling_price = $total_cost + ($total_cost * $profit / 100);

    $result = [
        'cost' => $total_cost,
        'price' => $selling_price,
        'profit' => $selling_price - $total_cost
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Book Profit Calculator</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --primary: #10b981;
        --primary-dark: #059669;
        --secondary: #6366f1;
        --gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --gradient-alt: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    body {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .main-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }
    
    .header-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        margin-bottom: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .page-title {
        font-size: 2rem;
        font-weight: 700;
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .calculator-form {
        background: white;
        border-radius: 20px;
        padding: 2.5rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        margin-bottom: 2rem;
    }
    
    .form-section-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 3px solid var(--primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .input-group-custom {
        margin-bottom: 1.5rem;
    }
    
    .input-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .input-icon {
        color: var(--primary);
        font-size: 1rem;
    }
    
    .form-control {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        outline: none;
    }
    
    .input-group-text {
        background: var(--primary);
        color: white;
        border: 2px solid var(--primary);
        font-weight: 600;
        border-radius: 12px 0 0 12px;
    }
    
    .input-with-icon .form-control {
        border-radius: 0 12px 12px 0;
    }
    
    .btn-calculate {
        background: var(--gradient);
        border: none;
        border-radius: 12px;
        padding: 1rem 2rem;
        font-weight: 700;
        font-size: 1.1rem;
        color: white;
        width: 100%;
        transition: all 0.3s ease;
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
        margin-top: 1rem;
    }
    
    .btn-calculate:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 40px rgba(16, 185, 129, 0.4);
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
    }
    
    .result-card {
        background: white;
        border-radius: 20px;
        padding: 2.5rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        margin-bottom: 2rem;
        animation: slideIn 0.5s ease;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .result-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .result-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }
    
    .result-item {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border-radius: 16px;
        padding: 1.5rem;
        border: 2px solid #bbf7d0;
        transition: all 0.3s ease;
    }
    
    .result-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.2);
    }
    
    .result-item.highlight {
        background: var(--gradient);
        border-color: var(--primary-dark);
    }
    
    .result-item.highlight .result-label,
    .result-item.highlight .result-value {
        color: white;
    }
    
    .result-label {
        font-size: 0.9rem;
        font-weight: 600;
        color: #065f46;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .result-value {
        font-size: 2rem;
        font-weight: 800;
        color: #047857;
        margin: 0;
    }
    
    .btn-back {
        background: white;
        color: var(--primary);
        border: 2px solid white;
        border-radius: 12px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-back:hover {
        background: rgba(255, 255, 255, 0.9);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        color: var(--primary-dark);
    }
    
    .cost-breakdown {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
        .page-title {
            font-size: 1.5rem;
        }
        
        .result-value {
            font-size: 1.5rem;
        }
    }
</style>
</head>
<body>

<div class="main-container">
    <div class="header-card">
        <h1 class="page-title">
            <i class="fas fa-chart-line"></i>
            Book Profit Calculator
        </h1>
    </div>

    <form method="POST" class="calculator-form">
        <h3 class="form-section-title">
            <i class="fas fa-money-bill-wave"></i>
            Cost Components
        </h3>
        
        <div class="cost-breakdown">
            <div class="input-group-custom">
                <label class="input-label">
                    <i class="fas fa-box input-icon"></i>
                    Material Cost
                </label>
                <div class="input-group input-with-icon">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" name="material" class="form-control" required value="<?= $_POST['material'] ?? '' ?>">
                </div>
            </div>

            <div class="input-group-custom">
                <label class="input-label">
                    <i class="fas fa-users input-icon"></i>
                    Labour Cost
                </label>
                <div class="input-group input-with-icon">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" name="labour" class="form-control" required value="<?= $_POST['labour'] ?? '' ?>">
                </div>
            </div>

            <div class="input-group-custom">
                <label class="input-label">
                    <i class="fas fa-plus-circle input-icon"></i>
                    Extra Cost
                </label>
                <div class="input-group input-with-icon">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" name="extra" class="form-control" required value="<?= $_POST['extra'] ?? '' ?>">
                </div>
            </div>

            <div class="input-group-custom">
                <label class="input-label">
                    <i class="fas fa-percentage input-icon"></i>
                    Profit Margin
                </label>
                <div class="input-group input-with-icon">
                    <span class="input-group-text">%</span>
                    <input type="number" step="0.1" name="profit" class="form-control" required value="<?= $_POST['profit'] ?? '' ?>">
                </div>
            </div>
        </div>

        <button type="submit" class="btn-calculate">
            <i class="fas fa-calculator"></i> Calculate Selling Price
        </button>
    </form>

    <?php if ($result): ?>
    <div class="result-card">
        <h3 class="result-title">
            <i class="fas fa-chart-pie"></i>
            Calculation Results
        </h3>
        
        <div class="result-grid">
            <div class="result-item">
                <div class="result-label">
                    <i class="fas fa-coins"></i> Total Cost
                </div>
                <div class="result-value">₹<?= number_format($result['cost'], 2) ?></div>
            </div>
            
            <div class="result-item highlight">
                <div class="result-label">
                    <i class="fas fa-tag"></i> Selling Price
                </div>
                <div class="result-value">₹<?= number_format($result['price'], 2) ?></div>
            </div>
            
            <div class="result-item">
                <div class="result-label">
                    <i class="fas fa-hand-holding-usd"></i> Profit Per Book
                </div>
                <div class="result-value">₹<?= number_format($result['profit'], 2) ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="text-center">
        <a href="index.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

</body>
</html>