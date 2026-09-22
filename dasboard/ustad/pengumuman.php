<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');

$nama_user = getUserNama();

// Proses Tambah Pengumuman (Menggunakan Prepared Statement)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_pengumuman'])) {
    $judul   = trim($_POST['judul']);
    $isi     = trim($_POST['isi']);
    $tanggal = date('Y-m-d H:i:s');

    if (!empty($judul) && !empty($isi)) {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO pengumuman (judul, isi, tanggal) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $judul, $isi, $tanggal);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: pengumuman.php?msg=posted");
            exit();
        }
    }
}

// Proses Hapus Pengumuman (Menggunakan Prepared Statement)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $p_id = intval($_GET['id']);
    
    $stmt_del = mysqli_prepare($koneksi, "DELETE FROM pengumuman WHERE id = ?");
    mysqli_stmt_bind_param($stmt_del, "i", $p_id);
    mysqli_stmt_execute($stmt_del);

    header("Location: pengumuman.php?msg=deleted");
    exit();
}

// Fetch Data Pengumuman
$pengumuman_list = mysqli_query($koneksi, "SELECT * FROM pengumuman ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengumuman - E-Hafalan</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js untuk Mobile Drawer -->
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

            <a href="statistik.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all duration-200 group">
                <i class="fa-solid fa-chart-line text-slate-400 group-hover:text-emerald-400 w-5 transition-colors"></i>
                <span>Statistik Hafalan</span>
            </a>
            <a href="laporan.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all duration-200 group">
                <i class="fa-solid fa-file-invoice text-slate-400 group-hover:text-emerald-400 w-5 transition-colors"></i>
                <span>Laporan Hafalan</span>
            </a>
            <a href="pengumuman.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-emerald-600 text-white font-medium shadow-lg shadow-emerald-600/30 hover:scale-[1.02] transition-all duration-200">
                <i class="fa-solid fa-bullhorn text-lg w-5"></i>
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
        <a href="pengumuman.php" class="flex flex-col items-center p-1 text-emerald-400 font-medium">
            <i class="fa-solid fa-bullhorn text-lg"></i>
            <span class="text-[10px] mt-0.5">Info</span>
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
                    <h2 class="text-lg sm:text-xl font-bold text-slate-800">Kelola Pengumuman</h2>
                    <p class="text-xs text-slate-500 hidden sm:block">Buat dan publikasikan informasi penting untuk wali santri & pengasuh</p>
                </div>
            </div>
        </header>

        <!-- CONTAINER CONTENT -->
        <div class="p-4 sm:p-6 space-y-6 animate-fade-in max-w-6xl">

            <!-- NOTIFIKASI ALERTS -->
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'posted'): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs sm:text-sm flex items-center space-x-3 shadow-sm">
                    <i class="fa-solid fa-circle-check text-base sm:text-lg text-emerald-600"></i>
                    <span class="font-medium">Pengumuman berhasil diterbitkan!</span>
                </div>
            <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs sm:text-sm flex items-center space-x-3 shadow-sm">
                    <i class="fa-solid fa-trash-can text-base sm:text-lg text-rose-600"></i>
                    <span class="font-medium">Pengumuman berhasil dihapus!</span>
                </div>
            <?php endif; ?>

            <!-- FORM BUAT PENGUMUMAN -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-5 border-b border-slate-100 pb-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <h3 class="font-bold text-slate-800 text-base">Buat Pengumuman Baru</h3>
                </div>
                
                <form action="pengumuman.php" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Judul Pengumuman <span class="text-rose-500">*</span></label>
                        <input type="text" name="judul" required placeholder="Misal: Jadwal Ujian Tahfizh Semester Ganjil" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Isi Pengumuman <span class="text-rose-500">*</span></label>
                        <textarea name="isi" rows="4" required placeholder="Tuliskan detail pengumuman secara rinci di sini..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"></textarea>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" name="post_pengumuman" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-semibold text-xs sm:text-sm rounded-xl shadow-md shadow-emerald-600/20 transition-all flex items-center space-x-2">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Terbitkan Pengumuman</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- RIWAYAT PENGUMUMAN -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 text-base flex items-center space-x-2">
                        <i class="fa-solid fa-clock-rotate-left text-emerald-600"></i>
                        <span>Riwayat Pengumuman</span>
                    </h3>
                    <span class="text-xs text-slate-400 font-medium">Total: <?php echo mysqli_num_rows($pengumuman_list); ?></span>
                </div>

                <?php if (mysqli_num_rows($pengumuman_list) > 0): ?>
                    <div class="grid grid-cols-1 gap-4">
                        <?php while ($p = mysqli_fetch_assoc($pengumuman_list)): ?>
                            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow relative flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between gap-4 mb-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                                                <i class="fa-solid fa-bullhorn text-sm"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-slate-800 text-sm sm:text-base leading-snug"><?php echo htmlspecialchars($p['judul']); ?></h4>
                                                <span class="text-[11px] text-slate-400 flex items-center space-x-1 mt-0.5">
                                                    <i class="fa-regular fa-calendar text-[10px]"></i>
                                                    <span><?php echo date('d M Y, H:i', strtotime($p['tanggal'])); ?> WIB</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-xs sm:text-sm text-slate-600 leading-relaxed bg-slate-50/50 p-4 rounded-xl border border-slate-100 my-2">
                                        <?php echo nl2br(htmlspecialchars($p['isi'])); ?>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end pt-2 border-t border-slate-100 mt-2">
                                    <a href="pengumuman.php?action=delete&id=<?php echo $p['id']; ?>" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus pengumuman ini?');" 
                                       class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-xs font-semibold transition-colors flex items-center space-x-1.5">
                                        <i class="fa-solid fa-trash-can"></i>
                                        <span>Hapus Pengumuman</span>
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white p-12 text-center rounded-2xl border border-slate-200/80 shadow-sm text-slate-400">
                        <div class="w-12 h-12 bg-slate-100 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-3 text-xl">
                            <i class="fa-solid fa-bullhorn"></i>
                        </div>
                        <p class="text-xs font-medium text-slate-500">Belum ada pengumuman yang diterbitkan</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Gunakan formulir di atas untuk menerbitkan pengumuman pertama Anda.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </main>

</body>
</html>