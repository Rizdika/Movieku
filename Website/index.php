<?php
// Koneksi ke database
include 'koneksi.php';

// Inisialisasi variabel pencarian
$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

// Ambil data film dari database dengan filter pencarian jika ada
$sql = "SELECT * FROM film WHERE judul LIKE ?";
$stmt = $koneksi->prepare($sql);
$searchParam = "%" . $search . "%";
$stmt->bind_param("s", $searchParam);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MovieKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/indexStyle.css">
</head>
<body>
    <header>
        <h1>MovieKu</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="about.html">About</a>
            <a href="faq.html">Faq</a>
            <a href="contact.html">Contact Us</a>
        </nav>
        <div class="header-right">
            <div class="search-bar">
                <form action="index.php" method="GET">
                    <input type="text" name="search" placeholder="Search movies..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">Search</button>
                </form>
            </div>
            <div class="auth-buttons">
                <button onclick="window.location.href='admin/login.php'">Login</button>
            </div>
        </div>
    </header>
    <main>
        <section class="grid-container">
            <?php
            if ($result->num_rows > 0) {
                while ($film = $result->fetch_assoc()) {
                    // Menggabungkan path direktori dengan nama file gambar
                    $coverImagePath = "img/content/" . $film['cover'];
                    echo '
                    <div class="card">
                        <img src="' . $coverImagePath . '" alt="Movie Poster">
                        <h3>' . $film['judul'] . '</h3>
                        <p>' . $film['tahun'] . ' | ' . $film['genre'] . '</p>
                    </div>';
                }
            } else {
                echo '<p>No movies found.</p>';
            }
            ?>
        </section>
    </main>
    <footer>
        <p>&copy; 2025 Kelompok 4 - TIF Malam A 2024. All rights reserved.</p>
    </footer>
</body>
</html>
