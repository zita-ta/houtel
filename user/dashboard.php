<?php
include '../koneksi.php';
include '../includes/auth.php';
checkUserLogin();

$user_id = $_SESSION['user_id'];

// Get user data
$user_query = mysqli_query($koneksi, "SELECT * FROM user WHERE user_id = '$user_id'");
$user = mysqli_fetch_assoc($user_query);

// Get bookings count
$booking_count = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM Booking WHERE user_id = '$user_id'");
$booking_total = mysqli_fetch_assoc($booking_count)['total'];

// Get recent bookings
$bookings = mysqli_query($koneksi, 
    "SELECT b.*, h.nama as hotel_name, p.metode_pembayaran
     FROM Booking b 
     JOIN Reservasi r ON b.booking_id = r.booking_id 
     JOIN Room rm ON r.room_id = rm.room_id 
     JOIN Hotel h ON rm.hotel_id = h.hotel_id
     LEFT JOIN Pembayaran p ON b.booking_id = p.booking_id
     WHERE b.user_id = '$user_id' 
     ORDER BY b.tgl_booking DESC 
     LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Hotel Zita</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
<body>
    <!-- USER HEADER -->
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
                    <a href="dashboard.php" class="active">
                        <i class="fas fa-user"></i> <?php echo $user['nama']; ?>
                    </a>
                    <a href="my_bookings.php">My Bookings</a>
                    <a href="../logout.php">Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Welcome Card -->
        <div class="user-info-card">
            <h2>Welcome, <?php echo htmlspecialchars($user['nama']); ?>!</h2>
            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['telepon']); ?></p>
            
            <div style="display: flex; gap: 20px; margin-top: 25px;">
                <div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 12px;">
                    <h3 style="color: white; margin-bottom: 5px;"><?php echo $booking_total; ?></h3>
                    <p style="color: white; opacity: 0.9;">Total Bookings</p>
                </div>
            </div>
            
            <a href="hotels.php" class="cream-btn" style="margin-top: 25px; background-color: white; color: var(--sage-green);">
                <i class="fas fa-plus"></i> Book New Hotel
            </a>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-grid" style="margin-top: 40px;">
            <div class="cream-card" style="text-align: center;">
                <i class="fas fa-hotel" style="font-size: 2.5rem; color: var(--sage-green); margin-bottom: 15px;"></i>
                <h3>Browse Hotels</h3>
                <p>Explore our luxury hotels</p>
                <a href="hotels.php" class="cream-btn" style="margin-top: 15px;">View Hotels</a>
            </div>
            
            <div class="cream-card" style="text-align: center;">
                <i class="fas fa-calendar-check" style="font-size: 2.5rem; color: var(--warm-brown); margin-bottom: 15px;"></i>
                <h3>My Bookings</h3>
                <p>View your reservations</p>
                <a href="my_bookings.php" class="cream-btn" style="margin-top: 15px; background-color: var(--warm-brown);">View Bookings</a>
            </div>
            
            <div class="cream-card" style="text-align: center;">
                <i class="fas fa-user-edit" style="font-size: 2.5rem; color: var(--soft-pink); margin-bottom: 15px;"></i>
                <h3>Profile</h3>
                <p>Edit your information</p>
                <a href="profile.php" class="cream-btn" style="margin-top: 15px; background-color: var(--soft-pink);">Edit Profile</a>
            </div>
        </div>

        <!-- Recent Bookings -->
        <h2 style="margin-top: 50px; color: var(--dark-brown);">Recent Bookings</h2>
        
        <?php if(mysqli_num_rows($bookings) > 0): ?>
            <?php while($booking = mysqli_fetch_assoc($bookings)): ?>
            <div class="cream-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3>Booking #<?php echo $booking['booking_id']; ?></h3>
                    <span class="status-badge <?php echo ($booking['status'] == 'Confirmed') ? 'status-confirmed' : 'status-pending'; ?>">
                        <?php echo $booking['status']; ?>
                    </span>
                </div>
                <p><i class="fas fa-hotel"></i> <strong>Hotel:</strong> <?php echo $booking['hotel_name']; ?></p>
                <p><i class="fas fa-calendar"></i> <strong>Date:</strong> <?php echo $booking['tgl_booking']; ?></p>
                <p><i class="fas fa-money-bill-wave"></i> <strong>Total:</strong> Rp <?php echo number_format($booking['jumlah_total'], 0, ',', '.'); ?></p>
                
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <?php if($booking['status'] == 'Confirmed' && !$booking['metode_pembayaran']): ?>
                        <a href="pembayaran.php?booking_id=<?php echo $booking['booking_id']; ?>" class="cream-btn">
                            <i class="fas fa-credit-card"></i> Make Payment
                        </a>
                    <?php endif; ?>
                    <a href="booking_details.php?id=<?php echo $booking['booking_id']; ?>" class="cream-btn" style="background-color: var(--soft-beige); color: var(--dark-brown);">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
            
            <center style="margin-top: 30px;">
                <a href="my_bookings.php" class="cream-btn">
                    <i class="fas fa-list"></i> View All Bookings
                </a>
            </center>
            
        <?php else: ?>
            <div class="cream-card">
                <center>
                    <i class="fas fa-calendar-plus" style="font-size: 4rem; color: var(--soft-beige); margin-bottom: 20px;"></i>
                    <h3 style="color: var(--dark-brown);">No Bookings Yet</h3>
                    <p style="color: var(--warm-brown); margin: 15px 0;">Start your luxury journey with us</p>
                    <a href="hotels.php" class="cream-btn">
                        <i class="fas fa-search"></i> Browse Hotels
                    </a>
                </center>
            </div>
        <?php endif; ?>
    </div>

    <div id="toast-container"></div>

    <footer class="cream-footer">
        <div class="container">
            <p><i class="fas fa-spa"></i> Hotel Zita - User Dashboard</p>
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
        <?php if(isset($_GET['payment']) && $_GET['payment'] == 'success'): ?>
        showToast('success','Pembayaran berhasil! Terima kasih.');
        <?php endif; ?>
        <?php if(isset($_GET['booking']) && $_GET['booking'] == 'success'): ?>
        showToast('success','Booking berhasil dibuat!');
        <?php endif; ?>
        <?php if(isset($_GET['login']) && $_GET['login'] == 'success'): ?>
        showToast('success','Login berhasil! Selamat datang, <?php echo addslashes($user["nama"]); ?>! 👋');
        <?php endif; ?>
    });
    </script>
</body>
</html>