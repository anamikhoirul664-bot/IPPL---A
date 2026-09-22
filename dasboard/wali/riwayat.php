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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Setoran - E-Hafalan</title>
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
                <a href="progres.php" class="flex items-center space-x-3 text-emerald-100 hover:bg-emerald-800 p-3 rounded-xl font-medium transition-colors">
                    <i class="fa-solid fa-bars-progress w-5"></i>
                    <span>Progres Hafalan</span>
                </a>
                <a href="nilai.php" class="flex items-center space-x-3 text-emerald-100 hover:bg-emerald-800 p-3 rounded-xl font-medium transition-colors">
                    <i class="fa-solid fa-star w-5"></i>
                    <span>Nilai & Penilaian</span>
                </a>
                <a href="riwayat.php" class="flex items-center space-x-3 bg-emerald-800 text-white p-3 rounded-xl font-medium">
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
                <h1 class="text-2xl font-bold text-slate-800">Riwayat Setoran Harian</h1>
                <p class="text-sm text-slate-500">
                    Jejak histori aktivitas setoran <span class="font-semibold text-emerald-700"><?php echo htmlspecialchars($santri['nama'] ?? 'Santri'); ?></span>
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

        <!-- Timeline / Daftar Riwayat -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-bold text-slate-800 mb-6">Aktivitas Terbaru</h3>

            <div class="space-y-4">
                <?php
                // Query mengambil riwayat setoran secara kronologis
                $q_riwayat = mysqli_query($koneksi, "
                    SELECT s.*, u.nama as nama_ustadz 
                    FROM setoran s
                    LEFT JOIN users u ON s.ustadz_id = u.id
                    WHERE s.santri_id = '$santri_id' 
                    ORDER BY s.tanggal DESC, s.id DESC
                ");

                if ($q_riwayat && mysqli_num_rows($q_riwayat) > 0) {
                    while ($row = mysqli_fetch_assoc($q_riwayat)) {
                        $is_lulus = strtolower($row['status']) == 'lulus';
                        $jenis = $row['jenis_setoran'] ?? 'Sabaq'; // Sabaq / Sabqi / Manzil
                        ?>
                        <div class="flex items-start justify-between p-4 bg-slate-50 hover:bg-slate-100/80 rounded-xl transition-colors border border-slate-100">
                            <div class="flex items-start space-x-4">
                                <div class="p-3 rounded-xl <?php echo $is_lulus ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600'; ?> mt-1">
                                    <i class="fa-solid <?php echo $is_lulus ? 'fa-circle-check' : 'fa-rotate'; ?> text-lg"></i>
                                </div>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-white border text-slate-600">
                                            <?php echo htmlspecialchars($jenis); ?>
                                        </span>
                                        <h4 class="font-bold text-slate-800">
                                            Surah <?php echo htmlspecialchars($row['surah']); ?>
                                            <span class="text-xs font-normal text-slate-500">(Ayat <?php echo htmlspecialchars($row['ayat'] ?? '-'); ?>)</span>
                                        </h4>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Disimak oleh: <span class="font-medium text-slate-700"><?php echo htmlspecialchars($row['nama_ustadz'] ?? 'Ustadz Pembimbing'); ?></span>
                                    </p>
                                    <?php if (!empty($row['catatan'])): ?>
                                        <p class="text-xs text-slate-600 bg-white p-2 rounded-lg border mt-2 italic">
                                            "<?php echo htmlspecialchars($row['catatan']); ?>"
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="text-right whitespace-nowrap">
                                <span class="text-xs font-semibold text-slate-400 block mb-1">
                                    <i class="fa-regular fa-calendar mr-1"></i><?php echo date('d M Y', strtotime($row['tanggal'])); ?>
                                </span>
                                <?php if ($is_lulus): ?>
                                    <span class="inline-block text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-100">
                                        LULUS
                                    </span>
                                <?php else: ?>
                                    <span class="inline-block text-xs font-bold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-md border border-amber-100">
                                        MENGULANG
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="text-center py-10 text-slate-400">
                        <i class="fa-solid fa-clock-rotate-left text-4xl mb-3 block text-slate-300"></i>
                        <p class="text-sm font-medium">Belum ada histori setoran harian untuk santri ini.</p>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </main>

</body>
</html>