<?php
require '../../auth.php';
check_role(['dosen', 'asistendosen']);
require '../../db.php';

$dosen_id = $_SESSION['user_id'];

// Fetch Praktikum
$praktikum = $conn->query("SELECT * FROM praktikum WHERE dosen_id = $dosen_id");

// Handle Update Status Laporan
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_laporan'])) {
    $laporan_id = intval($_POST['laporan_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE laporan SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $laporan_id);
    $stmt->execute();
    if ($status === 'ditolak' && !empty($_POST['komentar'])) {
    $komentar = htmlspecialchars($_POST['komentar']);
    $stmt_komentar = $conn->prepare("INSERT INTO komentar_laporan (laporan_id, user_id, komentar) VALUES (?, ?, ?)");
    $stmt_komentar->bind_param("iis", $laporan_id, $dosen_id, $komentar);
    $stmt_komentar->execute();
}
    header("Location: index.php");
    exit();
}

// Fetch Laporan
$laporan = [];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['lihat_laporan'])) {
    $praktikum_id = intval($_POST['praktikum_id']);
    $laporan_query = $conn->prepare("
        SELECT l.*, u.nama AS nama_mahasiswa 
        FROM laporan l
        JOIN users u ON l.mahasiswa_id = u.id
        WHERE l.praktikum_id = ?
        ORDER BY l.status ASC
    ");
    $laporan_query->bind_param("i", $praktikum_id);
    $laporan_query->execute();
    $laporan = $laporan_query->get_result();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Laporan Mahasiswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/dosenstyle.css">
</head>
<body>

<?php require '../../general/back.php';?>

<div class="container">
    <h1>Kelola Laporan Mahasiswa</h1>

    <h2>Pilih Praktikum</h2>
    <form method="POST" class="filter-form">
        <select name="praktikum_id" required>
            <option value="">Pilih Praktikum</option>
            <?php while ($row = $praktikum->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= $row['nama'] ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit" name="lihat_laporan" class="btn-filter">Lihat Laporan</button>
    </form>

    <?php if (!empty($laporan)): ?>
...
<h2>Daftar Laporan</h2>
<div class="table-container">
    <table class="styled-table">
        <tr>
            <th>Nama Mahasiswa</th>
            <th>Judul</th>
            <th>Jenis</th>
            <th>Kesimpulan</th>
          
            <th>Lampiran</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $laporan->fetch_assoc()): ?>
        <tr class="<?= $row['status'] ?>">
            <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
            <td><?= htmlspecialchars($row['laporan']) ?></td>
            <td><?= ucfirst($row['jenis']) ?></td>
            <td>
                <?php if ($row['jenis'] === 'mingguan'): ?>
                   
                    <strong>Kesimpulan:</strong> <?= nl2br($row['kesimpulan']) ?>
                <?php else: ?>
                    <em>Laporan Akhir</em>
                <?php endif; ?>
            </td>

            <td>
                <?php if (!empty($row['pdf_link'])): ?>
                    <a href="<?= $row['pdf_link'] ?>" class="btn-download" target="_blank">
                        <i class="fa-solid fa-file-pdf"></i> Unduh PDF
                    </a>
                <?php else: ?>
                    <em>Tidak tersedia</em>
                <?php endif; ?>
            </td>
            <td><?= ucfirst($row['status']) ?></td>
<td>
    <?php if ($row['status'] == 'pending'): ?>
        <form method="POST">
            <input type="hidden" name="laporan_id" value="<?= $row['id'] ?>">
            <select name="status" onchange="toggleKomentarField(this, <?= $row['id'] ?>)">
                <option value="disetujui">Setujui</option>
                <option value="ditolak">Tolak</option>
            </select>
            <div id="komentar_<?= $row['id'] ?>" style="display: none; margin-top:5px;">
                <textarea name="komentar" placeholder="Masukkan alasan penolakan" required></textarea>
            </div>
            <button type="submit" name="update_laporan" class="btn-edit">Simpan</button>
        </form>
    <?php else: ?>
        <em>Diproses</em>
    <?php endif; ?>
</td>

        </tr>
        <?php endwhile; ?>
    </table>
</div>

    <?php endif; ?>
</div>
<script>
function toggleKomentarField(select, id) {
    const komentarBox = document.getElementById("komentar_" + id);
    komentarBox.style.display = select.value === "ditolak" ? "block" : "none";
}
</script>
</body>
</html>
