<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('santri');

$user_id = getUserId();
$nama_user = getUserNama();

/* =========================================================
   DATA SANTRI
========================================================= */

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

/* =========================================================
   TOTAL SETORAN
========================================================= */

$query_total_setoran = "
    SELECT COUNT(*) AS total
    FROM setoran
    WHERE santri_id = ?
";

$stmt = mysqli_prepare($koneksi, $query_total_setoran);
mysqli_stmt_bind_param($stmt, "i", $santri_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$total_setoran = mysqli_fetch_assoc($result)['total'] ?? 0;

/* =========================================================
   TARGET HAFALAN
========================================================= */

$query_target = "
    SELECT *
    FROM target_hafalan
    WHERE santri_id = ?
    AND status = 'Berjalan'
    ORDER BY tgl_tenggat ASC
    LIMIT 1
";

$stmt = mysqli_prepare($koneksi, $query_target);
mysqli_stmt_bind_param($stmt, "i", $santri_id);
mysqli_stmt_execute($stmt);

$result_target = mysqli_stmt_get_result($stmt);
$target = mysqli_fetch_assoc($result_target);

$target_juz = $target['target_juz'] ?? ($data_santri['target_juz'] ?? 0);
$status_target = $target['status'] ?? 'Belum ada target';

$persentase_target = 0;

$total_hafalan = $data_santri['total_hafalan'] ?? 0;

if ($target_juz > 0) {
    $persentase_target = round(($total_hafalan / $target_juz) * 100);

    if ($persentase_target > 100) {
        $persentase_target = 100;
    }
}

/* =========================================================
   SETORAN TERBARU
========================================================= */

$query_setoran = "
    SELECT
        s.*,
        sr.nama_surah,
        sr.nama_arab
    FROM setoran s
    LEFT JOIN surah sr ON s.surah_id = sr.id
    WHERE s.santri_id = ?
    ORDER BY s.tanggal_setor DESC
    LIMIT 5
";

$stmt = mysqli_prepare($koneksi, $query_setoran);
mysqli_stmt_bind_param($stmt, "i", $santri_id);
mysqli_stmt_execute($stmt);

$result_setoran = mysqli_stmt_get_result($stmt);

/* =========================================================
   NILAI TERBARU
========================================================= */

$query_nilai = "
    SELECT *
    FROM penilaian
    WHERE santri_id = ?
    ORDER BY tanggal DESC
    LIMIT 5
";

$stmt = mysqli_prepare($koneksi, $query_nilai);
mysqli_stmt_bind_param($stmt, "i", $santri_id);
mysqli_stmt_execute($stmt);

$result_nilai = mysqli_stmt_get_result($stmt);

/* =========================================================
   PENGUMUMAN
========================================================= */

$query_pengumuman = "
    SELECT *
    FROM pengumuman
    WHERE target_role IN ('semua', 'santri')
    ORDER BY tanggal DESC
    LIMIT 3
";

$result_pengumuman = mysqli_query($koneksi, $query_pengumuman);

/* =========================================================
   RATA-RATA NILAI
========================================================= */

$query_rata_nilai = "
    SELECT AVG(nilai) AS rata_nilai
    FROM penilaian
    WHERE santri_id = ?
";

$stmt = mysqli_prepare($koneksi, $query_rata_nilai);
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

    <title>Dashboard Santri - E-Hafalan</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

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

        #sidebar {
            transition: transform 0.3s ease-in-out;
        }

        @media (min-width: 768px) {
            #sidebar {
                transform: translateX(0) !important;
            }
        }

        @media (max-width: 767px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebarOverlay {
                display: none;
            }

            #sidebarOverlay.active {
                display: block;
            }
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen text-slate-800 flex">

    <!-- OVERLAY MOBILE -->
    <div id="sidebarOverlay"
        class="fixed inset-0 bg-black/50 z-40 hidden md:hidden">
    </div>

    <!-- SIDEBAR -->
    <aside id="sidebar"
        class="fixed md:sticky top-0 left-0 z-50
               w-64 bg-slate-900 text-slate-300
               flex flex-col min-h-screen
               -translate-x-full md:translate-x-0">

        <!-- LOGO -->
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

            <!-- TOMBOL TUTUP -->
            <button id="closeSidebar"
                type="button"
                class="ml-auto text-slate-400 hover:text-white md:hidden">

                <i class="fa-solid fa-xmark text-xl"></i>

            </button>

        </div>

        <!-- MENU -->
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto text-sm">

            <a href="dasboard.php"
                class="flex items-center space-x-3 px-4 py-3 rounded-xl
                       bg-emerald-600 text-white font-medium
                       shadow-lg shadow-emerald-600/30">

                <i class="fa-solid fa-chart-pie text-lg w-5"></i>
                <span>Dashboard</span>

            </a>

            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase
                        tracking-wider text-slate-500">

                Hafalan Saya

            </div>

            <a href="hafalan.php"
                class="flex items-center space-x-3 px-4 py-2.5 rounded-xl
                       hover:bg-slate-800 hover:text-white transition-all">

                <i class="fa-solid fa-book-quran text-slate-400 w-5"></i>
                <span>Hafalan Saya</span>

            </a>

            <a href="target.php"
                class="flex items-center space-x-3 px-4 py-2.5 rounded-xl
                       hover:bg-slate-800 hover:text-white transition-all">

                <i class="fa-solid fa-bullseye text-slate-400 w-5"></i>
                <span>Target Hafalan</span>

            </a>

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

        <!-- USER -->
        <div class="p-4 border-t border-slate-800">

            <div class="flex items-center justify-between">

                <div class="flex items-center space-x-3 min-w-0">

                    <div class="w-9 h-9 rounded-full bg-emerald-600
                                flex-shrink-0 flex items-center justify-center
                                text-white font-bold text-sm">

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
                            <?php echo htmlspecialchars($nama_user); ?>
                        </p>

                        <p class="text-[10px] text-slate-400 uppercase">
                            Santri
                        </p>

                    </div>

                </div>

                <a href="../../logout.php"
                    id="logoutButton"
                    class="text-slate-400 hover:text-red-400 p-2 rounded-lg transition-colors"
                    title="Logout">
                    <i class="fa-solid fa-right-from-bracket text-lg"></i>
                </a>

            </div>

        </div>

    </aside>

    <!-- KONTEN UTAMA -->
    <main class="flex-1 flex flex-col min-w-0 overflow-x-hidden">

        <!-- HEADER -->
        <header class="bg-white border-b border-slate-200
                       px-4 sm:px-6 py-4
                       flex items-center justify-between
                       sticky top-0 z-20">

            <div class="flex items-center gap-3 min-w-0">

                <!-- TOMBOL BUKA SIDEBAR -->
                <button id="openSidebar"
                    type="button"
                    class="md:hidden w-10 h-10 rounded-xl
                           bg-slate-100 text-slate-600
                           hover:bg-slate-200 flex-shrink-0">

                    <i class="fa-solid fa-bars text-lg"></i>

                </button>

                <div class="min-w-0">

                    <h2 class="text-lg sm:text-xl font-bold text-slate-800">
                        Dashboard Santri
                    </h2>

                    <p class="text-xs text-slate-500 truncate">
                        Selamat datang kembali,
                        <?php echo htmlspecialchars($nama_user); ?>!
                    </p>

                </div>

            </div>

            <a href="target.php"
                class="px-3 sm:px-4 py-2 bg-emerald-600 hover:bg-emerald-700
                       text-white rounded-xl text-xs font-medium
                       shadow-md shadow-emerald-600/20
                       flex items-center space-x-2 flex-shrink-0">

                <i class="fa-solid fa-bullseye"></i>

                <span class="hidden sm:inline">
                    Lihat Target
                </span>

            </a>

        </header>

        <!-- ISI DASHBOARD -->
        <div class="p-4 sm:p-6 space-y-6 fade-in">

            <!-- WELCOME CARD -->
            <div class="bg-gradient-to-r from-emerald-600 to-teal-500
                        rounded-2xl p-4 sm:p-6 text-white shadow-lg">

                <div class="flex flex-col md:flex-row
                            md:items-center md:justify-between gap-5">

                    <div>

                        <p class="text-emerald-100 text-sm">
                            Assalamu'alaikum,
                        </p>

                        <h1 class="text-xl sm:text-2xl font-bold mt-1">
                            <?php echo htmlspecialchars($nama_user); ?>
                        </h1>

                        <p class="text-emerald-100 text-xs mt-2">
                            Terus semangat menghafal dan murajaah
                            Al-Qur'an setiap hari.
                        </p>

                    </div>

                    <div class="flex items-center justify-between gap-3 w-full md:w-auto">

                        <div class="text-left sm:text-right">

                            <p class="text-xs text-emerald-100">
                                Halaqah
                            </p>

                            <p class="font-semibold text-sm sm:text-base">
                                <?php
                                echo htmlspecialchars(
                                    $data_santri['nama_halaqah']
                                        ?? 'Belum ditentukan'
                                );
                                ?>
                            </p>

                        </div>

                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl
                                    bg-white/20 flex items-center justify-center">

                            <i class="fa-solid fa-mosque text-xl sm:text-2xl"></i>

                        </div>

                    </div>

                </div>

            </div>

            <!-- STATISTIK -->
            <div class="grid grid-cols-1 sm:grid-cols-2
                        lg:grid-cols-3 gap-4 sm:gap-5">

                <!-- TOTAL HAFALAN -->
                <div class="bg-white p-4 sm:p-5 rounded-2xl
                            border border-slate-200/80 shadow-sm
                            flex items-center justify-between gap-3">

                    <div>
                        <p class="text-xs text-slate-500 font-medium">
                            Total Hafalan
                        </p>

                        <h3 class="text-2xl font-bold text-slate-800 mt-1">
                            <?php echo $total_hafalan; ?>

                            <span class="text-sm font-medium text-slate-400">
                                Juz
                            </span>
                        </h3>
                    </div>

                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl
                                bg-emerald-50 text-emerald-600
                                flex items-center justify-center text-xl
                                flex-shrink-0">

                        <i class="fa-solid fa-book-quran"></i>

                    </div>

                </div>

                <!-- TOTAL SETORAN -->
                <div class="bg-white p-4 sm:p-5 rounded-2xl
                            border border-slate-200/80 shadow-sm
                            flex items-center justify-between gap-3">

                    <div>
                        <p class="text-xs text-slate-500 font-medium">
                            Total Setoran
                        </p>

                        <h3 class="text-2xl font-bold text-slate-800 mt-1">
                            <?php echo $total_setoran; ?>
                        </h3>
                    </div>

                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl
                                bg-amber-50 text-amber-600
                                flex items-center justify-center text-xl
                                flex-shrink-0">

                        <i class="fa-solid fa-book-open"></i>

                    </div>

                </div>

                <!-- RATA-RATA NILAI -->
                <div class="bg-white p-4 sm:p-5 rounded-2xl
                            border border-slate-200/80 shadow-sm
                            flex items-center justify-between gap-3">

                    <div>
                        <p class="text-xs text-slate-500 font-medium">
                            Rata-rata Nilai
                        </p>

                        <h3 class="text-2xl font-bold text-slate-800 mt-1">
                            <?php echo $rata_nilai; ?>
                        </h3>
                    </div>

                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl
                                bg-indigo-50 text-indigo-600
                                flex items-center justify-center text-xl
                                flex-shrink-0">

                        <i class="fa-solid fa-star"></i>

                    </div>

                </div>

            </div>

            <!-- TARGET DAN PROFIL -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- TARGET -->
                <div class="lg:col-span-2 bg-white rounded-2xl
                            border border-slate-200/80 shadow-sm overflow-hidden">

                    <div class="p-4 sm:p-5 border-b border-slate-100
                                flex items-center justify-between gap-3">

                        <div>
                            <h3 class="font-bold text-slate-800">
                                Target Hafalan
                            </h3>

                            <p class="text-xs text-slate-400 mt-1">
                                Progress hafalan Anda
                            </p>
                        </div>

                        <a href="target.php"
                            class="text-xs font-semibold text-emerald-600 hover:underline">
                            Detail
                        </a>

                    </div>

                    <div class="p-4 sm:p-5">

                        <?php if ($target): ?>

                            <div class="flex items-center justify-between
                                        mb-3 gap-3">

                                <div>
                                    <p class="text-sm font-semibold text-slate-700">
                                        Target <?php echo $target_juz; ?> Juz
                                    </p>

                                    <p class="text-xs text-slate-400">
                                        Tenggat:
                                        <?php
                                        echo date(
                                            'd M Y',
                                            strtotime($target['tgl_tenggat'])
                                        );
                                        ?>
                                    </p>
                                </div>

                                <span class="px-3 py-1 rounded-full
                                             bg-emerald-100 text-emerald-700
                                             text-xs font-semibold">

                                    <?php echo $persentase_target; ?>%

                                </span>

                            </div>

                            <div class="w-full bg-slate-100 rounded-full h-3">

                                <div class="bg-emerald-500 h-3 rounded-full"
                                    style="width: <?php echo $persentase_target; ?>%;">

                                </div>

                            </div>

                            <div class="flex justify-between mt-2">

                                <span class="text-xs text-slate-400">
                                    <?php echo $total_hafalan; ?> Juz
                                </span>

                                <span class="text-xs text-slate-400">
                                    <?php echo $target_juz; ?> Juz
                                </span>

                            </div>

                        <?php else: ?>

                            <div class="text-center py-5">

                                <div class="w-12 h-12 mx-auto rounded-full
                                            bg-slate-100 flex items-center
                                            justify-center text-slate-400">

                                    <i class="fa-solid fa-bullseye"></i>

                                </div>

                                <p class="text-sm text-slate-500 mt-3">
                                    Belum ada target hafalan yang sedang berjalan.
                                </p>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

                <!-- PROFIL SINGKAT -->
                <div class="bg-white rounded-2xl
                            border border-slate-200/80 shadow-sm overflow-hidden">

                    <div class="p-4 sm:p-5 border-b border-slate-100">

                        <h3 class="font-bold text-slate-800">
                            Profil Singkat
                        </h3>

                    </div>

                    <div class="p-4 sm:p-5 space-y-4">

                        <!-- NIS -->
                        <div class="flex items-center space-x-3">

                            <div class="w-9 h-9 rounded-xl bg-emerald-50
                                        text-emerald-600 flex items-center
                                        justify-center flex-shrink-0">

                                <i class="fa-solid fa-id-card"></i>

                            </div>

                            <div class="min-w-0">

                                <p class="text-[11px] text-slate-400">
                                    NIS
                                </p>

                                <p class="text-sm font-semibold text-slate-700 break-words">

                                    <?php
                                    echo htmlspecialchars(
                                        $data_santri['nis']
                                    );
                                    ?>

                                </p>

                            </div>

                        </div>

                        <!-- HALAQAH -->
                        <div class="flex items-center space-x-3">

                            <div class="w-9 h-9 rounded-xl bg-teal-50
                                        text-teal-600 flex items-center
                                        justify-center flex-shrink-0">

                                <i class="fa-solid fa-users"></i>

                            </div>

                            <div class="min-w-0">

                                <p class="text-[11px] text-slate-400">
                                    Halaqah
                                </p>

                                <p class="text-sm font-semibold text-slate-700 break-words">

                                    <?php
                                    echo htmlspecialchars(
                                        $data_santri['nama_halaqah']
                                            ?? 'Belum ditentukan'
                                    );
                                    ?>

                                </p>

                            </div>

                        </div>

                        <!-- USTADZ -->
                        <div class="flex items-center space-x-3">

                            <div class="w-9 h-9 rounded-xl bg-indigo-50
                                        text-indigo-600 flex items-center
                                        justify-center flex-shrink-0">

                                <i class="fa-solid fa-user-tie"></i>

                            </div>

                            <div class="min-w-0">

                                <p class="text-[11px] text-slate-400">
                                    Ustadz Pembimbing
                                </p>

                                <p class="text-sm font-semibold text-slate-700 break-words">

                                    <?php
                                    echo htmlspecialchars(
                                        $data_santri['nama_ustadz']
                                            ?? 'Belum ditentukan'
                                    );
                                    ?>

                                </p>

                            </div>

                        </div>

                        <a href="profil.php"
                            class="block text-center mt-4 px-4 py-2
                                  border border-slate-200 rounded-xl
                                  text-xs font-semibold text-slate-600
                                  hover:bg-slate-50 transition">

                            Lihat Profil

                        </a>

                    </div>

                </div>

            </div>

            <!-- SETORAN TERBARU -->
            <div class="bg-white rounded-2xl
                        border border-slate-200/80 shadow-sm overflow-hidden">

                <div class="p-4 sm:p-5 border-b border-slate-100
                            flex items-center justify-between gap-3">

                    <div>
                        <h3 class="font-bold text-slate-800">
                            Setoran Hafalan Terbaru
                        </h3>

                        <p class="text-xs text-slate-400 mt-1">
                            Riwayat setoran hafalan Anda
                        </p>
                    </div>

                    <a href="hafalan.php"
                        class="text-xs font-semibold text-emerald-600 hover:underline">

                        Lihat Semua

                    </a>

                </div>

                <!-- TABEL DAPAT DIGESER DI HP -->
                <div class="overflow-x-auto">

                    <table class="w-full min-w-[650px] text-left
                                  border-collapse text-sm">

                        <thead>

                            <tr class="bg-slate-50 text-slate-500
                                       text-xs uppercase tracking-wider
                                       border-b border-slate-100">

                                <th class="py-3 px-5 font-semibold">Jenis</th>
                                <th class="py-3 px-5 font-semibold">Surah</th>
                                <th class="py-3 px-5 font-semibold">Ayat</th>
                                <th class="py-3 px-5 font-semibold">Juz</th>
                                <th class="py-3 px-5 font-semibold">Nilai</th>
                                <th class="py-3 px-5 font-semibold">Tanggal</th>

                            </tr>

                        </thead>

                        <tbody class="divide-y divide-slate-100 text-slate-700">

                            <?php if (mysqli_num_rows($result_setoran) > 0): ?>

                                <?php while ($row = mysqli_fetch_assoc($result_setoran)): ?>

                                    <tr class="hover:bg-slate-50/80 transition-colors">

                                        <td class="py-3 px-5">

                                            <?php if ($row['jenis'] == 'ziyadah'): ?>

                                                <span class="px-2.5 py-1 rounded-full
                                                             text-xs font-semibold
                                                             bg-emerald-100 text-emerald-700">

                                                    Ziyadah

                                                </span>

                                            <?php else: ?>

                                                <span class="px-2.5 py-1 rounded-full
                                                             text-xs font-semibold
                                                             bg-indigo-100 text-indigo-700">

                                                    Murajaah

                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <td class="py-3 px-5">

                                            <p class="font-medium">
                                                <?php
                                                echo htmlspecialchars(
                                                    $row['nama_surah'] ?? '-'
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

                                        <td class="py-3 px-5">

                                            <?php
                                            echo ($row['ayat_mulai'] ?? '-') .
                                                ' - ' .
                                                ($row['ayat_selesai'] ?? '-');
                                            ?>

                                        </td>

                                        <td class="py-3 px-5">
                                            Juz <?php echo $row['juz']; ?>
                                        </td>

                                        <td class="py-3 px-5">

                                            <span class="font-semibold text-emerald-600">
                                                <?php echo $row['nilai_angka']; ?>
                                            </span>

                                        </td>

                                        <td class="py-3 px-5 text-xs text-slate-500">

                                            <?php
                                            echo date(
                                                'd M Y H:i',
                                                strtotime($row['tanggal_setor'])
                                            );
                                            ?>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>

                                    <td colspan="6"
                                        class="py-8 text-center text-slate-400 text-xs">

                                        <i class="fa-solid fa-book-open text-2xl mb-2"></i>

                                        <p>
                                            Belum ada riwayat setoran hafalan.
                                        </p>

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

            <!-- NILAI DAN PENGUMUMAN -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- NILAI -->
                <div class="bg-white rounded-2xl
                            border border-slate-200/80 shadow-sm overflow-hidden">

                    <div class="p-4 sm:p-5 border-b border-slate-100
                                flex items-center justify-between gap-3">

                        <h3 class="font-bold text-slate-800">
                            Nilai Terbaru
                        </h3>

                        <span class="text-xs text-slate-400">
                            Rata-rata:
                            <strong class="text-emerald-600">
                                <?php echo $rata_nilai; ?>
                            </strong>
                        </span>

                    </div>

                    <div class="p-4 sm:p-5">

                        <?php if (mysqli_num_rows($result_nilai) > 0): ?>

                            <div class="space-y-4">

                                <?php while ($nilai = mysqli_fetch_assoc($result_nilai)): ?>

                                    <div class="flex items-center justify-between
                                                gap-3 border-b border-slate-100 pb-3">

                                        <div class="min-w-0">

                                            <p class="text-sm font-semibold text-slate-700">
                                                <?php
                                                echo htmlspecialchars(
                                                    $nilai['jenis_ujian']
                                                );
                                                ?>
                                            </p>

                                            <p class="text-xs text-slate-400 mt-1">

                                                <?php
                                                echo $nilai['juz_diuji']
                                                    ? 'Juz ' . $nilai['juz_diuji']
                                                    : 'Juz -';
                                                ?>

                                                •

                                                <?php
                                                echo date(
                                                    'd M Y',
                                                    strtotime($nilai['tanggal'])
                                                );
                                                ?>

                                            </p>

                                        </div>

                                        <div class="text-right flex-shrink-0">

                                            <p class="text-lg font-bold text-emerald-600">
                                                <?php echo $nilai['nilai']; ?>
                                            </p>

                                            <?php if ($nilai['nilai'] >= 80): ?>

                                                <span class="text-[10px] text-emerald-600">
                                                    Sangat Baik
                                                </span>

                                            <?php elseif ($nilai['nilai'] >= 70): ?>

                                                <span class="text-[10px] text-teal-600">
                                                    Baik
                                                </span>

                                            <?php else: ?>

                                                <span class="text-[10px] text-amber-600">
                                                    Perlu Ditingkatkan
                                                </span>

                                            <?php endif; ?>

                                        </div>

                                    </div>

                                <?php endwhile; ?>

                            </div>

                        <?php else: ?>

                            <div class="text-center py-6 text-slate-400 text-xs">

                                <i class="fa-solid fa-star text-2xl mb-2"></i>

                                <p>
                                    Belum ada data penilaian.
                                </p>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

                <!-- PENGUMUMAN -->
                <div class="bg-white rounded-2xl
                            border border-slate-200/80 shadow-sm overflow-hidden">

                    <div class="p-4 sm:p-5 border-b border-slate-100">

                        <h3 class="font-bold text-slate-800">
                            Pengumuman
                        </h3>

                    </div>

                    <div class="p-4 sm:p-5">

                        <?php if (
                            $result_pengumuman &&
                            mysqli_num_rows($result_pengumuman) > 0
                        ): ?>

                            <div class="space-y-4">

                                <?php while (
                                    $pengumuman = mysqli_fetch_assoc($result_pengumuman)
                                ): ?>

                                    <div class="flex items-start space-x-3">

                                        <div class="w-9 h-9 rounded-xl
                                                    bg-amber-50 text-amber-600
                                                    flex items-center justify-center
                                                    flex-shrink-0">

                                            <i class="fa-solid fa-bullhorn"></i>

                                        </div>

                                        <div class="min-w-0">

                                            <h4 class="text-sm font-semibold text-slate-700">

                                                <?php
                                                echo htmlspecialchars(
                                                    $pengumuman['judul']
                                                );
                                                ?>

                                            </h4>

                                            <p class="text-xs text-slate-500 mt-1
                                                      line-clamp-2">

                                                <?php
                                                echo htmlspecialchars(
                                                    $pengumuman['isi']
                                                );
                                                ?>

                                            </p>

                                            <p class="text-[10px] text-slate-400 mt-1">

                                                <?php
                                                echo date(
                                                    'd M Y H:i',
                                                    strtotime($pengumuman['tanggal'])
                                                );
                                                ?>

                                            </p>

                                        </div>

                                    </div>

                                <?php endwhile; ?>

                            </div>

                        <?php else: ?>

                            <div class="text-center py-6 text-slate-400 text-xs">

                                <i class="fa-solid fa-bullhorn text-2xl mb-2"></i>

                                <p>
                                    Belum ada pengumuman.
                                </p>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    </main>

    <!-- Popup Konfirmasi Logout -->
    <div id="logoutModal"
        class="fixed inset-0 z-[999] hidden items-center justify-center
            bg-slate-950/70 backdrop-blur-sm px-5">

        <div id="logoutBox"
            class="w-full max-w-[340px] rounded-2xl bg-[#111a30]
                border border-slate-700/40 p-5 shadow-2xl
                opacity-0 scale-95 transition-all duration-200">

            <div class="mx-auto mb-3 flex h-12 w-12 items-center
                    justify-center rounded-full bg-red-500/10">
                <i class="fa-solid fa-right-from-bracket
                      text-xl text-red-500"></i>
            </div>

            <h3 class="text-center text-sm font-bold text-white">
                Konfirmasi Logout
            </h3>

            <p class="mx-auto mt-1.5 max-w-[260px] text-center
                  text-[10px] leading-relaxed text-slate-400">
                Apakah Anda yakin ingin keluar dari sistem ini?
            </p>

            <div class="mt-4 grid grid-cols-2 gap-2.5">
                <button type="button"
                    id="cancelLogout"
                    class="rounded-xl border border-slate-700
                           bg-transparent py-2.5 text-xs font-semibold
                           text-slate-300 transition hover:bg-slate-800">
                    Batal
                </button>

                <button type="button"
                    id="confirmLogout"
                    class="rounded-xl bg-red-500 py-2.5 text-xs
                           font-semibold text-white transition
                           hover:bg-red-600">
                    Ya, Keluar
                </button>
            </div>

        </div>
    </div>

    <!-- JAVASCRIPT -->
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
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            sidebar.style.transform = 'translateX(0)';
            overlay.classList.remove('hidden');
            overlay.classList.add('active');
        }

        function tutupSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            sidebar.style.transform = 'translateX(-100%)';
            overlay.classList.remove('active');
        }


        // Konfirmasi Logout
        document.addEventListener('DOMContentLoaded', function() {

            const logoutButton =
                document.getElementById('logoutButton');

            const logoutModal =
                document.getElementById('logoutModal');

            const logoutBox =
                document.getElementById('logoutBox');

            const cancelLogout =
                document.getElementById('cancelLogout');

            const confirmLogout =
                document.getElementById('confirmLogout');


            if (!logoutButton || !logoutModal || !logoutBox ||
                !cancelLogout || !confirmLogout) {
                return;
            }

            const openSidebar = document.getElementById('openSidebar');
            const closeSidebar = document.getElementById('closeSidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            if (openSidebar) {
                openSidebar.addEventListener('click', bukaSidebar);
            }

            if (closeSidebar) {
                closeSidebar.addEventListener('click', tutupSidebar);
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', tutupSidebar);
            }


            // Buka popup
            logoutButton.addEventListener('click', function(event) {
                event.preventDefault();

                logoutModal.classList.remove('hidden');
                logoutModal.classList.add('flex');

                requestAnimationFrame(function() {
                    logoutBox.classList.remove('opacity-0', 'scale-95');
                    logoutBox.classList.add('opacity-100', 'scale-100');
                });
            });


            // Tombol Batal
            cancelLogout.addEventListener('click', tutupLogout);


            // Tombol Ya, Keluar
            confirmLogout.addEventListener('click', function() {
                window.location.href = logoutButton.href;
            });


            // Klik area luar popup
            logoutModal.addEventListener('click', function(event) {
                if (event.target === logoutModal) {
                    tutupLogout();
                }
            });


            // Tombol Escape
            document.addEventListener('keydown', function(event) {
                if (
                    event.key === 'Escape' &&
                    !logoutModal.classList.contains('hidden')
                ) {
                    tutupLogout();
                }
            });


            function tutupLogout() {
                logoutBox.classList.remove('opacity-100', 'scale-100');
                logoutBox.classList.add('opacity-0', 'scale-95');

                setTimeout(function() {
                    logoutModal.classList.remove('flex');
                    logoutModal.classList.add('hidden');
                }, 200);
            }

        });
    </script>

</body>

</html>