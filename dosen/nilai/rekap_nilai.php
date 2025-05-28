<?php
require '../../auth.php';
require '../../db.php';
check_role(['dosen', 'asistendosen']);

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if (isset($_GET['praktikum_id'])) {
    $praktikum_id = intval($_GET['praktikum_id']);

    $stmt = $conn->prepare("
        SELECT 
            u.nama,
            np.pretest, np.keaktifan, np.laporan_per_acara, np.laporan_akhir, np.responsi,
            ROUND((np.pretest*0.05 + np.keaktifan*0.10 + np.laporan_per_acara*0.35 + np.laporan_akhir*0.10 + np.responsi*0.40), 2) AS total,
            (
                SELECT COUNT(*) FROM absensi a 
                WHERE a.mahasiswa_id = u.id AND a.praktikum_id = ? AND a.hadir != '0'
            ) / 
            (
                SELECT COUNT(*) FROM absensi a2 
                WHERE a2.mahasiswa_id = u.id AND a2.praktikum_id = ?
            ) * 100 AS kehadiran,
            (
                SELECT GROUP_CONCAT(DISTINCT l.laporan SEPARATOR ', ')
                FROM laporan l
                WHERE l.mahasiswa_id = u.id AND l.praktikum_id = ?
            ) AS judul_laporan
        FROM nilai_praktikum np
        JOIN users u ON u.id = np.mahasiswa_id
        WHERE np.praktikum_id = ?
    ");
    $stmt->bind_param("iiii", $praktikum_id, $praktikum_id, $praktikum_id, $praktikum_id);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Nilai Praktikum</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        h1 { color: #003366; margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #eef; }
        .btn-print {
            background: #0073e6; color: #fff; padding: 10px 16px; font-weight: bold;
            border: none; border-radius: 6px; cursor: pointer; margin-top: 15px;
        }
        .btn-print:hover { background-color: #005bb5; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Rekap Nilai Praktikum</h1>

        <form method="GET">
            <label>Pilih Praktikum:</label>
            <select name="praktikum_id" onchange="this.form.submit()">
                <option value="">-- Pilih Praktikum --</option>
                <?php
                if ($role === 'dosen') {
                    $praktikum = $conn->query("SELECT id, nama FROM praktikum WHERE dosen_id = $user_id");
                } else {
                    $praktikum = $conn->query("
                        SELECT p.id, p.nama FROM praktikum p
                        JOIN asisten_praktikum ap ON p.id = ap.praktikum_id
                        WHERE ap.asisten_id = $user_id
                    ");
                }
                while ($p = $praktikum->fetch_assoc()):
                ?>
                    <option value="<?= $p['id'] ?>" <?= ($_GET['praktikum_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                        <?= $p['nama'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <?php if (!empty($result) && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nama Mahasiswa</th>
                        <th>Pretest</th>
                        <th>Keaktifan</th>
                        <th>LKP</th>
                        <th>Laporan Akhir</th>
                        <th>Responsi</th>
                        <th>Total (%)</th>
                        <th>% Kehadiran</th>
                        <th>Judul Laporan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['nama'] ?></td>
                        <td><?= $row['pretest'] ?></td>
                        <td><?= $row['keaktifan'] ?></td>
                        <td><?= $row['laporan_per_acara'] ?></td>
                        <td><?= $row['laporan_akhir'] ?></td>
                        <td><?= $row['responsi'] ?></td>
                        <td><strong><?= $row['total'] ?></strong></td>
                        <td><?= round($row['kehadiran']) ?>%</td>
                        <td><?= $row['judul_laporan'] ?: '-' ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <button onclick="window.print()" class="btn-print">Cetak</button>
        <?php elseif (isset($_GET['praktikum_id'])): ?>
            <p>Tidak ada nilai ditemukan untuk praktikum ini.</p>
        <?php endif; ?>
    </div>
</body>
</html>
