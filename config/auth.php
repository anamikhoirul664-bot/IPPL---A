<?php
require_once __DIR__ . '/session.php';

/**
 * Memastikan pengguna sudah login. Jika belum, lempar ke halaman login.
 */
function checkAuth() {
    if (!isLoggedIn()) {
        header("Location: ../../login.php");
        exit();
    }
}

/**
 * Memastikan pengguna memiliki role tertentu untuk mengakses halaman.
 * Jika role tidak sesuai, pengguna akan diarahkan ke dashboard role-nya sendiri.
 * 
 * @param array|string $allowed_roles Role yang diperbolehkan (misal: 'santri' atau ['ustad', 'pengasuh'])
 */
function checkRole($allowed_roles) {
    // Pastikan pengguna sudah login terlebih dahulu
    checkAuth();

    $current_role = getUserRole();

    // Jika parameter berupa string tunggal, ubah ke array
    if (!is_array($allowed_roles)) {
        $allowed_roles = array($allowed_roles);
    }

    // Cek apakah role user saat ini ada dalam daftar role yang diizinkan
    if (!in_array($current_role, $allowed_roles)) {
        // Jika tidak berhak, alihkan ke dashboard miliknya sendiri
        header("Location: ../../dashboard/" . $current_role . "/index.php");
        exit();
    }
}

/**
 * Memastikan pengguna belum login.
 * Digunakan di halaman login/register agar user yang sudah login tidak bisa membuka form login lagi.
 */
function checkGuest() {
    if (isLoggedIn()) {
        $role = getUserRole();
        header("Location: dashboard/" . $role . "/index.php");
        exit();
    }
}
?>