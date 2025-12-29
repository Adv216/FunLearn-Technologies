<?php
include 'secure_page_template.php';
$alerts = $_SESSION['production_alerts'] ?? [];
unset($_SESSION['production_alerts']);
$id = $_GET['id'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Order Created Successfully</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.success-container {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    max-width: 600px;
    width: 90%;
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

.success-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2.5rem;
    text-align: center;
}

.success-icon {
    width: 80px;
    height: 80px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
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

.success-icon svg {
    width: 45px;
    height: 45px;
    stroke: #667eea;
    stroke-width: 3;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.checkmark {
    animation: drawCheck 0.5s ease-out 0.5s both;
}

@keyframes drawCheck {
    from {
        stroke-dasharray: 50;
        stroke-dashoffset: 50;
    }
    to {
        stroke-dasharray: 50;
        stroke-dashoffset: 0;
    }
}

.success-header h3 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 600;
}

.success-header p {
    margin: 0.5rem 0 0;
    opacity: 0.9;
    font-size: 1rem;
}

.success-body {
    padding: 2rem;
}

.order-id {
    background: #f8f9fa;
    border-left: 4px solid #667eea;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.order-id strong {
    color: #667eea;
    font-size: 1.1rem;
}

.production-alert {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.production-alert-header {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    color: #856404;
}

.production-alert-header svg {
    width: 24px;
    height: 24px;
    margin-right: 0.5rem;
    flex-shrink: 0;
}

.production-alert-header strong {
    font-size: 1.1rem;
}

.production-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.production-list li {
    padding: 0.75rem;
    background: white;
    border-radius: 6px;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    color: #856404;
    font-weight: 500;
}

.production-list li:before {
    content: "•";
    color: #ffc107;
    font-size: 1.5rem;
    margin-right: 0.75rem;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn-custom {
    flex: 1;
    min-width: 200px;
    padding: 0.875rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 1rem;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    text-align: center;
    display: inline-block;
}

.btn-primary-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    color: white;
}

.btn-secondary-custom {
    background: white;
    color: #667eea;
    border: 2px solid #667eea;
}

.btn-secondary-custom:hover {
    background: #667eea;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.success-footer {
    background: #f8f9fa;
    padding: 1.5rem;
    text-align: center;
    color: #6c757d;
    font-size: 0.9rem;
}

@media (max-width: 576px) {
    .success-container {
        width: 95%;
        margin: 1rem;
    }
    
    .success-header {
        padding: 2rem 1.5rem;
    }
    
    .success-body {
        padding: 1.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-custom {
        width: 100%;
        min-width: auto;
    }
}
</style>
</head>
<body>

<div class="success-container">
    <div class="success-header">
        <div class="success-icon">
            <svg fill="none" viewBox="0 0 24 24">
                <polyline class="checkmark" points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <h3>Order Created Successfully!</h3>
        <p>Your order has been saved to the system</p>
    </div>
    
    <div class="success-body">
        <div class="order-id">
            <strong>Order ID:</strong> #<?= htmlspecialchars($id) ?>
        </div>
        
        <?php if(count($alerts)): ?>
        <div class="production-alert">
            <div class="production-alert-header">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <strong>Production Required</strong>
            </div>
            <ul class="production-list">
                <?php foreach($alerts as $a): ?>
                <li><?= htmlspecialchars($a) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="action-buttons">
            <a href="invoice_view.php?id=<?= htmlspecialchars($id) ?>" class="btn-custom btn-primary-custom">
                📄 View Invoice
            </a>
            <?php if(count($alerts)): ?>
            <a href="production_requirements.php" class="btn-custom btn-secondary-custom">
                🏭 Production Panel
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="success-footer">
        You can access this order anytime from your orders list
    </div>
</div>

</body>
</html>