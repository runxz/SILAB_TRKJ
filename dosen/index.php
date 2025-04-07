<?php
require '../auth.php';

require '../db.php';

$user_id = $_SESSION['user_id'];

// Ambil Data Pengguna
$stmt = $conn->prepare("SELECT nama, foto_profil FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check role: Allow both 'dosen' and 'asistendosen'
check_role(['dosen', 'asistendosen']);

$user_role = $_SESSION['role']; // Get user role from session
$dashboard_title = ($user_role == 'dosen') ? "Dashboard Dosen" : "Dashboard Asisten Dosen";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $dashboard_title ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<!-- Navbar -->
<?php require '../general/navbar.php';
 ?>
 <?php require '../general/search.php';?>

<div class="container">
    <h1><?= $dashboard_title ?></h1>

    <div class="icon-container">
        <?php if ($user_role == 'dosen'): // Hide for Asisten Dosen ?>
            <a href="./praktikum/" class="icon-box">
                <i class="fa-solid fa-book"></i>
                <span>Kelola Praktikum</span>
            </a>

            <a href="./seleksi/" class="icon-box">
            <i class="fa-solid fa-desktop"></i>
            <span>Seleksi</span>
        </a>

        <a href="./modul/" class="icon-box">
            <i class="fa-solid fa-book"></i>
            <span>Modul</span>
        </a>
        <?php endif; ?>

        <a href="./jadwal/" class="icon-box">
            <i class="fa-solid fa-calendar"></i>
            <span>Kelola Jadwal</span>
        </a>
        <a href="./absensi/" class="icon-box">
            <i class="fa-solid fa-clipboard-user"></i>
            <span>Lihat Absensi</span>
        </a>
        <a href="./laporan/" class="icon-box">
            <i class="fa-solid fa-file-alt"></i>
            <span>Lihat Laporan</span>
        </a>
        <a href="./dokumentasi/" class="icon-box">
            <i class="fa-solid fa-camera"></i>
            <span>Dokumentasi</span>
        </a>
        <a href="./hasil_praktikum/" class="icon-box">
            <i class="fa-solid fa-file"></i>
            <span>Tugas Akhir Praktikum</span>
        </a>
        <?php if ($user_role == 'asistendosen'): // Hide for Asisten Dosen ?>
            <a href="./seleksi/asdos.php" class="icon-box">
                <i class="fa-solid fa-book"></i>
                <span>Daftar Seleksi</span>
            </a>
            
        <?php endif; ?>

    </div>
</div>

</body>
</html>
