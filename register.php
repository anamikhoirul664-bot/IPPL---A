<?php
session_start();
require_once 'config/koneksi.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $role     = trim($_POST['role']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // Validasi input
    if (empty($nama) || empty($username) || empty($email) || empty($role) || empty($password)) {
        $error = "Semua kolom wajib diisi.";
    } elseif ($password !== $confirm) {
        $error = "Konfirmasi password tidak cocok.";
    } else {
        // Cek ketersediaan username / email
        $check_stmt = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($check_stmt, "ss", $username, $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $error = "Username atau Email sudah terdaftar.";
        } else {
            // Hash Password demi Keamanan
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Insert User Baru
            $insert_stmt = mysqli_prepare($koneksi, "INSERT INTO users (nama, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($insert_stmt, "sssss", $nama, $username, $email, $hashed_password, $role);

            if (mysqli_stmt_execute($insert_stmt)) {

                // Ambil ID user yang baru saja dibuat
                $user_id = mysqli_insert_id($koneksi);

                // 1. Jika Role SANTRI -> Insert ke tabel santri
                if ($role == 'santri') {
                    $nis = 'NIS' . $user_id;
                    $santri_stmt = mysqli_prepare($koneksi, "INSERT INTO santri (user_id, nis) VALUES (?, ?)");
                    mysqli_stmt_bind_param($santri_stmt, "is", $user_id, $nis);

                    if (!mysqli_stmt_execute($santri_stmt)) {
                        $error = "Akun berhasil dibuat, tetapi data profil santri gagal dibuat.";
                    }
                    mysqli_stmt_close($santri_stmt);
                } 
                // 2. Jika Role WALI -> Insert ke tabel wali_santri
                elseif ($role == 'wali') {
                    $wali_stmt = mysqli_prepare($koneksi, "INSERT INTO wali_santri (user_id) VALUES (?)");
                    mysqli_stmt_bind_param($wali_stmt, "i", $user_id);

                    if (!mysqli_stmt_execute($wali_stmt)) {
                        $error = "Akun berhasil dibuat, tetapi data profil wali gagal dibuat.";
                    }
                    mysqli_stmt_close($wali_stmt);
                } 
                // 3. Jika Role PENGASUH -> Insert ke tabel pengasuh
                elseif ($role == 'pengasuh') {
                    $pengasuh_stmt = mysqli_prepare($koneksi, "INSERT INTO pengasuh (user_id) VALUES (?)");
                    mysqli_stmt_bind_param($pengasuh_stmt, "i", $user_id);

                    if (!mysqli_stmt_execute($pengasuh_stmt)) {
                        $error = "Akun berhasil dibuat, tetapi data profil pengasuh gagal dibuat.";
                    }
                    mysqli_stmt_close($pengasuh_stmt);
                } 
                // 4. Jika Role USTADZ -> Insert ke tabel ustadz
                elseif ($role == 'ustad') {
                    $ustadz_stmt = mysqli_prepare($koneksi, "INSERT INTO ustadz (user_id) VALUES (?)");
                    mysqli_stmt_bind_param($ustadz_stmt, "i", $user_id);

                    if (!mysqli_stmt_execute($ustadz_stmt)) {
                        $error = "Akun berhasil dibuat, tetapi data profil ustadz gagal dibuat.";
                    }
                    mysqli_stmt_close($ustadz_stmt);
                }

                if (empty($error)) {
                    $success = "Pendaftaran berhasil! Silakan login dengan akun Anda.";
                }
            } else {
                $error = "Gagal mendaftar. Silakan coba beberapa saat lagi.";
            }
            mysqli_stmt_close($insert_stmt);
        }
        mysqli_stmt_close($check_stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - E-Hafalan</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts & FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center relative overflow-hidden p-4">

    <!-- Background Orbs -->
    <div class="absolute -top-20 -right-20 w-96 h-96 bg-emerald-300/30 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-96 h-96 bg-teal-300/30 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-lg relative z-10 py-8">

        <!-- Header Logo -->
        <div class="text-center mb-6">
            <a href="index.php" class="inline-flex items-center space-x-3 group">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-emerald-500/30 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-quran"></i>
                </div>
                <span class="text-2xl font-bold bg-gradient-to-r from-emerald-700 to-teal-600 bg-clip-text text-transparent">E-Hafalan</span>
            </a>
            <h2 class="text-2xl font-bold text-slate-800 mt-4">Buat Akun Baru</h2>
            <p class="text-sm text-slate-500">Bergabung dalam ekosistem digital monitoring hafalan</p>
        </div>

        <!-- Card Form Register -->
        <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-2xl shadow-emerald-900/10">

            <?php if (!empty($error)): ?>
                <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-600 text-sm flex items-center space-x-3">
                    <i class="fa-solid fa-circle-exclamation text-lg"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                    <div>
                        <span><?php echo htmlspecialchars($success); ?></span>
                        <a href="login.php" class="block font-bold underline mt-1">Klik untuk Login</a>
                    </div>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="space-y-4">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                    <input type="text" name="nama" required placeholder="Ahmad Hanif" class="w-full px-4 py-2.5 bg-white/80 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Username</label>
                        <input type="text" name="username" required placeholder="ahmad_hanif" class="w-full px-4 py-2.5 bg-white/80 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Email</label>
                        <input type="email" name="email" required placeholder="hanif@example.com" class="w-full px-4 py-2.5 bg-white/80 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Daftar Sebagai (Role)</label>
                    <select name="role" required class="w-full px-4 py-2.5 bg-white/80 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all">
                        <option value="" disabled selected>Pilih Role Pengguna</option>
                        <option value="santri">Santri</option>
                        <option value="ustad">Ustadz / Penguji</option>
                        <option value="wali">Wali Santri</option>
                        <option value="pengasuh">Pengasuh / Pimpinan</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Password</label>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2.5 bg-white/80 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Password</label>
                        <input type="password" name="confirm_password" required placeholder="••••••••" class="w-full px-4 py-2.5 bg-white/80 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all">
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 mt-2 text-sm font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl shadow-lg shadow-emerald-600/25 transition-all duration-300 hover:-translate-y-0.5">
                    <i class="fa-solid fa-user-plus mr-2"></i> Daftar Sekarang
                </button>
            </form>

            <div class="mt-6 text-center text-xs text-slate-500">
                Sudah punya akun?
                <a href="login.php" class="text-emerald-600 font-bold hover:underline">Masuk Ke Aplikasi</a>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-slate-400">
            <a href="index.php" class="hover:text-emerald-600 transition-colors"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Beranda</a>
        </div>
    </div>

</body>

</html>