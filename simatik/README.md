# SIMATIK — Sistem Informasi Mahasiswa & Akademik (Universitas Semantik)

PHP native + MySQL (mysqli), CSS native. Tiga peran: **Admin**, **Prodi**, **Mahasiswa**.

## Hak akses per peran
| Peran | Halaman | Boleh | Tidak boleh |
|---|---|---|---|
| **Mahasiswa** | `dashboard_mahasiswa.php` | Ubah jenis kelamin, tempat lahir, tanggal lahir, alamat, password | Ubah nama, NPM, prodi; melihat mahasiswa lain |
| **Prodi** | `dashboard_prodi.php` | Melihat mahasiswa **prodinya saja**; mengubah prodi mahasiswa | Melihat mahasiswa prodi lain; mengubah data selain prodi |
| **Admin** | `dashboard.php`, `fakultas.php`, `prodi.php`, `mahasiswa.php` | Kelola fakultas, prodi, dan data mahasiswa (termasuk mengubah nama) | — |

Semua pembatasan dicek di **server** (bukan hanya disembunyikan di tampilan).

## Struktur file
```
koneksi.php            koneksi database (isi kredensial hosting)
auth.php               fungsi sesi, peran, CSRF, flash message
login.php / logout.php login 3 peran / keluar
index.php              landing page publik
dashboard.php          dashboard admin
fakultas.php prodi.php mahasiswa.php     CRUD admin
dashboard_mahasiswa.php  dashboard mahasiswa
dashboard_prodi.php      dashboard prodi
partials/nav.php       menu admin
partials/nav_user.php  menu mahasiswa & prodi
assets/style.css
database.sql           instalasi BARU (menghapus & membuat ulang tabel)
migrasi_role.sql       untuk database yang SUDAH berisi data
```

## Akun contoh
| Peran | Login | Password |
|---|---|---|
| Admin | `admin` | `admin123` |
| Prodi | `prodi_ti`, `prodi_sip`, `prodi_mjm` | `prodi123` |
| Mahasiswa | NPM `2355201001` s/d `2355201005` | `mahasiswa123` |

**Ganti semua password ini sebelum dipakai sungguhan.**

## Pasang di InfinityFree
1. Upload isi folder `simatik/` langsung ke `htdocs/` (pastikan `index.php` ada di `htdocs/`, bukan di subfolder).
2. Edit `koneksi.php` sesuai data MySQL dari vPanel.
3. phpMyAdmin → pilih database `if0_xxxxxxx_universitassemantik` di panel kiri, lalu tab **Import**:
   - Database **kosong / baru** → import `database.sql`.
   - Database **sudah berisi data** → import `migrasi_role.sql` saja (data tidak dihapus).
   Jangan pakai SQL yang berisi `CREATE DATABASE` atau `USE` di InfinityFree.
4. Buka situs, masuk lewat `login.php`.

## Menambah program studi baru
Akun login prodi **tidak dibuat otomatis** saat admin menambah prodi di `prodi.php`. Setelah menambah prodi, jalankan di phpMyAdmin (aman diulang):
```sql
INSERT IGNORE INTO akun_prodi (username, password, kode_prodi)
SELECT CONCAT('prodi_', LOWER(kode_prodi)),
       (SELECT password FROM akun_prodi WHERE username = 'prodi_ti'),
       kode_prodi
FROM program_studi;
```
Akun baru berbentuk `prodi_<kode>` dengan password yang sama seperti `prodi_ti`.

## Jika halaman kosong / error
`koneksi.php` menampilkan pesan error yang jelas (tabel belum ada, password salah, dll). Setelah situs normal, hapus baris `ini_set('display_errors', 1);`.
