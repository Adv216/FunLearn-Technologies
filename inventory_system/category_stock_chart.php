<?php
include 'secure_page_template.php';
include 'db_connect.php';

check_permission([ROLE_ADMIN, ROLE_MANAGER]);

$result = $conn->query("
    SELECT c.Category_Name, SUM(p.Quantity) AS total_qty
    FROM PRODUCT_CATEGORIES c
    LEFT JOIN FINISHED_PRODUCTS p ON c.Category_ID = p.Category_ID
    GROUP BY c.Category_ID
    ORDER BY c.Category_Name
");

$labels = [];
$data = [];

while ($row = $result->fetch_assoc()) {
    $labels[] = $row['Category_Name'];
    $data[] = (int)$row['total_qty'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Stock Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 2rem 0;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background Particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 15s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0) rotate(0deg);
                opacity: 0.3;
            }
            33% {
                transform: translateY(-120px) translateX(80px) rotate(120deg);
                opacity: 0.6;
            }
            66% {
                transform: translateY(-60px) translateX(-80px) rotate(240deg);
                opacity: 0.4;
            }
        }

        .main-container {
            position: relative;
            z-index: 1;
        }

        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
            animation: slideDown 0.6s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header-icon {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            }
        }

        .header-icon::before {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            background: var(--primary-gradient);
            border-radius: 30px;
            z-index: -1;
            animation: rotate 3s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .header-icon i {
            font-size: 3.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-title {
            font-size: 2.8rem;
            font-weight: 800;
            color: white;
            text-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        /* Chart Card */
        .chart-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.5);
            position: relative;
            overflow: hidden;
            animation: zoomIn 0.6s ease;
            margin-bottom: 2rem;
        }

        @keyframes zoomIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .chart-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #667eea);
            background-size: 200% 100%;
            animation: gradient 3s linear infinite;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }

        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #e9ecef;
        }

        .chart-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0;
        }

        .chart-title i {
            color: #667eea;
            font-size: 1.8rem;
        }

        .chart-stats {
            display: flex;
            gap: 1.5rem;
        }

        .stat-item {
            text-align: center;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-radius: 15px;
            border: 2px solid rgba(102, 126, 234, 0.2);
        }

        .stat-label {
            font-size: 0.85rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .chart-wrapper {
            position: relative;
            height: 500px;
            padding: 1rem;
            background: rgba(249, 250, 251, 0.5);
            border-radius: 15px;
        }

        /* Legend */
        .custom-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e9ecef;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.25rem;
            background: white;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .legend-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .legend-label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }

        .legend-value {
            font-weight: 700;
            color: #667eea;
            font-size: 1rem;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 0.6s ease 0.3s both;
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

        .btn-gradient {
            border: none;
            color: white;
            padding: 0.9rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            position: relative;
            overflow: hidden;
        }

        .btn-gradient::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-gradient:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-gradient span {
            position: relative;
            z-index: 1;
        }

        .btn-primary-gradient {
            background: linear-gradient(135deg, #bdc3c7 0%, #95a5a6 100%);
            box-shadow: 0 4px 15px rgba(149, 165, 166, 0.4);
        }

        .btn-primary-gradient:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(149, 165, 166, 0.6);
            color: white;
        }

        .btn-success-gradient {
            background: var(--success-gradient);
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);
        }

        .btn-success-gradient:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(17, 153, 142, 0.6);
            color: white;
        }

        /* Loading Animation */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            z-index: 10;
            animation: fadeOut 0.5s ease 1s forwards;
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                pointer-events: none;
            }
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 5px solid #e9ecef;
            border-top: 5px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }

            .chart-card {
                padding: 2rem 1.5rem;
            }

            .chart-header {
                flex-direction: column;
                gap: 1rem;
            }

            .chart-stats {
                width: 100%;
                justify-content: space-around;
            }

            .chart-wrapper {
                height: 350px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-gradient {
                width: 100%;
                justify-content: center;
            }

            .custom-legend {
                justify-content: center;
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state i {
            font-size: 5rem;
            color: #667eea;
            opacity: 0.3;
            margin-bottom: 1.5rem;
        }

        .empty-state h4 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: #6c757d;
        }
    </style>
</head>
<body>

<!-- Animated Background Particles -->
<div class="particles">
    <div class="particle" style="width: 80px; height: 80px; left: 10%; top: 15%; animation-delay: 0s;"></div>
    <div class="particle" style="width: 60px; height: 60px; left: 85%; top: 25%; animation-delay: 2s;"></div>
    <div class="particle" style="width: 100px; height: 100px; left: 50%; top: 50%; animation-delay: 4s;"></div>
    <div class="particle" style="width: 70px; height: 70px; left: 20%; top: 75%; animation-delay: 1s;"></div>
    <div class="particle" style="width: 90px; height: 90px; left: 75%; top: 80%; animation-delay: 3s;"></div>
</div>

<div class="main-container">
    <div class="container">

        <!-- Page Header -->
        <div class="page-header">
            <div class="header-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h1 class="page-title">Category Stock Analytics</h1>
            <p class="page-subtitle">Real-time inventory visualization</p>
        </div>

        <!-- Chart Card -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">
                    <i class="fas fa-cube"></i>
                    Stock Distribution
                </h3>
                <div class="chart-stats">
                    <div class="stat-item">
                        <div class="stat-label">Categories</div>
                        <div class="stat-value"><?= count($labels) ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Total Items</div>
                        <div class="stat-value"><?= array_sum($data) ?></div>
                    </div>
                </div>
            </div>

            <?php if (count($data) > 0): ?>
                <div class="chart-wrapper">
                    <div class="loading-overlay">
                        <div class="loading-spinner"></div>
                    </div>
                    <canvas id="stockChart"></canvas>
                </div>

                <!-- Custom Legend -->
                <div class="custom-legend" id="customLegend">
                    <?php 
                    $colors = ['#667eea', '#11998e', '#f59e0b', '#ef4444', '#06b6d4', '#8b5cf6', '#ec4899', '#10b981'];
                    foreach ($labels as $index => $label): 
                        $color = $colors[$index % count($colors)];
                    ?>
                        <div class="legend-item">
                            <div class="legend-color" style="background: <?= $color ?>"></div>
                            <span class="legend-label"><?= htmlspecialchars($label) ?>:</span>
                            <span class="legend-value"><?= $data[$index] ?> items</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h4>No Data Available</h4>
                    <p>There are no categories or products in the system yet.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="index.php" class="btn btn-gradient btn-primary-gradient">
                <span><i class="fas fa-home"></i> Back to Dashboard</span>
            </a>
            <button onclick="downloadChart()" class="btn btn-gradient btn-success-gradient">
                <span><i class="fas fa-download"></i> Download Chart</span>
            </button>
        </div>

    </div>
</div>

<?php if (count($data) > 0): ?>
<script>
const ctx = document.getElementById('stockChart').getContext('2d');

// Gradient colors
const gradientColors = [
    { start: '#667eea', end: '#764ba2' },
    { start: '#11998e', end: '#38ef7d' },
    { start: '#f59e0b', end: '#d97706' },
    { start: '#ef4444', end: '#dc2626' },
    { start: '#06b6d4', end: '#0891b2' },
    { start: '#8b5cf6', end: '#7c3aed' },
    { start: '#ec4899', end: '#db2777' },
    { start: '#10b981', end: '#059669' }
];

const backgroundColors = <?= json_encode($data) ?>.map((_, index) => {
    const colorSet = gradientColors[index % gradientColors.length];
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, colorSet.start);
    gradient.addColorStop(1, colorSet.end);
    return gradient;
});

const chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Stock Quantity',
            data: <?= json_encode($data) ?>,
            backgroundColor: backgroundColors,
            borderRadius: 12,
            borderSkipped: false,
            barThickness: 60,
            maxBarThickness: 80
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 2000,
            easing: 'easeInOutQuart'
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 15,
                borderRadius: 10,
                titleFont: {
                    size: 16,
                    weight: 'bold'
                },
                bodyFont: {
                    size: 14
                },
                callbacks: {
                    label: function(context) {
                        return 'Stock: ' + context.parsed.y + ' items';
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
                    color: '#6b7280',
                    padding: 10
                }
            },
            x: {
                grid: {
                    display: false,
                    drawBorder: false
                },
                ticks: {
                    font: {
                        size: 13,
                        weight: '600'
                    },
                    color: '#2c3e50',
                    padding: 10
                }
            }
        },
        onHover: (event, activeElements) => {
            event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
        }
    }
});

// Download chart function
function downloadChart() {
    const link = document.createElement('a');
    link.download = 'category-stock-chart.png';
    link.href = document.getElementById('stockChart').toDataURL();
    link.click();
}

// Animate stats on load
const statValues = document.querySelectorAll('.stat-value');
statValues.forEach(stat => {
    const finalValue = parseInt(stat.textContent);
    let currentValue = 0;
    const increment = finalValue / 50;
    const timer = setInterval(() => {
        currentValue += increment;
        if (currentValue >= finalValue) {
            stat.textContent = finalValue;
            clearInterval(timer);
        } else {
            stat.textContent = Math.floor(currentValue);
        }
    }, 30);
});
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>