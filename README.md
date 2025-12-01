# tiket-pesawat

Aplikasi web tiket pesawat berbasis PHP native dengan SQLite yang menampilkan jadwal penerbangan harian dan mendukung login, pemesanan menggunakan kredit, serta pengelolaan profil.

## Menjalankan aplikasi
1. Pastikan PHP 8+ terpasang.
2. Dari root proyek jalankan server bawaan PHP:
   ```bash
   php -S 0.0.0.0:8000
   ```
3. Buka `http://localhost:8000` di peramban.

Database SQLite akan otomatis dibuat di `storage/app.sqlite` beserta data:
- Pengguna demo: `demo` / `demo123` dengan kredit awal Rp 5.000.000
- Jadwal penerbangan 7 hari ke depan untuk seluruh daftar bandara Indonesia yang tersedia di aplikasi.

## Fitur utama
- **Pencarian tiket** berdasarkan asal, tujuan, tanggal, maskapai, dan harga maksimum.
- **Login & Registrasi** dengan hash kata sandi dan kredit awal Rp 5.000.000 per pengguna baru.
- **Pemesanan** membutuhkan login dan memotong kredit sesuai harga tiket.
- **Top up saldo** dengan form metode pembayaran dummy; saldo langsung bertambah tanpa proses pembayaran nyata.
- **Profil** untuk mengubah nama, username, dan kata sandi.
- **Dashboard** menampilkan saldo kredit dan riwayat pemesanan.
- **Generator harian** memastikan jadwal 7 hari ke depan tersedia; jika aplikasi baru dijalankan setelah sehari terlewat, jadwal diperbarui otomatis.

## Struktur berkas
- `bootstrap.php` – inisialisasi sesi, database, dan generator jadwal.
- `config/` – konfigurasi koneksi database.
- `src/` – helper dan logika generator penerbangan.
- `templates/` – template header/footer yang digunakan halaman utama.
- `storage/` – lokasi file database SQLite (dibuat otomatis).
- Halaman utama: `index.php`, `list_tiket.php`, `login.php`, `register.php`, `dashboard.php`, `profile.php`, `topup.php`, `purchase.php`, `logout.php`.

## Catatan
- Harga tiket dihasilkan otomatis antara Rp 600.000 hingga Rp 4.000.000 dengan variasi per maskapai (mis. Garuda Indonesia lebih mahal).
- Data bandara dan maskapai berasal dari katalog statis dalam kode sehingga aplikasi dapat berjalan offline.
