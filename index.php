<?php
session_start();
require './db.php';

// Absolute Path
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] );

// Jika sudah login, langsung redirect
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    redirect_to_dashboard();
}

// Fungsi redirect berdasarkan role
function redirect_to_dashboard() {
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: " . BASE_URL . "/lab/admin/");
            break;
        case 'mahasiswa':
            header("Location: " . BASE_URL . "/lab/mahasiswa/");
            break;
        case 'dosen':
        case 'asistendosen':
            header("Location: " . BASE_URL . "/lab/dosen/");
            break;
    }
    exit();
}

// Handle Login
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed_password, $role);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['role'] = $role;
        redirect_to_dashboard();
    } else {
        $error = "Email atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/loginregister.css">

</head>
<body>

<div class="login-container">
    <h2>Login</h2>
    
    <?php if ($error): ?>
        <p class="error-message"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" class="login-form">
        <label>Email</label>
        <input type="email" name="email" placeholder="Masukkan Email" required>

        <label>Password</label>
        <input type="password" name="password" placeholder="Masukkan Password" required>

        <button type="submit" class="btn-login">Login</button>
    </form>
    <p>Register Akun <a href="./register/">Disini</a></p>
</div>

</body>
</html>
