<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');

$nama_user = getUserNama();

// Hapus Data Setoran
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $setoran_id = intval($_GET['id']);
    
    mysqli_query($koneksi, "DELETE FROM setoran WHERE id = $setoran_id");
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
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col min-h-screen sticky top-0 z-30">
        <div class="p-5 border-b border-slate-800 flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white text-xl font-bold shadow-lg shadow-emerald-500/20">
                <i class="fa-solid fa-quran"></i>
            </div>
            <div>
                <h1 class="font-bold text-white text-lg leading-tight">E-Hafalan</h1>
                <span class="text-xs text-emerald-400 font-medium">Panel Super Admin</span>
            </div>
        </div>

        <nav class="flex-1 p-4 space-y-1 overflow-y-auto text-sm">
            <a href="dasboard.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-chart-pie text-slate-400 w-5"></i>
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
            <a href="setoran.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-emerald-600 text-white font-medium shadow-lg shadow-emerald-600/30">
                <i class="fa-solid fa-book-bookmark text-lg w-5"></i>
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

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col min-w-0 overflow-x-hidden">
        
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between sticky top-0 z-20">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Riwayat Setoran Hafalan</h2>
                <p class="text-xs text-slate-500">Daftar rekapan setoran hafalan santri</p>
            </div>
            <a href="tambah_setoran.php" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-medium shadow-md shadow-emerald-600/20 transition-all flex items-center space-x-2">
                <i class="fa-solid fa-plus"></i>
                <span>Catat Setoran Baru</span>
            </a>
        </header>

        <div class="p-6 space-y-6">

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                    <span>Setoran hafalan berhasil dicatat!</span>
                </div>
            <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                    <span>Data setoran hafalan berhasil dihapus!</span>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                                <th class="py-3 px-5 font-semibold">Tanggal & Waktu</th>
                                <th class="py-3 px-5 font-semibold">Santri</th>
                                <th class="py-3 px-5 font-semibold">Surah & Ayat</th>
                                <th class="py-3 px-5 font-semibold">Juz</th>
                                <th class="py-3 px-5 font-semibold">Kelancaran</th>
                                <th class="py-3 px-5 font-semibold">Tajwid</th>
                                <th class="py-3 px-5 font-semibold">Penguji</th>
                                <th class="py-3 px-5 font-semibold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3.5 px-5 font-medium text-slate-600">
                                            <?php echo date('d M Y', strtotime($row['tanggal_setor'])); ?>
                                            <span class="block text-[11px] text-slate-400"><?php echo date('H:i', strtotime($row['created_at'])); ?> WIB</span>
                                        </td>
                                        <td class="py-3.5 px-5 font-semibold text-slate-800">
                                            <?php echo htmlspecialchars($row['nama_santri']); ?>
                                            <span class="block text-xs font-normal text-slate-400">NIS: <?php echo htmlspecialchars($row['nis']); ?></span>
                                        </td>
                                        <td class="py-3.5 px-5 font-medium text-emerald-700">
                                            Surah <?php echo htmlspecialchars($row['surah_id']); ?>
                                            <span class="block text-xs font-normal text-slate-500">Ayat <?php echo htmlspecialchars($row['ayat_mulai']); ?> - <?php echo htmlspecialchars($row['ayat_selesai']); ?></span>
                                        </td>
                                        <td class="py-3.5 px-5 font-bold text-slate-700">Juz <?php echo $row['juz']; ?></td>
                                        <td class="py-3.5 px-5">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold 
                                                <?php 
                                                    if($row['kelancaran'] == 'Lancar') echo 'bg-emerald-100 text-emerald-700';
                                                    elseif($row['kelancaran'] == 'Cukup Lancar') echo 'bg-blue-100 text-blue-700';
                                                    else echo 'bg-amber-100 text-amber-700';
                                                ?>">
                                                <?php echo htmlspecialchars($row['kelancaran']); ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-5 text-xs text-slate-600"><?php echo htmlspecialchars($row['tajwid']); ?></td>
                                        <td class="py-3.5 px-5 text-xs text-slate-500"><?php echo htmlspecialchars($row['nama_penguji'] ?? 'Ustadz'); ?></td>
                                        <td class="py-3.5 px-5 text-center">
                                            <a href="setoran.php?action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data setoran ini?');" class="px-2.5 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg text-xs font-medium transition-colors">
                                                <i class="fa-solid fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-400 text-xs">Belum ada data setoran hafalan. Klik <b>Catat Setoran Baru</b> untuk menambahkan.</td>
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