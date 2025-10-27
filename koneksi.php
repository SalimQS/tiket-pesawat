<?php
$host = "localhost";
$user = "root"; // Ganti dengan user database Anda
$pass = ""; // Ganti dengan password database Anda
$db = "tiket_pesawat_db";   // Ganti dengan nama database Anda

$koneksi = new mysqli($host, $user, $pass, $db);

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
// echo "Koneksi berhasil!"; // Bisa dihapus setelah dipastikan berhasil
?>