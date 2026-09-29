<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
require_once "koneksi.php";

// Hapus data
if (isset($_GET['hapus'])) {
    $stmt = mysqli_prepare($koneksi, "DELETE FROM mahasiswa WHERE npm = ?");
    mysqli_stmt_bind_param($stmt, "s", $_GET['hapus']);
    mysqli_stmt_execute($stmt);
    header("Location: mahasiswa.php");
    exit;
}

// Simpan data (tambah / update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $npm            = trim($_POST['npm']);
    $nama           = trim($_POST['nama_mahasiswa']);
    $jk             = $_POST['jenis_kelamin'];
    $tempat_lahir   = trim($_POST['tempat_lahir']);
    $tanggal_lahir  = $_POST['tanggal_lahir'];
    $tanggal_masuk  = $_POST['tanggal_masuk'];
    $alamat         = trim($_POST['alamat']);
    $kode_prodi     = $_POST['kode_prodi'];

    if ($_POST['mode'] === 'tambah') {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($koneksi, "INSERT INTO mahasiswa
            (npm, nama_mahasiswa, jenis_kelamin, tempat_lahir, tanggal_lahir, tanggal_masuk, alamat, password, kode_prodi)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssssssss",
            $npm, $nama, $jk, $tempat_lahir, $tanggal_lahir, $tanggal_masuk, $alamat, $password, $kode_prodi);
    } else {
        $npm_lama = $_POST['npm_lama'];

        if (!empty($_POST['password'])) {
            // Password baru diisi -> update juga passwordnya
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($koneksi, "UPDATE mahasiswa SET
                npm = ?, nama_mahasiswa = ?, jenis_kelamin = ?, tempat_lahir = ?, tanggal_lahir = ?,
                tanggal_masuk = ?, alamat = ?, password = ?, kode_prodi = ? WHERE npm = ?");
            mysqli_stmt_bind_param($stmt, "ssssssssss",
                $npm, $nama, $jk, $tempat_lahir, $tanggal_lahir, $tanggal_masuk, $alamat, $password, $kode_prodi, $npm_lama);
        } else {
            // Password dikosongkan -> tidak diubah
            $stmt = mysqli_prepare($koneksi, "UPDATE mahasiswa SET
                npm = ?, nama_mahasiswa = ?, jenis_kelamin = ?, tempat_lahir = ?, tanggal_lahir = ?,
                tanggal_masuk = ?, alamat = ?, kode_prodi = ? WHERE npm = ?");
            mysqli_stmt_bind_param($stmt, "sssssssss",
                $npm, $nama, $jk, $tempat_lahir, $tanggal_lahir, $tanggal_masuk, $alamat, $kode_prodi, $npm_lama);
        }
    }
    mysqli_stmt_execute($stmt);
    header("Location: mahasiswa.php");
    exit;
}

// Ambil data untuk mode edit
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM mahasiswa WHERE npm = ?");
    mysqli_stmt_bind_param($stmt, "s", $_GET['edit']);
    mysqli_stmt_execute($stmt);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$daftar_prodi = mysqli_query($koneksi, "SELECT * FROM program_studi ORDER BY nama_prodi");

$data = mysqli_query($koneksi, "
    SELECT m.*, ps.nama_prodi
    FROM mahasiswa m
    JOIN program_studi ps ON m.kode_prodi = ps.kode_prodi
    ORDER BY m.npm
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SIMATIK - Mahasiswa</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php require "partials/nav.php"; ?>

<main>
    <h1>Data Mahasiswa</h1>

    <form method="post" class="form-card">
        <input type="hidden" name="mode" value="<?= $edit ? 'edit' : 'tambah' ?>">
        <?php if ($edit): ?>
            <input type="hidden" name="npm_lama" value="<?= htmlspecialchars($edit['npm']) ?>">
        <?php endif; ?>

        <label>NPM</label>
        <input type="text" name="npm" required value="<?= $edit ? htmlspecialchars($edit['npm']) : '' ?>">

        <label>Nama Mahasiswa</label>
        <input type="text" name="nama_mahasiswa" required value="<?= $edit ? htmlspecialchars($edit['nama_mahasiswa']) : '' ?>">

        <label>Jenis Kelamin</label>
        <select name="jenis_kelamin" required>
            <option value="L" <?= ($edit && $edit['jenis_kelamin'] === 'L') ? 'selected' : '' ?>>Laki-laki</option>
            <option value="P" <?= ($edit && $edit['jenis_kelamin'] === 'P') ? 'selected' : '' ?>>Perempuan</option>
        </select>

        <label>Tempat Lahir</label>
        <input type="text" name="tempat_lahir" value="<?= $edit ? htmlspecialchars($edit['tempat_lahir']) : '' ?>">

        <label>Tanggal Lahir</label>
        <input type="date" name="tanggal_lahir" value="<?= $edit ? htmlspecialchars($edit['tanggal_lahir']) : '' ?>">

        <label>Tanggal Masuk</label>
        <input type="date" name="tanggal_masuk" value="<?= $edit ? htmlspecialchars($edit['tanggal_masuk']) : '' ?>">

        <label>Alamat</label>
        <textarea name="alamat" rows="2"><?= $edit ? htmlspecialchars($edit['alamat']) : '' ?></textarea>

        <label>Program Studi</label>
        <select name="kode_prodi" required>
            <option value="">-- Pilih Program Studi --</option>
            <?php mysqli_data_seek($daftar_prodi, 0); while ($p = mysqli_fetch_assoc($daftar_prodi)): ?>
                <option value="<?= htmlspecialchars($p['kode_prodi']) ?>"
                    <?= ($edit && $edit['kode_prodi'] === $p['kode_prodi']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nama_prodi']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Password <?= $edit ? '(kosongkan jika tidak ingin mengubah)' : '' ?></label>
        <input type="password" name="password" <?= $edit ? '' : 'required' ?>>

        <button type="submit"><?= $edit ? 'Update' : 'Tambah' ?></button>
        <?php if ($edit): ?><a href="mahasiswa.php" class="btn-cancel">Batal edit</a><?php endif; ?>
    </form>

    <table>
        <tr>
            <th>NPM</th><th>Nama</th><th>JK</th><th>Prodi</th><th>Tgl Masuk</th><th>Aksi</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><?= htmlspecialchars($row['npm']) ?></td>
            <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
            <td><?= htmlspecialchars($row['jenis_kelamin']) ?></td>
            <td><?= htmlspecialchars($row['nama_prodi']) ?></td>
            <td><?= htmlspecialchars($row['tanggal_masuk']) ?></td>
            <td>
                <a href="?edit=<?= urlencode($row['npm']) ?>">Edit</a> |
                <a href="?hapus=<?= urlencode($row['npm']) ?>"
                   onclick="return confirm('Hapus mahasiswa ini?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</main>
</body>
</html>
