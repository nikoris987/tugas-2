<nav class="navbar">
    <div class="brand">SIMATIK</div>
    <ul>
        <li><a href="index.php">Beranda</a></li>
        <li><a href="<?= htmlspecialchars(halaman_utama_role($_SESSION['role'])) ?>">Dashboard</a></li>
        <li><span class="nav-user"><?= htmlspecialchars(isset($_SESSION['nama']) ? $_SESSION['nama'] : '') ?></span></li>
        <li><a href="logout.php" onclick="return confirm('Yakin ingin keluar?')">Logout</a></li>
    </ul>
</nav>
