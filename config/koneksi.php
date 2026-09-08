<?php
// Konfigurasi Database
$host     = "localhost";
$user     = "root";     // Sesuaikan dengan username database Anda
$password = "";         // Sesuaikan dengan password database Anda
$database = "db_monitoring_hafalan";

// Membuat Koneksi
$koneksi = mysqli_connect($host, $user, $password, $database);

// Cek Koneksi
if (!$koneksi) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}

// Set Charset UTF-8
mysqli_set_charset($koneksi, "utf8mb4");
?>