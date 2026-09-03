<?php
include '../koneksi.php';

$username = mysqli_real_escape_string($koneksi, $_POST['username']);
$password = $_POST['password'];

// Login menggunakan tabel user (cek berdasarkan email atau nama)
$query = mysqli_query($koneksi, 
    "SELECT * FROM user 
     WHERE (email = '$username' OR nama = '$username') 
     AND password = '$password'
     AND role = 'admin'"
);

if(!$query) {
    die("Query Error: " . mysqli_error($koneksi));
}

if(mysqli_num_rows($query) > 0) {
    $user = mysqli_fetch_assoc($query);
    
    // Set session untuk user
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    
    // Set session untuk kompatibilitas dengan kode admin lama
    $_SESSION['admin_id'] = $user['user_id'];
    $_SESSION['admin_username'] = $user['nama'];
    $_SESSION['admin_nama'] = $user['nama'];
    $_SESSION['admin_level'] = $user['role'];
    
    header("Location: ../admin/dashboard.php");
    exit();
} else {
    header("Location: ../login.php?error=1");
    exit();
}
?>
