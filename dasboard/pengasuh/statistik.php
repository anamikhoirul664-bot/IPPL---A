<?php
session_start();
require_once '../../config/koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'pengasuh') {
    header("Location: ../../login.php");
    exit();
}

$nama_pengasuh = $_SESSION['nama'] ?? 'Pengasuh';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Hafalan - E-Hafalan</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js untuk Drawer Mobile Sidebar & Animasi -->
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
                            bg: '#F4F6F8',          /* Light Background */
                            card: '#FFFFFF',
                            textNav: '#94A3B8',
                        }
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: 0, transform: 'translateY(12px)' },
                            '100%': { opacity: 1, transform: 'translateY(0)' },
                        }
                    },
                    animation: {
                        'fade-in': 'fadeInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                    }
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F4F6F8; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #061E29; }
        ::-webkit-scrollbar-thumb { background: #1E293B; border-radius: 10px; }
    </style>
</head>
<body class="bg-app-bg text-slate-800 min-h-screen flex flex-col md:flex-row antialiased overflow-x-hidden" x-data="{ sidebarOpen: false }">

    <!-- OVERLAY MOBILE SIDEBAR -->
    <div x-show="sidebarOpen" 
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
        
        <div class="overflow-y-auto max-h-[calc(100vh-80px)] pr-1">
            <!-- Header Brand / Logo -->
            <div class="flex items-center justify-between mb-6 px-2 pt-1">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-app-active text-white flex items-center justify-center font-bold text-base shadow-md">
                        <i class="fa-solid fa-quran"></i>
                    </div>
                    <div>
                        <span class="text-base font-bold text-white tracking-wide block leading-tight">E-Hafalan</span>
                        <span class="text-[10px] font-semibold text-app-textNav uppercase tracking-wider">PANEL PENGASUH</span>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-1">
                <a href="dasboard.php" class="flex items-center space-x-3 text-app-textNav hover:text-white hover:bg-slate-800/50 px-4 py-2.5 rounded-xl font-medium text-xs transition-all duration-150 group">
                    <i class="fa-solid fa-gauge-high w-5 text-center text-app-textNav group-hover:text-app-active transition-colors"></i>
                    <span>Dashboard</span>
                </a>

                <div class="pt-5 pb-1 px-4">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">AKADEMIK & HAFALAN</span>
                </div>

                <a href="monitoring.php" class="flex items-center space-x-3 text-app-textNav hover:text-white hover:bg-slate-800/50 px-4 py-2.5 rounded-xl font-medium text-xs transition-all duration-150 group">
                    <i class="fa-solid fa-eye w-5 text-center text-app-textNav group-hover:text-app-active transition-colors"></i>
                    <span>Monitoring Setoran</span>
                </a>

                <a href="statistik.php" class="flex items-center space-x-3 bg-app-active hover:bg-app-activeHover text-white px-4 py-2.5 rounded-xl font-semibold text-xs transition-all duration-150 shadow-md">
                    <i class="fa-solid fa-chart-line w-5 text-center text-sm"></i>
                    <span>Statistik Hafalan</span>
                </a>

                <div class="pt-4 pb-1 px-4">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">LAPORAN & INFO</span>
                </div>

                <a href="laporan.php" class="flex items-center space-x-3 text-app-textNav hover:text-white hover:bg-slate-800/50 px-4 py-2.5 rounded-xl font-medium text-xs transition-all duration-150 group">
                    <i class="fa-solid fa-file-lines w-5 text-center text-app-textNav group-hover:text-app-active transition-colors"></i>
                    <span>Laporan Hafalan</span>
                </a>
            </nav>
        </div>

        <!-- User Profile Card -->
        <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between px-2">
            <div class="flex items-center space-x-2.5 overflow-hidden">
                <div class="w-8 h-8 rounded-full bg-app-active text-white font-bold flex items-center justify-center text-xs">
                    <?php echo strtoupper(substr($nama_pengasuh, 0, 1)); ?>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-semibold text-white truncate max-w-[100px]"><?php echo htmlspecialchars($nama_pengasuh); ?></p>
                    <p class="text-[9px] text-app-textNav uppercase tracking-wider">PENGASUH</p>
                </div>
            </div>
            <a href="../../logout.php" title="Keluar" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-rose-600/80 text-slate-400 hover:text-white flex items-center justify-center transition-colors text-xs">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
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

        <div class="p-4 sm:p-8 lg:p-8 flex-1 max-w-7xl w-full mx-auto animate-fade-in">
            
            <!-- HEADER -->
            <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Statistik Capaian Hafalan</h1>
                    <p class="text-xs text-slate-400 mt-0.5">Analisis perkembangan hafalan santri secara kolektif</p>
                </div>
            </header>

            <!-- STATISTIK GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                
                <!-- CARD 1: CAPAIAN PER KATEGORI -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-8 h-8 rounded-lg bg-teal-50 text-app-active flex items-center justify-center text-sm font-semibold">
                                    <i class="fa-solid fa-chart-simple"></i>
                                </div>
                                <h3 class="font-bold text-xs text-slate-800 uppercase tracking-wider">Capaian per Kategori Hafalan</h3>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- Progress 1 -->
                            <div>
                                <div class="flex justify-between text-xs font-semibold mb-1.5">
                                    <span class="text-slate-700">Juz 30 (Juz Amma)</span>
                                    <span class="text-emerald-600 font-bold">80% Santri</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-emerald-500 h-2.5 rounded-full transition-all duration-500" style="width: 80%;"></div>
                                </div>
                            </div>

                            <!-- Progress 2 -->
                            <div>
                                <div class="flex justify-between text-xs font-semibold mb-1.5">
                                    <span class="text-slate-700">Juz 29 (Tabarak)</span>
                                    <span class="text-teal-600 font-bold">45% Santri</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-teal-500 h-2.5 rounded-full transition-all duration-500" style="width: 45%;"></div>
                                </div>
                            </div>

                            <!-- Progress 3 -->
                            <div>
                                <div class="flex justify-between text-xs font-semibold mb-1.5">
                                    <span class="text-slate-700">Juz 1 - 5</span>
                                    <span class="text-amber-600 font-bold">25% Santri</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-amber-500 h-2.5 rounded-full transition-all duration-500" style="width: 25%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: SANTRI TERAKTIF -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col items-center justify-center text-center">
                    <div class="w-16 h-16 bg-amber-50 text-amber-500 border border-amber-200/60 rounded-2xl flex items-center justify-center text-2xl font-bold mb-4 shadow-sm">
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                    <h3 class="font-bold text-sm text-slate-800">Santri Teraktif Minggu Ini</h3>
                    <p class="text-xs text-slate-400 max-w-xs mt-1.5 leading-relaxed">
                        Santri dengan kelulusan setoran terbanyak dalam periode 7 hari terakhir.
                    </p>
                </div>

            </div>

        </div>
    </main>

</body>
</html>