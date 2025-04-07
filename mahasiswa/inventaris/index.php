<?php
require '../../auth.php';
require '../../db.php';

// Handle search query
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$query = "SELECT * FROM inventaris WHERE 
    nama_alat LIKE '%$search%' OR 
    jenis LIKE '%$search%' OR 
    kondisi LIKE '%$search%'";

$inventaris = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaris Laboratorium</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/inventaris.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

<!-- Navbar -->
<?php require '../../general/back.php';?>

<div class="container">
    <h1>Inventaris Laboratorium</h1>

    <!-- Search Bar -->
    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Cari alat, jenis, atau kondisi..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit"><i class="fa fa-search"></i> Cari</button>
    </form>

    <div class="inventaris-container">
        <?php while ($row = $inventaris->fetch_assoc()): ?>
        <div class="inventaris-card">
            <i class="fa fa-tools icon-bg"></i>
            <img src="../../uploads/<?= htmlspecialchars($row['foto']) ?>" alt="<?= htmlspecialchars($row['nama_alat']) ?>" class="inventaris-img">
            <div class="inventaris-info">
                <h2><?= htmlspecialchars($row['nama_alat']) ?></h2>
                <p><strong>Jenis:</strong> <?= htmlspecialchars($row['jenis']) ?></p>
                <p><strong>Jumlah:</strong> <?= htmlspecialchars($row['jumlah']) ?></p>
                <p><strong>Kondisi:</strong> <?= htmlspecialchars($row['kondisi']) ?></p>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>
