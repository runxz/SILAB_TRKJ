<?php
require '../db.php';

$errorMessage = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $nama = htmlspecialchars(trim($_POST['nama'] ?? ''));
        $email = htmlspecialchars(trim($_POST['email'] ?? ''));
        $passwordRaw = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';

        if (empty($nama) || empty($email) || empty($passwordRaw) || empty($role)) {
            throw new Exception("Semua field harus diisi.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email tidak valid.");
        }

        $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

        $cekEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$cekEmail) {
            throw new Exception("Gagal menyiapkan query pengecekan email.");
        }

        $cekEmail->bind_param("s", $email);
        $cekEmail->execute();
        $cekEmail->store_result();

        if ($cekEmail->num_rows > 0) {
            throw new Exception("Email sudah digunakan.");
        }

        $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Gagal menyiapkan query penyimpanan.");
        }

        $stmt->bind_param("ssss", $nama, $email, $password, $role);
        if (!$stmt->execute()) {
            throw new Exception("Registrasi gagal disimpan ke database.");
        }

        $success = true;
        header("Location: ../");
        exit();
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/loginregister.css">
</head>
<body>

<div class="login-container">
    <h2>Register</h2>

    <?php if ($errorMessage): ?>
        <div class="error-message" style="color: red; margin-bottom: 15px;">
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <form method="post" class="login-form">
        <input type="text" name="nama" placeholder="Nama" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role" required>
            <option value="">Pilih Peran</option>
            <option value="admin">Admin</option>
            <option value="mahasiswa">Mahasiswa</option>
            <option value="dosen">Dosen</option>
            <option value="asistendosen">Asisten Dosen</option>
        </select>
        <button type="submit" class="btn-login">Register</button>
    </form>

    <p>Login lewat <a href="../">sini</a></p>
</div>

</body>
</html>
