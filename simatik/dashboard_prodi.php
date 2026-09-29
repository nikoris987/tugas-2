<?php
require_once "auth.php";
require_role('prodi');
require_once "koneksi.php";

// Kode prodi diambil dari sesi, BUKAN dari input pengguna
$kode_prodi = $_SESSION['kode_prodi'];

// Info prodi milik akun ini
$stmt = mysqli_prepare($koneksi, "
    SELECT ps.kode_prodi, ps.nama_prodi, f.nama_fakultas
    FROM program_studi ps
    LEFT JOIN fakultas f ON ps.kode_fakultas = f.kode_fakultas
    WHERE ps.kode_prodi = ?");
mysqli_stmt_bind_param($stmt, "s", $kode_prodi);
mysqli_stmt_execute($stmt);
$info = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$info) {           // prodi sudah dihapus admin
    session_destroy();
    header("Location: login.php");
    exit;
}

// ---------- Proses ubah prodi mahasiswa ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $npm        = isset($_POST['npm']) ? $_POST['npm'] : '';
    $prodi_baru = isset($_POST['kode_prodi']) ? $_POST['kode_prodi'] : '';

    // Mahasiswa harus benar-benar milik prodi ini
    $stmt = mysqli_prepare($koneksi, "SELECT nama_mahasiswa FROM mahasiswa WHERE npm = ? AND kode_prodi = ?");
    mysqli_stmt_bind_param($stmt, "ss", $npm, $kode_prodi);
    mysqli_stmt_execute($stmt);
    $mhs = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    // Prodi tujuan harus ada
    $stmt = mysqli_prepare($koneksi, "SELECT nama_prodi FROM program_studi WHERE kode_prodi = ?");
    mysqli_stmt_bind_param($stmt, "s", $prodi_baru);
    mysqli_stmt_execute($stmt);
    $tujuan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$mhs) {
        flash_set('err', "Mahasiswa tidak ditemukan di prodi Anda.");
    } elseif (!$tujuan) {
        flash_set('err', "Program studi tujuan tidak valid.");
    } elseif ($prodi_baru === $kode_prodi) {
        flash_set('err', "Program studi tujuan sama dengan prodi saat ini, tidak ada yang diubah.");
    } else {
        $stmt = mysqli_prepare($koneksi, "UPDATE mahasiswa SET kode_prodi = ? WHERE npm = ? AND kode_prodi = ?");
        mysqli_stmt_bind_param($stmt, "sss", $prodi_baru, $npm, $kode_prodi);
        mysqli_stmt_execute($stmt);
        flash_set('ok', $mhs['nama_mahasiswa'] . " dipindahkan ke " . $tujuan['nama_prodi'] . ".");
    }
    header("Location: dashboard_prodi.php");
    exit;
}

// ---------- Mode edit: hanya untuk mahasiswa prodi ini ----------
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = mysqli_prepare($koneksi, "SELECT npm, nama_mahasiswa FROM mahasiswa WHERE npm = ? AND kode_prodi = ?");
    mysqli_stmt_bind_param($stmt, "ss", $_GET['edit'], $kode_prodi);
    mysqli_stmt_execute($stmt);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$edit) {
        flash_set('err', "Mahasiswa tidak ditemukan di prodi Anda.");
    }
}

$daftar_prodi = mysqli_query($koneksi, "
    SELECT ps.kode_prodi, ps.nama_prodi, f.nama_fakultas
    FROM program_studi ps
    LEFT JOIN fakultas f ON ps.kode_fakultas = f.kode_fakultas
    ORDER BY f.nama_fakultas, ps.nama_prodi");

// Daftar mahasiswa: WHERE kode_prodi memastikan hanya prodi sendiri yang tampil (tanpa kolom password)
$stmt = mysqli_prepare($koneksi, "
    SELECT npm, nama_mahasiswa, jenis_kelamin, tempat_lahir, tanggal_lahir, tanggal_masuk, alamat
    FROM mahasiswa
    WHERE kode_prodi = ?
    ORDER BY npm");
mysqli_stmt_bind_param($stmt, "s", $kode_prodi);
mysqli_stmt_execute($stmt);
$data   = mysqli_stmt_get_result($stmt);
$jumlah = mysqli_num_rows($data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIMATIK - Dashboard Prodi</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php require "partials/nav_user.php"; ?>

<main>
    <h1>Prodi <?= htmlspecialchars($info['nama_prodi']) ?></h1>
    <p class="stat-line">Fakultas <?= htmlspecialchars($info['nama_fakultas'] ?? '-') ?> &middot;
        <span class="badge"><?= $jumlah ?> mahasiswa</span></p>

    <?php flash_tampil(); ?>

    <?php if ($edit): ?>
    <form method="post" class="form-card" onsubmit="return confirm('Pindahkan mahasiswa ini ke program studi yang dipilih?')">
        <?= csrf_field() ?>
        <input type="hidden" name="npm" value="<?= htmlspecialchars($edit['npm']) ?>">
        <h2>Ubah Program Studi</h2>
        <div class="hint"><?= htmlspecialchars($edit['nama_mahasiswa']) ?> (<?= htmlspecialchars($edit['npm']) ?>)</div>

        <label>Program Studi Baru</label>
        <select name="kode_prodi" required>
            <?php while ($p = mysqli_fetch_assoc($daftar_prodi)): ?>
                <option value="<?= htmlspecialchars($p['kode_prodi']) ?>"
                    <?= $p['kode_prodi'] === $kode_prodi ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nama_prodi']) ?> (<?= htmlspecialchars($p['nama_fakultas'] ?? '-') ?>)
                </option>
            <?php endwhile; ?>
        </select>
        <div class="hint">Setelah dipindahkan, mahasiswa tidak lagi tampil di daftar prodi Anda.</div>

        <button type="submit">Simpan</button>
        <a href="dashboard_prodi.php" class="btn-cancel">Batal</a>
    </form>
    <?php endif; ?>

    <div class="table-wrap">
    <table>
        <tr>
            <th>NPM</th><th>Nama</th><th>JK</th><th>Tempat, Tgl Lahir</th><th>Alamat</th><th>Tgl Masuk</th><th>Aksi</th>
        </tr>
        <?php if ($jumlah === 0): ?>
            <tr><td colspan="7">Belum ada mahasiswa di program studi ini.</td></tr>
        <?php endif; ?>
        <?php while ($row = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><?= htmlspecialchars($row['npm']) ?></td>
            <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
            <td><?= htmlspecialchars($row['jenis_kelamin']) ?></td>
            <td><?= htmlspecialchars(trim(($row['tempat_lahir'] ?? '') . ', ' . ($row['tanggal_lahir'] ?? ''), ', ')) ?></td>
            <td><?= htmlspecialchars($row['alamat'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['tanggal_masuk'] ?? '') ?></td>
            <td><a href="?edit=<?= urlencode($row['npm']) ?>">Ubah Prodi</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
    </div>
</main>
</body>
</html>
