<?php
require '../../auth.php';
check_role(['mahasiswa']);
require '../../db.php';

$mahasiswa_id = $_SESSION['user_id'];
$upload_dir = "../../uploads/laporan/";

// Fetch Praktikum Data
$praktikum = $conn->query("
    SELECT p.id, p.nama 
    FROM praktikum p
    JOIN mahasiswa_praktikum mp ON p.id = mp.praktikum_id
    WHERE mp.mahasiswa_id = $mahasiswa_id
");

// Fetch Reports
$laporan = $conn->query("
    SELECT l.*, p.nama AS nama_praktikum, l.pdf_link 
    FROM laporan l
    JOIN praktikum p ON l.praktikum_id = p.id
    WHERE l.mahasiswa_id = $mahasiswa_id
    ORDER BY l.status ASC
");

// Handle Laporan Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['kirim_laporan'])) {
    $praktikum_id = intval($_POST['praktikum_id']);
    $abstrak = htmlspecialchars($_POST['abstrak']);
    $kata_kunci = htmlspecialchars($_POST['kata_kunci']);
    $pendahuluan = htmlspecialchars($_POST['pendahuluan']);
    $studi_pustaka = htmlspecialchars($_POST['studi_pustaka']);
    $peralatan = htmlspecialchars($_POST['peralatan']);
    $prosedur = htmlspecialchars($_POST['prosedur']);
    $kesimpulan = htmlspecialchars($_POST['kesimpulan']);
    $saran = htmlspecialchars($_POST['saran']);

    // Proses Upload Gambar
    $rangkaian_percobaan = NULL;
    $hasil_percobaan = NULL;

    if (!empty($_FILES['rangkaian_percobaan']['name'])) {
        $rangkaian_percobaan = time() . "_" . basename($_FILES['rangkaian_percobaan']['name']);
        move_uploaded_file($_FILES['rangkaian_percobaan']['tmp_name'], $upload_dir . $rangkaian_percobaan);
    }

    if (!empty($_FILES['hasil_percobaan']['name'])) {
        $hasil_percobaan = time() . "_" . basename($_FILES['hasil_percobaan']['name']);
        move_uploaded_file($_FILES['hasil_percobaan']['tmp_name'], $upload_dir . $hasil_percobaan);
    }

    $stmt = $conn->prepare("
        INSERT INTO laporan (mahasiswa_id, praktikum_id, abstrak, kata_kunci, pendahuluan, studi_pustaka, peralatan, prosedur, kesimpulan, saran, rangkaian_percobaan, hasil_percobaan, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->bind_param("iissssssssss", $mahasiswa_id, $praktikum_id, $abstrak, $kata_kunci, $pendahuluan, $studi_pustaka, $peralatan, $prosedur, $kesimpulan, $saran, $rangkaian_percobaan, $hasil_percobaan);
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
    <title>Laporan Praktikum</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/mahasiswastyle.css">
    <style>
        .toggle-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn-toggle {
            flex: 1;
            background-color: #0A1F50;
            color: white;
            padding: 12px 20px;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            border: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-toggle:hover {
            background-color:rgb(15, 51, 134);
        }

        .toggle-section {
            display: none;
            margin-top: 20px;
        }

        .form-container {
            max-width: 700px;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: auto;
            margin-top: 10px;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .fa-icon {
            font-size: 20px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<?php require '../../general/back.php';?>

<div class="container">
    <h1>Laporan Praktikum</h1>

    <!-- Toggle Buttons -->
    <div class="toggle-container">
        <button class="btn-toggle" onclick="toggleSection('form-section', 'report-section')">
            <i class="fa-solid fa-plus fa-icon"></i> Buat Laporan
        </button>
        <button class="btn-toggle" onclick="toggleSection('report-section', 'form-section')">
            <i class="fa-solid fa-file-alt fa-icon"></i> Lihat Laporan
        </button>
    </div>

    <!-- Form for Creating Reports -->
    <div id="form-section" class="toggle-section form-container">
        <h2>Buat Laporan Praktikum</h2>
        <form method="POST" enctype="multipart/form-data">
    <label>Pilih Praktikum:</label>
    <select name="praktikum_id" required>
        <option value="">-- Pilih Praktikum --</option>
        <?php while ($row = $praktikum->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= $row['nama'] ?></option>
        <?php endwhile; ?>
    </select>

    <label>Abstrak:</label>
    <textarea name="abstrak" required></textarea>

    <label>Kata Kunci:</label>
    <input type="text" name="kata_kunci" required>

    <label>Pendahuluan:</label>
    <textarea name="pendahuluan" required></textarea>

    <label>Studi Pustaka:</label>
    <textarea name="studi_pustaka" required></textarea>

    <label>Peralatan Percobaan:</label>
    <textarea name="peralatan" required></textarea>

    <label>Prosedur Percobaan:</label>
    <textarea name="prosedur" required></textarea>

    <label>Kesimpulan:</label>
    <textarea name="kesimpulan" required></textarea>

    <label>Saran:</label>
    <textarea name="saran" required></textarea>

    <label>Rangkaian Percobaan (Gambar):</label>
    <input type="file" name="rangkaian_percobaan" accept="image/*">

    <label>Hasil Percobaan (Gambar):</label>
    <input type="file" name="hasil_percobaan" accept="image/*">

    <button type="submit" name="kirim_laporan" class="btn-add">
        <i class="fa-solid fa-paper-plane fa-icon"></i> Kirim
    </button>
</form>

    </div>

    <!-- Daftar Laporan -->
    <div id="report-section" class="toggle-section">
        <h2>Daftar Laporan</h2>
        <div class="table-container">
        <table class="styled-table">
    <tr>
        <th>Nama Praktikum</th>
        <th>Abstrak</th>
        <th>Kesimpulan</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>
    <?php while ($row = $laporan->fetch_assoc()): ?>
    <tr>
        <td><?= $row['nama_praktikum'] ?></td>
        <td><?= substr($row['abstrak'], 0, 50) ?>...</td>
        <td><?= substr($row['kesimpulan'], 0, 50) ?>...</td>
        <td><span class="status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
        <td>
            <a href="<?= $row['pdf_link'] ?>" target="_blank" class="btn-view">
                <i class="fa-solid fa-file-pdf fa-icon"></i> Lihat
            </a> |
            <a href="cetak_laporan.php?id=<?= $row['id'] ?>" target="_blank" class="btn-print">
                <i class="fa-solid fa-print fa-icon"></i> Cetak
            </a> |
            <?php if ($row['status'] == 'pending'): ?>
                <a href="edit_laporan.php?id=<?= $row['id'] ?>" class="btn-edit">
                    <i class="fa-solid fa-edit fa-icon"></i> Edit
                </a> |
                <a href="delete_laporan.php?id=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Hapus laporan ini?')">
                    <i class="fa-solid fa-trash fa-icon"></i> Hapus
                </a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

        </div>
    </div>
</div>

<script>
function toggleSection(showId, hideId) {
    document.getElementById(showId).style.display = "block";
    document.getElementById(hideId).style.display = "none";
}
</script>

</body>
</html>
