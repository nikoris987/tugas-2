<?php
// ==========================================================
// Konfigurasi koneksi database - SIMATIK
//
// Koneksi ke hosting InfinityFree
// ==========================================================

$host     = "sql202.infinityfree.com";
$user     = "if0_43037633";
$password = "D3g3C3fIMyrBT2";
$dbname   = "if0_43037633_universitas";

// Tampilkan pesan error yang jelas (bukan halaman kosong).
// Setelah website berjalan normal, baris display_errors boleh dihapus.
ini_set('display_errors', 1);
error_reporting(E_ALL);

set_exception_handler(function ($e) {
    http_response_code(500);
    echo "<h3>Terjadi kesalahan database</h3><p>" . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
});

$koneksi = mysqli_connect($host, $user, $password, $dbname);

mysqli_set_charset($koneksi, "utf8mb4");
?>
