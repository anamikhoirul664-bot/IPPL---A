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
    <title>Nilai Santri - E-Hafalan</title>
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
                <a href="nilai.php" class="flex items-center space-x-3 bg-emerald-800 text-white p-3 rounded-xl font-medium">
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
                <h1 class="text-2xl font-bold text-slate-800">Evaluasi & Nilai Bacaan</h1>
                <p class="text-sm text-slate-500">
                    Nilai tajwid, kelancaran, dan makhraj untuk <span class="font-semibold text-emerald-700"><?php echo htmlspecialchars($santri['nama'] ?? 'Santri'); ?></span>
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

        <!-- Tabel Daftar Nilai -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Daftar Penilaian Setoran</h3>
                <span class="text-xs bg-emerald-50 text-emerald-700 font-semibold px-3 py-1 rounded-full">Kriteria: A (Sangat Baik), B (Baik), C (Cukup)</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="p-4">Tanggal</th>
                            <th class="p-4">Surah & Ayat</th>
                            <th class="p-4">Makhraj</th>
                            <th class="p-4">Tajwid</th>
                            <th class="p-4">Kelancaran</th>
                            <th class="p-4">Nilai Akhir</th>
                            <th class="p-4">Catatan Ustadz</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        // Ambil data nilai dari tabel setoran / penilaian
                        $q_nilai = mysqli_query($koneksi, "
                            SELECT * FROM setoran 
                            WHERE santri_id = '$santri_id' 
                            ORDER BY tanggal DESC, id DESC
                        ");

                        if ($q_nilai && mysqli_num_rows($q_nilai) > 0) {
                            while ($row = mysqli_fetch_assoc($q_nilai)) {
                                $nilai_angka = $row['nilai'] ?? 0;
                                
                                // Predikat nilai
                                if ($nilai_angka >= 85) {
                                    $predikat = 'Mumtaz (A)';
                                    $badge = 'bg-emerald-100 text-emerald-700';
                                } elseif ($nilai_angka >= 75) {
                                    $predikat = 'Jayyid Jiddan (B)';
                                    $badge = 'bg-blue-100 text-blue-700';
                                } else {
                                    $predikat = 'Maqbul (C)';
                                    $badge = 'bg-amber-100 text-amber-700';
                                }
                                ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-4 text-xs text-slate-500 whitespace-nowrap">
                                        <?php echo date('d M Y', strtotime($row['tanggal'])); ?>
                                    </td>
                                    <td class="p-4 font-semibold text-slate-800">
                                        <?php echo htmlspecialchars($row['surah']); ?>
                                        <span class="block text-xs font-normal text-slate-400">Ayat <?php echo htmlspecialchars($row['ayat'] ?? '-'); ?></span>
                                    </td>
                                    <td class="p-4 font-medium text-slate-700"><?php echo htmlspecialchars($row['nilai_makhraj'] ?? 'A'); ?></td>
                                    <td class="p-4 font-medium text-slate-700"><?php echo htmlspecialchars($row['nilai_tajwid'] ?? 'A'); ?></td>
                                    <td class="p-4 font-medium text-slate-700"><?php echo htmlspecialchars($row['nilai_kelancaran'] ?? 'A'); ?></td>
                                    <td class="p-4">
                                        <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold <?php echo $badge; ?>">
                                            <?php echo $nilai_angka; ?> - <?php echo $predikat; ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-xs text-slate-500 max-w-xs italic">
                                        "<?php echo htmlspecialchars($row['catatan'] ?? 'Bagus, tingkatkan kelancaran.'); ?>"
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400 text-sm">
                                    <i class="fa-solid fa-award text-3xl mb-2 block text-slate-300"></i>
                                    Belum ada data nilai yang diinputkan oleh Ustadz.
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