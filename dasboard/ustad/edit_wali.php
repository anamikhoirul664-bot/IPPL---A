<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');
$nama_user = getUserNama();

$error = '';
$wali_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data wali santri saat ini
$stmt_curr = mysqli_prepare($koneksi, "SELECT ws.*, u.nama, u.username, u.email, u.telepon, u.status 
                                       FROM wali_santri ws 
                                       JOIN users u ON ws.user_id = u.id 
                                       WHERE ws.id = ?");
mysqli_stmt_bind_param($stmt_curr, "i", $wali_id);
mysqli_stmt_execute($stmt_curr);
$res_curr = mysqli_stmt_get_result($stmt_curr);
$data = mysqli_fetch_assoc($res_curr);

if (!$data) {
    header("Location: wali.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama      = trim($_POST['nama']);
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $telepon   = trim($_POST['telepon']);
    $status    = $_POST['status'];
    $pekerjaan = trim($_POST['pekerjaan']);
    $alamat    = trim($_POST['alamat']);
    $password  = $_POST['password'];

    $user_id = $data['user_id'];

    if (empty($nama) || empty($username) || empty($email)) {
        $error = "Kolom bertanda (*) wajib diisi.";
    } else {
        // Cek duplikasi Username / Email pada akun user lain
        $check_dup = mysqli_prepare($koneksi, "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        mysqli_stmt_bind_param($check_dup, "ssi", $username, $email, $user_id);
        mysqli_stmt_execute($check_dup);
        $res_dup = mysqli_stmt_get_result($check_dup);

        if (mysqli_num_rows($res_dup) > 0) {
            $error = "Username atau Email sudah digunakan oleh akun lain.";
        } else {
            // Update tabel users
            if (!empty($password)) {
                $hashed_pass = password_hash($password, PASSWORD_BCRYPT);
                $stmt1 = mysqli_prepare($koneksi, "UPDATE users SET nama=?, username=?, email=?, telepon=?, status=?, password=? WHERE id=?");
                mysqli_stmt_bind_param($stmt1, "ssssssi", $nama, $username, $email, $telepon, $status, $hashed_pass, $user_id);
            } else {
                $stmt1 = mysqli_prepare($koneksi, "UPDATE users SET nama=?, username=?, email=?, telepon=?, status=? WHERE id=?");
                mysqli_stmt_bind_param($stmt1, "sssssi", $nama, $username, $email, $telepon, $status, $user_id);
            }
            mysqli_stmt_execute($stmt1);

            // Update tabel wali_santri
            $stmt2 = mysqli_prepare($koneksi, "UPDATE wali_santri SET pekerjaan=?, alamat=? WHERE id=?");
            mysqli_stmt_bind_param($stmt2, "ssi", $pekerjaan, $alamat, $wali_id);
            mysqli_stmt_execute($stmt2);

            header("Location: wali.php?msg=updated");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Wali Santri - E-Hafalan</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js untuk Drawer Sidebar & Interaksi UI -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Google Fonts & FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style> 
        body { font-family: 'Poppins', sans-serif; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex flex-col md:flex-row pb-20 md:pb-0" 
      x-data="{ mobileMenuOpen: false, showPassword: false }">

    <!-- BACKDROP MOBILE MENU -->
    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileMenuOpen = false" 
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 md:hidden" 
         x-cloak></div>

    <!-- SIDEBAR (Desktop & Mobile Drawer) -->
    <aside :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
           class="w-64 bg-slate-900 text-slate-300 flex flex-col h-screen fixed md:sticky top-0 z-50 transition-transform duration-300 ease-in-out shadow-2xl md:shadow-none">
        
        <!-- Logo Brand -->
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-white text-xl font-bold shadow-lg shadow-emerald-500/30 transform hover:rotate-6 transition-transform">
                    <i class="fa-solid fa-quran"></i>
                </div>
                <div>
                    <h1 class="font-bold text-white text-lg leading-tight">E-Hafalan</h1>
                    <span class="text-[10px] text-emerald-400 font-semibold tracking-wider uppercase">Panel Admin</span>
                </div>
            </div>
            <button @click="mobileMenuOpen = false" class="md:hidden text-slate-400 hover:text-white p-2">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto text-sm scrollbar-thin">
            <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all duration-200 group">
                <i class="fa-solid fa-chart-pie text-slate-400 group-hover:text-emerald-400 w-5 transition-colors"></i>
                <span>Dashboard</span>
            </a>

            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Master Data</div>

            <a href="santri.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all duration-200 group">
                <i class="fa-solid fa-user-graduate text-slate-400 group-hover:text-emerald-400 w-5 transition-colors"></i>
                <span>Data Santri</span>
            </a>
            <a href="wali.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-emerald-600 text-white font-medium shadow-lg shadow-emerald-600/30 hover:scale-[1.02] transition-all duration-200">
                <i class="fa-solid fa-users text-lg w-5"></i>
                <span>Data Wali Santri</span>
            </a>
            <a href="pengasuh.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all duration-200 group">
                <i class="fa-solid fa-user-tie text-slate-400 group-hover:text-emerald-400 w-5 transition-colors"></i>
                <span>Data Pengasuh</span>
            </a>
            <a href="users.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all duration-200 group">
                <i class="fa-solid fa-user-gear text-slate-400 group-hover:text-emerald-400 w-5 transition-colors"></i>
                <span>Kelola User</span>
            </a>

            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Akademik & Hafalan</div>

            <a href="jadwal.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all duration-200 group">
                <i class="fa-regular fa-calendar-alt text-slate-400 group-hover:text-emerald-400 w-5 transition-colors"></i>
                <span>Jadwal Halaqah</span>
            </a>
            <a href="setoran.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all duration-200 group">
                <i class="fa-solid fa-book-bookmark text-slate-400 group-hover:text-emerald-400 w-5 transition-colors"></i>
                <span>Setoran Hafalan</span>
            </a>
            <a href="penilaian.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all duration-200 group">
                <i class="fa-solid fa-star text-slate-400 group-hover:text-emerald-400 w-5 transition-colors"></i>
                <span>Penilaian & Nilai</span>
            </a>

            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Laporan & Info</div>

            <a href="statistik.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all duration-200 group">
                <i class="fa-solid fa-chart-line text-slate-400 group-hover:text-emerald-400 w-5 transition-colors"></i>
                <span>Statistik Hafalan</span>
            </a>
            <a href="laporan.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all duration-200 group">
                <i class="fa-solid fa-file-invoice text-slate-400 group-hover:text-emerald-400 w-5 transition-colors"></i>
                <span>Laporan Hafalan</span>
            </a>
            <a href="pengumuman.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all duration-200 group">
                <i class="fa-solid fa-bullhorn text-slate-400 group-hover:text-emerald-400 w-5 transition-colors"></i>
                <span>Pengumuman</span>
            </a>
            <a href="setting.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all duration-200 group">
                <i class="fa-solid fa-sliders text-slate-400 group-hover:text-emerald-400 w-5 transition-colors"></i>
                <span>Pengaturan Sistem</span>
            </a>
        </nav>

        <!-- User Profile Footer -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/40">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                        <?php echo strtoupper(substr($nama_user, 0, 1)); ?>
                    </div>
                    <div class="truncate w-28">
                        <p class="text-xs font-semibold text-white truncate"><?php echo htmlspecialchars($nama_user); ?></p>
                        <p class="text-[10px] text-emerald-400 uppercase font-medium">Ustadz (Admin)</p>
                    </div>
                </div>
                <a href="../../logout.php" class="text-slate-400 hover:text-red-400 p-2 rounded-lg transition-colors" title="Logout">
                    <i class="fa-solid fa-right-from-bracket text-base"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- BOTTOM NAVIGATION (Khusus Mobile) -->
    <nav class="fixed bottom-0 left-0 right-0 bg-slate-900/95 backdrop-blur-md text-slate-400 border-t border-slate-800 z-40 flex justify-around items-center p-2 md:hidden">
        <a href="dashboard.php" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-chart-pie text-lg"></i>
            <span class="text-[10px] mt-0.5">Home</span>
        </a>
        <a href="wali.php" class="flex flex-col items-center p-1 text-emerald-400 font-medium">
            <i class="fa-solid fa-users text-lg"></i>
            <span class="text-[10px] mt-0.5">Wali</span>
        </a>
        <a href="setoran.php" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-book-bookmark text-lg"></i>
            <span class="text-[10px] mt-0.5">Setoran</span>
        </a>
        <a href="laporan.php" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-file-invoice text-lg"></i>
            <span class="text-[10px] mt-0.5">Laporan</span>
        </a>
        <button @click="mobileMenuOpen = true" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-bars text-lg"></i>
            <span class="text-[10px] mt-0.5">Menu</span>
        </button>
    </nav>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 flex flex-col min-w-0 overflow-x-hidden">
        
        <!-- HEADER TOP -->
        <header class="bg-white/80 backdrop-blur-md border-b border-slate-200 px-4 sm:px-6 py-4 flex items-center justify-between sticky top-0 z-20">
            <div class="flex items-center space-x-3">
                <button @click="mobileMenuOpen = true" class="md:hidden text-slate-600 hover:text-slate-900 p-2 rounded-lg bg-slate-100">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-800">Edit Data Wali Santri</h2>
                    <p class="text-xs text-slate-500 hidden sm:block">Perbarui informasi akun dan data diri wali santri</p>
                </div>
            </div>
            
            <a href="wali.php" class="px-3 sm:px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors flex items-center space-x-1 sm:space-x-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span class="hidden sm:inline">Kembali</span>
            </a>
        </header>

        <!-- FORM CONTAINER -->
        <div class="p-4 sm:p-6 max-w-4xl w-full mx-auto space-y-6 animate-fade-in">

            <?php if (!empty($error)): ?>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs sm:text-sm flex items-start space-x-3 shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation text-base sm:text-lg text-rose-500 mt-0.5"></i>
                    <div class="flex-1">
                        <span class="font-bold block mb-0.5">Terjadi Kesalahan</span>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-6 shadow-sm">
                <form action="edit_wali.php?id=<?php echo $wali_id; ?>" method="POST" class="space-y-6">
                    
                    <!-- SECTION 1: INFORMASI AKUN LOGIN -->
                    <div>
                        <div class="flex items-center space-x-2 pb-3 mb-4 border-b border-slate-100">
                            <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">
                                <i class="fa-solid fa-user-lock"></i>
                            </div>
                            <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">Informasi Akun Login</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap Wali <span class="text-rose-500">*</span></label>
                                <input type="text" name="nama" value="<?php echo htmlspecialchars($data['nama']); ?>" required 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Username <span class="text-rose-500">*</span></label>
                                <input type="text" name="username" value="<?php echo htmlspecialchars($data['username']); ?>" required 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Email <span class="text-rose-500">*</span></label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($data['email']); ?>" required 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Password Baru <span class="text-slate-400 font-normal">(Opsional)</span></label>
                                <div class="relative">
                                    <input :type="showPassword ? 'text' : 'password'" name="password" placeholder="Biarkan kosong jika tidak diganti" 
                                           class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                                    <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                        <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: INFORMASI PRIBADI & STATUS -->
                    <div>
                        <div class="flex items-center space-x-2 pb-3 mb-4 border-b border-slate-100">
                            <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">
                                <i class="fa-solid fa-address-card"></i>
                            </div>
                            <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">Informasi Pribadi & Kontak</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
                                <input type="text" name="telepon" value="<?php echo htmlspecialchars($data['telepon']); ?>" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Pekerjaan</label>
                                <input type="text" name="pekerjaan" value="<?php echo htmlspecialchars($data['pekerjaan']); ?>" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Status Akun</label>
                                <select name="status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                                    <option value="aktif" <?php echo $data['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="nonaktif" <?php echo $data['status'] == 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                </select>
                            </div>

                            <div class="sm:col-span-3">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Lengkap</label>
                                <textarea name="alamat" rows="3" placeholder="Masukkan alamat lengkap domisili wali..." 
                                          class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"><?php echo htmlspecialchars($data['alamat']); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- ACTION BUTTONS -->
                    <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row justify-end items-center gap-3">
                        <a href="wali.php" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 text-xs sm:text-sm font-semibold transition-colors text-center">
                            Batal
                        </a>
                        <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs sm:text-sm font-semibold transition-all shadow-md shadow-emerald-600/20 flex items-center justify-center space-x-2">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </main>

</body>
</html>