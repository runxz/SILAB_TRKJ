<?php
require '../../auth.php';
check_role(['dosen']);
require '../../db.php';

$dosen_id = $_SESSION['user_id'];

// Handle Tambah Seleksi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_seleksi'])) {
    $praktikum_id = intval($_POST['praktikum_id']);
    $waktu_mulai = $_POST['waktu_mulai'];
    $waktu_selesai = $_POST['waktu_selesai'];
    $file_name = '';

    if (isset($_FILES['file_soal']) && $_FILES['file_soal']['error'] == 0) {
        $file_name = 'soal_' . time() . '_' . basename($_FILES['file_soal']['name']);
        move_uploaded_file($_FILES['file_soal']['tmp_name'], "../../uploads/" . $file_name);
    }

    $stmt = $conn->prepare("INSERT INTO seleksi (praktikum_id, dosen_id, file_soal, waktu_mulai, waktu_selesai) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $praktikum_id, $dosen_id, $file_name, $waktu_mulai, $waktu_selesai);
    $stmt->execute();
    header("Location: ./");
    exit();
}

// Handle Keputusan Seleksi (Terima/Tolak)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $seleksi_asisten_id = intval($_POST['seleksi_asisten_id']);
    $status = $_POST['status']; // "diterima" atau "ditolak"

    $stmt = $conn->prepare("UPDATE seleksi_asisten SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $seleksi_asisten_id);
    $stmt->execute();
    header("Location: ./");
    exit();
}

// Fetch Data Seleksi
$seleksi = $conn->query("SELECT seleksi.*, praktikum.nama AS nama_praktikum FROM seleksi 
                         JOIN praktikum ON seleksi.praktikum_id = praktikum.id 
                         WHERE seleksi.dosen_id = $dosen_id");

// Fetch Asisten yang Mengikuti Seleksi
$seleksi_asisten = $conn->query("SELECT sa.id, sa.file_jawaban, sa.status, sa.submitted_at, u.nama AS nama_asisten, p.nama AS nama_praktikum
                                 FROM seleksi_asisten sa
                                 JOIN seleksi s ON sa.seleksi_id = s.id
                                 JOIN users u ON sa.asisten_id = u.id
                                 JOIN praktikum p ON s.praktikum_id = p.id
                                 WHERE s.dosen_id = $dosen_id");

// Fetch Praktikum
$praktikum = $conn->query("SELECT id, nama FROM praktikum WHERE dosen_id = $dosen_id");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Seleksi</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/dosenstyle.css">
</head>
<body>

<?php require '../../general/back.php';?>

<div class="container">
    <h1>Kelola Seleksi Asisten Dosen</h1>

    <!-- Button to Open Modal -->
    <button class="btn-add" id="openModal">Tambah Seleksi</button>

    <!-- List Seleksi -->
    <h2>Daftar Seleksi</h2>
    <table class="styled-table">
        <tr>
            <th>ID</th>
            <th>Praktikum</th>
            <th>File Soal</th>
            <th>Waktu Mulai</th>
            <th>Waktu Selesai</th>
        </tr>
        <?php while ($row = $seleksi->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['nama_praktikum'] ?></td>
            <td><a href="../../uploads/<?= $row['file_soal'] ?>" download>Download</a></td>
            <td><?= $row['waktu_mulai'] ?></td>
            <td><?= $row['waktu_selesai'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <!-- List Asisten yang Mengikuti Seleksi -->
    <h2>Peserta Seleksi</h2>
    <table class="styled-table">
        <tr>
            <th>Nama Asisten</th>
            <th>Praktikum</th>
            <th>Jawaban</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $seleksi_asisten->fetch_assoc()): ?>
        <tr>
            <td><?= $row['nama_asisten'] ?></td>
            <td><?= $row['nama_praktikum'] ?></td>
            <td><a href="../../uploads/<?= $row['file_jawaban'] ?>" download>Download</a></td>
            <td><?= ucfirst($row['status']) ?></td>
            <td>
            <form method="POST" style="display:inline;">
    <input type="hidden" name="seleksi_asisten_id" value="<?= $row['id'] ?>">
    <input type="hidden" name="status" value="diterima">
    <button type="submit" name="update_status" class="btn-approve">Terima</button>
</form>

<form method="POST" style="display:inline;">
    <input type="hidden" name="seleksi_asisten_id" value="<?= $row['id'] ?>">
    <input type="hidden" name="status" value="ditolak">
    <button type="submit" name="update_status" class="btn-reject">Tolak</button>
</form>

            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- Modal Tambah Seleksi -->
<div class="modal" id="modalForm">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2>Tambah Seleksi</h2>
        <form method="POST" enctype="multipart/form-data" class="styled-form">
            <select name="praktikum_id" required>
                <option value="">Pilih Praktikum</option>
                <?php while ($row = $praktikum->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= $row['nama'] ?></option>
                <?php endwhile; ?>
            </select>
            <input type="datetime-local" name="waktu_mulai" required>
            <input type="datetime-local" name="waktu_selesai" required>
            <input type="file" name="file_soal" accept=".pdf,.doc,.docx" required>
            <button type="submit" name="add_seleksi" class="btn-add">Tambah</button>
        </form>
    </div>
</div>

<script>
document.getElementById("openModal").addEventListener("click", function() {
    document.getElementById("modalForm").style.display = "block";
});

document.getElementById("closeModal").addEventListener("click", function() {
    document.getElementById("modalForm").style.display = "none";
});

window.onclick = function(event) {
    if (event.target === document.getElementById("modalForm")) {
        document.getElementById("modalForm").style.display = "none";
    }
};
</script>

</body>
</html>
