<?php
require '../../auth.php';
check_role(['dosen']);
require '../../db.php';

$dosen_id = $_SESSION['user_id'];

// Ambil daftar praktikum yang dibuat oleh dosen ini
$praktikum = $conn->query("SELECT * FROM praktikum WHERE dosen_id = $dosen_id");

// Tambah Modul
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_modul'])) {
    $praktikum_id = intval($_POST['praktikum_id']);
    $judul = htmlspecialchars($_POST['judul']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    
    // Upload File
    $file_name = $_FILES['modul_file']['name'];
    $file_tmp = $_FILES['modul_file']['tmp_name'];
    $file_path = "../../uploads/modul/" . time() . "_" . $file_name;
    
    if (move_uploaded_file($file_tmp, $file_path)) {
        $stmt = $conn->prepare("INSERT INTO modul (praktikum_id, judul, deskripsi, file_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $praktikum_id, $judul, $deskripsi, $file_path);
        $stmt->execute();
    }

    header("Location: ./");
    exit();
}

// Edit Modul
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_modul'])) {
    $modul_id = intval($_POST['modul_id']);
    $judul = htmlspecialchars($_POST['judul']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);

    $stmt = $conn->prepare("UPDATE modul SET judul = ?, deskripsi = ? WHERE id = ?");
    $stmt->bind_param("ssi", $judul, $deskripsi, $modul_id);
    $stmt->execute();
    header("Location: ./");
    exit();
}

// Hapus Modul
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_modul'])) {
    $modul_id = intval($_POST['modul_id']);

    // Hapus file modul dari server
    $stmt = $conn->prepare("SELECT file_path FROM modul WHERE id = ?");
    $stmt->bind_param("i", $modul_id);
    $stmt->execute();
    $stmt->bind_result($file_path);
    $stmt->fetch();
    if ($file_path && file_exists($file_path)) {
        unlink($file_path);
    }
    
    $stmt = $conn->prepare("DELETE FROM modul WHERE id = ?");
    $stmt->bind_param("i", $modul_id);
    $stmt->execute();
    header("Location: ,/");
    exit();
}

// Fetch Modul berdasarkan Praktikum
$modul = $conn->query("SELECT m.*, p.nama as praktikum_nama FROM modul m JOIN praktikum p ON m.praktikum_id = p.id WHERE p.dosen_id = $dosen_id");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Modul</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/dosenstyle.css">
</head>
<body>

<?php require '../../general/back.php';?>
<div class="container">
    <h1>Kelola Modul Praktikum</h1>

    <!-- Button to Open Modal -->
    <button class="btn-add" id="openModal">Tambah Modul</button>

    <!-- Modul List -->
    <h2>Daftar Modul</h2>
    <div class="table-container">
        <table class="styled-table">
            <tr>
                <th>ID</th>
                <th>Judul Modul</th>
                <th>Praktikum</th>
                <th>Deskripsi</th>
                <th>File</th>
                <th>Aksi</th>
            </tr>
            <?php while ($row = $modul->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['judul'] ?></td>
                <td><?= $row['praktikum_nama'] ?></td>
                <td><?= $row['deskripsi'] ?></td>
                <td><a href="<?= $row['file_path'] ?>" target="_blank">Download</a></td>
                <td>
                    <button class="btn-edit" onclick="openEditModal(<?= $row['id'] ?>, '<?= $row['judul'] ?>', '<?= $row['deskripsi'] ?>')">Edit</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="modul_id" value="<?= $row['id'] ?>">
                        <button type="submit" name="delete_modul" class="btn-delete" onclick="return confirm('Hapus modul ini?')">Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<!-- Modal for Adding Modul -->
<div class="modal" id="modalForm">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2>Tambah Modul</h2>
        <form method="POST" enctype="multipart/form-data" class="styled-form">
            <select name="praktikum_id" required>
                <option value="">Pilih Praktikum</option>
                <?php while ($row = $praktikum->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= $row['nama'] ?></option>
                <?php endwhile; ?>
            </select>
            <input type="text" name="judul" placeholder="Judul Modul" required>
            <textarea name="deskripsi" placeholder="Deskripsi" required></textarea>
            <input type="file" name="modul_file" required>
            <button type="submit" name="add_modul" class="btn-add">Tambah</button>
        </form>
    </div>
</div>

<!-- JavaScript for Modal -->
<script>
document.getElementById("openModal").addEventListener("click", function() {
    document.getElementById("modalForm").style.display = "block";
});
document.getElementById("closeModal").addEventListener("click", function() {
    document.getElementById("modalForm").style.display = "none";
});

function openEditModal(id, judul, deskripsi) {
    document.getElementById("edit_modul_id").value = id;
    document.getElementById("edit_judul").value = judul;
    document.getElementById("edit_deskripsi").value = deskripsi;
    document.getElementById("editModal").style.display = "block";
}
</script>

</body>
</html>
