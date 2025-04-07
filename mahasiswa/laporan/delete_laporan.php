<?php
require '../../auth.php';
check_role(['mahasiswa']);
require '../../db.php';

$mahasiswa_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    die("Laporan tidak ditemukan.");
}

$laporan_id = intval($_GET['id']);

// Check if the report is still pending
$stmt = $conn->prepare("SELECT * FROM laporan WHERE id = ? AND mahasiswa_id = ? AND status = 'pending'");
$stmt->bind_param("ii", $laporan_id, $mahasiswa_id);
$stmt->execute();
$result = $stmt->get_result();
$laporan = $result->fetch_assoc();

if (!$laporan) {
    die("Laporan tidak ditemukan atau tidak bisa dihapus.");
}

// Delete the report
$stmt = $conn->prepare("DELETE FROM laporan WHERE id = ? AND mahasiswa_id = ?");
$stmt->bind_param("ii", $laporan_id, $mahasiswa_id);
$stmt->execute();

header("Location: ./");
exit();
?>
