<?php
session_start();
include '../koneksi.php';

// Variabel untuk menyimpan pesan error
$error_message = "";

// Cek apakah formulir telah dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validasi input
    if (empty($username) || empty($password)) {
        $error_message = "Username dan password harus diisi.";
    } else {
        $sql = "SELECT u.id, u.username, u.password, r.role AS role
                FROM user u
                JOIN role r ON u.id_role = r.id_role
                WHERE u.username = ?";
        $stmt = $koneksi->prepare($sql);

        if (!$stmt) {
            die("Error: " . $koneksi->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verifikasi password
            if ($password === $user['password']) {
                // Mulai sesi dengan aman
                session_regenerate_id(true);
                $_SESSION['id_user'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect berdasarkan role
                if ($user['role'] === 'Administrator' || $user['role'] === 'Editor') {
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error_message = "Role tidak dikenali.";
                }
            } else {
                $error_message = "Password salah.";
            }
        } else {
            $error_message = "Username tidak ditemukan.";
        }

        $stmt->close();
    }

    $koneksi->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/loginStyle.css">
    <script>
        // Fungsi untuk menampilkan popup jika ada pesan error
        function showError(message) {
            if (message) {
                alert(message);
            }
        }
    </script>
</head>
<body onload="showError('<?php echo $error_message; ?>')">
    <div class="login-container">
        <h1>Login</h1>
        <form action="login.php" method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            <button type="submit">Login</button>
        </form>
        <a href="../index.php" class="back-button">Kembali</a>
    </div>
</body>
</html>
