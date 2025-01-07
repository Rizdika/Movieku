<?php
session_start();
include '../koneksi.php';
// Pastikan pengguna sudah login dan memiliki role yang tepat
if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'Administrator' && $_SESSION['role'] !== 'Editor')) {
    header("Location: login.php");
    exit;
}

// Ambil data pengguna dari sesi
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

// Mengambil jumlah total film
$total_film_query = "SELECT COUNT(*) as total_film FROM film"; // Sesuaikan dengan tabel dan kolom yang ada di database Anda
$result_film = $koneksi->query($total_film_query);
$total_film = 0;
if ($result_film) {
    if ($result_film->num_rows > 0) {
        $row_film = $result_film->fetch_assoc();
        $total_film = $row_film['total_film'];
    } else {
        // Jika tidak ada film ditemukan
        $total_film = 0;
    }
} else {
    // Jika query gagal dijalankan, tampilkan error
    die("Error: " . $koneksi->error);
}

// Mengambil jumlah total genre
$total_genre_query = "SELECT COUNT(DISTINCT genre) as total_genre FROM film"; // Sesuaikan dengan tabel dan kolom yang ada di database Anda
$result_genre = $koneksi->query($total_genre_query);
$total_genre = 0;
if ($result_genre) {
    if ($result_genre->num_rows > 0) {
        $row_genre = $result_genre->fetch_assoc();
        $total_genre = $row_genre['total_genre'];
    } else {
        // Jika tidak ada genre ditemukan
        $total_genre = 0;
    }
} else {
    // Jika query gagal dijalankan, tampilkan error
    die("Error: " . $koneksi->error);
}

// Mengambil 5 film terbaru berdasarkan tanggal
$latest_film_query = "
    SELECT film.*, user.username 
    FROM film
    JOIN user ON film.id = user.id 
    ORDER BY film.tgl_upload DESC
    LIMIT 5
";
$result_latest_film = $koneksi->query($latest_film_query);
$latest_film = [];
if ($result_latest_film) {
    while ($row = $result_latest_film->fetch_assoc()) {
        $latest_film[] = $row; // Menyimpan data film terbaru ke dalam array
    }
} else {
    die("Error: " . $koneksi->error);
}

// Menutup koneksi
$koneksi->close();
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
            <!-- Tampilkan foto profil berdasarkan nama pengguna -->
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
                <h2>Informasi Film</h2>
                <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                    <div style="text-align: center; width: 48%; background-color: #3f51b5; color: #fff; padding: 20px; border-radius: 8px;">
                        <h3>Total Film</h3>
                        <p style="font-size: 2rem; margin: 0;"><?php echo $total_film; ?></p>
                    </div>
                    <div style="text-align: center; width: 48%; background-color: #4caf50; color: #fff; padding: 20px; border-radius: 8px;">
                        <h3>Total Genre</h3>
                        <p style="font-size: 2rem; margin: 0;"><?php echo $total_genre; ?></p>
                    </div>
                </div>
            </div>
            <div class="card" style="margin-top: 20px;">
                <h2>Histori Penambahan Film</h2>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul Film</th>
                            <th>Genre</th>
                            <th>Admin</th> <!-- Menampilkan username admin -->
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($latest_film) > 0): ?>
                            <?php foreach ($latest_film as $index => $film): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo $film['judul']; ?></td> <!-- Sesuaikan dengan kolom yang ada di database -->
                                    <td><?php echo $film['genre']; ?></td> <!-- Sesuaikan dengan kolom yang ada di database -->
                                    <td><?php echo $film['username']; ?></td> <!-- Menampilkan username dari kolom yang digabung -->
                                    <td><?php echo $film['tgl_upload']; ?></td> <!-- Sesuaikan dengan kolom yang ada di database -->
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">Tidak ada data film terbaru.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
