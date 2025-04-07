<?php
require '../auth.php';
check_role(['admin']);
require '../db.php';

$upload_dir = "../uploads/laporan/";

// Ensure ID is provided
if (!isset($_GET['id'])) {
    die("Laporan tidak ditemukan.");
}

$laporan_id = intval($_GET['id']);
$stmt = $conn->prepare("
    SELECT l.*, p.nama AS nama_praktikum, u.nama AS nama_mahasiswa, l.pdf_link
    FROM laporan l
    JOIN praktikum p ON l.praktikum_id = p.id
    JOIN users u ON l.mahasiswa_id = u.id
    WHERE l.id = ?
");
$stmt->bind_param("i", $laporan_id);
$stmt->execute();
$result = $stmt->get_result();
$laporan = $result->fetch_assoc();

if (!$laporan) {
    die("Laporan tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Laporan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/adminstyle.css">
    <style>
        /* ========== Report Page ========== */
        .report-image {
            width: 100%;
            max-width: 400px;
            height: auto;
            margin-left: auto;
            margin-right: auto;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: block;
        }

        /* Button Styling */
        .btn-download {
            display: inline-block;
            padding: 10px 15px;
            background-color: #e74c3c;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .btn-download:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="logo">Web Labor TRKJ</div>
    <a href="manage_laporan.php" class="logout-btn"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
</nav>

<div class="container">
    <h1>Detail Laporan</h1>

    <h2>Nama Praktikum: <?= $laporan['nama_praktikum'] ?></h2>
    <h3>Nama Mahasiswa: <?= $laporan['nama_mahasiswa'] ?></h3>

    <h3>Abstrak</h3>
    <p><?= nl2br($laporan['abstrak']) ?></p>

    <h3>Hasil Percobaan</h3>
    <?php if ($laporan['hasil_percobaan']): ?>
        <img src="<?= $upload_dir . $laporan['hasil_percobaan'] ?>" class="report-image">
    <?php else: ?>
        <p>Tidak ada hasil percobaan yang diunggah.</p>
    <?php endif; ?>

    <h3>Kesimpulan</h3>
    <p><?= nl2br($laporan['kesimpulan']) ?></p>

    <h3>File Laporan</h3>
    <p>
        <?php if (!empty($laporan['pdf_link']) && file_exists(str_replace("http://" . $_SERVER['HTTP_HOST'] . "/lab/", "../", $laporan['pdf_link']))): ?>
            <a href="<?= $laporan['pdf_link'] ?>" class="btn-download" target="_blank">
                <i class="fa-solid fa-file-pdf"></i> Download Laporan
            </a>
        <?php else: ?>
            <a href="cetak_laporan.php?id=<?= $laporan['id'] ?>" class="btn-download">
                <i class="fa-solid fa-file-pdf"></i> Buat Laporan PDF
            </a>
        <?php endif; ?>
    </p>
</div>

</body>
</html>
