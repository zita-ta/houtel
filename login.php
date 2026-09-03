<?php
include 'koneksi.php';

if(isset($_SESSION['user_id'])) {
    header("Location: user/dashboard.php");
    exit();
}

if(isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hotel Zita</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .tab-btn {
        flex: 1;
        padding: 15px;
        background: none;
        border: none;
        font-size: 1.1rem;
        color: var(--dark-brown);
        cursor: pointer;
        transition: all 0.3s;
        border-bottom: 3px solid transparent;
    }
    .tab-btn.active {
        color: var(--sage-green);
        border-bottom-color: var(--sage-green);
        font-weight: 600;
    }
    .tab-btn:hover {
        background-color: var(--soft-beige);
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
                    <a href="index.php">Home</a>
                    <a href="register.php">Register</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="cream-form">
            <h2 style="text-align: center; color: var(--dark-brown); margin-bottom: 30px;">
                <i class="fas fa-sign-in-alt"></i> Welcome Back
            </h2>
            
            <!-- Tabs untuk User/Admin -->
            <div style="display: flex; margin-bottom: 30px; border-bottom: 2px solid var(--soft-beige);">
                <button type="button" id="userTab" class="tab-btn active" onclick="switchTab('user')">
                    <i class="fas fa-user"></i> User Login
                </button>
                <button type="button" id="adminTab" class="tab-btn" onclick="switchTab('admin')">
                    <i class="fas fa-user-shield"></i> Admin Login
                </button>
            </div>
            
            <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> Invalid credentials!
            </div>
            <?php endif; ?>
            
            <!-- User Login Form -->
            <form method="POST" action="process/login_proses.php" id="userForm" class="login-form">
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="cream-btn" style="width: 100%; padding: 16px; font-size: 1.1rem;">
                    <i class="fas fa-sign-in-alt"></i> Login as User
                </button>
            </form>
            
            <!-- Admin Login Form -->
            <form method="POST" action="process/admin_login_proses.php" id="adminForm" class="login-form" style="display: none;">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Username</label>
                    <input type="text" name="username" placeholder="Enter username" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="password" placeholder="Enter admin password" required>
                </div>
                
                <button type="submit" class="cream-btn" style="width: 100%; padding: 16px; font-size: 1.1rem; background-color: var(--warm-brown);">
                    <i class="fas fa-user-shield"></i> Login as Admin
                </button>
            </form>
            
            <p style="text-align: center; margin-top: 25px; color: var(--dark-brown);">
                Don't have an account? 
                <a href="register.php" style="color: var(--sage-green); font-weight: 600;">Register here</a>
            </p>
        </div>
    </div>

    <footer class="cream-footer">
        <div class="container">
            <p>&copy; 2024 Hotel Zita. Calm Cream Luxury Experience</p>
        </div>
    </footer>

    <script>
    function switchTab(tab) {
        document.getElementById('userTab').classList.remove('active');
        document.getElementById('adminTab').classList.remove('active');
        document.getElementById('userForm').style.display = 'none';
        document.getElementById('adminForm').style.display = 'none';
        if(tab === 'user') {
            document.getElementById('userTab').classList.add('active');
            document.getElementById('userForm').style.display = 'block';
        } else {
            document.getElementById('adminTab').classList.add('active');
            document.getElementById('adminForm').style.display = 'block';
            showToast('warning','Akses Admin. Masukkan kredensial admin.');
        }
    }

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
    function dismissToast(t){if(!t||t.classList.contains('hiding'))return;t.classList.add('hiding');setTimeout(()=>t.remove(),300);}

    window.addEventListener('DOMContentLoaded',()=>{
        <?php if(isset($_GET['error'])): ?>
        showToast('error','Email atau password salah. Silakan coba lagi.');
        <?php endif; ?>
        <?php if(isset($_GET['registered'])): ?>
        showToast('success','Registrasi berhasil! Silakan login.');
        <?php endif; ?>
        <?php if(isset($_GET['logout'])): ?>
        showToast('info','Kamu telah berhasil logout.');
        <?php endif; ?>
    });

    document.querySelectorAll('form').forEach(form=>{
        form.addEventListener('submit',function(){showToast('info','Sedang masuk...');});
    });
    </script>

    <div id="toast-container"></div>
</body>
</html>