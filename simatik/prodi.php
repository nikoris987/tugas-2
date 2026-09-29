<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
require_once "koneksi.php";

// Hapus data
if (isset($_GET['hapus'])) {
    $stmt = mysqli_prepare($koneksi, "DELETE FROM program_studi WHERE kode_prodi = ?");
    mysqli_stmt_bind_param($stmt, "s", $_GET['hapus']);
    mysqli_stmt_execute($stmt);
    header("Location: prodi.php");
    exit;
}

// Simpan data (tambah / update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode     = trim($_POST['kode_prodi']);
    $nama     = trim($_POST['nama_prodi']);
    $fakultas = $_POST['kode_fakultas'];

    if ($_POST['mode'] === 'tambah') {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO program_studi (kode_prodi, nama_prodi, kode_fakultas) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $kode, $nama, $fakultas);
    } else {
        $kode_lama = $_POST['kode_lama'];
        $stmt = mysqli_prepare($koneksi, "UPDATE program_studi SET kode_prodi = ?, nama_prodi = ?, kode_fakultas = ? WHERE kode_prodi = ?");
        mysqli_stmt_bind_param($stmt, "ssss", $kode, $nama, $fakultas, $kode_lama);
    }
    mysqli_stmt_execute($stmt);
    header("Location: prodi.php");
    exit;
}

// Ambil data untuk mode edit
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM program_studi WHERE kode_prodi = ?");
    mysqli_stmt_bind_param($stmt, "s", $_GET['edit']);
    mysqli_stmt_execute($stmt);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$daftar_fakultas = mysqli_query($koneksi, "SELECT * FROM fakultas ORDER BY nama_fakultas");

$data = mysqli_query($koneksi, "
    SELECT ps.*, f.nama_fakultas
    FROM program_studi ps
    JOIN fakultas f ON ps.kode_fakultas = f.kode_fakultas
    ORDER BY ps.kode_prodi
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SIMATIK - Program Studi</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php require "partials/nav.php"; ?>

<main>
    <h1>Data Program Studi</h1>

    <form method="post" class="form-card">
        <input type="hidden" name="mode" value="<?= $edit ? 'edit' : 'tambah' ?>">
        <?php if ($edit): ?>
            <input type="hidden" name="kode_lama" value="<?= htmlspecialchars($edit['kode_prodi']) ?>">
        <?php endif; ?>

        <label>Kode Prodi</label>
        <input type="text" name="kode_prodi" required
               value="<?= $edit ? htmlspecialchars($edit['kode_prodi']) : '' ?>">

        <label>Nama Program Studi</label>
        <input type="text" name="nama_prodi" required
               value="<?= $edit ? htmlspecialchars($edit['nama_prodi']) : '' ?>">

        <label>Fakultas</label>
        <select name="kode_fakultas" required>
            <option value="">-- Pilih Fakultas --</option>
            <?php mysqli_data_seek($daftar_fakultas, 0); while ($f = mysqli_fetch_assoc($daftar_fakultas)): ?>
                <option value="<?= htmlspecialchars($f['kode_fakultas']) ?>"
                    <?= ($edit && $edit['kode_fakultas'] === $f['kode_fakultas']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['nama_fakultas']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit"><?= $edit ? 'Update' : 'Tambah' ?></button>
        <?php if ($edit): ?><a href="prodi.php" class="btn-cancel">Batal edit</a><?php endif; ?>
    </form>

    <table>
        <tr><th>Kode</th><th>Nama Prodi</th><th>Fakultas</th><th>Aksi</th></tr>
        <?php while ($row = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><?= htmlspecialchars($row['kode_prodi']) ?></td>
            <td><?= htmlspecialchars($row['nama_prodi']) ?></td>
            <td><?= htmlspecialchars($row['nama_fakultas']) ?></td>
            <td>
                <a href="?edit=<?= urlencode($row['kode_prodi']) ?>">Edit</a> |
                <a href="?hapus=<?= urlencode($row['kode_prodi']) ?>"
                   onclick="return confirm('Hapus program studi ini?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</main>
</body>
</html>
