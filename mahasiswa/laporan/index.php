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
    SELECT l.*, p.nama AS nama_praktikum, l.pdf_link,
        (SELECT komentar FROM komentar_laporan k WHERE k.laporan_id = l.id ORDER BY waktu DESC LIMIT 1) AS komentar_terakhir
    FROM laporan l
    JOIN praktikum p ON l.praktikum_id = p.id
    WHERE l.mahasiswa_id = $mahasiswa_id
    ORDER BY l.status ASC
");

// Fetch Jadwal
$jadwal_stmt = $conn->prepare("SELECT j.id, j.tanggal, j.waktu 
  FROM jadwal j 
  WHERE j.praktikum_id IN (
    SELECT praktikum_id FROM mahasiswa_praktikum WHERE mahasiswa_id = ?
  ) ORDER BY j.tanggal ASC");
$jadwal_stmt->bind_param("i", $mahasiswa_id);
$jadwal_stmt->execute();
$jadwal_result = $jadwal_stmt->get_result();
// Handle Laporan Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $praktikum_id = intval($_POST['praktikum_id']);
    $jenis = $_POST['jenis'];
    $judul = htmlspecialchars($_POST['laporan']);

    if ($jenis === 'mingguan' && isset($_POST['kirim_laporan'])) {

        $judul = htmlspecialchars($_POST['laporan']);
        $tujuan = htmlspecialchars($_POST['tujuan_praktikum']);
        $langkah = htmlspecialchars($_POST['langkah_kerja']);
        $hasil = htmlspecialchars($_POST['hasil_praktikum']);
        $pembahasan = htmlspecialchars($_POST['pembahasan']);
        $kesimpulan = htmlspecialchars($_POST['kesimpulan']);
        $saran = htmlspecialchars($_POST['saran']);
        $pustaka = htmlspecialchars($_POST['daftar_pustaka']);
        $jadwal_id = intval($_POST['jadwal_id']);

        // Cek kehadiran
$cek = $conn->prepare("SELECT id FROM absensi WHERE mahasiswa_id = ? AND jadwal_id = ? AND hadir IN ('1','2')");
$cek->bind_param("ii", $mahasiswa_id, $jadwal_id);
$cek->execute();
$cek->store_result();
if ($cek->num_rows == 0) {
  die("Anda belum hadir di jadwal ini.");
}

        $stmt = $conn->prepare("INSERT INTO laporan
    (mahasiswa_id, praktikum_id,jadwal_id, laporan, status, tujuan_praktikum, langkah_kerja, hasil_praktikum, pembahasan, kesimpulan, saran, daftar_pustaka, jenis)
    VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, 'mingguan')");
$stmt->bind_param("iiissssssss", $mahasiswa_id, $praktikum_id, $jadwal_id, $judul, $tujuan, $langkah, $hasil, $pembahasan, $kesimpulan, $saran, $pustaka);
$stmt->execute();
  

    } elseif ($jenis === 'akhir' && isset($_POST['kirim_laporan_akhir'])) {
        $pdf_link = time() . "_" . basename($_FILES['laporan_pdf']['name']);
        move_uploaded_file($_FILES['laporan_pdf']['tmp_name'], $upload_dir . $pdf_link);

        $stmt = $conn->prepare("INSERT INTO laporan 
            (mahasiswa_id, praktikum_id, laporan, status, pdf_link, jenis) 
            VALUES (?, ?, ?, 'pending', ?, 'akhir')");
        $stmt->bind_param("iiss", $mahasiswa_id, $praktikum_id, $judul, $pdf_link);
        $stmt->execute();
    }

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
        <label>Pilih Jenis Laporan:</label>
<select id="jenis_select" name="jenis" onchange="toggleLaporanForm()" required>
  <option value="">-- Pilih Jenis Laporan --</option>
  <option value="mingguan">Mingguan</option>
  <option value="akhir">Laporan Akhir</option>
</select>

<div id="form_mingguan" style="display: none;">
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="jenis" value="mingguan">

    <label>Pilih Praktikum:</label>
    <select name="praktikum_id" required>
      <option value="">-- Pilih Praktikum --</option>
      <?php
        $praktikum->data_seek(0);
        while ($row = $praktikum->fetch_assoc()):
      ?>
        <option value="<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row['nama']) ?></option>
      <?php endwhile; ?>
    </select>
    <label>Pilih Jadwal Pertemuan:</label>
<select name="jadwal_id" required>
  <option value="">-- Pilih Jadwal --</option>
  <?php while ($j = $jadwal_result->fetch_assoc()): ?>
    <option value="<?= $j['id'] ?>">
      <?= $j['tanggal'] ?> (<?= $j['waktu'] ?>)
    </option>
  <?php endwhile; ?>
</select>

    <label>Judul Laporan:</label>
    <input type="text" name="laporan" required>

    <label>Tujuan Praktikum:</label>
    <textarea name="tujuan_praktikum" required></textarea>

    <label>Langkah Kerja (Flowchart / Screenshoot):</label>
    <textarea name="langkah_kerja" required></textarea>

    <label>Hasil Praktikum:</label>
    <textarea name="hasil_praktikum" required></textarea>

    <label>Pembahasan (5 Poin Refleksi & Analisis):</label>
    <textarea name="pembahasan" required></textarea>

    <label>Kesimpulan:</label>
    <textarea name="kesimpulan" required></textarea>

    <label>Saran:</label>
    <textarea name="saran" required></textarea>

    <label>Daftar Pustaka (≥5 sumber):</label>
    <textarea name="daftar_pustaka" required></textarea>

    <button type="submit" name="kirim_laporan" class="btn-add">Kirim Laporan</button>
  </form>
</div>


<div id="form_akhir" style="display: none;">
<form method="POST" enctype="multipart/form-data">
  <input type="hidden" name="jenis" value="akhir">
  <label>Pilih Praktikum:</label>
<select name="praktikum_id" required>
  <option value="">-- Pilih Praktikum --</option>
  <?php
    $praktikum->data_seek(0);
    while ($row = $praktikum->fetch_assoc()):
  ?>
    <option value="<?= $row['id'] ?>"><?= $row['nama'] ?></option>
  <?php endwhile; ?>
</select>

  <label>Judul Laporan:</label>
  <input type="text" name="laporan" required>

  <label>Unggah Laporan PDF:</label>
  <input type="file" name="laporan_pdf" accept="application/pdf" required>

  <button type="submit" name="kirim_laporan_akhir">Kirim</button>
</form>
</div>


    </div>

    <!-- Daftar Laporan -->
    <div id="report-section" class="toggle-section">
        <h2>Daftar Laporan</h2>
        <div class="table-container">
        <table class="styled-table">
    <tr>
        <th>Nama Praktikum</th>
        <th>Jenis</th>
       
       
        <th>Status</th>
        <th>Aksi</th>
        <th>Catatan</th>
    </tr>
    <?php while ($row = $laporan->fetch_assoc()): ?>
    <tr>
        <td><?= $row['nama_praktikum'] ?></td>
        <td><?= ucfirst($row['jenis']) ?></td>

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
        <td>
  <?php if ($row['status'] == 'ditolak' && !empty($row['komentar_terakhir'])): ?>
    <span style="color:red;"><?= htmlspecialchars($row['komentar_terakhir']) ?></span>
  <?php else: ?>
    <em>-</em>
  <?php endif; ?>
</td>

    </tr>
    <?php endwhile; ?>
</table>

        </div>
    </div>
</div>
<script>
function toggleLaporanForm() {
  const jenis = document.getElementById("jenis_select").value;
  document.getElementById("form_mingguan").style.display = jenis === "mingguan" ? "block" : "none";
  document.getElementById("form_akhir").style.display = jenis === "akhir" ? "block" : "none";
}
</script>
<script>
function toggleSection(showId, hideId) {
    document.getElementById(showId).style.display = "block";
    document.getElementById(hideId).style.display = "none";
}
</script>

</body>
</html>
