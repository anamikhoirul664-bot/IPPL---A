
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
        nama_surah,
        nama_arab,
        jumlah_ayat
    FROM surah
    ORDER BY id ASC
";

$result_surah = mysqli_query($koneksi, $query_surah);

if (!$result_surah) {
    die("Data surah gagal dimuat: " . mysqli_error($koneksi));
}



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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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

        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE MOBILE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 767px) {

            /* Sidebar pada layar HP */
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

            /* Overlay sidebar */
            #sidebarOverlay.active {
                display: block;
            }

            /* Ukuran judul header pada HP */
            header h2 {
                font-size: 1rem;
            }

            /* Tombol target pada HP */
            .target-button-text {
                display: none;
            }

            /* Tabel dapat digeser ke samping */
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


    <!-- =========================================================
         OVERLAY SIDEBAR MOBILE
    ========================================================== -->

    <div id="sidebarOverlay"
        class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"
        onclick="tutupSidebar()">
    </div>


    <!-- =========================================================
         SIDEBAR
    ========================================================== -->

    <aside id="sidebar"
        class="w-64 bg-slate-900 text-slate-300 flex flex-col min-h-screen sticky top-0 z-30">

        <!-- Tombol Tutup Sidebar Mobile -->
        <div class="flex justify-end p-3 md:hidden">

            <button type="button"
                onclick="tutupSidebar()"
                class="text-slate-400 hover:text-white text-xl"
                aria-label="Tutup menu">

                <i class="fa-solid fa-xmark"></i>

            </button>

        </div>


        <!-- Logo -->
        <div class="p-5 border-b border-slate-800 flex items-center space-x-3">

            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500
                    flex items-center justify-center text-white text-xl font-bold
                    shadow-lg shadow-emerald-500/20">

                <i class="fa-solid fa-quran"></i>

            </div>

            <div>

                <h1 class="font-bold text-white text-lg leading-tight">
                    E-Hafalan
                </h1>

                <span class="text-xs text-emerald-400 font-medium">
                    Panel Santri
                </span>

            </div>

        </div>


        <!-- Navigation -->
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto text-sm">


            <!-- Dashboard -->
            <a href="dasboard.php"
                class="flex items-center space-x-3 px-4 py-3 rounded-xl
                hover:bg-slate-800 hover:text-white transition-all">

                <i class="fa-solid fa-chart-pie text-lg w-5"></i>

                <span>Dashboard</span>

            </a>


            <!-- Hafalan -->
            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase
                    tracking-wider text-slate-500">

                Hafalan Saya

            </div>


            <!-- Hafalan Saya AKTIF -->
            <a href="hafalan.php"
                class="flex items-center space-x-3 px-4 py-3 rounded-xl
                bg-emerald-600 text-white font-medium
                shadow-lg shadow-emerald-600/30">

                <i class="fa-solid fa-book-quran text-lg w-5"></i>

                <span>Hafalan Saya</span>

            </a>


            <!-- Target Hafalan -->
            <a href="target.php"
                class="flex items-center space-x-3 px-4 py-2.5 rounded-xl
                hover:bg-slate-800 hover:text-white transition-all">

                <i class="fa-solid fa-bullseye text-slate-400 w-5"></i>

                <span>Target Hafalan</span>

            </a>


            <!-- Profil -->
            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase
                    tracking-wider text-slate-500">

                Akun Saya

            </div>


            <a href="profil.php"
                class="flex items-center space-x-3 px-4 py-2.5 rounded-xl
                hover:bg-slate-800 hover:text-white transition-all">

                <i class="fa-solid fa-user text-slate-400 w-5"></i>

                <span>Profil Saya</span>

            </a>

        </nav>


        <!-- User -->
        <div class="p-4 border-t border-slate-800">

            <div class="flex items-center justify-between">

                <div class="flex items-center space-x-3">

                    <div class="w-9 h-9 rounded-full bg-emerald-600
                            flex items-center justify-center text-white
                            font-bold text-sm">

                        <?php
                        echo strtoupper(
                            substr(
                                htmlspecialchars($nama_user),
                                0,
                                1
                            )
                        );
                        ?>

                    </div>


                    <div class="truncate w-28">

                        <p class="text-xs font-semibold text-white truncate">

                            <?php
                            echo htmlspecialchars($nama_user);
                            ?>

                        </p>

                        <p class="text-[10px] text-slate-400 uppercase">
                            Santri
                        </p>

                    </div>

                </div>


                <!-- Logout -->
                <a href="../../logout.php"
                    class="text-slate-400 hover:text-red-400 p-2
                    rounded-lg transition-colors"
                    title="Logout">

                    <i class="fa-solid fa-right-from-bracket text-lg"></i>

                </a>

            </div>

        </div>

    </aside>


    <!-- =========================================================
         MAIN CONTENT
    ========================================================== -->

    <main class="flex-1 flex flex-col min-w-0 overflow-x-hidden">


        <!-- =====================================================
             NAVBAR
        ====================================================== -->

        <header class="bg-white border-b border-slate-200 px-6 py-4
                flex items-center justify-between sticky top-0 z-20">

            <div class="flex items-center gap-3">

                <!-- Tombol Menu Mobile -->
                <button type="button"
                    onclick="bukaSidebar()"
                    class="md:hidden text-slate-600 hover:text-emerald-600 text-xl"
                    aria-label="Buka menu">

                    <i class="fa-solid fa-bars"></i>

                </button>

                <div>

                    <h2 class="text-xl font-bold text-slate-800">
                        Hafalan Saya
                    </h2>

                    <p class="text-xs text-slate-500">

                        Riwayat hafalan dan setoran Al-Qur'an Anda

                    </p>

                </div>

            </div>


            <div class="flex items-center space-x-3">

                <!-- Target Button -->
                <a href="target.php"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700
                    text-white rounded-xl text-xs font-medium
                    shadow-md shadow-emerald-600/20 transition-all
                    flex items-center space-x-2">

                    <i class="fa-solid fa-bullseye"></i>

                    <span class="target-button-text">Lihat Target</span>

                </a>

            </div>

        </header>


        <!-- =====================================================
             CONTENT
        ======================================================= -->

        <div class="p-6 space-y-6 fade-in">


            <!-- =================================================
                 WELCOME CARD
            ================================================== -->

            <div class="bg-gradient-to-r from-emerald-600 to-teal-500
                    rounded-2xl p-6 text-white shadow-lg">

                <div class="flex flex-col md:flex-row
                        md:items-center md:justify-between gap-5">


                    <div>

                        <p class="text-emerald-100 text-sm">
                            Hafalan Saya
                        </p>

                        <h1 class="text-2xl font-bold mt-1">

                            <?php echo htmlspecialchars($nama_user); ?>

                        </h1>

                        <p class="text-emerald-100 text-xs mt-2">

                            Lihat dan pantau seluruh riwayat setoran
                            hafalan Al-Qur'an Anda.

                        </p>

                    </div>


                    <div class="flex items-center justify-between gap-3 w-full md:w-auto">

                        <div class="text-left min-w-0">

                            <p class="text-xs text-emerald-100">
                                Halaqah
                            </p>

                            <p class="font-semibold">

                                <?php
                                echo htmlspecialchars(
                                    $data_santri['nama_halaqah']
                                        ?? 'Belum ditentukan'
                                );
                                ?>

                            </p>

                        </div>


                        <div class="w-14 h-14 rounded-2xl bg-white/20
                                flex items-center justify-center">

                            <i class="fa-solid fa-book-quran text-2xl"></i>

                        </div>

                    </div>

                </div>

            </div>



            <!-- =================================================
                 FORM INPUT SETORAN
            ================================================== -->

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100">
                    <h3 class="font-bold text-slate-800">Input Setoran Hafalan</h3>
                    <p class="text-xs text-slate-400 mt-1">
                        Isi data hafalan yang ingin disetorkan kepada ustadz.
                    </p>
                </div>

                <div class="p-5">
                    <?php if (!empty($pesan_sukses)): ?>
                        <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200
                                    px-4 py-3 text-sm text-emerald-700">
                            <?php echo htmlspecialchars($pesan_sukses); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($pesan_error)): ?>
                        <div class="mb-4 rounded-xl bg-red-50 border border-red-200
                                    px-4 py-3 text-sm text-red-700">
                            <?php echo htmlspecialchars($pesan_error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="jenis" class="block text-xs font-semibold text-slate-600 mb-2">
                                Jenis Setoran
                            </label>
                            <select id="jenis" name="jenis" required
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="">Pilih jenis setoran</option>
                                <option value="ziyadah">Ziyadah (Hafalan Baru)</option>
                                <option value="murajaah">Murajaah (Mengulang Hafalan)</option>
                            </select>
                        </div>

                        <div>
                            <label for="surah_id" class="block text-xs font-semibold text-slate-600 mb-2">
                                Surah
                            </label>
                            <select id="surah_id" name="surah_id" required
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="">Pilih surah</option>
                                <?php while ($surah = mysqli_fetch_assoc($result_surah)): ?>
                                    <option value="<?php echo (int) $surah['id']; ?>">
                                        <?php echo htmlspecialchars($surah['nama_surah']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label for="ayat_mulai" class="block text-xs font-semibold text-slate-600 mb-2">
                                Ayat Mulai
                            </label>
                            <input type="number" id="ayat_mulai" name="ayat_mulai" min="1" required
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label for="ayat_selesai" class="block text-xs font-semibold text-slate-600 mb-2">
                                Ayat Selesai
                            </label>
                            <input type="number" id="ayat_selesai" name="ayat_selesai" min="1" required
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label for="juz" class="block text-xs font-semibold text-slate-600 mb-2">
                                Juz
                            </label>
                            <input type="number" id="juz" name="juz" min="1" max="30" required
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label for="halaman" class="block text-xs font-semibold text-slate-600 mb-2">
                                Halaman
                            </label>
                            <input type="number" id="halaman" name="halaman" min="1" required
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div class="md:col-span-2">
                            <label for="catatan" class="block text-xs font-semibold text-slate-600 mb-2">
                                Catatan (Opsional)
                            </label>
                            <textarea id="catatan" name="catatan" rows="3"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                placeholder="Tambahkan catatan setoran jika diperlukan"></textarea>
                        </div>

                        <div class="md:col-span-2 flex justify-end">
                            <button type="submit" name="simpan_setoran"
                                class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700
                                       text-white text-sm font-semibold shadow-md shadow-emerald-600/20
                                       transition-all">
                                <i class="fa-solid fa-save mr-2"></i>
                                Simpan Setoran
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- =================================================
                 STATISTIC CARDS
            ================================================== -->

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">


                <!-- Total Setoran -->
                <div class="bg-white p-5 rounded-2xl
                        border border-slate-200/80 shadow-sm
                        flex items-center justify-between">

                    <div>

                        <p class="text-xs text-slate-500 font-medium">
                            Total Setoran
                        </p>

                        <h3 class="text-2xl font-bold text-slate-800 mt-1">

                            <?php echo $total_setoran; ?>

                        </h3>

                    </div>


                    <div class="w-12 h-12 rounded-2xl bg-emerald-50
                            text-emerald-600 flex items-center
                            justify-center text-xl">

                        <i class="fa-solid fa-book-open"></i>

                    </div>

                </div>


                <!-- Ziyadah -->
                <div class="bg-white p-5 rounded-2xl
                        border border-slate-200/80 shadow-sm
                        flex items-center justify-between">

                    <div>

                        <p class="text-xs text-slate-500 font-medium">
                            Ziyadah
                        </p>

                        <h3 class="text-2xl font-bold text-slate-800 mt-1">

                            <?php echo $total_ziyadah; ?>

                        </h3>

                    </div>


                    <div class="w-12 h-12 rounded-2xl bg-teal-50
                            text-teal-600 flex items-center
                            justify-center text-xl">

                        <i class="fa-solid fa-book-quran"></i>

                    </div>

                </div>


                <!-- Murajaah -->
                <div class="bg-white p-5 rounded-2xl
                        border border-slate-200/80 shadow-sm
                        flex items-center justify-between">

                    <div>

                        <p class="text-xs text-slate-500 font-medium">
                            Murajaah
                        </p>

                        <h3 class="text-2xl font-bold text-slate-800 mt-1">

                            <?php echo $total_murajaah; ?>

                        </h3>

                    </div>


                    <div class="w-12 h-12 rounded-2xl bg-indigo-50
                            text-indigo-600 flex items-center
                            justify-center text-xl">

                        <i class="fa-solid fa-rotate"></i>

                    </div>

                </div>


                <!-- Rata-rata -->
                <div class="bg-white p-5 rounded-2xl
                        border border-slate-200/80 shadow-sm
                        flex items-center justify-between">

                    <div>

                        <p class="text-xs text-slate-500 font-medium">
                            Rata-rata Nilai
                        </p>

                        <h3 class="text-2xl font-bold text-slate-800 mt-1">

                            <?php echo $rata_nilai; ?>

                        </h3>

                    </div>


                    <div class="w-12 h-12 rounded-2xl bg-amber-50
                            text-amber-600 flex items-center
                            justify-center text-xl">

                        <i class="fa-solid fa-star"></i>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 RIWAYAT SETORAN
            ================================================== -->

            <div class="bg-white rounded-2xl
                    border border-slate-200/80 shadow-sm overflow-hidden">


                <!-- Header Card -->
                <div class="p-5 border-b border-slate-100
                        flex items-center justify-between">

                    <div>

                        <h3 class="font-bold text-slate-800">
                            Riwayat Setoran Hafalan
                        </h3>

                        <p class="text-xs text-slate-400 mt-1">
                            Seluruh data setoran hafalan Anda
                        </p>

                    </div>


                    <div class="text-xs text-slate-400">

                        Total:

                        <strong class="text-emerald-600">
                            <?php echo $total_setoran; ?>
                        </strong>

                        setoran

                    </div>

                </div>


                <!-- Table -->
                <div class="overflow-x-auto table-responsive">

                    <table class="w-full text-left border-collapse text-sm">

                        <thead>

                            <tr class="bg-slate-50 text-slate-500
                                    text-xs uppercase tracking-wider
                                    border-b border-slate-100">

                                <th class="py-3 px-5 font-semibold">
                                    No
                                </th>

                                <th class="py-3 px-5 font-semibold">
                                    Jenis
                                </th>

                                <th class="py-3 px-5 font-semibold">
                                    Surah
                                </th>

                                <th class="py-3 px-5 font-semibold">
                                    Ayat
                                </th>

                                <th class="py-3 px-5 font-semibold">
                                    Juz
                                </th>

                                <th class="py-3 px-5 font-semibold">
                                    Halaman
                                </th>

                                <th class="py-3 px-5 font-semibold">
                                    Kelancaran
                                </th>

                                <th class="py-3 px-5 font-semibold">
                                    Makhroj
                                </th>

                                <th class="py-3 px-5 font-semibold">
                                    Tajwid
                                </th>

                                <th class="py-3 px-5 font-semibold">
                                    Nilai
                                </th>

                                <th class="py-3 px-5 font-semibold">
                                    Tanggal
                                </th>

                                <th class="py-3 px-5 font-semibold">
                                    Catatan
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-slate-100
                                text-slate-700">


                            <?php if (mysqli_num_rows($result_setoran) > 0): ?>

                                <?php
                                $no = 1;

                                while ($row = mysqli_fetch_assoc($result_setoran)):
                                ?>

                                    <tr class="hover:bg-slate-50/80 transition-colors">


                                        <!-- No -->
                                        <td class="py-3 px-5 text-xs text-slate-500">

                                            <?php echo $no++; ?>

                                        </td>


                                        <!-- Jenis -->
                                        <td class="py-3 px-5">

                                            <?php if ($row['jenis'] == 'ziyadah'): ?>

                                                <span class="px-2.5 py-1 rounded-full
                                                        text-xs font-semibold
                                                        bg-emerald-100
                                                        text-emerald-700">

                                                    Ziyadah

                                                </span>

                                            <?php else: ?>

                                                <span class="px-2.5 py-1 rounded-full
                                                        text-xs font-semibold
                                                        bg-indigo-100
                                                        text-indigo-700">

                                                    Murajaah

                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <!-- Surah -->
                                        <td class="py-3 px-5">

                                            <p class="font-medium">

                                                <?php
                                                echo htmlspecialchars(
                                                    $row['nama_surah']
                                                        ?? '-'
                                                );
                                                ?>

                                            </p>

                                            <?php if (!empty($row['nama_arab'])): ?>

                                                <span class="text-xs text-slate-400">

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $row['nama_arab']
                                                    );
                                                    ?>

                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <!-- Ayat -->
                                        <td class="py-3 px-5">

                                            <?php
                                            echo ($row['ayat_mulai'] ?? '-') .
                                                ' - ' .
                                                ($row['ayat_selesai'] ?? '-');
                                            ?>

                                        </td>


                                        <!-- Juz -->
                                        <td class="py-3 px-5">

                                            Juz <?php echo $row['juz']; ?>

                                        </td>


                                        <!-- Halaman -->
                                        <td class="py-3 px-5">

                                            <?php
                                            echo $row['halaman'] ?? '-';
                                            ?>

                                        </td>


                                        <!-- Kelancaran -->
                                        <td class="py-3 px-5">

                                            <?php if (!empty($row['kelancaran'])): ?>

                                                <span class="px-2.5 py-1 rounded-full
                                                        text-xs font-semibold
                                                        bg-teal-100 text-teal-700">

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $row['kelancaran']
                                                    );
                                                    ?>

                                                </span>

                                            <?php else: ?>

                                                -

                                            <?php endif; ?>

                                        </td>


                                        <!-- Makhroj -->
                                        <td class="py-3 px-5">

                                            <?php if (!empty($row['makhroj'])): ?>

                                                <span class="px-2.5 py-1 rounded-full
                                                        text-xs font-semibold
                                                        bg-indigo-100 text-indigo-700">

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $row['makhroj']
                                                    );
                                                    ?>

                                                </span>

                                            <?php else: ?>

                                                -

                                            <?php endif; ?>

                                        </td>


                                        <!-- Tajwid -->
                                        <td class="py-3 px-5">

                                            <?php if (!empty($row['tajwid'])): ?>

                                                <span class="px-2.5 py-1 rounded-full
                                                        text-xs font-semibold
                                                        bg-amber-100 text-amber-700">

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $row['tajwid']
                                                    );
                                                    ?>

                                                </span>

                                            <?php else: ?>

                                                -

                                            <?php endif; ?>

                                        </td>


                                        <!-- Nilai -->
                                        <td class="py-3 px-5">

                                            <?php if ($row['nilai_angka'] !== null): ?>

                                                <span class="font-semibold
                                                        text-emerald-600">

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $row['nilai_angka']
                                                    );
                                                    ?>

                                                </span>

                                            <?php else: ?>

                                                -

                                            <?php endif; ?>

                                        </td>


                                        <!-- Tanggal -->
                                        <td class="py-3 px-5 text-xs text-slate-500 whitespace-nowrap">

                                            <?php
                                            echo date(
                                                'd M Y H:i',
                                                strtotime(
                                                    $row['tanggal_setor']
                                                )
                                            );
                                            ?>

                                        </td>


                                        <!-- Catatan -->
                                        <td class="py-3 px-5 text-xs text-slate-500">

                                            <?php
                                            echo !empty($row['catatan'])
                                                ? htmlspecialchars($row['catatan'])
                                                : '-';
                                            ?>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>


                            <?php else: ?>

                                <tr>

                                    <td colspan="12"
                                        class="py-10 text-center
                                        text-slate-400 text-xs">

                                        <div class="mb-2">

                                            <i class="fa-solid fa-book-open
                                                text-2xl"></i>

                                        </div>

                                        Belum ada riwayat setoran hafalan.

                                    </td>

                                </tr>

                            <?php endif; ?>


                        </tbody>

                    </table>

                </div>

            </div>


            <!-- =================================================
                 INFORMASI
            ================================================== -->

            <div class="bg-white rounded-2xl
                    border border-slate-200/80 shadow-sm overflow-hidden">

                <div class="p-5">

                    <div class="flex items-start space-x-3">

                        <div class="w-9 h-9 rounded-xl
                                bg-emerald-50 text-emerald-600
                                flex items-center justify-center
                                flex-shrink-0">

                            <i class="fa-solid fa-circle-info"></i>

                        </div>


                        <div>

                            <h3 class="text-sm font-semibold text-slate-700">
                                Informasi Hafalan
                            </h3>

                            <p class="text-xs text-slate-500 mt-1">
                                Halaman ini menampilkan seluruh riwayat
                                setoran hafalan yang telah dicatat oleh
                                ustadz pembimbing Anda.
                            </p>

                        </div>

                    </div>

                </div>

            </div>


        </div>

    </main>


    <!-- =========================================================
         JAVASCRIPT
    ========================================================== -->

    <script>

        /*
        |--------------------------------------------------------------------------
        | ANIMASI
        |--------------------------------------------------------------------------
        */

        document.addEventListener('DOMContentLoaded', function() {

            const elements = document.querySelectorAll('.fade-in');

            elements.forEach(function(element) {

                element.style.opacity = '1';

            });

        });


        /*
        |--------------------------------------------------------------------------
        | KONFIRMASI LOGOUT
        |--------------------------------------------------------------------------
        */

        const logoutButton = document.querySelector(
            'a[href="../../logout.php"]'
        );

        if (logoutButton) {

            logoutButton.addEventListener('click', function(event) {

                const yakin = confirm(
                    'Apakah Anda yakin ingin keluar dari sistem?'
                );

                if (!yakin) {

                    event.preventDefault();

                }

            });

        }


        /*
        |--------------------------------------------------------------------------
        | SIDEBAR RESPONSIF MOBILE
        |--------------------------------------------------------------------------
        */

        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function bukaSidebar() {

            sidebar.classList.add('active');
            sidebarOverlay.classList.add('active');

        }

        function tutupSidebar() {

            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');

        }


        // Menutup sidebar setelah memilih menu pada HP
        const sidebarLinks = document.querySelectorAll('#sidebar a');

        sidebarLinks.forEach(function(link) {

            link.addEventListener('click', function() {

                if (window.innerWidth <= 767) {

                    tutupSidebar();

                }

            });

        });


        // Mengembalikan sidebar ketika layar diperbesar
        window.addEventListener('resize', function() {

            if (window.innerWidth >= 768) {

                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');

            }

        });

    </script>


</body>

</html>