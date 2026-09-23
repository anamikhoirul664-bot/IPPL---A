<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');

$nama_user = getUserNama();

// Hapus Data Setoran
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $setoran_id = intval($_GET['id']);
    
    if ($setoran_id > 0) {
        $stmt_del = mysqli_prepare($koneksi, "DELETE FROM setoran WHERE id = ?");
        mysqli_stmt_bind_param($stmt_del, "i", $setoran_id);
        mysqli_stmt_execute($stmt_del);
        mysqli_stmt_close($stmt_del);
    }
    
    header("Location: setoran.php?msg=deleted");
    exit();
}

// Ambil Data Setoran Hafalan
$query = "SELECT s_tr.*, u_santri.nama AS nama_santri, s.nis, s.kelas_kelompok, u_ustadz.nama AS nama_penguji
          FROM setoran s_tr
          JOIN santri s ON s_tr.santri_id = s.id
          JOIN users u_santri ON s.user_id = u_santri.id
          LEFT JOIN users u_ustadz ON s_tr.ustadz_id = u_ustadz.id
          ORDER BY s_tr.tanggal_setor DESC, s_tr.id DESC";
$result = mysqli_query($koneksi, $query);


?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setoran Hafalan - E-Hafalan</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

        .glass-header {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
        }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex flex-col md:flex-row relative overflow-x-hidden">

    <!-- BACKDROP MOBILE MENU -->
    <div id="sidebarBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/60 z-30 hidden md:hidden transition-opacity duration-300"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed md:sticky top-0 inset-y-0 left-0 w-64 bg-slate-900 text-slate-300 flex flex-col h-screen z-40 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out shadow-2xl md:shadow-none">
        <div class="p-5 border-b border-slate-800/80 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center text-white text-xl font-bold shadow-lg shadow-emerald-500/30 transform hover:scale-105 transition-transform">
                    <i class="fa-solid fa-quran"></i>
                </div>
                <div>
                    <h1 class="font-bold text-white text-lg leading-tight">E-Hafalan</h1>
                    <span class="text-xs text-emerald-400 font-medium">Panel Super Admin</span>
                </div>
            </div>
            <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white focus:outline-none">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto text-sm no-scrollbar">
            <a href="dasboard.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800/80 hover:text-white transition-all duration-200">
                <i class="fa-solid fa-chart-pie text-slate-400 w-5"></i>
                <span>Dashboard</span>
            </a>

            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Master Data</div>

            <a href="santri.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800/80 hover:text-white transition-all duration-200">
                <i class="fa-solid fa-user-graduate text-slate-400 w-5"></i>
                <span>Data Santri</span>
            </a>
            <a href="wali.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800/80 hover:text-white transition-all duration-200">
                <i class="fa-solid fa-users text-slate-400 w-5"></i>
                <span>Data Wali Santri</span>
            </a>
            <a href="pengasuh.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800/80 hover:text-white transition-all duration-200">
                <i class="fa-solid fa-user-tie text-slate-400 w-5"></i>
                <span>Data Pengasuh</span>
            </a>
            <a href="users.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800/80 hover:text-white transition-all duration-200">
                <i class="fa-solid fa-user-gear text-slate-400 w-5"></i>
                <span>Kelola User</span>
            </a>

            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Akademik & Hafalan</div>

            <a href="jadwal.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800/80 hover:text-white transition-all duration-200">
                <i class="fa-regular fa-calendar-alt text-slate-400 w-5"></i>
                <span>Jadwal Halaqah</span>
            </a>
            <a href="setoran.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-medium shadow-lg shadow-emerald-600/30">
                <i class="fa-solid fa-book-bookmark text-lg w-5"></i>
                <span>Setoran Hafalan</span>
            </a>
            <a href="penilaian.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800/80 hover:text-white transition-all duration-200">
                <i class="fa-solid fa-star text-slate-400 w-5"></i>
                <span>Penilaian & Nilai</span>
            </a>

            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Laporan & Info</div>

            <a href="statistik.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800/80 hover:text-white transition-all duration-200">
                <i class="fa-solid fa-chart-line text-slate-400 w-5"></i>
                <span>Statistik Hafalan</span>
            </a>
            <a href="laporan.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800/80 hover:text-white transition-all duration-200">
                <i class="fa-solid fa-file-invoice text-slate-400 w-5"></i>
                <span>Laporan Hafalan</span>
            </a>
            <a href="pengumuman.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800/80 hover:text-white transition-all duration-200">
                <i class="fa-solid fa-bullhorn text-slate-400 w-5"></i>
                <span>Pengumuman</span>
            </a>
            <a href="setting.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800/80 hover:text-white transition-all duration-200">
                <i class="fa-solid fa-sliders text-slate-400 w-5"></i>
                <span>Pengaturan Sistem</span>
            </a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col min-w-0 overflow-x-hidden">
        
        <!-- HEADER -->
        <header class="glass-header border-b border-slate-200/80 px-4 sm:px-6 py-4 flex items-center justify-between sticky top-0 z-20">
            <div class="flex items-center space-x-3">
                <button onclick="toggleSidebar()" class="md:hidden text-slate-600 hover:text-emerald-600 focus:outline-none p-1.5 rounded-lg border border-slate-200">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-800">Riwayat Setoran Hafalan</h2>
                    <p class="text-[11px] sm:text-xs text-slate-500 hidden sm:block">Daftar rekapan setoran hafalan santri</p>
                </div>
            </div>

            <a href="tambah_setoran.php" class="px-3.5 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl text-xs font-medium shadow-md shadow-emerald-600/20 hover:shadow-lg transition-all duration-200 flex items-center space-x-2 transform active:scale-95">
                <i class="fa-solid fa-plus text-xs"></i>
                <span class="hidden sm:inline">Catat Setoran Baru</span>
                <span class="sm:hidden">Tambah</span>
            </a>
        </header>

        <!-- CONTAINER CONTENT -->
        <div class="p-4 sm:p-6 space-y-6 animate-fade-in">

            <!-- NOTIFIKASI -->
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm animate-fade-in">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-xl"></i>
                        <span class="font-medium">Setoran hafalan berhasil dicatat!</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 p-1"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center justify-between shadow-sm animate-fade-in">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-circle-check text-red-600 text-xl"></i>
                        <span class="font-medium">Data setoran hafalan berhasil dihapus!</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-800 p-1"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <!-- TABLE CARD -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                
                <!-- TOOLBAR TABLE / SEARCH -->
                <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="relative flex-1 max-w-xs">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Cari santri atau NIS..." class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                    </div>
                    <div class="text-xs text-slate-500 text-right">
                        Total Data: <span class="font-bold text-slate-700"><?php echo mysqli_num_rows($result); ?></span> Record
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm" id="setoranTable">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-100">
                                <th class="py-3.5 px-5 font-semibold">Tanggal & Waktu</th>
                                <th class="py-3.5 px-5 font-semibold">Santri</th>
                                <th class="py-3.5 px-5 font-semibold">Surah & Ayat</th>
                                <th class="py-3.5 px-5 font-semibold">Juz</th>
                                <th class="py-3.5 px-5 font-semibold">Kelancaran</th>
                                <th class="py-3.5 px-5 font-semibold">Tajwid</th>
                                <th class="py-3.5 px-5 font-semibold">Penguji</th>
                                <th class="py-3.5 px-5 font-semibold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr class="hover:bg-slate-50/90 transition-colors duration-150">
                                    <!-- Tanggal & Waktu -->
                                    <td class="py-3.5 px-5 font-medium text-slate-600 whitespace-nowrap">
                                        <?php 
                                            $tgl = !empty($row['tanggal_setor']) ? date('d M Y', strtotime($row['tanggal_setor'])) : '-';
                                            $jam = !empty($row['created_at']) ? date('H:i', strtotime($row['created_at'])) . ' WIB' : '-';
                                            echo $tgl;
                                        ?>
                                        <span class="block text-[11px] text-slate-400 font-normal"><?php echo $jam; ?></span>
                                    </td>

                                    <!-- Santri -->
                                    <td class="py-3.5 px-5 whitespace-nowrap">
                                        <div class="font-semibold text-slate-800"><?php echo htmlspecialchars($row['nama_santri'] ?? '-'); ?></div>
                                        <span class="block text-xs font-normal text-slate-400">NIS: <?php echo htmlspecialchars($row['nis'] ?? '-'); ?></span>
                                    </td>

                                    <!-- Surah & Ayat -->
                                    <td class="py-3.5 px-5 whitespace-nowrap">
                                        <span class="font-medium text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-md inline-block mb-0.5">Surah <?php echo htmlspecialchars($row['surah_id'] ?? '-'); ?></span>
                                        <span class="block text-xs font-normal text-slate-500">Ayat <?php echo htmlspecialchars($row['ayat_mulai'] ?? '0'); ?> - <?php echo htmlspecialchars($row['ayat_selesai'] ?? '0'); ?></span>
                                    </td>

                                    <!-- Juz -->
                                    <td class="py-3.5 px-5 font-bold text-slate-700 whitespace-nowrap">Juz <?php echo htmlspecialchars($row['juz'] ?? '-'); ?></td>

                                    <!-- Kelancaran (Sudah Dibersihkan) -->
                                    <td class="py-3.5 px-5 whitespace-nowrap">
                                        <?php 
                                            $kelancaran_raw = $row['kelancaran'] ?? '';
                                            $kelancaran     = strtolower(trim($kelancaran_raw));

                                            if ($kelancaran === 'lancar') {
                                                echo '<span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-semibold">Lancar</span>';
                                            } elseif (in_array($kelancaran, ['cukup lancar', 'cukup'])) {
                                                echo '<span class="px-2.5 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-semibold">Cukup Lancar</span>';
                                            } elseif (in_array($kelancaran, ['kurang lancar', 'kurang'])) {
                                                echo '<span class="px-2.5 py-1 bg-rose-100 text-rose-700 rounded-full text-xs font-semibold">Kurang Lancar</span>';
                                            } else {
                                                $label = !empty($kelancaran_raw) ? htmlspecialchars($kelancaran_raw) : '-';
                                                echo '<span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-semibold">' . $label . '</span>';
                                            }
                                        ?>
                                    </td>

                                    <!-- Tajwid -->
                                    <td class="py-3.5 px-5 text-xs text-slate-600 whitespace-nowrap"><?php echo htmlspecialchars($row['tajwid'] ?? '-'); ?></td>

                                    <!-- Penguji -->
                                    <td class="py-3.5 px-5 text-xs text-slate-500 whitespace-nowrap"><?php echo htmlspecialchars($row['nama_penguji'] ?? 'Ustadz'); ?></td>

                                    <!-- Aksi -->
                                    <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                        <a href="setoran.php?action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data setoran ini?');" class="inline-flex items-center space-x-1 px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-lg text-xs font-medium transition-all duration-200 shadow-sm">
                                            <i class="fa-solid fa-trash text-[11px]"></i>
                                            <span>Hapus</span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400 text-xs">
                                    <i class="fa-regular fa-folder-open text-3xl mb-2 text-slate-300 block"></i>
                                    Belum ada data setoran hafalan. Klik <b>Catat Setoran Baru</b> untuk menambahkan.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    </table>
                </div>
            </div>

        </div>

    </main>

    <!-- JS UTILS FOR INTERACTION -->
    <script>
        // Toggle Mobile Sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }

        // Live Table Search Filter
        function filterTable() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toLowerCase();
            const table = document.getElementById("setoranTable");
            const tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                let tdSantri = tr[i].getElementsByTagName("td")[1];
                if (tdSantri) {
                    let txtValue = tdSantri.textContent || tdSantri.innerText;
                    if (txtValue.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</body>
</html>