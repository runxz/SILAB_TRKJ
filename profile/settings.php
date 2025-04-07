<?php
require '../auth.php';
require '../db.php';

$user_id = $_SESSION['user_id'];

// Ambil Data User Saat Ini
$stmt = $conn->prepare("SELECT nama, email, foto_profil FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle Perubahan Profil
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);
    $upload_dir = "../uploads/profiles/";
    $foto_profil = $user['foto_profil']; // Gunakan foto lama jika tidak ada perubahan

    // Jika Ada Upload Foto Baru
    if (!empty($_FILES['foto_profil']['name'])) {
        $foto_profil = time() . "_" . basename($_FILES['foto_profil']['name']);
        move_uploaded_file($_FILES['foto_profil']['tmp_name'], $upload_dir . $foto_profil);

        // Hapus Foto Lama Jika Ada
        if ($user['foto_profil'] && file_exists($upload_dir . $user['foto_profil'])) {
            unlink($upload_dir . $user['foto_profil']);
        }
    }

    // Update Data User
    $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, foto_profil = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nama, $email, $foto_profil, $user_id);
    $stmt->execute();

    header("Location: settings.php");
    exit();
}

// Handle Perubahan Password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = password_hash($_POST['password_baru'], PASSWORD_DEFAULT);

    // Cek Password Lama
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_password = $result->fetch_assoc();

    if (password_verify($password_lama, $user_password['password'])) {
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $password_baru, $user_id);
        $stmt->execute();
        header("Location: settings.php?success=password");
    } else {
        $error = "Password lama salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Profil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/profilestyle.css">
</head>
<body>

<?php require '../general/back.php';?>

<div class="container">
    <h1>Pengaturan Profil</h1>

    <!-- Profile Info -->
    <div class="profile-container">
        <img src="../uploads/profiles/<?= $user['foto_profil'] ?: 'default.png' ?>" class="profile-img">
        <h2><?= $user['nama'] ?></h2>
        <p><?= $user['email'] ?></p>
    </div>

    <!-- Form Update Profil -->
    <form method="POST" enctype="multipart/form-data">
        <label>Nama:</label>
        <input type="text" name="nama" value="<?= $user['nama'] ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?= $user['email'] ?>" required>

        <label>Foto Profil:</label>
        <input type="file" name="foto_profil" accept="image/*">

        <button type="submit" name="update_profile" class="btn-save">
            <i class="fa-solid fa-save"></i> Simpan Perubahan
        </button>
    </form>

    <hr>

    <!-- Form Update Password -->
    <h2>Ubah Password</h2>
    <?php if (isset($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>
    <form method="POST">
        <label>Password Lama:</label>
        <input type="password" name="password_lama" required>

        <label>Password Baru:</label>
        <input type="password" name="password_baru" required>

        <button type="submit" name="update_password" class="btn-save">
            <i class="fa-solid fa-key"></i> Ubah Password
        </button>
    </form>
</div>

</body>
</html>
