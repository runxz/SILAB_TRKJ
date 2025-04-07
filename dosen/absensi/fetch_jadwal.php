<?php
require '../../db.php';

if (!isset($_GET['praktikum_id']) || empty($_GET['praktikum_id'])) {
    echo '<option value="">Pilih Jadwal</option>';
    exit;
}

$praktikum_id = intval($_GET['praktikum_id']);

$jadwal = $conn->prepare("SELECT id, tanggal, waktu FROM jadwal WHERE praktikum_id = ?");
$jadwal->bind_param("i", $praktikum_id);
$jadwal->execute();
$result = $jadwal->get_result();

echo '<option value="">Pilih Jadwal</option>';
while ($row = $result->fetch_assoc()) {
    echo '<option value="'.$row['id'].'" data-tanggal="'.$row['tanggal'].'">'
        .$row['tanggal'].' - '.$row['waktu'].'</option>';
}
?>
