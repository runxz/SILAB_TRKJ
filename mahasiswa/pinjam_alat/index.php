<?php
require '../../auth.php';
check_role(['mahasiswa']);
require '../../db.php';

$user_id = $_SESSION['user_id'];
$tanggal_hari_ini = date("Y-m-d");

// Fetch Available Equipment from Inventory
$alat = $conn->query("SELECT * FROM inventaris WHERE jumlah > 0");

// Handle Loan Requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajukan_peminjaman'])) {
    $alat_id = intval($_POST['alat_id']);
    $jumlah = intval($_POST['jumlah']);
    
    $stmt = $conn->prepare("INSERT INTO peminjaman (peminjam_id, alat_id, jumlah, tanggal_pinjam, status) VALUES (?, ?, ?, ?, 'menunggu')");
    $stmt->bind_param("iiis", $user_id, $alat_id, $jumlah, $tanggal_hari_ini);
    $stmt->execute();

    header("Location: ./");
    exit();
}

// Fetch Loan History
$peminjaman = $conn->query("
    SELECT p.*, i.nama_alat, i.foto
    FROM peminjaman p
    JOIN inventaris i ON p.alat_id = i.id
    WHERE p.peminjam_id = $user_id
    ORDER BY p.status ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Alat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/mahasiswastyle.css">
    <style>
        .form-container {
            max-width: 600px;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: auto;
            margin-top: 20px;
            background: white;
        }

        .input-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-submit {
            background-color:#0A1F50;
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
            width: 100%;
        }

        .btn-submit:hover {
            background-color: #005bb5;
        }

        .fa-icon {
            font-size: 20px;
        }

        .img-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .status-menunggu {
            color: #e67e22;
            font-weight: bold;
        }

        .status-disetujui {
            color: #2ecc71;
            font-weight: bold;
        }

        .status-ditolak {
            color: #e74c3c;
            font-weight: bold;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<?php require '../../general/back.php';?>

<div class="container">
    <h1>Peminjaman Alat</h1>

    <div class="form-container">
        <h2>Form Peminjaman</h2>
        <form method="POST">
            <label>Pilih Alat:</label>
            <div class="input-group">
                <select name="alat_id" required>
                    <option value="">-- Pilih Alat --</option>
                    <?php while ($row = $alat->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= $row['nama_alat'] ?> (Tersedia: <?= $row['jumlah'] ?>)</option>
                    <?php endwhile; ?>
                </select>
                <i class="fa-solid fa-tools fa-icon"></i>
            </div>

            <label>Jumlah:</label>
            <div class="input-group">
                <input type="number" name="jumlah" placeholder="Jumlah" required min="1">
                <i class="fa-solid fa-list-ol fa-icon"></i>
            </div>

            <button type="submit" name="ajukan_peminjaman" class="btn-submit">
                <i class="fa-solid fa-paper-plane fa-icon"></i> Ajukan Peminjaman
            </button>
        </form>
    </div>

    <h2>Riwayat Peminjaman</h2>
    <div class="table-container">
        <table class="styled-table">
            <tr>
                <th>Nama Alat</th>
                <th>Foto</th>
                <th>Jumlah</th>
                <th>Tanggal Pinjam</th>
                <th>Status</th>
            </tr>
            <?php while ($row = $peminjaman->fetch_assoc()): ?>
            <tr>
                <td><?= $row['nama_alat'] ?></td>
                <td>
                    <?php if ($row['foto']): ?>
                        <img src="../../uploads/<?= $row['foto'] ?>" class="img-thumbnail">
                    <?php else: ?>
                        <span>Tidak Ada</span>
                    <?php endif; ?>
                </td>
                <td><?= $row['jumlah'] ?></td>
                <td><?= $row['tanggal_pinjam'] ?></td>
                <td>
                    <span class="status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>
