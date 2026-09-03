<?php
include '../koneksi.php';
include '../includes/auth.php';
checkUserLogin();

$user_id = $_SESSION['user_id'];

// Get all user bookings
$bookings = mysqli_query($koneksi, 
    "SELECT b.*, h.nama as hotel_name, r.tipe_kamar, re.tgl_check_in, re.tgl_check_out, re.jumlah_tamu,
            p.metode_pembayaran, p.tgl_pembayaran
     FROM Booking b
     JOIN Reservasi re ON b.booking_id = re.booking_id
     JOIN Room r ON re.room_id = r.room_id
     JOIN Hotel h ON r.hotel_id = h.hotel_id
     LEFT JOIN Pembayaran p ON b.booking_id = p.booking_id
     WHERE b.user_id = '$user_id'
     ORDER BY b.tgl_booking DESC"
);

if(!$bookings) {
    die("Query Error: " . mysqli_error($koneksi));
}

// Cancel booking
if(isset($_GET['cancel'])) {
    $booking_id = $_GET['cancel'];
    
    // Verify booking belongs to user
    $verify = mysqli_query($koneksi, 
        "SELECT * FROM Booking WHERE booking_id = '$booking_id' AND user_id = '$user_id'"
    );
    
    if(mysqli_num_rows($verify) > 0) {
        $update = mysqli_query($koneksi, 
            "UPDATE Booking SET status = 'Cancelled' WHERE booking_id = '$booking_id'"
        );
        
        if($update) {
            $success = "Booking cancelled successfully!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Hotel Zita</title>
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
                    <a href="my_bookings.php" class="active" >My Bookings</a>
                    <a href="../logout.php">Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <h1 style="color: var(--dark-brown); margin-bottom: 30px;">
            <i class="fas fa-calendar-alt"></i> My Bookings
        </h1>
        
        <!-- Messages -->
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Booking successful!
            </div>
        <?php endif; ?>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['payment'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Payment successful!
            </div>
        <?php endif; ?>
        
        <!-- Bookings List -->
        <?php if(mysqli_num_rows($bookings) > 0): ?>
            <?php while($booking = mysqli_fetch_assoc($bookings)): ?>
            <div class="cream-card" style="margin-bottom: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap;">
                    <div style="flex: 1;">
                        <h3>Booking #<?php echo $booking['booking_id']; ?></h3>
                        <p><strong>Hotel:</strong> <?php echo $booking['hotel_name']; ?></p>
                        <p><strong>Room:</strong> <?php echo $booking['tipe_kamar']; ?></p>
                        <p><strong>Check-in:</strong> <?php echo $booking['tgl_check_in']; ?></p>
                        <p><strong>Check-out:</strong> <?php echo $booking['tgl_check_out']; ?></p>
                        <p><strong>Guests:</strong> <?php echo $booking['jumlah_tamu']; ?></p>
                    </div>
                    
                    <div style="text-align: right; min-width: 200px;">
                        <p style="font-size: 1.8rem; font-weight: bold; color: var(--warm-brown); margin-bottom: 10px;">
                            Rp <?php echo number_format($booking['jumlah_total'], 0, ',', '.'); ?>
                        </p>
                        
                        <div style="margin-bottom: 15px;">
                            <span style="padding: 5px 15px; border-radius: 20px; 
                                background-color: <?php 
                                    if($booking['status'] == 'Confirmed') echo '#E8F5E9';
                                    elseif($booking['status'] == 'Pending') echo '#FFF3E0';
                                    elseif($booking['status'] == 'Completed') echo '#E3F2FD';
                                    else echo '#FFEBEE';
                                ?>; 
                                color: <?php 
                                    if($booking['status'] == 'Confirmed') echo '#2E7D32';
                                    elseif($booking['status'] == 'Pending') echo '#EF6C00';
                                    elseif($booking['status'] == 'Completed') echo '#1565C0';
                                    else echo '#C62828';
                                ?>;">
                                <?php echo $booking['status']; ?>
                            </span>
                        </div>
                        
                        <?php if($booking['metode_pembayaran']): ?>
                        <p>
                            <strong>Payment:</strong> <?php echo $booking['metode_pembayaran']; ?><br>
                            <small>Paid: <?php echo $booking['tgl_pembayaran'] ? date('d M Y', strtotime($booking['tgl_pembayaran'])) : '-'; ?></small>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div style="display: flex; gap: 15px; margin-top: 20px; flex-wrap: wrap;">
                    <?php if($booking['status'] == 'Confirmed' && !$booking['metode_pembayaran']): ?>
                        <a href="pembayaran.php?booking_id=<?php echo $booking['booking_id']; ?>" class="cream-btn">
                            <i class="fas fa-credit-card"></i> Make Payment
                        </a>
                    <?php endif; ?>

                        <?php if(($booking['status'] == 'Pending' || $booking['status'] == 'Confirmed') && !$booking['metode_pembayaran']): ?>
                        <a href="?cancel=<?php echo $booking['booking_id']; ?>" 
                           class="cream-btn" 
                           style="background: #e74c3c;"
                           onclick="return confirm('Are you sure you want to cancel this booking?')">
                            <i class="fas fa-times"></i> Cancel Booking
                        </a>
                    <?php endif; ?>
                    
                    <a href="booking_details.php?id=<?php echo $booking['booking_id']; ?>" class="cream-btn">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
            
        <?php else: ?>
            <div class="cream-card">
                <center>
                    <i class="fas fa-calendar-plus" style="font-size: 4rem; color: var(--soft-beige);"></i>
                    <h3 style="color: var(--dark-brown); margin-top: 20px;">No Bookings Found</h3>
                    <p style="color: var(--warm-brown);">You haven't made any bookings yet.</p>
                    <a href="hotels.php" class="cream-btn" style="margin-top: 20px;">
                        <i class="fas fa-search"></i> Browse Hotels
                    </a>
                </center>
            </div>
        <?php endif; ?>
    </div>

    <div id="toast-container"></div>

    <footer class="cream-footer">
        <div class="container">
            <p><i class="fas fa-spa"></i> Hotel Zita - Your Travel Partner</p>
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
    function showConfirmToast(message,onConfirm){
        const c=document.getElementById('toast-container');
        const t=document.createElement('div');
        t.className='toast warning';
        t.innerHTML=`<span class="toast-icon"><i class="fas fa-exclamation-triangle"></i></span><div class="toast-body"><div class="toast-title">Batalkan Booking?</div><div class="toast-msg">${message}</div><div style="display:flex;gap:8px;margin-top:10px;"><button id="cYes" style="background:white;color:#E65100;border:none;padding:5px 14px;border-radius:6px;font-weight:700;cursor:pointer;font-size:0.85rem;">Ya, Batalkan</button><button onclick="dismissToast(this.closest('.toast'))" style="background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.4);padding:5px 14px;border-radius:6px;cursor:pointer;font-size:0.85rem;">Tidak</button></div></div>`;
        t.querySelector('#cYes').addEventListener('click',()=>{dismissToast(t);showToast('info','Membatalkan booking...');setTimeout(onConfirm,400);});
        c.appendChild(t);
    }

    // PHP-triggered notifications
    window.addEventListener('DOMContentLoaded',()=>{
        <?php if(isset($_GET['success'])): ?>
        showToast('success','Booking berhasil dibuat!');
        <?php endif; ?>
        <?php if(isset($success)): ?>
        showToast('success','<?php echo addslashes($success); ?>');
        <?php endif; ?>
        <?php if(isset($_GET['payment'])): ?>
        showToast('success','Pembayaran berhasil!');
        <?php endif; ?>
    });

    // Intercept cancel links
    document.querySelectorAll('a[href*="cancel"]').forEach(link=>{
        link.addEventListener('click',function(e){
            e.preventDefault();
            const href=this.href;
            showConfirmToast('Booking yang dibatalkan tidak dapat dikembalikan.',()=>window.location.href=href);
        });
    });
    </script>
</body>
</html>
