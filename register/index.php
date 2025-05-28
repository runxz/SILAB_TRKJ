<?php
require '../db.php';

$errorMessage = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $nama = htmlspecialchars(trim($_POST['nama'] ?? ''));
        $npm = htmlspecialchars(trim($_POST['npm'] ?? ''));
        $email = htmlspecialchars(trim($_POST['email'] ?? ''));
        $passwordRaw = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';

        if (empty($nama) || empty($npm) || empty($email) || empty($passwordRaw) || empty($role)) {
            throw new Exception("Semua field harus diisi.");
        }



        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email tidak valid.");
        }

        $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

        // Cek duplikat email atau npm
        $cekDuplikat = $conn->prepare("SELECT id FROM users WHERE email = ? OR npm = ?");
        if (!$cekDuplikat) {
            throw new Exception("Gagal menyiapkan query pengecekan duplikat.");
        }

        $cekDuplikat->bind_param("ss", $email, $npm);
        $cekDuplikat->execute();
        $cekDuplikat->store_result();

        if ($cekDuplikat->num_rows > 0) {
            throw new Exception("Email atau NPM sudah digunakan.");
        }

        $stmt = $conn->prepare("INSERT INTO users (nama, npm, email, password, role) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Gagal menyiapkan query penyimpanan.");
        }

        $stmt->bind_param("sssss", $nama, $npm, $email, $password, $role);
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
        <input type="text" name="nama" placeholder="Nama Lengkap" required>
        <input type="text" name="npm" placeholder="NPM (10 digit)" required>
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

    <p>Sudah punya akun? <a href="../">Login disini</a></p>
</div>

</body>
</html>
