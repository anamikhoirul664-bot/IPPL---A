<?php
session_start();
require_once '../../config/koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'pengasuh') {
    header("Location: ../../login.php");
    exit();
}

$nama_pengasuh = $_SESSION['nama'] ?? $_SESSION['nama_user'] ?? 'Pengasuh';

// Hitung Ringkasan Data
$q_santri = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM santri");
$total_santri = mysqli_fetch_assoc($q_santri)['total'] ?? 0;

$q_ustadz = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM ustadz");
$total_ustadz = mysqli_fetch_assoc($q_ustadz)['total'] ?? 0;

$q_setoran = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM setoran");
$total_setoran = mysqli_fetch_assoc($q_setoran)['total'] ?? 0;

// Menghitung setoran yang lancar (Sangat Lancar / Lancar)
$q_lulus = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM setoran WHERE kelancaran IN ('Sangat Lancar', 'Lancar')");
$total_lulus = mysqli_fetch_assoc($q_lulus)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengasuh - E-Hafalan</title>
    
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
                            sidebar: '#061E29',     /* Dark Teal/Navy */
                            active: '#0D9488',      /* Tosca/Teal Active */
                            activeHover: '#0F766E',
                            bg: '#F8FAFC',          /* Slate Light Background */
                            card: '#FFFFFF',
                            textNav: '#94A3B8',
                        }
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(16px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        pulseGlow: {
                            '0%, 100%': { opacity: '0.4' },
                            '50%': { opacity: '0.8' },
                        }
                    },
                    animation: {
                        'fade-in': 'fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                        'pulse-glow': 'pulseGlow 3s infinite ease-in-out',
                    }
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #061E29; }
        ::-webkit-scrollbar-thumb { background: #1E293B; border-radius: 10px; }
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
           class="fixed md:static inset-y-0 left-0 z-50 w-64 bg-app-sidebar text-slate-300 min-h-screen p-4 flex flex-col justify-between transition-transform duration-300 ease-in-out md:translate-x-0 border-r border-slate-800/50 shadow-2xl md:shadow-none">
        
        <div class="overflow-y-auto max-h-[calc(100vh-90px)] pr-1">
            <!-- Header Brand / Logo -->
            <div class="flex items-center justify-between mb-8 px-2 pt-2">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-teal-600 to-teal-400 text-white flex items-center justify-center font-bold text-lg shadow-lg shadow-teal-900/30">
                        <i class="fa-solid fa-quran"></i>
                    </div>
                    <div>
                        <span class="text-base font-extrabold text-white tracking-wide block leading-tight">E-Hafalan</span>
                        <span class="text-[10px] font-semibold text-teal-400 tracking-wider">PANEL PENGASUH</span>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-1.5">
                <a href="dashboard.php" class="flex items-center space-x-3 bg-app-active text-white px-4 py-3 rounded-xl font-semibold text-xs shadow-md shadow-teal-900/20 transition-all duration-200">
                    <i class="fa-solid fa-gauge-high w-5 text-center text-sm"></i>
                    <span>Dashboard</span>
                </a>

                <div class="pt-5 pb-1 px-4">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">AKADEMIK & HAFALAN</span>
                </div>

                <a href="monitoring.php" class="flex items-center space-x-3 text-app-textNav hover:text-white hover:bg-slate-800/60 px-4 py-2.5 rounded-xl font-medium text-xs transition-all duration-200 group">
                    <i class="fa-solid fa-eye w-5 text-center group-hover:text-teal-400 transition-colors"></i>
                    <span>Monitoring Setoran</span>
                </a>

                <a href="statistik.php" class="flex items-center space-x-3 text-app-textNav hover:text-white hover:bg-slate-800/60 px-4 py-2.5 rounded-xl font-medium text-xs transition-all duration-200 group">
                    <i class="fa-solid fa-chart-line w-5 text-center group-hover:text-teal-400 transition-colors"></i>
                    <span>Statistik Hafalan</span>
                </a>

                <div class="pt-5 pb-1 px-4">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">LAPORAN & INFO</span>
                </div>

                <a href="laporan.php" class="flex items-center space-x-3 text-app-textNav hover:text-white hover:bg-slate-800/60 px-4 py-2.5 rounded-xl font-medium text-xs transition-all duration-200 group">
                    <i class="fa-solid fa-file-lines w-5 text-center group-hover:text-teal-400 transition-colors"></i>
                    <span>Laporan Hafalan</span>
                </a>
            </nav>
        </div>

        <!-- User Profile Card (Di Bawah Sidebar) -->
        <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between px-2">
            <div class="flex items-center space-x-3 overflow-hidden">
                <div class="w-9 h-9 rounded-xl bg-teal-600/30 border border-teal-500/30 text-teal-300 font-bold flex items-center justify-center text-xs shadow-inner">
                    <?php echo strtoupper(substr($nama_pengasuh, 0, 1)); ?>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-semibold text-white truncate max-w-[110px]"><?php echo htmlspecialchars($nama_pengasuh); ?></p>
                    <p class="text-[9px] text-teal-400 font-bold uppercase tracking-wider">Pengasuh</p>
                </div>
            </div>
            <button @click="logoutModalOpen = true" title="Keluar" class="w-8 h-8 rounded-lg bg-slate-800/80 hover:bg-rose-600/90 text-slate-400 hover:text-white flex items-center justify-center transition-all duration-200 text-xs shadow-sm">
                <i class="fa-solid fa-right-from-bracket"></i>
            </button>
        </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 min-w-0 flex flex-col min-h-screen">
        
        <!-- TOPBAR MOBILE -->
        <div class="md:hidden bg-app-sidebar text-white p-4 flex justify-between items-center border-b border-slate-800 shadow-md">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-app-active flex items-center justify-center text-white font-bold">
                    <i class="fa-solid fa-quran text-sm"></i>
                </div>
                <span class="font-bold text-sm tracking-wide">E-Hafalan</span>
            </div>
            <button @click="sidebarOpen = true" class="p-2 bg-slate-800 text-slate-200 rounded-lg hover:bg-slate-700 transition-colors">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
        </div>

        <div class="p-4 sm:p-8 lg:p-8 flex-1 max-w-7xl w-full mx-auto animate-fade-in space-y-6">
            
            <!-- HEADER -->
            <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Ringkasan Dashboard</h1>
                    <p class="text-xs text-slate-500 mt-1">Selamat datang kembali, <span class="font-semibold text-teal-700">KH. <?php echo htmlspecialchars($nama_pengasuh); ?></span>!</p>
                </div>

                <div class="flex items-center space-x-3">
                    <a href="monitoring.php" class="inline-flex items-center space-x-2 bg-app-active hover:bg-app-activeHover text-white px-4 py-2.5 rounded-xl text-xs font-semibold shadow-md shadow-teal-600/20 transition-all duration-150 transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-eye text-xs"></i>
                        <span>Lihat Monitoring</span>
                    </a>
                </div>
            </header>

            <!-- STATS CARDS WITH COUNTER ANIMATION -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                
                <!-- Card 1: Total Santri -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 flex items-center justify-between hover:shadow-md hover:border-emerald-200 transition-all duration-300 transform hover:-translate-y-1">
                    <div>
                        <p class="text-xs font-medium text-slate-400 mb-1">Total Santri</p>
                        <h3 class="text-2xl font-bold text-slate-900">
                            <span class="count-up" data-target="<?php echo $total_santri; ?>">0</span>
                            <span class="text-xs font-normal text-slate-400 ml-0.5">Orang</span>
                        </h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shadow-inner">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                </div>

                <!-- Card 2: Total Ustadz -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 flex items-center justify-between hover:shadow-md hover:border-teal-200 transition-all duration-300 transform hover:-translate-y-1">
                    <div>
                        <p class="text-xs font-medium text-slate-400 mb-1">Total Ustadz</p>
                        <h3 class="text-2xl font-bold text-slate-900">
                            <span class="count-up" data-target="<?php echo $total_ustadz; ?>">0</span>
                            <span class="text-xs font-normal text-slate-400 ml-0.5">Pengajar</span>
                        </h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg shadow-inner">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                </div>

                <!-- Card 3: Aktivitas Setoran -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 flex items-center justify-between hover:shadow-md hover:border-purple-200 transition-all duration-300 transform hover:-translate-y-1">
                    <div>
                        <p class="text-xs font-medium text-slate-400 mb-1">Aktivitas Setoran</p>
                        <h3 class="text-2xl font-bold text-slate-900">
                            <span class="count-up" data-target="<?php echo $total_setoran; ?>">0</span>
                            <span class="text-xs font-normal text-slate-400 ml-0.5">Kali</span>
                        </h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg shadow-inner">
                        <i class="fa-solid fa-book-quran"></i>
                    </div>
                </div>

                <!-- Card 4: Setoran Lancar -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 flex items-center justify-between hover:shadow-md hover:border-amber-200 transition-all duration-300 transform hover:-translate-y-1">
                    <div>
                        <p class="text-xs font-medium text-slate-400 mb-1">Setoran Lancar</p>
                        <h3 class="text-2xl font-bold text-slate-900">
                            <span class="count-up" data-target="<?php echo $total_lulus; ?>">0</span>
                            <span class="text-xs font-normal text-slate-400 ml-0.5">Capaian</span>
                        </h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shadow-inner">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>

            </div>

            <!-- GRAFIK RINGKASAN DATA (TAMBAHAN FITUR INTERAKTIF) -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Visualisasi Perbandingan Hafalan</h3>
                        <p class="text-[11px] text-slate-400">Ringkasan total aktivitas dan kelancaran setoran santri</p>
                    </div>
                    <span class="px-2.5 py-1 bg-teal-50 text-teal-700 text-[10px] font-bold rounded-full">Realtime</span>
                </div>
                <div class="h-56 relative w-full">
                    <canvas id="hafalanChart"></canvas>
                </div>
            </div>

            <!-- ACTION BANNER -->
            <div class="relative overflow-hidden bg-gradient-to-r from-slate-900 via-app-sidebar to-slate-900 rounded-2xl p-6 text-white flex flex-col md:flex-row justify-between items-center shadow-lg border border-slate-800">
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-teal-500/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="mb-4 md:mb-0 max-w-xl z-10">
                    <h2 class="text-lg font-bold mb-1 flex items-center space-x-2">
                        <span>Monitoring & Laporan Pesantren</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    </h2>
                    <p class="text-slate-400 text-xs leading-relaxed">Pantau perkembangan santri secara menyeluruh atau unduh laporan pencapaian harian dan bulanan.</p>
                </div>
                <div class="flex items-center space-x-3 w-full md:w-auto z-10">
                    <a href="monitoring.php" class="flex-1 md:flex-none text-center px-5 py-2.5 bg-app-active hover:bg-app-activeHover text-white font-semibold rounded-xl text-xs transition-all duration-150 shadow-md transform hover:-translate-y-0.5">
                        Monitoring
                    </a>
                    <a href="laporan.php" class="flex-1 md:flex-none text-center px-5 py-2.5 bg-white/10 hover:bg-white text-white hover:text-slate-900 border border-white/20 font-semibold rounded-xl text-xs transition-all duration-150 shadow-sm backdrop-blur-sm">
                        Unduh Laporan
                    </a>
                </div>
            </div>

        </div>
    </main>

    <!-- MODAL CONFIRMATION LOGOUT -->
    <div x-show="logoutModalOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="logoutModalOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4">
         
        <div x-show="logoutModalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-slate-900 border border-slate-800 rounded-2xl p-6 w-full max-w-sm shadow-2xl text-center">
            
            <div class="w-14 h-14 bg-red-500/10 text-red-500 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-red-500/20">
                <i class="fa-solid fa-right-from-bracket text-2xl"></i>
            </div>

            <h3 class="text-lg font-bold text-white mb-1">Konfirmasi Logout</h3>
            <p class="text-xs text-slate-400 mb-6">Apakah Anda yakin ingin keluar dari sistem E-Hafalan?</p>

            <div class="flex items-center space-x-3">
                <button type="button" @click="logoutModalOpen = false" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:bg-slate-800 font-semibold text-xs transition-all">
                    Batal
                </button>
                <a href="../../logout.php" class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-semibold text-xs transition-all shadow-lg shadow-red-600/30 text-center">
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

            // 2. Chart.js untuk Grafik Statistik Hafalan
            const ctx = document.getElementById('hafalanChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Santri', 'Ustadz', 'Total Setoran', 'Setoran Lancar'],
                    datasets: [{
                        label: 'Jumlah Data',
                        data: [
                            <?php echo $total_santri; ?>, 
                            <?php echo $total_ustadz; ?>, 
                            <?php echo $total_setoran; ?>, 
                            <?php echo $total_lulus; ?>
                        ],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.85)',
                            'rgba(13, 148, 136, 0.85)',
                            'rgba(147, 51, 234, 0.85)',
                            'rgba(245, 158, 11, 0.85)'
                        ],
                        borderRadius: 8,
                        borderSkipped: false
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