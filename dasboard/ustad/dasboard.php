<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

// Memastikan hanya role Ustadz yang dapat mengakses
checkRole('ustad');

$user_id = getUserId();
$nama_user = getUserNama();

// Fetch ringkasan statistik
$total_santri   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM santri"))['total'] ?? 0;
$total_wali     = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM wali_santri"))['total'] ?? 0;
$total_pengasuh = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengasuh"))['total'] ?? 0;
$total_setoran  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM setoran"))['total'] ?? 0;

// Fetch setoran hafalan terbaru (Relasi setoran -> santri -> users & surah)
$query_setoran = "SELECT s.*, st.nis, u.nama AS nama_santri, sr.nama_surah 
                  FROM setoran s
                  LEFT JOIN santri st ON s.santri_id = st.id
                  LEFT JOIN users u ON st.user_id = u.id
                  LEFT JOIN surah sr ON s.surah_id = sr.id
                  ORDER BY s.tanggal_setor DESC, s.id DESC LIMIT 5";

$result_setoran = mysqli_query($koneksi, $query_setoran);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Ustadz - E-Hafalan</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js untuk interaksi mobile & animasi dropdown/sidebar/modal -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Google Fonts & FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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

        /* Custom Scrollbar Utility */
        .no-scrollbar::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE dan Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen text-slate-800 flex flex-col md:flex-row pb-20 md:pb-0" x-data="{ mobileMenuOpen: false, logoutModalOpen: false }">

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
         style="display: none;"></div>

    <!-- SIDEBAR (Laptop Display & Mobile Drawer) -->
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
                    <span class="text-[10px] text-emerald-400 font-semibold tracking-wider uppercase">Panel Ustadz</span>
                </div>
            </div>
            <button @click="mobileMenuOpen = false" class="md:hidden text-slate-400 hover:text-white p-2">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto text-sm no-scrollbar">
            <a href="dasboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-emerald-600 text-white font-medium shadow-lg shadow-emerald-600/30 hover:scale-[1.02] transition-all duration-200">
                <i class="fa-solid fa-chart-pie text-lg w-5"></i>
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

        <!-- User Profile Card / Logout -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/40">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                        <?php echo strtoupper(substr($nama_user, 0, 1)); ?>
                    </div>
                    <div class="truncate w-28">
                        <p class="text-xs font-semibold text-white truncate"><?php echo htmlspecialchars($nama_user); ?></p>
                        <p class="text-[10px] text-emerald-400 uppercase font-medium">Ustadz</p>
                    </div>
                </div>

                <!-- TOMBOL LOGOUT (MEMBUAT MODAL KELUAR) -->
                <button type="button" @click="logoutModalOpen = true" class="text-slate-400 hover:text-red-400 p-2 rounded-lg transition-colors cursor-pointer" title="Logout">
                    <i class="fa-solid fa-right-from-bracket text-base"></i>
                </button>
            </div>
        </div>
    </aside>

    <!-- BOTTOM NAVIGATION UNTUK MOBILE -->
    <nav class="fixed bottom-0 left-0 right-0 bg-slate-900/95 backdrop-blur-md text-slate-400 border-t border-slate-800 z-40 flex justify-around items-center p-2 md:hidden">
        <a href="dasboard.php" class="flex flex-col items-center p-1 text-emerald-400 font-medium">
            <i class="fa-solid fa-chart-pie text-lg"></i>
            <span class="text-[10px] mt-0.5">Home</span>
        </a>
        <a href="setoran.php" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-book-bookmark text-lg"></i>
            <span class="text-[10px] mt-0.5">Setoran</span>
        </a>
        <a href="tambah_setoran.php" class="flex flex-col items-center -mt-5">
            <div class="w-12 h-12 bg-emerald-500 text-white rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/40 ring-4 ring-slate-900 active:scale-95 transition-transform">
                <i class="fa-solid fa-plus text-lg"></i>
            </div>
            <span class="text-[10px] mt-0.5 font-semibold text-emerald-400">Catat</span>
        </a>
        <a href="santri.php" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-user-graduate text-lg"></i>
            <span class="text-[10px] mt-0.5">Santri</span>
        </a>
        <button @click="mobileMenuOpen = true" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-bars text-lg"></i>
            <span class="text-[10px] mt-0.5">Menu</span>
        </button>
    </nav>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 flex flex-col min-w-0 overflow-x-hidden">

        <!-- NAVBAR TOP -->
        <header class="bg-white/80 backdrop-blur-md border-b border-slate-200 px-4 sm:px-6 py-4 flex items-center justify-between sticky top-0 z-20">
            <div class="flex items-center space-x-3">
                <button @click="mobileMenuOpen = true" class="md:hidden text-slate-600 hover:text-slate-900 p-2 rounded-lg bg-slate-100">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-800">Ringkasan Dashboard</h2>
                    <p class="text-xs text-slate-500 hidden sm:block">Selamat datang kembali, Ustadz <?php echo htmlspecialchars($nama_user); ?>!</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <a href="tambah_setoran.php" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs font-semibold shadow-md shadow-emerald-600/20 transition-all flex items-center space-x-2">
                    <i class="fa-solid fa-plus text-sm"></i>
                    <span class="hidden sm:inline">Catat Setoran</span>
                </a>
            </div>
        </header>

        <!-- DASHBOARD CONTENT -->
        <div class="p-4 sm:p-6 space-y-6 animate-fade-in">

            <!-- STATISTIC CARDS -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">

                <!-- Card Santri -->
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col sm:flex-row items-start sm:items-center justify-between group">
                    <div class="order-2 sm:order-1 mt-2 sm:mt-0">
                        <p class="text-[11px] sm:text-xs text-slate-500 font-medium">Total Santri</p>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-800 mt-0.5 sm:mt-1 group-hover:text-emerald-600 transition-colors"><?php echo $total_santri; ?></h3>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg sm:text-xl order-1 sm:order-2 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                </div>

                <!-- Card Wali -->
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col sm:flex-row items-start sm:items-center justify-between group">
                    <div class="order-2 sm:order-1 mt-2 sm:mt-0">
                        <p class="text-[11px] sm:text-xs text-slate-500 font-medium">Wali Santri</p>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-800 mt-0.5 sm:mt-1 group-hover:text-teal-600 transition-colors"><?php echo $total_wali; ?></h3>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg sm:text-xl order-1 sm:order-2 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>

                <!-- Card Pengasuh -->
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col sm:flex-row items-start sm:items-center justify-between group">
                    <div class="order-2 sm:order-1 mt-2 sm:mt-0">
                        <p class="text-[11px] sm:text-xs text-slate-500 font-medium">Pengasuh</p>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-800 mt-0.5 sm:mt-1 group-hover:text-indigo-600 transition-colors"><?php echo $total_pengasuh; ?></h3>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg sm:text-xl order-1 sm:order-2 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                </div>

                <!-- Card Setoran -->
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col sm:flex-row items-start sm:items-center justify-between group">
                    <div class="order-2 sm:order-1 mt-2 sm:mt-0">
                        <p class="text-[11px] sm:text-xs text-slate-500 font-medium">Total Setoran</p>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-800 mt-0.5 sm:mt-1 group-hover:text-amber-600 transition-colors"><?php echo $total_setoran; ?></h3>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg sm:text-xl order-1 sm:order-2 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                </div>

            </div>

            <!-- TABLE SETORAN TERBARU -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm sm:text-base">Setoran Hafalan Terbaru</h3>
                        <p class="text-[11px] text-slate-400 hidden sm:block">Aktivitas penambahan hafalan santri terkini</p>
                    </div>
                    <a href="setoran.php" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center space-x-1 group">
                        <span>Lihat Semua</span>
                        <i class="fa-solid fa-chevron-right text-[10px] transform group-hover:translate-x-0.5 transition-transform"></i>
                    </a>
                </div>

                <!-- Table Wrapper -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-slate-50/80 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-100">
                                <th class="py-3 px-4 sm:px-5 font-semibold">Santri</th>
                                <th class="py-3 px-4 sm:px-5 font-semibold">Surah</th>
                                <th class="py-3 px-4 sm:px-5 font-semibold">Ayat</th>
                                <th class="py-3 px-4 sm:px-5 font-semibold">Juz</th>
                                <th class="py-3 px-4 sm:px-5 font-semibold">Kelancaran</th>
                                <th class="py-3 px-4 sm:px-5 font-semibold">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php if (mysqli_num_rows($result_setoran) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result_setoran)): ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3 px-4 sm:px-5 font-medium text-slate-800 whitespace-nowrap">
                                            <?php echo htmlspecialchars($row['nama_santri'] ?? 'Santri'); ?>
                                            <span class="block text-[10px] text-slate-400">NIS: <?php echo htmlspecialchars($row['nis'] ?? '-'); ?></span>
                                        </td>
                                        <td class="py-3 px-4 sm:px-5 font-medium whitespace-nowrap"><?php echo htmlspecialchars($row['nama_surah'] ?? 'Surah'); ?></td>
                                        <td class="py-3 px-4 sm:px-5 whitespace-nowrap"><?php echo $row['ayat_mulai']; ?> - <?php echo $row['ayat_selesai']; ?></td>
                                        <td class="py-3 px-4 sm:px-5 whitespace-nowrap">Juz <?php echo $row['juz']; ?></td>
                                        <td class="py-3 px-4 sm:px-5 whitespace-nowrap">
                                            <?php
                                            $kelancaran = $row['kelancaran'];
                                            $badge = 'bg-amber-100 text-amber-700';
                                            if ($kelancaran == 'Sangat Lancar' || $kelancaran == 'Lancar') {
                                                $badge = 'bg-emerald-100 text-emerald-700';
                                            } else if ($kelancaran == 'Mengulang') {
                                                $badge = 'bg-red-100 text-red-700';
                                            }
                                            ?>
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold <?php echo $badge; ?>">
                                                <?php echo htmlspecialchars($kelancaran); ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 sm:px-5 text-[11px] text-slate-400 whitespace-nowrap">
                                            <?php echo date('d M Y, H:i', strtotime($row['tanggal_setor'])); ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 text-xs">Belum ada data setoran hafalan terbaru.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </main>

    <!-- MODAL CONFIRMATION LOGOUT (Menggunakan Alpine.js) -->
    <div x-show="logoutModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="logoutModalOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4" 
         style="display: none;">
         
        <!-- Card Box Modal -->
        <div x-show="logoutModalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-slate-900 border border-slate-800 rounded-2xl p-6 w-full max-w-sm shadow-2xl text-center">
            
            <!-- Icon Warning/Logout -->
            <div class="w-14 h-14 bg-red-500/10 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-500/20">
                <i class="fa-solid fa-right-from-bracket text-2xl"></i>
            </div>

            <!-- Judul & Pesan -->
            <h3 class="text-lg font-bold text-white mb-1">Konfirmasi Logout</h3>
            <p class="text-sm text-slate-400 mb-6">Apakah Anda yakin ingin keluar dari sistem ini?</p>

            <!-- Tombol Aksi -->
            <div class="flex items-center space-x-3">
                <button type="button" @click="logoutModalOpen = false" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:bg-slate-800 font-medium text-sm transition-all cursor-pointer">
                    Batal
                </button>
                <a href="../../logout.php" class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-medium text-sm transition-all shadow-lg shadow-red-600/30 text-center">
                    Ya, Keluar
                </a>
            </div>
        </div>
    </div>

</body>
</html>