<?php
require '../../auth.php';
check_role(['mahasiswa']);
require '../../db.php';

$mahasiswa_id = $_SESSION['user_id'];
$upload_dir = "../../uploads/laporan/";

if (!isset($_GET['id'])) {
    die("Laporan tidak ditemukan.");
}

$laporan_id = intval($_GET['id']);

// Fetch the Report Data
$stmt = $conn->prepare("SELECT * FROM laporan WHERE id = ? AND mahasiswa_id = ? AND status = 'pending'");
$stmt->bind_param("ii", $laporan_id, $mahasiswa_id);
$stmt->execute();
$result = $stmt->get_result();
$laporan = $result->fetch_assoc();

if (!$laporan) {
    die("Laporan tidak ditemukan atau tidak bisa diedit.");
}

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_laporan'])) {
    $abstrak = htmlspecialchars($_POST['abstrak']);
    $kata_kunci = htmlspecialchars($_POST['kata_kunci']);
    $pendahuluan = htmlspecialchars($_POST['pendahuluan']);
    $studi_pustaka = htmlspecialchars($_POST['studi_pustaka']);
    $peralatan = htmlspecialchars($_POST['peralatan']);
    $prosedur = htmlspecialchars($_POST['prosedur']);
    $kesimpulan = htmlspecialchars($_POST['kesimpulan']);
    $saran = htmlspecialchars($_POST['saran']);

    // Update Query
    $stmt = $conn->prepare("UPDATE laporan SET abstrak=?, kata_kunci=?, pendahuluan=?, studi_pustaka=?, peralatan=?, prosedur=?, kesimpulan=?, saran=? WHERE id=? AND mahasiswa_id=?");
    $stmt->bind_param("ssssssssii", $abstrak, $kata_kunci, $pendahuluan, $studi_pustaka, $peralatan, $prosedur, $kesimpulan, $saran, $laporan_id, $mahasiswa_id);
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
    <title>Edit Laporan Praktikum</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/mahasiswastyle.css">
    <style>
        .form-container {
            max-width: 800px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: auto;
        }

        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }

        textarea {
            width: 100%;
            min-height: 100px;
            resize: vertical;
        }

        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .btn-update {
            background-color: #0073e6;
            color: white;
            padding: 12px 18px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-update:hover {
            background-color: #005bb5;
        }

        .btn-back {
            background-color: #e74c3c;
            color: white;
            padding: 12px 18px;
            font-size: 16px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .btn-back:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<?php require '../../general/back.php';?>

<div class="container">
    <div class="form-container">
        <h2>Edit Laporan Praktikum</h2>
        <form method="POST">
            <label>Abstrak:</label>
            <textarea name="abstrak" required><?= $laporan['abstrak'] ?></textarea>

            <label>Kata Kunci:</label>
            <input type="text" name="kata_kunci" value="<?= $laporan['kata_kunci'] ?>" required>

            <label>Pendahuluan:</label>
            <textarea name="pendahuluan" required><?= $laporan['pendahuluan'] ?></textarea>

            <label>Studi Pustaka:</label>
            <textarea name="studi_pustaka" required><?= $laporan['studi_pustaka'] ?></textarea>

            <label>Peralatan Percobaan:</label>
            <textarea name="peralatan" required><?= $laporan['peralatan'] ?></textarea>

            <label>Prosedur Percobaan:</label>
            <textarea name="prosedur" required><?= $laporan['prosedur'] ?></textarea>

            <label>Kesimpulan:</label>
            <textarea name="kesimpulan" required><?= $laporan['kesimpulan'] ?></textarea>

            <label>Saran:</label>
            <textarea name="saran" required><?= $laporan['saran'] ?></textarea>

            <div class="btn-container">
                <button type="submit" name="update_laporan" class="btn-update">
                    <i class="fa-solid fa-save"></i> Update
                </button>
                <a href="./" class="btn-back">
                    <i class="fa-solid fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
