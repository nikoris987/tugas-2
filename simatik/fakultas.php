<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
require_once "koneksi.php";

// Hapus data
if (isset($_GET['hapus'])) {
    $stmt = mysqli_prepare($koneksi, "DELETE FROM fakultas WHERE kode_fakultas = ?");
    mysqli_stmt_bind_param($stmt, "s", $_GET['hapus']);
    mysqli_stmt_execute($stmt);
    header("Location: fakultas.php");
    exit;
}

// Simpan data (tambah / update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = trim($_POST['kode_fakultas']);
    $nama = trim($_POST['nama_fakultas']);

    if ($_POST['mode'] === 'tambah') {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO fakultas (kode_fakultas, nama_fakultas) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $kode, $nama);
    } else {
        $kode_lama = $_POST['kode_lama'];
        $stmt = mysqli_prepare($koneksi, "UPDATE fakultas SET kode_fakultas = ?, nama_fakultas = ? WHERE kode_fakultas = ?");
        mysqli_stmt_bind_param($stmt, "sss", $kode, $nama, $kode_lama);
    }
    mysqli_stmt_execute($stmt);
    header("Location: fakultas.php");
    exit;
}

// Ambil data untuk mode edit
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM fakultas WHERE kode_fakultas = ?");
    mysqli_stmt_bind_param($stmt, "s", $_GET['edit']);
    mysqli_stmt_execute($stmt);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$data = mysqli_query($koneksi, "SELECT * FROM fakultas ORDER BY kode_fakultas");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SIMATIK - Fakultas</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php require "partials/nav.php"; ?>

<main>
    <h1>Data Fakultas</h1>

    <form method="post" class="form-card">
        <input type="hidden" name="mode" value="<?= $edit ? 'edit' : 'tambah' ?>">
        <?php if ($edit): ?>
            <input type="hidden" name="kode_lama" value="<?= htmlspecialchars($edit['kode_fakultas']) ?>">
        <?php endif; ?>

        <label>Kode Fakultas</label>
        <input type="text" name="kode_fakultas" required
               value="<?= $edit ? htmlspecialchars($edit['kode_fakultas']) : '' ?>">

        <label>Nama Fakultas</label>
        <input type="text" name="nama_fakultas" required
               value="<?= $edit ? htmlspecialchars($edit['nama_fakultas']) : '' ?>">

        <button type="submit"><?= $edit ? 'Update' : 'Tambah' ?></button>
        <?php if ($edit): ?><a href="fakultas.php" class="btn-cancel">Batal edit</a><?php endif; ?>
    </form>

    <table>
        <tr><th>Kode</th><th>Nama Fakultas</th><th>Aksi</th></tr>
        <?php while ($row = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><?= htmlspecialchars($row['kode_fakultas']) ?></td>
            <td><?= htmlspecialchars($row['nama_fakultas']) ?></td>
            <td>
                <a href="?edit=<?= urlencode($row['kode_fakultas']) ?>">Edit</a> |
                <a href="?hapus=<?= urlencode($row['kode_fakultas']) ?>"
                   onclick="return confirm('Hapus fakultas ini?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</main>
</body>
</html>
