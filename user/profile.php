<?php
include '../koneksi.php';
include '../includes/auth.php';
checkUserLogin();

// Ambil data user
$user_query = mysqli_query($koneksi, "SELECT * FROM user WHERE user_id = '{$_SESSION['user_id']}'");
$user = mysqli_fetch_assoc($user_query);

$success = '';
$error = '';

// Update profile
if(isset($_POST['update_profile'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $telepon = mysqli_real_escape_string($koneksi, $_POST['telepon']);

    $cek_email = mysqli_query($koneksi, "SELECT * FROM user WHERE email = '$email' AND user_id != '{$_SESSION['user_id']}'");
    if(mysqli_num_rows($cek_email) > 0) {
        $error = 'Email already used by another account.';
    } else {
        mysqli_query($koneksi, "UPDATE user SET nama='$nama', email='$email', telepon='$telepon' WHERE user_id='{$_SESSION['user_id']}'");
        $_SESSION['nama'] = $nama;
        $_SESSION['email'] = $email;
        $success = 'Profile updated successfully!';
        $user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM user WHERE user_id = '{$_SESSION['user_id']}'"));
    }
}

// Update password
if(isset($_POST['update_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if($old_password !== $user['password']) {
        $error = 'Current password is incorrect.';
    } elseif($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } elseif(strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters.';
    } else {
        mysqli_query($koneksi, "UPDATE user SET password='$new_password' WHERE user_id='{$_SESSION['user_id']}'");
        $success = 'Password updated successfully!';
    }
}

// Stats booking
$total_booking = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM Booking WHERE user_id = '{$_SESSION['user_id']}'"));
$total_paid = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM Booking WHERE user_id = '{$_SESSION['user_id']}' AND status = 'Paid'"));
$total_pending = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM Booking WHERE user_id = '{$_SESSION['user_id']}' AND status = 'Pending'"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Hotel Zita</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-header {
            background: linear-gradient(135deg, var(--warm-brown), var(--dark-brown));
            border-radius: 16px;
            padding: 40px;
            color: white;
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 30px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            flex-shrink: 0;
        }

        .profile-info h2 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .profile-info p {
            opacity: 0.85;
            margin-top: 5px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(139, 69, 19, 0.1);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--dark-brown);
        }

        .stat-label {
            color: var(--warm-brown);
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .section-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(139, 69, 19, 0.1);
        }

        .section-card h3 {
            color: var(--dark-brown);
            margin-bottom: 25px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--soft-beige);
        }

        .alert-success {
            background: #D1FAE5;
            color: #065F46;
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #FEE2E2;
            color: #991B1B;
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

    /* ===== TOAST NOTIFICATION ===== */
    #toast-container{position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none;}
    .toast{display:flex;align-items:center;gap:12px;padding:16px 20px;border-radius:12px;min-width:300px;max-width:400px;box-shadow:0 8px 25px rgba(0,0,0,0.15);pointer-events:all;animation:slideInRight 0.4s cubic-bezier(0.175,0.885,0.32,1.275) forwards;position:relative;overflow:hidden;}
    .toast::before{content:'';position:absolute;bottom:0;left:0;height:3px;background:rgba(255,255,255,0.5);animation:toastProgress 3.5s linear forwards;}
    .toast.success{background:linear-gradient(135deg,#2E7D32,#43A047);color:white;}
    .toast.error{background:linear-gradient(135deg,#C62828,#E53935);color:white;}
    .toast.warning{background:linear-gradient(135deg,#E65100,#FB8C00);color:white;}
    .toast.info{background:linear-gradient(135deg,#1565C0,#1E88E5);color:white;}
    .toast-icon{font-size:1.4rem;flex-shrink:0;}
    .toast-body{flex:1;}
    .toast-title{font-weight:700;font-size:0.95rem;margin-bottom:2px;}
    .toast-msg{font-size:0.85rem;opacity:0.9;}
    .toast-close{background:none;border:none;color:white;opacity:0.7;cursor:pointer;font-size:1.1rem;padding:0;}
    .toast-close:hover{opacity:1;}
    .toast.hiding{animation:slideOutRight 0.3s ease forwards;}
    @keyframes slideInRight{from{transform:translateX(120%);opacity:0;}to{transform:translateX(0);opacity:1;}}
    @keyframes slideOutRight{from{transform:translateX(0);opacity:1;}to{transform:translateX(120%);opacity:0;}}
    @keyframes toastProgress{from{width:100%;}to{width:0%;}}
    </style>
</head>
<body>
    <header class="cream-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-spa"></i>
                    <span>Hotel Zita</span>
                </div>
                <nav class="nav-links">
                    <a href="home.php">Home</a>
                    <a href="hotels.php">Hotels</a>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="my_bookings.php">My Bookings</a>
                    <a href="../logout.php">Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container" style="padding: 40px 20px;">

        <?php if($success): ?>
        <div class="alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
        <?php endif; ?>

        <?php if($error): ?>
        <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user['nama']); ?></h2>
                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                <?php if(!empty($user['telepon'])): ?>
                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['telepon']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_booking; ?></div>
                <div class="stat-label"><i class="fas fa-calendar-check"></i> Total Bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: var(--sage-green);"><?php echo $total_paid; ?></div>
                <div class="stat-label"><i class="fas fa-check-circle"></i> Paid</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #F59E0B;"><?php echo $total_pending; ?></div>
                <div class="stat-label"><i class="fas fa-clock"></i> Pending</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">

            <!-- Edit Profile -->
            <div class="section-card">
                <h3><i class="fas fa-user-edit"></i> Edit Profile</h3>
                <form method="POST">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Phone Number</label>
                        <input type="text" name="telepon" value="<?php echo htmlspecialchars($user['telepon'] ?? ''); ?>">
                    </div>
                    <button type="submit" name="update_profile" class="cream-btn" style="width: 100%;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="section-card">
                <h3><i class="fas fa-lock"></i> Change Password</h3>
                <form method="POST">
                    <div class="form-group">
                        <label><i class="fas fa-key"></i> Current Password</label>
                        <input type="password" name="old_password" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> New Password</label>
                        <input type="password" name="new_password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Confirm New Password</label>
                        <input type="password" name="confirm_password" required minlength="6">
                    </div>
                    <button type="submit" name="update_password" class="cream-btn" style="width: 100%;">
                        <i class="fas fa-key"></i> Update Password
                    </button>
                </form>
            </div>

        </div>
    </div>

    <div id="toast-container"></div>

    <footer class="cream-footer">
        <div class="container">
            <p><i class="fas fa-spa"></i> Hotel Zita - User Portal</p>
            <p>&copy; 2024 Hotel Zita. All rights reserved.</p>
        </div>
    </footer>

    <script>
    const toastIcons={success:'<i class="fas fa-check-circle"></i>',error:'<i class="fas fa-times-circle"></i>',warning:'<i class="fas fa-exclamation-triangle"></i>',info:'<i class="fas fa-info-circle"></i>'};
    const toastTitles={success:'Berhasil',error:'Gagal',warning:'Peringatan',info:'Info'};
    function showToast(type,message,duration=3500){
        const c=document.getElementById('toast-container');
        const t=document.createElement('div');
        t.className=`toast ${type}`;
        t.innerHTML=`<span class="toast-icon">${toastIcons[type]}</span><div class="toast-body"><div class="toast-title">${toastTitles[type]}</div><div class="toast-msg">${message}</div></div><button class="toast-close" onclick="dismissToast(this.parentElement)">&times;</button>`;
        c.appendChild(t);
        setTimeout(()=>dismissToast(t),duration);
    }
    function dismissToast(t){
        if(!t||t.classList.contains('hiding'))return;
        t.classList.add('hiding');
        setTimeout(()=>t.remove(),300);
    }

    window.addEventListener('DOMContentLoaded',()=>{
        <?php if($success): ?>
        showToast('success','<?php echo addslashes($success); ?>');
        <?php endif; ?>
        <?php if($error): ?>
        showToast('error','<?php echo addslashes($error); ?>');
        <?php endif; ?>
    });

    // Form submit feedback
    document.querySelectorAll('form').forEach(form=>{
        form.addEventListener('submit',function(){
            const isProfile=this.querySelector('[name="update_profile"]');
            const isPassword=this.querySelector('[name="update_password"]');
            if(isProfile) showToast('info','Menyimpan perubahan profil...');
            if(isPassword) showToast('info','Memperbarui password...');
        });
    });
    </script>
</body>
</html>
