<?php
include '../koneksi.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Handle Delete User
if(isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    
    // Check if user has active bookings
    $check_bookings = mysqli_query($koneksi, 
        "SELECT COUNT(*) as count FROM booking WHERE user_id = '$user_id' AND status IN ('Pending', 'Confirmed')"
    );
    $booking_count = mysqli_fetch_assoc($check_bookings)['count'];
    
    if($booking_count > 0) {
        $error = "Cannot delete user with active bookings!";
    } else {
        $delete = mysqli_query($koneksi, "DELETE FROM user WHERE user_id = '$user_id' AND role != 'admin'");
        
        if($delete && mysqli_affected_rows($koneksi) > 0) {
            $success = "User deleted successfully!";
        } else {
            $error = "Failed to delete user. Cannot delete admin users.";
        }
    }
}

// Get all users with booking stats
$users = mysqli_query($koneksi, 
    "SELECT u.*, 
            COUNT(DISTINCT b.booking_id) as total_bookings,
            SUM(CASE WHEN b.status = 'Confirmed' THEN b.jumlah_total ELSE 0 END) as total_spent
     FROM user u
     LEFT JOIN booking b ON u.user_id = b.user_id
     WHERE u.role IN ('user', 'premium')
     GROUP BY u.user_id
     ORDER BY u.nama ASC"
);

if(!$users) {
    die("Query Error: " . mysqli_error($koneksi));
}

$total_users = mysqli_num_rows($users);
$total_revenue_result = mysqli_query($koneksi, "SELECT SUM(jumlah_total) as total FROM booking WHERE status = 'Confirmed'");
$total_revenue = mysqli_fetch_assoc($total_revenue_result)['total'] ?? 0;
$avg_bookings_result = mysqli_query($koneksi, "SELECT AVG(booking_count) as avg FROM (SELECT COUNT(*) as booking_count FROM booking GROUP BY user_id) as counts");
$avg_bookings = mysqli_fetch_assoc($avg_bookings_result)['avg'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Hotel Zita</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    /* TABLE STYLES WITH BORDERS */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background: white;
        border: 2px solid var(--soft-beige);
    }

    table th,
    table td {
        padding: 15px;
        text-align: left;
        border: 1px solid var(--soft-beige);
    }

    table th {
        background: linear-gradient(135deg, var(--soft-beige) 0%, #E8DCC8 100%);
        color: var(--dark-brown);
        font-weight: 600;
        border: 1px solid #D4C4A8;
    }

    table tbody tr:hover {
        background: var(--cream);
    }

    table tbody tr:nth-child(even) {
        background: #FDFBF7;
    }

    table tbody tr:nth-child(odd) {
        background: white;
    }
    
    .user-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(139, 69, 19, 0.1);
            border: 1px solid var(--soft-beige);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .user-info {
            flex: 1;
            min-width: 250px;
        }
        
        .user-stats {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-item .number {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--sage-green);
        }
        
        .stat-item .label {
            font-size: 0.9rem;
            color: var(--warm-brown);
        }
        
        .user-actions {
            display: flex;
            gap: 10px;
        }
        
        @media (max-width: 768px) {
            .user-card {
                flex-direction: column;
                align-items: stretch;
            }
            
            .user-stats {
                justify-content: space-around;
            }
        }

    /* ===== NOTIFICATION TOAST STYLES ===== */
    #toast-container {
        position:fixed; top:20px; right:20px; z-index:9999;
        display:flex; flex-direction:column; gap:10px; pointer-events:none;
    }
    .toast {
        display:flex; align-items:center; gap:12px;
        padding:16px 20px; border-radius:12px; min-width:300px; max-width:400px;
        box-shadow:0 8px 25px rgba(0,0,0,0.15); pointer-events:all;
        animation:slideInRight 0.4s cubic-bezier(0.175,0.885,0.32,1.275) forwards;
        position:relative; overflow:hidden;
    }
    .toast::before { content:''; position:absolute; bottom:0; left:0; height:3px; background:rgba(255,255,255,0.5); animation:toastProgress 3.5s linear forwards; }
    .toast.success { background:linear-gradient(135deg,#2E7D32,#43A047); color:white; }
    .toast.error   { background:linear-gradient(135deg,#C62828,#E53935); color:white; }
    .toast.warning { background:linear-gradient(135deg,#E65100,#FB8C00); color:white; }
    .toast.info    { background:linear-gradient(135deg,#1565C0,#1E88E5); color:white; }
    .toast-icon { font-size:1.4rem; flex-shrink:0; }
    .toast-body { flex:1; }
    .toast-title { font-weight:700; font-size:0.95rem; margin-bottom:2px; }
    .toast-msg   { font-size:0.85rem; opacity:0.9; }
    .toast-close { background:none; border:none; color:white; opacity:0.7; cursor:pointer; font-size:1.1rem; padding:0; }
    .toast-close:hover { opacity:1; }
    .toast.hiding { animation:slideOutRight 0.3s ease forwards; }
    @keyframes slideInRight { from{transform:translateX(120%);opacity:0;} to{transform:translateX(0);opacity:1;} }
    @keyframes slideOutRight{ from{transform:translateX(0);opacity:1;} to{transform:translateX(120%);opacity:0;} }
    @keyframes toastProgress { from{width:100%;} to{width:0%;} }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-hotel"></i>
                    <span>Hotel Zita</span>
                </div>
                <nav class="nav-links">
                    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="hotels.php"><i class="fas fa-building"></i> Hotels</a>
                    <a href="rooms.php"><i class="fas fa-bed"></i> Rooms</a>
                    <a href="bookings.php"><i class="fas fa-calendar"></i> Bookings</a>
                    <a href="users.php" class="active"><i class="fas fa-users"></i> Users</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <h1 style="color: var(--dark-brown); margin: 30px 0;">
            <i class="fas fa-users"></i> Manage Users
        </h1>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="cream-card" style="text-align: center;">
                <i class="fas fa-users" style="font-size: 2.5rem; color: var(--sage-green); margin-bottom: 10px;"></i>
                <h3 style="font-size: 2.5rem; color: var(--sage-green); margin: 10px 0;"><?php echo $total_users; ?></h3>
                <p style="color: var(--dark-brown); font-weight: 600;">Total Users</p>
            </div>
            
            <div class="cream-card" style="text-align: center;">
                <i class="fas fa-money-bill-wave" style="font-size: 2.5rem; color: #4CAF50; margin-bottom: 10px;"></i>
                <h3 style="font-size: 1.5rem; color: var(--warm-brown); margin: 10px 0;">
                    Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?>
                </h3>
                <p style="color: var(--dark-brown); font-weight: 600;">Total Revenue</p>
            </div>
            
            <div class="cream-card" style="text-align: center;">
                <i class="fas fa-chart-line" style="font-size: 2.5rem; color: #E91E63; margin-bottom: 10px;"></i>
                <h3 style="font-size: 2.5rem; color: #E91E63; margin: 10px 0;">
                    <?php echo number_format($avg_bookings, 1); ?>
                </h3>
                <p style="color: var(--dark-brown); font-weight: 600;">Avg Bookings/User</p>
            </div>
        </div>
        
        <h2 style="color: var(--dark-brown); margin-bottom: 20px;">
            <i class="fas fa-list"></i> All Users (<?php echo $total_users; ?>)
        </h2>
        
        <?php if(mysqli_num_rows($users) > 0): ?>
            <?php while($user = mysqli_fetch_assoc($users)): ?>
            <div class="user-card">
                <div class="user-info">
                    <h3 style="color: var(--dark-brown); margin-bottom: 5px;">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($user['nama']); ?>
                    </h3>
                    <p style="color: var(--warm-brown); margin-bottom: 5px;">
                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                    </p>
                    <p style="color: var(--warm-brown); margin-bottom: 5px;">
                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['telepon']); ?>
                    </p>
                    <p style="color: var(--warm-brown); margin-bottom: 5px;">
                        <i class="fas fa-tag"></i> Role: <strong><?php echo ucfirst($user['role']); ?></strong>
                    </p>
                </div>
                
                <div class="user-stats">
                    <div class="stat-item">
                        <div class="number"><?php echo $user['total_bookings']; ?></div>
                        <div class="label">Bookings</div>
                    </div>
                    <div class="stat-item">
                        <div class="number" style="font-size: 1.3rem;">Rp <?php echo number_format($user['total_spent'], 0, ',', '.'); ?></div>
                        <div class="label">Total Spent</div>
                    </div>
                </div>
                
                <div class="user-actions">
                    <a href="?delete=<?php echo $user['user_id']; ?>" 
                       class="cream-btn" 
                       style="background: #e74c3c;"
                       onclick="return confirm('Are you sure you want to delete this user?')">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
            
        <?php else: ?>
            <div class="cream-card" style="text-align: center; padding: 60px 20px;">
                <i class="fas fa-users" style="font-size: 4rem; color: var(--soft-beige);"></i>
                <h3 style="color: var(--dark-brown); margin-top: 20px;">No Users Found</h3>
                <p style="color: var(--warm-brown);">No registered users yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Toast Notification Container -->
    <div id="toast-container"></div>

    <footer>
        <div class="container">
            <p><i class="fas fa-hotel"></i> Hotel Zita - Admin Panel</p>
            <p>&copy; 2024 Hotel Zita. All rights reserved.</p>
        </div>
    </footer>

    <script>
    const toastIcons  = { success:'<i class="fas fa-check-circle"></i>', error:'<i class="fas fa-times-circle"></i>', warning:'<i class="fas fa-exclamation-triangle"></i>', info:'<i class="fas fa-info-circle"></i>' };
    const toastTitles = { success:'Berhasil', error:'Gagal', warning:'Peringatan', info:'Info' };

    function showToast(type, message, duration=3500) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <span class="toast-icon">${toastIcons[type]}</span>
            <div class="toast-body">
                <div class="toast-title">${toastTitles[type]}</div>
                <div class="toast-msg">${message}</div>
            </div>
            <button class="toast-close" onclick="dismissToast(this.parentElement)">&times;</button>`;
        container.appendChild(toast);
        setTimeout(() => dismissToast(toast), duration);
    }
    function dismissToast(toast) {
        if (!toast || toast.classList.contains('hiding')) return;
        toast.classList.add('hiding');
        setTimeout(() => toast.remove(), 300);
    }
    function showConfirmToast(message, onConfirm) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = 'toast warning';
        toast.innerHTML = `
            <span class="toast-icon"><i class="fas fa-exclamation-triangle"></i></span>
            <div class="toast-body">
                <div class="toast-title">Konfirmasi Hapus User</div>
                <div class="toast-msg">${message}</div>
                <div style="display:flex;gap:8px;margin-top:10px;">
                    <button id="confirmYes" style="background:white;color:#C62828;border:none;padding:5px 14px;border-radius:6px;font-weight:700;cursor:pointer;font-size:0.85rem;">Ya, Hapus</button>
                    <button onclick="dismissToast(this.closest('.toast'))" style="background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.4);padding:5px 14px;border-radius:6px;cursor:pointer;font-size:0.85rem;">Batal</button>
                </div>
            </div>`;
        toast.querySelector('#confirmYes').addEventListener('click', () => {
            dismissToast(toast);
            showToast('info', 'Menghapus user...');
            setTimeout(onConfirm, 400);
        });
        container.appendChild(toast);
    }

    // PHP-triggered notifications
    <?php if(isset($success)): ?>
    window.addEventListener('DOMContentLoaded', () => showToast('success', '<?php echo addslashes($success); ?>'));
    <?php endif; ?>
    <?php if(isset($error)): ?>
    window.addEventListener('DOMContentLoaded', () => showToast('error', '<?php echo addslashes($error); ?>'));
    <?php endif; ?>

    // Intercept delete links
    document.querySelectorAll('a[href*="delete"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.href;
            showConfirmToast('Aksi ini tidak dapat dibatalkan. Yakin ingin menghapus user ini?', () => window.location.href = href);
        });
    });
    </script>
</body>
</html>
