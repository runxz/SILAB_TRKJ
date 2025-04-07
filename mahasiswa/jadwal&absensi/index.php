<?php
require '../../auth.php';
check_role(['mahasiswa']);
require '../../db.php';

$mahasiswa_id = $_SESSION['user_id'];
$tanggal_hari_ini = date("Y-m-d");

// Ambil Jadwal Praktikum yang Diikuti Mahasiswa
$jadwal = $conn->query("
    SELECT j.*, p.nama AS nama_praktikum, p.dosen_id, u.nama AS nama_dosen
    FROM jadwal j
    JOIN praktikum p ON j.praktikum_id = p.id
    JOIN users u ON p.dosen_id = u.id
    JOIN mahasiswa_praktikum mp ON p.id = mp.praktikum_id
    WHERE mp.mahasiswa_id = $mahasiswa_id
    ORDER BY j.tanggal ASC
");

// Handle Pengisian Absensi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ambil_absensi'])) {
    $praktikum_id = intval($_POST['praktikum_id']);
    $jadwal_id = intval($_POST['jadwal_id']);
    $hadir = intval($_POST['hadir']); // 1 = Hadir, 2 = Izin

    // Cek apakah absensi sudah ada
    $cek_absensi = $conn->prepare("SELECT id FROM absensi WHERE mahasiswa_id = ? AND praktikum_id = ? AND tanggal = ?");
    $cek_absensi->bind_param("iis", $mahasiswa_id, $praktikum_id, $tanggal_hari_ini);
    $cek_absensi->execute();
    $cek_absensi->store_result();

    if ($cek_absensi->num_rows > 0) {
        // Jika sudah ada, update ke Hadir atau Izin
        $stmt = $conn->prepare("UPDATE absensi SET hadir = ? WHERE mahasiswa_id = ? AND praktikum_id = ? AND tanggal = ?");
        $stmt->bind_param("iiis", $hadir, $mahasiswa_id, $praktikum_id, $tanggal_hari_ini);
    } else {
        // Jika belum ada, tambahkan absensi baru
        $stmt = $conn->prepare("INSERT INTO absensi (mahasiswa_id, praktikum_id, hadir, tanggal) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $mahasiswa_id, $praktikum_id, $hadir, $tanggal_hari_ini);
    }
    
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
    <title>Jadwal Praktikum</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/mahasiswastyle.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            width: 350px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-absen {
            background-color: #2ecc71;
            color: white;
            padding: 12px 18px;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-absen:hover {
            background-color: #27ae60;
        }

        .btn-close {
            background-color: #e74c3c;
            color: white;
            padding: 10px 15px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-close:hover {
            background-color: #c0392b;
        }

        .fa-icon {
            font-size: 20px;
        }

        .status-info {
            color: #e67e22;
            font-weight: bold;
        }

        .table-container {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<?php require '../../general/back.php';?>

<div class="container">
    <h1>Jadwal Praktikum</h1>

    <div class="table-container">
        <table class="styled-table">
            <tr>
                <th>Nama Praktikum</th>
                <th>Dosen</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Ruangan</th>
                <th>Absensi</th>
            </tr>
            <?php while ($row = $jadwal->fetch_assoc()): ?>
            <tr>
                <td><?= $row['nama_praktikum'] ?></td>
                <td><?= $row['nama_dosen'] ?></td>
                <td><?= $row['tanggal'] ?></td>
                <td><?= $row['waktu'] ?></td>
                <td><?= $row['ruangan'] ?></td>
                <td>
                    <?php if ($row['tanggal'] == $tanggal_hari_ini): ?>
                        <button class="btn-absen" onclick="openModal(<?= $row['id'] ?>, <?= $row['praktikum_id'] ?>)">
                            <i class="fa-solid fa-check-circle fa-icon"></i> Absen
                        </button>
                    <?php else: ?>
                        <span class="status-info">Absensi hanya bisa dilakukan pada hari H</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<!-- Modal for Absensi -->
<div class="modal" id="absenModal">
    <div class="modal-content">
        <h2>Konfirmasi Absensi</h2>
        <form method="POST" id="absenForm">
            <input type="hidden" name="praktikum_id" id="praktikum_id">
            <input type="hidden" name="jadwal_id" id="jadwal_id">
            <label for="hadir">Pilih Status Kehadiran:</label>
            <select name="hadir">
                <option value="1">Hadir</option>
                <option value="2">Izin</option>
            </select>
            <button type="submit" name="ambil_absensi" class="btn-absen">
                <i class="fa-solid fa-paper-plane fa-icon"></i> Submit
            </button>
            <button type="button" class="btn-close" onclick="closeModal()">
                <i class="fa-solid fa-times fa-icon"></i> Batal
            </button>
        </form>
    </div>
</div>

<script>
function openModal(jadwalId, praktikumId) {
    document.getElementById("jadwal_id").value = jadwalId;
    document.getElementById("praktikum_id").value = praktikumId;
    document.getElementById("absenModal").style.display = "block";
}

function closeModal() {
    document.getElementById("absenModal").style.display = "none";
}

// Hindari modal muncul otomatis saat restart
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("absenModal").style.display = "none";
});
</script>

</body>
</html>
