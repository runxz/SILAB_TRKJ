<?php
require '../../auth.php';
check_role(['admin']);
require '../../db.php';

$status_filter = $_GET['status'] ?? '';
$tanggal_filter = $_GET['tanggal'] ?? '';

// Update status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ubah_status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'];
    $alasan = !empty($_POST['alasan']) ? trim($_POST['alasan']) : NULL;

    $stmt = $conn->prepare("UPDATE peminjaman_labor SET status = ?, alasan = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $alasan, $id);
    $stmt->execute();
    header("Location: kelola_peminjaman.php");
    exit();
}

// Fetch data with filter
$sql = "SELECT p.*, u.nama FROM peminjaman_labor p JOIN users u ON p.user_id = u.id WHERE 1=1";
$params = [];
$types = "";

if ($status_filter) {
    $sql .= " AND p.status = ?";
    $types .= "s";
    $params[] = $status_filter;
}

if ($tanggal_filter) {
    $sql .= " AND p.tanggal = ?";
    $types .= "s";
    $params[] = $tanggal_filter;
}

$sql .= " ORDER BY p.tanggal DESC";
$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Peminjaman Labor</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        form.filter-form { margin-bottom: 20px; display: flex; gap: 10px; align-items: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #eee; }
        .status-menunggu { color: orange; }
        .status-disetujui { color: green; }
        .status-ditolak { color: red; }
        textarea { width: 100%; resize: vertical; height: 60px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Kelola Peminjaman Laboratorium</h1>

    <form method="GET" class="filter-form">
        <select name="status">
            <option value="">-- Semua Status --</option>
            <option value="menunggu" <?= $status_filter == 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
            <option value="disetujui" <?= $status_filter == 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
            <option value="ditolak" <?= $status_filter == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
        </select>
        <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal_filter) ?>">
        <button type="submit">Filter</button>
    </form>

    <table>
        <tr>
            <th>Nama</th>
            <th>Tanggal</th>
            <th>Labor</th>
            <th>Waktu</th>
            <th>Kegiatan</th>
            <th>Status</th>
            <th>Alasan</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= $row['tanggal'] ?></td>
            <td><?= $row['laboratorium'] ?></td>
            <td><?= $row['waktu_mulai'] ?> - <?= $row['waktu_selesai'] ?></td>
            <td><?= htmlspecialchars($row['kegiatan']) ?></td>
            <td class="status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></td>
            <td><?= $row['alasan'] ?? '-' ?></td>
            <td>
                <?php if ($row['status'] === 'menunggu'): ?>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <select name="status" onchange="this.nextElementSibling.style.display = this.value === 'ditolak' ? 'block' : 'none';" required>
                        <option value="">Pilih</option>
                        <option value="disetujui">Setujui</option>
                        <option value="ditolak">Tolak</option>
                    </select>
                    <textarea name="alasan" placeholder="Alasan penolakan" style="display:none;"></textarea>
                    <button type="submit" name="ubah_status">Simpan</button>
                </form>
                <?php else: ?>
                    <em>Diproses</em>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
