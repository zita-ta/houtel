<?php
include '../koneksi.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Update Status
if(isset($_GET['update'])) {
    $id = $_GET['update'];
    $status = $_GET['status'];
    mysqli_query($koneksi, "UPDATE booking SET status='$status' WHERE booking_id='$id'");
    
    if($status == 'Cancelled') {
        $res = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT room_id FROM reservasi WHERE booking_id='$id'"));
        if($res) {
            mysqli_query($koneksi, "UPDATE room SET status_keberadaan='tersedia' WHERE room_id='{$res['room_id']}'");
        }
    }
    
    $success = "Booking status updated!";
}

$bookings = mysqli_query($koneksi, "SELECT b.*, u.nama as user_nama, u.email, u.telepon, r.tgl_check_in, r.tgl_check_out, r.jumlah_tamu, ro.tipe_kamar, ro.harga as harga_kamar, h.nama as hotel_nama, h.alamat as hotel_alamat
    FROM booking b 
    JOIN user u ON b.user_id = u.user_id 
    JOIN reservasi r ON b.booking_id = r.booking_id 
    JOIN room ro ON r.room_id = ro.room_id 
    JOIN hotel h ON ro.hotel_id = h.hotel_id 
    ORDER BY b.booking_id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Hotel Zita</title>
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
    
    .receipt-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }
    
    .receipt-content {
        background-color: white;
        margin: 50px auto;
        padding: 0;
        width: 90%;
        max-width: 600px;
        border-radius: 12px;
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .receipt-header {
        background: linear-gradient(135deg, var(--sage-green), var(--warm-brown));
        color: white;
        padding: 20px;
        text-align: center;
    }
    
    .receipt-body {
        padding: 30px;
    }
    
    .receipt-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px dashed var(--soft-beige);
    }
    
    .receipt-total {
        background: var(--cream);
        padding: 15px;
        margin-top: 20px;
        border-radius: 8px;
    }
    
    .close-modal {
        color: white;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    /* ===== NOTIFICATION TOAST STYLES ===== */
    #toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
    }
    .toast {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
        border-radius: 12px;
        min-width: 300px;
        max-width: 400px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        pointer-events: all;
        animation: slideInRight 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        position: relative;
        overflow: hidden;
    }
    .toast::before {
        content: '';
        position: absolute;
        bottom: 0; left: 0;
        height: 3px;
        background: rgba(255,255,255,0.5);
        animation: toastProgress 3.5s linear forwards;
    }
    .toast.success { background: linear-gradient(135deg, #2E7D32, #43A047); color: white; }
    .toast.error   { background: linear-gradient(135deg, #C62828, #E53935); color: white; }
    .toast.warning { background: linear-gradient(135deg, #E65100, #FB8C00); color: white; }
    .toast.info    { background: linear-gradient(135deg, #1565C0, #1E88E5); color: white; }
    .toast-icon { font-size: 1.4rem; flex-shrink: 0; }
    .toast-body { flex: 1; }
    .toast-title { font-weight: 700; font-size: 0.95rem; margin-bottom: 2px; }
    .toast-msg   { font-size: 0.85rem; opacity: 0.9; }
    .toast-close {
        background: none; border: none; color: white;
        opacity: 0.7; cursor: pointer; font-size: 1.1rem;
        padding: 0; flex-shrink: 0; transition: opacity 0.2s;
    }
    .toast-close:hover { opacity: 1; }
    .toast.hiding { animation: slideOutRight 0.3s ease forwards; }
    @keyframes slideInRight {
        from { transform: translateX(120%); opacity: 0; }
        to   { transform: translateX(0);    opacity: 1; }
    }
    @keyframes slideOutRight {
        from { transform: translateX(0);    opacity: 1; }
        to   { transform: translateX(120%); opacity: 0; }
    }
    @keyframes toastProgress {
        from { width: 100%; }
        to   { width: 0%; }
    }

    @media print {
        body * {
            visibility: hidden;
        }
        .receipt-content, .receipt-content * {
            visibility: visible;
        }
        .receipt-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
        }
        .close-modal, .no-print {
            display: none !important;
        }
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
                    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="hotels.php"><i class="fas fa-building"></i> Hotels</a>
                    <a href="rooms.php"><i class="fas fa-bed"></i> Rooms</a>
                    <a href="bookings.php" class="active"><i class="fas fa-calendar"></i> Bookings</a>
                    <a href="users.php"><i class="fas fa-users"></i> Users</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <h1 style="color: var(--dark-brown); margin: 30px 0;">
            <i class="fas fa-calendar-alt"></i> Manage Bookings
        </h1>

        <?php if(isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <div class="cream-card">
            <?php if(mysqli_num_rows($bookings) > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Hotel</th>
                            <th>Room</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Guests</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($booking = mysqli_fetch_assoc($bookings)): ?>
                        <tr>
                            <td><?php echo $booking['booking_id']; ?></td>
                            <td>
                                <?php echo $booking['user_nama']; ?><br>
                                <small style="color: var(--warm-brown);"><?php echo $booking['email']; ?></small>
                            </td>
                            <td><?php echo $booking['hotel_nama']; ?></td>
                            <td><?php echo $booking['tipe_kamar']; ?></td>
                            <td><?php echo date('d M Y', strtotime($booking['tgl_check_in'])); ?></td>
                            <td><?php echo date('d M Y', strtotime($booking['tgl_check_out'])); ?></td>
                            <td><?php echo $booking['jumlah_tamu']; ?></td>
                            <td>Rp <?php echo number_format($booking['jumlah_total'], 0, ',', '.'); ?></td>
                            <td>
                                <span style="padding: 5px 10px; border-radius: 5px; background: 
                                    <?php 
                                    echo $booking['status'] == 'Confirmed' ? '#28a745' : 
                                        ($booking['status'] == 'Cancelled' ? '#dc3545' : '#ffc107'); 
                                    ?>; 
                                    color: white; font-size: 0.85rem;">
                                    <?php echo $booking['status']; ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="showReceipt(<?php echo htmlspecialchars(json_encode($booking)); ?>)" 
                                        style="color: var(--sage-green); background: none; border: none; cursor: pointer; margin-right: 10px;">
                                    <i class="fas fa-receipt"></i> Receipt
                                </button>
                                <?php if($booking['status'] == 'Pending'): ?>
                                <a href="?update=<?php echo $booking['booking_id']; ?>&status=Confirmed" 
                                   style="color: green; margin-right: 10px;"
                                   onclick="return confirm('Confirm this booking?')">
                                    <i class="fas fa-check"></i>
                                </a>
                                <a href="?update=<?php echo $booking['booking_id']; ?>&status=Cancelled" 
                                   style="color: red;"
                                   onclick="return confirm('Cancel this booking?')">
                                    <i class="fas fa-times"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p style="text-align: center; padding: 40px; color: var(--warm-brown);">No bookings yet</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div id="receiptModal" class="receipt-modal">
        <div class="receipt-content">
            <div class="receipt-header">
                <span class="close-modal no-print" onclick="closeReceipt()">&times;</span>
                <h2><i class="fas fa-hotel"></i> Hotel Zita</h2>
                <p>Booking Receipt</p>
            </div>
            <div class="receipt-body" id="receiptBody">
                <!-- Content will be filled by JavaScript -->
            </div>
            <div style="padding: 20px; text-align: center;" class="no-print">
                <button onclick="window.print()" class="cream-btn">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
                <button onclick="closeReceipt()" class="cream-btn" style="background: #95a5a6; margin-left: 10px;">
                    <i class="fas fa-times"></i> Close
                </button>
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
    // ===== TOAST NOTIFICATION SYSTEM =====
    const toastIcons = {
        success: '<i class="fas fa-check-circle"></i>',
        error:   '<i class="fas fa-times-circle"></i>',
        warning: '<i class="fas fa-exclamation-triangle"></i>',
        info:    '<i class="fas fa-info-circle"></i>'
    };
    const toastTitles = { success: 'Berhasil', error: 'Gagal', warning: 'Peringatan', info: 'Info' };

    function showToast(type, message, duration = 3500) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <span class="toast-icon">${toastIcons[type]}</span>
            <div class="toast-body">
                <div class="toast-title">${toastTitles[type]}</div>
                <div class="toast-msg">${message}</div>
            </div>
            <button class="toast-close" onclick="dismissToast(this.parentElement)">&times;</button>
        `;
        container.appendChild(toast);
        setTimeout(() => dismissToast(toast), duration);
    }

    function dismissToast(toast) {
        if (!toast || toast.classList.contains('hiding')) return;
        toast.classList.add('hiding');
        setTimeout(() => toast.remove(), 300);
    }

    // PHP-triggered notifications
    <?php if(isset($success)): ?>
    window.addEventListener('DOMContentLoaded', () => showToast('success', '<?php echo addslashes($success); ?>'));
    <?php endif; ?>

    // Confirm actions with toast feedback
    document.querySelectorAll('a[onclick*="confirm"]').forEach(link => {
        link.removeAttribute('onclick');
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.href;
            const isConfirm = href.includes('Confirmed');
            const isCancel  = href.includes('Cancelled');
            const msg = isConfirm ? 'Konfirmasi booking ini?' : isCancel ? 'Batalkan booking ini?' : 'Lanjutkan aksi ini?';
            showConfirmToast(msg, () => window.location.href = href);
        });
    });

    function showConfirmToast(message, onConfirm) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = 'toast info';
        toast.style.cssText = 'min-width:320px;';
        toast.innerHTML = `
            <span class="toast-icon"><i class="fas fa-question-circle"></i></span>
            <div class="toast-body">
                <div class="toast-title">Konfirmasi</div>
                <div class="toast-msg">${message}</div>
                <div style="display:flex;gap:8px;margin-top:10px;">
                    <button onclick="this.closest('.toast').dataset.confirmed='1';this.closest('.toast').remove();" 
                        style="background:white;color:#1565C0;border:none;padding:5px 14px;border-radius:6px;font-weight:700;cursor:pointer;font-size:0.85rem;">
                        Ya
                    </button>
                    <button onclick="dismissToast(this.closest('.toast'))"
                        style="background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.4);padding:5px 14px;border-radius:6px;cursor:pointer;font-size:0.85rem;">
                        Batal
                    </button>
                </div>
            </div>
        `;
        toast.querySelector('button:first-of-type').addEventListener('click', () => {
            showToast('info', 'Memproses...');
            setTimeout(onConfirm, 300);
        });
        container.appendChild(toast);
    }
    </script>

    <script>
    function showReceipt(booking) {
        const modal = document.getElementById('receiptModal');
        const body = document.getElementById('receiptBody');
        
        // Calculate days
        const checkin = new Date(booking.tgl_check_in);
        const checkout = new Date(booking.tgl_check_out);
        const days = Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24));
        
        body.innerHTML = `
            <div style="text-align: center; margin-bottom: 20px;">
                <h3 style="color: var(--dark-brown);">Booking #${booking.booking_id}</h3>
                <p style="color: var(--warm-brown);">Date: ${new Date(booking.tgl_booking).toLocaleDateString('id-ID')}</p>
            </div>
            
            <div style="margin: 20px 0;">
                <h4 style="color: var(--dark-brown); border-bottom: 2px solid var(--soft-beige); padding-bottom: 10px;">
                    Customer Information
                </h4>
                <div class="receipt-row">
                    <span>Name:</span>
                    <strong>${booking.user_nama}</strong>
                </div>
                <div class="receipt-row">
                    <span>Email:</span>
                    <span>${booking.email}</span>
                </div>
                <div class="receipt-row">
                    <span>Phone:</span>
                    <span>${booking.telepon}</span>
                </div>
            </div>
            
            <div style="margin: 20px 0;">
                <h4 style="color: var(--dark-brown); border-bottom: 2px solid var(--soft-beige); padding-bottom: 10px;">
                    Booking Details
                </h4>
                <div class="receipt-row">
                    <span>Hotel:</span>
                    <strong>${booking.hotel_nama}</strong>
                </div>
                <div class="receipt-row">
                    <span>Address:</span>
                    <span>${booking.hotel_alamat}</span>
                </div>
                <div class="receipt-row">
                    <span>Room Type:</span>
                    <span>${booking.tipe_kamar}</span>
                </div>
                <div class="receipt-row">
                    <span>Check-in:</span>
                    <span>${new Date(booking.tgl_check_in).toLocaleDateString('id-ID')}</span>
                </div>
                <div class="receipt-row">
                    <span>Check-out:</span>
                    <span>${new Date(booking.tgl_check_out).toLocaleDateString('id-ID')}</span>
                </div>
                <div class="receipt-row">
                    <span>Number of Nights:</span>
                    <span>${days} night(s)</span>
                </div>
                <div class="receipt-row">
                    <span>Number of Guests:</span>
                    <span>${booking.jumlah_tamu} guest(s)</span>
                </div>
                <div class="receipt-row">
                    <span>Status:</span>
                    <span style="color: ${booking.status == 'Confirmed' ? 'green' : booking.status == 'Cancelled' ? 'red' : 'orange'};">
                        <strong>${booking.status}</strong>
                    </span>
                </div>
            </div>
            
            <div class="receipt-total">
                <div class="receipt-row" style="border: none;">
                    <span>Price per Night:</span>
                    <span>Rp ${Number(booking.harga_kamar).toLocaleString('id-ID')}</span>
                </div>
                <div class="receipt-row" style="border: none;">
                    <span>Duration:</span>
                    <span>${days} night(s)</span>
                </div>
                <div class="receipt-row" style="border-top: 2px solid var(--warm-brown); padding-top: 15px; margin-top: 10px;">
                    <strong style="font-size: 1.2rem;">Total Amount:</strong>
                    <strong style="font-size: 1.2rem; color: var(--sage-green);">
                        Rp ${Number(booking.jumlah_total).toLocaleString('id-ID')}
                    </strong>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px dashed var(--soft-beige);">
                <p style="color: var(--warm-brown); font-size: 0.9rem;">
                    Thank you for choosing Hotel Zita<br>
                    For inquiries, please contact us at info@hotelzita.com
                </p>
            </div>
        `;
        
        modal.style.display = 'block';
    }
    
    function closeReceipt() {
        document.getElementById('receiptModal').style.display = 'none';
    }
    
    window.onclick = function(event) {
        const modal = document.getElementById('receiptModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    </script>
</body>
</html>
