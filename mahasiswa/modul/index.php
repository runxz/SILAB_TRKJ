<?php
require '../../auth.php';
require '../../db.php';

// Inisialisasi query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$query = "
    SELECT 
        modul.id, 
        modul.judul, 
        modul.deskripsi, 
        modul.file_path, 
        praktikum.nama AS nama_praktikum, 
        users.nama AS nama_dosen 
    FROM modul
    JOIN praktikum ON modul.praktikum_id = praktikum.id
    JOIN users ON praktikum.dosen_id = users.id
";

// Jika ada pencarian, tambahkan kondisi WHERE
if (!empty($search)) {
    $query .= " WHERE modul.judul LIKE '%$search%' OR praktikum.nama LIKE '%$search%'";
}

$modul = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Modul</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/modul.css">
</head>
<body>

<!-- Navbar -->
<?php require '../../general/back.php';?>

<div class="container">
    <h1>Daftar Modul Praktikum</h1>

    <!-- Form Pencarian -->
    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Cari modul..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit"><i class="fa fa-search"></i></button>
    </form>

    <div class="modul-container">
        <?php 
        $index = 0;
        while ($row = $modul->fetch_assoc()): 
            $background_class = ($index % 2 == 0) ? "blue-bg" : "green-bg";
        ?>
<div class="modul-card <?= $background_class ?>">
    <i class="fa fa-book icon-bg"></i>
    <h2><?= htmlspecialchars($row['judul']) ?></h2>
    <p><strong>Praktikum:</strong> <?= htmlspecialchars($row['nama_praktikum']) ?></p>
    <p><strong>Dosen:</strong> <?= htmlspecialchars($row['nama_dosen']) ?></p>
    <p><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></p>
    <a href="<?= htmlspecialchars($row['file_path']) ?>" class="btn-download" download>Download Modul</a>
</div>

        <?php 
        $index++;
        endwhile; 
        ?>
    </div>
</div>

</body>
</html>
