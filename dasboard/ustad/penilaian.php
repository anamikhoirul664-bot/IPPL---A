<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');
$nama_user = getUserNama();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_nilai'])) {
    $santri_id   = intval($_POST['santri_id']);
    $jenis_ujian = trim($_POST['jenis_ujian']);
    $juz_diuji   = intval($_POST['juz_diuji']);
    $nilai       = floatval($_POST['nilai']);
    $keterangan  = trim($_POST['keterangan']);
    $tanggal     = date('Y-m-d');

    if ($santri_id > 0 && !empty($jenis_ujian)) {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO penilaian (santri_id, jenis_ujian, juz_diuji, nilai, keterangan, tanggal) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isidss", $santri_id, $jenis_ujian, $juz_diuji, $nilai, $keterangan, $tanggal);
        if (mysqli_stmt_execute($stmt)) {
            $msg = 'success';
        } else {
            $msg = 'error';
        }
    }
}

// Fetch santri & daftar nilai
$santri_list = mysqli_query($koneksi, "SELECT s.id, u.nama, s.nis FROM santri s JOIN users u ON s.user_id = u.id ORDER BY u.nama ASC");
$penilaian_list = mysqli_query($koneksi, "SELECT p.*, u.nama AS nama_santri, s.nis FROM penilaian p JOIN santri s ON p.santri_id = s.id JOIN users u ON s.user_id = u.id ORDER BY p.id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Penilaian & Munaqasyah - E-Hafalan</title>
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
            <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
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
            <a href="setoran.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-book-bookmark text-slate-400 w-5"></i>
                <span>Setoran Hafalan</span>
            </a>
            <a href="penilaian.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-emerald-600 text-white font-medium shadow-lg shadow-emerald-600/30">
                <i class="fa-solid fa-star text-lg w-5"></i>
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
        <header class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-20">
            <h2 class="text-xl font-bold text-slate-800">Penilaian Ujian & Munaqasyah</h2>
            <p class="text-xs text-slate-500">Input dan rekap penilaian ujian juz santri</p>
        </header>

        <div class="p-6 space-y-6">
            <?php if ($msg == 'success'): ?>
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
                    <i class="fa-solid fa-circle-check mr-2"></i> Penilaian berhasil disimpan!
                </div>
            <?php endif; ?>

            <!-- Input Nilai Baru -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <h3 class="font-bold text-slate-800 text-base mb-4 flex items-center">
                    <i class="fa-solid fa-pen-to-square text-emerald-600 mr-2"></i> Input Nilai Munaqasyah Baru
                </h3>
                <form action="penilaian.php" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Santri *</label>
                        <select name="santri_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                            <option value="">-- Pilih Santri --</option>
                            <?php while ($s = mysqli_fetch_assoc($santri_list)): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['nama']) . ' (' . htmlspecialchars($s['nis']) . ')'; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Ujian *</label>
                        <select name="jenis_ujian" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                            <option value="Ujian Per Juz">Ujian Per Juz</option>
                            <option value="Munaqasyah 5 Juz">Munaqasyah 5 Juz</option>
                            <option value="Munaqasyah 10 Juz">Munaqasyah 10 Juz</option>
                            <option value="Munaqasyah 30 Juz (Khatam)">Munaqasyah 30 Juz (Khatam)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Juz ke-</label>
                        <input type="number" min="1" max="30" name="juz_diuji" placeholder="Contoh: 30" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nilai Akhir (0 - 100) *</label>
                        <input type="number" step="0.1" min="0" max="100" name="nilai" required placeholder="85.5" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Penguji</label>
                        <input type="text" name="keterangan" placeholder="Tajwid sudah lancar, perhatikan kelancaran kelancaran mad thabi'i..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div class="md:col-span-3 text-right pt-2">
                        <button type="submit" name="submit_nilai" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs rounded-xl shadow-md transition-all">
                            <i class="fa-solid fa-save mr-1"></i> Simpan Penilaian
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table Rekap Nilai -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 font-bold text-slate-800 text-sm">Riwayat Penilaian Ujian</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                                <th class="py-3 px-5">Tanggal</th>
                                <th class="py-3 px-5">Santri</th>
                                <th class="py-3 px-5">Jenis Ujian</th>
                                <th class="py-3 px-5">Juz</th>
                                <th class="py-3 px-5">Nilai</th>
                                <th class="py-3 px-5">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php if (mysqli_num_rows($penilaian_list) > 0): ?>
                                <?php while ($p = mysqli_fetch_assoc($penilaian_list)): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-5 text-xs text-slate-500"><?php echo date('d/m/Y', strtotime($p['tanggal'])); ?></td>
                                        <td class="py-3 px-5 font-medium text-slate-800"><?php echo htmlspecialchars($p['nama_santri']); ?></td>
                                        <td class="py-3 px-5"><span class="px-2.5 py-1 rounded-full text-xs bg-blue-50 text-blue-700 font-semibold"><?php echo htmlspecialchars($p['jenis_ujian']); ?></span></td>
                                        <td class="py-3 px-5">Juz <?php echo $p['juz_diuji'] ?: '-'; ?></td>
                                        <td class="py-3 px-5 font-bold <?php echo $p['nilai'] >= 75 ? 'text-emerald-600' : 'text-red-600'; ?>"><?php echo number_format($p['nilai'], 1); ?></td>
                                        <td class="py-3 px-5 text-xs text-slate-500"><?php echo htmlspecialchars($p['keterangan'] ?: '-'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="p-6 text-center text-slate-400 text-xs">Belum ada data penilaian ujian.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>