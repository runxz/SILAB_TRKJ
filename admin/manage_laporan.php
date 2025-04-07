<?php
require '../auth.php';
check_role(['admin']);
require '../db.php';

// Fetch Laporan Statistics
$stats_query = $conn->query("
    SELECT 
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
        SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) AS approved_count,
        SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) AS rejected_count
    FROM laporan
");

$stats = $stats_query->fetch_assoc();

// Fetch Practicum List
$praktikum_list = $conn->query("SELECT id, nama FROM praktikum");

// Fetch Reports (Default: All)
$selected_praktikum = isset($_GET['praktikum_id']) ? intval($_GET['praktikum_id']) : null;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "SELECT l.*, p.nama AS nama_praktikum, u.nama AS nama_mahasiswa FROM laporan l
          JOIN praktikum p ON l.praktikum_id = p.id
          JOIN users u ON l.mahasiswa_id = u.id WHERE 1=1";

if ($selected_praktikum) {
    $query .= " AND l.praktikum_id = $selected_praktikum";
}

if (!empty($search_query)) {
    $query .= " AND u.nama LIKE '%$search_query%'";
}

$query .= " ORDER BY l.status ASC";
$laporan = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Laporan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/adminstyle.css">
    <style>
        /* ========== Filter & Search Section ========== */
.filter-form {
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
}

.filter-form label {
    font-weight: bold;
}

.filter-form select, .filter-form input, .filter-form button {
    padding: 10px;
    font-size: 16px;
    border-radius: 5px;
    border: 1px solid #ddd;
}

.filter-form input {
    width: 200px;
}

.filter-form button {
    background-color: #0073e6;
    color: white;
    border: none;
    cursor: pointer;
}

.filter-form button:hover {
    background-color: #005bb5;
}

    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="logo">Web Labor TRKJ</div>
    <a href="./" class="logout-btn"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
</nav>

<div class="container">
    <h1>Manajemen Laporan</h1>

    <!-- Laporan Statistics -->
    <h2>Statistik Laporan</h2>
    <div class="stats-container">
        <div class="stat-box pending">
            <i class="fa-solid fa-hourglass-half"></i>
            <span>Pending</span>
            <h3><?= $stats['pending_count'] ?></h3>
        </div>
        <div class="stat-box approved">
            <i class="fa-solid fa-check-circle"></i>
            <span>Disetujui</span>
            <h3><?= $stats['approved_count'] ?></h3>
        </div>
        <div class="stat-box rejected">
            <i class="fa-solid fa-times-circle"></i>
            <span>Ditolak</span>
            <h3><?= $stats['rejected_count'] ?></h3>
        </div>
    </div>

    <!-- Filter and Search -->
    <h2>Filter & Pencarian</h2>
    <form method="GET" class="filter-form">
        <label for="praktikum_id">Pilih Praktikum:</label>
        <select name="praktikum_id" id="praktikum_id" onchange="this.form.submit()">
            <option value="">Semua Praktikum</option>
            <?php while ($row = $praktikum_list->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>" <?= ($selected_praktikum == $row['id']) ? 'selected' : '' ?>>
                    <?= $row['nama'] ?>
                </option>
            <?php endwhile; ?>
        </select>
        
        <input type="text" name="search" placeholder="Cari Mahasiswa..." value="<?= htmlspecialchars($search_query) ?>">
        <button type="submit"><i class="fa-solid fa-search"></i> Cari</button>
    </form>

    <!-- List Laporan -->
    <h2>Daftar Laporan</h2>
    <div class="table-container">
        <table class="styled-table">
            <tr>
                <th>ID</th>
                <th>Nama Mahasiswa</th>
                <th>Praktikum</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            <?php while ($row = $laporan->fetch_assoc()): ?>
            <tr class="<?= $row['status'] ?>">
                <td><?= $row['id'] ?></td>
                <td><?= $row['nama_mahasiswa'] ?></td>
                <td><?= $row['nama_praktikum'] ?></td>
                <td><?= ucfirst($row['status']) ?></td>
                <td>
                    <a href="view_laporan.php?id=<?= $row['id'] ?>" class="btn-view">Lihat</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>
