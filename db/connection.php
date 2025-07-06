<?php

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "20230140160_UAS_PDW";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
