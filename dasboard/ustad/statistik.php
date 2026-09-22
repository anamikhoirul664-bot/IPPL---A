<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');
$nama_user = getUserNama();

// Count Metrics
$tot_santri  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM santri"))['c'];
$tot_setoran = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM setoran"))['c'];
$tot_khatam  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM santri WHERE total_hafalan >= 30"))['c'];

// Data Distribusi Hafalan Santri (Contoh Pengelompokan Juz)
$juz_1_5   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM santri WHERE total_hafalan BETWEEN 1 AND 5"))['c'];
$juz_6_15  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM santri WHERE total_hafalan BETWEEN 6 AND 15"))['c'];
$juz_16_29 = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM santri WHERE total_hafalan BETWEEN 16 AND 29"))['c'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Hafalan - E-Hafalan</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js untuk drawer sidebar & interaksi UI -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js untuk Grafis Statistik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

            <a href="statistik.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-emerald-600 text-white font-medium shadow-lg shadow-emerald-600/30 hover:scale-[1.02] transition-all duration-200">
                <i class="fa-solid fa-chart-line text-lg w-5"></i>
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
        <a href="setoran.php" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-book-bookmark text-lg"></i>
            <span class="text-[10px] mt-0.5">Setoran</span>
        </a>
        <a href="statistik.php" class="flex flex-col items-center p-1 text-emerald-400 font-medium">
            <i class="fa-solid fa-chart-line text-lg"></i>
            <span class="text-[10px] mt-0.5">Statistik</span>
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
                    <h2 class="text-lg sm:text-xl font-bold text-slate-800">Statistik Hafalan</h2>
                    <p class="text-xs text-slate-500 hidden sm:block">Ringkasan capaian hafalan dan keaktifan santri</p>
                </div>
            </div>
        </header>

        <!-- CONTENT BODY -->
        <div class="p-4 sm:p-6 space-y-6 animate-fade-in">

            <!-- METRIC CARDS GRID -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                
                <!-- Card Total Santri -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
                    <div>
                        <p class="text-[11px] text-slate-400 uppercase font-semibold tracking-wider">Total Santri Aktif</p>
                        <h4 class="text-2xl sm:text-3xl font-bold text-slate-800 mt-1"><?php echo number_format($tot_santri); ?></h4>
                        <span class="text-[11px] text-emerald-600 font-medium inline-flex items-center mt-1">
                            <i class="fa-solid fa-user-check mr-1"></i> Terdaftar di sistem
                        </span>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-xl font-bold shadow-inner flex-shrink-0">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>

                <!-- Card Total Setoran -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
                    <div>
                        <p class="text-[11px] text-slate-400 uppercase font-semibold tracking-wider">Total Setoran Tercatat</p>
                        <h4 class="text-2xl sm:text-3xl font-bold text-slate-800 mt-1"><?php echo number_format($tot_setoran); ?></h4>
                        <span class="text-[11px] text-emerald-600 font-medium inline-flex items-center mt-1">
                            <i class="fa-solid fa-arrow-trend-up mr-1"></i> Transaksi hafalan
                        </span>
                    </div>
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-xl font-bold shadow-inner flex-shrink-0">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                </div>

                <!-- Card Khatam 30 Juz -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow sm:col-span-2 lg:col-span-1">
                    <div>
                        <p class="text-[11px] text-slate-400 uppercase font-semibold tracking-wider">Santri Khatam (30 Juz)</p>
                        <h4 class="text-2xl sm:text-3xl font-bold text-slate-800 mt-1"><?php echo number_format($tot_khatam); ?></h4>
                        <span class="text-[11px] text-amber-600 font-medium inline-flex items-center mt-1">
                            <i class="fa-solid fa-award mr-1"></i> Capaian maksimal
                        </span>
                    </div>
                    <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-xl font-bold shadow-inner flex-shrink-0">
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                </div>

            </div>

            <!-- GRAFIK VISUALISASI DATA -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Grafik Distribusi Capaian Juz (Bar Chart) -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm sm:text-base">Distribusi Capaian Santri</h3>
                                <p class="text-xs text-slate-400">Pengelompokan santri berdasarkan jumlah juz yang dihafal</p>
                            </div>
                            <span class="p-2 bg-slate-50 text-slate-400 rounded-xl text-xs"><i class="fa-solid fa-chart-simple"></i></span>
                        </div>
                        <div class="relative w-full h-64 sm:h-72">
                            <canvas id="hafalanChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Grafik Doughnut Persentase Khatam -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm sm:text-base">Rasio Santri Khatam</h3>
                                <p class="text-xs text-slate-400">Persentase kelulusan 30 Juz</p>
                            </div>
                            <span class="p-2 bg-slate-50 text-slate-400 rounded-xl text-xs"><i class="fa-solid fa-chart-pie"></i></span>
                        </div>
                        <div class="relative w-full h-56 flex items-center justify-center">
                            <canvas id="khatamChart"></canvas>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-100 text-center text-xs text-slate-500">
                        <span class="font-semibold text-slate-700"><?php echo $tot_santri > 0 ? round(($tot_khatam / $tot_santri) * 100, 1) : 0; ?>%</span> Santri telah menyelesaikan 30 Juz.
                    </div>
                </div>

            </div>

            <!-- INFORMASI TAMBAHAN -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm">
                <div class="flex items-start space-x-3">
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl flex-shrink-0 text-base">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm mb-1">Informasi Visualisasi Data</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Statistik hafalan ini menyajikan gambaran kuantitatif dari seluruh transaksi setoran hafalan yang terekam di dalam database lembaga. Grafik dapat dimanfaatkan untuk memantau ritme perkembangan serta merencanakan program bimbingan halaqah secara presisi.
                        </p>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <!-- SCRIPT CHART.JS INITIALIZATION -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Data Grafik Bar (Distribusi Hafalan)
            const ctxBar = document.getElementById('hafalanChart').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: ['1 - 5 Juz', '6 - 15 Juz', '16 - 29 Juz', '30 Juz (Khatam)'],
                    datasets: [{
                        label: 'Jumlah Santri',
                        data: [<?php echo $juz_1_5; ?>, <?php echo $juz_6_15; ?>, <?php echo $juz_16_29; ?>, <?php echo $tot_khatam; ?>],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.85)',
                            'rgba(16, 185, 129, 0.85)',
                            'rgba(245, 158, 11, 0.85)',
                            'rgba(139, 92, 246, 0.85)'
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
                            ticks: { stepSize: 1, font: { family: 'Poppins', size: 11 } },
                            grid: { color: '#f1f5f9' }
                        },
                        x: {
                            ticks: { font: { family: 'Poppins', size: 11 } },
                            grid: { display: false }
                        }
                    }
                }
            });

            // Data Grafik Doughnut (Rasio Khatam)
            const ctxDoughnut = document.getElementById('khatamChart').getContext('2d');
            new Chart(ctxDoughnut, {
                type: 'doughnut',
                data: {
                    labels: ['Khatam (30 Juz)', 'Belum Khatam'],
                    datasets: [{
                        data: [<?php echo $tot_khatam; ?>, <?php echo max(0, $tot_santri - $tot_khatam); ?>],
                        backgroundColor: [
                            'rgba(245, 158, 11, 0.9)',
                            'rgba(226, 232, 240, 0.9)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { font: { family: 'Poppins', size: 11 }, usePointStyle: true }
                        }
                    },
                    cutout: '75%'
                }
            });
        });
    </script>

</body>
</html>