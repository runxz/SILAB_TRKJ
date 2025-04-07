<?php
require '../auth.php';
check_role(['admin']);
require '../db.php';

$upload_dir = "../uploads/";

// Handle Hapus Inventaris
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Fetch the item to delete
    $stmt = $conn->prepare("SELECT foto FROM inventaris WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    // Delete the photo if it exists
    if ($item && $item['foto']) {
        unlink($upload_dir . $item['foto']);
    }

    // Delete the item from the database
    $stmt = $conn->prepare("DELETE FROM inventaris WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: manage_inventaris.php");
    exit();
}

// Handle Tambah Inventaris
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_inventaris'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $jumlah = intval($_POST['jumlah']);
    $kondisi = $_POST['kondisi'];
    $jenis = $_POST['jenis'];

    // Proses upload foto
    $foto = NULL;
    if (!empty($_FILES['foto']['name'])) {
        $foto_name = time() . "_" . basename($_FILES['foto']['name']);
        $foto_path = $upload_dir . $foto_name;
        move_uploaded_file($_FILES['foto']['tmp_name'], $foto_path);
        $foto = $foto_name;
    }

    $stmt = $conn->prepare("INSERT INTO inventaris (nama_alat, jumlah, kondisi, jenis, foto) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisss", $nama, $jumlah, $kondisi, $jenis, $foto);
    $stmt->execute();
    header("Location: manage_inventaris.php");
    exit();
}

// Fetch Inventaris Data
$inventaris = $conn->query("SELECT * FROM inventaris");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Inventaris</title>
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
    <h1>Manajemen Inventaris</h1>

    <!-- Filter Section -->
    <div class="filter-container">
        <label for="filter-jenis">Filter Jenis:</label>
        <select id="filter-jenis">
            <option value="all">Semua</option>
            <option value="alat">Alat</option>
            <option value="bahan">Bahan</option>
            <option value="perlengkapan">Perlengkapan</option>
        </select>
        <button class="btn-add" id="openModal">Tambah Inventaris</button>
    </div>

    <h2>Daftar Inventaris</h2>
    <div class="table-container">
        <table class="styled-table" id="inventory-table">
            <tr>
                <th>ID</th>
                <th>Foto</th>
                <th>Nama Alat</th>
                <th>Jenis</th>
                <th>Jumlah</th>
                <th>Kondisi</th>
                <th>Aksi</th>
            </tr>
            <?php while ($row = $inventaris->fetch_assoc()): ?>
            <tr class="inventaris-row" data-jenis="<?= $row['jenis'] ?>">
                <td><?= $row['id'] ?></td>
                <td>
                    <?php if ($row['foto']): ?>
                        <img src="../uploads/<?= $row['foto'] ?>" class="inventory-img">
                    <?php else: ?>
                        Tidak Ada
                    <?php endif; ?>
                </td>
                <td><?= $row['nama_alat'] ?></td>
                <td><?= ucfirst($row['jenis']) ?></td>
                <td><?= $row['jumlah'] ?></td>
                <td><?= ucfirst($row['kondisi']) ?></td>
                <td>
                    <a href="edit_inventaris.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a> |
                    <a href="manage_inventaris.php?delete=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Hapus alat ini?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<!-- Modal Form -->
<div class="modal" id="modalForm">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2>Tambah Inventaris</h2>
        <form method="POST" enctype="multipart/form-data" class="inventory-form">
            <input type="text" name="nama" placeholder="Nama Alat" required>
            <input type="number" name="jumlah" placeholder="Jumlah" required>
            <select name="kondisi" required>
                <option value="baik">Baik</option>
                <option value="rusak">Rusak</option>
            </select>
            <select name="jenis" required>
                <option value="alat">Alat</option>
                <option value="bahan">Bahan</option>
                <option value="perlengkapan">Perlengkapan</option>
            </select>
            <input type="file" name="foto" accept="image/*">
            <button type="submit" name="add_inventaris" class="btn-add">Tambah</button>
        </form>
    </div>
</div>

<!-- JavaScript -->
<script>
document.getElementById("filter-jenis").addEventListener("change", function() {
    var selectedJenis = this.value;
    var rows = document.querySelectorAll(".inventaris-row");

    rows.forEach(function(row) {
        if (selectedJenis === "all" || row.getAttribute("data-jenis") === selectedJenis) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
});

// Modal Open/Close Logic
const modal = document.getElementById("modalForm");
const openModal = document.getElementById("openModal");
const closeModal = document.getElementById("closeModal");

openModal.addEventListener("click", () => modal.style.display = "block");
closeModal.addEventListener("click", () => modal.style.display = "none");

window.addEventListener("click", (e) => {
    if (e.target === modal) {
        modal.style.display = "none";
    }
});
</script>

</body>
</html>
