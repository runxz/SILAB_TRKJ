<?php
require '../auth.php';
require '../db.php';

$user_role = $_SESSION['role'] ?? 'guest';

if (!isset($_GET['q']) || trim($_GET['q']) === '') {
    die("Masukkan kata kunci!");
}

$q = trim($_GET['q']);
$filters = $_GET['filter'] ?? [];

$allowed_tables = [
    'admin' => ['praktikum', 'modul', 'laporan', 'absensi', 'mahasiswa_praktikum', 'hasil_praktikum', 'jadwal', 'asisten_praktikum', 'users'],
    'mahasiswa' => ['praktikum', 'modul', 'hasil_praktikum', 'laporan', 'absensi', 'jadwal', 'users'],
    'dosen' => ['praktikum', 'jadwal', 'mahasiswa_praktikum', 'laporan', 'absensi', 'asisten_praktikum', 'users'],
    'asisten_dosen' => ['praktikum', 'modul', 'mahasiswa_praktikum', 'hasil_praktikum', 'absensi', 'users']
];

$valid_tables = array_intersect($allowed_tables[$user_role] ?? [], $filters);
if (empty($valid_tables)) {
    $valid_tables = $allowed_tables[$user_role] ?? [];
}

if (empty($valid_tables)) {
    die("Akses ditolak atau tidak ada tabel yang dapat dicari.");
}

$conn->begin_transaction();

$results = [];
$searchTerm = "%" . $q . "%";

$query_map = [
    "praktikum" => "SELECT id, nama AS nama_item, 'praktikum' AS sumber, NULL AS file_path FROM praktikum WHERE nama LIKE ?",
    "modul" => "SELECT id, judul AS nama_item, 'modul' AS sumber, file_path FROM modul WHERE judul LIKE ?",
    "laporan" => "SELECT id, status AS nama_item, 'laporan' AS sumber, pdf_link AS file_path FROM laporan WHERE status LIKE ?",
    "absensi" => "SELECT id, hadir AS nama_item, 'absensi' AS sumber, NULL AS file_path FROM absensi WHERE hadir LIKE ?",
    "mahasiswa_praktikum" => "SELECT id, mahasiswa_id AS nama_item, 'mahasiswa_praktikum' AS sumber, NULL AS file_path FROM mahasiswa_praktikum WHERE mahasiswa_id LIKE ?",
    "hasil_praktikum" => "SELECT id, hasil AS nama_item, 'hasil_praktikum' AS sumber, laporan_pdf AS file_path FROM hasil_praktikum WHERE hasil LIKE ?",
    "asisten_praktikum" => "SELECT id, asisten_id AS nama_item, 'asisten_praktikum' AS sumber, NULL AS file_path FROM asisten_praktikum WHERE asisten_id LIKE ?",
    "jadwal" => "SELECT id, CONCAT(tanggal, ' ', waktu, ' - ', ruangan) AS nama_item, 'jadwal' AS sumber, NULL AS file_path FROM jadwal 
                 WHERE tanggal LIKE ? OR waktu LIKE ? OR ruangan LIKE ?"
];

if (in_array("users", $valid_tables)) {
    $user_conditions = [];
    
    if ($user_role === 'mahasiswa') {
        $user_conditions[] = "role = 'dosen'";
        $user_conditions[] = "role = 'asisten_dosen'";
    } elseif ($user_role === 'asisten_dosen') {
        $user_conditions[] = "role = 'dosen'";
        $user_conditions[] = "role = 'mahasiswa'";
    } elseif ($user_role === 'dosen') {
        $user_conditions[] = "role = 'asisten_dosen'";
        $user_conditions[] = "role = 'mahasiswa'";
    }

    if (!empty($user_conditions)) {
        $query_map["users"] = "SELECT id, nama AS nama_item, role AS sumber, NULL AS file_path FROM users 
                               WHERE nama LIKE ? AND (" . implode(" OR ", $user_conditions) . ")";
    }
}

$sql_parts = [];
$params = [];
$types = "";

foreach ($valid_tables as $table) {
    if (isset($query_map[$table])) {
        $sql_parts[] = $query_map[$table];

        if ($table === "jadwal") {
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "sss";
        } else {
            $params[] = $searchTerm;
            $types .= "s";
        }
    }
}

if (!empty($sql_parts)) {
    $final_query = implode(" UNION ", $sql_parts);
    $stmt = $conn->prepare($final_query);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }

    $stmt->close();
}

$conn->commit();
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dosenstyle.css">
</head>
<body>
    <div class="container">
    <h2>Hasil Pencarian : <?php echo $q;?></h2>

    <?php if (!empty($results)): ?>
        <table class="styled-table">
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Sumber</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($results as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td>
                        <?php if (!empty($row['file_path'])): ?>
                            <a href="<?= htmlspecialchars($row['file_path']) ?>" download>
                                <?= htmlspecialchars($row['nama_item']) ?> (Unduh)
                            </a>
                        <?php elseif ($row['sumber'] === 'dosen' || $row['sumber'] === 'asisten_dosen' || $row['sumber'] === 'mahasiswa'): ?>
                            <a href="detil.php?id=<?= htmlspecialchars($row['id']) ?>&role=<?= htmlspecialchars($row['sumber']) ?>">
                                <?= htmlspecialchars($row['nama_item']) ?>
                            </a>
                        <?php else: ?>
                            <?= htmlspecialchars($row['nama_item']) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['sumber']) ?></td>
                    <td>
                        <?php if (!empty($row['file_path'])): ?>
                            <a href="<?= htmlspecialchars($row['file_path']) ?>" download>Download</a>
                        <?php elseif ($row['sumber'] === 'dosen' || $row['sumber'] === 'asisten_dosen' || $row['sumber'] === 'mahasiswa'): ?>
                            <a href="detil.php?id=<?= htmlspecialchars($row['id']) ?>&role=<?= htmlspecialchars($row['sumber']) ?>">Lihat Profil</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Tidak ada hasil ditemukan.</p>
    <?php endif; ?>
    </div>
</body>
</html>
