<?php
require '../../auth.php';
check_role(['dosen', 'asistendosen']);
require '../../db.php';

$dosen_id = $_SESSION['user_id'];
$tanggal_hari_ini = date("Y-m-d");

// Fetch Praktikum berdasarkan dosen
$praktikum_stmt = $conn->prepare("SELECT * FROM praktikum WHERE dosen_id = ?");
$praktikum_stmt->bind_param("i", $dosen_id);
$praktikum_stmt->execute();
$praktikum = $praktikum_stmt->get_result();

// Handle Pembuatan Absensi Otomatis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['buat_absensi'])) {
    $praktikum_id = intval($_POST['praktikum_id']);
    $jadwal_id = intval($_POST['jadwal_id']);

    $jadwal_stmt = $conn->prepare("SELECT tanggal FROM jadwal WHERE id = ?");
    $jadwal_stmt->bind_param("i", $jadwal_id);
    $jadwal_stmt->execute();
    $jadwal_result = $jadwal_stmt->get_result()->fetch_assoc();
    $tanggal = $jadwal_result['tanggal'];

    $mahasiswa_stmt = $conn->prepare("SELECT mahasiswa_id FROM mahasiswa_praktikum WHERE praktikum_id = ?");
    $mahasiswa_stmt->bind_param("i", $praktikum_id);
    $mahasiswa_stmt->execute();
    $mahasiswa_result = $mahasiswa_stmt->get_result();

    $insert_stmt = $conn->prepare("INSERT INTO absensi (mahasiswa_id, praktikum_id, jadwal_id, hadir, tanggal) VALUES (?, ?, ?, 0, ?)");
    $insert_stmt->bind_param("iiis", $mahasiswa_id, $praktikum_id, $jadwal_id, $tanggal);

    while ($mhs = $mahasiswa_result->fetch_assoc()) {
        $mahasiswa_id = $mhs['mahasiswa_id'];
        $cek_absensi = $conn->prepare("SELECT id FROM absensi WHERE mahasiswa_id = ? AND jadwal_id = ?");
        $cek_absensi->bind_param("ii", $mahasiswa_id, $jadwal_id);
        $cek_absensi->execute();
        $cek_absensi->store_result();
        
        if ($cek_absensi->num_rows == 0) {
            $insert_stmt->execute();
        }
    }
    header("Location: ./");
    exit();
}

// Handle Update Status Kehadiran
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_kehadiran'])) {
    $absensi_id = intval($_POST['absensi_id']);
    $hadir = intval($_POST['hadir']);
    
    $update_stmt = $conn->prepare("UPDATE absensi SET hadir = ? WHERE id = ?");
    $update_stmt->bind_param("ii", $hadir, $absensi_id);
    $update_stmt->execute();
    
    header("Location: ./");
    exit();
}

// Fetch Absensi Data
$absensi = [];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['lihat_absensi'])) {
    $praktikum_id = intval($_POST['praktikum_id']);
    $jadwal_id = intval($_POST['jadwal_id']);

    $absensi_query = $conn->prepare("SELECT a.*, u.nama AS nama_mahasiswa, j.tanggal, j.waktu FROM absensi a
        JOIN users u ON a.mahasiswa_id = u.id
        JOIN jadwal j ON a.jadwal_id = j.id
        WHERE a.praktikum_id = ? AND a.jadwal_id = ? ORDER BY j.tanggal ASC");
    $absensi_query->bind_param("ii", $praktikum_id, $jadwal_id);
    $absensi_query->execute();
    $absensi = $absensi_query->get_result();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Absensi Mahasiswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/dosenstyle.css">
</head>
<body>

<?php require '../../general/back.php';?>

    <!-- Modal for Creating Absensi -->
    <div class="modal" id="modalForm">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Buat Absensi</h2>
            <form method="POST" class="styled-form">
                <label>Pilih Praktikum:</label>
                <select name="praktikum_id" id="praktikum_modal" required onchange="fetchJadwalModal()">
                    <option value="">Pilih Praktikum</option>
                    <?php 
                    $praktikum->data_seek(0);
                    while ($row = $praktikum->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= $row['nama'] ?></option>
                    <?php endwhile; ?>
                </select>

                <label>Pilih Jadwal:</label>
                <select name="jadwal_id" id="jadwal_modal" required onchange="setTanggal()">
                    <option value="">Pilih Jadwal</option>
                </select>

             
                <input type="hidden" name="tanggal" id="tanggal_input" required readonly>

                <button type="submit" name="buat_absensi" class="btn-add">Buat Absensi</button>
            </form>
        </div>
    </div>
</div>

<div class="container">
    <h1>Kelola Absensi Mahasiswa</h1>

    <!-- Button to Open Modal -->
    <button class="btn-add" onclick="openModal()">Buat Absensi</button>

    <!-- Lihat Absensi -->
    <h2>Lihat Absensi</h2>
    <form method="POST" class="filter-form">
        <select name="praktikum_id" id="praktikum_id" required onchange="fetchJadwal()">
            <option value="">Pilih Praktikum</option>
            <?php 
            $praktikum->data_seek(0);
            while ($row = $praktikum->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= $row['nama'] ?></option>
            <?php endwhile; ?>
        </select>

        <select name="jadwal_id" id="jadwal_id" required>
            <option value="">Pilih Jadwal</option>
        </select>

        <button type="submit" name="lihat_absensi" class="btn-filter">Lihat Absensi</button>
    </form>

    <?php if (!empty($absensi)): ?>
        <h2>Daftar Absensi</h2>
        <div class="table-container">
            <table class="styled-table">
                <tr>
                    <th>Nama Mahasiswa</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Status Kehadiran</th>
                    <th>Aksi</th>
                </tr>
                <?php while ($row = $absensi->fetch_assoc()): ?>
                <tr class="<?= $row['hadir'] == '1' ? 'hadir' : ($row['hadir'] == '2' ? 'izin' : 'tidak-hadir') ?>">
                    <td><?= $row['nama_mahasiswa'] ?></td>
                    <td><?= $row['tanggal'] ?></td>
                    <td><?= $row['waktu'] ?></td>
                    <td>
                        <?= ($row['hadir'] == '1') ? "Hadir" : (($row['hadir'] == '2') ? "Izin" : "Tidak Hadir") ?>
                    </td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="absensi_id" value="<?= $row['id'] ?>">
                            <select name="hadir">
                                <option value="0" <?= ($row['hadir'] == '0') ? 'selected' : '' ?>>Tidak Hadir</option>
                                <option value="1" <?= ($row['hadir'] == '1') ? 'selected' : '' ?>>Hadir</option>
                                <option value="2" <?= ($row['hadir'] == '2') ? 'selected' : '' ?>>Izin</option>
                            </select>
                            <button type="submit" name="update_kehadiran" class="btn-update">Simpan</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- JavaScript -->
<script>
function openModal() {
    document.getElementById("modalForm").style.display = "block";
}

function fetchJadwal() {
    let praktikumId = document.getElementById("praktikum_id").value;
    let jadwalDropdown = document.getElementById("jadwal_id");

    fetch(`fetch_jadwal.php?praktikum_id=${praktikumId}`)
        .then(response => response.text())
        .then(data => {
            jadwalDropdown.innerHTML = data;
        });
}
</script><script>
function closeModal() {
    document.getElementById("modalForm").style.display = "none";
}

document.addEventListener("DOMContentLoaded", function() {
    document.querySelector(".close").addEventListener("click", closeModal);
});

function fetchJadwalModal() {
    let praktikumId = document.getElementById("praktikum_modal").value;
    let jadwalDropdown = document.getElementById("jadwal_modal");
    
    fetch(`fetch_jadwal.php?praktikum_id=${praktikumId}`)
        .then(response => response.text())
        .then(data => {
            jadwalDropdown.innerHTML = data;
        });
}

// Mengisi tanggal otomatis dari jadwal yang dipilih
document.getElementById("jadwal_modal").addEventListener("change", function() {
    let selectedOption = this.options[this.selectedIndex];
    let tanggalInput = document.getElementById("tanggal_input");

    if (selectedOption.dataset.tanggal) {
        tanggalInput.value = selectedOption.dataset.tanggal;
    }
});
</script>

</body>
</html>
