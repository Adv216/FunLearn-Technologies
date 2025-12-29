<?php
include 'db_connect.php';

$users = [
    ['admin','admin123'],
    ['TEST','test123']
];

foreach($users as $u){
    $hash = password_hash($u[1], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE USERS SET Password=? WHERE Username=?");
    $stmt->bind_param("ss", $hash, $u[0]);
    $stmt->execute();
}

echo "✔ Passwords rebuilt";
