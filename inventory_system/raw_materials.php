<?php
include 'secure_page_template.php';
include 'db_connect.php';

if(isset($_POST['add'])){
    $stmt=$conn->prepare("INSERT INTO RAW_MATERIALS(Material_Name,Quantity,Min_Required) VALUES(?,?,?)");
    $stmt->bind_param("sii",$_POST['name'],$_POST['quantity'],$_POST['min']);
    $stmt->execute();
    header("Location: raw_materials.php"); exit;
}

if(isset($_POST['delete'])){
    $conn->query("DELETE FROM RAW_MATERIALS WHERE Material_ID=".$_POST['id']);
    header("Location: raw_materials.php"); exit;
}

$data=$conn->query("SELECT * FROM RAW_MATERIALS ORDER BY Material_Name");
?>
<!DOCTYPE html>
<html>
<head>
<title>Raw Materials</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

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
    font-size:3rem;
    font-weight:700;
    color:#fff;
    margin-bottom:0.5rem;
    text-shadow:0 2px 4px rgba(0,0,0,0.1);
}

.page-header p {
    font-size:1.1rem;
    color:rgba(255,255,255,0.9);
    font-weight:500;
}

.card {
    background:#fff;
    border:none;
    border-radius:20px;
    box-shadow:0 20px 60px rgba(0,0,0,0.15);
    margin-bottom:2rem;
    overflow:hidden;
    animation:fadeInUp 0.6s ease;
    transition:transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform:translateY(-5px);
    box-shadow:0 25px 70px rgba(0,0,0,0.2);
}

.card-header {
    background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding:1.5rem 2rem;
    border:none;
}

.card-header h4 {
    font-size:1.4rem;
    font-weight:700;
    color:#fff;
    margin:0;
    display:flex;
    align-items:center;
    gap:0.5rem;
}

.card-body {
    padding:2rem;
}

.form-group {
    margin-bottom:1.5rem;
}

.form-label {
    font-weight:600;
    color:#334155;
    margin-bottom:0.5rem;
    font-size:0.9rem;
    text-transform:uppercase;
    letter-spacing:0.5px;
}

.form-control {
    border:2px solid #e2e8f0;
    border-radius:12px;
    padding:0.75rem 1rem;
    font-size:1rem;
    transition:all 0.3s ease;
    font-weight:500;
}

.form-control:focus {
    border-color:#667eea;
    box-shadow:0 0 0 4px rgba(102,126,234,0.1);
    outline:none;
}

.form-control::placeholder {
    color:#94a3b8;
}

.input-group {
    display:grid;
    grid-template-columns:2fr 1fr 1fr;
    gap:1rem;
    margin-bottom:1.5rem;
}

.btn {
    border:none;
    border-radius:12px;
    padding:0.75rem 2rem;
    font-weight:600;
    font-size:1rem;
    cursor:pointer;
    transition:all 0.3s ease;
    text-transform:uppercase;
    letter-spacing:0.5px;
}

.btn-primary {
    background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color:#fff;
    box-shadow:0 4px 15px rgba(102,126,234,0.4);
}

.btn-primary:hover {
    transform:translateY(-2px);
    box-shadow:0 6px 20px rgba(102,126,234,0.5);
}

.btn-primary:active {
    transform:translateY(0);
}

.btn-danger {
    background:linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color:#fff;
    padding:0.5rem 1.5rem;
    font-size:0.85rem;
    box-shadow:0 2px 10px rgba(239,68,68,0.3);
}

.btn-danger:hover {
    transform:translateY(-2px);
    box-shadow:0 4px 15px rgba(239,68,68,0.4);
}

.table-container {
    overflow-x:auto;
    border-radius:12px;
}

.table {
    margin:0;
    width:100%;
}

.table thead {
    background:linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.table thead th {
    padding:1.25rem 1.5rem;
    font-weight:700;
    color:#475569;
    text-transform:uppercase;
    font-size:0.85rem;
    letter-spacing:0.5px;
    border:none;
}

.table tbody tr {
    border-bottom:1px solid #f1f5f9;
    transition:all 0.2s ease;
}

.table tbody tr:hover {
    background:linear-gradient(135deg, #faf5ff 0%, #f5f3ff 100%);
    transform:scale(1.01);
}

.table tbody td {
    padding:1.25rem 1.5rem;
    vertical-align:middle;
    color:#334155;
    font-weight:500;
    border:none;
}

.badge {
    padding:0.5rem 1rem;
    border-radius:50px;
    font-weight:700;
    font-size:0.8rem;
    text-transform:uppercase;
    letter-spacing:0.5px;
    display:inline-flex;
    align-items:center;
    gap:0.4rem;
}

.badge-success {
    background:linear-gradient(135deg, #10b981 0%, #059669 100%);
    color:#fff;
    box-shadow:0 2px 10px rgba(16,185,129,0.3);
}

.badge-danger {
    background:linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color:#fff;
    box-shadow:0 2px 10px rgba(239,68,68,0.3);
    animation:pulse 2s ease-in-out infinite;
}

.material-name {
    font-weight:600;
    color:#1e293b;
    font-size:1.05rem;
}

.quantity-display {
    font-size:1.1rem;
    font-weight:700;
    color:#667eea;
}

.min-display {
    font-size:1rem;
    font-weight:600;
    color:#64748b;
}

.empty-state {
    text-align:center;
    padding:3rem;
    color:#94a3b8;
}

.empty-state svg {
    width:80px;
    height:80px;
    margin-bottom:1rem;
    opacity:0.5;
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

@keyframes pulse {
    0%, 100% {
        box-shadow:0 2px 10px rgba(239,68,68,0.3);
    }
    50% {
        box-shadow:0 2px 20px rgba(239,68,68,0.6);
    }
}

.action-cell {
    text-align:right;
}

@media (max-width: 768px) {
    .input-group {
        grid-template-columns:1fr;
    }
    
    .page-header h1 {
        font-size:2rem;
    }
    
    .table {
        font-size:0.9rem;
    }
}
</style>
</head>
<body>

<div class="container">

<div class="page-header">
    <h1>🏭 Raw Materials</h1>
    <p>Manage your inventory and track stock levels</p>
</div>

<div class="card">
    <div class="card-header">
        <h4>➕ Add New Material</h4>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="input-group">
                <div class="form-group">
                    <label class="form-label">Material Name</label>
                    <input class="form-control" name="name" placeholder="Enter material name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control" name="quantity" placeholder="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Min Stock</label>
                    <input type="number" class="form-control" name="min" placeholder="0" required>
                </div>
            </div>
            <button name="add" class="btn btn-primary w-100">Add Material</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h4>📊 Inventory Overview</h4>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Current Stock</th>
                        <th>Min Required</th>
                        <th>Status</th>
                        <th class="action-cell">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($r=$data->fetch_assoc()): ?>
                    <tr>
                        <td><span class="material-name"><?= htmlspecialchars($r['Material_Name']) ?></span></td>
                        <td><span class="quantity-display"><?= $r['Quantity'] ?></span></td>
                        <td><span class="min-display"><?= $r['Min_Required'] ?></span></td>
                        <td>
                            <?php if($r['Quantity'] <= $r['Min_Required']): ?>
                                <span class="badge badge-danger">⚠ Low Stock</span>
                            <?php else: ?>
                                <span class="badge badge-success">✓ In Stock</span>
                            <?php endif; ?>
                        </td>
                        <td class="action-cell">
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="id" value="<?= $r['Material_ID'] ?>">
                                <button name="delete" class="btn btn-danger" onclick="return confirm('Delete this material?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>

</body>
</html>