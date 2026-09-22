<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');
$nama_user = getUserNama();

// Ambil daftar halaqah untuk filter dropdown
$halaqah_options = mysqli_query($koneksi, "SELECT id, nama_halaqah FROM halaqah ORDER BY nama_halaqah ASC");

// Handling Filter & Search
$filter_halaqah = isset($_GET['halaqah_id']) ? trim($_GET['halaqah_id']) : '';
$search_query   = isset($_GET['q']) ? trim($_GET['q']) : '';

// Base Query
$where_clauses = [];
if (!empty($filter_halaqah)) {
    $where_clauses[] = "s.halaqah_id = '" . mysqli_real_escape_string($koneksi, $filter_halaqah) . "'";
}
if (!empty($search_query)) {
    $q_escaped = mysqli_real_escape_string($koneksi, $search_query);
    $where_clauses[] = "(s.nis LIKE '%$q_escaped%' OR u.nama LIKE '%$q_escaped%')";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

$laporan_query = "SELECT s.nis, u.nama AS nama_santri, h.nama_halaqah, s.total_hafalan, 
                  (SELECT COUNT(*) FROM setoran st WHERE st.santri_id = s.id) AS total_setoran
                  FROM santri s
                  JOIN users u ON s.user_id = u.id
                  LEFT JOIN halaqah h ON s.halaqah_id = h.id
                  $where_sql
                  ORDER BY u.nama ASC";
$laporan_data = mysqli_query($koneksi, $laporan_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Hafalan - E-Hafalan</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js untuk drawer sidebar & interaksi UI -->
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

        /* Styling Khusus Mode Cetak / Print PDF */
        @media print {
            aside, header, nav, .no-print, button, form { 
                display: none !important; 
            }
            body { 
                background: #ffffff !important; 
                color: #000000 !important; 
                padding: 0 !important;
                margin: 0 !important;
            }
            main { 
                margin: 0 !important; 
                padding: 0 !important; 
                width: 100% !important;
            }
            .print-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            th, td {
                border: 1px solid #cbd5e1 !important;
                padding: 8px 12px !important;
            }
            .print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 20px;
            }
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
      x-data="{ mobileMenuOpen: false }">

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
           class="w-64 bg-slate-900 text-slate-300 flex flex-col h-screen fixed md:sticky top-0 z-50 transition-transform duration-300 ease-in-out shadow-2xl md:shadow-none no-print">
        
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
            <a href="laporan.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-emerald-600 text-white font-medium shadow-lg shadow-emerald-600/30 hover:scale-[1.02] transition-all duration-200">
                <i class="fa-solid fa-file-invoice text-lg w-5"></i>
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
    <nav class="fixed bottom-0 left-0 right-0 bg-slate-900/95 backdrop-blur-md text-slate-400 border-t border-slate-800 z-40 flex justify-around items-center p-2 md:hidden no-print">
        <a href="dashboard.php" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-chart-pie text-lg"></i>
            <span class="text-[10px] mt-0.5">Home</span>
        </a>
        <a href="santri.php" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-user-graduate text-lg"></i>
            <span class="text-[10px] mt-0.5">Santri</span>
        </a>
        <a href="setoran.php" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-book-bookmark text-lg"></i>
            <span class="text-[10px] mt-0.5">Setoran</span>
        </a>
        <a href="laporan.php" class="flex flex-col items-center p-1 text-emerald-400 font-medium">
            <i class="fa-solid fa-file-invoice text-lg"></i>
            <span class="text-[10px] mt-0.5">Laporan</span>
        </a>
        <button @click="mobileMenuOpen = true" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-bars text-lg"></i>
            <span class="text-[10px] mt-0.5">Menu</span>
        </button>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col min-w-0 overflow-x-hidden">
        
        <!-- HEADER TOP -->
        <header class="bg-white/80 backdrop-blur-md border-b border-slate-200 px-4 sm:px-6 py-4 flex items-center justify-between sticky top-0 z-20 no-print">
            <div class="flex items-center space-x-3">
                <button @click="mobileMenuOpen = true" class="md:hidden text-slate-600 hover:text-slate-900 p-2 rounded-lg bg-slate-100">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-800">Laporan Hafalan Santri</h2>
                    <p class="text-xs text-slate-500 hidden sm:block">Rekap seluruh perkembangan capaian hafalan</p>
                </div>
            </div>
            
            <button onclick="window.print()" class="no-print px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs font-semibold shadow-md shadow-emerald-600/20 transition-all flex items-center space-x-2">
                <i class="fa-solid fa-print"></i>
                <span class="hidden sm:inline">Cetak Laporan</span>
            </button>
        </header>

        <!-- CONTENT BODY -->
        <div class="p-4 sm:p-6 space-y-6 animate-fade-in">

            <!-- FILTER & SEARCH BAR -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm no-print">
                <form method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                    
                    <!-- Search Input -->
                    <div class="sm:col-span-6 lg:col-span-5 relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" 
                               placeholder="Cari NIS atau Nama Santri..." 
                               class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                    </div>

                    <!-- Dropdown Filter Halaqah -->
                    <div class="sm:col-span-4 lg:col-span-4">
                        <select name="halaqah_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                            <option value="">-- Semua Halaqah --</option>
                            <?php while ($h = mysqli_fetch_assoc($halaqah_options)): ?>
                                <option value="<?php echo $h['id']; ?>" <?php echo $filter_halaqah == $h['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($h['nama_halaqah']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="sm:col-span-2 lg:col-span-3 flex items-center space-x-2">
                        <button type="submit" class="flex-1 py-2 px-3 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition-all flex items-center justify-center space-x-1">
                            <i class="fa-solid fa-filter text-[10px]"></i>
                            <span>Filter</span>
                        </button>
                        <?php if(!empty($filter_halaqah) || !empty($search_query)): ?>
                            <a href="laporan.php" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs transition-all" title="Reset Filter">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                </form>
            </div>

            <!-- CARD REKAP / PRINTER AREA -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden p-4 sm:p-6 print-card">
                
                <!-- HEADER LAPORAN (Selalu muncul di cetakan PDF/Print) -->
                <div class="text-center mb-6 pb-4 border-b border-slate-200 print-header">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-900 uppercase tracking-wide">Laporan Rekapitulasi Capaian Hafalan Al-Qur'an</h1>
                    <p class="text-xs text-slate-500 mt-1">Sistem Informasi Monitoring Hafalan Santri (E-Hafalan)</p>
                </div>

                <!-- DESKTOP TABLE VIEW (hidden di HP/Mobile) -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 text-[11px] uppercase tracking-wider border-b border-slate-200">
                                <th class="py-3 px-4 font-semibold w-12 text-center">No</th>
                                <th class="py-3 px-4 font-semibold">NIS</th>
                                <th class="py-3 px-4 font-semibold">Nama Santri</th>
                                <th class="py-3 px-4 font-semibold">Halaqah</th>
                                <th class="py-3 px-4 font-semibold text-center">Setoran</th>
                                <th class="py-3 px-4 font-semibold text-right">Total Hafalan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php 
                            if (mysqli_num_rows($laporan_data) > 0):
                                $no = 1; 
                                while ($row = mysqli_fetch_assoc($laporan_data)): 
                            ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3 px-4 text-xs font-semibold text-center text-slate-400"><?php echo $no++; ?></td>
                                    <td class="py-3 px-4 font-mono text-xs text-slate-500"><?php echo htmlspecialchars($row['nis']); ?></td>
                                    <td class="py-3 px-4 font-medium text-slate-800"><?php echo htmlspecialchars($row['nama_santri']); ?></td>
                                    <td class="py-3 px-4 text-xs text-slate-600">
                                        <span class="px-2.5 py-1 bg-slate-100 rounded-lg border border-slate-200/60 font-medium">
                                            <?php echo htmlspecialchars($row['nama_halaqah'] ?: '-'); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-center font-semibold text-slate-600">
                                        <?php echo $row['total_setoran']; ?> Kali
                                    </td>
                                    <td class="py-3 px-4 font-bold text-right text-emerald-600">
                                        <?php echo $row['total_hafalan']; ?> Juz
                                    </td>
                                </tr>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                        <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300 block"></i>
                                        Tidak ada data laporan hafalan yang cocok.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- MOBILE CARD VIEW (Khusus Tampilan Mobile HP) -->
                <div class="sm:hidden space-y-3 no-print">
                    <?php 
                    mysqli_data_seek($laporan_data, 0); // Reset pointer loop
                    if (mysqli_num_rows($laporan_data) > 0):
                        $no_m = 1;
                        while ($row_m = mysqli_fetch_assoc($laporan_data)):
                    ?>
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2">
                            <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                                <span class="text-[10px] font-mono text-slate-400">#<?php echo sprintf('%02d', $no_m++); ?> | NIS: <?php echo htmlspecialchars($row_m['nis']); ?></span>
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 font-bold text-xs rounded-full">
                                    <?php echo $row_m['total_hafalan']; ?> Juz
                                </span>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($row_m['nama_santri']); ?></h4>
                                <div class="flex items-center space-x-2 mt-1 text-xs text-slate-500">
                                    <span class="inline-flex items-center"><i class="fa-solid fa-users text-[10px] mr-1 text-slate-400"></i> <?php echo htmlspecialchars($row_m['nama_halaqah'] ?: '-'); ?></span>
                                    <span>•</span>
                                    <span class="inline-flex items-center"><i class="fa-solid fa-clock-rotate-left text-[10px] mr-1 text-slate-400"></i> <?php echo $row_m['total_setoran']; ?> Setoran</span>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <div class="text-center py-6 text-slate-400 text-xs">
                            <i class="fa-solid fa-inbox text-2xl mb-1 block text-slate-300"></i>
                            Tidak ada data ditemukannya.
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    </main>

</body>
</html>