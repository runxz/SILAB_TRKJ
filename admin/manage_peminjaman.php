<?php
require '../auth.php';
check_role(['admin']);
require '../db.php';

// Handle Update Status Peminjaman
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $id = intval($_POST['peminjaman_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE peminjaman SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    header("Location: manage_peminjaman.php");
    exit();
}

// Fetch Peminjaman Statistics
$peminjaman_query = $conn->query("
    SELECT 
        SUM(CASE WHEN status = 'menunggu' THEN jumlah ELSE 0 END) AS pending,
        SUM(CASE WHEN status = 'disetujui' THEN jumlah ELSE 0 END) AS approved,
        SUM(CASE WHEN status = 'ditolak' THEN jumlah ELSE 0 END) AS rejected,
        SUM(CASE WHEN status = 'dikembalikan' THEN jumlah ELSE 0 END) AS returned
    FROM peminjaman
");
$peminjaman = $peminjaman_query->fetch_assoc();

// Ambil Data Peminjaman
$peminjaman_list = $conn->query("SELECT p.*, u.nama AS peminjam, i.nama_alat 
                            FROM peminjaman p 
                            JOIN users u ON p.peminjam_id = u.id 
                            JOIN inventaris i ON p.alat_id = i.id 
                            ORDER BY p.status ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Peminjaman</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/adminstyle.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="logo">Web Labor TRKJ</div>
    <a href="./" class="logout-btn"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
</nav>

<div class="container">
    <h1>Manajemen Peminjaman Alat</h1>

    <!-- Peminjaman Overview -->
    <h2>Statistik Peminjaman</h2>
    <div class="stats-container">
        <div class="stat-box pending">
            <i class="fa-solid fa-hourglass-half"></i>
            <span>Menunggu</span>
            <h3><?= $peminjaman['pending'] ?></h3>
        </div>
        <div class="stat-box approved">
            <i class="fa-solid fa-check-circle"></i>
            <span>Disetujui</span>
            <h3><?= $peminjaman['approved'] ?></h3>
        </div>
        <div class="stat-box rejected">
            <i class="fa-solid fa-times-circle"></i>
            <span>Ditolak</span>
            <h3><?= $peminjaman['rejected'] ?></h3>
        </div>
        <div class="stat-box returned">
            <i class="fa-solid fa-box-open"></i>
            <span>Dikembalikan</span>
            <h3><?= $peminjaman['returned'] ?></h3>
        </div>
    </div>

    <h2>Daftar Peminjaman</h2>
    <div class="table-container">
        <table class="styled-table">
            <tr>
                <th>ID</th>
                <th>Peminjam</th>
                <th>Alat</th>
                <th>Jumlah</th>
                <th>Tanggal Pinjam</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            <?php while ($row = $peminjaman_list->fetch_assoc()): ?>
            <tr class="<?= $row['status'] ?>">
                <td><?= $row['id'] ?></td>
                <td><?= $row['peminjam'] ?></td>
                <td><?= $row['nama_alat'] ?></td>
                <td><?= $row['jumlah'] ?></td>
                <td><?= $row['tanggal_pinjam'] ?></td>
                <td><?= ucfirst($row['status']) ?></td>
                <td>
                    <?php if ($row['status'] == 'menunggu'): ?>
                        <form method="POST">
                            <input type="hidden" name="peminjaman_id" value="<?= $row['id'] ?>">
                            <select name="status">
                                <option value="disetujui">Setujui</option>
                                <option value="ditolak">Tolak</option>
                            </select>
                            <button type="submit" name="update_status">Simpan</button>
                        </form>
                    <?php elseif ($row['status'] == 'disetujui'): ?>
                        <form method="POST">
                            <input type="hidden" name="peminjaman_id" value="<?= $row['id'] ?>">
                            <select name="status">
                                <option value="dikembalikan">Tandai Dikembalikan</option>
                            </select>
                            <button type="submit" name="update_status">Simpan</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>
