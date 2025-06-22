<?php
// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Login user
function login($username, $password) {
    global $conn;
    
    $sql = "SELECT id, username, password, role FROM users WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Update last login time
            $update_sql = "UPDATE users SET last_login = NOW() WHERE id = :id";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bindParam(':id', $user['id']);
            $update_stmt->execute();
            
            return true;
        }
    }
    return false;
}

// Logout user
function logout() {
    session_unset();
    session_destroy();
}

// Redirect if not logged in
function require_login() {
    if (!is_logged_in()) {
        header("Location: ../pages/login.php");
        exit();
    }
}

// Redirect if not admin
function require_admin() {
    require_login();
    if (!is_admin()) {
        header("Location: ../index.php");
        exit();
    }
}
?>