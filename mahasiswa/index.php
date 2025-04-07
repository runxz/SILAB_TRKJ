<?php
require '../auth.php';
check_role(['mahasiswa']);
require '../db.php';

$user_id = $_SESSION['user_id'];

// Ambil Data Pengguna
$stmt = $conn->prepare("SELECT nama, foto_profil FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css"> <!-- Link to the external CSS file -->
</head>
<body>

<!-- Navbar -->
<?php require '../general/navbar.php';?>

<?php require '../general/search.php';?>


<div class="container">
    <h1>Dashboard Mahasiswa</h1>

    <div class="icon-container">
        <a href="./praktikum" class="icon-box">
            <i class="fa-solid fa-book"></i>
            <span>Pilih Praktikum</span>
        </a>
        <a href="./modul" class="icon-box">
            <i class="fa-solid fa-book"></i>
            <span>Modul</span>
        </a>
        <a href="./jadwal&absensi" class="icon-box">
            <i class="fa-solid fa-calendar"></i>
            <span>Lihat Jadwal & Absensi</span>
        </a>
        <a href="./laporan" class="icon-box">
            <i class="fa-solid fa-file-alt"></i>
            <span>Buat Laporan</span>
        </a>
        <a href="./inventaris" class="icon-box">
            <i class="fa-solid fa-file-alt"></i>
            <span>Inventaris</span>
        </a>
        <a href="./hasil_praktikum" class="icon-box">
            <i class="fa-solid fa-vial"></i>
            <span>Input Hasil Praktikum</span>
        </a>
        <a href="./dokumentasi" class="icon-box">
            <i class="fa-solid fa-camera"></i>
            <span>Dokumentasi</span>
        </a>
        <a href="./pinjam_alat" class="icon-box">
            <i class="fa-solid fa-toolbox"></i>
            <span>Pinjam Alat</span>
        </a>
    </div>
</div>

</body>
</html>
