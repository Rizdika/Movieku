<?php
include '../koneksi.php';

session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'Administrator' && $_SESSION['role'] !== 'Editor')) {
    header("Location: login.php");
    exit;
}
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Tentukan lokasi folder foto profil
$folder_foto_profil = "../img/foto_profile/";
$foto_profil_path = $folder_foto_profil . $username;

// Cari file foto profil berdasarkan pola nama (mendukung berbagai format gambar)
$foto_formats = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
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

// Ambil data film dari database
$query = "
    SELECT 
        film.id_film, 
        film.judul, 
        film.genre, 
        film.tahun, 
        film.cover, 
        film.tgl_upload, 
        user.username AS id 
    FROM 
        film 
    LEFT JOIN 
        user 
    ON 
        film.id = user.id";
$result = mysqli_query($koneksi, $query);
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
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h2>Daftar Film</h2>
                    <a href="#" class="btn btn-add-user" data-bs-toggle="modal" data-bs-target="#addFilmModal">
                        <i class="fas fa-plus"></i> Add Film
                    </a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Genre</th>
                            <th>Tahun</th>
                            <th>Cover</th>
                            <th>Pemosting</th>
                            <th>Tgl Upload</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . $no++ . "</td>";
                                echo "<td>" . htmlspecialchars($row['judul']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['genre']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['tahun']) . "</td>";
                                echo "<td><img src='../img/content/" . htmlspecialchars($row['cover']) . "' alt='Cover' width='50'></td>";
                                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['tgl_upload']) . "</td>";
                                echo "<td>
                                    <a href='#' class='btn btn-edit' data-bs-toggle='modal' data-bs-target='#editFilmModal' 
                                    data-id='{$row['id_film']}'
                                    data-judul='{$row['judul']}'
                                    data-genre='{$row['genre']}'
                                    data-tahun='{$row['tahun']}'
                                    data-cover='{$row['cover']}'>
                                    <i class='fas fa-edit'></i>Edit
                                    </a>
                                    <a href='crud/proses_delete_film.php?id_film={$row['id_film']}' 
                                        class='btn btn-delete' 
                                        onclick='return confirm(\"Apakah Anda yakin ingin menghapus film ini?\");'>
                                        <i class='fas fa-trash'></i>Delete
                                    </a>
                                </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8'>Tidak ada data film tersedia.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="modal fade" id="addFilmModal" tabindex="-1" aria-labelledby="addFilmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFilmModalLabel">Add New Film</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="crud/proses_add_film.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Film</label>
                            <input type="text" class="form-control" id="judul" name="judul" required>
                        </div>
                        <div class="mb-3">
                            <label for="genre" class="form-label">Genre</label>
                            <input type="text" class="form-control" id="genre" name="genre" required>
                        </div>
                        <div class="mb-3">
                            <label for="tahun" class="form-label">Tahun</label>
                            <input type="number" class="form-control" id="tahun" name="tahun" required>
                        </div>
                        <div class="mb-3">
                            <label for="cover" class="form-label">Cover Film</label>
                            <input type="file" class="form-control" id="cover" name="cover" accept="image/*" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Kembali
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Tambah
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editFilmModal" tabindex="-1" aria-labelledby="editFilmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFilmModalLabel">Edit Film</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <form id="editFilmForm" method="POST" action="crud/proses_edit_film.php" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="editId">
                    <input type="hidden" name="cover_lama" id="editCoverLama"> <!-- Tambahkan input hidden untuk cover lama -->

                    <div class="mb-3">
                        <label for="editJudul" class="form-label">Judul Film</label>
                        <input type="text" class="form-control" id="editJudul" name="judul" required>
                    </div>
                    <div class="mb-3">
                        <label for="editGenre" class="form-label">Genre</label>
                        <input type="text" class="form-control" id="editGenre" name="genre" required>
                    </div>
                    <div class="mb-3">
                        <label for="editTahun" class="form-label">Tahun</label>
                        <input type="number" class="form-control" id="editTahun" name="tahun" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCover" class="form-label">Cover Film</label>
                        <input type="file" class="form-control" id="editCover" name="cover" accept="image/*">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kembali</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>

    <?php
    if (isset($_SESSION['editStatus'])) {
        $editStatus = $_SESSION['editStatus'];
        echo "<script>
            alert('" . ($editStatus === 'success' ? "Film berhasil diperbarui!" : "Gagal memperbarui film. Silakan coba lagi.") . "');
        </script>";
        unset($_SESSION['editStatus']); // Hapus pesan setelah ditampilkan
    }
    ?>
    <script>
        // Cek URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            alert('Film berhasil ditambahkan!');
            // Hapus parameter dari URL
            window.history.replaceState({}, document.title, window.location.pathname);
        } else if (urlParams.has('error')) {
            alert('Gagal menambahkan film. Silakan coba lagi.');
            // Hapus parameter dari URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        document.addEventListener('DOMContentLoaded', function () {
            const editButtons = document.querySelectorAll('.btn-edit');

            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    const judul = this.getAttribute('data-judul');
                    const genre = this.getAttribute('data-genre');
                    const tahun = this.getAttribute('data-tahun');
                    const cover = this.getAttribute('data-cover');

                    // Isi form modal dengan data yang sesuai
                    document.getElementById('editId').value = id;
                    document.getElementById('editJudul').value = judul;
                    document.getElementById('editGenre').value = genre;
                    document.getElementById('editTahun').value = tahun;

                    // Tampilkan gambar cover lama
                    const coverPreview = document.getElementById('editCoverPreview');
                    if (coverPreview) {
                        coverPreview.src = `../img/content/${cover}`;
                    }
                });
            });
        });

        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.dataset.id;
                const judul = this.dataset.judul;
                const genre = this.dataset.genre;
                const tahun = this.dataset.tahun;
                const coverLama = this.dataset.cover;

                document.getElementById('editId').value = id;
                document.getElementById('editJudul').value = judul;
                document.getElementById('editGenre').value = genre;
                document.getElementById('editTahun').value = tahun;
                document.getElementById('editCoverLama').value = coverLama; // Isi input hidden dengan nama cover lama
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('delete_status')) {
                const status = urlParams.get('delete_status');
                if (status === 'success') {
                    alert('Film berhasil dihapus!');
                } else if (status === 'error') {
                    alert('Gagal menghapus film. Silakan coba lagi.');
                }

                // Hapus parameter dari URL setelah ditampilkan
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
