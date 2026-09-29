<?php
require_once 'config/koneksi.php';

// Data Ustadz yang ingin ditambahkan
$nama     = 'Ustadz Ahmad, S.Pd.I';
$username = 'ustadz_ahmad';
$email    = 'ustadz.ahmad@gmail.com';
$role     = 'ustad'; // Sesuaikan jika di DB Anda memakai 'ustadz' atau 'admin'
$pass_raw = 'Ustadz123'; // Password polos yang akan digunakan untuk login

// Generate Hash BCRYPT resmi dari PHP server Anda
$hashed_password = password_hash($pass_raw, PASSWORD_BCRYPT);

// Cek apakah username/email sudah ada
$check = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username' OR email = '$email'");

if (mysqli_num_rows($check) > 0) {
    // Jika user sudah ada, perbarui (UPDATE) passwordnya
    $sql = "UPDATE users SET password = '$hashed_password' WHERE username = '$username' OR email = '$email'";
    if (mysqli_query($koneksi, $sql)) {
        echo "<h2 style='color:green;'>BERHASIL! Password akun Ustadz telah diperbarui.</h2>";
        echo "<p>Silakan login dengan:</p>";
        echo "<b>Username / Email:</b> $email <br>";
        echo "<b>Password:</b> $pass_raw";
    } else {
        echo "<h2 style='color:red;'>Gagal update: " . mysqli_error($koneksi) . "</h2>";
    }
} else {
    // Jika belum ada, buat (INSERT) user baru
    $sql = "INSERT INTO users (nama, username, email, password, role) VALUES ('$nama', '$username', '$email', '$hashed_password', '$role')";
    if (mysqli_query($koneksi, $sql)) {
        echo "<h2 style='color:green;'>BERHASIL! Akun Ustadz baru telah dibuat.</h2>";
        echo "<p>Silakan login dengan:</p>";
        echo "<b>Username / Email:</b> $email <br>";
        echo "<b>Password:</b> $pass_raw";
    } else {
        echo "<h2 style='color:red;'>Gagal insert: " . mysqli_error($koneksi) . "</h2>";
    }
}
?>