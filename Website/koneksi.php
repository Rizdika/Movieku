<?php
$namaserver = "localhost";
$username = "root";
$password = "";
$database = "uas_risdika_241351138";

$koneksi = new mysqli($namaserver, $username, $password, $database);

if ($koneksi->connect_error){
    die("Koneksi Gagal: " . $koneksi->connect_error );
}
?>