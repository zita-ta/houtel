<?php
include '../koneksi.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get statistics
$total_users_result = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM user WHERE role = 'user'");
$total_users = mysqli_fetch_assoc($total_users_result)['total'];

$total_hotels_result = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM hotel");
$total_hotels = mysqli_fetch_assoc($total_hotels_result)['total'];

$total_bookings_result = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM booking");
$total_bookings = mysqli_fetch_assoc($total_bookings_result)['total'];

$total_revenue_result = mysqli_query($koneksi, "SELECT SUM(jumlah_total) as total FROM booking WHERE status = 'Confirmed'");
$total_revenue = mysqli_fetch_assoc($total_revenue_result)['total'] ?? 0;

// Recent bookings
$recent_bookings = mysqli_query($koneksi, 
    "SELECT b.*, u.nama as user_name, h.nama as hotel_name 
     FROM booking b
     JOIN user u ON b.user_id = u.user_id 
     JOIN reservasi r ON b.booking_id = r.booking_id 
     JOIN room rm ON r.room_id = rm.room_id 
     JOIN hotel h ON rm.hotel_id = h.hotel_id 
     ORDER BY b.tgl_booking DESC 
     LIMIT 5"
);

if(!$recent_bookings) {
    die("Query Error: " . mysqli_error($koneksi));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hotel Zita</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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

    .admin-card {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(93, 74, 46, 0.08);
        text-align: center;
        transition: transform 0.3s;
        border: 1px solid var(--soft-beige);
    }
    
    .admin-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(93, 74, 46, 0.12);
    }
    
    .admin-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--sage-green);
        margin: 10px 0;
    }
    
    .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .status-confirmed {
        background: #E8F5E9;
        color: #2E7D32;
    }
    
    .status-pending {
        background: #FFF3E0;
        color: #EF6C00;
    }
    
    .status-cancelled {
        background: #FFEBEE;
        color: #C62828;
    }
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
                    <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="hotels.php"><i class="fas fa-building"></i> Hotels</a>
                    <a href="rooms.php"><i class="fas fa-bed"></i> Rooms</a>
                    <a href="bookings.php"><i class="fas fa-calendar"></i> Bookings</a>
                    <a href="users.php"><i class="fas fa-users"></i> Users</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="cream-card" style="background: linear-gradient(135deg, var(--sage-green) 0%, #7A9168 100%); color: white; margin-top: 30px;">
            <h2 style="color: white;">Welcome, Admin!</h2>
            <p><i class="fas fa-user-tag"></i> Administrator</p>
            <p><i class="fas fa-calendar"></i> <?php echo date('l, F j, Y'); ?></p>
        </div>

        <h2 style="color: var(--dark-brown); margin: 40px 0 20px;">
            <i class="fas fa-chart-pie"></i> Overview
        </h2>
        <div class="admin-grid">
            <div class="admin-card">
                <i class="fas fa-users" style="font-size: 2.5rem; color: var(--sage-green); margin-bottom: 15px;"></i>
                <div class="stat-number"><?php echo $total_users; ?></div>
                <h3 style="color: var(--dark-brown); margin: 10px 0;">Total Users</h3>
                <p style="color: var(--warm-brown); font-size: 0.9rem;">Registered customers</p>
            </div>
            
            <div class="admin-card">
                <i class="fas fa-hotel" style="font-size: 2.5rem; color: var(--warm-brown); margin-bottom: 15px;"></i>
                <div class="stat-number"><?php echo $total_hotels; ?></div>
                <h3 style="color: var(--dark-brown); margin: 10px 0;">Hotels</h3>
                <p style="color: var(--warm-brown); font-size: 0.9rem;">Available properties</p>
            </div>
            
            <div class="admin-card">
                <i class="fas fa-calendar-check" style="font-size: 2.5rem; color: #E91E63; margin-bottom: 15px;"></i>
                <div class="stat-number"><?php echo $total_bookings; ?></div>
                <h3 style="color: var(--dark-brown); margin: 10px 0;">Bookings</h3>
                <p style="color: var(--warm-brown); font-size: 0.9rem;">Total reservations</p>
            </div>
            
            <div class="admin-card">
                <i class="fas fa-money-bill-wave" style="font-size: 2.5rem; color: #4CAF50; margin-bottom: 15px;"></i>
                <div class="stat-number" style="font-size: 1.8rem;">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></div>
                <h3 style="color: var(--dark-brown); margin: 10px 0;">Revenue</h3>
                <p style="color: var(--warm-brown); font-size: 0.9rem;">From confirmed bookings</p>
            </div>
        </div>

        <h2 style="color: var(--dark-brown); margin-top: 40px;">
            <i class="fas fa-history"></i> Recent Bookings
        </h2>
        
        <?php if(mysqli_num_rows($recent_bookings) > 0): ?>
            <div class="cream-card">
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Hotel</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($booking = mysqli_fetch_assoc($recent_bookings)): 
                                $status_class = '';
                                if($booking['status'] == 'Confirmed') $status_class = 'status-confirmed';
                                elseif($booking['status'] == 'Pending') $status_class = 'status-pending';
                                else $status_class = 'status-cancelled';
                            ?>
                            <tr>
                                <td><strong>#<?php echo $booking['booking_id']; ?></strong></td>
                                <td><?php echo $booking['user_name']; ?></td>
                                <td><?php echo $booking['hotel_name']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($booking['tgl_booking'])); ?></td>
                                <td><strong style="color: var(--warm-brown);">Rp <?php echo number_format($booking['jumlah_total'], 0, ',', '.'); ?></strong></td>
                                <td>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo $booking['status']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <center style="margin-top: 30px;">
                <a href="bookings.php" class="cream-btn">
                    <i class="fas fa-list"></i> View All Bookings
                </a>
            </center>
            
        <?php else: ?>
            <div class="cream-card">
                <center>
                    <i class="fas fa-calendar-times" style="font-size: 4rem; color: var(--soft-beige);"></i>
                    <h3 style="color: var(--dark-brown); margin-top: 20px;">No Bookings Yet</h3>
                    <p style="color: var(--warm-brown);">No bookings have been made yet.</p>
                </center>
            </div>
        <?php endif; ?>

        <div class="admin-grid" style="margin-top: 50px;">
            <div class="admin-card">
                <i class="fas fa-plus-circle" style="font-size: 2.5rem; color: var(--sage-green); margin-bottom: 15px;"></i>
                <h3 style="color: var(--dark-brown);">Add New Hotel</h3>
                <p style="color: var(--warm-brown); margin: 10px 0;">Add a new hotel to the system</p>
                <a href="hotels.php" class="cream-btn" style="margin-top: 15px;">
                    <i class="fas fa-plus"></i> Add Hotel
                </a>
            </div>
            
            <div class="admin-card">
                <i class="fas fa-bed" style="font-size: 2.5rem; color: var(--warm-brown); margin-bottom: 15px;"></i>
                <h3 style="color: var(--dark-brown);">Manage Rooms</h3>
                <p style="color: var(--warm-brown); margin: 10px 0;">Add/edit hotel rooms</p>
                <a href="rooms.php" class="cream-btn" style="margin-top: 15px; background-color: var(--warm-brown);">
                    <i class="fas fa-edit"></i> Manage Rooms
                </a>
            </div>
            
            <div class="admin-card">
                <i class="fas fa-calendar-alt" style="font-size: 2.5rem; color: #E91E63; margin-bottom: 15px;"></i>
                <h3 style="color: var(--dark-brown);">View Bookings</h3>
                <p style="color: var(--warm-brown); margin: 10px 0;">View all booking records</p>
                <a href="bookings.php" class="cream-btn" style="margin-top: 15px; background-color: #E91E63;">
                    <i class="fas fa-list"></i> View Bookings
                </a>
            </div>
        </div>
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

    // Show welcome notification on load
    window.addEventListener('DOMContentLoaded', () => {
        showToast('info', 'Selamat datang kembali, Admin! 👋');
    });
    </script>
</body>
</html>
