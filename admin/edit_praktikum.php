<?php
require '../auth.php';
check_role(['admin']);
require '../db.php';

// Ambil Data Praktikum
$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM praktikum WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$praktikum = $result->fetch_assoc();

// Ambil Daftar Dosen
$dosen = $conn->query("SELECT id, nama FROM users WHERE role = 'dosen'");

if (!$praktikum) {
    echo "Data tidak ditemukan!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Praktikum</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../adminstyle.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="logo">Web Labor TRKJ</div>
    <a href="manage_praktikum.php" class="logout-btn"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
</nav>

<div class="container">
    <h1>Edit Praktikum</h1>

    <form method="POST" class="praktikum-form">
        <input type="hidden" name="praktikum_id" value="<?= $praktikum['id'] ?>">
        
        <label>Nama Praktikum:</label>
        <input type="text" name="nama" value="<?= $praktikum['nama'] ?>" required>

        <label>Deskripsi:</label>
        <textarea name="deskripsi" required><?= $praktikum['deskripsi'] ?></textarea>

        <label>Dosen Pengampu:</label>
        <select name="dosen_id" required>
            <option value="">Pilih Dosen</option>
            <?php while ($row = $dosen->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>" <?= $row['id'] == $praktikum['dosen_id'] ? 'selected' : '' ?>><?= $row['nama'] ?></option>
            <?php endwhile; ?>
        </select>

        <button type="submit" name="edit_praktikum" class="btn-save">Simpan</button>
    </form>
</div>

</body>
</html>
