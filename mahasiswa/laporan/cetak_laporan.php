<?php
require '../../auth.php';
require '../../db.php';
require '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$mahasiswa_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    die("Laporan tidak ditemukan.");
}

$laporan_id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT l.*, p.nama AS nama_praktikum, u.nama AS nama_mahasiswa 
                        FROM laporan l
                        JOIN praktikum p ON l.praktikum_id = p.id
                        JOIN users u ON l.mahasiswa_id = u.id
                        WHERE l.id = ? AND l.mahasiswa_id = ?");
$stmt->bind_param("ii", $laporan_id, $mahasiswa_id);
$stmt->execute();
$result = $stmt->get_result();
$laporan = $result->fetch_assoc();

if (!$laporan) {
    die("Laporan tidak ditemukan.");
}

// Ensure the base URL is correct
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/lab/uploads/laporan/";
$pdf_dir = "../../uploads/laporan/"; // Directory to save PDF
$pdf_filename = "laporan_{$laporan_id}.pdf";
$pdf_path = $pdf_dir . $pdf_filename;
$pdf_url = $base_url . $pdf_filename;

// Configure Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Build PDF Content
$html = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; }
            h2, h3 { color: #0073e6; }
            p { text-align: justify; margin-bottom: 10px; }
            .image { text-align: center; margin: 10px 0; }
            .image img { max-width: 100%; height: auto; }
        </style>
    </head>
    <body>
        <h2>Laporan Praktikum</h2>
        <h3>Nama Praktikum: {$laporan['nama_praktikum']}</h3>
        <h3>Nama Mahasiswa: {$laporan['nama_mahasiswa']}</h3>
        
        <h3>Abstrak</h3>
        <p>{$laporan['abstrak']}</p>
        
        <h3>Kata Kunci</h3>
        <p>{$laporan['kata_kunci']}</p>
        
        <h3>Pendahuluan</h3>
        <p>{$laporan['pendahuluan']}</p>
        
        <h3>Studi Pustaka</h3>
        <p>{$laporan['studi_pustaka']}</p>
        
        <h3>Peralatan Percobaan</h3>
        <p>{$laporan['peralatan']}</p>
        
        <h3>Prosedur Percobaan</h3>
        <p>{$laporan['prosedur']}</p>";

if (!empty($laporan['rangkaian_percobaan'])) {
    $html .= "<h3>Rangkaian Percobaan</h3>
              <div class='image'><img src='{$base_url}{$laporan['rangkaian_percobaan']}' width='300'></div>";
}

if (!empty($laporan['hasil_percobaan'])) {
    $html .= "<h3>Hasil Percobaan</h3>
              <div class='image'><img src='{$base_url}{$laporan['hasil_percobaan']}' width='300'></div>";
}

$html .= "
        <h3>Kesimpulan</h3>
        <p>{$laporan['kesimpulan']}</p>
        
        <h3>Saran</h3>
        <p>{$laporan['saran']}</p>
    </body>
    </html>";

// Generate and save PDF file
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$pdf_output = $dompdf->output();

// Check if PDF is successfully written to the server
if (file_put_contents($pdf_path, $pdf_output) === false) {
    die("Error: Failed to save PDF file.");
}

// Save PDF link to database
$update_stmt = $conn->prepare("UPDATE laporan SET pdf_link = ? WHERE id = ?");
$update_stmt->bind_param("si", $pdf_url, $laporan_id);

if (!$update_stmt->execute()) {
    die("Error updating database: " . $conn->error);
}

// Commit the transaction to ensure data is saved
$conn->commit();

// Redirect to download PDF
header("Location: $pdf_url");
exit();
