<?php
include '../../koneksi.php';
session_start();


if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Administrator' && $_SESSION['role'] !== 'Editor') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id_film'])) {
    $id_film = $_GET['id_film'];

    
    $query = "DELETE FROM film WHERE id_film = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $id_film);

    if ($stmt->execute()) {
        
        header("Location: ../manageFilm.php?delete_status=success");
    } else {
        
        header("Location: ../manageFilm.php?delete_status=error");
    }
    $stmt->close();
} else {
    
    header("Location: ../manageFilm.php?delete_status=error");
}

$koneksi->close();
?>
