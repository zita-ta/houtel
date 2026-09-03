<?php
include '../koneksi.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Add Room
if(isset($_POST['add_room'])) {
    $hotel_id = $_POST['hotel_id'];
    $tipe_kamar = mysqli_real_escape_string($koneksi, $_POST['tipe_kamar']);
    $harga = $_POST['harga'];
    
    $insert = mysqli_query($koneksi, "INSERT INTO room (hotel_id, tipe_kamar, harga, status_keberadaan) VALUES ('$hotel_id', '$tipe_kamar', '$harga', 'tersedia')");
    
    if($insert) {
        $success = "Room added successfully!";
    }
}

// Edit Room
if(isset($_POST['edit_room'])) {
    $room_id = $_POST['room_id'];
    $hotel_id = $_POST['hotel_id'];
    $tipe_kamar = mysqli_real_escape_string($koneksi, $_POST['tipe_kamar']);
    $harga = $_POST['harga'];
    $status = $_POST['status_keberadaan'];
    
    $update = mysqli_query($koneksi, "UPDATE room SET hotel_id='$hotel_id', tipe_kamar='$tipe_kamar', harga='$harga', status_keberadaan='$status' WHERE room_id='$room_id'");
    
    if($update) {
        $success = "Room updated successfully!";
        header("Location: rooms.php?success=updated");
        exit();
    }
}

// Delete Room
if(isset($_GET['delete'])) {
    mysqli_query($koneksi, "DELETE FROM room WHERE room_id='{$_GET['delete']}'");
    $success = "Room deleted!";
}

// Get room for editing
$edit_room = null;
if(isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_result = mysqli_query($koneksi, "SELECT * FROM room WHERE room_id='$edit_id'");
    $edit_room = mysqli_fetch_assoc($edit_result);
}

$rooms = mysqli_query($koneksi, "SELECT r.*, h.nama as hotel_nama 
    FROM room r 
    JOIN hotel h ON r.hotel_id = h.hotel_id 
    ORDER BY r.room_id ASC");
$hotels = mysqli_query($koneksi, "SELECT * FROM hotel ORDER BY nama");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - Hotel Zita</title>
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
                    <a href="rooms.php" class="active"><i class="fas fa-bed"></i> Rooms</a>
                    <a href="bookings.php"><i class="fas fa-calendar"></i> Bookings</a>
                    <a href="users.php"><i class="fas fa-users"></i> Users</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <h1 style="color: var(--dark-brown); margin: 30px 0;">
            <i class="fas fa-bed"></i> Manage Rooms
        </h1>

        <?php if(isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['success']) && $_GET['success'] == 'updated'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Room updated successfully!
            </div>
        <?php endif; ?>

        <div class="cream-card" style="margin-bottom: 30px;">
            <h2 style="color: var(--dark-brown); margin-bottom: 20px;">
                <i class="fas fa-<?php echo $edit_room ? 'edit' : 'plus'; ?>"></i> 
                <?php echo $edit_room ? 'Edit Room' : 'Add New Room'; ?>
            </h2>
            
            <form method="POST">
                <?php if($edit_room): ?>
                    <input type="hidden" name="room_id" value="<?php echo $edit_room['room_id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Hotel</label>
                    <select name="hotel_id" required>
                        <option value="">-- Select Hotel --</option>
                        <?php 
                        mysqli_data_seek($hotels, 0);
                        while($hotel = mysqli_fetch_assoc($hotels)): 
                        ?>
                        <option value="<?php echo $hotel['hotel_id']; ?>" <?php echo ($edit_room && $edit_room['hotel_id'] == $hotel['hotel_id']) ? 'selected' : ''; ?>>
                            <?php echo $hotel['nama']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Room Type</label>
                    <input type="text" name="tipe_kamar" required placeholder="Deluxe, Suite, Standard, etc." value="<?php echo $edit_room ? htmlspecialchars($edit_room['tipe_kamar']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Price per Night (Rp)</label>
                    <input type="number" name="harga" required value="<?php echo $edit_room ? $edit_room['harga'] : ''; ?>">
                </div>
                
                <?php if($edit_room): ?>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status_keberadaan" required>
                        <option value="tersedia" <?php echo ($edit_room['status_keberadaan'] == 'tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                        <option value="tidak tersedia" <?php echo ($edit_room['status_keberadaan'] == 'tidak tersedia') ? 'selected' : ''; ?>>Tidak Tersedia</option>
                    </select>
                </div>
                <?php endif; ?>
                
                <div style="display: flex; gap: 15px;">
                    <button type="submit" name="<?php echo $edit_room ? 'edit_room' : 'add_room'; ?>" class="cream-btn">
                        <i class="fas fa-<?php echo $edit_room ? 'save' : 'plus'; ?>"></i> 
                        <?php echo $edit_room ? 'Update Room' : 'Add Room'; ?>
                    </button>
                    <?php if($edit_room): ?>
                        <a href="rooms.php" class="cream-btn" style="background: #95a5a6;">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="cream-card">
            <h2 style="color: var(--dark-brown); margin-bottom: 20px;">
                <i class="fas fa-list"></i> All Rooms
            </h2>
            
            <?php if(mysqli_num_rows($rooms) > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Hotel</th>
                            <th>Room Type</th>
                            <th>Price/Night</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($room = mysqli_fetch_assoc($rooms)): ?>
                        <tr>
                            <td><?php echo $room['room_id']; ?></td>
                            <td><?php echo $room['hotel_nama']; ?></td>
                            <td><?php echo $room['tipe_kamar']; ?></td>
                            <td>Rp <?php echo number_format($room['harga'], 0, ',', '.'); ?></td>
                            <td>
                                <span style="padding: 5px 10px; border-radius: 5px; background: <?php echo $room['status_keberadaan'] == 'tersedia' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $room['status_keberadaan'] == 'tersedia' ? '#155724' : '#721c24'; ?>;">
                                    <?php echo ucfirst($room['status_keberadaan']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="?edit=<?php echo $room['room_id']; ?>" style="color: var(--sage-green); margin-right: 15px;">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="?delete=<?php echo $room['room_id']; ?>" 
                                   onclick="return confirm('Delete this room?')"
                                   style="color: #dc3545;">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p style="text-align: center; padding: 40px; color: var(--warm-brown);">No rooms yet</p>
            <?php endif; ?>
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
    function showConfirmToast(message, onConfirm) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = 'toast warning';
        toast.innerHTML = `
            <span class="toast-icon"><i class="fas fa-exclamation-triangle"></i></span>
            <div class="toast-body">
                <div class="toast-title">Konfirmasi Hapus</div>
                <div class="toast-msg">${message}</div>
                <div style="display:flex;gap:8px;margin-top:10px;">
                    <button id="confirmYes" style="background:white;color:#E65100;border:none;padding:5px 14px;border-radius:6px;font-weight:700;cursor:pointer;font-size:0.85rem;">Ya, Hapus</button>
                    <button onclick="dismissToast(this.closest('.toast'))" style="background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.4);padding:5px 14px;border-radius:6px;cursor:pointer;font-size:0.85rem;">Batal</button>
                </div>
            </div>`;
        toast.querySelector('#confirmYes').addEventListener('click', () => {
            dismissToast(toast);
            showToast('info', 'Menghapus kamar...');
            setTimeout(onConfirm, 400);
        });
        container.appendChild(toast);
    }

    // PHP-triggered notifications
    <?php if(isset($success)): ?>
    window.addEventListener('DOMContentLoaded', () => showToast('success', '<?php echo addslashes($success); ?>'));
    <?php endif; ?>
    <?php if(isset($_GET['success']) && $_GET['success'] == 'updated'): ?>
    window.addEventListener('DOMContentLoaded', () => showToast('success', 'Kamar berhasil diperbarui!'));
    <?php endif; ?>

    // Intercept delete links
    document.querySelectorAll('a[href*="delete"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.href;
            showConfirmToast('Kamar ini akan dihapus. Lanjutkan?', () => window.location.href = href);
        });
    });

    // Form submit feedback
    document.querySelector('form').addEventListener('submit', function() {
        const isEdit = this.querySelector('[name="edit_room"]');
        showToast('info', isEdit ? 'Menyimpan perubahan kamar...' : 'Menambahkan kamar baru...');
    });
    </script>
</body>
</html>
