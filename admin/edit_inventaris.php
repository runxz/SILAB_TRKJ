<?php
require '../auth.php';
check_role(['admin']);
require '../db.php';

$upload_dir = "uploads/";

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM inventaris WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$inventaris = $result->fetch_assoc();

if (!$inventaris) {
    echo "Data tidak ditemukan!";
    exit();
}

// Handle Edit Inventaris
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_inventaris'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $jumlah = intval($_POST['jumlah']);
    $kondisi = $_POST['kondisi'];
    $jenis = $_POST['jenis'];

    // Proses upload foto baru
    if (!empty($_FILES['foto']['name'])) {
        $foto_name = time() . "_" . basename($_FILES['foto']['name']);
        $foto_path = $upload_dir . $foto_name;
        move_uploaded_file($_FILES['foto']['tmp_name'], $foto_path);

        // Hapus foto lama jika ada
        if ($inventaris['foto']) {
            unlink($upload_dir . $inventaris['foto']);
        }

        $stmt = $conn->prepare("UPDATE inventaris SET nama_alat = ?, jumlah = ?, kondisi = ?, jenis = ?, foto = ? WHERE id = ?");
        $stmt->bind_param("sisssi", $nama, $jumlah, $kondisi, $jenis, $foto_name, $id);
    } else {
        $stmt = $conn->prepare("UPDATE inventaris SET nama_alat = ?, jumlah = ?, kondisi = ?, jenis = ? WHERE id = ?");
        $stmt->bind_param("sissi", $nama, $jumlah, $kondisi, $jenis, $id);
    }

    $stmt->execute();
    header("Location: manage_inventaris.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Inventaris</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/adminstyle.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="logo">Web Labor TRKJ</div>
    <a href="manage_inventaris.php" class="logout-btn"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
</nav>

<div class="container">
    <h1>Edit Inventaris</h1>

    <form method="POST" enctype="multipart/form-data" class="inventory-form">
        <input type="hidden" name="inventaris_id" value="<?= $inventaris['id'] ?>">

        <label>Nama Alat:</label>
        <input type="text" name="nama" value="<?= $inventaris['nama_alat'] ?>" required>

        <label>Jumlah:</label>
        <input type="number" name="jumlah" value="<?= $inventaris['jumlah'] ?>" required>

        <label>Kondisi:</label>
        <select name="kondisi" required>
            <option value="baik" <?= ($inventaris['kondisi'] == 'baik') ? 'selected' : '' ?>>Baik</option>
            <option value="rusak" <?= ($inventaris['kondisi'] == 'rusak') ? 'selected' : '' ?>>Rusak</option>
        </select>

        <label>Jenis:</label>
        <select name="jenis" required>
            <option value="alat" <?= ($inventaris['jenis'] == 'alat') ? 'selected' : '' ?>>Alat</option>
            <option value="bahan" <?= ($inventaris['jenis'] == 'bahan') ? 'selected' : '' ?>>Bahan</option>
            <option value="perlengkapan" <?= ($inventaris['jenis'] == 'perlengkapan') ? 'selected' : '' ?>>Perlengkapan</option>
        </select>

        <label>Foto Inventaris (Opsional):</label>
        <?php if ($inventaris['foto']): ?>
            <img src="../uploads/<?= $inventaris['foto'] ?>" class="inventory-img-preview">
        <?php endif; ?>
        <input type="file" name="foto" accept="image/*">

        <button type="submit" name="edit_inventaris" class="btn-save">Simpan</button>
    </form>
</div>

</body>
</html>
