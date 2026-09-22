<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');

$nama_user = getUserNama();

// Hapus Jadwal / Halaqah (Menggunakan Prepared Statement demi Keamanan)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $h_id = intval($_GET['id']);
    
    // Lepaskan keterkaitan santri dari halaqah ini
    $stmt_update = mysqli_prepare($koneksi, "UPDATE santri SET halaqah_id = NULL WHERE halaqah_id = ?");
    mysqli_stmt_bind_param($stmt_update, "i", $h_id);
    mysqli_stmt_execute($stmt_update);

    // Hapus record halaqah
    $stmt_delete = mysqli_prepare($koneksi, "DELETE FROM halaqah WHERE id = ?");
    mysqli_stmt_bind_param($stmt_delete, "i", $h_id);
    mysqli_stmt_execute($stmt_delete);

    header("Location: jadwal.php?msg=deleted");
    exit();
}

// Fetch Data Halaqah (Menggunakan ustad_id langsung ke tabel users)
$query = "SELECT h.*, u.nama AS nama_pengasuh, COUNT(s.id) AS total_santri 
          FROM halaqah h
          LEFT JOIN users u ON h.ustad_id = u.id
          LEFT JOIN santri s ON s.halaqah_id = h.id
          GROUP BY h.id
          ORDER BY h.id DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Halaqah - E-Hafalan</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js untuk Responsive Drawer & Interaction -->
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

            <a href="jadwal.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-emerald-600 text-white font-medium shadow-lg shadow-emerald-600/30 hover:scale-[1.02] transition-all duration-200">
                <i class="fa-regular fa-calendar-alt text-lg w-5"></i>
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
        <a href="jadwal.php" class="flex flex-col items-center p-1 text-emerald-400 font-medium">
            <i class="fa-regular fa-calendar-alt text-lg"></i>
            <span class="text-[10px] mt-0.5">Halaqah</span>
        </a>
        <a href="setoran.php" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-book-bookmark text-lg"></i>
            <span class="text-[10px] mt-0.5">Setoran</span>
        </a>
        <a href="laporan.php" class="flex flex-col items-center p-1 hover:text-white transition-colors">
            <i class="fa-solid fa-file-invoice text-lg"></i>
            <span class="text-[10px] mt-0.5">Laporan</span>
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
                    <h2 class="text-lg sm:text-xl font-bold text-slate-800">Jadwal Halaqah</h2>
                    <p class="text-xs text-slate-500 hidden sm:block">Kelola kelompok halaqah, pengasuh, dan jadwal pertemuan</p>
                </div>
            </div>
            
            <a href="tambah_jadwal.php" class="px-3 sm:px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs font-semibold shadow-md shadow-emerald-600/20 transition-all flex items-center space-x-1 sm:space-x-2">
                <i class="fa-solid fa-plus"></i>
                <span>Buat Halaqah Baru</span>
            </a>
        </header>

        <!-- CONTAINER CONTENT -->
        <div class="p-4 sm:p-6 space-y-6 animate-fade-in">

            <!-- NOTIFIKASI / PESAN ALERTS -->
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs sm:text-sm flex items-center space-x-3 shadow-sm">
                    <i class="fa-solid fa-circle-check text-base sm:text-lg text-emerald-600"></i>
                    <span class="font-medium">Kelompok Halaqah baru berhasil ditambahkan!</span>
                </div>
            <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs sm:text-sm flex items-center space-x-3 shadow-sm">
                    <i class="fa-solid fa-circle-check text-base sm:text-lg text-emerald-600"></i>
                    <span class="font-medium">Data kelompok halaqah berhasil diperbarui!</span>
                </div>
            <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs sm:text-sm flex items-center space-x-3 shadow-sm">
                    <i class="fa-solid fa-trash-can text-base sm:text-lg text-rose-600"></i>
                    <span class="font-medium">Halaqah berhasil dihapus!</span>
                </div>
            <?php endif; ?>

            <!-- GRID HALAQAH CARDS -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($h = mysqli_fetch_assoc($result)): ?>
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                            <div>
                                <!-- Header Card -->
                                <div class="flex items-start justify-between mb-3 pb-3 border-b border-slate-100">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-base group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                                            <i class="fa-solid fa-users-rectangle"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-slate-800 text-base leading-tight"><?php echo htmlspecialchars($h['nama_halaqah']); ?></h3>
                                            <span class="text-[11px] text-slate-400 font-medium">ID Halaqah: #<?php echo $h['id']; ?></span>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700 flex items-center space-x-1">
                                        <i class="fa-solid fa-user-graduate text-[10px]"></i>
                                        <span><?php echo $h['total_santri']; ?> Santri</span>
                                    </span>
                                </div>

                                <!-- Body Detail Info -->
                                <div class="space-y-2.5 text-xs text-slate-600 my-4">
                                    <div class="flex items-center space-x-2.5">
                                        <div class="w-6 text-center text-slate-400">
                                            <i class="fa-solid fa-user-tie"></i>
                                        </div>
                                        <span class="truncate"><b>Pengampu:</b> <?php echo htmlspecialchars($h['nama_pengasuh'] ?? 'Belum ditentukan'); ?></span>
                                    </div>
                                    <div class="flex items-center space-x-2.5">
                                        <div class="w-6 text-center text-slate-400">
                                            <i class="fa-regular fa-clock"></i>
                                        </div>
                                        <span><b>Waktu:</b> <?php echo htmlspecialchars($h['hari'] . ' (' . $h['jam_mulai'] . ' - ' . $h['jam_selesai'] . ')'); ?></span>
                                    </div>
                                    <div class="flex items-center space-x-2.5">
                                        <div class="w-6 text-center text-slate-400">
                                            <i class="fa-solid fa-location-dot"></i>
                                        </div>
                                        <span class="truncate"><b>Lokasi:</b> <?php echo htmlspecialchars($h['ruangan'] ?? 'Masjid Utama'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Action Buttons -->
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                                <a href="santri.php?halaqah_id=<?php echo $h['id']; ?>" class="px-3 py-1.5 bg-slate-100 text-slate-600 hover:bg-slate-200 rounded-xl text-xs font-medium transition-colors flex items-center space-x-1">
                                    <i class="fa-solid fa-list-check"></i>
                                    <span>Santri</span>
                                </a>

                                <div class="flex items-center space-x-1.5">
                                    <a href="edit_jadwal.php?id=<?php echo $h['id']; ?>" class="px-3 py-1.5 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-xl text-xs font-semibold transition-colors flex items-center space-x-1">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <span>Edit</span>
                                    </a>
                                    <a href="jadwal.php?action=delete&id=<?php echo $h['id']; ?>" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus kelompok halaqah ini? Santri di dalamnya tidak akan terhapus.');" 
                                       class="px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-xl text-xs font-semibold transition-colors flex items-center space-x-1">
                                        <i class="fa-solid fa-trash"></i>
                                        <span>Hapus</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full bg-white rounded-2xl border border-slate-200/80 p-8 sm:p-12 text-center text-slate-400">
                        <div class="w-16 h-16 bg-slate-100 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                            <i class="fa-regular fa-calendar-xmark"></i>
                        </div>
                        <p class="text-sm font-semibold text-slate-600">Belum ada kelompok halaqah</p>
                        <p class="text-xs text-slate-400 mt-1 mb-4">Silakan klik tombol di bawah untuk membuat jadwal halaqah baru.</p>
                        <a href="tambah_jadwal.php" class="inline-flex items-center space-x-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-emerald-600/20 transition-all">
                            <i class="fa-solid fa-plus"></i>
                            <span>Buat Halaqah Baru</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </main>

</body>
</html>