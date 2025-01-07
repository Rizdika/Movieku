<?php
include '../../koneksi.php';
session_start();


date_default_timezone_set("Asia/Jakarta");


if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Administrator' && $_SESSION['role'] !== 'Editor') {
    header("Location: ../login.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_film = $_POST['id'];
    $judul = $_POST['judul'];
    $genre = $_POST['genre'];
    $tahun = $_POST['tahun'];
    $cover = $_FILES['cover']['name'];
    $cover_lama = $_POST['cover_lama']; 

    
    $pemosting = $_SESSION['username']; 
    $tgl_upload = date("Y-m-d H:i:s"); 

    
    if (empty($id_film) || empty($judul) || empty($genre) || empty($tahun)) {
        echo "Error: Semua field harus diisi.";
        exit;
    }

    
    $cover_nama_baru = $cover_lama;

    if (!empty($cover)) {
        $target_dir = "../../img/content/";
        $target_file = $target_dir . basename($cover);
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        
        $valid_extensions = ['jpg', 'jpeg', 'png'];
        if (!in_array($file_type, $valid_extensions)) {
            echo "Error: Hanya file JPG, JPEG, dan PNG yang diperbolehkan.";
            exit;
        }

        
        if (move_uploaded_file($_FILES['cover']['tmp_name'], $target_file)) {
            $cover_nama_baru = $cover; 
        } else {
            echo "Error: Gagal mengupload file.";
            exit;
        }
    }

    
    $query = "UPDATE film 
              SET judul = ?, genre = ?, tahun = ?, cover = ?, id = (SELECT id FROM user WHERE username = ?), tgl_upload = ? 
              WHERE id_film = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("ssssssi", $judul, $genre, $tahun, $cover_nama_baru, $pemosting, $tgl_upload, $id_film);

    session_start();
    if ($stmt->execute()) {
        $_SESSION['editStatus'] = 'success';
    } else {
        $_SESSION['editStatus'] = 'error';
    }
    header("Location: ../manageFilm.php");
    exit;    

    $stmt->close();
} else {
    echo "Error: Metode tidak valid.";
}
$koneksi->close();
?>
