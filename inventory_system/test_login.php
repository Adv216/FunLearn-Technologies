<?php
include 'db_connect.php';

$username = 'admin';
$password = 'admin123';

$stmt = $conn->prepare("SELECT * FROM USERS WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    die("❌ User not found in DB");
}

$user = $result->fetch_assoc();

echo "User Found<br>";
echo "DB Hash: " . $user['Password'] . "<br>";

if(password_verify($password, $user['Password'])){
    echo "✅ PASSWORD MATCHES";
}else{
    echo "❌ PASSWORD DOES NOT MATCH";
}
