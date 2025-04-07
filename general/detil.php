<?php
require '../auth.php';
require '../db.php';

if (!isset($_GET['id']) || !isset($_GET['role'])) {
    die("Parameter tidak lengkap!");
}

$id = intval($_GET['id']);
$role = $_GET['role'];

$allowed_roles = ['mahasiswa', 'dosen', 'asisten_dosen'];

if (!in_array($role, $allowed_roles)) {
    die("Role tidak valid!");
}

// Ambil Data Pengguna dari tabel users
$stmt = $conn->prepare("SELECT nama, email, foto_profil, role FROM users WHERE id = ? AND role = ?");
$stmt->bind_param("is", $id, $role);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Pengguna tidak ditemukan!");
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil <?= ucfirst($user['nama']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/profilestyle.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="logo">Web Labor TRKJ</div>
    <a href="javascript:history.back()" class="logout-btn"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
</nav>

<div class="container">
    <h1>Profil Pengguna</h1>

    <!-- Profile Info -->
    <div class="profile-container">
        <img src="../uploads/profiles/<?= $user['foto_profil'] ?: 'default.png' ?>" class="profile-img">
        <h2><?= htmlspecialchars($user['nama']) ?></h2>
        <p><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
        <p><i class="fa-solid fa-user-tag"></i> <?= ucfirst(htmlspecialchars($user['role'])) ?></p>
    </div>
</div>

</body>
</html>
