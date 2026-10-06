<?php
require_once 'config/koneksi.php';

// Data Pengasuh yang ingin ditambahkan
$nama     = 'KH. Abdullah Maksum';
$username = 'pengasuh_utama';
$email    = 'pengasuh@gmail.com';
$role     = 'pengasuh'; // Role khusus untuk pengasuh
$pass_raw = 'Pengasuh123'; // Password untuk login

// Generate Hash BCRYPT resmi dari PHP server Anda
$hashed_password = password_hash($pass_raw, PASSWORD_BCRYPT);

// Cek apakah username/email sudah ada
$check = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username' OR email = '$email'");

if (mysqli_num_rows($check) > 0) {
    // Jika akun sudah ada, perbarui (UPDATE) password & rolenya
    $sql = "UPDATE users SET password = '$hashed_password', role = '$role' WHERE username = '$username' OR email = '$email'";
    if (mysqli_query($koneksi, $sql)) {
        echo "<h2 style='color:green;'>BERHASIL! Password & Role akun Pengasuh telah diperbarui.</h2>";
        echo "<p>Silakan login dengan:</p>";
        echo "<b>Username / Email:</b> $email <br>";
        echo "<b>Password:</b> $pass_raw";
    } else {
        echo "<h2 style='color:red;'>Gagal update: " . mysqli_error($koneksi) . "</h2>";
    }
} else {
    // Jika belum ada, buat (INSERT) user pengasuh baru
    $sql = "INSERT INTO users (nama, username, email, password, role) VALUES ('$nama', '$username', '$email', '$hashed_password', '$role')";
    if (mysqli_query($koneksi, $sql)) {
        echo "<h2 style='color:green;'>BERHASIL! Akun Pengasuh baru telah dibuat.</h2>";
        echo "<p>Silakan login dengan:</p>";
        echo "<b>Username / Email:</b> $email <br>";
        echo "<b>Password:</b> $pass_raw";
    } else {
        echo "<h2 style='color:red;'>Gagal insert: " . mysqli_error($koneksi) . "</h2>";
    }
}
?>