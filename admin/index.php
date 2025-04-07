<?php
require '../auth.php';
check_role(['admin']);
require '../db.php';

$user_id = $_SESSION['user_id'];

// Ambil Data Pengguna
$stmt = $conn->prepare("SELECT nama, foto_profil FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    
   
</head>
<body>

<?php require '../general/navbar.php';?>
<?php require '../general/search.php';?>

<div class="container">
    <h1>Dashboard Admin</h1>

    <div class="icon-container">
        <a href="./manage_users.php" class="icon-box">
            <i class="fa-solid fa-users"></i>
            <span>Manajemen User</span>
        </a>
        <a href="./manage_praktikum.php" class="icon-box">
            <i class="fa-solid fa-book"></i>
            <span>Manajemen Praktikum</span>
        </a>
        <a href="./manage_inventaris.php" class="icon-box">
            <i class="fa-solid fa-box"></i>
            <span>Manajemen Inventaris</span>
        </a>
        <a href="./manage_laporan.php" class="icon-box">
            <i class="fa-solid fa-file-alt"></i>
            <span>Manajemen Laporan</span>
        </a>
        <a href="./manage_peminjaman.php" class="icon-box">
            <i class="fa-solid fa-toolbox"></i>
            <span>Manajemen Peminjaman</span>
        </a>
    </div>
</div>

</body>
</html>
