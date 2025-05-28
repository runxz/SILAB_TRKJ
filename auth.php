<?php
session_start();

// Absolute path untuk redirection
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST']);

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// Fungsi untuk memastikan hanya role tertentu yang bisa mengakses halaman
function check_role($allowed_roles) {
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        redirect_to_dashboard();
    }
}

// Fungsi redirect ke dashboard sesuai role
function redirect_to_dashboard() {
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: " . BASE_URL . "/admin/");
            break;
        case 'mahasiswa':
            header("Location: " . BASE_URL . "/mahasiswa/");
            break;
        case 'dosen':
            header("Location: " . BASE_URL . "/dosen/");
            break;
        case 'asistendosen':
            header("Location: " . BASE_URL . "/dosen/");
            break;
    }
    exit();
}
?>
