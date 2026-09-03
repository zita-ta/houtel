<?php
include '../koneksi.php';

$email = mysqli_real_escape_string($koneksi, $_POST['email']);
$password = $_POST['password'];

// Simple login tanpa hash
$query = mysqli_query($koneksi, 
    "SELECT * FROM user WHERE email = '$email' AND password = '$password'"
);

if(mysqli_num_rows($query) > 0) {
    $user = mysqli_fetch_assoc($query);
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = 'user';
    
    header("Location: ../user/dashboard.php?login=success");
    exit();
} else {
    header("Location: ../login.php?error=1");
    exit();
}
?>