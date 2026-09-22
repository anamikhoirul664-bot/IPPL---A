<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');

$nama_user = getUserNama();

// Hapus Data Pengasuh (Menggunakan Prepared Statement demi Keamanan)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $pengasuh_id = intval($_GET['id']);
    
    // Ambil user_id dari pengasuh
    $stmt_get = mysqli_prepare($koneksi, "SELECT user_id FROM pengasuh WHERE id = ?");
    mysqli_stmt_bind_param($stmt_get, "i", $pengasuh_id);
    mysqli_stmt_execute($stmt_get);
    $res_get = mysqli_stmt_get_result($stmt_get);

    if ($data = mysqli_fetch_assoc($res_get)) {
        $user_id_del = $data['user_id'];
        
        // Lepas relasi ustad_id di tabel halaqah agar tidak error FK Constraint
        $stmt_rel = mysqli_prepare($koneksi, "UPDATE halaqah SET ustad_id = NULL WHERE ustad_id = ?");
        mysqli_stmt_bind_param($stmt_rel, "i", $user_id_del);
        mysqli_stmt_execute($stmt_rel);
        
        // Hapus akun di tabel users (Cascade menghapus record di tabel pengasuh)
        $stmt_del = mysqli_prepare($koneksi, "DELETE FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt_del, "i", $user_id_del);
        mysqli_stmt_execute($stmt_del);

        header("Location: pengasuh.php?msg=deleted");
        exit();
    }
}

// Fitur Filter / Pencarian
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = "";
if (!empty($search)) {
    $search_clean = mysqli_real_escape_string($koneksi, $search);
    $where_clause = " WHERE u.nama LIKE '%$search_clean%' OR p.nip LIKE '%$search_clean%' OR u.username LIKE '%$search_clean%'";
}

// Fetch Data Pengasuh + Jumlah Halaqah & Santri yang dibimbing
$query = "SELECT p.*, u.nama, u.username, u.email, u.telepon, u.status,
                 COUNT(DISTINCT h.id) AS total_halaqah,
                 COUNT(DISTINCT s.id) AS total_santri
          FROM pengasuh p
          JOIN users u ON p.user_id = u.id
          LEFT JOIN halaqah h ON h.ustad_id = u.id
          LEFT JOIN santri s ON s.halaqah_id = h.id
          $where_clause
          GROUP BY p.id
          ORDER BY p.id DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengasuh - E-Hafalan</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js untuk Responsive Drawer -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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
                    <span class="text-[10px] text-emerald-400 font-semibold tracking-wider uppercase">Panel Admin</span>
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
            <a href="pengasuh.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-emerald-600 text-white font-medium shadow-lg shadow-emerald-600/30 hover:scale-[1.02] transition-all duration-200">
                <i class="fa-solid fa-user-tie text-lg w-5"></i>
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

            <a href="statistik.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all duration-200 group">
                <i class="fa-solid fa-chart-line text-slate-400 group-hover:text-emerald-400 w-5 transition-colors"></i>
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

    <!-- BOTTOM NAVIGATION (Khusus Mobile) -->
    <nav class="fixed bottom-0 left-0 right-0 bg-slate-900/95 backdrop-blur-md text-slate-400 border-t border-slate-800 z-40 flex justify-around items-center p-2 md:hidden">
        <a href="dashboard.php" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-chart-pie text-lg"></i>
            <span class="text-[10px] mt-0.5">Home</span>
        </a>
        <a href="pengasuh.php" class="flex flex-col items-center p-1 text-emerald-400 font-medium">
            <i class="fa-solid fa-user-tie text-lg"></i>
            <span class="text-[10px] mt-0.5">Pengasuh</span>
        </a>
        <a href="santri.php" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-user-graduate text-lg"></i>
            <span class="text-[10px] mt-0.5">Santri</span>
        </a>
        <a href="jadwal.php" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-regular fa-calendar-alt text-lg"></i>
            <span class="text-[10px] mt-0.5">Halaqah</span>
        </a>
        <button @click="mobileMenuOpen = true" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-bars text-lg"></i>
            <span class="text-[10px] mt-0.5">Menu</span>
        </button>
    </nav>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 flex flex-col min-w-0 overflow-x-hidden">
        
        <!-- HEADER TOP -->
        <header class="bg-white/80 backdrop-blur-md border-b border-slate-200 px-4 sm:px-6 py-4 flex items-center justify-between sticky top-0 z-20">
            <div class="flex items-center space-x-3">
                <button @click="mobileMenuOpen = true" class="md:hidden text-slate-600 hover:text-slate-900 p-2 rounded-lg bg-slate-100">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-800">Data Pengasuh / Ustadz</h2>
                    <p class="text-xs text-slate-500 hidden sm:block">Kelola akun ustadz dan pembimbing halaqah</p>
                </div>
            </div>
            
            <a href="tambah_pengasuh.php" class="px-3 sm:px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs font-semibold shadow-md shadow-emerald-600/20 transition-all flex items-center space-x-1 sm:space-x-2">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Pengasuh</span>
            </a>
        </header>

        <!-- CONTAINER CONTENT -->
        <div class="p-4 sm:p-6 space-y-6 animate-fade-in">

            <!-- NOTIFIKASI / ALERTS -->
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs sm:text-sm flex items-center space-x-3 shadow-sm">
                    <i class="fa-solid fa-circle-check text-base sm:text-lg text-emerald-600"></i>
                    <span class="font-medium">Data pengasuh baru berhasil ditambahkan!</span>
                </div>
            <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs sm:text-sm flex items-center space-x-3 shadow-sm">
                    <i class="fa-solid fa-circle-check text-base sm:text-lg text-emerald-600"></i>
                    <span class="font-medium">Data pengasuh berhasil diperbarui!</span>
                </div>
            <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs sm:text-sm flex items-center space-x-3 shadow-sm">
                    <i class="fa-solid fa-trash-can text-base sm:text-lg text-rose-600"></i>
                    <span class="font-medium">Data pengasuh berhasil dihapus!</span>
                </div>
            <?php endif; ?>

            <!-- TOOLBAR SEARCH -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <form method="GET" action="pengasuh.php" class="w-full sm:w-80 flex items-center space-x-2">
                    <div class="relative w-full">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari nama, NIP, username..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    </div>
                    <button type="submit" class="px-3 py-2 bg-slate-800 text-white rounded-xl text-xs hover:bg-slate-900 transition-colors">
                        Cari
                    </button>
                    <?php if(!empty($search)): ?>
                        <a href="pengasuh.php" class="p-2 text-slate-400 hover:text-slate-600 text-xs" title="Reset Search">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>
                </form>
                
                <div class="text-xs text-slate-500 w-full sm:w-auto text-right">
                    Total Pengasuh: <span class="font-bold text-slate-800"><?php echo mysqli_num_rows($result); ?></span>
                </div>
            </div>

            <!-- TABLE DATA PENGASUH -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-[11px] font-semibold uppercase tracking-wider border-b border-slate-100">
                                <th class="py-3.5 px-5">Pengasuh / Ustadz</th>
                                <th class="py-3.5 px-5">NIP / Kode</th>
                                <th class="py-3.5 px-5">Kontak</th>
                                <th class="py-3.5 px-5">Spesialisasi</th>
                                <th class="py-3.5 px-5">Bimbingan</th>
                                <th class="py-3.5 px-5">Status</th>
                                <th class="py-3.5 px-5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3.5 px-5">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-9 h-9 rounded-full bg-emerald-100 border border-emerald-200 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                    <?php echo strtoupper(substr($row['nama'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($row['nama']); ?></div>
                                                    <div class="text-[11px] text-slate-400 font-normal">@<?php echo htmlspecialchars($row['username']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-5 font-mono text-xs text-slate-600">
                                            <?php echo htmlspecialchars($row['nip'] ?? '-'); ?>
                                        </td>
                                        <td class="py-3.5 px-5 text-xs text-slate-600 space-y-0.5">
                                            <div><i class="fa-solid fa-phone text-slate-400 w-4"></i> <?php echo htmlspecialchars($row['telepon'] ?? '-'); ?></div>
                                            <div><i class="fa-solid fa-envelope text-slate-400 w-4"></i> <?php echo htmlspecialchars($row['email'] ?? '-'); ?></div>
                                        </td>
                                        <td class="py-3.5 px-5 text-slate-600">
                                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-medium text-xs inline-block">
                                                <?php echo htmlspecialchars($row['spesialisasi'] ?? 'Tahfizh Umum'); ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-5 text-xs">
                                            <div class="flex items-center space-x-1.5">
                                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold"><?php echo $row['total_halaqah']; ?> Halaqah</span>
                                                <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-semibold"><?php echo $row['total_santri']; ?> Santri</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-5">
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold inline-flex items-center space-x-1 <?php echo $row['status'] == 'aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'; ?>">
                                                <span class="w-1.5 h-1.5 rounded-full <?php echo $row['status'] == 'aktif' ? 'bg-emerald-500' : 'bg-rose-500'; ?>"></span>
                                                <span><?php echo ucfirst($row['status']); ?></span>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-5 text-center">
                                            <div class="flex items-center justify-center space-x-1.5">
                                                <a href="edit_pengasuh.php?id=<?php echo $row['id']; ?>" class="px-2.5 py-1.5 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-lg text-xs font-semibold transition-colors flex items-center space-x-1" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                    <span class="hidden sm:inline">Edit</span>
                                                </a>
                                                <a href="pengasuh.php?action=delete&id=<?php echo $row['id']; ?>" 
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus data pengasuh ini?');" 
                                                   class="px-2.5 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg text-xs font-semibold transition-colors flex items-center space-x-1" title="Hapus">
                                                    <i class="fa-solid fa-trash"></i>
                                                    <span class="hidden sm:inline">Hapus</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400">
                                        <div class="w-12 h-12 bg-slate-100 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-2 text-xl">
                                            <i class="fa-solid fa-user-slash"></i>
                                        </div>
                                        <p class="text-xs font-medium text-slate-500">Tidak ada data pengasuh ditemukan</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">Silakan tambahkan data baru atau atur ulang kata kunci pencarian.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </main>

</body>
</html>