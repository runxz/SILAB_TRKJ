<?php
require '../../auth.php';
check_role(['asistendosen']);
require '../../db.php';

$asisten_id = $_SESSION['user_id'];

// Handle Pendaftaran Seleksi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['daftar_seleksi'])) {
    $seleksi_id = intval($_POST['seleksi_id']);
    
    $stmt = $conn->prepare("INSERT INTO seleksi_asisten (seleksi_id, asisten_id, status) VALUES (?, ?, 'menunggu')");
    $stmt->bind_param("ii", $seleksi_id, $asisten_id);
    $stmt->execute();
    header("Location: asdos.php");
    exit();
}

// Handle Upload Jawaban
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_jawaban'])) {
    $seleksi_asisten_id = intval($_POST['seleksi_asisten_id']);
    $file_name = '';

    if (isset($_FILES['file_jawaban']) && $_FILES['file_jawaban']['error'] == 0) {
        $file_name = 'jawaban_' . time() . '_' . basename($_FILES['file_jawaban']['name']);
        move_uploaded_file($_FILES['file_jawaban']['tmp_name'], "../../uploads/" . $file_name);
    }

    $stmt = $conn->prepare("UPDATE seleksi_asisten SET file_jawaban = ?, submitted_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $file_name, $seleksi_asisten_id);
    $stmt->execute();
    header("Location: asdos.php");
    exit();
}

// Fetch Seleksi yang Bisa Diikuti
$seleksi = $conn->query("SELECT s.id, p.nama AS nama_praktikum, s.file_soal, s.waktu_mulai, s.waktu_selesai
                         FROM seleksi s
                         JOIN praktikum p ON s.praktikum_id = p.id
                         WHERE s.waktu_selesai > NOW()
                         AND s.id NOT IN (SELECT seleksi_id FROM seleksi_asisten WHERE asisten_id = $asisten_id)");

// Fetch Seleksi yang Sudah Diikuti
$seleksi_diikuti = $conn->query("SELECT sa.id, p.nama AS nama_praktikum, s.file_soal, sa.file_jawaban, sa.status, sa.submitted_at
                                 FROM seleksi_asisten sa
                                 JOIN seleksi s ON sa.seleksi_id = s.id
                                 JOIN praktikum p ON s.praktikum_id = p.id
                                 WHERE sa.asisten_id = $asisten_id");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Asisten Dosen</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/dosenstyle.css">
</head>
<body>

<?php require '../../general/back.php';?>

<div class="container">
    <h1>Seleksi Asisten Dosen</h1>

    <!-- List Seleksi yang Bisa Diikuti -->
    <h2>Seleksi yang Tersedia</h2>
    <table class="styled-table">
        <tr>
            <th>Praktikum</th>
            <th>File Soal</th>
            <th>Waktu Mulai</th>
            <th>Waktu Selesai</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $seleksi->fetch_assoc()): ?>
        <tr>
            <td><?= $row['nama_praktikum'] ?></td>
            <td><a href="../../uploads/<?= $row['file_soal'] ?>" download>Download</a></td>
            <td><?= $row['waktu_mulai'] ?></td>
            <td><?= $row['waktu_selesai'] ?></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="seleksi_id" value="<?= $row['id'] ?>">
                    <button type="submit" name="daftar_seleksi" class="btn-apply">Daftar</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <!-- List Seleksi yang Sudah Diikuti -->
    <h2>Seleksi yang Diikuti</h2>
    <table class="styled-table">
        <tr>
            <th>Praktikum</th>
            <th>File Soal</th>
            <th>Jawaban</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $seleksi_diikuti->fetch_assoc()): ?>
        <tr>
            <td><?= $row['nama_praktikum'] ?></td>
            <td><a href="../../uploads/<?= $row['file_soal'] ?>" download>Download</a></td>
            <td>
                <?php if ($row['file_jawaban']): ?>
                    <a href="../../uploads/<?= $row['file_jawaban'] ?>" download>Download</a>
                <?php else: ?>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="seleksi_asisten_id" value="<?= $row['id'] ?>">
                        <input type="file" name="file_jawaban" required>
                        <button type="submit" name="upload_jawaban" class="btn-upload">Upload</button>
                    </form>
                <?php endif; ?>
            </td>
            <td><?= ucfirst($row['status']) ?></td>
            <td>
                <?php if (!$row['file_jawaban']): ?>
                    <button disabled class="btn-disabled">Belum Upload</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
