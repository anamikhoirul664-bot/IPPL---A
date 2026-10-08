<?php
session_start();
require_once '../../config/koneksi.php';

// Proteksi Halaman: Hanya Role Wali yang bisa akses
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'wali') {
    header("Location: ../../login.php");
    exit();
}

$wali_id = $_SESSION['user_id'];
$nama_wali = $_SESSION['nama'] ?? $_SESSION['nama_user'] ?? 'Wali Santri';

// Query Ambil Data Santri + Nama Santri via JOIN
$query_santri = mysqli_query($koneksi, "
    SELECT s.*, u.nama AS nama_santri 
    FROM wali_santri ws
    JOIN santri s ON s.wali_id = ws.id
    JOIN users u ON s.user_id = u.id
    WHERE ws.user_id = '$wali_id' 
    LIMIT 1
");

$santri = mysqli_fetch_assoc($query_santri) ?? [];
$santri_id = $santri['id'] ?? 0;
$nama_santri = $santri['nama_santri'] ?? 'Santri';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Setoran - E-Hafalan</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
                        }
                    },
                    animation: {
                        'fade-in': 'fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards',
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
                <a href="dasboard.php" class="flex items-center space-x-3 text-emerald-100/70 hover:text-white hover:bg-emerald-900/50 px-4 py-2.5 rounded-xl font-medium text-xs transition-all duration-200 group">
                    <i class="fa-solid fa-chart-pie w-5 text-center group-hover:text-emerald-400 transition-colors"></i>
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

                <!-- ACTIVE MENU: RIWAYAT -->
                <a href="riwayat.php" class="flex items-center space-x-3 bg-app-active text-white px-4 py-3 rounded-xl font-semibold text-xs shadow-md shadow-emerald-900/30 transition-all duration-200">
                    <i class="fa-solid fa-clock-rotate-left w-5 text-center text-sm"></i>
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
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Riwayat Setoran Hafalan</h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Rekam aktivitas setoran harian untuk santri <span class="font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200/60"><?php echo htmlspecialchars($nama_santri); ?></span>
                    </p>
                </div>

                <div class="flex items-center space-x-3">
                    <a href="nilai.php" class="inline-flex items-center space-x-2 bg-app-active hover:bg-app-activeHover text-white px-4 py-2.5 rounded-xl text-xs font-semibold shadow-md shadow-emerald-600/20 transition-all duration-150 transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-star text-xs"></i>
                        <span>Lihat Evaluasi Nilai</span>
                    </a>
                </div>
            </header>

            <!-- TIMELINE RIWAYAT CARD -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 sm:p-8">

                <div class="flex items-center gap-3 mb-8 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base shadow-inner">
                        <i class="fa-solid fa-timeline"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Aktivitas Setoran Terbaru</h3>
                        <p class="text-[11px] text-slate-400">Menampilkan rekam kronologis hasil hafalan dari Ustadz</p>
                    </div>
                </div>

                <!-- Timeline Container -->
                <div class="relative border-l-2 border-slate-200 ml-3 md:ml-4 space-y-8 pb-4">
                    <?php
                    if ($santri_id > 0) {
                        // Query riwayat kronologis setoran
                        $q_riwayat = mysqli_query($koneksi, "
                            SELECT 
                                st.*,
                                s.nama_surah,
                                u.nama AS nama_ustadz
                            FROM setoran st
                            LEFT JOIN surah s ON st.surah_id = s.id
                            LEFT JOIN users u ON st.ustadz_id = u.id
                            WHERE st.santri_id = '$santri_id'
                            ORDER BY st.tanggal_setor DESC, st.id DESC
                        ");

                        if ($q_riwayat && mysqli_num_rows($q_riwayat) > 0) {
                            while ($row = mysqli_fetch_assoc($q_riwayat)) {
                                $nilai_angka = floatval($row['nilai_angka'] ?? 0);
                                $is_lulus = $nilai_angka >= 75;

                                $jenis = ucfirst($row['jenis'] ?? 'Ziyadah');
                                $surah = $row['nama_surah'] ?? 'Surah';

                                $ayat_mulai = $row['ayat_mulai'] ?? '-';
                                $ayat_selesai = $row['ayat_selesai'] ?? '-';

                                $nama_ustadz = $row['nama_ustadz'] ?? 'Ustadz Pembimbing';
                                $catatan = trim($row['catatan'] ?? '');

                                $tgl_format = !empty($row['tanggal_setor']) 
                                    ? date('d M Y', strtotime($row['tanggal_setor'])) 
                                    : 'Tanggal -';

                                $dotColor = $is_lulus ? 'bg-emerald-500 border-emerald-100' : 'bg-amber-500 border-amber-100';
                                $badgeStyle = $is_lulus ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200';
                                $iconStatus = $is_lulus ? 'fa-circle-check' : 'fa-rotate-right';
                        ?>

                                <!-- Timeline Item -->
                                <div class="relative pl-6 sm:pl-8 group">
                                    <!-- Timeline Dot -->
                                    <div class="absolute -left-[11px] top-3 w-5 h-5 rounded-full border-4 <?php echo $dotColor; ?> group-hover:scale-125 transition-transform duration-300 shadow-sm z-10"></div>

                                    <!-- Card Content -->
                                    <div class="bg-slate-50/70 p-4 sm:p-5 rounded-2xl border border-slate-200/80 hover:shadow-md hover:bg-white transition-all duration-300 hover:-translate-y-0.5">
                                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                                            <!-- Info Kiri -->
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                                    <span class="text-[10px] font-bold uppercase px-2.5 py-0.5 rounded-md bg-white text-slate-600 border border-slate-200 shadow-sm">
                                                        <?php echo htmlspecialchars($jenis); ?>
                                                    </span>
                                                    <span class="text-xs font-semibold text-slate-400 flex items-center gap-1 bg-white px-2 py-0.5 rounded-md border border-slate-100">
                                                        <i class="fa-regular fa-calendar text-slate-400"></i> <?php echo htmlspecialchars($tgl_format); ?>
                                                    </span>
                                                </div>

                                                <h4 class="text-base font-bold text-slate-800">
                                                    Surah <?php echo htmlspecialchars($surah); ?>
                                                    <span class="text-xs font-semibold text-slate-500 ml-1">(Ayat <?php echo htmlspecialchars($ayat_mulai); ?> - <?php echo htmlspecialchars($ayat_selesai); ?>)</span>
                                                </h4>

                                                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                                                    <i class="fa-solid fa-user-pen text-slate-400"></i> Disimak oleh: <span class="font-semibold text-slate-700"><?php echo htmlspecialchars($nama_ustadz); ?></span>
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

                                        <!-- Catatan Ustadz -->
                                        <?php if (!empty($catatan)): ?>
                                            <div class="mt-3 pt-3 border-t border-slate-200/60">
                                                <div class="relative bg-white p-3 rounded-xl border border-slate-100 shadow-sm">
                                                    <p class="text-xs text-slate-600 italic leading-relaxed">
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
                            <div class="pl-6 sm:pl-8 py-8">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mb-3 text-slate-400 shadow-inner">
                                        <i class="fa-solid fa-folder-open text-2xl"></i>
                                    </div>
                                    <h4 class="text-slate-700 font-bold text-sm">Belum Ada Riwayat Setoran</h4>
                                    <p class="text-xs text-slate-400 max-w-sm mt-1">Data setoran harian ananda akan tercatat di sini secara otomatis setelah diinput oleh Ustadz.</p>
                                </div>
                            </div>
                        <?php
                        }
                    }
                    ?>
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
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/75 backdrop-blur-sm p-4">
         
        <div x-show="logoutModalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-slate-900 border border-emerald-900/60 rounded-2xl p-6 w-full max-w-sm shadow-2xl text-center relative overflow-hidden">
            
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

</body>
</html>