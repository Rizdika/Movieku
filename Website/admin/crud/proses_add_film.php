<?php
include '../../koneksi.php';

session_start();


if (!isset($_SESSION['id_user'])  || $_SESSION['role'] !== 'Administrator' && $_SESSION['role'] !== 'Editor') {
    die("Akses ditolak: Anda belum login.");
}

$id_user = $_SESSION['id_user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $genre = mysqli_real_escape_string($koneksi, $_POST['genre']);
    $tahun = (int)$_POST['tahun'];
    
    
    $cover = $_FILES['cover'];
    $target_dir = "../../img/content/";
    $file_name = time() . "_" . basename($cover['name']); 
    $target_file = $target_dir . $file_name;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    
    $check = getimagesize($cover["tmp_name"]);
    if ($check === false) {
        $uploadOk = 0;
        die("File bukan gambar.");
    }

    
    if (file_exists($target_file)) {
        $uploadOk = 0;
        die("File sudah ada.");
    }

    
    if ($cover["size"] > 2000000) {
        $uploadOk = 0;
        die("File terlalu besar.");
    }

    
    if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
        $uploadOk = 0;
        die("Hanya format JPG, JPEG, PNG, & GIF yang diperbolehkan.");
    }

    if ($uploadOk === 0) {
        header("Location: ../manageFilm.php?error=upload_failed");
        exit;
    } else {
        if (move_uploaded_file($cover["tmp_name"], $target_file)) {
            $sql = "INSERT INTO film (judul, genre, tahun, cover, tgl_upload, id) 
                    VALUES ('$judul', '$genre', $tahun, '$file_name', NOW(), $id_user)";
            if (mysqli_query($koneksi, $sql)) {
                header("Location: ../manageFilm.php?success=1");
                exit;
            } else {
                header("Location: ../manageFilm.php?error=db_error");
                exit;
            }
        } else {
            header("Location: ../manageFilm.php?error=file_move_error");
            exit;
        }
    }        
} else {
    header("Location: ../manageFilm.php");
    exit;
}
?>
