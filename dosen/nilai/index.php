<?php
require '../../auth.php';
require '../../db.php';

check_role(['dosen', 'asistendosen']);
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch praktikum berdasarkan role
if ($role === 'dosen') {
    $praktikum = $conn->prepare("SELECT id, nama FROM praktikum WHERE dosen_id = ?");
} else {
    $praktikum = $conn->prepare("
        SELECT p.id, p.nama FROM praktikum p
        JOIN asisten_praktikum ap ON p.id = ap.praktikum_id
        WHERE ap.asisten_id = ?
    ");
}
$praktikum->bind_param("i", $user_id);
$praktikum->execute();
$praktikum_result = $praktikum->get_result();

// Handle Submit Nilai
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['simpan_nilai'])) {
    $mahasiswa_id = intval($_POST['mahasiswa_id']);
    $praktikum_id = intval($_POST['praktikum_id']);

    $pretest = floatval($_POST['pretest']);
    $keaktifan = floatval($_POST['keaktifan']);
    $lkp = floatval($_POST['laporan_per_acara']);
    $akhir = floatval($_POST['laporan_akhir']);
    $responsi = floatval($_POST['responsi']);

    $check = $conn->prepare("SELECT id FROM nilai_praktikum WHERE mahasiswa_id = ? AND praktikum_id = ?");
    $check->bind_param("ii", $mahasiswa_id, $praktikum_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE nilai_praktikum SET pretest=?, keaktifan=?, laporan_per_acara=?, laporan_akhir=?, responsi=? WHERE mahasiswa_id=? AND praktikum_id=?");
        $stmt->bind_param("dddddii", $pretest, $keaktifan, $lkp, $akhir, $responsi, $mahasiswa_id, $praktikum_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO nilai_praktikum (mahasiswa_id, praktikum_id, pretest, keaktifan, laporan_per_acara, laporan_akhir, responsi) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiddddd", $mahasiswa_id, $praktikum_id, $pretest, $keaktifan, $lkp, $akhir, $responsi);
    }

    $stmt->execute();
    header("Location: index.php?praktikum_id=$praktikum_id");
    exit();
}

// Fetch Mahasiswa untuk Praktikum tertentu
$mahasiswa_result = [];
if (isset($_GET['praktikum_id'])) {
    $praktikum_id = intval($_GET['praktikum_id']);
    $stmt = $conn->prepare("
        SELECT u.id, u.nama FROM users u
        JOIN mahasiswa_praktikum mp ON u.id = mp.mahasiswa_id
        WHERE mp.praktikum_id = ?
    ");
    $stmt->bind_param("i", $praktikum_id);
    $stmt->execute();
    $mahasiswa_result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Nilai Praktikum</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/dosenstyle.css">
     <style>
.modal-content form {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding-top: 10px;
}

.modal-content label {
    font-weight: bold;
    color: #003366;
    margin-bottom: 4px;
    text-align: left;
}

.modal-content input[type="number"] {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
}

.modal-content button[type="submit"] {
    background-color: #003366;
    color: white;
    padding: 10px 18px;
    font-weight: bold;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 12px;
    align-self: flex-start;
}

.modal-content button[type="submit"]:hover {
    background-color: #002244;
}

       
     </style>
</head>
<body>

<nav class="navbar">
    <div class="logo">Web Labor TRKJ</div>
    <a href="../" class="logout-btn">Kembali</a>
</nav>

<div class="container">
    
    <h1>Input Nilai Praktikum</h1>

    <form method="GET" class="filter-form">
        <label for="praktikum_id">Pilih Praktikum:</label>
        <select name="praktikum_id" onchange="this.form.submit()" required>
            <option value="">-- Pilih Praktikum --</option>
            <?php while ($p = $praktikum_result->fetch_assoc()): ?>
                <option value="<?= $p['id'] ?>" <?= isset($_GET['praktikum_id']) && $_GET['praktikum_id'] == $p['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nama']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

<?php if (!empty($mahasiswa_result) && $mahasiswa_result->num_rows > 0): ?>
    <h2>Mahasiswa</h2>
     <a href="./rekap_nilai.php" class=""><B class="text-left">Rekap Nilai</B></a>
    <table class="styled-table">
        <tr>
            <th>Nama Mahasiswa</th>
            <th>Aksi</th>
        </tr>
        <?php while ($mhs = $mahasiswa_result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($mhs['nama']) ?></td>
            <td>
                <button class="btn-assign" onclick="openModal(<?= $mhs['id'] ?>)"><B>INPUT NILAI</B></button>
            </td>
        </tr>

        <!-- Modal -->
        <div class="modal" id="modal-<?= $mhs['id'] ?>" style="display:none;">
            <div class="modal-content">
                <span class="close" onclick="closeModal(<?= $mhs['id'] ?>)">&times;</span>
                <h3>Input Nilai - <?= htmlspecialchars($mhs['nama']) ?></h3>
                <form method="POST" class="styled-form">
                    <input type="hidden" name="mahasiswa_id" value="<?= $mhs['id'] ?>">
                    <input type="hidden" name="praktikum_id" value="<?= $praktikum_id ?>">
<div class="form-group">
    <label for="pretest">Pretest (5%)</label>
    <input type="number" name="pretest" id="pretest" step="0.01" required>
</div>

<div class="form-group">
    <label for="keaktifan">Keaktifan & LKKP (10%)</label>
    <input type="number" name="keaktifan" id="keaktifan" step="0.01" required>
</div>

<div class="form-group">
    <label for="laporan_per_acara">Laporan Per Acara (35%)</label>
    <input type="number" name="laporan_per_acara" id="laporan_per_acara" step="0.01" required>
</div>

<div class="form-group">
    <label for="laporan_akhir">Laporan Akhir Praktikum (10%)</label>
    <input type="number" name="laporan_akhir" id="laporan_akhir" step="0.01" required>
</div>

<div class="form-group">
    <label for="responsi">Responsi, UTS, UAS (40%)</label>
    <input type="number" name="responsi" id="responsi" step="0.01" required>
</div>

      <button type="submit" name="simpan_nilai" class="btn-add">Simpan</button>

                </form>
            </div>
        </div>
        <?php endwhile; ?>
    </table>
<?php endif; ?>
<script>
function openModal(id) {
    document.getElementById("modal-" + id).style.display = "block";
}
function closeModal(id) {
    document.getElementById("modal-" + id).style.display = "none";
}
</script>

</body>
</html>
