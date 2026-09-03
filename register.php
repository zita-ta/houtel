<?php
session_start();
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Hotel Zita</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
                    <a href="login.php">Login</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="cream-form">
            <h2 style="text-align: center; color: var(--dark-brown); margin-bottom: 30px;">
                <i class="fas fa-user-plus"></i> Create Account
            </h2>
            
            <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> Email already exists!
            </div>
            <?php endif; ?>
            
            <form method="POST" action="process/register_proses.php">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" name="nama" placeholder="Enter your full name" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Phone Number</label>
                    <input type="tel" name="telepon" placeholder="Enter your phone number" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="password" placeholder="Create password" required>
                </div>
                
                <button type="submit" class="cream-btn" style="width: 100%; padding: 16px; font-size: 1.1rem;">
                    <i class="fas fa-user-plus"></i> Register Now
                </button>
            </form>
            
            <p style="text-align: center; margin-top: 25px; color: var(--dark-brown);">
                Already have an account? 
                <a href="login.php" style="color: var(--sage-green); font-weight: 600;">Login here</a>
            </p>
        </div>
    </div>

    <div id="toast-container"></div>

    <footer class="cream-footer">
        <div class="container">
            <p>&copy; 2024 Hotel Zita. Calm Cream Luxury Experience</p>
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
    function dismissToast(t){if(!t||t.classList.contains('hiding'))return;t.classList.add('hiding');setTimeout(()=>t.remove(),300);}

    window.addEventListener('DOMContentLoaded',()=>{
        <?php if(isset($_GET['error'])): ?>
        showToast('error','Email sudah terdaftar! Gunakan email lain.');
        <?php endif; ?>
        <?php if(isset($_GET['success'])): ?>
        showToast('success','Registrasi berhasil! Silakan login.');
        <?php endif; ?>
    });

    // Validasi form sebelum submit
    document.querySelector('form').addEventListener('submit',function(e){
        const pass = this.querySelector('[name="password"]').value;
        if(pass.length < 6){
            e.preventDefault();
            showToast('warning','Password minimal 6 karakter!');
            return;
        }
        showToast('info','Membuat akun...');
    });
    </script>
</body>
</html>