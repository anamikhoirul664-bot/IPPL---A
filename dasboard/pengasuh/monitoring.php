<?php
session_start();
require_once '../../config/koneksi.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'pengasuh') {
    header("Location: ../../login.php");
    exit();
}
$nama_pengasuh = $_SESSION['nama'] ?? 'Pengasuh';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Monitoring Setoran - E-Hafalan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-slate-50 min-h-screen flex">

    <aside class="w-64 bg-slate-900 text-white min-h-screen p-5 flex flex-col justify-between hidden md:flex">
        <div>
            <div class="flex items-center space-x-3 mb-8 px-2">
                <i class="fa-solid fa-quran text-2xl text-amber-400"></i>
                <span class="text-xl font-bold">E-Hafalan</span>
            </div>
            <nav class="space-y-2">
                <a href="dasboard.php" class="flex items-center space-x-3 text-slate-300 hover:bg-slate-800 p-3 rounded-xl font-medium transition-colors">
                    <i class="fa-solid fa-chart-pie w-5"></i><span>Dashboard</span>
                </a>
                <a href="monitoring.php" class="flex items-center space-x-3 bg-amber-600 text-white p-3 rounded-xl font-medium">
                    <i class="fa-solid fa-eye w-5"></i><span>Monitoring Setoran</span>
                </a>
                <a href="statistik.php" class="flex items-center space-x-3 text-slate-300 hover:bg-slate-800 p-3 rounded-xl font-medium transition-colors">
                    <i class="fa-solid fa-chart-line w-5"></i><span>Statistik Hafalan</span>
                </a>
                <a href="laporan.php" class="flex items-center space-x-3 text-slate-300 hover:bg-slate-800 p-3 rounded-xl font-medium transition-colors">
                    <i class="fa-solid fa-file-lines w-5"></i><span>Laporan</span>
                </a>
            </nav>
        </div>
        <a href="../../logout.php" class="flex items-center space-x-3 bg-red-600 hover:bg-red-700 text-white p-3 rounded-xl font-medium transition-colors">
            <i class="fa-solid fa-right-from-bracket w-5"></i><span>Keluar</span>
        </a>
    </aside>

    <main class="flex-1 p-6 sm:p-10">
        <header class="mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Monitoring Setoran Realtime</h1>
            <p class="text-sm text-slate-500">Aktivitas penambahan dan penilaian hafalan santri oleh Ustadz</p>
        </header>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Riwayat Seluruh Setoran</h3>
                <span class="text-xs bg-amber-50 text-amber-700 px-3 py-1 rounded-full font-semibold">Live Monitoring</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="p-4">Tanggal</th>
                            <th class="p-4">Nama Santri</th>
                            <th class="p-4">Surah & Ayat</th>
                            <th class="p-4">Ustadz Penyimak</th>
                            <th class="p-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        $q_mon = mysqli_query($koneksi, "
                            SELECT s.*, st.nama as nama_santri, u.nama as nama_ustadz
                            FROM setoran s
                            LEFT JOIN santri st ON s.santri_id = st.id
                            LEFT JOIN users u ON s.ustadz_id = u.id
                            ORDER BY s.tanggal DESC, s.id DESC
                            LIMIT 15
                        ");

                        if ($q_mon && mysqli_num_rows($q_mon) > 0) {
                            while ($row = mysqli_fetch_assoc($q_mon)) {
                                $is_lulus = strtolower($row['status']) == 'lulus';
                                ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-4 text-xs text-slate-500"><?php echo date('d M Y', strtotime($row['tanggal'])); ?></td>
                                    <td class="p-4 font-semibold text-slate-800"><?php echo htmlspecialchars($row['nama_santri'] ?? 'Santri'); ?></td>
                                    <td class="p-4 font-medium text-slate-700"><?php echo htmlspecialchars($row['surah']); ?> (Ayat <?php echo htmlspecialchars($row['ayat'] ?? '-'); ?>)</td>
                                    <td class="p-4 text-xs text-slate-500"><?php echo htmlspecialchars($row['nama_ustadz'] ?? 'Ustadz Pembimbing'); ?></td>
                                    <td class="p-4">
                                        <?php if ($is_lulus): ?>
                                            <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-semibold">Lulus</span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 bg-amber-100 text-amber-700 rounded-lg text-xs font-semibold">Mengulang</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400 text-sm">
                                    Belum ada data setoran yang masuk.
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
</body>
</html>