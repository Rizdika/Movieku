<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'Administrator' && $_SESSION['role'] !== 'Editor')) {
    header("Location: login.php");
    exit;
}

include '../koneksi.php';

$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Ambil data pengguna dari database
$sql = "SELECT * FROM user WHERE username = ?";
$stmt = $koneksi->prepare($sql);

if ($stmt === false) {
    die('Error preparing statement: ' . $koneksi->error);
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$foto_profil = "../img/foto_profile/" . $user['foto'];
if (!file_exists($foto_profil) || empty($user['foto'])) {
    $foto_profil = "https://via.placeholder.com/100";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];
    $new_password = $_POST['password'];
    $foto_profil_baru = $user['foto']; // Default to existing photo

    // Proses upload foto profil baru
    if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profilePicture']['tmp_name'];
        $file_name = basename($_FILES['profilePicture']['name']);
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array(strtolower($file_ext), $allowed_ext)) {
            $new_file_name = $new_username . '.' . $file_ext;
            $target_path = "../img/foto_profile/" . $new_file_name;

            if (move_uploaded_file($file_tmp, $target_path)) {
                $foto_profil_baru = $new_file_name;
            } else {
                echo "<script>alert('Gagal mengunggah foto profil.');</script>";
            }
        } else {
            echo "<script>alert('Format file tidak didukung. Hanya JPG, JPEG, PNG, dan GIF.');</script>";
        }
    }

    // Update database
    $update_sql = "UPDATE user SET username = ?, email = ?, foto = ?";

    if (!empty($new_password)) {
        $update_sql .= ", password = ?";
    }
    $update_sql .= " WHERE username = ?";

    $stmt = $koneksi->prepare($update_sql);
    if ($stmt === false) {
        die('Error preparing update statement: ' . $koneksi->error);
    }

    if (!empty($new_password)) {
        $stmt->bind_param("sssss", $new_username, $new_email, $foto_profil_baru, $new_password, $username);
    } else {
        $stmt->bind_param("ssss", $new_username, $new_email, $foto_profil_baru, $username);
    }

    if ($stmt->execute()) {
        $_SESSION['update_success'] = true;
        $_SESSION['username'] = $new_username;
        header("Location: pengaturanProfile.php");
        exit;
    } else {
        echo "<script>alert('Terjadi kesalahan saat memperbarui profil.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/dashboardStyle.css">
</head>
<body>
    <nav>
        <div class="profile">
            <img src="<?php echo $foto_profil; ?>" alt="Profile Picture">
            <h3><?php echo $username; ?></h3>
            <p><?php echo $role; ?></p>
        </div>
        <a href="dashboard.php"><i class="fas fa-home"></i> Beranda</a>
        <a href="manageFilm.php"><i class="fas fa-film"></i> Daftar Film</a>
        <?php if ($_SESSION['role'] === 'Administrator'): ?>
            <a href="manageUsers.php"><i class="fas fa-users"></i> Daftar User</a>
        <?php endif; ?>
        <a href="pengaturanProfile.php"><i class="fas fa-user-cog"></i> Pengaturan Profile</a>
        <a href="logout.php" style="margin-top: auto; background-color: #f44336;" class="d-flex align-items-center justify-content-start"><i class="fas fa-sign-out-alt" style="margin-right: 10px;"></i> Logout</a>
    </nav>
    <div class="content">
        <header>
            <h1>Dashboard Admin</h1>
            <button onclick="window.location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </header>
        <section>
            <div class="card">
                <h2>Pengaturan Profil</h2>
                <form id="profileForm" method="POST" enctype="multipart/form-data" style="margin-top: 20px;">
                    <!-- Edit Foto Profil -->
                    <div class="form-group" style="margin-bottom: 20px; text-align: center;">
                        <label for="profilePicture" style="font-weight: 500; display: block; margin-bottom: 10px;">Foto Profil</label>
                        <img src="<?php echo $foto_profil; ?>" id="profilePreview" alt="Foto Profil" style="border-radius: 50%; width: 150px; height: 150px; object-fit: cover; margin-bottom: 10px;">
                        <input type="file" id="profilePicture" class="form-control" accept="image/*" name="profilePicture" style="max-width: 300px; margin: 0 auto;">
                    </div>

                    <!-- Edit Username -->
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="username" style="font-weight: 500;">Username</label>
                        <input type="text" id="username" class="form-control" placeholder="Masukkan username baru" value="<?php echo $user['username']; ?>" name="username">
                    </div>

                    <!-- Edit Email -->
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="email" style="font-weight: 500;">Email</label>
                        <input type="email" id="email" class="form-control" placeholder="Masukkan email baru" value="<?php echo $user['email']; ?>" name="email">
                    </div>

                    <!-- Edit Password -->
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="password" style="font-weight: 500;">Password Baru</label>
                        <input type="password" id="password" class="form-control" placeholder="Masukkan password baru" name="password">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="confirmPassword" style="font-weight: 500;">Konfirmasi Password</label>
                        <input type="password" id="confirmPassword" class="form-control" placeholder="Konfirmasi password baru" name="confirmPassword">
                    </div>

                    <!-- Tombol Simpan -->
                    <button type="submit" class="btn btn-add-user" style="margin-top: 10px;"><i class="fas fa-save"></i> Simpan Perubahan</button>
                </form>
            </div>
        </section>
    </div>

    <?php
    if (isset($_SESSION['update_success']) && $_SESSION['update_success'] === true) {
        echo "<script>
                alert('Profil berhasil diperbarui!');
                window.location.reload();
              </script>";
        unset($_SESSION['update_success']);
    }
    ?>

    <script>
        const profilePictureInput = document.getElementById('profilePicture');
        const profilePreview = document.getElementById('profilePreview');

        profilePictureInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    profilePreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
