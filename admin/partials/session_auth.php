<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication check
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Function to check for super admin role and deny access if not met
function require_super_admin() {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'super_admin') {
        // Redirect non-super admins to the dashboard with an error message
        header('Location: index.php?error=access_denied');
        exit();
    }
}