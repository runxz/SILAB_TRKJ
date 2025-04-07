<?php
require '../auth.php';
require '../db.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Ambil Data Pengguna
$stmt = $conn->prepare("SELECT nama, email, foto_profil, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Ambil Praktikum (berdasarkan role)
if ($role == 'mahasiswa') {
    $praktikum = $conn->query("
        SELECT p.nama FROM praktikum p
        JOIN mahasiswa_praktikum mp ON p.id = mp.praktikum_id
        WHERE mp.mahasiswa_id = $user_id
    ");
} elseif ($role == 'dosen') {
    $praktikum = $conn->query("SELECT nama FROM praktikum WHERE dosen_id = $user_id");
} else {
    $praktikum = null;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Profil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/profilestyle.css">
</head>
<body>

<?php require '../general/back.php';?>

<div class="container">
    <h1>Profil Pengguna</h1>

    <!-- Profile Info -->
    <div class="profile-container">
        <img src="../uploads/profiles/<?= $user['foto_profil'] ?: 'default.png' ?>" class="profile-img">
        <h2><?= $user['nama'] ?></h2>
        <p><i class="fa-solid fa-envelope"></i> <?= $user['email'] ?></p>
        <p><i class="fa-solid fa-user-tag"></i> <?= ucfirst($user['role']) ?></p>
        <a href="settings.php" class="btn-edit"><i class="fa-solid fa-pen"></i> Edit Profil</a>
    </div>

    <!-- Praktikum List -->
    <?php if ($praktikum && $praktikum->num_rows > 0): ?>
        <h2><?= $role == 'mahasiswa' ? 'Praktikum yang Diikuti' : 'Praktikum yang Dikelola' ?></h2>
        <ul class="praktikum-list">
            <?php while ($row = $praktikum->fetch_assoc()): ?>
                <li><i class="fa-solid fa-flask"></i> <?= $row['nama'] ?></li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p class="no-praktikum"><i class="fa-solid fa-circle-info"></i> Tidak ada praktikum terkait.</p>
    <?php endif; ?>
</div>

</body>
</html>
