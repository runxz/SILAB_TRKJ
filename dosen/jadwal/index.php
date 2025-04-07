<?php
require '../../auth.php';
check_role(['dosen', 'asistendosen']);
require '../../db.php';


$dosen_id = $_SESSION['user_id'];

// Handle Tambah Jadwal Praktikum
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_jadwal'])) {
    $praktikum_id = intval($_POST['praktikum_id']);
    $tanggal = $_POST['tanggal'];
    $waktu = $_POST['waktu'];
    $ruangan = htmlspecialchars($_POST['ruangan']);

    $stmt = $conn->prepare("INSERT INTO jadwal (praktikum_id, tanggal, waktu, ruangan) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $praktikum_id, $tanggal, $waktu, $ruangan);
    $stmt->execute();
    header("Location: index.php");
    exit();
}

// Handle Hapus Jadwal
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM jadwal WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: index.php");
    exit();
}

// Fetch Data
$praktikum = $conn->query("SELECT * FROM praktikum WHERE dosen_id = $dosen_id");
$jadwal = $conn->query("SELECT j.*, p.nama AS nama_praktikum 
                        FROM jadwal j 
                        JOIN praktikum p ON j.praktikum_id = p.id 
                        WHERE p.dosen_id = $dosen_id 
                        ORDER BY j.tanggal ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jadwal Praktikum</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/dosenstyle.css">
</head>
<body>

<?php require '../../general/back.php';?>

<div class="container">
    <h1>Kelola Jadwal Praktikum</h1>

    <!-- Button to Open Modal -->
    <button class="btn-add" id="openModal">Tambah Jadwal</button>

    <!-- Jadwal Praktikum List -->
    <h2>Daftar Jadwal</h2>
    <div class="table-container">
        <table class="styled-table">
            <tr>
                <th>ID</th>
                <th>Praktikum</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Ruangan</th>
                <th>Aksi</th>
            </tr>
            <?php while ($row = $jadwal->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['nama_praktikum'] ?></td>
                <td><?= $row['tanggal'] ?></td>
                <td><?= $row['waktu'] ?></td>
                <td><?= $row['ruangan'] ?></td>
                <td>
                    <a href="edit_jadwal.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a> |
                    <a href="dosen_jadwal.php?delete=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Hapus jadwal ini?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<!-- Modal for Adding Jadwal -->
<div class="modal" id="modalForm">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2>Tambah Jadwal Praktikum</h2>
        <form method="POST" class="styled-form">
            <select name="praktikum_id" required>
                <option value="">Pilih Praktikum</option>
                <?php while ($row = $praktikum->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= $row['nama'] ?></option>
                <?php endwhile; ?>
            </select>
            <input type="date" name="tanggal" required>
            <input type="time" name="waktu" required>
            <input type="text" name="ruangan" placeholder="Ruangan" required>
            <button type="submit" name="add_jadwal" class="btn-add">Tambah</button>
        </form>
    </div>
</div>

<!-- JavaScript for Modal -->
<script>
document.getElementById("openModal").addEventListener("click", function() {
    document.getElementById("modalForm").style.display = "block";
});

document.getElementById("closeModal").addEventListener("click", function() {
    document.getElementById("modalForm").style.display = "none";
});

window.onclick = function(event) {
    if (event.target === document.getElementById("modalForm")) {
        document.getElementById("modalForm").style.display = "none";
    }
};
</script>

</body>
</html>
