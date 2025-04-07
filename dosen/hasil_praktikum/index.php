<?php
require '../../auth.php';
check_role(['dosen', 'asistendosen']);
require '../../db.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Ambil Praktikum yang Dibuat oleh Dosen atau yang Diikuti Asisten
if ($role == 'dosen') {
    $praktikum = $conn->query("SELECT * FROM praktikum WHERE dosen_id = $user_id");
} else {
    $praktikum = $conn->query("SELECT p.* FROM praktikum p
                               JOIN asisten_praktikum ap ON p.id = ap.praktikum_id
                               WHERE ap.asisten_id = $user_id");
}

// Handle Update Status Hasil Praktikum
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_hasil'])) {
    $hasil_id = intval($_POST['hasil_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE hasil_praktikum SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $hasil_id);
    $stmt->execute();
    header("Location: ./");
    exit();
}

// Ambil Data Hasil Praktikum
$hasil_praktikum = [];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['lihat_hasil'])) {
    $praktikum_id = intval($_POST['praktikum_id']);
    $hasil_query = $conn->prepare("
        SELECT h.*, u.nama AS nama_mahasiswa 
        FROM hasil_praktikum h
        JOIN users u ON h.mahasiswa_id = u.id
        WHERE h.praktikum_id = ?
        ORDER BY h.status ASC
    ");
    $hasil_query->bind_param("i", $praktikum_id);
    $hasil_query->execute();
    $hasil_praktikum = $hasil_query->get_result();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Hasil Praktikum</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/dosenstyle.css">
</head>
<body>

<?php require '../../general/back.php';?>
<div class="container">
    <h1>Kelola Hasil Praktikum</h1>

    <!-- Form Pemilihan Praktikum -->
    <form method="POST">
        <label for="praktikum_id">Pilih Praktikum:</label>
        <select name="praktikum_id" required>
            <option value="">-- Pilih Praktikum --</option>
            <?php while ($row = $praktikum->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= $row['nama'] ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit" name="lihat_hasil"><i class="fa-solid fa-eye"></i> Lihat Hasil</button>
    </form>

    <!-- Tabel Hasil Praktikum -->
    <?php if (!empty($hasil_praktikum)): ?>
        <div class="table-container">
            <table class="styled-table">
                <tr>
                    <th>Nama Mahasiswa</th>
                    <th>Deskripsi Hasil</th>
                    <th>Gambar</th>
                    <th>File PDF</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                <?php while ($row = $hasil_praktikum->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['nama_mahasiswa'] ?></td>
                    <td><?= nl2br($row['hasil']) ?></td>
                    <td>
                        <?php if ($row['gambar']): ?>
                            <a href="../uploads/hasil_praktikum/<?= $row['gambar'] ?>" target="_blank">
                                <img src="../uploads/hasil_praktikum/<?= $row['gambar'] ?>" class="table-img">
                            </a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['laporan_pdf']): ?>
                            <a href="../uploads/hasil_praktikum/<?= $row['laporan_pdf'] ?>" target="_blank">
                                <i class="fa-solid fa-file-pdf"></i> Unduh PDF
                            </a>
                        <?php else: ?>
                            <em>Tidak ada file</em>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span>
                    </td>
                    <td>
                        <?php if ($row['status'] == 'pending'): ?>
                            <form method="POST">
                                <input type="hidden" name="hasil_id" value="<?= $row['id'] ?>">
                                <select name="status">
                                    <option value="disetujui">Setujui</option>
                                    <option value="ditolak">Tolak</option>
                                </select>
                                <button type="submit" name="update_hasil" class="btn-action">
                                    <i class="fa-solid fa-check"></i> Simpan
                                </button>
                            </form>
                        <?php else: ?>
                            <em>Sudah Diproses</em>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
