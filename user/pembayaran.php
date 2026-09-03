<?php
include '../koneksi.php';
include '../includes/auth.php';
checkUserLogin();

$booking_id = isset($_GET['booking_id']) ? $_GET['booking_id'] : '';

// Ambil data booking
$booking_query = mysqli_query($koneksi, 
    "SELECT b.*, u.nama as user_name 
     FROM Booking b 
     JOIN user u ON b.user_id = u.user_id 
     WHERE b.booking_id = '$booking_id' 
     AND b.user_id = '{$_SESSION['user_id']}'"
);
$booking = mysqli_fetch_assoc($booking_query);

if(isset($_POST['pay'])) {
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $jumlah = $booking['jumlah_total'];
    
    $insert_payment = mysqli_query($koneksi,
        "INSERT INTO Pembayaran (booking_id, jumlah, tgl_pembayaran, metode_pembayaran) 
         VALUES ('$booking_id', '$jumlah', CURDATE(), '$metode_pembayaran')"
    );
    
    if($insert_payment) {
        mysqli_query($koneksi, "UPDATE Booking SET status = 'Paid' WHERE booking_id = '$booking_id'");
        header("Location: dashboard.php?payment=success");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Hotel Zita</title>
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
    <header class="calm-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-hotel"></i>
                    <span>Hotel Zita</span>
                </div>
                <nav class="nav-links">
                    <a href="home.php">Home</a>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="../logout.php">Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <?php if($booking): ?>
        <div class="calm-form">
            <h2><i class="fas fa-credit-card"></i> Payment</h2>
            
            <div class="dashboard-card" style="margin-bottom: 30px;">
                <h3>Payment Details</h3>
                <p><strong>Booking ID:</strong> #<?php echo $booking['booking_id']; ?></p>
                <p><strong>Customer:</strong> <?php echo $booking['user_name']; ?></p>
                <p><strong>Total Amount:</strong> Rp <?php echo number_format($booking['jumlah_total'], 0, ',', '.'); ?></p>
                <p><strong>Booking Date:</strong> <?php echo $booking['tgl_booking']; ?></p>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="metode_pembayaran" required>
                        <option value="">-- Select Payment Method --</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="E-Wallet">E-Wallet</option>
                        <option value="Cash">Cash</option>
                    </select>
                </div>
                
                <button type="submit" name="pay" class="calm-btn" style="width: 100%; padding: 15px;">
                    <i class="fas fa-check-circle"></i> Confirm Payment
                </button>
            </form>
        </div>
        <?php else: ?>
        <div class="dashboard-card">
            <p style="text-align: center; padding: 40px;">
                <i class="fas fa-exclamation-circle" style="font-size: 3rem; color: #FFB300;"></i><br><br>
                Booking not found or you don't have permission to access this payment.
            </p>
            <center><a href="dashboard.php" class="calm-btn">Back to Dashboard</a></center>
        </div>
        <?php endif; ?>
    </div>

    <div id="toast-container"></div>

    <footer class="calm-footer">
        <div class="container">
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

    // Konfirmasi sebelum submit pembayaran
    const form = document.querySelector('form');
    if(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const metode = this.querySelector('[name="metode_pembayaran"]').value;
            if(!metode) {
                showToast('warning', 'Pilih metode pembayaran terlebih dahulu!');
                return;
            }
            showToast('info', `Memproses pembayaran via ${metode}...`, 5000);
            setTimeout(()=>{
                const hidden=document.createElement('input');
                hidden.type='hidden';
                hidden.name='pay';
                hidden.value='1';
                form.appendChild(hidden);
                form.submit();
            }, 800);
        });
    }

    window.addEventListener('DOMContentLoaded',()=>{
        <?php if(!$booking): ?>
        showToast('error','Booking tidak ditemukan atau akses tidak diizinkan.');
        <?php endif; ?>
    });
    </script>
</body>
</html>