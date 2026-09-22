<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');
$user_id   = getUserId();
$nama_user = getUserNama();

$msg = '';
$err = '';

// 1. Ambil data user yang sedang login dengan Prepared Statement
$stmt_get = mysqli_prepare($koneksi, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt_get, "i", $user_id);
mysqli_stmt_execute($stmt_get);
$result_get = mysqli_stmt_get_result($stmt_get);
$user_data  = mysqli_fetch_assoc($result_get);
mysqli_stmt_close($stmt_get);

// Jika user tidak ditemukan di database
if (!$user_data) {
    header("Location: ../../logout.php");
    exit();
}

// 2. Proses Update Profil saat Submit Form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama     = trim($_POST['nama']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($nama) || empty($email)) {
        $err = "Nama dan Email tidak boleh kosong.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "Format alamat email tidak valid.";
    } else {
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = mysqli_prepare($koneksi, "UPDATE users SET nama = ?, email = ?, password = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sssi", $nama, $email, $hashed, $user_id);
        } else {
            $stmt = mysqli_prepare($koneksi, "UPDATE users SET nama = ?, email = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ssi", $nama, $email, $user_id);
        }

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['user_nama'] = $nama;
            $nama_user = $nama; // Update variabel untuk tampilan UI saat ini
            $user_data['nama']  = $nama;
            $user_data['email'] = $email;
            
            $msg = 'Profil berhasil diperbarui!';
        } else {
            $err = "Gagal memperbarui profil.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem - E-Hafalan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex flex-col md:flex-row">

    <!-- OVERLAY BACKGROUND UNTUK MOBILE -->
    <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 hidden md:hidden"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-slate-900 text-slate-300 flex flex-col z-40 transition-transform duration-300 transform -translate-x-full md:translate-x-0 md:static md:min-h-screen">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white text-xl font-bold shadow-lg shadow-emerald-500/20">
                    <i class="fa-solid fa-quran"></i>
                </div>
                <div>
                    <h1 class="font-bold text-white text-lg leading-tight">E-Hafalan</h1>
                    <span class="text-xs text-emerald-400 font-medium">Panel Super Admin</span>
                </div>
            </div>
            <button onclick="toggleSidebar()" class="text-slate-400 hover:text-white md:hidden p-2">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <nav class="flex-1 p-4 space-y-1 overflow-y-auto text-sm">
            <a href="dasboard.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-chart-pie text-slate-400 w-5"></i>
                <span>Dashboard</span>
            </a>
            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Master Data</div>
            <a href="santri.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-user-graduate text-slate-400 w-5"></i>
                <span>Data Santri</span>
            </a>
            <a href="wali.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-users text-slate-400 w-5"></i>
                <span>Data Wali Santri</span>
            </a>
            <a href="pengasuh.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-user-tie text-slate-400 w-5"></i>
                <span>Data Pengasuh</span>
            </a>
            <a href="users.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-user-gear text-slate-400 w-5"></i>
                <span>Kelola User</span>
            </a>
            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Akademik & Hafalan</div>
            <a href="jadwal.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-regular fa-calendar-alt text-slate-400 w-5"></i>
                <span>Jadwal Halaqah</span>
            </a>
            <a href="setoran.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-book-bookmark text-slate-400 w-5"></i>
                <span>Setoran Hafalan</span>
            </a>
            <a href="penilaian.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-star text-slate-400 w-5"></i>
                <span>Penilaian & Nilai</span>
            </a>
            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Laporan & Info</div>
            <a href="statistik.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-chart-line text-slate-400 w-5"></i>
                <span>Statistik Hafalan</span>
            </a>
            <a href="laporan.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-file-invoice text-slate-400 w-5"></i>
                <span>Laporan Hafalan</span>
            </a>
            <a href="pengumuman.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-bullhorn text-slate-400 w-5"></i>
                <span>Pengumuman</span>
            </a>
            <a href="setting.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-emerald-600 text-white font-medium shadow-lg shadow-emerald-600/30">
                <i class="fa-solid fa-sliders text-lg w-5"></i>
                <span>Pengaturan Sistem</span>
            </a>
        </nav>


    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col min-w-0 overflow-x-hidden">
        <!-- HEADER DENGAN TOMBOL MENU UNTUK MOBILE -->
        <header class="bg-white border-b border-slate-200 px-4 md:px-6 py-4 sticky top-0 z-20 flex items-center space-x-3">
            <button onclick="toggleSidebar()" class="p-2 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 md:hidden focus:outline-none">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            <div>
                <h2 class="text-base md:text-xl font-bold text-slate-800 leading-tight">Pengaturan Akun</h2>
                <p class="text-[10px] md:text-xs text-slate-500">Perbarui informasi profil dan kata sandi akun Anda</p>
            </div>
        </header>

        <div class="p-4 md:p-6 max-w-2xl">
            <?php if (!empty($msg)): ?>
                <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs md:text-sm flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><?php echo htmlspecialchars($msg); ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-base">&times;</button>
                </div>
            <?php endif; ?>

            <?php if (!empty($err)): ?>
                <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs md:text-sm flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span><?php echo htmlspecialchars($err); ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-base">&times;</button>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 md:p-6 shadow-sm">
                <form action="setting.php" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Username (Tetap)</label>
                        <input type="text" value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" disabled class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-xs md:text-sm font-mono text-slate-500 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                        <input type="text" name="nama" value="<?php echo htmlspecialchars($user_data['nama'] ?? ''); ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500 focus:bg-white transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email *</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500 focus:bg-white transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Password Baru (Kosongkan jika tidak diubah)</label>
                        <input type="password" name="password" placeholder="••••••••" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500 focus:bg-white transition-all">
                    </div>
                    <div class="pt-4 text-right">
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs md:text-sm rounded-xl shadow-md transition-all">
                            <i class="fa-solid fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- SCRIPT TOGGLE SIDEBAR MOBILE -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>
</body>
</html>