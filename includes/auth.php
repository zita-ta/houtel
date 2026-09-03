<?php
// File: includes/auth.php

// Cek apakah user sudah login
function checkUserLogin() {
    if(!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
}

// Cek apakah admin sudah login
function checkAdminLogin() {
    if(!isset($_SESSION['admin_id'])) {
        header("Location: ../login.php");
        exit();
    }
}

// Cek apakah super admin
function checkSuperAdmin() {
    if(!isset($_SESSION['admin_id']) || $_SESSION['admin_level'] != 'superadmin') {
        header("Location: admin/dashboard.php");
        exit();
    }
}

// Redirect berdasarkan role
function redirectBasedOnRole() {
    if(isset($_SESSION['admin_id'])) {
        header("Location: admin/dashboard.php");
        exit();
    } elseif(isset($_SESSION['user_id'])) {
        header("Location: user/dashboard.php");
        exit();
    }
}
?>