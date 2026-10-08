<?php
session_start();
require_once 'config/koneksi.php';

// Generate CSRF Token untuk keamanan tambahan
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

// Inisialisasi variabel input agar tidak hilang saat ada eror
$nama = $username = $email = $role = $nis_santri = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF Token
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = "Sesi tidak valid. Silakan muat ulang halaman dan coba lagi.";
    } else {
        $nama       = trim($_POST['nama'] ?? '');
        $username   = trim($_POST['username'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $role       = trim($_POST['role'] ?? '');
        $nis_santri = trim($_POST['nis_santri'] ?? '');
        $password   = $_POST['password'] ?? '';
        $confirm    = $_POST['confirm_password'] ?? '';

        // Hanya izinkan pendaftaran publik untuk 'santri' dan 'wali'
        $allowed_roles = ['santri', 'wali'];

        // 1. VALIDASI INPUT KOSONG
        if (empty($nama) || empty($username) || empty($email) || empty($role) || empty($password) || empty($confirm)) {
            $error = "Semua kolom wajib diisi.";
        } 
        // 1b. VALIDASI NIS KHUSUS ROLE WALI
        elseif ($role === 'wali' && empty($nis_santri)) {
            $error = "NIS Santri wajib diisi untuk pendaftaran Wali Santri.";
        }
        // 2. VALIDASI FORMAT NAMA
        elseif (!preg_match('/^[a-zA-Z\s\'.]{3,50}$/', $nama)) {
            $error = "Nama hanya boleh berisi huruf, spasi, tanda petik, dan titik (3–50 karakter).";
        }
        // 3. VALIDASI FORMAT USERNAME
        elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            $error = "Username hanya boleh berupa 3–20 karakter huruf, angka, atau underscore (_).";
        } 
        // 4. VALIDASI FORMAT EMAIL
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Format email tidak valid.";
        } 
        // 5. VALIDASI ROLE
        elseif (!in_array($role, $allowed_roles, true)) {
            $error = "Peran (role) yang dipilih tidak sah.";
        } 
        // 6. VALIDASI KEKUATAN PASSWORD
        elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $error = "Password minimal 8 karakter dan harus mengandung kombinasi huruf besar, huruf kecil, serta angka.";
        } 
        // 7. VALIDASI KONFIRMASI PASSWORD
        elseif ($password !== $confirm) {
            $error = "Konfirmasi password tidak cocok.";
        } 
        else {
            // Cek ketersediaan username / email
            $check_stmt = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ? OR email = ?");
            mysqli_stmt_bind_param($check_stmt, "ss", $username, $email);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);

            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $error = "Username atau Email sudah terdaftar.";
                mysqli_stmt_close($check_stmt);
            } else {
                mysqli_stmt_close($check_stmt);

                // Cek Validitas NIS Santri (Khusus Pendaftaran Wali)
                $santri_target_id = null;
                if ($role === 'wali') {
                    $check_nis = mysqli_prepare($koneksi, "SELECT id FROM santri WHERE nis = ?");
                    mysqli_stmt_bind_param($check_nis, "s", $nis_santri);
                    mysqli_stmt_execute($check_nis);
                    $res_nis = mysqli_stmt_get_result($check_nis);
                    
                    if ($row_santri = mysqli_fetch_assoc($res_nis)) {
                        $santri_target_id = $row_santri['id'];
                    } else {
                        $error = "NIS Santri tidak ditemukan. Pastikan akun Santri sudah terdaftar terlebih dahulu.";
                    }
                    mysqli_stmt_close($check_nis);
                }

                if (empty($error)) {
                    // Transaksi Database Dimulai (Aman & Atomis)
                    mysqli_begin_transaction($koneksi);

                    try {
                        // Hash Password menggunakan BCRYPT
                        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                        // Insert User Baru
                        $insert_stmt = mysqli_prepare($koneksi, "INSERT INTO users (nama, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
                        mysqli_stmt_bind_param($insert_stmt, "sssss", $nama, $username, $email, $hashed_password, $role);

                        if (!mysqli_stmt_execute($insert_stmt)) {
                            throw new Exception("Gagal membuat akun.");
                        }

                        $user_id = mysqli_insert_id($koneksi);
                        mysqli_stmt_close($insert_stmt);

                        // Insert ke Tabel Spesifik Role
                        if ($role === 'santri') {
                            $nis = 'NIS' . str_pad($user_id, 5, '0', STR_PAD_LEFT);
                            $role_stmt = mysqli_prepare($koneksi, "INSERT INTO santri (user_id, nis) VALUES (?, ?)");
                            mysqli_stmt_bind_param($role_stmt, "is", $user_id, $nis);
                            
                            if (!mysqli_stmt_execute($role_stmt)) {
                                throw new Exception("Gagal membuat profil santri.");
                            }
                            mysqli_stmt_close($role_stmt);

                        } elseif ($role === 'wali') {
                            $role_stmt = mysqli_prepare($koneksi, "INSERT INTO wali_santri (user_id) VALUES (?)");
                            mysqli_stmt_bind_param($role_stmt, "i", $user_id);
                            
                            if (!mysqli_stmt_execute($role_stmt)) {
                                throw new Exception("Gagal membuat profil wali.");
                            }
                            
                            $wali_id_baru = mysqli_insert_id($koneksi);
                            mysqli_stmt_close($role_stmt);

                            // Hubungkan Wali dengan Santri
                            $link_stmt = mysqli_prepare($koneksi, "UPDATE santri SET wali_id = ? WHERE id = ?");
                            mysqli_stmt_bind_param($link_stmt, "ii", $wali_id_baru, $santri_target_id);
                            
                            if (!mysqli_stmt_execute($link_stmt)) {
                                throw new Exception("Gagal menghubungkan data wali dengan santri.");
                            }
                            mysqli_stmt_close($link_stmt);
                        }

                        // Commit transaksi jika seluruh tahapan sukses
                        mysqli_commit($koneksi);
                        
                        $success = "Pendaftaran berhasil! Silakan login dengan akun Anda.";
                        // Reset input
                        $nama = $username = $email = $role = $nis_santri = '';

                    } catch (Exception $e) {
                        // Rollback transaksi jika terjadi kesalahan
                        mysqli_rollback($koneksi);
                        $error = "Terjadi kesalahan saat pendaftaran. Silakan coba lagi.";
                    }
                }
            }
        }
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
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.6);
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen flex items-center justify-center relative overflow-x-hidden p-4">

    <!-- Background Orbs Decorative -->
    <div class="absolute -top-20 -right-20 w-80 h-80 md:w-96 md:h-96 bg-emerald-300/30 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-80 h-80 md:w-96 md:h-96 bg-teal-300/30 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-lg relative z-10 py-6">

        <!-- Header Logo -->
        <div class="text-center mb-6">
            <a href="index.php" class="inline-flex items-center space-x-3 group">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-emerald-500/30 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-quran"></i>
                </div>
                <span class="text-2xl font-bold bg-gradient-to-r from-emerald-700 to-teal-600 bg-clip-text text-transparent">E-Hafalan</span>
            </a>
            <h2 class="text-xl md:text-2xl font-bold text-slate-800 mt-4">Buat Akun Baru</h2>
            <p class="text-xs md:text-sm text-slate-500">Bergabung dalam ekosistem digital monitoring hafalan</p>
        </div>

        <!-- Card Form Register -->
        <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-2xl shadow-emerald-900/10">

            <?php if (!empty($error)): ?>
                <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs md:text-sm flex items-start space-x-3">
                    <i class="fa-solid fa-circle-exclamation text-base mt-0.5 shrink-0"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs md:text-sm flex items-start space-x-3">
                    <i class="fa-solid fa-circle-check text-base mt-0.5 shrink-0"></i>
                    <div>
                        <span><?php echo htmlspecialchars($success); ?></span>
                        <a href="login.php" class="block font-bold underline mt-1 text-emerald-800 hover:text-emerald-900">Klik untuk Login</a>
                    </div>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" id="regForm" class="space-y-4" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap *</label>
                    <input type="text" name="nama" value="<?php echo htmlspecialchars($nama); ?>" required placeholder="Ahmad Hanif" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all">
                </div>

                <!-- Username & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Username *</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required placeholder="ahmad_hanif" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Email *</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required placeholder="hanif@example.com" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all">
                    </div>
                </div>

                <!-- Role Selection -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Daftar Sebagai (Role) *</label>
                    <select name="role" id="roleSelect" onchange="toggleNisInput()" required class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all">
                        <option value="" disabled <?php echo empty($role) ? 'selected' : ''; ?>>Pilih Peran Pendaftaran</option>
                        <option value="santri" <?php echo $role === 'santri' ? 'selected' : ''; ?>>Santri</option>
                        <option value="wali" <?php echo $role === 'wali' ? 'selected' : ''; ?>>Wali Santri</option>
                    </select>
                </div>

                <!-- Input NIS Santri (Hanya Muncul Jika Role = Wali) -->
                <div id="nisContainer" class="<?php echo $role === 'wali' ? '' : 'hidden'; ?>">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">NIS Santri (Anak) *</label>
                    <input type="text" name="nis_santri" id="nis_santri" value="<?php echo htmlspecialchars($nis_santri); ?>" placeholder="Contoh: NIS00001" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all">
                    <p class="text-[11px] text-slate-400 mt-1">Masukkan Nomor Induk Santri (NIS) dari anak yang bersangkutan.</p>
                </div>

                <!-- Password & Confirm Password -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Password *</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required placeholder="••••••••" oninput="checkPasswordStrength()" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all pr-10">
                            <button type="button" onclick="togglePass('password', 'eye1')" class="absolute right-3 top-3 text-slate-400 hover:text-slate-600 focus:outline-none">
                                <i id="eye1" class="fa-solid fa-eye text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Password *</label>
                        <div class="relative">
                            <input type="password" id="confirm_password" name="confirm_password" required placeholder="••••••••" oninput="checkMatch()" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all pr-10">
                            <button type="button" onclick="togglePass('confirm_password', 'eye2')" class="absolute right-3 top-3 text-slate-400 hover:text-slate-600 focus:outline-none">
                                <i id="eye2" class="fa-solid fa-eye text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Indikator Kesesuaian Password -->
                <div id="match-msg" class="text-xs hidden font-medium"></div>

                <!-- Petunjuk Password -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-1">
                    <p class="text-[11px] font-semibold text-slate-600">Syarat Password Khas:</p>
                    <div class="grid grid-cols-2 gap-1 text-[11px] text-slate-500">
                        <span id="rule-len" class="flex items-center"><i class="fa-solid fa-circle-dot text-[9px] mr-1.5 text-slate-300"></i>Min. 8 Karakter</span>
                        <span id="rule-upper" class="flex items-center"><i class="fa-solid fa-circle-dot text-[9px] mr-1.5 text-slate-300"></i>Huruf Besar (A-Z)</span>
                        <span id="rule-lower" class="flex items-center"><i class="fa-solid fa-circle-dot text-[9px] mr-1.5 text-slate-300"></i>Huruf Kecil (a-z)</span>
                        <span id="rule-num" class="flex items-center"><i class="fa-solid fa-circle-dot text-[9px] mr-1.5 text-slate-300"></i>Angka (0-9)</span>
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 mt-2 text-sm font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl shadow-lg shadow-emerald-600/25 transition-all duration-300 hover:-translate-y-0.5 active:translate-y-0">
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

    <script>
        // Toggle Input NIS Santri berdasarkan Role yang dipilih
        function toggleNisInput() {
            const roleSelect = document.getElementById('roleSelect');
            const nisContainer = document.getElementById('nisContainer');
            const nisInput = document.getElementById('nis_santri');

            if (roleSelect.value === 'wali') {
                nisContainer.classList.remove('hidden');
                nisInput.setAttribute('required', 'required');
            } else {
                nisContainer.classList.add('hidden');
                nisInput.removeAttribute('required');
                nisInput.value = '';
            }
        }

        // Toggle Show/Hide Password
        function togglePass(inputId, eyeId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(eyeId);
            if (input.type === "password") {
                input.type = "text";
                eye.classList.remove("fa-eye");
                eye.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                eye.classList.remove("fa-eye-slash");
                eye.classList.add("fa-eye");
            }
        }

        // Live Password Strength Checklist
        function checkPasswordStrength() {
            const val = document.getElementById('password').value;
            
            updateRule('rule-len', val.length >= 8);
            updateRule('rule-upper', /[A-Z]/.test(val));
            updateRule('rule-lower', /[a-z]/.test(val));
            updateRule('rule-num', /[0-9]/.test(val));

            checkMatch();
        }

        function updateRule(elementId, isValid) {
            const el = document.getElementById(elementId);
            const icon = el.querySelector('i');
            if (isValid) {
                el.classList.remove('text-slate-500');
                el.classList.add('text-emerald-600', 'font-medium');
                icon.className = "fa-solid fa-circle-check text-[10px] mr-1.5 text-emerald-500";
            } else {
                el.classList.remove('text-emerald-600', 'font-medium');
                el.classList.add('text-slate-500');
                icon.className = "fa-solid fa-circle-dot text-[9px] mr-1.5 text-slate-300";
            }
        }

        // Live Password Match Validation
        function checkMatch() {
            const pass = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            msg = document.getElementById('match-msg');

            if (confirm.length === 0) {
                msg.classList.add('hidden');
                return;
            }

            msg.classList.remove('hidden');
            if (pass === confirm) {
                msg.className = "text-xs font-medium text-emerald-600 flex items-center";
                msg.innerHTML = '<i class="fa-solid fa-check mr-1.5"></i> Konfirmasi password sesuai.';
            } else {
                msg.className = "text-xs font-medium text-red-500 flex items-center";
                msg.innerHTML = '<i class="fa-solid fa-xmark mr-1.5"></i> Konfirmasi password tidak cocok.';
            }
        }

        // Jalankan pemeriksaan awal saat halaman dimuat
        document.addEventListener("DOMContentLoaded", function() {
            toggleNisInput();
        });
    </script>
</body>

</html>