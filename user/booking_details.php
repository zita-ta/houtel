<?php
include '../koneksi.php';
include '../includes/auth.php';
checkUserLogin();

$booking_id = isset($_GET['id']) ? $_GET['id'] : '';

// Ambil data booking lengkap
$query = mysqli_query($koneksi,
    "SELECT b.*, u.nama as user_name, u.email,
            r.tipe_kamar, r.harga,
            h.nama as hotel_nama, h.alamat as hotel_alamat,
            res.tgl_check_in, res.tgl_check_out, res.jumlah_tamu,
            p.metode_pembayaran, p.tgl_pembayaran, p.jumlah as jumlah_bayar
     FROM Booking b
     JOIN user u ON b.user_id = u.user_id
     JOIN reservasi res ON b.booking_id = res.booking_id
     JOIN room r ON res.room_id = r.room_id
     JOIN Hotel h ON r.hotel_id = h.hotel_id
     LEFT JOIN Pembayaran p ON b.booking_id = p.booking_id
     WHERE b.booking_id = '$booking_id'
     AND b.user_id = '{$_SESSION['user_id']}'"
);
$booking = mysqli_fetch_assoc($query);

if(!$booking) {
    header("Location: my_bookings.php");
    exit();
}

// Hitung durasi menginap
$checkin = new DateTime($booking['tgl_check_in']);
$checkout = new DateTime($booking['tgl_check_out']);
$days = $checkin->diff($checkout)->days;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - Hotel Zita</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .detail-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(139, 69, 19, 0.1);
        }

        .detail-section h3 {
            color: var(--dark-brown);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--soft-beige);
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--soft-beige);
            color: var(--warm-brown);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-row strong {
            color: var(--dark-brown);
        }

        .status-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .status-pending {
            background: #FFF3CD;
            color: #856404;
        }

        .status-paid {
            background: #D1FAE5;
            color: #065F46;
        }

        .status-cancelled {
            background: #FEE2E2;
            color: #991B1B;
        }

        .total-amount {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--sage-green);
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
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
        <div style="margin-bottom: 25px;">
            <a href="my_bookings.php" style="color: var(--warm-brown); text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to My Bookings
            </a>
        </div>

        <h1 style="color: var(--dark-brown); margin-bottom: 30px;">
            <i class="fas fa-receipt"></i> Booking Details
        </h1>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px;">
            <div>
                <!-- Booking Info -->
                <div class="detail-section">
                    <h3><i class="fas fa-info-circle"></i> Booking Information</h3>
                    <div class="detail-row">
                        <span>Booking ID</span>
                        <strong>#<?php echo $booking['booking_id']; ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Booking Date</span>
                        <strong><?php echo date('d M Y', strtotime($booking['tgl_booking'])); ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Status</span>
                        <span class="status-badge status-<?php echo strtolower($booking['status']); ?>">
                            <?php echo $booking['status']; ?>
                        </span>
                    </div>
                </div>

                <!-- Hotel Info -->
                <div class="detail-section">
                    <h3><i class="fas fa-hotel"></i> Hotel & Room</h3>
                    <div class="detail-row">
                        <span>Hotel</span>
                        <strong><?php echo htmlspecialchars($booking['hotel_nama']); ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Address</span>
                        <strong><?php echo htmlspecialchars($booking['hotel_alamat']); ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Room Type</span>
                        <strong><?php echo htmlspecialchars($booking['tipe_kamar']); ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Price per Night</span>
                        <strong>Rp <?php echo number_format($booking['harga'], 0, ',', '.'); ?></strong>
                    </div>
                </div>

                <!-- Stay Info -->
                <div class="detail-section">
                    <h3><i class="fas fa-calendar-alt"></i> Stay Details</h3>
                    <div class="detail-row">
                        <span>Check-in</span>
                        <strong><?php echo date('d M Y', strtotime($booking['tgl_check_in'])); ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Check-out</span>
                        <strong><?php echo date('d M Y', strtotime($booking['tgl_check_out'])); ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Duration</span>
                        <strong><?php echo $days; ?> night(s)</strong>
                    </div>
                    <div class="detail-row">
                        <span>Guests</span>
                        <strong><?php echo $booking['jumlah_tamu']; ?> person(s)</strong>
                    </div>
                </div>
            </div>

            <div>
                <!-- Payment Summary -->
                <div class="detail-section">
                    <h3><i class="fas fa-credit-card"></i> Payment Summary</h3>
                    <div class="detail-row">
                        <span>Room × <?php echo $days; ?> night(s)</span>
                        <strong>Rp <?php echo number_format($booking['harga'] * $days, 0, ',', '.'); ?></strong>
                    </div>
                    <div class="detail-row" style="margin-top: 10px;">
                        <span>Total</span>
                        <span class="total-amount">Rp <?php echo number_format($booking['jumlah_total'], 0, ',', '.'); ?></span>
                    </div>

                    <?php if($booking['metode_pembayaran']): ?>
                    <div class="detail-row">
                        <span>Payment Method</span>
                        <strong><?php echo $booking['metode_pembayaran']; ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Payment Date</span>
                        <strong><?php echo date('d M Y', strtotime($booking['tgl_pembayaran'])); ?></strong>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Actions -->
                <div class="detail-section">
                    <h3><i class="fas fa-cogs"></i> Actions</h3>
                    <div class="action-buttons" style="flex-direction: column;">
                        <?php if($booking['status'] == 'Pending'): ?>
                            <a href="pembayaran.php?booking_id=<?php echo $booking['booking_id']; ?>" class="cream-btn" style="text-align: center;">
                                <i class="fas fa-credit-card"></i> Pay Now
                            </a>
                        <?php endif; ?>
                        <a href="my_bookings.php" class="cream-btn" style="text-align: center; background: var(--soft-beige); color: var(--dark-brown);">
                            <i class="fas fa-list"></i> All Bookings
                        </a>
                    </div>
                </div>
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

    // Notifikasi status booking
    window.addEventListener('DOMContentLoaded',()=>{
        const status = '<?php echo $booking["status"]; ?>';
        if(status === 'Pending') showToast('warning', 'Booking ini menunggu konfirmasi pembayaran.');
        else if(status === 'Confirmed') showToast('info', 'Booking dikonfirmasi! Silakan lanjutkan pembayaran.');
        else if(status === 'Cancelled') showToast('error', 'Booking ini telah dibatalkan.');
        else if(status === 'Paid') showToast('success', 'Pembayaran telah selesai. Selamat menikmati!');
    });
    </script>
</body>
</html>
