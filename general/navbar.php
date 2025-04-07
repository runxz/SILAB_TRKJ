

<!-- Navbar -->
<nav class="navbar">
    <div class="logo">SILAB TRKJ</div>

    <!-- Profile Dropdown -->
    <div class="profile-menu">
        <button class="profile-btn" onclick="toggleDropdown()">
            <?php if ($user['foto_profil']): ?>
                <img src="../uploads/profiles/<?= $user['foto_profil'] ?>" class="profile-icon">
            <?php else: ?>
                <i class="fa-solid fa-user profile-icon"></i>
            <?php endif; ?>
        </button>

        <!-- Dropdown Menu -->
        <div id="dropdown-menu" class="dropdown-content">
            <a href="../profile/profile.php"><i class="fa-solid fa-user"></i> Profil</a>
            <a href="../profile/settings.php"><i class="fa-solid fa-cog"></i> Pengaturan</a>
            <a href="../general/logout.php" class="logout-link"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</nav>

<script>
function toggleDropdown() {
    document.getElementById("dropdown-menu").classList.toggle("show");
}

// Tutup dropdown jika klik di luar
window.onclick = function(event) {
    if (!event.target.matches('.profile-btn') && !event.target.matches('.profile-icon')) {
        let dropdown = document.getElementById("dropdown-menu");
        if (dropdown.classList.contains("show")) {
            dropdown.classList.remove("show");
        }
    }
};
</script>
