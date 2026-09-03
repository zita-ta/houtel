<?php
include '../koneksi.php';
include '../includes/auth.php';
checkUserLogin();

// Function to get hotel image path
function getHotelImage($hotel_id) {
    $extensions = ['jpg', 'jpeg', 'png', 'gif'];
    foreach($extensions as $ext) {
        $path = '../assets/images/hotels/hotel_' . $hotel_id . '.' . $ext;
        if(file_exists($path)) {
            return 'hotel_' . $hotel_id . '.' . $ext;
        }
    }
    return null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Zita - Home</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hotel-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }

        .hotel-placeholder {
            width: 100%;
            height: 220px;
            background: linear-gradient(135deg, var(--soft-beige), var(--cream));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--warm-brown);
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
    <!-- HEADER -->
    <header class="cream-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-spa"></i>
                    <span>Hotel Zita</span>
                </div>
                <nav class="nav-links">
                    <a href="home.php" class="active">Home</a>
                    <a href="hotels.php">Hotels</a>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="my_bookings.php">My Bookings</a>
                    <a href="../logout.php">Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- HERO -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Find Your Peaceful Escape</h1>
                <p>Experience luxury and tranquility in our calm cream-themed hotels. Your perfect getaway awaits.</p>
                <a href="hotels.php" class="cream-btn">Explore Hotels</a>
            </div>
        </div>
    </section>

    <!-- FEATURED HOTELS -->
    <section class="container">
        <h2 style="text-align: center; margin-bottom: 40px; color: var(--dark-brown);">Featured Hotels</h2>
        <div class="hotel-grid">
            <?php
            $sql = "SELECT * FROM Hotel LIMIT 3";
            $result = mysqli_query($koneksi, $sql);
            while($hotel = mysqli_fetch_assoc($result)):
                $hotel_image = getHotelImage($hotel['hotel_id']);
            ?>
            <div class="hotel-card">
                <?php if($hotel_image): ?>
                    <img src="../assets/images/hotels/<?php echo $hotel_image; ?>"
                         alt="<?php echo htmlspecialchars($hotel['nama']); ?>"
                         class="hotel-image">
                <?php else: ?>
                    <div class="hotel-placeholder">
                        <i class="fas fa-hotel"></i>
                    </div>
                <?php endif; ?>

                <div class="hotel-card-content">
                    <h3><?php echo htmlspecialchars($hotel['nama']); ?></h3>
                    <div class="rating">
                        <?php
                        $rating = $hotel['rating'];
                        for($i = 1; $i <= 5; $i++):
                            if($i <= floor($rating)): ?>
                                <i class="fas fa-star"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php endif;
                        endfor; ?>
                        <span>(<?php echo number_format($rating, 1); ?>)</span>
                    </div>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['alamat']); ?></p>
                    <p class="price">Rp <?php echo number_format($hotel['harga_permalam'], 0, ',', '.'); ?>/night</p>
                    <a href="../booking.php?hotel_id=<?php echo $hotel['hotel_id']; ?>" class="cream-btn">Book Now</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- FEATURES -->
    <section class="container" style="margin-top: 80px;">
        <div class="dashboard-grid">
            <div class="cream-card">
                <center><i class="fas fa-heart" style="font-size: 3rem; color: var(--soft-pink); margin-bottom: 20px;"></i></center>
                <h3 style="text-align: center;">Luxury Experience</h3>
                <p style="text-align: center;">Experience ultimate comfort in our cream-themed luxurious rooms.</p>
            </div>
            <div class="cream-card">
                <center><i class="fas fa-leaf" style="font-size: 3rem; color: var(--sage-green); margin-bottom: 20px;"></i></center>
                <h3 style="text-align: center;">Calm Environment</h3>
                <p style="text-align: center;">Tranquil surroundings designed for your peace and relaxation.</p>
            </div>
            <div class="cream-card">
                <center><i class="fas fa-concierge-bell" style="font-size: 3rem; color: var(--warm-brown); margin-bottom: 20px;"></i></center>
                <h3 style="text-align: center;">Premium Service</h3>
                <p style="text-align: center;">24/7 premium service to ensure your stay is perfect.</p>
            </div>
        </div>
    </section>

    <div id="toast-container"></div>

    <!-- FOOTER -->
    <footer class="cream-footer">
        <div class="container">
            <p><i class="fas fa-spa"></i> Hotel Zita - Calm Cream Luxury</p>
            <p>&copy; 2024 Hotel Zita. All rights reserved.</p>
            <p style="margin-top: 20px; color: var(--warm-brown);">
                <i class="fas fa-phone"></i> (021) 1234-5678 | 
                <i class="fas fa-envelope"></i> info@hotelzita.com
            </p>
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
        showToast('info','Selamat datang di Hotel Zita, <?php echo addslashes($user["nama"] ?? ""); ?>! 👋');
    });
    </script>
</body>
</html>
