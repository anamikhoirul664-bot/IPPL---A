<?php
session_start();
require_once '../../config/koneksi.php';

// Proteksi Halaman: Hanya Role Wali yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'wali') {
    header("Location: '../../login.php'");
    exit();
}

$wali_id = $_SESSION['user_id'];
$nama_wali = $_SESSION['nama'];

// Contoh Query Ambil Data Santri Berdasarkan Wali ID 
$query_santri = mysqli_query($koneksi, "SELECT * FROM santri WHERE wali_id = '$wali_id' LIMIT 1");
$santri = mysqli_fetch_assoc($query_santri);
$santri_id = $santri['id'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Wali - E-Hafalan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> 
        body { font-family: 'Poppins', sans-serif; } 
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
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
                <a href="dasboard.php" class="flex items-center space-x-3 bg-emerald-500/20 text-white px-4 py-3.5 rounded-xl font-medium border border-emerald-400/20 shadow-inner">
                    <i class="fa-solid fa-chart-pie w-5 text-emerald-400"></i>
                    <span>Dashboard</span>
                </a>
                <a href="progres.php" class="flex items-center space-x-3 text-emerald-100/70 hover:bg-emerald-800/50 hover:text-white px-4 py-3.5 rounded-xl font-medium transition-all duration-200 hover:pl-5 group">
                    <i class="fa-solid fa-bars-progress w-5 group-hover:text-emerald-400 transition-colors"></i>
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
                    <h1 class="text-xl font-bold text-slate-800">Dashboard</h1>
                    <p class="text-xs text-slate-500 font-medium">Panel Pantau Hafalan Wali Santri</p>
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
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-6 sm:p-8">
            
            <!-- Greeting Mobile Only -->
            <div class="sm:hidden mb-6">
                <h1 class="text-2xl font-bold text-slate-800">Dashboard</h1>
                <p class="text-sm text-slate-500">Selamat datang, Bapak/Ibu <?php echo htmlspecialchars($nama_wali); ?></p>
            </div>

            <!-- Highlight Banner -->
            <div class="bg-gradient-to-r from-emerald-600 via-emerald-500 to-teal-500 rounded-2xl p-6 sm:p-8 text-white flex flex-col md:flex-row justify-between items-center shadow-lg shadow-emerald-600/20 mb-8 relative overflow-hidden">
                <!-- Decorative Circles -->
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
                <div class="absolute -bottom-10 right-20 w-32 h-32 bg-teal-400/20 rounded-full blur-xl"></div>
                
                <div class="relative z-10 text-center md:text-left mb-6 md:mb-0">
                    <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-lg text-xs font-semibold tracking-wider mb-3 border border-white/20">RINGKASAN</span>
                    <h2 class="text-2xl sm:text-3xl font-bold mb-2">Pantau Hafalan Putra/Putri Anda</h2>
                    <p class="text-emerald-50 max-w-lg text-sm sm:text-base">Lihat statistik perkembangan hafalan harian dan pantau evaluasi dari Ustadz pembimbing secara real-time.</p>
                </div>
                <a href="progres.php" class="relative z-10 w-full md:w-auto text-center px-6 py-3 bg-white text-emerald-700 font-bold rounded-xl text-sm hover:bg-emerald-50 hover:scale-105 hover:shadow-xl transition-all duration-300">
                    Lihat Progres Lengkap <i class="fa-solid fa-arrow-right ml-2"></i>
                </a>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- Card 1 -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Identitas Santri</span>
                        <div class="w-10 h-10 flex justify-center items-center bg-blue-50 text-blue-600 rounded-xl group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300"><i class="fa-solid fa-user-graduate"></i></div>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800"><?php echo htmlspecialchars($santri['nama'] ?? 'Belum terhubung'); ?></h3>
                    <p class="text-sm text-slate-500 mt-1 flex items-center gap-2">
                        <i class="fa-regular fa-id-card text-slate-400"></i> NIS: <span class="font-medium"><?php echo htmlspecialchars($santri['nis'] ?? '-'); ?></span>
                    </p>
                </div>

                <!-- Card 2 -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pencapaian Hafalan</span>
                        <div class="w-10 h-10 flex justify-center items-center bg-emerald-50 text-emerald-600 rounded-xl group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300"><i class="fa-solid fa-book-quran"></i></div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-bold text-slate-800">2 Juz</h3>
                        <span class="text-sm text-slate-400 font-medium">(45 Surah)</span>
                    </div>
                    <p class="text-xs text-emerald-600 mt-3 font-semibold bg-emerald-50 inline-block px-2 py-1 rounded-md">
                        <i class="fa-solid fa-arrow-trend-up mr-1"></i> +1 Surah minggu ini
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group sm:col-span-2 lg:col-span-1">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Predikat Akhir</span>
                        <div class="w-10 h-10 flex justify-center items-center bg-amber-50 text-amber-600 rounded-xl group-hover:bg-amber-500 group-hover:text-white transition-colors duration-300"><i class="fa-solid fa-award text-lg"></i></div>
                    </div>
                    <h3 class="text-3xl font-bold text-amber-500">Mumtaz <span class="text-xl text-slate-600 font-semibold">(A)</span></h3>
                    <p class="text-sm text-slate-500 mt-2 font-medium">Rata-rata Kelancaran & Tajwid sangat baik</p>
                </div>
            </div>

            <!-- Optional: Recent Activity / Setoran Terakhir Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800">Setoran Terakhir</h3>
                    <a href="riwayat.php" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700">Lihat Semua</a>
                </div>
                <div class="p-6 text-center text-slate-500 py-10">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex justify-center items-center mx-auto mb-3">
                        <i class="fa-solid fa-clock-rotate-left text-2xl text-slate-300"></i>
                    </div>
                    <p class="text-sm">Belum ada data setoran baru di minggu ini.</p>
                </div>
            </div>

        </main>
    </div>

    <!-- Script for Mobile Sidebar Toggle -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            // Toggle translate class for sidebar
            sidebar.classList.toggle('-translate-x-full');
            // Toggle hidden class for overlay
            overlay.classList.toggle('hidden');
        }
    </script>
</body>
</html>