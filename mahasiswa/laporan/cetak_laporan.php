<?php
require '../../auth.php';
require '../../db.php';
require '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;


 
$mahasiswa_id = $_SESSION['user_id'];
if (!isset($_GET['id'])) die("Laporan tidak ditemukan.");

$laporan_id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT l.*, p.nama AS nama_praktikum, p.laboratorium, u.nama AS nama_mahasiswa, u.npm 
    FROM laporan l
    JOIN praktikum p ON l.praktikum_id = p.id
    JOIN users u ON l.mahasiswa_id = u.id
    WHERE l.id = ? AND l.mahasiswa_id = ?
");

$stmt->bind_param("ii", $laporan_id, $mahasiswa_id);
$stmt->execute();
$result = $stmt->get_result();
$laporan = $result->fetch_assoc();

if (!$laporan) die("Laporan tidak ditemukan.");

$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/lab/uploads/laporan/";
$pdf_dir = "../../uploads/laporan/";
$pdf_filename = "laporan_{$laporan_id}.pdf";
$pdf_path = $pdf_dir . $pdf_filename;
$pdf_url = $base_url . $pdf_filename;

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Halaman Judul
$html = "
<html>
<head>
    <style>
        @page {
            margin-top: 4cm;
            margin-bottom: 3cm;
            margin-left: 4cm;
            margin-right: 3cm;
    
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.5;
             position: relative;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .italic { font-style: italic; }
        .mt { margin-top: 40px; }
        .mb { margin-bottom: 40px; }
        img.logo {
            width: 120px;
            display: block;
            margin: 30px auto;
        }
        .section { page-break-before: always; margin-top: 20px; }
        p { text-align: justify; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class='center'>
    <div class='bold'>LAPORAN PRAKTIKUM</div>
    <div class='bold'>{$laporan['nama_praktikum']}</div>
    <br><br>
    <div class='italic'>Diajukan sebagai salah satu syarat untuk menyelesaikan Praktikum</div>
    <div class='italic'>pada Program Studi Teknologi Rekayasa Komputer Jaringan</div>
    <div class='italic'>Fakultas Teknologi Industri</div>
    <div class='italic'>Universitas Bung Hatta</div>
    <br>
<img src='http://localhost/lab/assets/image.png' class='logo'>


    <br><br>
    <div>Oleh:</div>
    <div class='bold'>{$laporan['nama_mahasiswa']}</div>
    <div>{$laporan['npm']}</div>

    <div class='mt'>PROGRAM STUDI TEKNOLOGI REKAYASA KOMPUTER JARINGAN</div>
    <div>FAKULTAS TEKNOLOGI INDUSTRI</div>
    <div>UNIVERSITAS BUNG HATTA</div>
    <div class='mt'>PADANG</div>
    <div>2024</div>
</div>

<div class='section'>
    <h3>Tujuan Praktikum</h3>
    <p>{$laporan['tujuan_praktikum']}</p>

    <h3>Langkah Kerja</h3>
    <p>{$laporan['langkah_kerja']}</p>";

if (!empty($laporan['rangkaian_percobaan'])) {
    $html .= "<h3>Flowchart / Screenshoot</h3>
              <img src='{$base_url}{$laporan['rangkaian_percobaan']}' style='width:100%;'>";
}

$html .= "
    <h3>Hasil Praktikum</h3>
    <p>{$laporan['hasil_praktikum']}</p>

    <h3>Pembahasan (Refleksi & Analisis)</h3>
    <p>{$laporan['pembahasan']}</p>

    <h3>Kesimpulan</h3>
    <p>{$laporan['kesimpulan']}</p>

    <h3>Saran</h3>
    <p>{$laporan['saran']}</p>

    <h3>Daftar Pustaka</h3>
    <p>{$laporan['daftar_pustaka']}</p>
</div>
<footer style='position: fixed; bottom: -20px; left: 0; right: 0; text-align: center; font-size: 11pt; font-family: Times New Roman;'>
    {$laporan['laboratorium']}
</footer>

</body>
</html>";

// Generate PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$output = $dompdf->output();

if (file_put_contents($pdf_path, $output) === false) die("Gagal menyimpan PDF.");

$update_stmt = $conn->prepare("UPDATE laporan SET pdf_link = ? WHERE id = ?");
$update_stmt->bind_param("si", $pdf_url, $laporan_id);
$update_stmt->execute();

$conn->commit();
header("Location: $pdf_url");
exit();
?>
