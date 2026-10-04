<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}


function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function current_user_role() {
    return $_SESSION['user_role'] ?? null;
}

function current_user_name() {
    return $_SESSION['user_name'] ?? 'User';
}

function current_user_email() {
    return $_SESSION['user_email'] ?? '';
}


function require_login($redirect_to = null) {
    if (!is_logged_in()) {
        if (function_exists('set_flash')) {
            set_flash('danger', 'You must log in to access that page.');
        }
        $target = $redirect_to ? $redirect_to : base_url('login.php');
        header("Location: " . $target);
        exit();
    }
}


function require_role($allowed_roles) {
    require_login();
    
    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }
    
    $current_role = current_user_role();
    
    if (!in_array($current_role, $allowed_roles)) {
        if (function_exists('set_flash')) {
            set_flash('danger', 'Access Denied: You do not have permission to view that section.');
        }
        redirect_by_role($current_role);
        exit();
    }
}


function redirect_by_role($role) {
    switch ($role) {
        case 'admin':
            header("Location: " . base_url('admin/dashboard.php'));
            break;
        case 'parent':
            header("Location: " . base_url('parent/dashboard.php'));
            break;
        case 'hospital':
            header("Location: " . base_url('hospital/dashboard.php'));
            break;
        default:
            header("Location: " . base_url('index.php'));
            break;
    }
    exit();
}
?>

