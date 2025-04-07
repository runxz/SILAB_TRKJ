<?php
require '../../auth.php';
check_role(['dosen', 'asistendosen']);
require '../../db.php';

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM jadwal WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$jadwal = $result->fetch_assoc();

if (!$jadwal) {
    echo "Data tidak ditemukan!";
    exit();
}

// Handle Edit Jadwal
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_jadwal'])) {
    $tanggal = $_POST['tanggal'];
    $waktu = $_POST['waktu'];
    $ruangan = htmlspecialchars($_POST['ruangan']);

    $stmt = $conn->prepare("UPDATE jadwal SET tanggal = ?, waktu = ?, ruangan = ? WHERE id = ?");
    $stmt->bind_param("sssi", $tanggal, $waktu, $ruangan, $id);
    $stmt->execute();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jadwal Praktikum</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/dosenstyle.css">
</head>
<body>

<?php require '../../general/back.php';?>

<div class="container">
    <h1>Edit Jadwal Praktikum</h1>

    <form method="POST" class="edit-form">
        <label for="tanggal">Tanggal:</label>
        <input type="date" name="tanggal" value="<?= $jadwal['tanggal'] ?>" required>

        <label for="waktu">Waktu:</label>
        <input type="time" name="waktu" value="<?= $jadwal['waktu'] ?>" required>

        <label for="ruangan">Ruangan:</label>
        <input type="text" name="ruangan" value="<?= $jadwal['ruangan'] ?>" required>

        <button type="submit" name="edit_jadwal" class="btn-save">Simpan</button>
    </form>
</div>

</body>
</html>
