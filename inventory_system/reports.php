<?php
include 'secure_page_template.php'; 
include 'db_connect.php'; 

// === PERMISSION CHECK: MANAGER/ADMIN ONLY ===
if (!check_permission([ROLE_ADMIN, ROLE_MANAGER])) {
    header("Location: index.php?error=permission");
    exit;
}
// ==========================

// --- 1. Total Revenue (All Sales) ---
$sql_revenue = "SELECT SUM(TotalAmount) AS TotalRevenue FROM INVOICE";
$result_revenue = $conn->query($sql_revenue)->fetch_assoc();
$total_revenue = $result_revenue['TotalRevenue'] ?? 0;

// --- 2. Total Purchase Cost (Approximate) ---
$sql_cost = "SELECT SUM(TotalAmount) AS TotalCost FROM PURCHASE";
$result_cost = $conn->query($sql_cost)->fetch_assoc();
$total_cost = $result_cost['TotalCost'] ?? 0;

// --- 3. Inventory Value (Current Stock * Selling Price) ---
$sql_inventory_value = "SELECT SUM(Stock_Quantity * Price) AS CurrentValue FROM PRODUCTS";
$result_inv_value = $conn->query($sql_inventory_value)->fetch_assoc();
$inventory_value = $result_inv_value['CurrentValue'] ?? 0;

// --- 4. Top 5 Selling Products (by Quantity) ---
$sql_top_selling = "
    SELECT 
        P.Name, 
        SUM(ID.Quantity) AS TotalQuantity, 
        SUM(ID.Subtotal) AS TotalSales
    FROM 
        INVOICE_DETAILS ID
    JOIN 
        PRODUCTS P ON ID.Product_ID = P.Product_ID
    GROUP BY 
        P.Name
    ORDER BY 
        TotalQuantity DESC
    LIMIT 5";
$result_top_selling = $conn->query($sql_top_selling);

// --- 5. Monthly Sales Trend (Last 6 Months) ---
$sql_monthly_sales = "
    SELECT
        DATE_FORMAT(Date, '%Y-%m') AS Month,
        SUM(TotalAmount) AS Sales
    FROM 
        INVOICE
    WHERE 
        Date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY
        Month
    ORDER BY
        Month ASC";
$result_monthly_sales = $conn->query($sql_monthly_sales);
$monthly_sales_data = [];
if ($result_monthly_sales) {
    while($row = $result_monthly_sales->fetch_assoc()) {
        $monthly_sales_data[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Analytics Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --success-dark: #059669;
            --danger: #ef4444;
            --danger-dark: #dc2626;
            --warning: #f59e0b;
            --warning-dark: #d97706;
            --info: #06b6d4;
            --info-dark: #0891b2;
            --dark: #1e293b;
            --gray: #64748b;
            --light-gray: #f1f5f9;
        }

        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3), transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(99, 102, 241, 0.3), transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(167, 139, 250, 0.3), transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .navbar {
            background: rgba(15, 23, 42, 0.95) !important;
            backdrop-filter: blur(20px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500;
            padding: 0.6rem 1.2rem !important;
            border-radius: 0.75rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .nav-link:hover::before {
            left: 100%;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .report-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1.5rem;
            position: relative;
            z-index: 1;
        }

        .report-header {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 2rem;
            padding: 3rem;
            margin-bottom: 3rem;
            box-shadow: 
                0 20px 60px -15px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideDown 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .report-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.1), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .report-header h1 {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--info));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0 0 1rem 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .report-header p {
            font-size: 1.1rem;
            color: var(--gray);
            margin: 0;
        }

        /* Enhanced KPI Cards */
        .kpi-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 
                0 10px 30px -10px rgba(0, 0, 0, 0.2),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) backwards;
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, transparent, currentColor, transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .kpi-card:hover::before {
            opacity: 1;
        }

        .kpi-card:hover {
            transform: translateY(-8px);
            box-shadow: 
                0 20px 50px -10px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.2);
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

        .col-md-3:nth-child(1) .kpi-card { animation-delay: 0.1s; }
        .col-md-3:nth-child(2) .kpi-card { animation-delay: 0.2s; }
        .col-md-3:nth-child(3) .kpi-card { animation-delay: 0.3s; }
        .col-md-3:nth-child(4) .kpi-card { animation-delay: 0.4s; }

        .kpi-icon {
            width: 60px;
            height: 60px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }

        .kpi-card:hover .kpi-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .kpi-card-header {
            font-weight: 600;
            color: var(--gray);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .kpi-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .kpi-change {
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-dark);
        }

        .kpi-card.revenue { color: var(--success); }
        .kpi-card.revenue .kpi-icon {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0.1));
            color: var(--success);
        }
        .kpi-card.revenue .kpi-value { color: var(--success); }

        .kpi-card.cost { color: var(--danger); }
        .kpi-card.cost .kpi-icon {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.1));
            color: var(--danger);
        }
        .kpi-card.cost .kpi-value { color: var(--danger); }

        .kpi-card.value { color: var(--primary); }
        .kpi-card.value .kpi-icon {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(99, 102, 241, 0.1));
            color: var(--primary);
        }
        .kpi-card.value .kpi-value { color: var(--primary); }

        .kpi-card.profit { color: var(--warning); }
        .kpi-card.profit .kpi-icon {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0.1));
            color: var(--warning);
        }
        .kpi-card.profit .kpi-value {
            background: linear-gradient(135deg, var(--warning), var(--warning-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Enhanced Cards */
        .top-selling-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 1.5rem;
            box-shadow: 
                0 20px 50px -15px rgba(0, 0, 0, 0.2),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            height: 100%;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: transform 0.3s ease;
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.5s backwards;
        }

        .top-selling-card:hover {
            transform: translateY(-5px);
        }

        .top-selling-card h3 {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            padding: 1.5rem 2rem;
            margin: 0;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .top-selling-table {
            margin: 0;
        }

        .top-selling-table td {
            padding: 1.25rem 2rem;
            font-weight: 600;
            border-color: #f1f5f9;
            transition: all 0.3s ease;
        }
        
        .top-selling-table tr {
            transition: all 0.3s ease;
        }

        .top-selling-table tr:hover {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.05), transparent);
            transform: scale(1.01);
        }

        .table-badge {
            padding: 0.5rem 1rem;
            border-radius: 0.75rem;
            font-weight: 700;
            display: inline-block;
            font-size: 0.9rem;
            transition: transform 0.2s ease;
        }

        .table-badge:hover {
            transform: scale(1.05);
        }

        .rank-badge {
            background: linear-gradient(135deg, var(--info), var(--info-dark));
            color: white;
            box-shadow: 0 4px 10px rgba(6, 182, 212, 0.3);
        }
        
        .qty-badge {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: var(--info-dark);
        }

        .sales-badge {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: var(--success-dark);
        }

        /* Forecast Button */
        .btn-forecast {
            background: linear-gradient(135deg, var(--info), var(--primary));
            border: none;
            color: white;
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 700;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-forecast::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }

        .btn-forecast:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-forecast:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(99, 102, 241, 0.4);
        }

        .navbar-text strong {
            color: #fff;
            font-weight: 600;
        }

        /* Chart Container */
        .chart-wrapper {
            height: 320px;
            margin-top: 1.5rem;
            padding: 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .report-header h1 {
                font-size: 2rem;
            }
            
            .kpi-value {
                font-size: 2rem;
            }

            .chart-wrapper {
                height: 250px;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-chart-line me-2"></i>Billing System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="invoice_form.php">
                            <i class="fas fa-file-invoice me-2"></i>New Invoice
                        </a>
                    </li>
                    <?php if (check_permission([ROLE_ADMIN, ROLE_MANAGER])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="purchase_form.php">
                            <i class="fas fa-truck-loading me-2"></i>New Purchase
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar me-2"></i>Reports
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="view_inventory.php">
                            <i class="fas fa-boxes me-2"></i>Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="invoice_history.php">
                            <i class="fas fa-history me-2"></i>History
                        </a>
                    </li>
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

    <div class="report-container">
        
        <div class="report-header">
            <h1>
                <i class="fas fa-chart-line"></i>
                <span>Business Analytics</span>
            </h1>
            <p>Real-time insights into your business performance and key metrics</p>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="kpi-card revenue">
                    <div class="kpi-icon">
                        <i class="fas fa-arrow-trend-up"></i>
                    </div>
                    <div class="kpi-card-header">Total Revenue</div>
                    <div class="kpi-value">₹<?= number_format($total_revenue, 2) ?></div>
                    <span class="kpi-change"><i class="fas fa-arrow-up"></i> From all sales</span>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="kpi-card cost">
                    <div class="kpi-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="kpi-card-header">Total Purchases</div>
                    <div class="kpi-value">₹<?= number_format($total_cost, 2) ?></div>
                    <span class="kpi-change" style="background: rgba(239, 68, 68, 0.1); color: var(--danger-dark);"><i class="fas fa-arrow-down"></i> Cost incurred</span>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="kpi-card value">
                    <div class="kpi-icon">
                        <i class="fas fa-boxes-stacked"></i>
                    </div>
                    <div class="kpi-card-header">Inventory Value</div>
                    <div class="kpi-value">₹<?= number_format($inventory_value, 2) ?></div>
                    <span class="kpi-change" style="background: rgba(99, 102, 241, 0.1); color: var(--primary-dark);"><i class="fas fa-cube"></i> Current stock</span>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="kpi-card profit">
                    <div class="kpi-icon">
                        <i class="fas fa-sack-dollar"></i>
                    </div>
                    <div class="kpi-card-header">Gross Profit</div>
                    <div class="kpi-value">₹<?= number_format($total_revenue - $total_cost, 2) ?></div>
                    <span class="kpi-change" style="background: rgba(245, 158, 11, 0.1); color: var(--warning-dark);"><i class="fas fa-chart-line"></i> Net margin</span>
                </div>
            </div>
        </div>
        
        <?php if (check_permission([ROLE_ADMIN, ROLE_MANAGER])): ?>
        <div class="d-flex justify-content-center mb-5">
            <a href="view_forecast.php" class="btn btn-forecast">
                <i class="fas fa-wand-magic-sparkles me-2"></i> View AI Demand Forecast
            </a>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            
            <div class="col-lg-6">
                <div class="top-selling-card">
                    <h3><i class="fas fa-trophy"></i>Top Performing Products</h3>
                    <div class="table-responsive">
                        <table class="table table-hover top-selling-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">Rank</th>
                                    <th>Product Name</th>
                                    <th class="text-end">Qty Sold</th>
                                    <th class="text-end">Total Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $rank = 1; while($row = $result_top_selling->fetch_assoc()): ?>
                                <tr>
                                    <td><span class="table-badge rank-badge">#<?= $rank++ ?></span></td>
                                    <td><strong><?= htmlspecialchars($row['Name']) ?></strong></td>
                                    <td class="text-end"><span class="table-badge qty-badge"><?= $row['TotalQuantity'] ?> units</span></td>
                                    <td class="text-end"><span class="table-badge sales-badge">₹<?= number_format($row['TotalSales'], 2) ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="top-selling-card p-4">
                    <h3><i class="fas fa-chart-column"></i>Sales Trend Analysis</h3>
                    <div class="chart-wrapper">
                        <canvas id="monthlySalesChart"></canvas>
                    </div>
                    <p class="text-muted mt-3 mb-0"><i class="fas fa-info-circle me-2"></i>Track your monthly performance and identify seasonal patterns</p>
                </div>
            </div>

        </div>

    </div>

    <script>
        const monthlySalesData = <?php echo json_encode($monthly_sales_data); ?>;
        
        const labels = monthlySalesData.map(item => item.Month);
        const dataValues = monthlySalesData.map(item => item.Sales);

        const ctx = document.getElementById('monthlySalesChart').getContext('2d');
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(99, 102, 241, 0.8)');
        gradient.addColorStop(1, 'rgba(6, 182, 212, 0.4)');

        const monthlySalesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Monthly Sales (₹)',
                    data: dataValues,
                    backgroundColor: gradient,
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    hoverBackgroundColor: 'rgba(79, 70, 229, 0.9)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        borderColor: 'rgba(99, 102, 241, 0.5)',
                        borderWidth: 1,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return '₹' + context.parsed.y.toLocaleString('en-IN');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                weight: '600'
                            },
                            color: '#64748b',
                            callback: function(value) {
                                return '₹' + (value / 1000) + 'k';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Revenue (₹)',
                            font: {
                                size: 13,
                                weight: '700'
                            },
                            color: '#1e293b'
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                weight: '600'
                            },
                            color: '#64748b'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php $conn->close(); ?>
</body>
</html>