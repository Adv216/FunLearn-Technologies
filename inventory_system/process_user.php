<?php
include 'secure_page_template.php'; 
include 'db_connect.php'; 

// === PERMISSION CHECK: ADMIN ONLY FOR ALL USER CRUD ===
if (!check_permission([ROLE_ADMIN])) {
    die("Permission Denied: Only Administrators can modify user accounts.");
}
// ==========================

$action = $_REQUEST['action'] ?? '';
$user_id = (int)($_REQUEST['user_id'] ?? 0);
$redirect_url = 'user_management.php';
$message = '';

try {
    if ($action == 'save' && $_SERVER["REQUEST_METHOD"] == "POST") {
        
        $username = $conn->real_escape_string($_POST['username']);
        $role = $conn->real_escape_string($_POST['role']);
        $password_input = $_POST['password'];

        if ($user_id == 0) {
            // --- CREATE (ADD) ---
            if (empty($password_input)) {
                throw new Exception("Password is required for new users.");
            }
            $hashed_password = md5($password_input); 
            $sql = "INSERT INTO USERS (Username, Password, Role) VALUES ('$username', '$hashed_password', '$role')";
            $message = "User '$username' created successfully.";
        } else {
            // --- UPDATE (EDIT) ---
            $sql = "UPDATE USERS SET Username='$username', Role='$role'";
            
            // Only update password if a value was provided
            if (!empty($password_input)) {
                $hashed_password = md5($password_input);
                $sql .= ", Password='$hashed_password'";
            }
            $sql .= " WHERE User_ID=$user_id";
            $message = "User ID $user_id updated successfully.";
        }
        
        if (!$conn->query($sql)) {
            throw new Exception("Database error: " . $conn->error);
        }
        
    } elseif ($action == 'delete' && $user_id > 0) {
        // --- DELETE ---
        if ($user_id == $_SESSION['user_id']) {
             throw new Exception("Cannot delete your own active account.");
        }
        
        $sql = "DELETE FROM USERS WHERE User_ID=$user_id";
        if (!$conn->query($sql)) {
             throw new Exception("Database error: " . $conn->error);
        }
        $message = "User ID $user_id deleted successfully.";
        
    } else {
        // Invalid action or access
        $redirect_url = 'user_management.php';
        header("Location: $redirect_url");
        exit;
    }
    
    // Success redirect
    header("Location: $redirect_url?status=success&msg=" . urlencode($message));
    exit;

} catch (Exception $e) {
    // Error redirect
    header("Location: $redirect_url?status=error&msg=" . urlencode($e->getMessage()));
    exit;
}

$conn->close();
?>