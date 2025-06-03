<?php
require '../../auth.php';
check_role(['dosen']);
require '../../db.php';

$dosen_id = $_SESSION['user_id'];

// Handle Tambah Praktikum
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_praktikum'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $semester = $_POST['semester'];
    $laboratorium = $_POST['laboratorium'];

    $stmt = $conn->prepare("INSERT INTO praktikum (nama, deskripsi, dosen_id, semester, laboratorium) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiss", $nama, $deskripsi, $dosen_id, $semester, $laboratorium);
    $stmt->execute();
    header("Location: index.php");
    exit();
}

// Handle Edit Praktikum
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_praktikum'])) {
    $praktikum_id = intval($_POST['praktikum_id']);
    $nama = htmlspecialchars($_POST['nama']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $semester = $_POST['semester'];
    $laboratorium = $_POST['laboratorium'];

    $stmt = $conn->prepare("UPDATE praktikum SET nama = ?, deskripsi = ?, semester = ?, laboratorium = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $nama, $deskripsi, $semester, $laboratorium, $praktikum_id);
    $stmt->execute();
    header("Location: index.php");
    exit();
}

// Handle Hapus Praktikum
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_praktikum'])) {
    $praktikum_id = intval($_POST['praktikum_id']);

    $stmt = $conn->prepare("DELETE FROM praktikum WHERE id = ?");
    $stmt->bind_param("i", $praktikum_id);
    $stmt->execute();
    header("Location: index.php");
    exit();
}

// Ambil daftar praktikum yang dibuat oleh dosen ini
$praktikum = $conn->query("SELECT * FROM praktikum WHERE dosen_id = $dosen_id");

// Ambil daftar asisten yang telah lolos seleksi berdasarkan tabel seleksi
$asisten_per_praktikum = [];
$query = "SELECT sa.asisten_id, u.nama, s.praktikum_id
          FROM seleksi_asisten sa
          JOIN seleksi s ON sa.seleksi_id = s.id
          JOIN users u ON sa.asisten_id = u.id
          WHERE sa.status = 'diterima'";

$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $asisten_per_praktikum[$row['praktikum_id']][] = $row;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Praktikum</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/dosenstyle.css">
         <style>
.modal-content form {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding-top: 10px;
}

.modal-content label {
    font-weight: bold;
    color: #003366;
    margin-bottom: 4px;
    text-align: left;
}

.modal-content input[type="number"] {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
}

.modal-content button[type="submit"] {
    background-color: #003366;
    color: white;
    padding: 10px 18px;
    font-weight: bold;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 12px;
    align-self: flex-start;
}

.modal-content button[type="submit"]:hover {
    background-color: #002244;
}

       
     </style>
</head>
<body>

<?php require '../../general/back.php';?>

<div class="container">
    <h1>Kelola Praktikum</h1>

    <!-- Button to Open Modal -->
    <button class="btn-add" id="openModal">Tambah Praktikum</button>

    <!-- Praktikum List -->
    <h2>Daftar Praktikum</h2>
    <div class="table-container">
        <table class="styled-table">
            <tr>
                <th>ID</th>
                <th>Nama Praktikum</th>
                <th>Semester</th>
                <th>Laboratorium</th>
                <th>Deskripsi</th>
                <th>Aksi</th>
            </tr>
            <?php while ($row = $praktikum->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['nama'] ?></td>
                <td><?= htmlspecialchars($row['semester']) ?></td>
                <td><?= htmlspecialchars($row['laboratorium']) ?></td>
                <td><?= $row['deskripsi'] ?></td>
                <td>
                    <button class="btn-edit" onclick="openEditModal(<?= $row['id'] ?>, '<?= $row['nama'] ?>', '<?= $row['deskripsi'] ?>')">Edit</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="praktikum_id" value="<?= $row['id'] ?>">
                        <button type="submit" name="delete_praktikum" class="btn-delete" onclick="return confirm('Hapus praktikum ini?')">Hapus</button>
                    </form>
                    <button class="btn-assign" onclick="openAssignModal(<?= $row['id'] ?>)">Pilih Asisten</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<!-- Modal for Adding Praktikum -->
<div class="modal" id="modalForm">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2>Tambah Praktikum</h2>
        <form method="POST" class="styled-form">
            <label>Nama Praktikum :</label>
            <input type="text" name="nama" placeholder="Nama Praktikum" required>
            <label>Deskripsi</label>
            <textarea name="deskripsi" placeholder="Deskripsi" required></textarea>
<label>Semester:</label>
<select name="semester" required>
  <option value="">Pilih Semester</option>
  <option value="Ganjil">Ganjil</option>
  <option value="Genap">Genap</option>
</select>


<label>Laboratorium:</label>
<select name="laboratorium" required>
  <option value="">Pilih Laboratorium</option>
  <option value="Laboratorium Embedded System">Laboratorium Embedded System</option>
  <option value="Laboratorium Jaringan">Laboratorium Jaringan</option>
  <option value="Laboratorium Komputasi">Laboratorium Komputasi</option>
  <option value="Laboratorium Server dan Data Center">Laboratorium Server dan Data Center</option>
</select>

            <button type="submit" name="add_praktikum" class="btn-add">Tambah</button>
        </form>
    </div>
</div>

<!-- Modal for Editing Praktikum -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <span class="close" id="closeEditModal">&times;</span>
        <h2>Edit Praktikum</h2>
        <form method="POST" class="styled-form">
            <input type="hidden" name="praktikum_id" id="edit_praktikum_id">
            <label>Nama Praktikum:</label>
            <input type="text" name="nama" id="edit_nama" placeholder="Nama Praktikum" required>
<label>Semester:</label>
<select name="semester" id="edit_semester" required>
  <option value="">Pilih Semester</option>
  <option value="Ganjil">Ganjil</option>
  <option value="Genap">Genap</option>
</select>


<label>Laboratorium:</label>
<select name="laboratorium" id="edit_laboratorium" required>
  <option value="">Pilih Laboratorium</option>
  <option value="Laboratorium Embedded System">Laboratorium Embedded System</option>
  <option value="Laboratorium Jaringan">Laboratorium Jaringan</option>
  <option value="Laboratorium Komputasi">Laboratorium Komputasi</option>
  <option value="Laboratorium Server dan Data Center">Laboratorium Server dan Data Center</option>
</select>
<label>Deskripsi:</label>
            <textarea name="deskripsi" id="edit_deskripsi" placeholder="Deskripsi" required></textarea>
            <button type="submit" name="edit_praktikum" class="btn-edit">Simpan</button>
        </form>
    </div>
</div>

<!-- Modal for Assigning Asisten -->
<div class="modal" id="assignModal">
    <div class="modal-content">
        <span class="close" id="closeAssignModal">&times;</span>
        <h2>Tambahkan Asisten Dosen</h2>
        <form method="POST" class="styled-form">
            <input type="hidden" name="praktikum_id" id="praktikum_id">
            <select name="asisten_id" id="asisten_select" required>
                <option value="">Pilih Asisten</option>
            </select>
            <button type="submit" name="add_asisten" class="btn-add">Tambahkan</button>
        </form>
    </div>
</div>


<!-- JavaScript for Modal -->
<script>
document.getElementById("openModal").addEventListener("click", function() {
    document.getElementById("modalForm").style.display = "block";
});

document.getElementById("closeModal").addEventListener("click", function() {
    document.getElementById("modalForm").style.display = "none";
});

document.getElementById("closeEditModal").addEventListener("click", function() {
    document.getElementById("editModal").style.display = "none";
});

document.getElementById("closeAssignModal").addEventListener("click", function() {
    document.getElementById("assignModal").style.display = "none";
});

window.onclick = function(event) {
    if (event.target === document.getElementById("modalForm")) {
        document.getElementById("modalForm").style.display = "none";
    }
    if (event.target === document.getElementById("editModal")) {
        document.getElementById("editModal").style.display = "none";
    }
    if (event.target === document.getElementById("assignModal")) {
        document.getElementById("assignModal").style.display = "none";
    }
};

function openEditModal(id, nama, deskripsi, semester, laboratorium) {
    document.getElementById("edit_praktikum_id").value = id;
    document.getElementById("edit_nama").value = nama;
    document.getElementById("edit_deskripsi").value = deskripsi;
    document.getElementById("edit_semester").value = semester;
    document.getElementById("edit_laboratorium").value = laboratorium;
    document.getElementById("editModal").style.display = "block";
}


function openAssignModal(praktikum_id) {
    document.getElementById("praktikum_id").value = praktikum_id;

    // Ambil dropdown asisten
    var select = document.getElementById("asisten_select");
    select.innerHTML = "<option value=''>Pilih Asisten</option>"; 

    // Data asisten yang lolos seleksi berdasarkan praktikum_id
    var asistenData = <?= json_encode($asisten_per_praktikum) ?>;
    
    if (asistenData[praktikum_id]) {
        asistenData[praktikum_id].forEach(asisten => {
            var option = document.createElement("option");
            option.value = asisten.asisten_id;
            option.textContent = asisten.nama;
            select.appendChild(option);
        });
    }

    document.getElementById("assignModal").style.display = "block";
}

</script>

</body>
</html>
