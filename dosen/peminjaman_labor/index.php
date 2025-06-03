<?php
require '../../auth.php';
check_role(['dosen', 'asistendosen']);
require '../../db.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle submit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_peminjaman'])) {
    $labor = $_POST['laboratorium'];
    $tanggal = $_POST['tanggal'];
    $mulai = $_POST['waktu_mulai'];
    $selesai = $_POST['waktu_selesai'];
    $kegiatan = htmlspecialchars($_POST['kegiatan']);

    if (!$labor || !$tanggal || !$mulai || !$selesai || !$kegiatan) {
        $error = "Semua field wajib diisi.";
    } else {
        $stmt = $conn->prepare("INSERT INTO peminjaman_labor 
        (user_id, laboratorium, tanggal, waktu_mulai, waktu_selesai, kegiatan)
        VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $labor, $tanggal, $mulai, $selesai, $kegiatan);
        if ($stmt->execute()) $success = "Pengajuan peminjaman berhasil.";
        else $error = "Gagal menyimpan data.";
    }
}

// Fetch logs
$log = $conn->prepare("SELECT * FROM peminjaman_labor WHERE user_id = ? ORDER BY created_at DESC");
$log->bind_param("i", $user_id);
$log->execute();
$result = $log->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Peminjaman Laboratorium</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/dosenstyle.css">
    <style>
        form { max-width: 600px; margin: auto; padding: 20px; background: #f8f8f8; border-radius: 8px; }
        form input, form select, form textarea { width: 100%; padding: 8px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <nav class="navbar">
    <div class="logo">Web Labor TRKJ</div>
    <a href="../" class="logout-btn">Kembali</a>
</nav>
<div class="container">
    <h1>Peminjaman Laboratorium</h1>

    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

    <form method="POST">
        <label>Laboratorium</label>
        <select name="laboratorium" required>
            <option value="">-- Pilih Labor --</option>
            <option value="Laboratorium Embedded System">Laboratorium Embedded System</option>
            <option value="Laboratorium Jaringan">Laboratorium Jaringan</option>
            <option value="Laboratorium Komputasi">Laboratorium Komputasi</option>
            <option value="Laboratorium Server dan Data Center">Laboratorium Server dan Data Center</option>
        </select>

        <label>Tanggal</label>
        <input type="date" name="tanggal" required>

        <label>Waktu Mulai</label>
        <input type="time" name="waktu_mulai" required>

        <label>Waktu Selesai</label>
        <input type="time" name="waktu_selesai" required>

        <label>Kegiatan</label>
        <textarea name="kegiatan" rows="4" required></textarea>

        <button type="submit"class="btn-save" name="submit_peminjaman">Ajukan Peminjaman</button>
    </form>

    <h2>Riwayat Peminjaman</h2>
    <table>
        <tr>
            <th>Tanggal</th>
            <th>Labor</th>
            <th>Waktu</th>
            <th>Kegiatan</th>
            <th>Status</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['tanggal'] ?></td>
            <td><?= $row['laboratorium'] ?></td>
            <td><?= $row['waktu_mulai'] ?> - <?= $row['waktu_selesai'] ?></td>
            <td><?= $row['kegiatan'] ?></td>
            <td><?= ucfirst($row['status']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
