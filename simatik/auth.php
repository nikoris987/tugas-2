<?php
// ==========================================================
// Fungsi bantu: sesi, peran (role), CSRF, dan pesan flash
// Peran yang dikenal: admin, prodi, mahasiswa
// ==========================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Halaman utama tiap peran
function halaman_utama_role($role) {
    $peta = [
        'admin'     => 'dashboard.php',
        'prodi'     => 'dashboard_prodi.php',
        'mahasiswa' => 'dashboard_mahasiswa.php',
    ];
    return isset($peta[$role]) ? $peta[$role] : 'login.php';
}

// Wajibkan peran tertentu, kalau tidak cocok kembali ke login
function require_role($role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header("Location: login.php");
        exit;
    }
}

// ---------- CSRF ----------
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_check() {
    $dikirim = isset($_POST['csrf']) ? $_POST['csrf'] : '';
    $asli    = isset($_SESSION['csrf']) ? $_SESSION['csrf'] : '';
    if ($asli === '' || !hash_equals($asli, $dikirim)) {
        http_response_code(403);
        die("Permintaan tidak valid (token keamanan salah). Muat ulang halaman lalu coba lagi.");
    }
}

// ---------- Pesan flash ----------
function flash_set($tipe, $pesan) {
    $_SESSION['flash'] = [$tipe, $pesan];
}

function flash_tampil() {
    if (!empty($_SESSION['flash'])) {
        $tipe  = $_SESSION['flash'][0];
        $pesan = $_SESSION['flash'][1];
        unset($_SESSION['flash']);
        $kelas = ($tipe === 'ok') ? 'alert-ok' : 'alert-err';
        echo '<div class="alert ' . $kelas . '">' . htmlspecialchars($pesan) . '</div>';
    }
}

// ---------- Validasi ----------
function tanggal_valid($s) {
    $d = DateTime::createFromFormat('Y-m-d', $s);
    return $d && $d->format('Y-m-d') === $s;
}
