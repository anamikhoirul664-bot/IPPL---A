<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

// Memastikan hanya role Ustadz yang dapat mengakses
checkRole('ustad');

$user_id = getUserId();
$nama_user = getUserNama();

// Fetch ringkasan statistik
$total_santri   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM santri"))['total'] ?? 0;
$total_wali     = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM wali_santri"))['total'] ?? 0;
$total_pengasuh = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengasuh"))['total'] ?? 0;
$total_setoran  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM setoran"))['total'] ?? 0;

// Fetch setoran hafalan terbaru
$query_setoran = "SELECT s.*, st.nis, u.nama AS nama_santri, sr.nama_surah 
                  FROM setoran s
                  JOIN santri st ON s.santri_id = st.id
                  JOIN users u ON st.user_id = u.id
                  JOIN surah sr ON s.surah_id = sr.id
                  ORDER BY s.tanggal_setor DESC LIMIT 5";
$result_setoran = mysqli_query($koneksi, $query_setoran);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Ustadz (Super Admin) - E-Hafalan</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts & FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col min-h-screen sticky top-0 z-30">
        <!-- Logo Brand -->
        <div class="p-5 border-b border-slate-800 flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white text-xl font-bold shadow-lg shadow-emerald-500/20">
                <i class="fa-solid fa-quran"></i>
            </div>
            <div>
                <h1 class="font-bold text-white text-lg leading-tight">E-Hafalan</h1>
                <span class="text-xs text-emerald-400 font-medium">Panel Super Admin</span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto text-sm">
            <a href="dasboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-emerald-600 text-white font-medium shadow-lg shadow-emerald-600/30">
                <i class="fa-solid fa-chart-pie text-lg w-5"></i>
                <span>Dashboard</span>
            </a>

            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Master Data</div>

            <a href="santri.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-user-graduate text-slate-400 w-5"></i>
                <span>Data Santri</span>
            </a>
            <a href="wali.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-users text-slate-400 w-5"></i>
                <span>Data Wali Santri</span>
            </a>
            <a href="pengasuh.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-user-tie text-slate-400 w-5"></i>
                <span>Data Pengasuh</span>
            </a>
            <a href="users.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-user-gear text-slate-400 w-5"></i>
                <span>Kelola User</span>
            </a>

            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Akademik & Hafalan</div>

            <a href="jadwal.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-regular fa-calendar-alt text-slate-400 w-5"></i>
                <span>Jadwal Halaqah</span>
            </a>
            <a href="setoran.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-book-bookmark text-slate-400 w-5"></i>
                <span>Setoran Hafalan</span>
            </a>
            <a href="penilaian.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-star text-slate-400 w-5"></i>
                <span>Penilaian & Nilai</span>
            </a>

            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Laporan & Info</div>

            <a href="statistik.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-chart-line text-slate-400 w-5"></i>
                <span>Statistik Hafalan</span>
            </a>
            <a href="laporan.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-file-invoice text-slate-400 w-5"></i>
                <span>Laporan Hafalan</span>
            </a>
            <a href="pengumuman.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-bullhorn text-slate-400 w-5"></i>
                <span>Pengumuman</span>
            </a>
            <a href="setting.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-sliders text-slate-400 w-5"></i>
                <span>Pengaturan Sistem</span>
            </a>
        </nav>

        <!-- User Profile Card / Logout -->
        <div class="p-4 border-t border-slate-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white font-bold text-sm">
                        <?php echo strtoupper(substr($nama_user, 0, 1)); ?>
                    </div>
                    <div class="truncate w-28">
                        <p class="text-xs font-semibold text-white truncate"><?php echo htmlspecialchars($nama_user); ?></p>
                        <p class="text-[10px] text-slate-400 uppercase">Ustadz (Admin)</p>
                    </div>
                </div>
                <a href="../../logout.php" class="text-slate-400 hover:text-red-400 p-2 rounded-lg transition-colors" title="Logout">
                    <i class="fa-solid fa-right-from-bracket text-lg"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 flex flex-col min-w-0 overflow-x-hidden">

        <!-- NAVBAR TOP -->
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between sticky top-0 z-20">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Ringkasan Dashboard</h2>
                <p class="text-xs text-slate-500">Selamat datang kembali, <?php echo htmlspecialchars($nama_user); ?>!</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="tambah_setoran.php" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-medium shadow-md shadow-emerald-600/20 transition-all flex items-center space-x-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Catat Setoran</span>
                </a>
            </div>
        </header>

        <!-- DASHBOARD CONTENT -->
        <div class="p-6 space-y-6">

            <!-- STATISTIC CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                
                <!-- Card Santri -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500 font-medium">Total Santri</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1"><?php echo $total_santri; ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                </div>

                <!-- Card Wali -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500 font-medium">Wali Santri</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1"><?php echo $total_wali; ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>

                <!-- Card Pengasuh -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500 font-medium">Pengasuh / Pimpinan</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1"><?php echo $total_pengasuh; ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                </div>

                <!-- Card Setoran -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500 font-medium">Total Setoran</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1"><?php echo $total_setoran; ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                </div>

            </div>

            <!-- TABLE SETORAN TERBARU -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800">Setoran Hafalan Terbaru</h3>
                    <a href="setoran.php" class="text-xs font-semibold text-emerald-600 hover:underline">Lihat Semua</a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                                <th class="py-3 px-5 font-semibold">Santri</th>
                                <th class="py-3 px-5 font-semibold">Surah</th>
                                <th class="py-3 px-5 font-semibold">Ayat</th>
                                <th class="py-3 px-5 font-semibold">Juz</th>
                                <th class="py-3 px-5 font-semibold">Kelancaran</th>
                                <th class="py-3 px-5 font-semibold">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php if (mysqli_num_rows($result_setoran) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result_setoran)): ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3 px-5 font-medium text-slate-800">
                                            <?php echo htmlspecialchars($row['nama_santri']); ?>
                                            <span class="block text-xs text-slate-400">NIS: <?php echo htmlspecialchars($row['nis']); ?></span>
                                        </td>
                                        <td class="py-3 px-5"><?php echo htmlspecialchars($row['nama_surah']); ?></td>
                                        <td class="py-3 px-5"><?php echo $row['ayat_mulai']; ?> - <?php echo $row['ayat_selesai']; ?></td>
                                        <td class="py-3 px-5">Juz <?php echo $row['juz']; ?></td>
                                        <td class="py-3 px-5">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold 
                                                <?php 
                                                    echo $row['kelancaran'] == 'Sangat Lancar' ? 'bg-emerald-100 text-emerald-700' : 
                                                        ($row['kelancaran'] == 'Lancar' ? 'bg-teal-100 text-teal-700' : 'bg-amber-100 text-amber-700'); 
                                                ?>">
                                                <?php echo htmlspecialchars($row['kelancaran']); ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-5 text-xs text-slate-500">
                                            <?php echo date('d M Y H:i', strtotime($row['tanggal_setor'])); ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-slate-400 text-xs">Belum ada data setoran hafalan terbaru.</td>
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