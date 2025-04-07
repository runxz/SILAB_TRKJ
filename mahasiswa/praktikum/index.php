<?php
require '../../auth.php';
check_role(['mahasiswa']);
require '../../db.php';

$mahasiswa_id = $_SESSION['user_id'];

// Handle Mahasiswa Mengambil Praktikum
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ambil_praktikum'])) {
    $praktikum_id = intval($_POST['praktikum_id']);

    // Cek apakah sudah terdaftar
    $cek = $conn->prepare("SELECT id FROM mahasiswa_praktikum WHERE mahasiswa_id = ? AND praktikum_id = ?");
    $cek->bind_param("ii", $mahasiswa_id, $praktikum_id);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows == 0) {
        // Daftarkan mahasiswa ke praktikum
        $stmt = $conn->prepare("INSERT INTO mahasiswa_praktikum (mahasiswa_id, praktikum_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $mahasiswa_id, $praktikum_id);
        $stmt->execute();
    }

    header("Location: ./");
    exit();
}

// Ambil List Praktikum
$praktikum = $conn->query("
    SELECT p.*, u.nama AS nama_dosen
    FROM praktikum p
    JOIN users u ON p.dosen_id = u.id
");

// Ambil List Asisten Dosen
$asisten_praktikum = $conn->query("
    SELECT ap.praktikum_id, u.nama AS nama_asisten
    FROM asisten_praktikum ap
    JOIN users u ON ap.asisten_id = u.id
");

$asisten_list = [];
while ($row = $asisten_praktikum->fetch_assoc()) {
    $asisten_list[$row['praktikum_id']][] = $row['nama_asisten'];
}

// Ambil Praktikum yang Sudah Diambil Mahasiswa
$praktikum_terdaftar = $conn->query("
    SELECT praktikum_id FROM mahasiswa_praktikum WHERE mahasiswa_id = $mahasiswa_id
");

$terdaftar = [];
while ($row = $praktikum_terdaftar->fetch_assoc()) {
    $terdaftar[] = $row['praktikum_id'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Praktikum</title>
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

        .btn-add {
            background-color: #0073e6;
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

        .btn-add:hover {
            background-color: #005bb5;
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

        .status-registered {
            color: #2ecc71;
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
    <h1>Pilih Praktikum</h1>

    <div class="table-container">
        <table class="styled-table">
            <tr>
                <th>Nama Praktikum</th>
                <th>Dosen</th>
                <th>Asisten Dosen</th>
                <th>Deskripsi</th>
                <th>Aksi</th>
            </tr>
            <?php while ($row = $praktikum->fetch_assoc()): ?>
            <tr>
                <td><?= $row['nama'] ?></td>
                <td><?= $row['nama_dosen'] ?></td>
                <td><?= isset($asisten_list[$row['id']]) ? implode(", ", $asisten_list[$row['id']]) : "Belum Ada" ?></td>
                <td><?= nl2br($row['deskripsi']) ?></td>
                <td>
                    <?php if (!in_array($row['id'], $terdaftar)): ?>
                        <button class="btn-add" onclick="openModal(<?= $row['id'] ?>)">
                            <i class="fa-solid fa-check fa-icon"></i> Ambil
                        </button>
                    <?php else: ?>
                        <span class="status-registered">Sudah Terdaftar</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<!-- Modal for Confirmation -->
<div class="modal" id="confirmationModal">
    <div class="modal-content">
        <h2>Konfirmasi</h2>
        <p>Apakah Anda yakin ingin mengambil praktikum ini?</p>
        <form method="POST" id="confirmForm">
            <input type="hidden" name="praktikum_id" id="praktikum_id">
            <button type="submit" name="ambil_praktikum" class="btn-add">
                <i class="fa-solid fa-paper-plane fa-icon"></i> Ya, Ambil
            </button>
            <button type="button" class="btn-close" onclick="closeModal()">
                <i class="fa-solid fa-times fa-icon"></i> Batal
            </button>
        </form>
    </div>
</div>

<script>
function openModal(praktikumId) {
    document.getElementById("praktikum_id").value = praktikumId;
    document.getElementById("confirmationModal").style.display = "block";
}

function closeModal() {
    document.getElementById("confirmationModal").style.display = "none";
}

// Hindari modal muncul otomatis saat restart
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("confirmationModal").style.display = "none";
});
</script>

</body>
</html>
