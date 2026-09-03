<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db   = "hotel_zita";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function untuk cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']) || isset($_SESSION['admin_id']);
}

// Function untuk cek role
function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function isUser() {
    return isset($_SESSION['user_id']) && !isset($_SESSION['admin_id']);
}

function getUserRole() {
    if(isset($_SESSION['admin_id'])) return 'admin';
    if(isset($_SESSION['user_id'])) return 'user';
    return 'guest';
}
?>
