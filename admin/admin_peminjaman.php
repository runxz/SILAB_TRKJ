<?php
require '../auth.php';
check_role(['admin']);
require '../db.php';

// Handle Update Status Peminjaman
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_peminjaman'])) {
    $peminjaman_id = intval($_POST['peminjaman_id']);
    $status = $_POST['status'];

    // Jika disetujui, kurangi stok alat
    if ($status == "disetujui") {
        $cek = $conn->prepare("SELECT alat_id, jumlah FROM peminjaman WHERE id = ?");
        $cek->bind_param("i", $peminjaman_id);
        $cek->execute();
        $cek_result = $cek->get_result()->fetch_assoc();

        $alat_id = $cek_result['alat_id'];
        $jumlah_dipinjam = $cek_result['jumlah'];

        $update_stok = $conn->prepare("UPDATE inventaris SET jumlah = jumlah - ? WHERE id = ?");
        $update_stok->bind_param("ii", $jumlah_dipinjam, $alat_id);
        $update_stok->execute();
    }

    // Update status peminjaman
    $stmt = $conn->prepare("UPDATE peminjaman SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $peminjaman_id);
    $stmt->execute();
    header("Location: admin_peminjaman.php");
    exit();
}

// Ambil Data Peminjaman
$peminjaman = $conn->query("
    SELECT p.*, u.nama AS peminjam, i.nama_alat
    FROM peminjaman p
    JOIN users u ON p.peminjam_id = u.id
    JOIN inventaris i ON p.alat_id = i.id
    ORDER BY p.status ASC
");
?>

<h2>Manajemen Peminjaman Alat</h2>
<a href="dashboard_admin.php">Kembali</a>

<table border="1">
    <tr>
        <th>Peminjam</th>
        <th>Alat</th>
        <th>Jumlah</th>
        <th>Tanggal Pinjam</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>
    <?php while ($row = $peminjaman->fetch_assoc()): ?>
    <tr>
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
                    <button type="submit" name="update_peminjaman">Simpan</button>
                </form>
            <?php elseif ($row['status'] == 'disetujui'): ?>
                <form method="POST">
                    <input type="hidden" name="peminjaman_id" value="<?= $row['id'] ?>">
                    <select name="status">
                        <option value="dikembalikan">Tandai Dikembalikan</option>
                    </select>
                    <button type="submit" name="update_peminjaman">Simpan</button>
                </form>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
