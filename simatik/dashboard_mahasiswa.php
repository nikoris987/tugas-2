<?php
require_once "auth.php";
require_role('mahasiswa');
require_once "koneksi.php";

// NPM diambil dari sesi, BUKAN dari form, supaya mahasiswa hanya bisa mengubah datanya sendiri
$npm = $_SESSION['npm'];

function ambil_data($koneksi, $npm) {
    $stmt = mysqli_prepare($koneksi, "
        SELECT m.npm, m.nama_mahasiswa, m.jenis_kelamin, m.tempat_lahir, m.tanggal_lahir,
               m.tanggal_masuk, m.alamat, ps.nama_prodi, f.nama_fakultas
        FROM mahasiswa m
        LEFT JOIN program_studi ps ON m.kode_prodi = ps.kode_prodi
        LEFT JOIN fakultas f ON ps.kode_fakultas = f.kode_fakultas
        WHERE m.npm = ?");
    mysqli_stmt_bind_param($stmt, "s", $npm);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$mhs = ambil_data($koneksi, $npm);
if (!$mhs) {            // data sudah dihapus admin
    session_destroy();
    header("Location: login.php");
    exit;
}

$_SESSION['nama'] = $mhs['nama_mahasiswa'];   // jaga nama di navigasi tetap terbaru

$errors = [];
$form = [
    'jenis_kelamin' => $mhs['jenis_kelamin'],
    'tempat_lahir'  => $mhs['tempat_lahir'],
    'tanggal_lahir' => $mhs['tanggal_lahir'],
    'alamat'        => $mhs['alamat'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    // Hanya 4 field + password yang dibaca. nama, npm, dan prodi dari form diabaikan.
    $form['jenis_kelamin'] = isset($_POST['jenis_kelamin']) ? $_POST['jenis_kelamin'] : '';
    $form['tempat_lahir']  = isset($_POST['tempat_lahir'])  ? trim($_POST['tempat_lahir']) : '';
    $form['tanggal_lahir'] = isset($_POST['tanggal_lahir']) ? trim($_POST['tanggal_lahir']) : '';
    $form['alamat']        = isset($_POST['alamat'])        ? trim($_POST['alamat']) : '';
    $pass_baru = isset($_POST['password_baru'])       ? $_POST['password_baru'] : '';
    $pass_konf = isset($_POST['password_konfirmasi']) ? $_POST['password_konfirmasi'] : '';

    if (!in_array($form['jenis_kelamin'], ['L', 'P'], true)) {
        $errors[] = "Jenis kelamin tidak valid.";
    }
    if (strlen($form['tempat_lahir']) > 100) {
        $errors[] = "Tempat lahir terlalu panjang (maksimal 100 karakter).";
    }
    if ($form['tanggal_lahir'] !== '') {
        if (!tanggal_valid($form['tanggal_lahir'])) {
            $errors[] = "Format tanggal lahir tidak valid.";
        } elseif ($form['tanggal_lahir'] > date('Y-m-d')) {
            $errors[] = "Tanggal lahir tidak boleh di masa depan.";
        }
    }
    if ($pass_baru !== '') {
        if (strlen($pass_baru) < 6) {
            $errors[] = "Password baru minimal 6 karakter.";
        } elseif ($pass_baru !== $pass_konf) {
            $errors[] = "Konfirmasi password tidak sama.";
        }
    }

    if (!$errors) {
        $tgl = ($form['tanggal_lahir'] === '') ? null : $form['tanggal_lahir'];

        if ($pass_baru !== '') {
            $hash = password_hash($pass_baru, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($koneksi, "UPDATE mahasiswa
                SET jenis_kelamin = ?, tempat_lahir = ?, tanggal_lahir = ?, alamat = ?, password = ?
                WHERE npm = ?");
            mysqli_stmt_bind_param($stmt, "ssssss",
                $form['jenis_kelamin'], $form['tempat_lahir'], $tgl, $form['alamat'], $hash, $npm);
        } else {
            $stmt = mysqli_prepare($koneksi, "UPDATE mahasiswa
                SET jenis_kelamin = ?, tempat_lahir = ?, tanggal_lahir = ?, alamat = ?
                WHERE npm = ?");
            mysqli_stmt_bind_param($stmt, "sssss",
                $form['jenis_kelamin'], $form['tempat_lahir'], $tgl, $form['alamat'], $npm);
        }
        mysqli_stmt_execute($stmt);

        flash_set('ok', $pass_baru !== '' ? "Data dan password berhasil disimpan." : "Data berhasil disimpan.");
        header("Location: dashboard_mahasiswa.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIMATIK - Dashboard Mahasiswa</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php require "partials/nav_user.php"; ?>

<main>
    <h1>Dashboard Mahasiswa</h1>

    <?php flash_tampil(); ?>
    <?php if ($errors): ?>
        <div class="alert alert-err">
            <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="info-card">
        <h2>Data Akademik</h2>
        <div class="info-row"><span class="k">NPM</span><span class="v"><?= htmlspecialchars($mhs['npm']) ?></span></div>
        <div class="info-row"><span class="k">Nama</span><span class="v"><?= htmlspecialchars($mhs['nama_mahasiswa']) ?></span></div>
        <div class="info-row"><span class="k">Program Studi</span><span class="v"><?= htmlspecialchars($mhs['nama_prodi'] ?? '-') ?></span></div>
        <div class="info-row"><span class="k">Fakultas</span><span class="v"><?= htmlspecialchars($mhs['nama_fakultas'] ?? '-') ?></span></div>
        <div class="info-row"><span class="k">Tanggal Masuk</span><span class="v"><?= htmlspecialchars($mhs['tanggal_masuk'] ?? '-') ?></span></div>
        <p class="note">Nama diubah oleh Admin, dan program studi diubah oleh Prodi. Hubungi mereka jika ada kesalahan.</p>
    </div>

    <form method="post" class="form-card">
        <?= csrf_field() ?>
        <h2>Ubah Data Pribadi</h2>

        <label>Jenis Kelamin</label>
        <select name="jenis_kelamin" required>
            <option value="L" <?= $form['jenis_kelamin'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
            <option value="P" <?= $form['jenis_kelamin'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
        </select>

        <label>Tempat Lahir</label>
        <input type="text" name="tempat_lahir" maxlength="100" value="<?= htmlspecialchars($form['tempat_lahir'] ?? '') ?>">

        <label>Tanggal Lahir</label>
        <input type="date" name="tanggal_lahir" max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($form['tanggal_lahir'] ?? '') ?>">

        <label>Alamat</label>
        <textarea name="alamat" rows="3"><?= htmlspecialchars($form['alamat'] ?? '') ?></textarea>

        <h2 style="margin-top:8px;">Ganti Password</h2>
        <div class="hint">Kosongkan jika tidak ingin mengubah password.</div>

        <label>Password Baru</label>
        <input type="password" name="password_baru" autocomplete="new-password">

        <label>Konfirmasi Password Baru</label>
        <input type="password" name="password_konfirmasi" autocomplete="new-password">

        <button type="submit">Simpan Perubahan</button>
    </form>
</main>
</body>
</html>
