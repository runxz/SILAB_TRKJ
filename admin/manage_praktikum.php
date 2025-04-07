<?php
require '../auth.php';
check_role(['admin']);
require '../db.php';

// Handle Tambah Praktikum
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_praktikum'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $dosen_id = intval($_POST['dosen_id']);

    $stmt = $conn->prepare("INSERT INTO praktikum (nama, deskripsi, dosen_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $nama, $deskripsi, $dosen_id);
    $stmt->execute();
    header("Location: manage_praktikum.php");
    exit();
}

// Handle Edit Praktikum
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_praktikum'])) {
    $id = intval($_POST['praktikum_id']);
    $nama = htmlspecialchars($_POST['nama']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $dosen_id = intval($_POST['dosen_id']);

    $stmt = $conn->prepare("UPDATE praktikum SET nama = ?, deskripsi = ?, dosen_id = ? WHERE id = ?");
    $stmt->bind_param("ssii", $nama, $deskripsi, $dosen_id, $id);
    $stmt->execute();
    header("Location: manage_praktikum.php");
    exit();
}

// Handle Hapus Praktikum
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM praktikum WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_praktikum.php");
    exit();
}

// Ambil Data Praktikum
$praktikum = $conn->query("SELECT p.*, u.nama AS nama_dosen FROM praktikum p JOIN users u ON p.dosen_id = u.id");

// Ambil Daftar Dosen
$dosen = $conn->query("SELECT id, nama FROM users WHERE role = 'dosen'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Praktikum</title>
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
    <h1>Manajemen Praktikum</h1>

    <!-- Button to Open Modal -->
    <button class="btn-add" id="openModal">Tambah Praktikum</button>

    <h2>Daftar Praktikum</h2>
    <div class="table-container">
        <table class="styled-table">
            <tr>
                <th>ID</th>
                <th>Nama Praktikum</th>
                <th>Deskripsi</th>
                <th>Dosen Pengampu</th>
                <th>Aksi</th>
            </tr>
            <?php while ($row = $praktikum->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['nama'] ?></td>
                <td><?= $row['deskripsi'] ?></td>
                <td><?= $row['nama_dosen'] ?></td>
                <td>
                    <a href="edit_praktikum.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a> |
                    <a href="manage_praktikum.php?delete=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Hapus praktikum ini?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<!-- Modal Form for Adding Praktikum -->
<div class="modal" id="modalForm">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2>Tambah Praktikum</h2>
        <form method="POST" class="praktikum-form">
            <input type="text" name="nama" placeholder="Nama Praktikum" required>
            <textarea name="deskripsi" placeholder="Deskripsi" required></textarea>
            <select name="dosen_id" required>
                <option value="">Pilih Dosen</option>
                <?php while ($row = $dosen->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= $row['nama'] ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="add_praktikum" class="btn-add">Tambah</button>
        </form>
    </div>
</div>

<!-- JavaScript -->
<script>
document.getElementById("openModal").addEventListener("click", function() {
    document.getElementById("modalForm").style.display = "block";
});

document.getElementById("closeModal").addEventListener("click", function() {
    document.getElementById("modalForm").style.display = "none";
});

window.addEventListener("click", function(event) {
    if (event.target === document.getElementById("modalForm")) {
        document.getElementById("modalForm").style.display = "none";
    }
});
</script>

</body>
</html>
