<?php
$host = "localhost";
$user = "root"; // Ganti dengan user database Anda
$pass = ""; // Ganti dengan password database Anda
$dbname = "praktikum_db"; // Ganti dengan nama database

$conn = new mysqli($host, $user, $pass, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
