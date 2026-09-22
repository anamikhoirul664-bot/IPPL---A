<?php
session_start();
require_once '../../config/koneksi.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'pengasuh') {
    header("Location: ../../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Hafalan - E-Hafalan</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- GSAP for Smooth Animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
        }
        
        /* Glassmorphism background effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        /* Custom Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Print Style Optimization */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
            }
            .print-card {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100/70 min-h-screen flex flex-col md:flex-row antialiased text-slate-800">

    <!-- Mobile Top Navigation Bar -->
    <div class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center z-50 sticky top-0 shadow-lg no-print">
        <div class="flex items-center space-x-3">
            <div class="w-9 h-9 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-400">
                <i class="fa-solid fa-quran text-lg"></i>
            </div>
            <span class="text-lg font-bold tracking-wide">E-Hafalan</span>
        </div>
        <button id="mobileMenuBtn" class="p-2 rounded-lg bg-slate-800 text-slate-300 hover:text-white focus:outline-none">
            <i class="fa-solid fa-bars text-xl" id="menuIcon"></i>
        </button>
    </div>

    <!-- Sidebar Overlay for Mobile -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 hidden md:hidden transition-opacity duration-300 opacity-0 no-print"></div>

    <!-- Sidebar Navigation -->
    <aside id="sidebar" class="fixed md:static inset-y-0 left-0 z-40 w-72 bg-slate-900 text-white p-6 flex flex-col justify-between transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out no-print shrink-0 shadow-2xl md:shadow-none">
        <div>
            <!-- Logo Header -->
            <div class="flex items-center space-x-3 mb-10 px-2">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-400 shadow-inner">
                    <i class="fa-solid fa-quran text-2xl"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-xl font-extrabold tracking-tight bg-gradient-to-r from-white via-slate-200 to-slate-400 bg-clip-text text-transparent">E-Hafalan</span>
                    <span class="text-[10px] text-amber-400 font-semibold tracking-wider uppercase">Portal Pengasuh</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-2" id="navContainer">
                <a href="dasboard.php" class="flex items-center space-x-3 text-slate-400 hover:text-white hover:bg-slate-800/70 p-3.5 rounded-xl font-medium transition-all duration-200 group">
                    <i class="fa-solid fa-chart-pie w-5 text-center text-slate-400 group-hover:text-amber-400 transition-colors"></i>
                    <span>Dashboard</span>
                </a>
                <a href="monitoring.php" class="flex items-center space-x-3 text-slate-400 hover:text-white hover:bg-slate-800/70 p-3.5 rounded-xl font-medium transition-all duration-200 group">
                    <i class="fa-solid fa-eye w-5 text-center text-slate-400 group-hover:text-amber-400 transition-colors"></i>
                    <span>Monitoring Setoran</span>
                </a>
                <a href="statistik.php" class="flex items-center space-x-3 text-slate-400 hover:text-white hover:bg-slate-800/70 p-3.5 rounded-xl font-medium transition-all duration-200 group">
                    <i class="fa-solid fa-chart-line w-5 text-center text-slate-400 group-hover:text-amber-400 transition-colors"></i>
                    <span>Statistik Hafalan</span>
                </a>
                <a href="laporan.php" class="flex items-center space-x-3 bg-gradient-to-r from-amber-500 to-amber-600 text-white p-3.5 rounded-xl font-semibold shadow-lg shadow-amber-600/20">
                    <i class="fa-solid fa-file-lines w-5 text-center"></i>
                    <span>Laporan</span>
                </a>
            </nav>
        </div>

        <!-- Logout Button -->
        <a href="../../logout.php" class="flex items-center space-x-3 bg-slate-800/80 hover:bg-rose-600 text-slate-300 hover:text-white p-3.5 rounded-xl font-medium transition-all duration-300 border border-slate-700/50 hover:border-transparent group">
            <i class="fa-solid fa-right-from-bracket w-5 text-center group-hover:translate-x-1 transition-transform"></i>
            <span>Keluar</span>
        </a>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 p-4 sm:p-8 lg:p-10 max-w-7xl w-full mx-auto overflow-hidden">
        
        <!-- Header Page -->
        <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 no-print opacity-0" id="headerAnim">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Laporan Rekapitulasi Hafalan</h1>
                <p class="text-sm text-slate-500 mt-1">Cetak laporan rekapan hafalan santri pesantren secara real-time</p>
            </div>
            <button onclick="window.print()" class="inline-flex items-center justify-center space-x-2.5 px-6 py-3 bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-semibold rounded-xl text-sm shadow-xl shadow-slate-900/10 transition-all duration-200 group shrink-0">
                <i class="fa-solid fa-print text-amber-400 group-hover:scale-110 transition-transform"></i>
                <span>Cetak / PDF</span>
            </button>
        </header>

        <!-- Area Cetak Laporan / Main Card -->
        <div class="print-card glass-card p-6 sm:p-8 lg:p-10 rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-200/80 opacity-0" id="cardAnim">
            
            <!-- Document Header -->
            <div class="text-center border-b border-slate-200 pb-6 mb-8 relative">
                <div class="inline-block p-3 rounded-2xl bg-amber-50 text-amber-600 mb-3 no-print">
                    <i class="fa-solid fa-file-invoice text-2xl"></i>
                </div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 uppercase tracking-wider">Laporan Rekapitulasi Hafalan Santri</h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1">Sistem Monitoring E-Hafalan Pesantren</p>
            </div>

            <!-- Table Responsive Container -->
            <div class="overflow-x-auto rounded-xl border border-slate-200/80">
                <table class="w-full text-left text-xs sm:text-sm text-slate-600 border-collapse">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-800 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                            <th class="py-4 px-4 text-center border-r border-slate-200/60 w-12">No</th>
                            <th class="py-4 px-4 border-r border-slate-200/60 w-32">NIS</th>
                            <th class="py-4 px-4 border-r border-slate-200/60">Nama Santri</th>
                            <th class="py-4 px-4 border-r border-slate-200/60 text-center">Total Setoran</th>
                            <th class="py-4 px-4 border-r border-slate-200/60 text-center">Surah Lulus</th>
                            <th class="py-4 px-4 text-center w-36">Status Target</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70" id="tableBody">
                        <?php
                        $q_lap = mysqli_query($koneksi, "
                            SELECT st.id, st.nis, st.nama, 
                                   COUNT(s.id) as total_setoran,
                                   SUM(CASE WHEN s.status = 'Lulus' THEN 1 ELSE 0 END) as total_lulus
                            FROM santri st
                            LEFT JOIN setoran s ON st.id = s.santri_id
                            GROUP BY st.id
                            ORDER BY st.nama ASC
                        ");

                        if ($q_lap && mysqli_num_rows($q_lap) > 0) {
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($q_lap)) {
                                ?>
                                <tr class="table-row-anim hover:bg-amber-50/40 transition-colors duration-150">
                                    <td class="py-3.5 px-4 text-center font-medium text-slate-500 border-r border-slate-200/60"><?php echo $no++; ?></td>
                                    <td class="py-3.5 px-4 font-mono text-slate-600 border-r border-slate-200/60"><?php echo htmlspecialchars($row['nis'] ?? '-'); ?></td>
                                    <td class="py-3.5 px-4 font-semibold text-slate-900 border-r border-slate-200/60"><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td class="py-3.5 px-4 text-center border-r border-slate-200/60">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                            <?php echo $row['total_setoran']; ?> Kali
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center border-r border-slate-200/60">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200/60">
                                            <?php echo $row['total_lulus']; ?> Surah
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Aktif
                                        </span>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400 bg-slate-50/50">
                                    <i class="fa-solid fa-folder-open text-4xl mb-3 block text-slate-300"></i>
                                    <span>Belum ada data santri.</span>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Print Footer Signatures -->
            <div class="mt-12 hidden print:flex justify-between items-end text-xs text-slate-500 pt-6 border-t border-slate-200">
                <div>
                    <p>Dicetak otomatis melalui Aplikasi E-Hafalan</p>
                    <p>Tanggal Cetak: <?php echo date('d F Y'); ?></p>
                </div>
                <div class="text-center w-48">
                    <p class="mb-16">Pengasuh Pesantren,</p>
                    <p class="font-bold text-slate-800 underline">( .................................... )</p>
                </div>
            </div>

        </div>
    </main>

    <!-- GSAP & Interactivity Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // GSAP Page Entrance Animation
            const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

            tl.to('#headerAnim', { opacity: 1, y: 0, duration: 0.6 })
              .to('#cardAnim', { opacity: 1, y: 0, duration: 0.6 }, "-=0.3")
              .from('.table-row-anim', {
                  opacity: 0,
                  y: 15,
                  duration: 0.4,
                  stagger: 0.05
              }, "-=0.2");

            // Mobile Navigation Toggle Logic
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const menuIcon = document.getElementById('menuIcon');

            function toggleSidebar() {
                const isOpen = !sidebar.classList.contains('-translate-x-full');
                
                if (isOpen) {
                    sidebar.classList.add('-translate-x-full');
                    sidebarOverlay.classList.add('opacity-0');
                    setTimeout(() => sidebarOverlay.classList.add('hidden'), 300);
                    menuIcon.classList.replace('fa-xmark', 'fa-bars');
                } else {
                    sidebarOverlay.classList.remove('hidden');
                    setTimeout(() => sidebarOverlay.classList.remove('opacity-0'), 10);
                    sidebar.classList.remove('-translate-x-full');
                    menuIcon.classList.replace('fa-bars', 'fa-xmark');
                }
            }

            mobileMenuBtn?.addEventListener('click', toggleSidebar);
            sidebarOverlay?.addEventListener('click', toggleSidebar);
        });
    </script>
</body>
</html>