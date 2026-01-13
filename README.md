# tiket-kereta

Aplikasi web tiket kereta berbasis PHP native dengan SQLite yang menampilkan jadwal perjalanan harian dan mendukung login, pemesanan menggunakan kredit, serta pengelolaan profil.

## Menjalankan aplikasi
1. Pastikan PHP 8+ terpasang.
2. Dari root proyek jalankan server bawaan PHP:
   ```bash
   php -S 0.0.0.0:8000
   ```
3. Buka `http://localhost:8000` di peramban.

Database SQLite akan otomatis dibuat di `storage/app.sqlite` beserta data:
- Pengguna demo: `demo` / `demo123` dengan kredit awal Rp 5.000.000
- Jadwal kereta 7 hari ke depan untuk seluruh daftar stasiun Indonesia yang tersedia di aplikasi.

## Fitur utama
- **Pencarian tiket** berdasarkan asal, tujuan, tanggal, operator kereta, dan harga maksimum.
- **Login & Registrasi** dengan hash kata sandi dan kredit awal Rp 5.000.000 per pengguna baru.
- **Pemesanan** membutuhkan login dan memotong kredit sesuai harga tiket dengan halaman konfirmasi detail tiket terlebih dahulu.
- **Deposit saldo** dengan form metode pembayaran dummy; saldo langsung bertambah tanpa proses pembayaran nyata.
- **Profil** untuk mengubah nama, username, dan kata sandi.
- **Dashboard** menampilkan saldo kredit dan riwayat pemesanan.
- **Generator harian** memastikan jadwal 7 hari ke depan tersedia; jika aplikasi baru dijalankan setelah sehari terlewat, jadwal diperbarui otomatis.

## Struktur berkas
- `bootstrap.php` – inisialisasi sesi, database, dan generator jadwal.
- `config/` – konfigurasi koneksi database.
- `src/` – helper dan logika generator jadwal kereta.
- `templates/` – template header/footer yang digunakan halaman utama.
- `storage/` – lokasi file database SQLite (dibuat otomatis).
- Halaman utama: `index.php`, `list_tiket.php`, `login.php`, `register.php`, `dashboard.php`, `profile.php`, `deposit.php`, `ticket_detail.php`, `purchase.php`, `logout.php`.

## Catatan
- Harga tiket dihasilkan otomatis antara Rp 60.000 hingga Rp 800.000 dengan variasi per operator kereta.
- Data stasiun dan operator kereta berasal dari katalog statis dalam kode sehingga aplikasi dapat berjalan offline.
