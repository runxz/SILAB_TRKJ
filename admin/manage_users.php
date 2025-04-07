<?php
require '../auth.php';
check_role(['admin']);
require '../db.php';

// Handle Hapus User
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id = $id");
    header("Location: manage_users.php");
    exit();
}

// Handle Update Role User
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_role'])) {
    $id = intval($_POST['user_id']);
    $role = $_POST['role'];
    $conn->query("UPDATE users SET role = '$role' WHERE id = $id");
    header("Location: manage_users.php");
    exit();
}

// Handle Tambah User
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Email tidak valid.");
    }

    $conn->query("INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$password', '$role')");
    header("Location: manage_users.php");
    exit();
}

// Ambil Data User
$result = $conn->query("SELECT * FROM users");

// Fetch User Statistics
$stats_query = $conn->query("
    SELECT 
        SUM(CASE WHEN role = 'mahasiswa' THEN 1 ELSE 0 END) AS mahasiswa_count,
        SUM(CASE WHEN role = 'dosen' THEN 1 ELSE 0 END) AS dosen_count,
        SUM(CASE WHEN role = 'asistendosen' THEN 1 ELSE 0 END) AS asdos_count
    FROM users
");

$stats = $stats_query->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/adminstyle.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="logo">Web Labor TRKJ</div>
    <a href="./" class="logout-btn"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
</nav>

<div class="container">
    <h1>Manajemen User</h1>

    <!-- User Statistics -->
    <h2>Statistik Pengguna</h2>
    <div class="stats-container">
        <div class="stat-box mahasiswa">
            <i class="fa-solid fa-user-graduate"></i>
            <span>Mahasiswa</span>
            <h3><?= $stats['mahasiswa_count'] ?></h3>
        </div>
        <div class="stat-box dosen">
            <i class="fa-solid fa-chalkboard-teacher"></i>
            <span>Dosen</span>
            <h3><?= $stats['dosen_count'] ?></h3>
        </div>
        <div class="stat-box asdos">
            <i class="fa-solid fa-user-tie"></i>
            <span>Asisten Dosen</span>
            <h3><?= $stats['asdos_count'] ?></h3>
        </div>
    </div>

    <!-- Button to Open Modal -->
    <button class="btn-add" id="openModal">Tambah User</button>

    <h2>Daftar User</h2>
    <div class="table-container">
        <table class="styled-table">
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['nama'] ?></td>
                <td><?= $row['email'] ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                        <select name="role">
                            <option value="admin" <?= ($row['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                            <option value="mahasiswa" <?= ($row['role'] == 'mahasiswa') ? 'selected' : '' ?>>Mahasiswa</option>
                            <option value="dosen" <?= ($row['role'] == 'dosen') ? 'selected' : '' ?>>Dosen</option>
                            <option value="asistendosen" <?= ($row['role'] == 'asistendosen') ? 'selected' : '' ?>>Asisten Dosen</option>
                        </select>
                        <button type="submit" name="update_role" class="btn-edit">Ubah</button>
                    </form>
                </td>
                <td>
                    <a href="manage_users.php?delete=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Hapus user ini?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<!-- Modal Form for Adding User -->
<div class="modal" id="modalForm">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2>Tambah User</h2>
        <form method="POST" class="user-form">
            <input type="text" name="nama" placeholder="Nama" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role">
                <option value="admin">Admin</option>
                <option value="mahasiswa">Mahasiswa</option>
                <option value="dosen">Dosen</option>
                <option value="asistendosen">Asisten Dosen</option>
            </select>
            <button type="submit" name="add_user" class="btn-add">Tambah</button>
        </form>
    </div>
</div>

<!-- JavaScript -->
<script>
document.getElementById("openModal").addEventListener("click", function() {
    document.getElementById("modalForm").style.display = "block";
});

document.getElementById("closeModal").addEventListener("click", function() {
    document.getElementById("modalForm").style.display = "none";
});

window.addEventListener("click", function(event) {
    if (event.target === document.getElementById("modalForm")) {
        document.getElementById("modalForm").style.display = "none";
    }
});
</script>

</body>
</html>
