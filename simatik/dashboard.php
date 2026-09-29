<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
require_once "koneksi.php";

$jml_fakultas = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM fakultas"))['jml'];
$jml_prodi    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM program_studi"))['jml'];
$jml_mhs      = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM mahasiswa"))['jml'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SIMATIK - Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php require "partials/nav.php"; ?>

<main>
    <h1>Dashboard SIMATIK</h1>
    <div class="cards">
        <div class="card">
            <div class="num"><?= $jml_fakultas ?></div>
            <div class="label">Fakultas</div>
        </div>
        <div class="card">
            <div class="num"><?= $jml_prodi ?></div>
            <div class="label">Program Studi</div>
        </div>
        <div class="card">
            <div class="num"><?= $jml_mhs ?></div>
            <div class="label">Mahasiswa</div>
        </div>
    </div>
    <p>Gunakan menu di atas untuk mengelola data Fakultas, Program Studi, dan Mahasiswa.</p>
</main>
</body>
</html>
