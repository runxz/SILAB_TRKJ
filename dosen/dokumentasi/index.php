<?php
require '../../auth.php';
check_role(['dosen', 'asistendosen']);
require '../../db.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch Praktikum for Dosen or Asisten Dosen
if ($role == 'dosen') {
    $praktikum = $conn->query("SELECT * FROM praktikum WHERE dosen_id = $user_id");
} else {
    $praktikum = $conn->query("SELECT p.* FROM praktikum p
                               JOIN asisten_praktikum ap ON p.id = ap.praktikum_id
                               WHERE ap.asisten_id = $user_id");
}

// Handle File Upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_dokumentasi'])) {
    $praktikum_id = intval($_POST['praktikum_id']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $upload_dir = "../../uploads/dokumentasi/";

    if (!empty($_FILES['file']['name'])) {
        $file_name = time() . "_" . basename($_FILES['file']['name']);
        $file_path = $upload_dir . $file_name;
        move_uploaded_file($_FILES['file']['tmp_name'], $file_path);

        $stmt = $conn->prepare("INSERT INTO dokumentasi (praktikum_id, file_path, deskripsi) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $praktikum_id, $file_name, $deskripsi);
        $stmt->execute();
        header("Location: ./");
        exit();
    }
}

// Handle File Deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $file_query = $conn->prepare("SELECT file_path FROM dokumentasi WHERE id = ?");
    $file_query->bind_param("i", $id);
    $file_query->execute();
    $file_result = $file_query->get_result()->fetch_assoc();
    
    if ($file_result) {
        unlink("../../uploads/dokumentasi/" . $file_result['file_path']);
    }

    $stmt = $conn->prepare("DELETE FROM dokumentasi WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: ./");
    exit();
}

// Fetch Dokumentasi
$dokumentasi = [];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['lihat_dokumentasi'])) {
    $praktikum_id = intval($_POST['praktikum_id']);
    $dok_query = $conn->prepare("SELECT * FROM dokumentasi WHERE praktikum_id = ?");
    $dok_query->bind_param("i", $praktikum_id);
    $dok_query->execute();
    $dokumentasi = $dok_query->get_result();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Dokumentasi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/dosenstyle.css">
</head>
<body>

<!-- Navbar -->
<?php require '../../general/back.php';?>

<div class="container">
    <h1>Kelola Dokumentasi</h1>

    <!-- Filter Dokumentasi -->
    <form method="POST" class="filter-form">
        <label for="praktikum_id">Pilih Praktikum:</label>
        <select name="praktikum_id" required>
            <option value="">Semua Praktikum</option>
            <?php while ($row = $praktikum->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= $row['nama'] ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit" name="lihat_dokumentasi" class="btn-filter">Lihat Dokumentasi</button>
        <button id="openModal" class="btn-add"><i class="fa-solid fa-upload"></i> Unggah Dokumentasi</button>
    </form>

    <?php if (!empty($dokumentasi)): ?>
        <h2>Daftar Dokumentasi</h2>
        <div class="table-container">
            <table class="styled-table">
                <tr>
                    <th>Deskripsi</th>
                    <th>File</th>
                    <th>Aksi</th>
                </tr>
                <?php while ($row = $dokumentasi->fetch_assoc()): ?>
                <tr>
                    <td><?= nl2br($row['deskripsi']) ?></td>
                    <td>
                        <a href="../../uploads/dokumentasi/<?= $row['file_path'] ?>" class="btn-download" target="_blank">
                            <i class="fa-solid fa-file"></i> Lihat File
                        </a>
                    </td>
                    <td>
                        <a href="?delete=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Hapus dokumentasi ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Form -->
<div class="modal" id="modalForm">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2>Unggah Dokumentasi</h2>
        <form method="POST" enctype="multipart/form-data" class="upload-form">
            <select name="praktikum_id" required>
                <option value="">Pilih Praktikum</option>
                <?php 
                $praktikum->data_seek(0);
                while ($row = $praktikum->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= $row['nama'] ?></option>
                <?php endwhile; ?>
            </select>
            <textarea name="deskripsi" placeholder="Deskripsi" required></textarea>
            <input type="file" name="file" accept="image/*,.pdf" required>
            <button type="submit" name="upload_dokumentasi" class="btn-add">Unggah</button>
        </form>
    </div>
</div>

<!-- JavaScript -->
<script>
document.getElementById("openModal").addEventListener("click", function(event) {
    event.preventDefault();
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
