# pemesanan-visa-online

Aplikasi web pemesanan visa online berbasis PHP native dengan SQLite yang menampilkan paket visa multi-negara, mendukung login, pengajuan menggunakan kredit, serta pengelolaan profil.

## Menjalankan aplikasi
1. Pastikan PHP 8+ terpasang.
2. Dari root proyek jalankan server bawaan PHP:
   ```bash
   php -S 0.0.0.0:8000
   ```
3. Buka `http://localhost:8000` di peramban.

Database SQLite akan otomatis dibuat di `storage/app.sqlite` beserta data:
- Pengguna demo: `demo` / `demo123` dengan kredit awal Rp 5.000.000
- Paket visa untuk 7 hari ke depan untuk seluruh daftar negara yang tersedia di aplikasi.

## Fitur utama
- **Pencarian visa** berdasarkan kewarganegaraan, negara tujuan, tanggal rencana berangkat, jenis visa, dan biaya maksimum.
- **Login & Registrasi** dengan hash kata sandi dan kredit awal Rp 5.000.000 per pengguna baru.
- **Pengajuan visa** membutuhkan login dan memotong kredit sesuai biaya layanan dengan halaman konfirmasi detail visa terlebih dahulu.
- **Deposit saldo** dengan form metode pembayaran dummy; saldo langsung bertambah tanpa proses pembayaran nyata.
- **Profil** untuk mengubah nama, username, dan kata sandi.
- **Dashboard** menampilkan saldo kredit dan riwayat pengajuan visa.
- **Generator harian** memastikan paket visa 7 hari ke depan tersedia; jika aplikasi baru dijalankan setelah sehari terlewat, paket diperbarui otomatis.

## Struktur berkas
- `bootstrap.php` – inisialisasi sesi, database, dan generator paket visa.
- `config/` – konfigurasi koneksi database.
- `src/` – helper dan logika generator visa.
- `templates/` – template header/footer yang digunakan halaman utama.
- `storage/` – lokasi file database SQLite (dibuat otomatis).
- Halaman utama: `index.php`, `list_tiket.php`, `login.php`, `register.php`, `dashboard.php`, `profile.php`, `deposit.php`, `ticket_detail.php`, `purchase.php`, `logout.php`.

## Catatan
- Biaya layanan visa dihasilkan otomatis antara Rp 350.000 hingga Rp 6.000.000 dengan variasi per jenis visa.
- Data negara dan jenis visa berasal dari katalog statis dalam kode sehingga aplikasi dapat berjalan offline.
