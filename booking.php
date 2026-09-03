<?php
include 'koneksi.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if(!isset($_GET['hotel_id'])) {
    header('Location: hotels.php');
    exit();
}

$hotel_id = $_GET['hotel_id'];
$hotel = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM hotel WHERE hotel_id='$hotel_id'"));

if(isset($_POST['book'])) {
    $room_id = $_POST['room_id'];
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $jumlah_tamu = $_POST['jumlah_tamu'];
    
    // Calculate days
    $date1 = new DateTime($checkin);
    $date2 = new DateTime($checkout);
    $days = $date1->diff($date2)->days;
    
    // Get room price
    $room = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM room WHERE room_id='$room_id'"));
    $jumlah_total = $room['harga'] * $days;
    
    // Insert booking
    $insert_booking = mysqli_query($koneksi, "INSERT INTO booking (user_id, tgl_booking, jumlah_total, status) VALUES ('{$_SESSION['user_id']}', NOW(), '$jumlah_total', 'Pending')");
    
    if($insert_booking) {
        $booking_id = mysqli_insert_id($koneksi);
        
        // Insert reservasi
        $insert_reservasi = mysqli_query($koneksi, "INSERT INTO reservasi (booking_id, room_id, tgl_check_in, tgl_check_out, jumlah_tamu) VALUES ('$booking_id', '$room_id', '$checkin', '$checkout', '$jumlah_tamu')");
        
        if($insert_reservasi) {
            // Update room status
            mysqli_query($koneksi, "UPDATE room SET status_keberadaan='tidak tersedia' WHERE room_id='$room_id'");
            
            header('Location: user/dashboard.php?success=booking');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Room - Hotel Zita</title>
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
                    <a href="user/home.php">Home</a>
                    <a href="hotels.php" class="active">Hotels</a>
                    <a href="user/dashboard.php">Dashboard</a>
                    <a href="user/my_bookings.php">My Bookings</a>
                    <a href="logout.php">Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <h1 style="color: var(--dark-brown); margin: 30px 0;">
            <i class="fas fa-calendar-check"></i> Book Room
        </h1>

        <div class="cream-card">
            <h2 style="color: var(--dark-brown);"><?php echo $hotel['nama']; ?></h2>
            <p style="color: var(--warm-brown); margin-bottom: 30px;">
                <i class="fas fa-map-marker-alt"></i> <?php echo $hotel['alamat']; ?>
            </p>

            <form method="POST">
                <div class="form-group">
                    <label><i class="fas fa-bed"></i> Select Room</label>
                    <select name="room_id" required>
                        <option value="">-- Choose Room --</option>
                        <?php
                        $rooms = mysqli_query($koneksi, "SELECT * FROM room WHERE hotel_id='$hotel_id' AND status_keberadaan='tersedia'");
                        while($room = mysqli_fetch_assoc($rooms)):
                        ?>
                        <option value="<?php echo $room['room_id']; ?>">
                            <?php echo $room['tipe_kamar']; ?> 
                            (Rp <?php echo number_format($room['harga'], 0, ',', '.'); ?>/night)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-calendar"></i> Check-in Date</label>
                    <input type="date" name="checkin" required min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-calendar"></i> Check-out Date</label>
                    <input type="date" name="checkout" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-users"></i> Number of Guests</label>
                    <input type="number" name="jumlah_tamu" required min="1" value="1">
                </div>

                <button type="submit" name="book" class="cream-btn">
                    <i class="fas fa-check"></i> Confirm Booking
                </button>
            </form>
        </div>
    </div>

    <div id="toast-container"></div>

    <footer class="cream-footer">
        <div class="container">
            <p><i class="fas fa-spa"></i> Hotel Zita - Luxury Redefined</p>
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
    function dismissToast(t){if(!t||t.classList.contains('hiding'))return;t.classList.add('hiding');setTimeout(()=>t.remove(),300);}
    function showConfirmToast(title,msg,onConfirm){
        const c=document.getElementById('toast-container');
        const t=document.createElement('div');
        t.className='toast info';
        t.innerHTML=`<span class="toast-icon"><i class="fas fa-calendar-check"></i></span><div class="toast-body"><div class="toast-title">${title}</div><div class="toast-msg">${msg}</div><div style="display:flex;gap:8px;margin-top:10px;"><button id="cYes" style="background:white;color:#1565C0;border:none;padding:5px 14px;border-radius:6px;font-weight:700;cursor:pointer;font-size:0.85rem;">Konfirmasi</button><button onclick="dismissToast(this.closest('.toast'))" style="background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.4);padding:5px 14px;border-radius:6px;cursor:pointer;font-size:0.85rem;">Batal</button></div></div>`;
        t.querySelector('#cYes').addEventListener('click',()=>{dismissToast(t);showToast('info','Memproses booking...',5000);setTimeout(onConfirm,500);});
        c.appendChild(t);
    }

    // Validasi & konfirmasi form booking
    document.querySelector('form').addEventListener('submit',function(e){
        e.preventDefault();
        const room=this.querySelector('[name="room_id"]').value;
        const checkin=this.querySelector('[name="checkin"]').value;
        const checkout=this.querySelector('[name="checkout"]').value;
        const tamu=this.querySelector('[name="jumlah_tamu"]').value;

        if(!room){showToast('warning','Pilih tipe kamar terlebih dahulu!');return;}
        if(!checkin){showToast('warning','Pilih tanggal check-in!');return;}
        if(!checkout){showToast('warning','Pilih tanggal check-out!');return;}
        if(new Date(checkout)<=new Date(checkin)){showToast('error','Tanggal check-out harus setelah check-in!');return;}

        const d1=new Date(checkin),d2=new Date(checkout);
        const nights=Math.round((d2-d1)/(1000*60*60*24));
        const roomText=this.querySelector('[name="room_id"] option:checked').text;

        const form=this;
        showConfirmToast(
            'Konfirmasi Booking',
            `<strong>${roomText}</strong><br>Check-in: ${checkin} → Check-out: ${checkout}<br>${nights} malam, ${tamu} tamu`,
            ()=>{
                const hidden=document.createElement('input');
                hidden.type='hidden';
                hidden.name='book';
                hidden.value='1';
                form.appendChild(hidden);
                form.submit();
            }
        );
    });
    </script>
</body>
</html>
