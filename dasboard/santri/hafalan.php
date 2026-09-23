<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

// Memastikan hanya role santri yang dapat mengakses
checkRole('santri');

$user_id = getUserId();
$nama_user = getUserNama();


/*
|--------------------------------------------------------------------------
| DATA SANTRI
|--------------------------------------------------------------------------
*/

$query_santri = "
    SELECT 
        s.*,
        u.nama,
        u.username,
        u.email,
        u.foto,
        h.nama_halaqah,
        h.ruangan,
        us.id AS id_ustadz,
        us.gelar,
        us.spesialisasi,
        uu.nama AS nama_ustadz
    FROM santri s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN halaqah h ON s.halaqah_id = h.id
    LEFT JOIN ustadz us ON s.ustadz_id = us.id
    LEFT JOIN users uu ON us.user_id = uu.id
    WHERE s.user_id = ?
    LIMIT 1
";

$stmt_santri = mysqli_prepare($koneksi, $query_santri);
mysqli_stmt_bind_param($stmt_santri, "i", $user_id);
mysqli_stmt_execute($stmt_santri);

$result_santri = mysqli_stmt_get_result($stmt_santri);
$data_santri = mysqli_fetch_assoc($result_santri);

if (!$data_santri) {
    die("Data santri tidak ditemukan.");
}

$santri_id = $data_santri['id'];

/*
|--------------------------------------------------------------------------
| PROSES INPUT SETORAN OLEH SANTRI
|--------------------------------------------------------------------------
*/

$pesan_sukses = '';
$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_setoran'])) {
    $jenis = $_POST['jenis'] ?? '';
    $surah_id = (int) ($_POST['surah_id'] ?? 0);
    $ayat_mulai = (int) ($_POST['ayat_mulai'] ?? 0);
    $ayat_selesai = (int) ($_POST['ayat_selesai'] ?? 0);
    $juz = (int) ($_POST['juz'] ?? 0);
    $halaman = (int) ($_POST['halaman'] ?? 0);
    $catatan = trim($_POST['catatan'] ?? '');

    $jenis_valid = ['ziyadah', 'murajaah'];

    if (!in_array($jenis, $jenis_valid, true)) {
        $pesan_error = 'Jenis setoran tidak valid.';

    } elseif ($surah_id <= 0 || $ayat_mulai <= 0 || $ayat_selesai <= 0) {
        $pesan_error = 'Surah dan ayat wajib diisi dengan benar.';

    } elseif ($ayat_selesai < $ayat_mulai) {
        $pesan_error = 'Ayat selesai tidak boleh lebih kecil dari ayat mulai.';

    } elseif ($juz < 1 || $juz > 30) {
        $pesan_error = 'Juz harus berada antara 1 sampai 30.';

    } elseif ($halaman < 1) {
        $pesan_error = 'Halaman harus diisi minimal 1.';

    } else {

        // Mengecek jumlah ayat sesuai surah
        $query_cek_surah = "
            SELECT nama_surah, jumlah_ayat
            FROM surah
            WHERE id = ?
        ";

        $stmt_cek_surah = mysqli_prepare($koneksi, $query_cek_surah);

        mysqli_stmt_bind_param(
            $stmt_cek_surah,
            "i",
            $surah_id
        );

        mysqli_stmt_execute($stmt_cek_surah);

        $result_cek_surah = mysqli_stmt_get_result($stmt_cek_surah);
        $data_cek_surah = mysqli_fetch_assoc($result_cek_surah);

        if (!$data_cek_surah) {

            $pesan_error = 'Surah yang dipilih tidak ditemukan.';

        } elseif (
            $ayat_mulai > (int) $data_cek_surah['jumlah_ayat'] ||
            $ayat_selesai > (int) $data_cek_surah['jumlah_ayat']
        ) {

            $pesan_error = 'Ayat tidak boleh melebihi jumlah ayat '
                . $data_cek_surah['nama_surah']
                . ', yaitu '
                . $data_cek_surah['jumlah_ayat']
                . ' ayat.';

        } else {

            $query_insert = "
                INSERT INTO setoran
                (
                    santri_id,
                    jenis,
                    surah_id,
                    ayat_mulai,
                    ayat_selesai,
                    juz,
                    halaman,
                    catatan
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ";

            $stmt_insert = mysqli_prepare($koneksi, $query_insert);

            if ($stmt_insert) {

                mysqli_stmt_bind_param(
                    $stmt_insert,
                    "isiiiiis",
                    $santri_id,
                    $jenis,
                    $surah_id,
                    $ayat_mulai,
                    $ayat_selesai,
                    $juz,
                    $halaman,
                    $catatan
                );

                if (mysqli_stmt_execute($stmt_insert)) {

                    $pesan_sukses = 'Setoran berhasil disimpan dan menunggu penilaian ustadz.';

                } else {

                    $pesan_error = 'Setoran gagal disimpan. Silakan coba lagi.';

                }

                mysqli_stmt_close($stmt_insert);

            } else {

                $pesan_error = 'Terjadi kesalahan pada proses penyimpanan.';

            }
        }

        mysqli_stmt_close($stmt_cek_surah);
    }
}

/*
|--------------------------------------------------------------------------
| DATA SURAH
|--------------------------------------------------------------------------
*/

$query_surah = "
    SELECT 
        id,
        nomor_surah,
        nama_surah,
        nama_arab,
        jumlah_ayat
    FROM surah
    ORDER BY nomor_surah ASC
";

$result_surah = mysqli_query($koneksi, $query_surah);

if (!$result_surah) {
    die("Data surah gagal dimuat: " . mysqli_error($koneksi));
}

// Map Pemetaan Nomor Surah ke Awal Juz (1-114)
$juz_map = [
    1=>1, 2=>1, 3=>3, 4=>4, 5=>6, 6=>7, 7=>8, 8=>9, 9=>10, 10=>11,
    11=>11, 12=>12, 13=>13, 14=>13, 15=>14, 16=>14, 17=>15, 18=>15, 19=>16, 20=>16,
    21=>17, 22=>17, 23=>18, 24=>18, 25=>18, 26=>19, 27=>19, 28=>19, 29=>20, 30=>20,
    31=>21, 32=>21, 33=>21, 34=>22, 35=>22, 36=>22, 37=>23, 38=>23, 39=>23, 40=>24,
    41=>24, 42=>25, 43=>25, 44=>25, 45=>25, 46=>26, 47=>26, 48=>26, 49=>26, 50=>26,
    51=>26, 52=>27, 53=>27, 54=>27, 55=>27, 56=>27, 57=>27, 58=>28, 59=>28, 60=>28,
    61=>28, 62=>28, 63=>28, 64=>28, 65=>28, 66=>28, 67=>29, 68=>29, 69=>29, 70=>29,
    71=>29, 72=>29, 73=>29, 74=>29, 75=>29, 76=>29, 77=>29
];

/*
|--------------------------------------------------------------------------
| STATISTIK HAFALAN
|--------------------------------------------------------------------------
*/

// Total semua setoran
$query_total = "
    SELECT COUNT(*) AS total
    FROM setoran
    WHERE santri_id = ?
";

$stmt = mysqli_prepare($koneksi, $query_total);
mysqli_stmt_bind_param($stmt, "i", $santri_id);
mysqli_stmt_execute($stmt);

$result_total = mysqli_stmt_get_result($stmt);
$total_setoran = mysqli_fetch_assoc($result_total)['total'] ?? 0;


// Total Ziyadah
$query_ziyadah = "
    SELECT COUNT(*) AS total
    FROM setoran
    WHERE santri_id = ?
    AND jenis = 'ziyadah'
";

$stmt = mysqli_prepare($koneksi, $query_ziyadah);
mysqli_stmt_bind_param($stmt, "i", $santri_id);
mysqli_stmt_execute($stmt);

$result_ziyadah = mysqli_stmt_get_result($stmt);
$total_ziyadah = mysqli_fetch_assoc($result_ziyadah)['total'] ?? 0;


// Total Murajaah
$query_murajaah = "
    SELECT COUNT(*) AS total
    FROM setoran
    WHERE santri_id = ?
    AND jenis = 'murajaah'
";

$stmt = mysqli_prepare($koneksi, $query_murajaah);
mysqli_stmt_bind_param($stmt, "i", $santri_id);
mysqli_stmt_execute($stmt);

$result_murajaah = mysqli_stmt_get_result($stmt);
$total_murajaah = mysqli_fetch_assoc($result_murajaah)['total'] ?? 0;


// Total hafalan dan halaman
$total_hafalan = $data_santri['total_hafalan'] ?? 0;
$total_halaman = $data_santri['total_hafalan_halaman'] ?? 0;


/*
|--------------------------------------------------------------------------
| DATA RIWAYAT SETORAN
|--------------------------------------------------------------------------
*/

$query_setoran = "
    SELECT
        s.*,
        sr.nama_surah,
        sr.nama_arab
    FROM setoran s
    LEFT JOIN surah sr ON s.surah_id = sr.id
    WHERE s.santri_id = ?
    ORDER BY s.tanggal_setor DESC
";

$stmt_setoran = mysqli_prepare($koneksi, $query_setoran);
mysqli_stmt_bind_param($stmt_setoran, "i", $santri_id);
mysqli_stmt_execute($stmt_setoran);

$result_setoran = mysqli_stmt_get_result($stmt_setoran);


/*
|--------------------------------------------------------------------------
| RATA-RATA NILAI
|--------------------------------------------------------------------------
*/

$query_rata = "
    SELECT AVG(nilai_angka) AS rata_nilai
    FROM setoran
    WHERE santri_id = ?
    AND nilai_angka IS NOT NULL
";

$stmt = mysqli_prepare($koneksi, $query_rata);
mysqli_stmt_bind_param($stmt, "i", $santri_id);
mysqli_stmt_execute($stmt);

$result_rata = mysqli_stmt_get_result($stmt);
$data_rata = mysqli_fetch_assoc($result_rata);

$rata_nilai = round($data_rata['rata_nilai'] ?? 0, 2);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hafalan Saya - E-Hafalan</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        /* Animasi sederhana */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Mobile */
        @media (max-width: 767px) {
            #sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 50;
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
            }

            #sidebar.active {
                transform: translateX(0);
            }

            #sidebarOverlay.active {
                display: block;
            }

            header h2 {
                font-size: 1rem;
            }

            .target-button-text {
                display: none;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .table-responsive table {
                min-width: 1100px;
            }
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen text-slate-800 flex">

    <!-- Overlay Sidebar Mobile -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden" onclick="tutupSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="w-64 bg-slate-900 text-slate-300 flex flex-col min-h-screen sticky top-0 z-30">
        <!-- Tombol Tutup Sidebar Mobile -->
        <div class="flex justify-end p-3 md:hidden">
            <button type="button" onclick="tutupSidebar()" class="text-slate-400 hover:text-white text-xl" aria-label="Tutup menu">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Logo -->
        <div class="p-5 border-b border-slate-800 flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white text-xl font-bold shadow-lg shadow-emerald-500/20">
                <i class="fa-solid fa-quran"></i>
            </div>
            <div>
                <h1 class="font-bold text-white text-lg leading-tight">E-Hafalan</h1>
                <span class="text-xs text-emerald-400 font-medium">Panel Santri</span>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto text-sm">
            <a href="dasboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-chart-pie text-lg w-5"></i>
                <span>Dashboard</span>
            </a>

            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Hafalan Saya</div>

            <a href="hafalan.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-emerald-600 text-white font-medium shadow-lg shadow-emerald-600/30">
                <i class="fa-solid fa-book-quran text-lg w-5"></i>
                <span>Hafalan Saya</span>
            </a>

            <a href="target.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-bullseye text-slate-400 w-5"></i>
                <span>Target Hafalan</span>
            </a>

            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase tracking-wider text-slate-500">Akun Saya</div>

            <a href="profil.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fa-solid fa-user text-slate-400 w-5"></i>
                <span>Profil Saya</span>
            </a>
        </nav>

        <!-- User Profile -->
        <div class="p-4 border-t border-slate-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white font-bold text-sm">
                        <?php echo strtoupper(substr(htmlspecialchars($nama_user), 0, 1)); ?>
                    </div>
                    <div class="truncate w-28">
                        <p class="text-xs font-semibold text-white truncate"><?php echo htmlspecialchars($nama_user); ?></p>
                        <p class="text-[10px] text-slate-400 uppercase">Santri</p>
                    </div>
                </div>

                <a href="../../logout.php" class="text-slate-400 hover:text-red-400 p-2 rounded-lg transition-colors" title="Logout">
                    <i class="fa-solid fa-right-from-bracket text-lg"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0 overflow-x-hidden">
        <!-- Navbar Header -->
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <button type="button" onclick="bukaSidebar()" class="md:hidden text-slate-600 hover:text-emerald-600 text-xl" aria-label="Buka menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Hafalan Saya</h2>
                    <p class="text-xs text-slate-500">Riwayat hafalan dan setoran Al-Qur'an Anda</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <a href="target.php" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-medium shadow-md shadow-emerald-600/20 transition-all flex items-center space-x-2">
                    <i class="fa-solid fa-bullseye"></i>
                    <span class="target-button-text">Lihat Target</span>
                </a>
            </div>
        </header>

        <!-- Content Area -->
        <div class="p-6 space-y-6 fade-in">
            <!-- Welcome Card -->
            <div class="bg-gradient-to-r from-emerald-600 to-teal-500 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">
                    <div>
                        <p class="text-emerald-100 text-sm">Hafalan Saya</p>
                        <h1 class="text-2xl font-bold mt-1"><?php echo htmlspecialchars($nama_user); ?></h1>
                        <p class="text-emerald-100 text-xs mt-2">Lihat dan pantau seluruh riwayat setoran hafalan Al-Qur'an Anda.</p>
                    </div>

                    <div class="flex items-center justify-between gap-3 w-full md:w-auto">
                        <div class="text-left min-w-0">
                            <p class="text-xs text-emerald-100">Halaqah</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($data_santri['nama_halaqah'] ?? 'Belum ditentukan'); ?></p>
                        </div>
                        <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center">
                            <i class="fa-solid fa-book-quran text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Input Setoran -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100">
                    <h3 class="font-bold text-slate-800">Input Setoran Hafalan</h3>
                    <p class="text-xs text-slate-400 mt-1">Isi data hafalan yang ingin disetorkan kepada ustadz.</p>
                </div>

                <div class="p-5">
                    <?php if (!empty($pesan_sukses)): ?>
                        <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700">
                            <?php echo htmlspecialchars($pesan_sukses); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($pesan_error)): ?>
                        <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                            <?php echo htmlspecialchars($pesan_error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="jenis" class="block text-xs font-semibold text-slate-600 mb-2">Jenis Setoran</label>
                            <select id="jenis" name="jenis" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="">Pilih jenis setoran</option>
                                <option value="ziyadah">Ziyadah (Hafalan Baru)</option>
                                <option value="murajaah">Murajaah (Mengulang Hafalan)</option>
                            </select>
                        </div>

                        <div>
                            <label for="surah_id" class="block text-xs font-semibold text-slate-600 mb-2">Surah</label>
                            <select id="surah_id" name="surah_id" onchange="autoIsiJuz(this)" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="">Pilih surah</option>
                                <?php while ($surah = mysqli_fetch_assoc($result_surah)): 
                                    $no_surah = (int) $surah['nomor_surah'];
                                    $juz_estimasi = isset($juz_map[$no_surah]) ? $juz_map[$no_surah] : 30;
                                ?>
                                    <option value="<?php echo (int) $surah['id']; ?>" data-juz="<?php echo $juz_estimasi; ?>">
                                        <?php echo $no_surah . '. ' . htmlspecialchars($surah['nama_surah']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label for="ayat_mulai" class="block text-xs font-semibold text-slate-600 mb-2">Ayat Mulai</label>
                            <input type="number" id="ayat_mulai" name="ayat_mulai" min="1" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label for="ayat_selesai" class="block text-xs font-semibold text-slate-600 mb-2">Ayat Selesai</label>
                            <input type="number" id="ayat_selesai" name="ayat_selesai" min="1" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label for="juz" class="block text-xs font-semibold text-slate-600 mb-2">Juz</label>
                            <input type="number" id="juz" name="juz" min="1" max="30" required placeholder="Pilih surah..." class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label for="halaman" class="block text-xs font-semibold text-slate-600 mb-2">Halaman</label>
                            <input type="number" id="halaman" name="halaman" min="1" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div class="md:col-span-2">
                            <label for="catatan" class="block text-xs font-semibold text-slate-600 mb-2">Catatan (Opsional)</label>
                            <textarea id="catatan" name="catatan" rows="3" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Tambahkan catatan setoran jika diperlukan"></textarea>
                        </div>

                        <div class="md:col-span-2 flex justify-end">
                            <button type="submit" name="simpan_setoran" class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-md shadow-emerald-600/20 transition-all">
                                <i class="fa-solid fa-save mr-2"></i> Simpan Setoran
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistic Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500 font-medium">Total Setoran</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1"><?php echo $total_setoran; ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500 font-medium">Ziyadah</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1"><?php echo $total_ziyadah; ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-book-quran"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500 font-medium">Murajaah</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1"><?php echo $total_murajaah; ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-rotate"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500 font-medium">Rata-rata Nilai</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1"><?php echo $rata_nilai; ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-star"></i>
                    </div>
                </div>
            </div>

            <!-- Riwayat Setoran -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800">Riwayat Setoran Hafalan</h3>
                        <p class="text-xs text-slate-400 mt-1">Seluruh data setoran hafalan Anda</p>
                    </div>
                    <div class="text-xs text-slate-400">
                        Total: <strong class="text-emerald-600"><?php echo $total_setoran; ?></strong> setoran
                    </div>
                </div>

                <div class="overflow-x-auto table-responsive">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                                <th class="p-4 font-semibold">Tanggal</th>
                                <th class="p-4 font-semibold">Jenis</th>
                                <th class="p-4 font-semibold">Surah & Ayat</th>
                                <th class="p-4 font-semibold">Juz</th>
                                <th class="p-4 font-semibold">Halaman</th>
                                <th class="p-4 font-semibold">Nilai</th>
                                <th class="p-4 font-semibold">Predikat</th>
                                <th class="p-4 font-semibold">Status</th>
                                <th class="p-4 font-semibold">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (mysqli_num_rows($result_setoran) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result_setoran)): ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="p-4 text-xs font-medium text-slate-600 whitespace-nowrap">
                                            <?php echo date('d-m-Y H:i', strtotime($row['tanggal_setor'])); ?>
                                        </td>
                                        <td class="p-4 capitalize whitespace-nowrap">
                                            <?php if ($row['jenis'] === 'ziyadah'): ?>
                                                <span class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200">
                                                    Ziyadah
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-200">
                                                    Murajaah
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 font-medium text-slate-800 whitespace-nowrap">
                                            <?php echo htmlspecialchars($row['nama_surah'] ?? 'Surah ID: ' . $row['surah_id']); ?> 
                                            <span class="text-xs text-slate-500 font-normal">
                                                (Ayat <?php echo $row['ayat_mulai']; ?> - <?php echo $row['ayat_selesai']; ?>)
                                            </span>
                                        </td>
                                        <td class="p-4 text-slate-600 font-medium whitespace-nowrap">
                                            Juz <?php echo $row['juz']; ?>
                                        </td>
                                        <td class="p-4 text-slate-600 whitespace-nowrap">
                                            Hal. <?php echo $row['halaman']; ?>
                                        </td>
                                        <td class="p-4 font-bold text-slate-800 whitespace-nowrap">
                                            <?php echo $row['nilai_angka'] !== null ? $row['nilai_angka'] : '-'; ?>
                                        </td>
                                        <td class="p-4 whitespace-nowrap">
                                            <?php if (!empty($row['nilai_huruf'])): ?>
                                                <span class="font-semibold text-slate-700"><?php echo htmlspecialchars($row['nilai_huruf']); ?></span>
                                            <?php else: ?>
                                                <span class="text-slate-400">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 whitespace-nowrap">
                                            <?php
                                            $status = $row['status'] ?? 'pending';
                                            if ($status === 'diterima' || $status === 'lulus') {
                                                echo '<span class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-emerald-100 text-emerald-800">Diterima</span>';
                                            } elseif ($status === 'ditolak' || $status === 'mengulang') {
                                                echo '<span class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-red-100 text-red-800">Mengulang</span>';
                                            } else {
                                                echo '<span class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-amber-100 text-amber-800">Menunggu</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="p-4 text-xs text-slate-500 max-w-xs truncate">
                                            <?php echo !empty($row['catatan']) ? htmlspecialchars($row['catatan']) : '-'; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="p-6 text-center text-slate-400 text-sm">
                                        Belum ada riwayat setoran hafalan.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- JavaScript Auto-Fill Juz & Sidebar Toggle -->
    <script>
        function autoIsiJuz(selectElement) {
            var selectedOption = selectElement.options[selectElement.selectedIndex];
            var juz = selectedOption.getAttribute('data-juz');
            var inputJuz = document.getElementById('juz');
            
            if (juz) {
                inputJuz.value = juz;
            } else {
                inputJuz.value = '';
            }
        }

        function bukaSidebar() {
            document.getElementById('sidebar').classList.add('active');
            document.getElementById('sidebarOverlay').classList.add('active');
        }

        function tutupSidebar() {
            document.getElementById('sidebar').classList.remove('active');
            document.getElementById('sidebarOverlay').classList.remove('active');
        }
    </script>
</body>
</html>