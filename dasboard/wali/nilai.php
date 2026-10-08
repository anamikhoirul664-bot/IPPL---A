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
    <title>Nilai & Penilaian Santri - E-Hafalan</title>

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

                <!-- ACTIVE MENU: NILAI -->
                <a href="nilai.php" class="flex items-center space-x-3 bg-app-active text-white px-4 py-3 rounded-xl font-semibold text-xs shadow-md shadow-emerald-900/30 transition-all duration-200">
                    <i class="fa-solid fa-star w-5 text-center text-sm"></i>
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
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Evaluasi & Nilai Santri</h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Rekapitulasi penilaian tajwid, kelancaran, & makhraj untuk santri <span class="font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200/60"><?php echo htmlspecialchars($nama_santri); ?></span>
                    </p>
                </div>

                <div class="flex items-center space-x-3">
                    <a href="progres.php" class="inline-flex items-center space-x-2 bg-app-active hover:bg-app-activeHover text-white px-4 py-2.5 rounded-xl text-xs font-semibold shadow-md shadow-emerald-600/20 transition-all duration-150 transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-chart-line text-xs"></i>
                        <span>Lihat Progres Santri</span>
                    </a>
                </div>
            </header>

            <!-- TABEL DAFTAR NILAI -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden flex flex-col">
                <!-- Table Header Bar -->
                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base shadow-inner">
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Daftar Penilaian Setoran</h3>
                            <p class="text-[11px] text-slate-400">Hasil evaluasi detail bacaan per sesi setoran</p>
                        </div>
                    </div>
                    
                    <div class="text-[11px] bg-white border border-slate-200/80 text-slate-600 font-medium px-3.5 py-2 rounded-xl flex items-center gap-2 w-full sm:w-auto overflow-x-auto whitespace-nowrap shadow-sm">
                        <i class="fa-solid fa-circle-info text-emerald-500"></i>
                        <span>Kriteria: <strong>A</strong> (&ge;85) &bull; <strong>B</strong> (75-84) &bull; <strong>C</strong> (&lt;75)</span>
                    </div>
                </div>

                <!-- Table Content Wrapper -->
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left text-sm text-slate-600 min-w-[800px]">
                        <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold border-b border-slate-100">
                            <tr>
                                <th class="p-4 pl-6">Tanggal</th>
                                <th class="p-4">Surah & Ayat</th>
                                <th class="p-4 text-center">Makhraj</th>
                                <th class="p-4 text-center">Tajwid</th>
                                <th class="p-4 text-center">Kelancaran</th>
                                <th class="p-4">Nilai Akhir</th>
                                <th class="p-4 pr-6">Catatan Ustadz</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <?php
                            if ($santri_id > 0) {
                                // Query Ambil Data Penilaian Setoran Santri
                                $q_nilai = mysqli_query($koneksi, "
                                    SELECT 
                                        st.*,
                                        s.nama_surah
                                    FROM setoran st
                                    JOIN surah s ON st.surah_id = s.id
                                    WHERE st.santri_id = '$santri_id'
                                    ORDER BY st.tanggal_setor DESC, st.id DESC
                                ");

                                if ($q_nilai && mysqli_num_rows($q_nilai) > 0) {
                                    while ($row = mysqli_fetch_assoc($q_nilai)) {
                                        $nilai_angka = floatval($row['nilai_angka'] ?? 0);
                                        
                                        // Menentukan predikat & gaya warna badge
                                        if ($nilai_angka >= 85) {
                                            $predikat = 'Mumtaz (A)';
                                            $badge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                        } elseif ($nilai_angka >= 75) {
                                            $predikat = 'Jayyid (B)';
                                            $badge = 'bg-blue-50 text-blue-700 border-blue-200';
                                        } else {
                                            $predikat = 'Maqbul (C)';
                                            $badge = 'bg-amber-50 text-amber-700 border-amber-200';
                                        }
                                        ?>
                                        <tr class="hover:bg-slate-50/80 transition-colors duration-200 group">
                                            <td class="p-4 pl-6 font-medium text-slate-500 whitespace-nowrap">
                                                <i class="fa-regular fa-calendar text-slate-400 mr-1.5 group-hover:text-emerald-500 transition-colors"></i> 
                                                <?php echo date('d M Y', strtotime($row['tanggal_setor'])); ?>
                                            </td>
                                            <td class="p-4">
                                                <span class="font-bold text-slate-800 text-sm block"><?php echo htmlspecialchars($row['nama_surah']); ?></span>
                                                <span class="text-[11px] font-semibold text-slate-400 mt-0.5 block">
                                                    Juz <?php echo htmlspecialchars($row['juz']); ?> &bull; Ayat <?php echo htmlspecialchars($row['ayat_mulai'] ?? '-') . '-' . htmlspecialchars($row['ayat_selesai'] ?? '-'); ?>
                                                </span>
                                            </td>
                                            <td class="p-4 text-center font-bold text-slate-700">
                                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200/80">
                                                    <?php echo htmlspecialchars($row['makhroj'] ?? '-'); ?>
                                                </span>
                                            </td>
                                            <td class="p-4 text-center font-bold text-slate-700">
                                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200/80">
                                                    <?php echo htmlspecialchars($row['tajwid'] ?? '-'); ?>
                                                </span>
                                            </td>
                                            <td class="p-4 text-center font-bold text-slate-700">
                                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200/80">
                                                    <?php echo htmlspecialchars($row['kelancaran'] ?? '-'); ?>
                                                </span>
                                            </td>
                                            <td class="p-4 whitespace-nowrap">
                                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg font-bold border <?php echo $badge; ?>">
                                                    <span class="text-sm font-extrabold"><?php echo $nilai_angka; ?></span>
                                                    <span class="w-px h-3 bg-current opacity-30"></span>
                                                    <span><?php echo $predikat; ?></span>
                                                </div>
                                            </td>
                                            <td class="p-4 pr-6 text-slate-500 max-w-xs leading-relaxed italic border-l border-slate-100">
                                                "<?php echo htmlspecialchars(!empty($row['catatan']) ? $row['catatan'] : 'Bagus, teruskan murajaah.'); ?>"
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="7" class="p-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-slate-400">
                                                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mb-3 text-slate-400 shadow-inner">
                                                    <i class="fa-solid fa-folder-open text-2xl"></i>
                                                </div>
                                                <p class="text-sm font-bold text-slate-700">Belum Ada Data Nilai</p>
                                                <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto">Nilai dan catatan evaluasi akan muncul secara otomatis setelah Ustadz menginput setoran.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="7" class="p-12 text-center text-slate-400">
                                        <p class="text-sm font-bold text-slate-600">Data Santri Tidak Ditemukan</p>
                                        <p class="text-xs mt-1">Akun wali ini belum terhubung dengan data santri manapun.</p>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
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