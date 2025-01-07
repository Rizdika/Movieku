<?php
include '../../koneksi.php';

session_start();


if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: ../login.php");
    exit;
}


if (isset($_GET['id']) && !empty($_GET['id'])) {
    $userId = intval($_GET['id']); 

    
    $sql = "DELETE FROM user WHERE id = $userId";

    if ($koneksi->query($sql) === TRUE) {
        
        header("Location: ../manageUsers.php?successDelete=true");
        exit;
    } else {
        
        header("Location: ../manageUsers.php?errorDelete=true");
        exit;
    }    
} else {
    
    header("Location: ../manageUsers.php?error=invalid");
    exit;
}
?>
