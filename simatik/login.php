<?php
require_once "auth.php";
require_once "koneksi.php";

// Kalau sudah login, arahkan ke halaman sesuai peran
if (isset($_SESSION['role']) && halaman_utama_role($_SESSION['role']) !== 'login.php') {
    header("Location: " . halaman_utama_role($_SESSION['role']));
    exit;
}

$error    = "";
$role     = isset($_POST['role']) ? $_POST['role'] : 'mahasiswa';
$username = isset($_POST['username']) ? trim($_POST['username']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $sesi     = null;

    if ($role === 'admin') {
        $stmt = mysqli_prepare($koneksi, "SELECT username, password FROM admin WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        if ($row && password_verify($password, $row['password'])) {
            $sesi = [
                'role'            => 'admin',
                'admin_logged_in' => true,   // dipakai halaman admin yang sudah ada
                'nama'            => $row['username'],
            ];
        }

    } elseif ($role === 'prodi') {
        $stmt = mysqli_prepare($koneksi, "
            SELECT a.username, a.password, a.kode_prodi, ps.nama_prodi
            FROM akun_prodi a
            JOIN program_studi ps ON a.kode_prodi = ps.kode_prodi
            WHERE a.username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        if ($row && password_verify($password, $row['password'])) {
            $sesi = [
                'role'       => 'prodi',
                'kode_prodi' => $row['kode_prodi'],
                'nama'       => 'Prodi ' . $row['nama_prodi'],
            ];
        }

    } elseif ($role === 'mahasiswa') {
        $stmt = mysqli_prepare($koneksi, "SELECT npm, nama_mahasiswa, password FROM mahasiswa WHERE npm = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        if ($row && password_verify($password, $row['password'])) {
            $sesi = [
                'role' => 'mahasiswa',
                'npm'  => $row['npm'],
                'nama' => $row['nama_mahasiswa'],
            ];
        }
    }

    if ($sesi) {
        session_regenerate_id(true);
        $_SESSION = $sesi;
        header("Location: " . halaman_utama_role($sesi['role']));
        exit;
    }
    $error = "Username/NPM atau password salah.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - SIMATIK</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --navy:#0f1f38;
  --navy-light:#16324f;
  --accent:#f5a524;
  --teal:#2dd4bf;
  --text:#1f2937;
  --muted:#6b7280;
}
*{box-sizing:border-box;margin:0;padding:0;}
body{
  font-family:'Inter',sans-serif;
  background:linear-gradient(135deg,var(--navy) 0%,#173154 55%,#134e4a 100%);
  min-height:100vh;
  display:flex;align-items:center;justify-content:center;
  padding:20px;
}
.login-card{
  background:#fff;border-radius:16px;padding:40px 36px;width:100%;max-width:400px;
  box-shadow:0 20px 50px rgba(0,0,0,.25);
}
.login-card .logo{text-align:center;font-size:32px;margin-bottom:6px;}
.login-card h1{font-family:'Poppins',sans-serif;font-size:20px;text-align:center;color:var(--navy);margin-bottom:4px;}
.login-card .sub{text-align:center;color:var(--muted);font-size:13px;margin-bottom:24px;}
.roles{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:6px;}
.roles input{display:none;}
.roles span{
  display:block;text-align:center;padding:9px 4px;border:1px solid #d7dde5;border-radius:8px;
  font-size:13px;font-weight:600;cursor:pointer;color:var(--text);
}
.roles input:checked + span{background:var(--navy);color:#fff;border-color:var(--navy);}
.hint{font-size:12px;color:var(--muted);margin-bottom:18px;text-align:center;}
.form-group{margin-bottom:16px;}
.form-group label{display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:6px;}
.form-group input{width:100%;padding:11px 14px;border:1px solid #d7dde5;border-radius:8px;font-size:14px;}
.form-group input:focus{outline:none;border-color:var(--teal);}
.btn-login{
  width:100%;padding:12px;background:var(--accent);color:var(--navy);border:none;
  border-radius:8px;font-weight:700;font-size:15px;cursor:pointer;margin-top:6px;
}
.btn-login:hover{opacity:.9;}
.error-box{background:#fdecea;color:#b3261e;border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:18px;text-align:center;}
.back-link{display:block;text-align:center;margin-top:22px;font-size:13px;color:var(--muted);text-decoration:none;}
.back-link:hover{color:var(--navy);}
</style>
</head>
<body>

<div class="login-card">
  <div class="logo">🎓</div>
  <h1>Masuk ke SIMATIK</h1>
  <div class="sub">Portal Akademik Universitas Semantik</div>

  <?php if ($error): ?>
    <div class="error-box"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="roles">
      <label><input type="radio" name="role" value="mahasiswa" <?= $role === 'mahasiswa' ? 'checked' : '' ?>><span>Mahasiswa</span></label>
      <label><input type="radio" name="role" value="prodi" <?= $role === 'prodi' ? 'checked' : '' ?>><span>Prodi</span></label>
      <label><input type="radio" name="role" value="admin" <?= $role === 'admin' ? 'checked' : '' ?>><span>Admin</span></label>
    </div>
    <div class="hint">Mahasiswa masuk dengan NPM. Prodi &amp; Admin dengan username.</div>

    <div class="form-group">
      <label>Username / NPM</label>
      <input type="text" name="username" required autofocus value="<?= htmlspecialchars($username) ?>">
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <button type="submit" class="btn-login">Masuk</button>
  </form>

  <a href="index.php" class="back-link">&larr; Kembali ke Beranda</a>
</div>

</body>
</html>
