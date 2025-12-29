<?php
include 'secure_page_template.php';
include 'db_connect.php';

check_permission([ROLE_ADMIN, ROLE_MANAGER]);

// Handle form submit
if (isset($_POST['use_material'])) {
    $material_id = $_POST['material_id'];
    $qty = $_POST['quantity'];
    $date = $_POST['date'];
    $notes = $_POST['notes'];

    // Deduct stock
    $conn->query("UPDATE RAW_MATERIALS SET Quantity = Quantity - $qty WHERE Material_ID = $material_id");

    // Log usage
    $stmt = $conn->prepare("INSERT INTO RAW_MATERIAL_USAGE (Material_ID, Used_Quantity, Usage_Date, Notes) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $material_id, $qty, $date, $notes);
    $stmt->execute();
}

// Fetch data
$materials = $conn->query("SELECT * FROM RAW_MATERIALS ORDER BY Material_Name");
$history = $conn->query("
    SELECT u.*, r.Material_Name 
    FROM RAW_MATERIAL_USAGE u
    JOIN RAW_MATERIALS r ON u.Material_ID = r.Material_ID
    ORDER BY u.Usage_Date DESC
    LIMIT 50
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Raw Material Usage</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

* { margin:0; padding:0; box-sizing:border-box }

body {
    font-family:'Inter',system-ui,-apple-system,sans-serif;
    background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height:100vh;
    padding:2rem 0;
}

.container { max-width:1200px }

.page-header {
    text-align:center;
    margin-bottom:3rem;
    animation:fadeInDown 0.6s ease;
}

.page-header h1 {
    font-size:3.5rem;
    font-weight:800;
    color:#fff;
    margin-bottom:0.5rem;
    text-shadow:0 4px 12px rgba(0,0,0,0.2);
    letter-spacing:-1px;
}

.page-header p {
    font-size:1.15rem;
    color:rgba(255,255,255,0.95);
    font-weight:500;
}

.card {
    background:#fff;
    border:none;
    border-radius:24px;
    box-shadow:0 20px 60px rgba(0,0,0,0.2);
    margin-bottom:2rem;
    overflow:hidden;
    animation:fadeInUp 0.6s ease;
    transition:transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform:translateY(-8px);
    box-shadow:0 30px 80px rgba(0,0,0,0.25);
}

.card-header {
    background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding:2rem;
    border:none;
}

.card-header h4 {
    font-size:1.5rem;
    font-weight:800;
    color:#fff;
    margin:0;
    display:flex;
    align-items:center;
    gap:0.75rem;
    letter-spacing:-0.5px;
}

.card-body {
    padding:2.5rem;
}

.form-group {
    margin-bottom:1.5rem;
}

.form-label {
    font-weight:700;
    color:#1e293b;
    margin-bottom:0.75rem;
    font-size:0.9rem;
    text-transform:uppercase;
    letter-spacing:0.5px;
    display:block;
}

.form-control, .form-select {
    border:2px solid #e2e8f0;
    border-radius:14px;
    padding:0.875rem 1.25rem;
    font-size:1rem;
    transition:all 0.3s ease;
    font-weight:500;
    width:100%;
    background:#fff;
}

.form-control:focus, .form-select:focus {
    border-color:#667eea;
    box-shadow:0 0 0 4px rgba(102,126,234,0.15);
    outline:none;
    transform:translateY(-2px);
}

.form-control::placeholder {
    color:#94a3b8;
}

.form-row {
    display:grid;
    grid-template-columns:2fr 1fr 1.5fr 1.5fr;
    gap:1.25rem;
    margin-bottom:1.5rem;
}

.btn {
    border:none;
    border-radius:14px;
    padding:0.875rem 2rem;
    font-weight:700;
    font-size:1rem;
    cursor:pointer;
    transition:all 0.3s ease;
    text-transform:uppercase;
    letter-spacing:0.5px;
}

.btn-success {
    background:linear-gradient(135deg, #10b981 0%, #059669 100%);
    color:#fff;
    box-shadow:0 6px 20px rgba(16,185,129,0.4);
}

.btn-success:hover {
    transform:translateY(-3px);
    box-shadow:0 8px 30px rgba(16,185,129,0.5);
}

.btn-secondary {
    background:linear-gradient(135deg, #64748b 0%, #475569 100%);
    color:#fff;
    box-shadow:0 6px 20px rgba(100,116,139,0.4);
}

.btn-secondary:hover {
    transform:translateY(-3px);
    box-shadow:0 8px 30px rgba(100,116,139,0.5);
}

.table-container {
    overflow-x:auto;
    border-radius:16px;
}

.table {
    margin:0;
    width:100%;
}

.table thead {
    background:linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.table thead th {
    padding:1.5rem 1.75rem;
    font-weight:800;
    color:#334155;
    text-transform:uppercase;
    font-size:0.8rem;
    letter-spacing:1px;
    border:none;
}

.table tbody tr {
    border-bottom:1px solid #f1f5f9;
    transition:all 0.2s ease;
}

.table tbody tr:hover {
    background:linear-gradient(135deg, #faf5ff 0%, #f5f3ff 100%);
    transform:scale(1.01);
    box-shadow:0 4px 12px rgba(102,126,234,0.08);
}

.table tbody td {
    padding:1.5rem 1.75rem;
    vertical-align:middle;
    color:#475569;
    font-weight:500;
    border:none;
}

.material-name {
    font-weight:700;
    color:#1e293b;
    font-size:1.05rem;
}

.quantity-badge {
    display:inline-flex;
    align-items:center;
    gap:0.5rem;
    padding:0.5rem 1rem;
    border-radius:50px;
    font-weight:800;
    font-size:0.9rem;
    background:linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color:#fff;
    box-shadow:0 4px 14px rgba(239,68,68,0.3);
}

.date-badge {
    display:inline-flex;
    align-items:center;
    gap:0.5rem;
    font-weight:600;
    color:#667eea;
    font-size:0.95rem;
}

.notes-text {
    color:#64748b;
    font-style:italic;
}

.back-button-container {
    text-align:center;
    margin-top:2rem;
    animation:fadeInUp 0.6s ease 0.3s backwards;
}

@keyframes fadeInDown {
    from {
        opacity:0;
        transform:translateY(-30px);
    }
    to {
        opacity:1;
        transform:translateY(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity:0;
        transform:translateY(30px);
    }
    to {
        opacity:1;
        transform:translateY(0);
    }
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns:1fr;
    }
    
    .page-header h1 {
        font-size:2.5rem;
    }
    
    .table {
        font-size:0.9rem;
    }
    
    .card-body {
        padding:1.5rem;
    }
}
</style>
</head>

<body>
<div class="container">

<div class="page-header">
    <h1>📝 Material Usage</h1>
    <p>Track and record raw material consumption</p>
</div>

<div class="card">
    <div class="card-header">
        <h4>✏️ Record Material Usage</h4>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Select Material</label>
                    <select name="material_id" class="form-select" required>
                        <option value="">Choose material...</option>
                        <?php while($m = $materials->fetch_assoc()): ?>
                        <option value="<?= $m['Material_ID'] ?>">
                            <?= htmlspecialchars($m['Material_Name']) ?> (Stock: <?= $m['Quantity'] ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Used Quantity</label>
                    <input type="number" name="quantity" class="form-control" placeholder="0" min="1" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Usage Date</label>
                    <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Notes (Optional)</label>
                    <input type="text" name="notes" class="form-control" placeholder="Add notes...">
                </div>
            </div>

            <button name="use_material" class="btn btn-success w-100">💾 Save Usage Record</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h4>📊 Usage History</h4>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Material Name</th>
                        <th>Quantity Used</th>
                        <th>Usage Date</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($history->num_rows > 0): ?>
                        <?php while($row = $history->fetch_assoc()): ?>
                        <tr>
                            <td><span class="material-name"><?= htmlspecialchars($row['Material_Name']) ?></span></td>
                            <td><span class="quantity-badge">- <?= $row['Used_Quantity'] ?></span></td>
                            <td><span class="date-badge">📅 <?= date('M d, Y', strtotime($row['Usage_Date'])) ?></span></td>
                            <td><span class="notes-text"><?= $row['Notes'] ? htmlspecialchars($row['Notes']) : '—' ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center" style="padding:3rem;color:#94a3b8">
                                <div style="font-size:3rem;margin-bottom:1rem">📋</div>
                                <div style="font-weight:600;font-size:1.1rem">No usage records yet</div>
                                <div style="font-size:0.9rem;margin-top:0.5rem">Start by recording your first material usage above</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="back-button-container">
    <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
</div>

</div>
</body>
</html>