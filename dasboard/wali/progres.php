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

// 3. Query Ambil Statistik Progres Setoran
$total_setoran = 0;
$total_lulus = 0;

if ($santri_id > 0) {
    // Hitung total setoran
    $q_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM setoran WHERE santri_id = '$santri_id'");
    $r_total = mysqli_fetch_assoc($q_total);
    $total_setoran = $r_total['total'] ?? 0;

    // Hitung setoran yang berstatus Lulus / Selesai
    $q_lulus = mysqli_query($koneksi, "SELECT COUNT(DISTINCT surah) as total_surah FROM setoran WHERE santri_id = '$santri_id' AND status = 'Lulus'");
    $r_lulus = mysqli_fetch_assoc($q_lulus);
    $total_lulus = $r_lulus['total_surah'] ?? 0;
}

// Target Juz 30 terdiri dari 37 Surah
$target_surah = 37;
$persentase = $target_surah > 0 ? round(($total_lulus / $target_surah) * 100) : 0;
if ($persentase > 100) $persentase = 100;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progres Hafalan - E-Hafalan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-slate-50 min-h-screen flex">

    <!-- Sidebar Navbar -->
    <aside class="w-64 bg-emerald-900 text-white min-h-screen p-5 flex flex-col justify-between hidden md:flex">
        <div>
            <div class="flex items-center space-x-3 mb-8 px-2">
                <i class="fa-solid fa-quran text-2xl text-emerald-400"></i>
                <span class="text-xl font-bold">E-Hafalan</span>
            </div>
            <nav class="space-y-2">
                <a href="dasboard.php" class="flex items-center space-x-3 text-emerald-100 hover:bg-emerald-800 p-3 rounded-xl font-medium transition-colors">
                    <i class="fa-solid fa-chart-pie w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="progres.php" class="flex items-center space-x-3 bg-emerald-800 text-white p-3 rounded-xl font-medium">
                    <i class="fa-solid fa-bars-progress w-5"></i>
                    <span>Progres Hafalan</span>
                </a>
                <a href="nilai.php" class="flex items-center space-x-3 text-emerald-100 hover:bg-emerald-800 p-3 rounded-xl font-medium transition-colors">
                    <i class="fa-solid fa-star w-5"></i>
                    <span>Nilai & Penilaian</span>
                </a>
                <a href="riwayat.php" class="flex items-center space-x-3 text-emerald-100 hover:bg-emerald-800 p-3 rounded-xl font-medium transition-colors">
                    <i class="fa-solid fa-clock-rotate-left w-5"></i>
                    <span>Riwayat Setoran</span>
                </a>
            </nav>
        </div>
        <a href="../../logout.php" class="flex items-center space-x-3 bg-red-600 hover:bg-red-700 text-white p-3 rounded-xl font-medium transition-colors">
            <i class="fa-solid fa-right-from-bracket w-5"></i>
            <span>Keluar</span>
        </a>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 p-6 sm:p-10">
        <!-- Header -->
        <header class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Progres Hafalan Santri</h1>
                <p class="text-sm text-slate-500">
                    Memantau perkembangan capaian hafalan <span class="font-semibold text-emerald-700"><?php echo htmlspecialchars($santri['nama'] ?? 'Santri'); ?></span>
                </p>
            </div>
            <div class="flex items-center space-x-3 bg-white p-2 rounded-2xl shadow-sm border">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div class="text-left pr-3">
                    <p class="text-xs font-bold text-slate-700"><?php echo htmlspecialchars($nama_wali); ?></p>
                    <p class="text-[10px] text-slate-400 uppercase">Wali Santri</p>
                </div>
            </div>
        </header>

        <!-- Progress Summary Card -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-2">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">Target Capaian Hafalan (Juz 30)</h3>
                    <p class="text-xs text-slate-500">Telah menyelesaikan <?php echo $total_lulus; ?> dari <?php echo $target_surah; ?> Surah</p>
                </div>
                <span class="text-2xl font-extrabold text-emerald-600"><?php echo $persentase; ?>%</span>
            </div>
            
            <!-- Progress Bar -->
            <div class="w-full bg-slate-100 rounded-full h-4 overflow-hidden mb-4">
                <div class="bg-emerald-600 h-4 rounded-full transition-all duration-500" style="width: <?php echo $persentase; ?>%;"></div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-slate-100 text-center">
                <div class="p-3 bg-slate-50 rounded-xl">
                    <span class="text-[11px] text-slate-400 font-semibold uppercase block">Total Setoran</span>
                    <span class="text-lg font-bold text-slate-800"><?php echo $total_setoran; ?> Kali</span>
                </div>
                <div class="p-3 bg-emerald-50 rounded-xl">
                    <span class="text-[11px] text-emerald-600 font-semibold uppercase block">Surah Tuntas</span>
                    <span class="text-lg font-bold text-emerald-700"><?php echo $total_lulus; ?> Surah</span>
                </div>
                <div class="p-3 bg-amber-50 rounded-xl">
                    <span class="text-[11px] text-amber-600 font-semibold uppercase block">Sisa Surah</span>
                    <span class="text-lg font-bold text-amber-700"><?php echo max(0, $target_surah - $total_lulus); ?> Surah</span>
                </div>
                <div class="p-3 bg-teal-50 rounded-xl">
                    <span class="text-[11px] text-teal-600 font-semibold uppercase block">Target Target</span>
                    <span class="text-lg font-bold text-teal-700">Juz 30</span>
                </div>
            </div>
        </div>

        <!-- Detail Rincian Surah -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Daftar Status Hafalan (Juz 30)</h3>
                <span class="text-xs bg-slate-100 text-slate-600 px-3 py-1 rounded-full font-medium">Auto Update</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="p-4">No</th>
                            <th class="p-4">Nama Surah</th>
                            <th class="p-4">Jumlah Ayat</th>
                            <th class="p-4">Status Setoran</th>
                            <th class="p-4">Tanggal Lulus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        // Ambil daftar setoran santri dari database
                        $q_progres = mysqli_query($koneksi, "
                            SELECT surah, MAX(tanggal) as tgl_terakhir, status 
                            FROM setoran 
                            WHERE santri_id = '$santri_id' 
                            GROUP BY surah 
                            ORDER BY id DESC
                        ");

                        if (mysqli_num_rows($q_progres) > 0) {
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($q_progres)) {
                                $is_lulus = strtolower($row['status']) == 'lulus';
                                ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-4 font-medium text-slate-400"><?php echo $no++; ?></td>
                                    <td class="p-4 font-semibold text-slate-800"><?php echo htmlspecialchars($row['surah']); ?></td>
                                    <td class="p-4 text-xs text-slate-500">Lengkap</td>
                                    <td class="p-4">
                                        <?php if ($is_lulus): ?>
                                            <span class="inline-flex items-center px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-semibold">
                                                <i class="fa-solid fa-circle-check mr-1.5"></i> Lulus
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-1 bg-amber-100 text-amber-700 rounded-lg text-xs font-semibold">
                                                <i class="fa-solid fa-clock mr-1.5"></i> Perlu Mengulang
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-xs text-slate-500">
                                        <?php echo date('d M Y', strtotime($row['tgl_terakhir'])); ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400 text-sm">
                                    <i class="fa-solid fa-folder-open text-3xl mb-2 block"></i>
                                    Belum ada data progres hafalan yang dicatat oleh Ustadz.
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