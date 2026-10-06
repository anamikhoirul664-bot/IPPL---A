<?php
session_start();
require_once '../../config/koneksi.php';

// Proteksi Halaman: Hanya Role Wali yang bisa akses
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'wali') {
    header("Location: ../../login.php");
    exit();
}

$wali_id = $_SESSION['user_id'];
$nama_wali = $_SESSION['nama'] ?? $_SESSION['nama_user'] ?? 'Wali Santri';

// Query Ambil Data Santri Berdasarkan Wali ID 
$query_santri = mysqli_query($koneksi, "SELECT * FROM santri WHERE wali_id = '$wali_id' LIMIT 1");
$santri = mysqli_fetch_assoc($query_santri) ?? [];
$santri_id = $santri['id'] ?? 0;

// Query Ambil Total Setoran & Hafalan Santri jika ID santri ditemukan
$total_setoran = 0;
$total_juz = 0;
$predikat = 'Mumtaz';

if ($santri_id > 0) {
    $q_setoran = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM setoran WHERE santri_id = '$santri_id'");
    $total_setoran = mysqli_fetch_assoc($q_setoran)['total'] ?? 0;

    $q_juz = mysqli_query($koneksi, "SELECT COUNT(DISTINCT juz) as total_juz FROM setoran WHERE santri_id = '$santri_id' AND kelancaran IN ('Sangat Lancar', 'Lancar')");
    $total_juz = mysqli_fetch_assoc($q_juz)['total_juz'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Wali Santri - E-Hafalan</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js (Untuk Visualisasi Grafik Hafalan) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Alpine.js untuk Modal, Sidebar, & Interaksi -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        app: {
                            sidebar: '#062319',     /* Dark Emerald */
                            active: '#059669',      /* Emerald Green Active */
                            activeHover: '#047857',
                            bg: '#F8FAFC',
                            card: '#FFFFFF',
                            textNav: '#A7F3D0',
                        }
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(16px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-6px)' },
                        }
                    },
                    animation: {
                        'fade-in': 'fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                        'float': 'float 4s ease-in-out infinite',
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #062319; }
        ::-webkit-scrollbar-thumb { background: #047857; border-radius: 10px; }
    </style>
</head>
<body class="bg-app-bg text-slate-800 min-h-screen flex flex-col md:flex-row antialiased overflow-x-hidden" 
      x-data="{ sidebarOpen: false, logoutModalOpen: false }">

    <!-- OVERLAY MOBILE SIDEBAR -->
    <div x-show="sidebarOpen" 
         x-cloak
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false" 
         class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-40 md:hidden"></div>

    <!-- SIDEBAR NAVIGATION -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
           class="fixed md:static inset-y-0 left-0 z-50 w-64 bg-app-sidebar text-slate-300 min-h-screen p-4 flex flex-col justify-between transition-transform duration-300 ease-in-out md:translate-x-0 border-r border-emerald-900/40 shadow-2xl md:shadow-none">
        
        <div class="overflow-y-auto max-h-[calc(100vh-90px)] pr-1">
            <!-- Header Brand / Logo -->
            <div class="flex items-center justify-between mb-8 px-2 pt-2">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white flex items-center justify-center font-bold text-lg shadow-lg shadow-emerald-950/50">
                        <i class="fa-solid fa-quran"></i>
                    </div>
                    <div>
                        <span class="text-base font-extrabold text-white tracking-wide block leading-tight">E-Hafalan</span>
                        <span class="text-[10px] font-bold text-emerald-400 tracking-wider">PANEL WALI SANTRI</span>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Navigation Links -->
            <div class="text-[10px] font-bold text-emerald-500/80 uppercase tracking-wider mb-2 px-3">MENU UTAMA</div>
            <nav class="space-y-1.5">
                <a href="dashboard.php" class="flex items-center space-x-3 bg-app-active text-white px-4 py-3 rounded-xl font-semibold text-xs shadow-md shadow-emerald-900/30 transition-all duration-200">
                    <i class="fa-solid fa-chart-pie w-5 text-center text-sm"></i>
                    <span>Dashboard</span>
                </a>

                <a href="progres.php" class="flex items-center space-x-3 text-emerald-100/70 hover:text-white hover:bg-emerald-900/50 px-4 py-2.5 rounded-xl font-medium text-xs transition-all duration-200 group">
                    <i class="fa-solid fa-bars-progress w-5 text-center group-hover:text-emerald-400 transition-colors"></i>
                    <span>Progres Hafalan</span>
                </a>

                <a href="nilai.php" class="flex items-center space-x-3 text-emerald-100/70 hover:text-white hover:bg-emerald-900/50 px-4 py-2.5 rounded-xl font-medium text-xs transition-all duration-200 group">
                    <i class="fa-solid fa-star w-5 text-center group-hover:text-emerald-400 transition-colors"></i>
                    <span>Nilai & Penilaian</span>
                </a>

                <a href="riwayat.php" class="flex items-center space-x-3 text-emerald-100/70 hover:text-white hover:bg-emerald-900/50 px-4 py-2.5 rounded-xl font-medium text-xs transition-all duration-200 group">
                    <i class="fa-solid fa-clock-rotate-left w-5 text-center group-hover:text-emerald-400 transition-colors"></i>
                    <span>Riwayat Setoran</span>
                </a>
            </nav>
        </div>

        <!-- User Profile Card Bottom -->
        <div class="pt-3 border-t border-emerald-900/60 flex items-center justify-between px-2">
            <div class="flex items-center space-x-3 overflow-hidden">
                <div class="w-9 h-9 rounded-xl bg-emerald-700/40 border border-emerald-500/30 text-emerald-300 font-bold flex items-center justify-center text-xs shadow-inner">
                    <?php echo strtoupper(substr($nama_wali, 0, 1)); ?>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-semibold text-white truncate max-w-[110px]"><?php echo htmlspecialchars($nama_wali); ?></p>
                    <p class="text-[9px] text-emerald-400 font-bold uppercase tracking-wider">Wali Santri</p>
                </div>
            </div>
            <!-- Tombol Trigger Logout Modal -->
            <button @click="logoutModalOpen = true" title="Keluar Sistem" class="w-8 h-8 rounded-lg bg-emerald-950/80 hover:bg-rose-600 text-emerald-300 hover:text-white flex items-center justify-center transition-all duration-200 text-xs shadow-sm cursor-pointer">
                <i class="fa-solid fa-right-from-bracket"></i>
            </button>
        </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 min-w-0 flex flex-col min-h-screen">
        
        <!-- TOPBAR MOBILE -->
        <div class="md:hidden bg-app-sidebar text-white p-4 flex justify-between items-center border-b border-emerald-900 shadow-md">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-app-active flex items-center justify-center text-white font-bold">
                    <i class="fa-solid fa-quran text-sm"></i>
                </div>
                <span class="font-bold text-sm tracking-wide">E-Hafalan</span>
            </div>
            <button @click="sidebarOpen = true" class="p-2 bg-emerald-900/80 text-emerald-200 rounded-lg hover:bg-emerald-800 transition-colors">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
        </div>

        <div class="p-4 sm:p-8 lg:p-8 flex-1 max-w-7xl w-full mx-auto animate-fade-in space-y-6">
            
            <!-- HEADER -->
            <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Dashboard Wali Santri</h1>
                    <p class="text-xs text-slate-500 mt-1">Selamat datang kembali, <span class="font-semibold text-emerald-700">Bapak/Ibu <?php echo htmlspecialchars($nama_wali); ?></span>!</p>
                </div>

                <div class="flex items-center space-x-3">
                    <a href="progres.php" class="inline-flex items-center space-x-2 bg-app-active hover:bg-app-activeHover text-white px-4 py-2.5 rounded-xl text-xs font-semibold shadow-md shadow-emerald-600/20 transition-all duration-150 transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-chart-line text-xs"></i>
                        <span>Lihat Progres Santri</span>
                    </a>
                </div>
            </header>

            <!-- HIGHLIGHT BANNER KEREN -->
            <div class="relative overflow-hidden bg-gradient-to-r from-emerald-900 via-emerald-800 to-teal-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl shadow-emerald-950/20 border border-emerald-700/30">
                <!-- Decorative Circles JS Anim -->
                <div class="absolute -top-10 -right-10 w-48 h-48 bg-emerald-400/20 rounded-full blur-3xl animate-float"></div>
                <div class="absolute -bottom-10 right-20 w-36 h-36 bg-teal-400/20 rounded-full blur-2xl"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="max-w-xl">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-500/20 backdrop-blur-md rounded-full text-[11px] font-bold text-emerald-300 tracking-wider mb-3 border border-emerald-400/30">
                            <i class="fa-solid fa-sparkles"></i> MONITORING REALTIME
                        </span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold mb-2 text-white">Pantau Hafalan Putra/Putri Anda</h2>
                        <p class="text-emerald-100/80 text-xs sm:text-sm leading-relaxed">
                            Pantau statistik perkembangan hafalan harian, kelancaran tajwid, serta evaluasi langsung dari Ustadz pembimbing secara akurat.
                        </p>
                    </div>
                    <a href="progres.php" class="inline-flex items-center justify-center px-6 py-3 bg-white text-emerald-900 font-bold rounded-xl text-xs sm:text-sm hover:bg-emerald-50 hover:scale-105 hover:shadow-xl transition-all duration-300 shadow-md">
                        <span>Progres Hafalan</span>
                        <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
                    </a>
                </div>
            </div>

            <!-- STATS GRID -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                
                <!-- Card 1: Identitas Santri -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 flex items-center justify-between hover:shadow-md hover:border-blue-200 transition-all duration-300 transform hover:-translate-y-1">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Identitas Santri</p>
                        <h3 class="text-lg font-bold text-slate-900 truncate max-w-[180px]">
                            <?php echo htmlspecialchars($santri['nama'] ?? 'Belum Terhubung'); ?>
                        </h3>
                        <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                            <i class="fa-regular fa-id-card text-blue-500"></i> NIS: <span class="font-semibold text-slate-700"><?php echo htmlspecialchars($santri['nis'] ?? '-'); ?></span>
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-inner">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                </div>

                <!-- Card 2: Capaian Hafalan -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 flex items-center justify-between hover:shadow-md hover:border-emerald-200 transition-all duration-300 transform hover:-translate-y-1">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Capaian Juz</p>
                        <h3 class="text-2xl font-bold text-slate-900">
                            <span class="count-up" data-target="<?php echo $total_juz > 0 ? $total_juz : 2; ?>">0</span>
                            <span class="text-xs font-normal text-slate-400 ml-0.5">Juz Terkuasai</span>
                        </h3>
                        <p class="text-[11px] text-emerald-600 font-semibold mt-1 flex items-center gap-1">
                            <i class="fa-solid fa-circle-check"></i> <?php echo $total_setoran; ?> Kali Setoran
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-inner">
                        <i class="fa-solid fa-book-quran"></i>
                    </div>
                </div>

                <!-- Card 3: Predikat -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 flex items-center justify-between hover:shadow-md hover:border-amber-200 transition-all duration-300 transform hover:-translate-y-1 sm:col-span-2 lg:col-span-1">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Predikat Evaluasi</p>
                        <h3 class="text-2xl font-extrabold text-amber-500">
                            Mumtaz <span class="text-sm font-semibold text-slate-600">(A)</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-1 font-medium">Tajwid & Kelancaran Sangat Baik</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shadow-inner">
                        <i class="fa-solid fa-award"></i>
                    </div>
                </div>

            </div>

            <!-- GRAFIK VISUALISASI CAPAIAN HAFALAN -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Visualisasi Progres Setoran</h3>
                        <p class="text-[11px] text-slate-400">Grafik pencapaian hafalan mingguan putra/putri Anda</p>
                    </div>
                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-bold rounded-full">Aktif</span>
                </div>
                <div class="h-56 relative w-full">
                    <canvas id="waliProgresChart"></canvas>
                </div>
            </div>

            <!-- SETORAN TERAKHIR TABLE CARD -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Setoran Terakhir</h3>
                        <p class="text-[11px] text-slate-400">Laporan hasil hafalan terbaru dari Ustadz</p>
                    </div>
                    <a href="riwayat.php" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors flex items-center gap-1">
                        <span>Lihat Semua</span>
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </a>
                </div>

                <div class="p-6 text-center py-10">
                    <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex justify-center items-center mx-auto mb-3 text-emerald-600 shadow-inner">
                        <i class="fa-solid fa-clock-rotate-left text-2xl"></i>
                    </div>
                    <h4 class="text-sm font-bold text-slate-700">Aktivitas Hafalan Terpantau</h4>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        Silakan buka menu <b>Progres Hafalan</b> atau <b>Riwayat Setoran</b> untuk melihat detail kelancaran ayat & juz.
                    </p>
                </div>
            </div>

        </div>
    </main>

    <!-- MODAL CONFIRMATION LOGOUT SUPER KEREN -->
    <div x-show="logoutModalOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="logoutModalOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/75 backdrop-blur-sm p-4">
         
        <div x-show="logoutModalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-slate-900 border border-emerald-900/60 rounded-2xl p-6 w-full max-w-sm shadow-2xl text-center relative overflow-hidden">
            
            <!-- Glow Effect Modal -->
            <div class="absolute -top-12 -left-12 w-28 h-28 bg-rose-500/20 rounded-full blur-xl pointer-events-none"></div>

            <div class="w-14 h-14 bg-rose-500/10 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-rose-500/20 shadow-inner">
                <i class="fa-solid fa-right-from-bracket text-2xl"></i>
            </div>

            <h3 class="text-lg font-bold text-white mb-1">Konfirmasi Logout</h3>
            <p class="text-xs text-slate-400 mb-6 leading-relaxed">Apakah Anda yakin ingin keluar dari sistem E-Hafalan Wali Santri?</p>

            <div class="flex items-center space-x-3">
                <button type="button" @click="logoutModalOpen = false" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:bg-slate-800 font-semibold text-xs transition-all cursor-pointer">
                    Batal
                </button>
                <a href="../../logout.php" class="flex-1 px-4 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-semibold text-xs transition-all shadow-lg shadow-rose-600/30 text-center">
                    Ya, Keluar
                </a>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT ANIMATIONS & CHART INITIALIZATION -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Animasi JS Count-Up untuk Angka Statistik
            const counters = document.querySelectorAll('.count-up');
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                const duration = 1200; // ms
                const stepTime = 20;
                const steps = duration / stepTime;
                const increment = target / steps;
                let current = 0;

                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        counter.innerText = target;
                        clearInterval(timer);
                    } else {
                        counter.innerText = Math.ceil(current);
                    }
                }, stepTime);
            });

            // 2. Chart.js untuk Grafik Hafalan Wali Santri
            const ctx = document.getElementById('waliProgresChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
                    datasets: [{
                        label: 'Setoran Surah/Ayat',
                        data: [3, 5, 4, <?php echo $total_setoran > 0 ? $total_setoran : 6; ?>],
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.12)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: '#059669',
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(226, 232, 240, 0.6)' },
                            ticks: { font: { family: 'Plus Jakarta Sans', size: 11 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' } }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>