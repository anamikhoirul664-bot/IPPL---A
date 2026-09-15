<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');
$nama_user = getUserNama();

$laporan_query = "SELECT s.nis, u.nama AS nama_santri, h.nama_halaqah, s.total_hafalan, 
                  (SELECT COUNT(*) FROM setoran st WHERE st.santri_id = s.id) AS total_setoran
                  FROM santri s
                  JOIN users u ON s.user_id = u.id
                  LEFT JOIN halaqah h ON s.halaqah_id = h.id
                  ORDER BY u.nama ASC";
$laporan_data = mysqli_query($koneksi, $laporan_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Hafalan - E-Hafalan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> 
        body { font-family: 'Poppins', sans-serif; }
        @media print {
            aside, header, .no-print { display: none !important; }
            body { background: white; }
            main { margin: 0; padding: 0; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex">

    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col min-h-screen sticky top-0 z-30">
        <div class="p-5 border-b border-slate-800 flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white text-xl font-bold">
                <i class="fa-solid fa-quran"></i>
            </div>
            <div>
                <h1 class="font-bold text-white text-lg leading-tight">E-Hafalan</h1>
                <span class="text-xs text-emerald-400 font-medium">Panel Super Admin</span>
            </div>
        </div>
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto text-sm">
            <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-solid fa-chart-pie text-slate-400 w-5"></i><span>Dashboard</span></a>
            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase text-slate-500">Master Data</div>
            <a href="santri.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-solid fa-user-graduate text-slate-400 w-5"></i><span>Data Santri</span></a>
            <a href="wali.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-solid fa-users text-slate-400 w-5"></i><span>Data Wali Santri</span></a>
            <a href="pengasuh.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-solid fa-user-tie text-slate-400 w-5"></i><span>Data Pengasuh</span></a>
            <a href="users.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-solid fa-user-gear text-slate-400 w-5"></i><span>Kelola User</span></a>
            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase text-slate-500">Akademik & Hafalan</div>
            <a href="jadwal.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-regular fa-calendar-alt text-slate-400 w-5"></i><span>Jadwal Halaqah</span></a>
            <a href="setoran.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-solid fa-book-bookmark text-slate-400 w-5"></i><span>Setoran Hafalan</span></a>
            <a href="penilaian.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-solid fa-star text-slate-400 w-5"></i><span>Penilaian & Nilai</span></a>
            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase text-slate-500">Laporan & Info</div>
            <a href="statistik.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-solid fa-chart-line text-slate-400 w-5"></i><span>Statistik Hafalan</span></a>
            <a href="laporan.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-emerald-600 text-white font-medium shadow-lg shadow-emerald-600/30"><i class="fa-solid fa-file-invoice text-lg w-5"></i><span>Laporan Hafalan</span></a>
            <a href="pengumuman.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-solid fa-bullhorn text-slate-400 w-5"></i><span>Pengumuman</span></a>
            <a href="setting.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-solid fa-sliders text-slate-400 w-5"></i><span>Pengaturan Sistem</span></a>
        </nav>
        <div class="p-4 border-t border-slate-800 flex items-center justify-between">
            <span class="text-xs text-white truncate font-semibold"><?php echo htmlspecialchars($nama_user); ?></span>
            <a href="../../logout.php" class="text-slate-400 hover:text-red-400"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </aside>

    <main class="flex-1 flex flex-col min-w-0">
        <header class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-20 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Laporan Hafalan Santri</h2>
                <p class="text-xs text-slate-500">Rekap seluruh perkembangan capaian hafalan</p>
            </div>
            <button onclick="window.print()" class="no-print px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-medium shadow-md transition-all flex items-center space-x-2">
                <i class="fa-solid fa-print"></i>
                <span>Cetak Laporan</span>
            </button>
        </header>

        <div class="p-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden p-6">
                <div class="text-center mb-6 pb-4 border-b border-slate-200">
                    <h1 class="text-xl font-bold text-slate-900 uppercase tracking-wide">Laporan Rekapitulasi Capaian Hafalan Al-Qur'an</h1>
                    <p class="text-xs text-slate-500 mt-1">Sistem Informasi Monitoring Hafalan Santri (E-Hafalan)</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-100 text-slate-700 text-xs uppercase border-b border-slate-200">
                                <th class="py-3 px-4">No</th>
                                <th class="py-3 px-4">NIS</th>
                                <th class="py-3 px-4">Nama Santri</th>
                                <th class="py-3 px-4">Halaqah</th>
                                <th class="py-3 px-4">Frekuensi Setoran</th>
                                <th class="py-3 px-4">Total Hafalan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-slate-800">
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($laporan_data)): ?>
                                <tr>
                                    <td class="py-3 px-4 text-xs font-semibold"><?php echo $no++; ?></td>
                                    <td class="py-3 px-4 font-mono text-xs"><?php echo htmlspecialchars($row['nis']); ?></td>
                                    <td class="py-3 px-4 font-medium"><?php echo htmlspecialchars($row['nama_santri']); ?></td>
                                    <td class="py-3 px-4 text-xs"><?php echo htmlspecialchars($row['nama_halaqah'] ?: '-'); ?></td>
                                    <td class="py-3 px-4 text-xs"><?php echo $row['total_setoran']; ?> Kali</td>
                                    <td class="py-3 px-4 font-bold text-emerald-600"><?php echo $row['total_hafalan']; ?> Juz</td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>