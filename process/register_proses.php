<?php
include '../koneksi.php';

$nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
$email = mysqli_real_escape_string($koneksi, $_POST['email']);
$telepon = mysqli_real_escape_string($koneksi, $_POST['telepon']);
$password = mysqli_real_escape_string($koneksi, $_POST['password']); // No hash for simplicity

// Check if email exists
$check = mysqli_query($koneksi, "SELECT * FROM user WHERE email = '$email'");
if(mysqli_num_rows($check) > 0) {
    header("Location: ../register.php?error=1");
    exit();
}

// Insert new user
$query = mysqli_query($koneksi, 
    "INSERT INTO user (nama, email, telepon, password) 
     VALUES ('$nama', '$email', '$telepon', '$password')"
);

if($query) {
    header("Location: ../login.php?success=1");
} else {
    header("Location: ../register.php?error=2");
}
?>