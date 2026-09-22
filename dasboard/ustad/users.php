<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');

$nama_user = getUserNama();

// Toggle status aktif / nonaktif
if (isset($_GET['action']) && $_GET['action'] == 'toggle_status' && isset($_GET['id'])) {
    $u_id = intval($_GET['id']);
    $get_status = mysqli_query($koneksi, "SELECT status FROM users WHERE id = $u_id");
    if ($curr = mysqli_fetch_assoc($get_status)) {
        $new_status = ($curr['status'] == 'aktif') ? 'nonaktif' : 'aktif';
        mysqli_query($koneksi, "UPDATE users SET status = '$new_status' WHERE id = $u_id");
        header("Location: users.php?msg=updated");
        exit();
    }
}

// Fetch seluruh user
$query = "SELECT * FROM users ORDER BY id DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - E-Hafalan</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js untuk drawer sidebar, live search, & filter -->
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
    <style>
        body { font-family: 'Poppins', sans-serif; }
        
        /* Keyframe Custom Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* ------------------------------------------------ */
        /* TAMBAHKAN CLASS INI UNTUK SEMBUNYIKAN SCROLLBAR  */
        /* ------------------------------------------------ */
        .no-scrollbar::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE dan Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex flex-col md:flex-row pb-20 md:pb-0" 
      x-data="{ mobileMenuOpen: false, search: '', roleFilter: '' }">

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
                    <span class="text-[10px] text-emerald-400 font-semibold tracking-wider uppercase">Panel Super Admin</span>
                </div>
            </div>
            <button @click="mobileMenuOpen = false" class="md:hidden text-slate-400 hover:text-white p-2">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto text-sm no-scrollbar">
            <a href="dasboard.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all duration-200 group">
                <i class="fa-solid fa-chart-pie text-slate-400 group-hover:text-emerald-400 w-5 transition-colors"></i>
                <span>Dashboard</span>
            </a>

            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Master Data</div>

            <a href="santri.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all duration-200 group">
                <i class="fa-solid fa-user-graduate text-slate-400 group-hover:text-emerald-400 w-5 transition-colors"></i>
                <span>Data Santri</span>
            </a>
            <a href="wali.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all duration-200 group">
                <i class="fa-solid fa-users text-slate-400 group-hover:text-emerald-400 w-5 transition-colors"></i>
                <span>Data Wali Santri</span>
            </a>
            <a href="pengasuh.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all duration-200 group">
                <i class="fa-solid fa-user-tie text-slate-400 group-hover:text-emerald-400 w-5 transition-colors"></i>
                <span>Data Pengasuh</span>
            </a>
            <a href="users.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-emerald-600 text-white font-medium shadow-lg shadow-emerald-600/30 hover:scale-[1.02] transition-all duration-200">
                <i class="fa-solid fa-user-gear text-lg w-5"></i>
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


    </aside>

    <!-- BOTTOM NAVIGATION (Khusus Tampilan Mobile) -->
    <nav class="fixed bottom-0 left-0 right-0 bg-slate-900/95 backdrop-blur-md text-slate-400 border-t border-slate-800 z-40 flex justify-around items-center p-2 md:hidden">
        <a href="dashboard.php" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-chart-pie text-lg"></i>
            <span class="text-[10px] mt-0.5">Home</span>
        </a>
        <a href="santri.php" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-user-graduate text-lg"></i>
            <span class="text-[10px] mt-0.5">Santri</span>
        </a>
        <a href="wali.php" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-users text-lg"></i>
            <span class="text-[10px] mt-0.5">Wali</span>
        </a>
        <a href="users.php" class="flex flex-col items-center p-1 text-emerald-400 font-medium">
            <i class="fa-solid fa-user-gear text-lg"></i>
            <span class="text-[10px] mt-0.5">User</span>
        </a>
        <button @click="mobileMenuOpen = true" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-bars text-lg"></i>
            <span class="text-[10px] mt-0.5">Menu</span>
        </button>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col min-w-0 overflow-x-hidden">
        
        <!-- HEADER TOP -->
        <header class="bg-white/80 backdrop-blur-md border-b border-slate-200 px-4 sm:px-6 py-4 flex items-center justify-between sticky top-0 z-20">
            <div class="flex items-center space-x-3">
                <button @click="mobileMenuOpen = true" class="md:hidden text-slate-600 hover:text-slate-900 p-2 rounded-lg bg-slate-100">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-800">Kelola User / Akun Sistem</h2>
                    <p class="text-xs text-slate-500 hidden sm:block">Daftar seluruh akun pengguna dan kontrol hak akses</p>
                </div>
            </div>
        </header>

        <!-- CONTENT BODY -->
        <div class="p-4 sm:p-6 space-y-5 animate-fade-in">

            <!-- ALERT NOTIFIKASI -->
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
                <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-700 text-xs sm:text-sm flex items-center space-x-3 shadow-sm animate-fade-in">
                    <i class="fa-solid fa-circle-check text-lg text-blue-600"></i>
                    <span>Status akun pengguna berhasil diperbarui!</span>
                </div>
            <?php endif; ?>

            <!-- TOOLBAR & CARI DATA -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row gap-3 justify-between items-center">
                
                <!-- Pencarian Teks -->
                <div class="relative w-full sm:w-80">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" 
                           x-model="search" 
                           placeholder="Cari nama, username, email..." 
                           class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                </div>

                <!-- Filter Role & Total Users -->
                <div class="flex items-center justify-between sm:justify-end gap-3 w-full sm:w-auto">
                    <select x-model="roleFilter" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        <option value="">Semua Role</option>
                        <option value="ustad">Ustadz / Admin</option>
                        <option value="wali">Wali Santri</option>
                        <option value="santri">Santri</option>
                    </select>

                    <div class="text-xs text-slate-500 whitespace-nowrap">
                        Total: <span class="font-bold text-slate-800"><?php echo mysqli_num_rows($result); ?></span>
                    </div>
                </div>

            </div>

            <!-- TABLE WRAPPER -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-slate-50/80 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-100">
                                <th class="py-3.5 px-4 sm:px-5 font-semibold">Nama</th>
                                <th class="py-3.5 px-4 sm:px-5 font-semibold">Username / Email</th>
                                <th class="py-3.5 px-4 sm:px-5 font-semibold">Role Access</th>
                                <th class="py-3.5 px-4 sm:px-5 font-semibold">Telepon</th>
                                <th class="py-3.5 px-4 sm:px-5 font-semibold">Status</th>
                                <th class="py-3.5 px-4 sm:px-5 font-semibold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): 
                                    $searchableText = strtolower($row['nama'] . ' ' . $row['username'] . ' ' . $row['email'] . ' ' . ($row['telepon'] ?? ''));
                                    $userRole = strtolower($row['role']);
                                ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors"
                                        x-show="'<?php echo addslashes($searchableText); ?>'.includes(search.toLowerCase()) && (roleFilter === '' || roleFilter === '<?php echo $userRole; ?>')">
                                        
                                        <!-- NAMA -->
                                        <td class="py-3.5 px-4 sm:px-5 font-semibold text-slate-800 whitespace-nowrap">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs flex-shrink-0">
                                                    <?php echo strtoupper(substr($row['nama'], 0, 1)); ?>
                                                </div>
                                                <span><?php echo htmlspecialchars($row['nama']); ?></span>
                                            </div>
                                        </td>

                                        <!-- USERNAME & EMAIL -->
                                        <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap text-xs">
                                            <span class="font-mono text-slate-700 font-medium block">@<?php echo htmlspecialchars($row['username']); ?></span>
                                            <span class="text-slate-400 block mt-0.5"><?php echo htmlspecialchars($row['email']); ?></span>
                                        </td>

                                        <!-- ROLE -->
                                        <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                            <?php if ($row['role'] == 'ustad'): ?>
                                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-purple-100 text-purple-700">Ustadz / Admin</span>
                                            <?php elseif ($row['role'] == 'wali'): ?>
                                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-700">Wali Santri</span>
                                            <?php else: ?>
                                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">Santri</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- TELEPON -->
                                        <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap text-xs text-slate-600">
                                            <?php echo htmlspecialchars($row['telepon'] ?? '-'); ?>
                                        </td>

                                        <!-- STATUS -->
                                        <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold <?php echo $row['status'] == 'aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>

                                        <!-- AKSI -->
                                        <td class="py-3.5 px-4 sm:px-5 text-center whitespace-nowrap">
                                            <a href="users.php?action=toggle_status&id=<?php echo $row['id']; ?>" 
                                               onclick="return confirm('Apakah Anda yakin ingin mengubah status akun pengguna ini?');"
                                               class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-medium border transition-all active:scale-95 <?php echo $row['status'] == 'aktif' ? 'border-red-200 bg-red-50/50 text-red-600 hover:bg-red-100' : 'border-emerald-200 bg-emerald-50/50 text-emerald-600 hover:bg-emerald-100'; ?>">
                                                <i class="fa-solid <?php echo $row['status'] == 'aktif' ? 'fa-user-xmark' : 'fa-user-check'; ?>"></i>
                                                <span><?php echo $row['status'] == 'aktif' ? 'Nonaktifkan' : 'Aktifkan'; ?></span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                        <i class="fa-solid fa-users-slash text-3xl mb-2 text-slate-300 block"></i>
                                        Belum ada pengguna terdaftar dalam sistem.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </main>

</body>
</html>