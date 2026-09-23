<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');
$nama_user = getUserNama();

$error = '';

// Data Santri
$santri_query = mysqli_query($koneksi, "SELECT s.id, u.nama, s.nis FROM santri s JOIN users u ON s.user_id = u.id ORDER BY u.nama ASC");

// Ambil ID Pengasuh/Ustadz penguji saat ini
$cur_user_id = getUserId();
$get_ust = mysqli_query($koneksi, "SELECT id FROM pengasuh WHERE user_id = $cur_user_id");

if (!$get_ust || mysqli_num_rows($get_ust) == 0) {
    $get_ust = mysqli_query($koneksi, "SELECT id FROM ustadz WHERE user_id = $cur_user_id");
}

$ustadz_data = mysqli_fetch_assoc($get_ust);
$ustadz_id = $ustadz_data['id'] ?? NULL;

// Array Data 114 Surah Al-Qur'an (Key sebagai ID Surah 1-114)
$daftar_surah = [
    1 => ["nama" => "Al-Fatihah", "juz" => 1],
    2 => ["nama" => "Al-Baqarah", "juz" => 1],
    3 => ["nama" => "Ali 'Imran", "juz" => 3],
    4 => ["nama" => "An-Nisa'", "juz" => 4],
    5 => ["nama" => "Al-Ma'idah", "juz" => 6],
    6 => ["nama" => "Al-An'am", "juz" => 7],
    7 => ["nama" => "Al-A'raf", "juz" => 8],
    8 => ["nama" => "Al-Anfal", "juz" => 9],
    9 => ["nama" => "At-Taubah", "juz" => 10],
    10 => ["nama" => "Yunus", "juz" => 11],
    11 => ["nama" => "Hud", "juz" => 11],
    12 => ["nama" => "Yusuf", "juz" => 12],
    13 => ["nama" => "Ar-Ra'd", "juz" => 13],
    14 => ["nama" => "Ibrahim", "juz" => 13],
    15 => ["nama" => "Al-Hijr", "juz" => 14],
    16 => ["nama" => "An-Nahl", "juz" => 14],
    17 => ["nama" => "Al-Isra'", "juz" => 15],
    18 => ["nama" => "Al-Kahf", "juz" => 15],
    19 => ["nama" => "Maryam", "juz" => 16],
    20 => ["nama" => "Taha", "juz" => 16],
    21 => ["nama" => "Al-Anbiya'", "juz" => 17],
    22 => ["nama" => "Al-Hajj", "juz" => 17],
    23 => ["nama" => "Al-Mu'minun", "juz" => 18],
    24 => ["nama" => "An-Nur", "juz" => 18],
    25 => ["nama" => "Al-Furqan", "juz" => 18],
    26 => ["nama" => "Asy-Syu'ara'", "juz" => 19],
    27 => ["nama" => "An-Naml", "juz" => 19],
    28 => ["nama" => "Al-Qasas", "juz" => 20],
    29 => ["nama" => "Al-'Ankabut", "juz" => 20],
    30 => ["nama" => "Ar-Rum", "juz" => 21],
    31 => ["nama" => "Luqman", "juz" => 21],
    32 => ["nama" => "As-Sajdah", "juz" => 21],
    33 => ["nama" => "Al-Ahzab", "juz" => 21],
    34 => ["nama" => "Saba'", "juz" => 22],
    35 => ["nama" => "Fatir", "juz" => 22],
    36 => ["nama" => "Yasin", "juz" => 22],
    37 => ["nama" => "As-Saffat", "juz" => 23],
    38 => ["nama" => "Sad", "juz" => 23],
    39 => ["nama" => "Az-Zumar", "juz" => 23],
    40 => ["nama" => "Ghafir", "juz" => 24],
    41 => ["nama" => "Fussilat", "juz" => 24],
    42 => ["nama" => "Asy-Syura", "juz" => 25],
    43 => ["nama" => "Az-Zukhruf", "juz" => 25],
    44 => ["nama" => "Ad-Dukhan", "juz" => 25],
    45 => ["nama" => "Al-Jasiyah", "juz" => 25],
    46 => ["nama" => "Al-Ahqaf", "juz" => 26],
    47 => ["nama" => "Muhammad", "juz" => 26],
    48 => ["nama" => "Al-Fath", "juz" => 26],
    49 => ["nama" => "Al-Hujurat", "juz" => 26],
    50 => ["nama" => "Qaf", "juz" => 26],
    51 => ["nama" => "Az-Zariyat", "juz" => 26],
    52 => ["nama" => "At-Tur", "juz" => 27],
    53 => ["nama" => "An-Najm", "juz" => 27],
    54 => ["nama" => "Al-Qamar", "juz" => 27],
    55 => ["nama" => "Ar-Rahman", "juz" => 27],
    56 => ["nama" => "Al-Waqi'ah", "juz" => 27],
    57 => ["nama" => "Al-Hadid", "juz" => 27],
    58 => ["nama" => "Al-Mujadilah", "juz" => 28],
    59 => ["nama" => "Al-Hasyr", "juz" => 28],
    60 => ["nama" => "Al-Mumtahanah", "juz" => 28],
    61 => ["nama" => "As-Saff", "juz" => 28],
    62 => ["nama" => "Al-Jumu'ah", "juz" => 28],
    63 => ["nama" => "Al-Munafiqun", "juz" => 28],
    64 => ["nama" => "At-Taghabun", "juz" => 28],
    65 => ["nama" => "At-Talaq", "juz" => 28],
    66 => ["nama" => "At-Tahrim", "juz" => 28],
    67 => ["nama" => "Al-Mulk", "juz" => 29],
    68 => ["nama" => "Al-Qalam", "juz" => 29],
    69 => ["nama" => "Al-Haqqah", "juz" => 29],
    70 => ["nama" => "Al-Ma'arij", "juz" => 29],
    71 => ["nama" => "Nuh", "juz" => 29],
    72 => ["nama" => "Al-Jinn", "juz" => 29],
    73 => ["nama" => "Al-Muzzammil", "juz" => 29],
    74 => ["nama" => "Al-Muddassir", "juz" => 29],
    75 => ["nama" => "Al-Qiyamah", "juz" => 29],
    76 => ["nama" => "Al-Insan", "juz" => 29],
    77 => ["nama" => "Al-Mursalat", "juz" => 29],
    78 => ["nama" => "An-Naba'", "juz" => 30],
    79 => ["nama" => "An-Nazi'at", "juz" => 30],
    80 => ["nama" => "'Abasa", "juz" => 30],
    81 => ["nama" => "At-Takwir", "juz" => 30],
    82 => ["nama" => "Al-Infitar", "juz" => 30],
    83 => ["nama" => "Al-Mutaffifin", "juz" => 30],
    84 => ["nama" => "Al-Insyiqaq", "juz" => 30],
    85 => ["nama" => "Al-Buruj", "juz" => 30],
    86 => ["nama" => "At-Tariq", "juz" => 30],
    87 => ["nama" => "Al-A'la", "juz" => 30],
    88 => ["nama" => "Al-Ghasyiyah", "juz" => 30],
    89 => ["nama" => "Al-Fajr", "juz" => 30],
    90 => ["nama" => "Al-Balad", "juz" => 30],
    91 => ["nama" => "Asy-Syams", "juz" => 30],
    92 => ["nama" => "Al-Lail", "juz" => 30],
    93 => ["nama" => "Ad-Duha", "juz" => 30],
    94 => ["nama" => "Asy-Syarh", "juz" => 30],
    95 => ["nama" => "At-Tin", "juz" => 30],
    96 => ["nama" => "Al-'Alaq", "juz" => 30],
    97 => ["nama" => "Al-Qadr", "juz" => 30],
    98 => ["nama" => "Al-Bayyinah", "juz" => 30],
    99 => ["nama" => "Az-Zalzalah", "juz" => 30],
    100 => ["nama" => "Al-'Adiyat", "juz" => 30],
    101 => ["nama" => "Al-Qari'ah", "juz" => 30],
    102 => ["nama" => "At-Takasur", "juz" => 30],
    103 => ["nama" => "Al-'Asr", "juz" => 30],
    104 => ["nama" => "Al-Humazah", "juz" => 30],
    105 => ["nama" => "Al-Fil", "juz" => 30],
    106 => ["nama" => "Quraisy", "juz" => 30],
    107 => ["nama" => "Al-Ma'un", "juz" => 30],
    108 => ["nama" => "Al-Kausar", "juz" => 30],
    109 => ["nama" => "Al-Kafirun", "juz" => 30],
    110 => ["nama" => "An-Nasr", "juz" => 30],
    111 => ["nama" => "Al-Lahab", "juz" => 30],
    112 => ["nama" => "Al-Ikhlas", "juz" => 30],
    113 => ["nama" => "Al-Falaq", "juz" => 30],
    114 => ["nama" => "An-Nas", "juz" => 30]
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $santri_id    = intval($_POST['santri_id']);
    $tanggal      = $_POST['tanggal'];
    $surah_id     = intval($_POST['surah']);
    $ayat_mulai   = intval($_POST['ayat_mulai']);
    $ayat_selesai = intval($_POST['ayat_selesai']);
    $juz          = intval($_POST['juz']);
    $kelancaran   = $_POST['kelancaran'];
    $tajwid       = $_POST['tajwid'];
    $catatan      = trim($_POST['catatan']);

    // LOGIKA HITUNG NILAI ANGKA BERDASARKAN KELANCARAN & TAJWID
    $nilai_angka = 0;
    
    // Perhitungan Poin Kelancaran
    if ($kelancaran == 'Lancar') {
        $poin_lancar = 50;
    } elseif ($kelancaran == 'Cukup Lancar') {
        $poin_lancar = 40;
    } else { // Kurang Lancar
        $poin_lancar = 25;
    }

    // Perhitungan Poin Tajwid
    if ($tajwid == 'Sangat Baik') {
        $poin_tajwid = 50;
    } elseif ($tajwid == 'Baik') {
        $poin_tajwid = 40;
    } elseif ($tajwid == 'Cukup') {
        $poin_tajwid = 30;
    } else { // Perlu Perbaikan
        $poin_tajwid = 20;
    }

    // Total Nilai Akhir
    $nilai_angka = $poin_lancar + $poin_tajwid;

    if (empty($santri_id) || empty($surah_id) || empty($juz) || empty($tanggal)) {
        $error = "Pilih Santri, Surah, Juz, dan Tanggal setoran terlebih dahulu.";
    } else {
        // PREPARED STATEMENT FIX (11 kolom & 11 parameter tipe 'iisiiiisssd')
        $stmt = mysqli_prepare($koneksi, "INSERT INTO setoran (santri_id, ustadz_id, tanggal_setor, surah_id, ayat_mulai, ayat_selesai, juz, kelancaran, tajwid, catatan, nilai_angka) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Tipe parameter yang benar:
        // santri_id (i), ustadz_id (i), tanggal (s), surah_id (i), ayat_mulai (i), ayat_selesai (i), juz (i), kelancaran (s), tajwid (s), catatan (s), nilai_angka (d)
        mysqli_stmt_bind_param($stmt, "iisiiiisssd", $santri_id, $ustadz_id, $tanggal, $surah_id, $ayat_mulai, $ayat_selesai, $juz, $kelancaran, $tajwid, $catatan, $nilai_angka);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);

            // Hitung total juz yang sudah disetorkan
            $count_query = mysqli_query($koneksi, "SELECT COUNT(DISTINCT juz) AS total_juz FROM setoran WHERE santri_id = $santri_id AND kelancaran IN ('Lancar', 'Cukup Lancar')");
            $count_data = mysqli_fetch_assoc($count_query);
            $total_juz = $count_data['total_juz'] ?? 0;

            mysqli_query($koneksi, "UPDATE santri SET total_hafalan_juz = $total_juz WHERE id = $santri_id");

            header("Location: setoran.php?msg=success");
            exit();
        } else {
            $error = "Gagal mencatat setoran hafalan: " . mysqli_error($koneksi);
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat Setoran Hafalan - E-Hafalan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex flex-col md:flex-row">

    <!-- OVERLAY BACKGROUND UNTUK MOBILE -->
    <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 hidden md:hidden"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-slate-900 text-slate-300 flex flex-col z-40 transition-transform duration-300 transform -translate-x-full md:translate-x-0 md:static md:min-h-screen">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white text-xl font-bold shadow-lg shadow-emerald-500/20">
                    <i class="fa-solid fa-quran"></i>
                </div>
                <div>
                    <h1 class="font-bold text-white text-lg leading-tight">E-Hafalan</h1>
                    <span class="text-xs text-emerald-400 font-medium">Panel Super Admin</span>
                </div>
            </div>
            <button onclick="toggleSidebar()" class="text-slate-400 hover:text-white md:hidden p-2">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
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
        <!-- HEADER DENGAN TOMBOL MENU UNTUK MOBILE -->
        <header class="bg-white border-b border-slate-200 px-4 md:px-6 py-4 sticky top-0 z-20 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <button onclick="toggleSidebar()" class="p-2 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 md:hidden focus:outline-none">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div>
                    <h2 class="text-base md:text-xl font-bold text-slate-800 leading-tight">Catat Setoran Hafalan Baru</h2>
                    <p class="text-[10px] md:text-xs text-slate-500">Input hasil simaan hafalan santri</p>
                </div>
            </div>
            <a href="setoran.php" class="px-3 py-1.5 md:px-4 md:py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium transition-colors flex items-center">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
            </a>
        </header>

        <div class="p-4 md:p-6 max-w-4xl">
            <?php if (!empty($error)): ?>
                <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs md:text-sm flex items-center space-x-3">
                    <i class="fa-solid fa-circle-exclamation text-lg"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 md:p-6 shadow-sm">
                <form action="" method="POST" class="space-y-5">
                    
                    <h3 class="text-xs md:text-sm font-bold text-emerald-600 uppercase tracking-wider border-b border-slate-100 pb-2">Identitas Santri & Tanggal</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Santri *</label>
                            <select name="santri_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500">
                                <option value="">-- Pilih Santri --</option>
                                <?php while($s = mysqli_fetch_assoc($santri_query)): ?>
                                    <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['nama']) . " (" . htmlspecialchars($s['nis']) . ")"; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Setoran *</label>
                            <input type="date" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500">
                        </div>
                    </div>

                    <h3 class="text-xs md:text-sm font-bold text-emerald-600 uppercase tracking-wider border-b border-slate-100 pb-2 pt-4">Materi Hafalan</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Surah *</label>
                            <select name="surah" id="surahSelect" onchange="updateJuz()" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500">
                                <option value="">-- Pilih Surah --</option>
                                <?php foreach ($daftar_surah as $no => $item): ?>
                                    <option value="<?php echo $no; ?>" data-juz="<?php echo $item['juz']; ?>">
                                        <?php echo $no . ". " . $item['nama'] . " (Juz " . $item['juz'] . ")"; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Juz Ke- *</label>
                            <select name="juz" id="juzSelect" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500">
                                <option value="">-- Pilih Juz --</option>
                                <?php for ($j = 1; $j <= 30; $j++): ?>
                                    <option value="<?php echo $j; ?>">Juz <?php echo $j; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="flex space-x-2">
                            <div class="w-1/2">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Ayat Mulai</label>
                                <input type="number" name="ayat_mulai" value="1" min="1" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500">
                            </div>
                            <div class="w-1/2">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Ayat Selesai</label>
                                <input type="number" name="ayat_selesai" value="10" min="1" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500">
                            </div>
                        </div>
                    </div>

                    <h3 class="text-xs md:text-sm font-bold text-emerald-600 uppercase tracking-wider border-b border-slate-100 pb-2 pt-4">Penilaian & Catatan Penguji</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tingkat Kelancaran *</label>
                            <select name="kelancaran" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500">
                                <option value="Lancar">Lancar (Sangat Baik)</option>
                                <option value="Cukup Lancar">Cukup Lancar (Ada 1-3 Kali Lupa)</option>
                                <option value="Kurang Lancar">Kurang Lancar (Perlu Mengulang)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kualitas Tajwid & Makhraj *</label>
                            <select name="tajwid" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500">
                                <option value="Sangat Baik">Sangat Baik</option>
                                <option value="Baik">Baik</option>
                                <option value="Cukup">Cukup</option>
                                <option value="Perlu Perbaikan">Perlu Perbaikan</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan / Evaluasi Ustadz</label>
                            <textarea name="catatan" rows="3" placeholder="Contoh: Perhatikan makhraj huruf 'Ain dan perpanjang mad jaiz muttashil." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm focus:outline-none focus:border-emerald-500"></textarea>
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end space-x-3">
                        <a href="setoran.php" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 text-xs font-medium transition-colors">Batal</a>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium transition-all shadow-md shadow-emerald-600/20">
                            <i class="fa-solid fa-save mr-1"></i> Simpan Setoran Hafalan
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </main>

    <!-- SCRIPT UTILS -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Otomatis memilih Juz sesuai Surah yang dipilih
        function updateJuz() {
            const surahSelect = document.getElementById('surahSelect');
            const juzSelect = document.getElementById('juzSelect');
            const selectedOption = surahSelect.options[surahSelect.selectedIndex];
            
            const defaultJuz = selectedOption.getAttribute('data-juz');
            if (defaultJuz) {
                juzSelect.value = defaultJuz;
            }
        }
    </script>
</body>
</html>