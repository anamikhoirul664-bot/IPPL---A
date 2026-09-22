<?php
session_start();
require_once '../../config/koneksi.php';

// 1. Proteksi Halaman: Cek apakah sesi aktif dan role adalah wali
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'wali') {
    header("Location: ../../login.php");
    exit();
}

$wali_id = $_SESSION['user_id'];
$nama_wali = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Bapak/Ibu Wali';

// 2. Query Ambil Data Santri yang Terhubung dengan Wali Ini
$query_santri = mysqli_query($koneksi, "SELECT * FROM santri WHERE wali_id = '$wali_id' LIMIT 1");
$santri = mysqli_fetch_assoc($query_santri);

// [PERBAIKAN ERROR]: Cek ketersediaan data santri dengan aman
if ($santri) {
    $santri_id = $santri['id'];
    $nama_santri = $santri['nama'];
} else {
    $santri_id = 0;
    $nama_santri = 'Belum terhubung';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Setoran - E-Hafalan</title>
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
                <a href="progres.php" class="flex items-center space-x-3 text-emerald-100/70 hover:bg-emerald-800/50 hover:text-white px-4 py-3.5 rounded-xl font-medium transition-all duration-200 hover:pl-5 group">
                    <i class="fa-solid fa-bars-progress w-5 group-hover:text-emerald-400 transition-colors"></i>
                    <span>Progres Hafalan</span>
                </a>
                <a href="nilai.php" class="flex items-center space-x-3 text-emerald-100/70 hover:bg-emerald-800/50 hover:text-white px-4 py-3.5 rounded-xl font-medium transition-all duration-200 hover:pl-5 group">
                    <i class="fa-solid fa-star w-5 group-hover:text-emerald-400 transition-colors"></i>
                    <span>Nilai & Penilaian</span>
                </a>
                <!-- Active Menu -->
                <a href="riwayat.php" class="flex items-center space-x-3 bg-emerald-500/20 text-white px-4 py-3.5 rounded-xl font-medium border border-emerald-400/20 shadow-inner">
                    <i class="fa-solid fa-clock-rotate-left w-5 text-emerald-400"></i>
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
                    <h1 class="text-xl font-bold text-slate-800">Riwayat Setoran</h1>
                    <p class="text-xs text-slate-500 font-medium">Jejak aktivitas setoran harian</p>
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
                <h1 class="text-2xl font-bold text-slate-800 block sm:hidden mb-1">Riwayat Setoran</h1>
                <p class="text-sm text-slate-500">
                    Histori aktivitas hafalan ananda <span class="font-semibold text-emerald-700 bg-emerald-100/50 px-2 py-0.5 rounded-md"><?php echo htmlspecialchars($nama_santri); ?></span>
                </p>
            </div>

            <!-- Content Area -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 sm:p-8">
                
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                        <i class="fa-solid fa-timeline"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-slate-800">Aktivitas Terbaru</h3>
                        <p class="text-xs text-slate-500">Menampilkan rekaman setoran secara kronologis</p>
                    </div>
                </div>

                <!-- Timeline Container -->
                <div class="relative border-l-2 border-slate-200 ml-3 md:ml-4 space-y-8 pb-4">
                    <?php
                    // Query mengambil riwayat setoran secara kronologis
                    $q_riwayat = mysqli_query($koneksi, "
                        SELECT s.*, u.nama as nama_ustadz 
                        FROM setoran s
                        LEFT JOIN users u ON s.ustadz_id = u.id
                        WHERE s.santri_id = '$santri_id' 
                        ORDER BY s.tanggal DESC, s.id DESC
                    ");

                    if ($q_riwayat && mysqli_num_rows($q_riwayat) > 0) {
                        while ($row = mysqli_fetch_assoc($q_riwayat)) {
                            
                            // [PERBAIKAN ERROR]: Deklarasi variabel dengan aman mengecek ketersediaan datanya
                            $status_asli = isset($row['status']) ? $row['status'] : '';
                            $is_lulus    = strtolower($status_asli) === 'lulus';
                            
                            $jenis       = isset($row['jenis_setoran']) ? $row['jenis_setoran'] : 'Sabaq';
                            $surah       = isset($row['surah']) ? $row['surah'] : 'Belum ditentukan';
                            $ayat        = isset($row['ayat']) ? $row['ayat'] : '-';
                            $nama_ustadz = isset($row['nama_ustadz']) ? $row['nama_ustadz'] : 'Ustadz Pembimbing';
                            $catatan     = isset($row['catatan']) ? trim($row['catatan']) : '';
                            
                            $tgl_format  = !empty($row['tanggal']) ? date('d M Y', strtotime($row['tanggal'])) : 'Tanggal tidak tersedia';
                            
                            // Styling berdasarkan status
                            $dotColor = $is_lulus ? 'bg-emerald-500 border-emerald-100' : 'bg-amber-500 border-amber-100';
                            $badgeStyle = $is_lulus ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-amber-100 text-amber-700 border-amber-200';
                            $iconStatus = $is_lulus ? 'fa-circle-check' : 'fa-rotate-right';
                            ?>
                            
                            <!-- Timeline Item -->
                            <div class="relative pl-6 sm:pl-8 group">
                                <!-- Timeline Dot -->
                                <div class="absolute -left-[11px] sm:-left-[11px] top-4 w-5 h-5 rounded-full border-4 <?php echo $dotColor; ?> group-hover:scale-125 transition-transform duration-300 shadow-sm z-10"></div>
                                
                                <!-- Card -->
                                <div class="bg-slate-50/50 p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md hover:bg-white transition-all duration-300 hover:-translate-y-1">
                                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                                        <!-- Info Kiri -->
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                                <span class="text-[10px] sm:text-xs font-bold uppercase px-2.5 py-1 rounded-md bg-white text-slate-600 border border-slate-200 tracking-wider shadow-sm">
                                                    <?php echo htmlspecialchars($jenis); ?>
                                                </span>
                                                <span class="text-xs font-semibold text-slate-500 flex items-center gap-1 bg-white px-2 py-1 rounded-md border border-slate-100">
                                                    <i class="fa-regular fa-calendar text-slate-400"></i> <?php echo htmlspecialchars($tgl_format); ?>
                                                </span>
                                            </div>
                                            
                                            <h4 class="text-lg font-bold text-slate-800">
                                                Surah <?php echo htmlspecialchars($surah); ?>
                                                <span class="text-sm font-medium text-slate-500 ml-1">(Ayat <?php echo htmlspecialchars($ayat); ?>)</span>
                                            </h4>
                                            
                                            <p class="text-xs text-slate-500 mt-1.5 flex items-center gap-1.5">
                                                <i class="fa-solid fa-user-pen text-slate-400"></i> Disimak oleh: <span class="font-medium text-slate-700"><?php echo htmlspecialchars($nama_ustadz); ?></span>
                                            </p>
                                        </div>

                                        <!-- Badge Status Kanan -->
                                        <div class="shrink-0">
                                            <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg border shadow-sm <?php echo $badgeStyle; ?>">
                                                <i class="fa-solid <?php echo $iconStatus; ?>"></i> 
                                                <?php echo $is_lulus ? 'LULUS' : 'MENGULANG'; ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Blockquote Catatan -->
                                    <?php if (!empty($catatan)): ?>
                                        <div class="mt-4 pt-4 border-t border-slate-200/60">
                                            <div class="relative bg-white p-3 rounded-xl border border-slate-100 shadow-sm">
                                                <i class="fa-solid fa-quote-left text-emerald-100 text-2xl absolute top-2 left-2"></i>
                                                <p class="text-sm text-slate-600 italic relative z-10 pl-7 leading-relaxed">
                                                    "<?php echo htmlspecialchars($catatan); ?>"
                                                </p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        ?>
                        <!-- Empty State -->
                        <div class="pl-6 sm:pl-8 py-10">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 border border-slate-100">
                                    <i class="fa-solid fa-folder-open text-2xl text-slate-300"></i>
                                </div>
                                <h4 class="text-slate-700 font-bold mb-1">Belum Ada Riwayat</h4>
                                <p class="text-sm text-slate-500 max-w-sm">Ananda belum memiliki histori setoran harian. Data akan muncul otomatis setelah Ustadz melakukan input.</p>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
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