<?php
require '../auth.php';
require '../db.php';

header('Content-Type: application/json');

// Pastikan ada kata kunci yang dikirim
if (!isset($_GET['q']) || trim($_GET['q']) === '') {
    echo json_encode([]);
    exit;
}

$q = trim($_GET['q']);
$searchTerm = "%" . $q . "%";
$results = [];

$conn->begin_transaction();

$query_map = [
    "praktikum" => "SELECT id, nama AS nama_item FROM praktikum WHERE nama LIKE ? LIMIT 5",
    "modul" => "SELECT id, judul AS nama_item FROM modul WHERE judul LIKE ? LIMIT 5",
    "laporan" => "SELECT id, status AS nama_item FROM laporan WHERE status LIKE ? LIMIT 5",
    "absensi" => "SELECT id, hadir AS nama_item FROM absensi WHERE hadir LIKE ? LIMIT 5",
    "jadwal" => "SELECT id, CONCAT(tanggal, ' ', waktu, ' - ', ruangan) AS nama_item FROM jadwal 
                 WHERE tanggal LIKE ? OR waktu LIKE ? OR ruangan LIKE ? LIMIT 5"
];

$sql_parts = [];
$params = [];
$types = "";

foreach ($query_map as $table => $query) {
    if ($table === "jadwal") {
        $sql_parts[] = $query;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    } else {
        $sql_parts[] = $query;
        $params[] = $searchTerm;
        $types .= "s";
    }
}

// Gabungkan semua query dengan UNION
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
$conn->commit();
$conn->close();

echo json_encode($results);
?>
