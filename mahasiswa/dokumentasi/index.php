<?php
require '../../auth.php';
require '../../db.php';

// Ambil kata kunci pencarian jika ada
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// Query SQL dengan filter pencarian
$sql = "
    SELECT dokumentasi.file_path, dokumentasi.deskripsi, dokumentasi.uploaded_at, 
           praktikum.nama AS judul_praktikum 
    FROM dokumentasi
    JOIN praktikum ON dokumentasi.praktikum_id = praktikum.id";

if (!empty($search)) {
    $sql .= " WHERE praktikum.nama LIKE ? OR dokumentasi.deskripsi LIKE ? ";
}

// Urutkan berdasarkan waktu upload terbaru
$sql .= " ORDER BY dokumentasi.uploaded_at DESC";

// Persiapkan dan jalankan query
$stmt = $conn->prepare($sql);

if (!empty($search)) {
    $searchParam = "%$search%";
    $stmt->bind_param("ss", $searchParam, $searchParam);
}

$stmt->execute();
$dokumentasi = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumentasi Kegiatan</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/dokumentasi.css">
</head>
<body>

<!-- Navbar -->
<?php require '../../general/back.php';?>

<div class="container">
    <h1>Dokumentasi Kegiatan</h1>

    <!-- 🔎 Form Pencarian -->
    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Cari dokumentasi..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit"><i class="fa fa-search"></i> Cari</button>
    </form>

    <div class="dokumentasi-container">
        <?php if ($dokumentasi->num_rows > 0): ?>
            <?php while ($row = $dokumentasi->fetch_assoc()): ?>
            <div class="dokumentasi-card">
                <img src="../../uploads/dokumentasi/<?= htmlspecialchars($row['file_path']) ?>" alt="Dokumentasi Praktikum" class="dokumentasi-img">
                <div class="dokumentasi-content">
                    <h2><?= htmlspecialchars($row['judul_praktikum']) ?></h2>
                    <p class="date"><i class="fa fa-calendar"></i> <?= date("d M Y", strtotime($row['uploaded_at'])) ?></p>
                    <p><?= htmlspecialchars($row['deskripsi']) ?></p>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-results">Tidak ada dokumentasi ditemukan.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
