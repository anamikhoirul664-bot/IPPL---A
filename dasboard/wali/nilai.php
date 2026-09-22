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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nilai Santri - E-Hafalan</title>
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
                <!-- Active Menu -->
                <a href="nilai.php" class="flex items-center space-x-3 bg-emerald-500/20 text-white px-4 py-3.5 rounded-xl font-medium border border-emerald-400/20 shadow-inner">
                    <i class="fa-solid fa-star w-5 text-emerald-400"></i>
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
                    <h1 class="text-xl font-bold text-slate-800">Evaluasi & Nilai</h1>
                    <p class="text-xs text-slate-500 font-medium">Data Penilaian Bacaan Santri</p>
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
                <h1 class="text-2xl font-bold text-slate-800 block sm:hidden mb-1">Evaluasi & Nilai</h1>
                <p class="text-sm text-slate-500">
                    Rekapitulasi nilai tajwid, kelancaran, dan makhraj untuk <span class="font-semibold text-emerald-700 bg-emerald-100/50 px-2 py-0.5 rounded-md"><?php echo htmlspecialchars($santri['nama'] ?? 'Santri'); ?></span>
                </p>
            </div>

            <!-- Tabel Daftar Nilai -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                <!-- Table Header Section -->
                <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <h3 class="font-bold text-lg text-slate-800">Daftar Penilaian Setoran</h3>
                    </div>
                    <div class="text-[11px] sm:text-xs bg-slate-50 border border-slate-200 text-slate-600 font-medium px-3 py-2 rounded-xl flex items-center gap-2 w-full sm:w-auto overflow-x-auto whitespace-nowrap">
                        <i class="fa-solid fa-circle-info text-emerald-500"></i>
                        <span>Kriteria: <strong>A</strong> (Sangat Baik) &bull; <strong>B</strong> (Baik) &bull; <strong>C</strong> (Cukup)</span>
                    </div>
                </div>

                <!-- Table Content Wrapper -->
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left text-sm text-slate-600 min-w-[800px]">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider font-semibold border-b border-slate-100">
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
                        <tbody class="divide-y divide-slate-100">
                            <?php
                            // Ambil data nilai dari tabel setoran / penilaian
                            $q_nilai = mysqli_query($koneksi, "
                                SELECT * FROM setoran 
                                WHERE santri_id = '$santri_id' 
                                ORDER BY tanggal DESC, id DESC
                            ");

                            if ($q_nilai && mysqli_num_rows($q_nilai) > 0) {
                                while ($row = mysqli_fetch_assoc($q_nilai)) {
                                    $nilai_angka = $row['nilai'] ?? 0;
                                    
                                    // Predikat nilai
                                    if ($nilai_angka >= 85) {
                                        $predikat = 'Mumtaz (A)';
                                        $badge = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                                    } elseif ($nilai_angka >= 75) {
                                        $predikat = 'Jayyid Jiddan (B)';
                                        $badge = 'bg-blue-100 text-blue-700 border-blue-200';
                                    } else {
                                        $predikat = 'Maqbul (C)';
                                        $badge = 'bg-amber-100 text-amber-700 border-amber-200';
                                    }
                                    ?>
                                    <tr class="hover:bg-slate-50/70 transition-colors duration-200 group">
                                        <td class="p-4 pl-6 text-xs font-medium text-slate-500 whitespace-nowrap">
                                            <i class="fa-regular fa-calendar text-slate-400 mr-1 group-hover:text-emerald-500 transition-colors"></i> 
                                            <?php echo date('d M Y', strtotime($row['tanggal'])); ?>
                                        </td>
                                        <td class="p-4">
                                            <span class="font-bold text-slate-800 text-base"><?php echo htmlspecialchars($row['surah']); ?></span>
                                            <span class="block text-xs font-medium text-slate-400 mt-0.5">Ayat <?php echo htmlspecialchars($row['ayat'] ?? '-'); ?></span>
                                        </td>
                                        <td class="p-4 text-center font-bold text-slate-700"><?php echo htmlspecialchars($row['nilai_makhraj'] ?? 'A'); ?></td>
                                        <td class="p-4 text-center font-bold text-slate-700"><?php echo htmlspecialchars($row['nilai_tajwid'] ?? 'A'); ?></td>
                                        <td class="p-4 text-center font-bold text-slate-700"><?php echo htmlspecialchars($row['nilai_kelancaran'] ?? 'A'); ?></td>
                                        <td class="p-4">
                                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold border <?php echo $badge; ?>">
                                                <span class="text-sm"><?php echo $nilai_angka; ?></span>
                                                <span class="w-px h-3 bg-current opacity-30"></span>
                                                <span><?php echo $predikat; ?></span>
                                            </div>
                                        </td>
                                        <td class="p-4 pr-6 text-xs text-slate-500 max-w-xs leading-relaxed italic border-l border-slate-50">
                                            "<?php echo htmlspecialchars($row['catatan'] ?? 'Bagus, tingkatkan kelancaran dan murajaah.'); ?>"
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="7" class="p-10 text-center">
                                        <div class="flex flex-col items-center justify-center text-slate-400">
                                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                                                <i class="fa-solid fa-folder-open text-2xl text-slate-300"></i>
                                            </div>
                                            <p class="text-sm font-medium text-slate-500">Belum ada data nilai.</p>
                                            <p class="text-xs mt-1">Nilai akan muncul setelah Ustadz melakukan input evaluasi.</p>
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