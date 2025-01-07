<?php
include '../../koneksi.php';

session_start();


if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: ../login.php");
    exit;
}


$id_user = $_POST['id_user'];
$username = $_POST['username'];
$email = $_POST['email'];
$role = $_POST['role'];
$foto_lama = $_POST['foto_lama']; 


if (isset($_FILES['foto_profile']) && $_FILES['foto_profile']['error'] === UPLOAD_ERR_OK) {
    $foto_baru = $_FILES['foto_profile']['name'];
    $target_dir = "../../img/foto_profile/";
    $target_file = $target_dir . basename($foto_baru);

    
    if (move_uploaded_file($_FILES['foto_profile']['tmp_name'], $target_file)) {
        
        $foto = $foto_baru;

        
        if ($foto_lama !== 'https://via.placeholder.com/100') {
            $path_foto_lama = "../../img/foto_profile/" . $foto_lama;
        
            
            if (is_file($path_foto_lama) && file_exists($path_foto_lama)) {
                unlink($path_foto_lama);
            } else {
                error_log("Gagal menghapus file: $path_foto_lama bukan file yang valid.");
            }
        }        
    } else {
        
        $foto = $foto_lama;
    }
} else {
    
    $foto = $foto_lama;
}



$sql = "UPDATE user SET username = ?, email = ?, id_role = (SELECT id_role FROM role WHERE role = ?), foto = ? WHERE id = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("ssssi", $username, $email, $role, $foto, $id_user);

if ($stmt->execute()) {
    
    header("Location: ../manageUsers.php?successEdit=Data berhasil diperbarui.");
    exit;
} else {
    
    header("Location: ../manageUsers.php?errorEdit=Gagal memperbarui data.");
    exit;
}

$stmt->close();
$koneksi->close();
?>
