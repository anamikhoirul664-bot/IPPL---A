<?php
// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    // Pengaturan Keamanan Session Cookie
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    
    session_start();
}

/**
 * Cek apakah pengguna sudah login
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Dapatkan role pengguna yang sedang login
 * @return string|null
 */
function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

/**
 * Dapatkan ID pengguna yang sedang login
 * @return int|null
 */
function getUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Dapatkan nama pengguna yang sedang login
 * @return string
 */
function getUserNama() {
    return isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Pengguna';
}
?>