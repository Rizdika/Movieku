<?php
include '../koneksi.php';

session_start();

// Pastikan hanya admin yang dapat mengakses halaman ini
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Tentukan lokasi folder foto profil
$folder_foto_profil = "../img/foto_profile/";
$foto_profil_path = $folder_foto_profil . $username;

// Cari file foto profil berdasarkan pola nama (mendukung berbagai format gambar)
$foto_formats = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'JPG', 'JPEG', 'PNG', 'GIF', 'WEBP'];
$foto_profil = null;
foreach ($foto_formats as $format) {
    $foto = glob($foto_profil_path . '.' . $format);
    if (!empty($foto)) {
        $foto_profil = $foto[0];
        break;
    }
}

// Jika tidak ditemukan, gunakan gambar placeholder default
if (!$foto_profil) {
    $foto_profil = "https://via.placeholder.com/100";
}

// Ambil data pengguna dari database
$sql = "SELECT u.id, u.username, u.email, r.role, u.foto
        FROM user u
        JOIN role r ON u.id_role = r.id_role";
$result = $koneksi->query($sql);

if (!$result) {
    die("Error: " . $koneksi->error);
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
    <style>
        /* Styling tambahan untuk modal form */
        .modal-content {
            border-radius: 8px;
            padding: 20px;
        }
        .modal-header {
            border-bottom: none;
        }
        .modal-body input, .modal-body select {
            margin-bottom: 10px;
        }
        .modal-footer button {
            width: 100px;
        }
    </style>
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
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h2>Daftar User</h2>
                    <!-- Button Add User -->
                    <button class="btn btn-add-user" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-user-plus"></i> Add User
                    </button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Foto</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($row = $result->fetch_assoc()) {
                            $foto_profile = "../img/foto_profile/" . $row['foto'];
                            $foto_asli = $row['foto']; // Nama file asli

                            if (!file_exists($foto_profile)) {
                                $foto_profile = "https://via.placeholder.com/100";
                                $foto_asli = ""; // Kosongkan jika tidak ada file
                            }
                            echo "<tr>";
                            echo "<td>{$no}</td>";
                            echo "<td>{$row['username']}</td>";
                            echo "<td>{$row['email']}</td>";
                            echo "<td>{$row['role']}</td>";
                            echo "<td><img src='{$foto_profile}' alt='Foto Profil' style='width: 50px; height: 50px; border-radius: 50%;'></td>";
                            echo "<td>
                                    <a href='#' class='btn btn-edit' data-bs-toggle='modal' data-bs-target='#editUserModal' 
                                        data-id='{$row['id']}' 
                                        data-username='{$row['username']}' 
                                        data-email='{$row['email']}' 
                                        data-role='{$row['role']}' 
                                        data-foto='{$foto_asli}'>
                                        <i class='fas fa-edit'></i>Edit
                                    </a>
                                    <a href='#' class='btn btn-delete' 
                                    data-id='{$row['id']}>' 
                                    data-username='{$row['username']}'>
                                    <i class='fas fa-trash'></i> Delete
                                    </a>
                                </td>";
                            echo "</tr>";
                            $no++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- Modal Add User -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="crud/proses_add_user.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="Administrator">Administrator</option>
                                <option value="Editor">Editor</option>
                            </select>
                        </div>
                        <!-- Field untuk Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="foto_profile" class="form-label">Foto Profil</label>
                            <input type="file" class="form-control" id="foto_profile" name="foto_profile" accept="image/*">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Kembali
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Tambah
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="crud/proses_edit_user.php" method="POST" enctype="multipart/form-data">
                        <!-- Field untuk menyimpan ID user -->
                        <input type="hidden" id="edit_id_user" name="id_user">

                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_role" class="form-label">Role</label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="Administrator">Administrator</option>
                                <option value="Editor">Editor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_foto_profile" class="form-label">Foto Profil</label>
                            <input type="file" class="form-control" id="edit_foto_profile" name="foto_profile" accept="image/*">
                            <!-- Hidden input untuk menyimpan nama foto lama -->
                            <input type="hidden" id="edit_foto_lama" name="foto_lama">
                            <div class="mt-2">
                                <img id="edit_preview_foto" src="#" alt="Preview Foto" style="width: 100px; height: 100px; border-radius: 50%; display: none;">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Kembali
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // JavaScript untuk mengisi data ke dalam form edit user
        document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const username = this.getAttribute('data-username');
            const email = this.getAttribute('data-email');
            const role = this.getAttribute('data-role');
            const foto = this.getAttribute('data-foto'); // Ambil data-foto

            document.getElementById('edit_id_user').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            
            // Isi input hidden foto lama
            document.getElementById('edit_foto_lama').value = foto;

            // Perbarui preview foto
            const preview = document.getElementById('edit_preview_foto');
            if (foto) {
                const placeholderURL = "https://via.placeholder.com/100";
                preview.src = foto ? `../img/foto_profile/${foto}` : placeholderURL;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        });
    });

        document.querySelectorAll('.btn-delete').forEach(button => { 
            button.addEventListener('click', function (event) {
                event.preventDefault();

                const userId = this.getAttribute('data-id'); // Ambil ID pengguna
                const username = this.getAttribute('data-username'); // Ambil username pengguna

                const confirmDelete = confirm(`Apakah Anda yakin ingin menghapus pengguna "${username}"?`);
                if (confirmDelete) {
                    // Redirect ke proses delete
                    window.location.href = `crud/proses_delete_user.php?id=${userId}`;
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);

            // Fungsi untuk menampilkan alert berdasarkan parameter URL
            const showAlert = (message) => {
                alert(message);
            };

            // Cek parameter untuk Add User
            if (urlParams.has('successAdd')) {
                showAlert('Pengguna berhasil ditambahkan!');
            } else if (urlParams.has('errorAdd')) {
                showAlert('Gagal menambahkan pengguna.');
            }

            // Cek parameter untuk Edit User
            if (urlParams.has('successEdit')) {
                showAlert('Pengguna berhasil diperbarui!');
            } else if (urlParams.has('errorEdit')) {
                showAlert('Gagal memperbarui pengguna.');
            }

            // Cek parameter untuk Delete User
            if (urlParams.has('successDelete')) {
                showAlert('Pengguna berhasil dihapus!');
            } else if (urlParams.has('errorDelete')) {
                showAlert('Gagal menghapus pengguna.');
            }

            // Hapus parameter dari URL setelah notifikasi ditampilkan
            if (urlParams.has('successAdd') || urlParams.has('errorAdd') ||
                urlParams.has('successEdit') || urlParams.has('errorEdit') ||
                urlParams.has('successDelete') || urlParams.has('errorDelete')) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$koneksi->close(); // Menutup koneksi ke database
?>
