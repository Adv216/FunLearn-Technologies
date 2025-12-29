<?php
include 'secure_page_template.php';
include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FunLearn Business Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
:root {
    --primary: #6366f1;
    --primary-dark: #4f46e5;
    --secondary: #ec4899;
    --success: #10b981;
    --warning: #f59e0b;
    --info: #06b6d4;
    --danger: #ef4444;
    --dark: #0f172a;
    --light: #f8fafc;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    font-family: 'Inter', 'Segoe UI', sans-serif;
    position: relative;
    overflow-x: hidden;
}

body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3), transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(99, 102, 241, 0.3), transparent 50%);
    pointer-events: none;
    z-index: 0;
}

.content-wrapper {
    position: relative;
    z-index: 1;
}

/* NAVBAR */
.navbar {
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(20px);
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.navbar-brand {
    font-size: 1.8rem;
    font-weight: 900;
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display: flex;
    align-items: center;
    gap: 10px;
}

.navbar-brand i {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.user-info {
    background: rgba(255, 255, 255, 0.1);
    padding: 8px 20px;
    border-radius: 25px;
    color: white;
    font-weight: 600;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
}

.user-info:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
}

.btn-logout {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    border: none;
    padding: 8px 24px;
    border-radius: 25px;
    font-weight: 700;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
}

.btn-logout:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(239, 68, 68, 0.4);
}

/* HEADER */
.dashboard-header {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.9));
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 40px;
    margin: 40px 0 30px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.5);
    animation: fadeInDown 0.6s ease-out;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dashboard-header h2 {
    font-weight: 900;
    font-size: 2.5rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
}

.dashboard-header p {
    color: #64748b;
    font-size: 1.1rem;
    font-weight: 500;
    margin: 0;
}

/* SECTIONS */
.section-title {
    font-weight: 900;
    font-size: 1.6rem;
    color: white;
    margin: 40px 0 20px;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    gap: 12px;
    animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.section-title::before {
    content: '';
    width: 6px;
    height: 30px;
    background: linear-gradient(180deg, #fbbf24, #f59e0b);
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(251, 191, 36, 0.5);
}

/* CARDS */
.tile {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.9));
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 32px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.5);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.6s ease-out backwards;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.tile::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--tile-color), var(--tile-color-light));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s ease;
}

.tile:hover::before {
    transform: scaleX(1);
}

.tile:hover {
    transform: translateY(-12px) scale(1.02);
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
}

.tile-icon {
    width: 70px;
    height: 70px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    position: relative;
    transition: all 0.4s ease;
}

.tile:hover .tile-icon {
    transform: rotateY(360deg);
}

.tile-icon i {
    font-size: 32px;
    color: white;
    z-index: 1;
}

.tile h5 {
    font-weight: 800;
    font-size: 1.1rem;
    margin: 15px 0 20px;
    color: #1e293b;
}

.tile .btn {
    border-radius: 12px;
    font-weight: 700;
    padding: 12px;
    border: none;
    transition: all 0.3s ease;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.tile .btn:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

/* Color variants */
.tile-primary { --tile-color: #6366f1; --tile-color-light: #818cf8; }
.tile-primary .tile-icon { background: linear-gradient(135deg, #6366f1, #818cf8); }
.tile-primary .btn-primary { background: linear-gradient(135deg, #6366f1, #818cf8); }

.tile-info { --tile-color: #06b6d4; --tile-color-light: #22d3ee; }
.tile-info .tile-icon { background: linear-gradient(135deg, #06b6d4, #22d3ee); }
.tile-info .btn-info { background: linear-gradient(135deg, #06b6d4, #22d3ee); }

.tile-warning { --tile-color: #f59e0b; --tile-color-light: #fbbf24; }
.tile-warning .tile-icon { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
.tile-warning .btn-warning { background: linear-gradient(135deg, #f59e0b, #fbbf24); }

.tile-success { --tile-color: #10b981; --tile-color-light: #34d399; }
.tile-success .tile-icon { background: linear-gradient(135deg, #10b981, #34d399); }
.tile-success .btn-success { background: linear-gradient(135deg, #10b981, #34d399); }

/* Staggered animation delays */
.tile:nth-child(1) { animation-delay: 0.1s; }
.tile:nth-child(2) { animation-delay: 0.2s; }
.tile:nth-child(3) { animation-delay: 0.3s; }
.tile:nth-child(4) { animation-delay: 0.4s; }

/* FOOTER SPACING */
.mb-space {
    margin-bottom: 80px;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .dashboard-header h2 {
        font-size: 2rem;
    }
    
    .section-title {
        font-size: 1.3rem;
    }
    
    .tile {
        margin-bottom: 20px;
    }
}

/* Smooth scroll */
html {
    scroll-behavior: smooth;
}
</style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark px-4 py-3">
    <a class="navbar-brand" href="#">
        <i class="fas fa-brain"></i>
        FunLearn
    </a>
    <div class="ms-auto d-flex align-items-center gap-3">
        <div class="user-info">
            <i class="fas fa-user-circle me-2"></i><?= $_SESSION['username'] ?>
        </div>
        <a href="logout.php" class="btn btn-logout">
            <i class="fas fa-sign-out-alt me-2"></i>Logout
        </a>
    </div>
</nav>

<div class="content-wrapper">
    <div class="container">

        <div class="dashboard-header">
            <h2>📊 Business Dashboard</h2>
            <p>Comprehensive management for Orders • Production • Inventory • Attendance • Payroll • Costing</p>
        </div>

        <h4 class="section-title">
            <i class="fas fa-shopping-cart"></i>
            Sales & Inventory
        </h4>
        <div class="row g-4">
            <?php
            $tiles = [
                ["invoice.php", "file-invoice", "primary", "New Order"],
                ["finished_products.php", "boxes", "info", "Inventory"],
                ["raw_materials.php", "industry", "warning", "Raw Materials"],
                ["production_requirements.php", "tools", "success", "Production Needs"]
            ];
            foreach($tiles as $t){
                echo "
                <div class='col-md-6 col-lg-3'>
                    <div class='tile tile-{$t[2]} text-center'>
                        <div class='tile-icon'>
                            <i class='fas fa-{$t[1]}'></i>
                        </div>
                        <h5>{$t[3]}</h5>
                        <a href='{$t[0]}' class='btn btn-{$t[2]} w-100'>
                            Open <i class='fas fa-arrow-right ms-2'></i>
                        </a>
                    </div>
                </div>";
            }
            ?>
        </div>

        <h4 class="section-title">
            <i class="fas fa-cogs"></i>
            Operations
        </h4>
        <div class="row g-4">
            <?php
            $ops = [
                ["attendance_entry.php", "user-clock", "primary", "Mark Attendance"],
                ["attendance_view.php", "list", "info", "Attendance Log"],
                ["attendance_monthly_summary.php", "calendar-alt", "warning", "Monthly Summary"],
                ["salary_report.php", "rupee-sign", "success", "Salary Report"]
            ];
            foreach($ops as $t){
                echo "
                <div class='col-md-6 col-lg-3'>
                    <div class='tile tile-{$t[2]} text-center'>
                        <div class='tile-icon'>
                            <i class='fas fa-{$t[1]}'></i>
                        </div>
                        <h5>{$t[3]}</h5>
                        <a href='{$t[0]}' class='btn btn-{$t[2]} w-100'>
                            Open <i class='fas fa-arrow-right ms-2'></i>
                        </a>
                    </div>
                </div>";
            }
            ?>
        </div>

        <h4 class="section-title">
            <i class="fas fa-chart-line"></i>
            Management & Reports
        </h4>
        <div class="row g-4 mb-space">
            <?php
            $mgmt = [
                ["employees.php", "users", "primary", "Employees"],
                ["daily_production.php", "book", "info", "Daily Production"],
                ["book_cost_calculator.php", "calculator", "warning", "Book Costing"],
                ["raw_material_report.php", "chart-bar", "success", "Reports"]
            ];
            foreach($mgmt as $t){
                echo "
                <div class='col-md-6 col-lg-3'>
                    <div class='tile tile-{$t[2]} text-center'>
                        <div class='tile-icon'>
                            <i class='fas fa-{$t[1]}'></i>
                        </div>
                        <h5>{$t[3]}</h5>
                        <a href='{$t[0]}' class='btn btn-{$t[2]} w-100'>
                            Open <i class='fas fa-arrow-right ms-2'></i>
                        </a>
                    </div>
                </div>";
            }
            ?>
        </div>

    </div>
</div>

</body>
</html>