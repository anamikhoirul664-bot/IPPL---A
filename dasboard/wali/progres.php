<?php
session_start();
require_once '../../config/koneksi.php';

// 1. Proteksi Halaman: Hanya Role Wali yang bisa akses
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'wali') {
    header("Location: ../../login.php");
    exit();
}

$wali_id = $_SESSION['user_id'];
$nama_wali = $_SESSION['nama'];

// 2. Query Ambil Data Santri yang Terhubung dengan Wali Ini
$query_santri = mysqli_query($koneksi, "SELECT * FROM santri WHERE wali_id = '$wali_id' LIMIT 1");
$santri = mysqli_fetch_assoc($query_santri);
$santri_id = $santri['id'] ?? 0;

// 3. Query Ambil Statistik Progres Setoran
$total_setoran = 0;
$total_lulus = 0;

if ($santri_id > 0) {
    // Hitung total setoran
    $q_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM setoran WHERE santri_id = '$santri_id'");
    $r_total = mysqli_fetch_assoc($q_total);
    $total_setoran = $r_total['total'] ?? 0;

    // Hitung setoran yang berstatus Lulus / Selesai
    $q_lulus = mysqli_query($koneksi, "SELECT COUNT(DISTINCT surah) as total_surah FROM setoran WHERE santri_id = '$santri_id' AND status = 'Lulus'");
    $r_lulus = mysqli_fetch_assoc($q_lulus);
    $total_lulus = $r_lulus['total_surah'] ?? 0;
}

// Target Juz 30 terdiri dari 37 Surah
$target_surah = 37;
$persentase = $target_surah > 0 ? round(($total_lulus / $target_surah) * 100) : 0;
if ($persentase > 100) $persentase = 100;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progres Hafalan - E-Hafalan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> 
        body { font-family: 'Poppins', sans-serif; } 
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Progress Bar Animation */
        @keyframes fillProgress {
            from { width: 0%; }
        }
        .animate-progress {
            animation: fillProgress 1.5s ease-out forwards;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 h-screen flex overflow-hidden">

    <!-- Overlay for Mobile Sidebar -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 z-20 hidden lg:hidden backdrop-blur-sm transition-opacity" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="bg-gradient-to-b from-emerald-900 to-emerald-950 text-white w-72 min-h-screen p-5 flex flex-col justify-between fixed lg:relative z-30 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out shadow-2xl lg:shadow-none border-r border-emerald-800/50">
        <div>
            <!-- Logo -->
            <div class="flex items-center justify-between mb-10 px-2 mt-2 lg:mt-0">
                <div class="flex items-center space-x-3">
                    <div class="bg-emerald-500/20 p-2 rounded-xl border border-emerald-400/30">
                        <i class="fa-solid fa-quran text-2xl text-emerald-400"></i>
                    </div>
                    <span class="text-xl font-bold tracking-wide">E-Hafalan</span>
                </div>
                <!-- Close Button Mobile -->
                <button onclick="toggleSidebar()" class="lg:hidden text-emerald-300 hover:text-white">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </div>

            <!-- Menu -->
            <div class="text-xs font-semibold text-emerald-400/70 uppercase tracking-wider mb-3 px-3">Menu Utama</div>
            <nav class="space-y-1.5">
                <a href="dasboard.php" class="flex items-center space-x-3 text-emerald-100/70 hover:bg-emerald-800/50 hover:text-white px-4 py-3.5 rounded-xl font-medium transition-all duration-200 hover:pl-5 group">
                    <i class="fa-solid fa-chart-pie w-5 group-hover:text-emerald-400 transition-colors"></i>
                    <span>Dashboard</span>
                </a>
                <!-- Active Menu -->
                <a href="progres.php" class="flex items-center space-x-3 bg-emerald-500/20 text-white px-4 py-3.5 rounded-xl font-medium border border-emerald-400/20 shadow-inner">
                    <i class="fa-solid fa-bars-progress w-5 text-emerald-400"></i>
                    <span>Progres Hafalan</span>
                </a>
                <a href="nilai.php" class="flex items-center space-x-3 text-emerald-100/70 hover:bg-emerald-800/50 hover:text-white px-4 py-3.5 rounded-xl font-medium transition-all duration-200 hover:pl-5 group">
                    <i class="fa-solid fa-star w-5 group-hover:text-emerald-400 transition-colors"></i>
                    <span>Nilai & Penilaian</span>
                </a>
                <a href="riwayat.php" class="flex items-center space-x-3 text-emerald-100/70 hover:bg-emerald-800/50 hover:text-white px-4 py-3.5 rounded-xl font-medium transition-all duration-200 hover:pl-5 group">
                    <i class="fa-solid fa-clock-rotate-left w-5 group-hover:text-emerald-400 transition-colors"></i>
                    <span>Riwayat Setoran</span>
                </a>
            </nav>
        </div>

        <a href="../../logout.php" class="flex items-center space-x-3 bg-red-500/10 hover:bg-red-500 hover:text-white text-red-400 px-4 py-3.5 rounded-xl font-medium transition-all duration-200 border border-red-500/20 hover:shadow-lg hover:shadow-red-500/20 mt-8">
            <i class="fa-solid fa-right-from-bracket w-5"></i>
            <span>Keluar Sistem</span>
        </a>
    </aside>

    <!-- Main Wrapper -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- Top Navbar -->
        <header class="bg-white border-b border-slate-100 shadow-sm px-6 py-4 flex justify-between items-center z-10 flex-shrink-0">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg bg-slate-50 text-slate-600 hover:bg-slate-100 transition-colors">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="hidden sm:block">
                    <h1 class="text-xl font-bold text-slate-800">Progres Hafalan</h1>
                    <p class="text-xs text-slate-500 font-medium">Pantau pencapaian hafalan harian</p>
                </div>
            </div>

            <!-- Profile Info -->
            <div class="flex items-center space-x-3 bg-slate-50 px-3 py-2 rounded-full border border-slate-100 hover:shadow-md transition-shadow cursor-pointer">
                <div class="text-right hidden sm:block px-2">
                    <p class="text-sm font-bold text-slate-700 leading-tight"><?php echo htmlspecialchars($nama_wali); ?></p>
                    <p class="text-[10px] text-emerald-600 font-semibold uppercase tracking-wider">Wali Santri</p>
                </div>
                <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-400 text-white flex items-center justify-center font-bold shadow-sm">
                    <i class="fa-solid fa-user-tie text-sm"></i>
                </div>
            </div>
        </header>

        <!-- Main Content (Scrollable) -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-4 sm:p-8">
            
            <!-- Mobile Header Content -->
            <div class="mb-6 sm:mb-8">
                <h1 class="text-2xl font-bold text-slate-800 block sm:hidden mb-1">Progres Hafalan</h1>
                <p class="text-sm text-slate-500">
                    Perkembangan capaian hafalan <span class="font-semibold text-emerald-700 bg-emerald-100/50 px-2 py-0.5 rounded-md"><?php echo htmlspecialchars($santri['nama'] ?? 'Santri'); ?></span>
                </p>
            </div>

            <!-- Progress Summary Card -->
            <div class="bg-white p-5 sm:p-8 rounded-2xl shadow-sm border border-slate-100 mb-6 sm:mb-8 relative overflow-hidden group">
                <!-- Decorative element -->
                <div class="absolute -right-10 -top-10 w-32 h-32 bg-emerald-50 rounded-full blur-3xl opacity-60 group-hover:bg-teal-50 transition-colors duration-700"></div>

                <div class="relative z-10">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-5 sm:mb-6 gap-3">
                        <div>
                            <span class="inline-block px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-bold uppercase tracking-wider mb-3">Statistik Utama</span>
                            <h3 class="font-bold text-slate-800 text-xl sm:text-2xl">Target Capaian <span class="text-emerald-600">Juz 30</span></h3>
                            <p class="text-sm text-slate-500 mt-1 flex items-center gap-2">
                                <i class="fa-solid fa-bullseye text-amber-500"></i> Telah menyelesaikan <strong class="text-slate-700"><?php echo $total_lulus; ?></strong> dari <strong class="text-slate-700"><?php echo $target_surah; ?></strong> Surah
                            </p>
                        </div>
                        <div class="flex items-baseline gap-1 bg-emerald-50 px-4 py-2 rounded-xl border border-emerald-100 self-start md:self-auto">
                            <span class="text-3xl sm:text-4xl font-extrabold text-emerald-600"><?php echo $persentase; ?></span>
                            <span class="text-emerald-700 font-bold text-lg">%</span>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="w-full bg-slate-100 rounded-full h-4 sm:h-5 overflow-hidden mb-6 border border-slate-200/60 shadow-inner">
                        <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-full rounded-full relative animate-progress shadow-[0_0_10px_rgba(16,185,129,0.4)]" style="width: <?php echo $persentase; ?>%;">
                            <!-- Shimmer effect inside progress bar -->
                            <div class="absolute top-0 inset-x-0 h-full w-full bg-white/20 animate-pulse"></div>
                        </div>
                    </div>

                    <!-- Grid Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-6 border-t border-slate-100">
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100/50 hover:bg-slate-100 transition-colors">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="fa-solid fa-list-check text-slate-400"></i>
                                <span class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">Total Setoran</span>
                            </div>
                            <span class="text-xl font-bold text-slate-800"><?php echo $total_setoran; ?> <span class="text-sm font-medium text-slate-500">Kali</span></span>
                        </div>
                        
                        <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100/50 hover:bg-emerald-100/80 transition-colors">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="fa-solid fa-check-double text-emerald-500"></i>
                                <span class="text-[11px] text-emerald-700 font-bold uppercase tracking-wider">Surah Tuntas</span>
                            </div>
                            <span class="text-xl font-bold text-emerald-800"><?php echo $total_lulus; ?> <span class="text-sm font-medium text-emerald-600/70">Surah</span></span>
                        </div>
                        
                        <div class="p-4 bg-amber-50 rounded-xl border border-amber-100/50 hover:bg-amber-100/80 transition-colors">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="fa-regular fa-hourglass-half text-amber-500"></i>
                                <span class="text-[11px] text-amber-700 font-bold uppercase tracking-wider">Sisa Surah</span>
                            </div>
                            <span class="text-xl font-bold text-amber-800"><?php echo max(0, $target_surah - $total_lulus); ?> <span class="text-sm font-medium text-amber-600/70">Surah</span></span>
                        </div>
                        
                        <div class="p-4 bg-blue-50 rounded-xl border border-blue-100/50 hover:bg-blue-100/80 transition-colors">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="fa-solid fa-book-quran text-blue-500"></i>
                                <span class="text-[11px] text-blue-700 font-bold uppercase tracking-wider">Target Utama</span>
                            </div>
                            <span class="text-xl font-bold text-blue-800">Juz 30</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Rincian Surah -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                            <i class="fa-solid fa-list-ol"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-slate-800">Daftar Status Hafalan</h3>
                            <p class="text-xs text-slate-500">Rincian per surah untuk Juz 30</p>
                        </div>
                    </div>
                    <span class="text-[11px] sm:text-xs bg-slate-50 border border-slate-200 text-slate-500 font-semibold px-3 py-1.5 rounded-lg flex items-center gap-2">
                        <i class="fa-solid fa-rotate text-emerald-500 animate-spin-slow"></i> Auto Update
                    </span>
                </div>

                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left text-sm text-slate-600 min-w-[600px]">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider font-semibold border-b border-slate-100">
                            <tr>
                                <th class="p-4 pl-6 w-16">No</th>
                                <th class="p-4">Nama Surah</th>
                                <th class="p-4">Keterangan</th>
                                <th class="p-4">Status Setoran</th>
                                <th class="p-4 pr-6">Tanggal Update</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php
                            // Ambil daftar setoran santri dari database
                            $q_progres = mysqli_query($koneksi, "
                                SELECT surah, MAX(tanggal) as tgl_terakhir, status 
                                FROM setoran 
                                WHERE santri_id = '$santri_id' 
                                GROUP BY surah 
                                ORDER BY id DESC
                            ");

                            if ($q_progres && mysqli_num_rows($q_progres) > 0) {
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($q_progres)) {
                                    $is_lulus = strtolower($row['status']) == 'lulus';
                                    ?>
                                    <tr class="hover:bg-slate-50/70 transition-colors duration-200">
                                        <td class="p-4 pl-6 font-medium text-slate-400"><?php echo str_pad($no++, 2, '0', STR_PAD_LEFT); ?></td>
                                        <td class="p-4 font-bold text-slate-800 text-base"><?php echo htmlspecialchars($row['surah']); ?></td>
                                        <td class="p-4">
                                            <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2.5 py-1 rounded-md border border-slate-200">Lengkap</span>
                                        </td>
                                        <td class="p-4">
                                            <?php if ($is_lulus): ?>
                                                <span class="inline-flex items-center px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-bold shadow-sm">
                                                    <i class="fa-solid fa-circle-check mr-2 text-emerald-500"></i> TUNTAS / LULUS
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-3 py-1.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-xs font-bold shadow-sm">
                                                    <i class="fa-solid fa-rotate-right mr-2 text-amber-500"></i> MENGULANG
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 pr-6 text-xs font-medium text-slate-500">
                                            <i class="fa-regular fa-calendar-check mr-1.5 text-slate-400"></i> 
                                            <?php echo date('d M Y', strtotime($row['tgl_terakhir'])); ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="5" class="p-10 text-center">
                                        <div class="flex flex-col items-center justify-center text-slate-400">
                                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3 border border-slate-100">
                                                <i class="fa-solid fa-folder-open text-2xl text-slate-300"></i>
                                            </div>
                                            <p class="text-sm font-bold text-slate-600 mb-1">Belum Ada Progres</p>
                                            <p class="text-xs text-slate-500">Data hafalan surah akan muncul otomatis setelah Ustadz melakukan input.</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Script for Mobile Sidebar Toggle -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>
</body>
</html>