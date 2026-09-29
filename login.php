<?php
session_start();
require_once 'config/koneksi.php';

// Jika pengguna sudah login, langsung alihkan ke dashboard rolenya masing-masing
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    header("Location: dasboard/" . $_SESSION['role'] . "/dasboard.php");
    exit();
}

// Generate CSRF Token untuk keamanan form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$username_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF Token
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = "Sesi tidak valid. Silakan muat ulang halaman dan coba lagi.";
    } else {
        $username_input = trim($_POST['username'] ?? '');
        $password       = trim($_POST['password'] ?? '');

        if (!empty($username_input) && !empty($password)) {
            // Cari akun berdasarkan Username ATAU Email
            $stmt = mysqli_prepare($koneksi, "SELECT id, nama, username, password, role FROM users WHERE username = ? OR email = ?");
            mysqli_stmt_bind_param($stmt, "ss", $username_input, $username_input);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($user = mysqli_fetch_assoc($result)) {
                if (password_verify($password, $user['password'])) {
                    // Regenerate Session ID untuk mencegah Session Fixation Attack
                    session_regenerate_id(true);

                    // Set Session Pengguna
                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['nama']     = $user['nama'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role']     = strtolower($user['role']); // santri, ustad, pengasuh, wali

                    mysqli_stmt_close($stmt);

                    // Redireksi langsung ke Dashboard sesuai role
                    header("Location: dasboard/" . $_SESSION['role'] . "/dasboard.php");
                    exit();
                } else {
                    $error = "Password yang Anda masukkan salah.";
                }
            } else {
                $error = "Username atau Email tidak ditemukan.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Silakan isi semua kolom login.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - E-Hafalan</title>
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
    <div class="absolute -top-20 -left-20 w-80 h-80 md:w-96 md:h-96 bg-emerald-300/30 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -right-20 w-80 h-80 md:w-96 md:h-96 bg-teal-300/30 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">

        <!-- Header Logo -->
        <div class="text-center mb-8">
            <a href="index.php" class="inline-flex items-center space-x-3 group">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-emerald-500/30 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-quran"></i>
                </div>
                <span class="text-2xl font-bold bg-gradient-to-r from-emerald-700 to-teal-600 bg-clip-text text-transparent">E-Hafalan</span>
            </a>
            <h2 class="text-2xl font-bold text-slate-800 mt-4">Selamat Datang Kembali</h2>
            <p class="text-sm text-slate-500">Masuk ke portal monitoring hafalan santri</p>
        </div>

        <!-- Card Form Login -->
        <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-2xl shadow-emerald-900/10">

            <?php if (!empty($error)): ?>
                <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-600 text-sm flex items-center space-x-3">
                    <i class="fa-solid fa-circle-exclamation text-lg shrink-0"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-5" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <!-- Field Username / Email -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Username / Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fa-solid fa-user"></i>
                        </span>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($username_input); ?>" required placeholder="Masukkan username atau email" class="w-full pl-11 pr-4 py-3 bg-white/80 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all">
                    </div>
                </div>

                <!-- Field Password -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password" required placeholder="••••••••" class="w-full pl-11 pr-10 py-3 bg-white/80 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 focus:outline-none">
                            <i id="toggleEye" class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember & Forgot Password -->
                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center space-x-2 text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span>Ingat saya</span>
                    </label>
                    <a href="lupa_password.php" class="text-emerald-600 hover:underline font-semibold">Lupa Password?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3.5 text-sm font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl shadow-lg shadow-emerald-600/25 transition-all duration-300 hover:-translate-y-0.5 active:translate-y-0">
                    <i class="fa-solid fa-right-to-bracket mr-2"></i> Masuk Sekarang
                </button>
            </form>

            <div class="mt-6 text-center text-xs text-slate-500">
                Belum memiliki akun?
                <a href="register.php" class="text-emerald-600 font-bold hover:underline">Daftar Akun Baru</a>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-slate-400">
            <a href="index.php" class="hover:text-emerald-600 transition-colors"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Beranda</a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passInput = document.getElementById('password');
            const eyeIcon = document.getElementById('toggleEye');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>