<?php
session_start();
include 'db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM USERS WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['User_ID'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['role'] = $user['Role'];

            header("Location: index.php");
            exit;
        }
    }

    $error = "Invalid username or password";
}
?>
<!DOCTYPE html>
<html>
<head>
<title>FunLearn Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background: linear-gradient(135deg,#5B9BD5,#2B4C7E);
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}
.login-card{
    width:380px;
    background:white;
    padding:2.5rem;
    border-radius:15px;
    box-shadow:0 15px 40px rgba(0,0,0,.3);
}
.btn-login{
    background:linear-gradient(135deg,#10b981,#059669);
    border:none;
}
</style>
</head>
<body>

<div class="login-card">
    <h2 class="text-center mb-2">FunLearn</h2>
    <p class="text-center text-muted mb-4">Inventory & Production System</p>

    <?php if($error): ?>
        <div class="alert alert-danger text-center"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Username</label>
            <input name="username" class="form-control" required>
        </div>

        <div class="mb-4">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button class="btn btn-login w-100 text-white py-2">Login</button>
    </form>
</div>

</body>
</html>
