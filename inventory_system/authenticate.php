<?php
// Start the session immediately
session_start();

include 'db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password']; 
    
    // Hash the input password using the same method used during insertion
    $hashed_password = md5($password);

    $sql = "SELECT User_ID, Username, Role, Password 
            FROM USERS 
            WHERE Username = '$username' AND Password = '$hashed_password'";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Success: Set session variables
        $_SESSION['user_id'] = $user['User_ID'];
        $_SESSION['username'] = $user['Username'];
        $_SESSION['role'] = $user['Role']; // Role is critical for permission checks
        
        // Redirect to dashboard
        header("Location: index.php");
        exit;
    } else {
        // Failure: Redirect back to login with an error flag
        header("Location: login.php?error=1");
        exit;
    }

    $conn->close();
} else {
    // If accessed directly, go to login page
    header("Location: login.php");
    exit;
}
?>