<?php
include '../../koneksi.php';

session_start();


if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: login.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    $foto_profile = $_FILES['foto_profile'];

    $foto_name = ''; 

    
    if (isset($foto_profile) && $foto_profile['error'] === 0) {
        
        $foto_name = time() . '_' . $foto_profile['name']; 
        $foto_tmp = $foto_profile['tmp_name'];
        $foto_path = "../../img/foto_profile/" . $foto_name;

        
        if (!move_uploaded_file($foto_tmp, $foto_path)) {
            header("Location: ../manageUsers.php?error=Gagal mengunggah foto profil!");
            exit;
        }
    }

    
    $sql = "INSERT INTO user (username, email, password, id_role, foto) 
            VALUES ('$username', '$email', '$password', 
            (SELECT id_role FROM role WHERE role = '$role'), '$foto_name')";

    if ($koneksi->query($sql) === TRUE) {
        var_dump("Add User Success"); 
        header("Location: ../manageUsers.php?successAdd=User baru berhasil ditambahkan!");
    } else {
        var_dump("Add User Failed"); 
        var_dump($koneksi->error);  
        exit; 
    }
}
?>
