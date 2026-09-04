<?php
session_start();

$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$db   = getenv('MYSQLDATABASE') ?: 'hotel_zita';
$port = getenv('MYSQLPORT') ?: 3306;

$koneksi = mysqli_connect($host, $user, $pass, $db, $port);

if (!$koneksi) {
    die("Connection failed: " . mysqli_connect_error());
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) || isset($_SESSION['admin_id']);
}

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
