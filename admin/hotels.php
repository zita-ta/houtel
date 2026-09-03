<?php
include '../koneksi.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

function getAdminHotelImage($hotel_id) {
    $extensions = ['jpg', 'jpeg', 'png', 'gif'];
    foreach($extensions as $ext) {
        $path = '../assets/images/hotels/hotel_' . $hotel_id . '.' . $ext;
        if(file_exists($path)) {
            return 'hotel_' . $hotel_id . '.' . $ext;
        }
    }
    return null;
}

// Add Hotel
if(isset($_POST['add_hotel'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $rating = $_POST['rating'];
    $harga = $_POST['harga_permalam'];
    
    $insert = mysqli_query($koneksi, "INSERT INTO hotel (nama, alamat, rating, harga_permalam) VALUES ('$nama', '$alamat', '$rating', '$harga')");
    
    if($insert) {
        $hotel_id = mysqli_insert_id($koneksi);
        
        if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            
            if(in_array($ext, $allowed)) {
                $filename = 'hotel_' . $hotel_id . '.' . $ext;
                $path = '../assets/images/hotels/';
                
                if(!file_exists($path)) mkdir($path, 0777, true);
                
                move_uploaded_file($_FILES['foto']['tmp_name'], $path . $filename);
            }
        }
        
        $success = "Hotel added successfully!";
    }
}

// Edit Hotel
if(isset($_POST['edit_hotel'])) {
    $hotel_id = $_POST['hotel_id'];
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $rating = $_POST['rating'];
    $harga = $_POST['harga_permalam'];
    
    $update = mysqli_query($koneksi, "UPDATE hotel SET nama='$nama', alamat='$alamat', rating='$rating', harga_permalam='$harga' WHERE hotel_id='$hotel_id'");
    
    if($update) {
        if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            
            if(in_array($ext, $allowed)) {
                // Delete old images
                foreach(['jpg', 'jpeg', 'png', 'gif'] as $old_ext) {
                    $old_file = '../assets/images/hotels/hotel_' . $hotel_id . '.' . $old_ext;
                    if(file_exists($old_file)) unlink($old_file);
                }
                
                $filename = 'hotel_' . $hotel_id . '.' . $ext;
                $path = '../assets/images/hotels/';
                
                if(!file_exists($path)) mkdir($path, 0777, true);
                
                move_uploaded_file($_FILES['foto']['tmp_name'], $path . $filename);
            }
        }
        
        $success = "Hotel updated successfully!";
        header("Location: hotels.php?success=updated");
        exit();
    }
}

// Delete Hotel
if(isset($_GET['delete'])) {
    $hotel_id = $_GET['delete'];
    
    foreach(['jpg', 'jpeg', 'png', 'gif'] as $ext) {
        $file = '../assets/images/hotels/hotel_' . $hotel_id . '.' . $ext;
        if(file_exists($file)) unlink($file);
    }
    
    mysqli_query($koneksi, "DELETE FROM hotel WHERE hotel_id='$hotel_id'");
    $success = "Hotel deleted!";
}

// Get hotel for editing
$edit_hotel = null;
if(isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_result = mysqli_query($koneksi, "SELECT * FROM hotel WHERE hotel_id='$edit_id'");
    $edit_hotel = mysqli_fetch_assoc($edit_result);
}

$hotels = mysqli_query($koneksi, "SELECT * FROM hotel ORDER BY hotel_id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hotels - Hotel Zita</title>
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
                    <a href="hotels.php" class="active"><i class="fas fa-building"></i> Hotels</a>
                    <a href="rooms.php"><i class="fas fa-bed"></i> Rooms</a>
                    <a href="bookings.php"><i class="fas fa-calendar"></i> Bookings</a>
                    <a href="users.php"><i class="fas fa-users"></i> Users</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <h1 style="color: var(--dark-brown); margin: 30px 0;">
            <i class="fas fa-building"></i> Manage Hotels
        </h1>

        <?php if(isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['success']) && $_GET['success'] == 'updated'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Hotel updated successfully!
            </div>
        <?php endif; ?>

        <div class="cream-card" style="margin-bottom: 30px;">
            <h2 style="color: var(--dark-brown); margin-bottom: 20px;">
                <i class="fas fa-<?php echo $edit_hotel ? 'edit' : 'plus'; ?>"></i> 
                <?php echo $edit_hotel ? 'Edit Hotel' : 'Add New Hotel'; ?>
            </h2>
            
            <form method="POST" enctype="multipart/form-data">
                <?php if($edit_hotel): ?>
                    <input type="hidden" name="hotel_id" value="<?php echo $edit_hotel['hotel_id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Hotel Name</label>
                    <input type="text" name="nama" required value="<?php echo $edit_hotel ? htmlspecialchars($edit_hotel['nama']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="alamat" required style="min-height: 80px;"><?php echo $edit_hotel ? htmlspecialchars($edit_hotel['alamat']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Rating (0-5)</label>
                    <input type="number" name="rating" step="0.1" min="0" max="5" required value="<?php echo $edit_hotel ? $edit_hotel['rating'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Price per Night (Rp)</label>
                    <input type="number" name="harga_permalam" required value="<?php echo $edit_hotel ? $edit_hotel['harga_permalam'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Hotel Image</label>
                    <input type="file" name="foto" accept="image/*">
                    <?php if($edit_hotel): ?>
                        <?php $current_img = getAdminHotelImage($edit_hotel['hotel_id']); ?>
                        <?php if($current_img): ?>
                            <div style="margin-top: 10px;">
                                <img src="../assets/images/hotels/<?php echo $current_img; ?>" style="max-width: 200px; border-radius: 8px;">
                                <p style="color: var(--warm-brown); font-size: 0.9rem; margin-top: 5px;">Current image (upload new to replace)</p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <div style="display: flex; gap: 15px;">
                    <button type="submit" name="<?php echo $edit_hotel ? 'edit_hotel' : 'add_hotel'; ?>" class="cream-btn">
                        <i class="fas fa-<?php echo $edit_hotel ? 'save' : 'plus'; ?>"></i> 
                        <?php echo $edit_hotel ? 'Update Hotel' : 'Add Hotel'; ?>
                    </button>
                    <?php if($edit_hotel): ?>
                        <a href="hotels.php" class="cream-btn" style="background: #95a5a6;">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <?php if(!$edit_hotel): ?>
        <div class="cream-card">
            <h2 style="color: var(--dark-brown); margin-bottom: 20px;">
                <i class="fas fa-list"></i> All Hotels
            </h2>
            
            <?php if(mysqli_num_rows($hotels) > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Rating</th>
                            <th>Price/Night</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($hotel = mysqli_fetch_assoc($hotels)): ?>
                        <tr>
                            <td><?php echo $hotel['hotel_id']; ?></td>
                            <td>
                                <?php
                                $img = getAdminHotelImage($hotel['hotel_id']);
                                if($img): ?>
                                    <img src="../assets/images/hotels/<?php echo $img; ?>" style="width: 60px; height: 40px; object-fit: cover; border-radius: 5px;">
                                <?php else: ?>
                                    <i class="fas fa-image" style="font-size: 2rem; color: var(--soft-beige);"></i>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $hotel['nama']; ?></td>
                            <td><?php echo substr($hotel['alamat'], 0, 50); ?>...</td>
                            <td><?php echo $hotel['rating']; ?> <i class="fas fa-star" style="color: #FFA500;"></i></td>
                            <td>Rp <?php echo number_format($hotel['harga_permalam'], 0, ',', '.'); ?></td>
                            <td>
                                <a href="?edit=<?php echo $hotel['hotel_id']; ?>" style="color: var(--sage-green); margin-right: 15px;">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="?delete=<?php echo $hotel['hotel_id']; ?>" 
                                   onclick="return confirm('Delete this hotel?')"
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
            <p style="text-align: center; padding: 40px; color: var(--warm-brown);">No hotels yet</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <footer>
        <div class="container">
            <p><i class="fas fa-hotel"></i> Hotel Zita - Admin Panel</p>
            <p>&copy; 2024 Hotel Zita. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
