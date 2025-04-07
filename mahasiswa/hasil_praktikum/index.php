<?php
require '../../auth.php';
check_role(['mahasiswa']);
require '../../db.php';

$mahasiswa_id = $_SESSION['user_id'];
$upload_dir = "uploads/hasil_praktikum/";

// Fetch Praktikum Data
$praktikum = $conn->query("
    SELECT p.id, p.nama 
    FROM praktikum p
    JOIN mahasiswa_praktikum mp ON p.id = mp.praktikum_id
    WHERE mp.mahasiswa_id = $mahasiswa_id
");

// Handle Pengiriman Hasil Praktikum
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['kirim_hasil'])) {
    $praktikum_id = intval($_POST['praktikum_id']);
    $hasil = htmlspecialchars($_POST['hasil']);

    // Proses Upload Gambar
    $gambar = NULL;
    if (!empty($_FILES['gambar']['name'])) {
        $gambar = time() . "_" . basename($_FILES['gambar']['name']);
        move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_dir . $gambar);
    }

    // Proses Upload Laporan PDF
    $laporan_pdf = NULL;
    if (!empty($_FILES['laporan_pdf']['name'])) {
        $laporan_pdf = time() . "_" . basename($_FILES['laporan_pdf']['name']);
        move_uploaded_file($_FILES['laporan_pdf']['tmp_name'], $upload_dir . $laporan_pdf);
    }

    // Simpan ke Database
    $stmt = $conn->prepare("
        INSERT INTO hasil_praktikum (mahasiswa_id, praktikum_id, hasil, gambar, laporan_pdf, status) 
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->bind_param("iisss", $mahasiswa_id, $praktikum_id, $hasil, $gambar, $laporan_pdf);
    $stmt->execute();

    header("Location: ./");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Praktikum</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/mahasiswastyle.css">
    <style>
        .form-container {
            max-width: 700px;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: auto;
            margin-top: 20px;
            background: white;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .input-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-submit {
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
            width: 100%;
        }

        .btn-submit:hover {
            background-color: #005bb5;
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
    <h1>Input Hasil Praktikum</h1>

    <div class="form-container">
        <form method="POST" enctype="multipart/form-data">
            <label>Pilih Praktikum:</label>
            <select name="praktikum_id" required>
                <option value="">-- Pilih Praktikum --</option>
                <?php while ($row = $praktikum->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= $row['nama'] ?></option>
                <?php endwhile; ?>
            </select>

            <label>Deskripsi Hasil:</label>
            <textarea name="hasil" placeholder="Jelaskan hasil percobaan..." required></textarea>

            <label>Unggah Gambar Hasil:</label>
            <div class="input-group">
                <input type="file" name="gambar" accept="image/*">
                <i class="fa-solid fa-image fa-icon"></i>
            </div>

            <label>Unggah Laporan Hasil (PDF):</label>
            <div class="input-group">
                <input type="file" name="laporan_pdf" accept="application/pdf">
                <i class="fa-solid fa-file-pdf fa-icon"></i>
            </div>

            <button type="submit" name="kirim_hasil" class="btn-submit">
                <i class="fa-solid fa-paper-plane fa-icon"></i> Kirim Hasil
            </button>
        </form>
    </div>
</div>

</body>
</html>
