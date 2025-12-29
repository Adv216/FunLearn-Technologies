<?php
// secure_page_template.php
session_start();

// Define permission levels for easy checking
define('ROLE_ADMIN', 'Admin');
define('ROLE_MANAGER', 'Manager');
define('ROLE_CASHIER', 'Cashier');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: login.php");
    exit;
}

/**
 * Function to check if the current user has the required role.
 * @param array $required_roles Array of roles (e.g., ['Admin', 'Manager'])
 * @return bool True if user has the permission, false otherwise.
 */
function check_permission(array $required_roles) {
    if (!isset($_SESSION['role'])) return false;
    return in_array($_SESSION['role'], $required_roles);
}
?>