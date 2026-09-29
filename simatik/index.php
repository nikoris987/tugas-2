<?php
require_once "koneksi.php";

$jml_fakultas = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM fakultas"))['jml'];
$jml_prodi    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM program_studi"))['jml'];
$jml_mhs      = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM mahasiswa"))['jml'];

// Ambil fakultas beserta program studi di dalamnya
$raw = mysqli_query($koneksi, "
    SELECT f.kode_fakultas, f.nama_fakultas, ps.kode_prodi, ps.nama_prodi
    FROM fakultas f
    LEFT JOIN program_studi ps ON f.kode_fakultas = ps.kode_fakultas
    ORDER BY f.nama_fakultas, ps.nama_prodi
");

$fakultas = [];
while ($row = mysqli_fetch_assoc($raw)) {
    $kf = $row['kode_fakultas'];
    if (!isset($fakultas[$kf])) {
        $fakultas[$kf] = ['nama' => $row['nama_fakultas'], 'prodi' => []];
    }
    if ($row['kode_prodi']) {
        $fakultas[$kf]['prodi'][] = $row['nama_prodi'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Universitas Semantik</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --navy:#0f1f38;
  --navy-light:#16324f;
  --accent:#f5a524;
  --teal:#2dd4bf;
  --bg:#f7f9fc;
  --text:#1f2937;
  --muted:#6b7280;
}
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Inter',sans-serif;color:var(--text);background:var(--bg);line-height:1.6;}
h1,h2,h3{font-family:'Poppins',sans-serif;}
a{text-decoration:none;}
.container{max-width:1120px;margin:0 auto;padding:0 24px;}

/* NAVBAR */
.nav{
  position:sticky;top:0;z-index:50;
  background:var(--navy);
  padding:16px 0;
}
.nav .container{display:flex;align-items:center;justify-content:space-between;}
.nav .logo{color:#fff;font-family:'Poppins',sans-serif;font-weight:700;font-size:20px;display:flex;align-items:center;gap:8px;}
.nav ul{list-style:none;display:flex;gap:28px;}
.nav a{color:#dbe4f0;font-size:14px;font-weight:500;}
.nav a:hover{color:var(--accent);}
.nav .cta{background:var(--accent);color:var(--navy);padding:9px 18px;border-radius:6px;font-weight:600;}
.nav .cta:hover{opacity:.9;}

/* HERO */
.hero{
  background:linear-gradient(135deg,var(--navy) 0%,#173154 55%,#134e4a 100%);
  color:#fff;
  padding:100px 0 140px;
  text-align:center;
  position:relative;
}
.hero h1{font-size:44px;font-weight:800;margin-bottom:18px;}
.hero h1 span{color:var(--accent);}
.hero p{font-size:17px;color:#c9d4e2;max-width:600px;margin:0 auto 32px;}
.hero-buttons{display:flex;gap:16px;justify-content:center;flex-wrap:wrap;}
.btn-primary{background:var(--accent);color:var(--navy);padding:14px 28px;border-radius:8px;font-weight:700;}
.btn-primary:hover{opacity:.9;}
.btn-outline{border:1.5px solid rgba(255,255,255,.5);color:#fff;padding:14px 28px;border-radius:8px;font-weight:600;}
.btn-outline:hover{background:rgba(255,255,255,.1);}

/* STATS */
.stats{
  max-width:1000px;margin:-70px auto 60px;padding:0 24px;
  display:grid;grid-template-columns:repeat(3,1fr);gap:20px;
}
.stat-card{
  background:#fff;border-radius:14px;padding:28px 20px;text-align:center;
  box-shadow:0 12px 30px rgba(15,31,56,.12);
}
.stat-card .icon{font-size:30px;margin-bottom:8px;}
.stat-card .num{font-size:32px;font-weight:800;color:var(--navy);font-family:'Poppins',sans-serif;}
.stat-card .label{color:var(--muted);font-size:14px;margin-top:4px;}

/* SECTION HEADING */
.section{padding:70px 0;}
.section-head{text-align:center;max-width:600px;margin:0 auto 48px;}
.section-head .eyebrow{color:var(--teal);font-weight:700;font-size:13px;letter-spacing:1.5px;text-transform:uppercase;}
.section-head h2{font-size:32px;margin-top:8px;color:var(--navy);}
.section-head p{color:var(--muted);margin-top:10px;font-size:15px;}

/* FAKULTAS / PRODI */
.fakultas-block{margin-bottom:40px;}
.fakultas-title{display:flex;align-items:center;gap:10px;font-size:20px;color:var(--navy-light);margin-bottom:18px;font-family:'Poppins',sans-serif;font-weight:700;}
.fakultas-title .dot{width:10px;height:10px;border-radius:50%;background:var(--accent);}
.prodi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;}
.prodi-card{
  background:#fff;border-radius:12px;padding:22px;border:1px solid #eef1f6;
  box-shadow:0 4px 14px rgba(15,31,56,.05);transition:transform .15s, box-shadow .15s;
}
.prodi-card:hover{transform:translateY(-4px);box-shadow:0 10px 24px rgba(15,31,56,.12);}
.prodi-card .icon{font-size:22px;margin-bottom:10px;}
.prodi-card h4{font-size:16px;color:var(--navy);}

/* WHY */
.why-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:24px;}
.why-card{background:#fff;border-radius:14px;padding:28px 22px;box-shadow:0 4px 14px rgba(15,31,56,.05);}
.why-card .icon{font-size:26px;margin-bottom:12px;}
.why-card h4{font-size:16px;margin-bottom:8px;color:var(--navy);}
.why-card p{font-size:14px;color:var(--muted);}

/* CTA */
.cta-band{
  background:linear-gradient(120deg,var(--navy-light),var(--navy));
  color:#fff;text-align:center;padding:64px 24px;border-radius:20px;
  margin:0 24px;
}
.cta-band h2{font-size:28px;margin-bottom:12px;}
.cta-band p{color:#c9d4e2;margin-bottom:26px;}

/* FOOTER */
footer{background:var(--navy);color:#a9b6c9;padding:40px 0 24px;margin-top:70px;}
footer .container{display:flex;justify-content:space-between;flex-wrap:wrap;gap:20px;}
footer .brand{color:#fff;font-family:'Poppins',sans-serif;font-weight:700;font-size:18px;margin-bottom:6px;}
footer small{display:block;text-align:center;margin-top:24px;color:#6b7c93;}

@media(max-width:720px){
  .stats{grid-template-columns:1fr;}
  .nav ul{display:none;}
  .hero h1{font-size:32px;}
}
</style>
</head>
<body>

<nav class="nav">
  <div class="container">
    <div class="logo">🎓 Universitas Semantik</div>
    <ul>
      <li><a href="#beranda">Beranda</a></li>
      <li><a href="#prodi">Program Studi</a></li>
      <li><a href="#kenapa">Kenapa Kami</a></li>
    </ul>
    <a href="login.php" class="cta">Portal Akademik</a>
  </div>
</nav>

<header class="hero" id="beranda">
  <div class="container">
    <h1>Membangun Generasi <span>Unggul</span><br>Universitas Semantik</h1>
    <p>Kampus yang berkomitmen mencetak lulusan kompeten, siap kerja, dan siap berkontribusi bagi masyarakat melalui pendidikan berkualitas.</p>
    <div class="hero-buttons">
      <a href="#prodi" class="btn-primary">Lihat Program Studi</a>
      <a href="login.php" class="btn-outline">Portal Akademik</a>
    </div>
  </div>
</header>

<div class="stats">
  <div class="stat-card">
    <div class="icon">🏛️</div>
    <div class="num"><?= $jml_fakultas ?></div>
    <div class="label">Fakultas</div>
  </div>
  <div class="stat-card">
    <div class="icon">📚</div>
    <div class="num"><?= $jml_prodi ?></div>
    <div class="label">Program Studi</div>
  </div>
  <div class="stat-card">
    <div class="icon">👥</div>
    <div class="num"><?= $jml_mhs ?></div>
    <div class="label">Mahasiswa Aktif</div>
  </div>
</div>

<section class="section" id="prodi">
  <div class="container">
    <div class="section-head">
      <div class="eyebrow">Akademik</div>
      <h2>Program Studi Kami</h2>
      <p>Pilihan program studi di setiap fakultas, dirancang mengikuti kebutuhan dunia kerja.</p>
    </div>

    <?php foreach ($fakultas as $fk): ?>
    <div class="fakultas-block">
      <div class="fakultas-title"><span class="dot"></span> Fakultas <?= htmlspecialchars($fk['nama']) ?></div>
      <div class="prodi-grid">
        <?php foreach ($fk['prodi'] as $nama_prodi): ?>
        <div class="prodi-card">
          <div class="icon">📖</div>
          <h4><?= htmlspecialchars($nama_prodi) ?></h4>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="section" id="kenapa" style="background:#fff;">
  <div class="container">
    <div class="section-head">
      <div class="eyebrow">Keunggulan</div>
      <h2>Kenapa Memilih Kami</h2>
    </div>
    <div class="why-grid">
      <div class="why-card">
        <div class="icon">🎯</div>
        <h4>Kurikulum Relevan</h4>
        <p>Disusun mengikuti perkembangan industri agar lulusan siap kerja.</p>
      </div>
      <div class="why-card">
        <div class="icon">👨‍🏫</div>
        <h4>Dosen Berpengalaman</h4>
        <p>Diampu oleh pengajar yang kompeten di bidangnya masing-masing.</p>
      </div>
      <div class="why-card">
        <div class="icon">🏢</div>
        <h4>Fasilitas Memadai</h4>
        <p>Lingkungan belajar yang nyaman dan mendukung aktivitas akademik.</p>
      </div>
      <div class="why-card">
        <div class="icon">🤝</div>
        <h4>Jaringan Luas</h4>
        <p>Kerja sama dengan berbagai instansi untuk peluang magang dan karier.</p>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="cta-band">
    <h2>Siap Menjadi Bagian dari Kami?</h2>
    <p>Kelola dan pantau data akademik lewat portal SIMATIK.</p>
    <a href="login.php" class="btn-primary">Masuk ke Portal Akademik</a>
  </div>
</section>

<footer>
  <div class="container">
    <div>
      <div class="brand">🎓 Universitas Semantik</div>
      <p>Sistem Informasi Mahasiswa &amp; Akademik (SIMATIK)</p>
    </div>
    <div>
      <div class="brand">Kontak</div>
      <p>Bengkulu, Indonesia</p>
    </div>
  </div>
  <small>&copy; <?= date("Y") ?> Universitas Semantik. All rights reserved.</small>
</footer>

</body>
</html>
