<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');
$nama_user = getUserNama();

$error = '';

// Ambil data Wali Santri untuk pilihan dropdown
$wali_query = mysqli_query($koneksi, "SELECT ws.id, u.nama FROM wali_santri ws JOIN users u ON ws.user_id = u.id");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama           = trim($_POST['nama']);
    $username       = trim($_POST['username']);
    $email          = trim($_POST['email']);
    $telepon        = trim($_POST['telepon']);
    $password       = $_POST['password'];
    $nis            = trim($_POST['nis']);
    $kelas          = trim($_POST['kelas_kelompok']);
    $wali_id        = !empty($_POST['wali_id']) ? intval($_POST['wali_id']) : NULL;
    $target_juz     = intval($_POST['target_juz']);

    if (empty($nama) || empty($username) || empty($email) || empty($password) || empty($nis)) {
        $error = "Kolom yang bertanda bintang (*) wajib diisi.";
    } else {
        // Cek username/email unik
        $check_stmt = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($check_stmt, "ss", $username, $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        // Cek NIS unik
        $check_nis_stmt = mysqli_prepare($koneksi, "SELECT id FROM santri WHERE nis = ?");
        mysqli_stmt_bind_param($check_nis_stmt, "s", $nis);
        mysqli_stmt_execute($check_nis_stmt);
        mysqli_stmt_store_result($check_nis_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $error = "Username atau Email sudah terdaftar.";
        } elseif (mysqli_stmt_num_rows($check_nis_stmt) > 0) {
            $error = "NIS santri sudah terdaftar.";
        } else {
            $hashed_pass = password_hash($password, PASSWORD_BCRYPT);

            // Insert ke tabel users
            $stmt1 = mysqli_prepare($koneksi, "INSERT INTO users (nama, username, email, password, role, telepon) VALUES (?, ?, ?, ?, 'santri', ?)");
            mysqli_stmt_bind_param($stmt1, "sssss", $nama, $username, $email, $hashed_pass, $telepon);

            if (mysqli_stmt_execute($stmt1)) {
                $user_id = mysqli_insert_id($koneksi);
                mysqli_stmt_close($stmt1);

                // Ambil ID pengasuh/ustadz dari ustadz yang sedang login
                $cur_user_id = getUserId();
                $get_ust = mysqli_query($koneksi, "SELECT id FROM pengasuh WHERE user_id = $cur_user_id");
                
                // Fallback jika menggunakan nama tabel 'ustadz'
                if (!$get_ust || mysqli_num_rows($get_ust) == 0) {
                    $get_ust = mysqli_query($koneksi, "SELECT id FROM ustadz WHERE user_id = $cur_user_id");
                }

                $ustadz_id = NULL;
                if ($get_ust && $ustadz_data = mysqli_fetch_assoc($get_ust)) {
                    $ustadz_id = $ustadz_data['id'];
                }

                // Insert ke tabel santri
                $stmt2 = mysqli_prepare($koneksi, "INSERT INTO santri (user_id, nis, kelas_kelompok, ustadz_id, wali_id, target_juz) VALUES (?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt2, "issiii", $user_id, $nis, $kelas, $ustadz_id, $wali_id, $target_juz);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);

                header("Location: santri.php?msg=success");
                exit();
            } else {
                $error = "Gagal menambah santri baru.";
            }
        }

        mysqli_stmt_close($check_stmt);
        mysqli_stmt_close($check_nis_stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Santri Baru - E-Hafalan</title>
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
            <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-chart-pie text-slate-400 w-5"></i>
                <span>Dashboard</span>
            </a>
            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Master Data</div>
            <a href="santri.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-emerald-600 text-white font-medium shadow-lg shadow-emerald-600/30">
                <i class="fa-solid fa-user-graduate text-lg w-5"></i>
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
            <a href="setting.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-sliders text-slate-400 w-5"></i>
                <span>Pengaturan Sistem</span>
            </a>
        </nav>

        <div class="p-4 border-t border-slate-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white font-bold text-sm">
                        <?php echo strtoupper(substr($nama_user, 0, 1)); ?>
                    </div>
                    <div class="truncate w-28">
                        <p class="text-xs font-semibold text-white truncate"><?php echo htmlspecialchars($nama_user); ?></p>
                        <p class="text-[10px] text-slate-400 uppercase">Ustadz (Admin)</p>
                    </div>
                </div>
                <a href="../../logout.php" class="text-slate-400 hover:text-red-400 p-2 rounded-lg transition-colors" title="Logout">
                    <i class="fa-solid fa-right-from-bracket text-lg"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col min-w-0 overflow-x-hidden">
        <!-- HEADER DENGAN TOMBOL MENU UNTUK MOBILE -->
        <header class="bg-white border-b border-slate-200 px-4 md:px-6 py-4 sticky top-0 z-20 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <button onclick="toggleSidebar()" class="p-2 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 md:hidden focus:outline-none">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div>
                    <h2 class="text-base md:text-xl font-bold text-slate-800 leading-tight">Tambah Santri Baru</h2>
                    <p class="text-[10px] md:text-xs text-slate-500">Buat data dan akun login santri baru</p>
                </div>
            </div>
            <a href="santri.php" class="px-3 py-1.5 md:px-4 md:py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium transition-colors flex items-center">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
            </a>
        </header>

        <div class="p-4 md:p-6 max-w-4xl">
            <?php if (!empty($error)): ?>
                <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs md:text-sm flex items-center space-x-3">
                    <i class="fa-solid fa-circle-exclamation text-lg"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 md:p-6 shadow-sm">
                <form action="" method="POST" class="space-y-5">
                    
                    <h3 class="text-xs md:text-sm font-bold text-emerald-600 uppercase tracking-wider border-b border-slate-100 pb-2">Informasi Akun Login</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap Santri *</label>
                            <input type="text" name="nama" required placeholder="Ahmad Hanif" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">NIS (Nomor Induk Santri) *</label>
                            <input type="text" name="nis" required placeholder="SNT-2026-001" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Username *</label>
                            <input type="text" name="username" required placeholder="santri_hanif" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email *</label>
                            <input type="email" name="email" required placeholder="hanif@gmail.com" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Password *</label>
                            <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Telepon / WA</label>
                            <input type="text" name="telepon" placeholder="081234567890" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500">
                        </div>
                    </div>

                    <h3 class="text-xs md:text-sm font-bold text-emerald-600 uppercase tracking-wider border-b border-slate-100 pb-2 pt-4">Informasi Akademik & Hafalan</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kelas / Kelompok</label>
                            <input type="text" name="kelas_kelompok" value="Kelas Tahfidz 1" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Wali Santri</label>
                            <select name="wali_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500">
                                <option value="">-- Pilih Wali Santri --</option>
                                <?php while($wali = mysqli_fetch_assoc($wali_query)): ?>
                                    <option value="<?php echo $wali['id']; ?>"><?php echo htmlspecialchars($wali['nama']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Target Hafalan (Juz)</label>
                            <input type="number" name="target_juz" value="30" min="1" max="30" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500">
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end space-x-3">
                        <a href="santri.php" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 text-xs font-medium transition-colors">Batal</a>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium transition-all shadow-md shadow-emerald-600/20">
                            <i class="fa-solid fa-save mr-1"></i> Simpan Data Santri
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