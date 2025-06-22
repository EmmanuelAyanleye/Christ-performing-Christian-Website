<?php
session_start();
include('../includes/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Query the correct 'users' table and check for role and status
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND status = 'active' AND (role = 'admin' OR role = 'super_admin')");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_full_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        if (isset($_POST['remember'])) {
            // Set cookie for remember me
            $token = bin2hex(random_bytes(32));
            setcookie('admin_remember', $token, time() + 86400 * 30, '/');
            
            // Store token in the correct 'users' table
            $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $stmt->execute([$token, $user['id']]);
        }
        
        header('Location: index.php');
        exit();
    } else {
        header('Location: login.php?error=invalid_credentials');
        exit();
    }
}