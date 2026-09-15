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
| Mengambil data santri berdasarkan user yang sedang login
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
| STATISTIK HAFALAN
|--------------------------------------------------------------------------
*/

// Total setoran
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


// Total hafalan dari tabel santri
$total_hafalan = $data_santri['total_hafalan'] ?? 0;
$total_halaman = $data_santri['total_hafalan_halaman'] ?? 0;


// Target hafalan yang sedang berjalan
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


// Jika tidak ada target berjalan
$target_juz = $target['target_juz'] ?? $data_santri['target_juz'];
$status_target = $target['status'] ?? 'Belum ada target';


// Hitung persentase target
$persentase_target = 0;

if ($target_juz > 0) {
    $persentase_target = round(($total_hafalan / $target_juz) * 100);

    if ($persentase_target > 100) {
        $persentase_target = 100;
    }
}


/*
|--------------------------------------------------------------------------
| SETORAN TERBARU
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
    LIMIT 5
";

$stmt = mysqli_prepare($koneksi, $query_setoran);
mysqli_stmt_bind_param($stmt, "i", $santri_id);
mysqli_stmt_execute($stmt);

$result_setoran = mysqli_stmt_get_result($stmt);


/*
|--------------------------------------------------------------------------
| NILAI TERBARU
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| PENGUMUMAN TERBARU
|--------------------------------------------------------------------------
*/

$query_pengumuman = "
    SELECT *
    FROM pengumuman
    WHERE target_role IN ('semua', 'santri')
    ORDER BY tanggal DESC
    LIMIT 3
";

$result_pengumuman = mysqli_query($koneksi, $query_pengumuman);


/*
|--------------------------------------------------------------------------
| HITUNG RATA-RATA NILAI
|--------------------------------------------------------------------------
*/

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
    </style>

</head>


<body class="bg-slate-100 min-h-screen text-slate-800 flex">


    <!-- =========================================================
     SIDEBAR
========================================================= -->

    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col min-h-screen sticky top-0 z-30">

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
                  bg-emerald-600 text-white font-medium
                  shadow-lg shadow-emerald-600/30">

                <i class="fa-solid fa-chart-pie text-lg w-5"></i>

                <span>Dashboard</span>

            </a>


            <!-- Hafalan -->
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
========================================================= -->

    <main class="flex-1 flex flex-col min-w-0 overflow-x-hidden">


        <!-- =====================================================
         NAVBAR
    ====================================================== -->

        <header class="bg-white border-b border-slate-200 px-6 py-4
                   flex items-center justify-between sticky top-0 z-20">

            <div>

                <h2 class="text-xl font-bold text-slate-800">
                    Dashboard Santri
                </h2>

                <p class="text-xs text-slate-500">

                    Selamat datang kembali,
                    <?php echo htmlspecialchars($nama_user); ?>!

                </p>

            </div>


            <div class="flex items-center space-x-3">

                <!-- Target Button -->
                <a href="target.php"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700
                      text-white rounded-xl text-xs font-medium
                      shadow-md shadow-emerald-600/20 transition-all
                      flex items-center space-x-2">

                    <i class="fa-solid fa-bullseye"></i>

                    <span>Lihat Target</span>

                </a>

            </div>

        </header>



        <!-- =====================================================
         CONTENT
    ====================================================== -->

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
                            Assalamu'alaikum,
                        </p>

                        <h1 class="text-2xl font-bold mt-1">

                            <?php
                            echo htmlspecialchars($nama_user);
                            ?>

                        </h1>

                        <p class="text-emerald-100 text-xs mt-2">

                            Terus semangat menghafal dan murajaah
                            Al-Qur'an setiap hari.

                        </p>

                    </div>


                    <div class="flex items-center space-x-4">

                        <div class="text-right">

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

                            <i class="fa-solid fa-mosque text-2xl"></i>

                        </div>

                    </div>

                </div>

            </div>



            <!-- =================================================
             STATISTIC CARDS
        ================================================== -->

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">


                <!-- Total Hafalan -->
                <div class="bg-white p-5 rounded-2xl
                        border border-slate-200/80 shadow-sm
                        flex items-center justify-between">

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


                    <div class="w-12 h-12 rounded-2xl bg-emerald-50
                            text-emerald-600 flex items-center
                            justify-center text-xl">

                        <i class="fa-solid fa-book-quran"></i>

                    </div>

                </div>



                <!-- Halaman -->
                <div class="bg-white p-5 rounded-2xl
                        border border-slate-200/80 shadow-sm
                        flex items-center justify-between">

                    <div>

                        <p class="text-xs text-slate-500 font-medium">
                            Total Halaman
                        </p>

                        <h3 class="text-2xl font-bold text-slate-800 mt-1">

                            <?php echo $total_halaman; ?>

                        </h3>

                    </div>


                    <div class="w-12 h-12 rounded-2xl bg-teal-50
                            text-teal-600 flex items-center
                            justify-center text-xl">

                        <i class="fa-solid fa-file-lines"></i>

                    </div>

                </div>



                <!-- Setoran -->
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


                    <div class="w-12 h-12 rounded-2xl bg-amber-50
                            text-amber-600 flex items-center
                            justify-center text-xl">

                        <i class="fa-solid fa-book-open"></i>

                    </div>

                </div>



                <!-- Nilai -->
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


                    <div class="w-12 h-12 rounded-2xl bg-indigo-50
                            text-indigo-600 flex items-center
                            justify-center text-xl">

                        <i class="fa-solid fa-star"></i>

                    </div>

                </div>

            </div>



            <!-- =================================================
             TARGET + PROFIL
        ================================================== -->

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">


                <!-- TARGET -->
                <div class="lg:col-span-2 bg-white rounded-2xl
                        border border-slate-200/80 shadow-sm overflow-hidden">


                    <div class="p-5 border-b border-slate-100
                            flex items-center justify-between">

                        <div>

                            <h3 class="font-bold text-slate-800">
                                Target Hafalan
                            </h3>

                            <p class="text-xs text-slate-400 mt-1">
                                Progress hafalan Anda
                            </p>

                        </div>


                        <a href="target.php"
                            class="text-xs font-semibold text-emerald-600
                              hover:underline">

                            Detail

                        </a>

                    </div>


                    <div class="p-5">

                        <?php if ($target): ?>

                            <div class="flex items-center justify-between mb-3">

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


                            <!-- Progress -->
                            <div class="w-full bg-slate-100 rounded-full h-3">

                                <div
                                    class="bg-emerald-500 h-3 rounded-full transition-all"
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

                                <div class="w-12 h-12 mx-auto
                                        rounded-full bg-slate-100
                                        flex items-center justify-center
                                        text-slate-400">

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


                    <div class="p-5 border-b border-slate-100">

                        <h3 class="font-bold text-slate-800">
                            Profil Singkat
                        </h3>

                    </div>


                    <div class="p-5 space-y-4">


                        <!-- NIS -->
                        <div class="flex items-center space-x-3">

                            <div class="w-9 h-9 rounded-xl bg-emerald-50
                                    text-emerald-600 flex items-center
                                    justify-center">

                                <i class="fa-solid fa-id-card"></i>

                            </div>

                            <div>

                                <p class="text-[11px] text-slate-400">
                                    NIS
                                </p>

                                <p class="text-sm font-semibold text-slate-700">

                                    <?php
                                    echo htmlspecialchars(
                                        $data_santri['nis']
                                    );
                                    ?>

                                </p>

                            </div>

                        </div>


                        <!-- Halaqah -->
                        <div class="flex items-center space-x-3">

                            <div class="w-9 h-9 rounded-xl bg-teal-50
                                    text-teal-600 flex items-center
                                    justify-center">

                                <i class="fa-solid fa-users"></i>

                            </div>

                            <div>

                                <p class="text-[11px] text-slate-400">
                                    Halaqah
                                </p>

                                <p class="text-sm font-semibold text-slate-700">

                                    <?php
                                    echo htmlspecialchars(
                                        $data_santri['nama_halaqah']
                                            ?? 'Belum ditentukan'
                                    );
                                    ?>

                                </p>

                            </div>

                        </div>


                        <!-- Ustadz -->
                        <div class="flex items-center space-x-3">

                            <div class="w-9 h-9 rounded-xl bg-indigo-50
                                    text-indigo-600 flex items-center
                                    justify-center">

                                <i class="fa-solid fa-user-tie"></i>

                            </div>

                            <div>

                                <p class="text-[11px] text-slate-400">
                                    Ustadz Pembimbing
                                </p>

                                <p class="text-sm font-semibold text-slate-700">

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



            <!-- =================================================
             SETORAN TERBARU
        ================================================== -->

            <div class="bg-white rounded-2xl
                    border border-slate-200/80 shadow-sm overflow-hidden">


                <div class="p-5 border-b border-slate-100
                        flex items-center justify-between">

                    <div>

                        <h3 class="font-bold text-slate-800">
                            Setoran Hafalan Terbaru
                        </h3>

                        <p class="text-xs text-slate-400 mt-1">
                            Riwayat setoran hafalan Anda
                        </p>

                    </div>


                    <a href="hafalan.php"
                        class="text-xs font-semibold text-emerald-600
                          hover:underline">

                        Lihat Semua

                    </a>

                </div>



                <div class="overflow-x-auto">

                    <table class="w-full text-left border-collapse text-sm">

                        <thead>

                            <tr class="bg-slate-50 text-slate-500
                                   text-xs uppercase tracking-wider
                                   border-b border-slate-100">

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
                                    Nilai
                                </th>

                                <th class="py-3 px-5 font-semibold">
                                    Tanggal
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-slate-100
                                  text-slate-700">


                            <?php if (mysqli_num_rows($result_setoran) > 0): ?>

                                <?php while ($row = mysqli_fetch_assoc($result_setoran)): ?>

                                    <tr class="hover:bg-slate-50/80 transition-colors">


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


                                        <!-- Nilai -->
                                        <td class="py-3 px-5">

                                            <span class="font-semibold
                                                 text-emerald-600">

                                                <?php
                                                echo $row['nilai_angka'];
                                                ?>

                                            </span>

                                        </td>


                                        <!-- Tanggal -->
                                        <td class="py-3 px-5 text-xs text-slate-500">

                                            <?php
                                            echo date(
                                                'd M Y H:i',
                                                strtotime(
                                                    $row['tanggal_setor']
                                                )
                                            );
                                            ?>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>


                            <?php else: ?>

                                <tr>

                                    <td colspan="6"
                                        class="py-8 text-center
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
             NILAI & PENGUMUMAN
        ================================================== -->

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">


                <!-- NILAI -->
                <div class="bg-white rounded-2xl
                        border border-slate-200/80 shadow-sm overflow-hidden">


                    <div class="p-5 border-b border-slate-100
                            flex items-center justify-between">

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


                    <div class="p-5">


                        <?php if (mysqli_num_rows($result_nilai) > 0): ?>

                            <div class="space-y-4">

                                <?php while ($nilai = mysqli_fetch_assoc($result_nilai)): ?>

                                    <div class="flex items-center justify-between
                                            border-b border-slate-100 pb-3">


                                        <div>

                                            <p class="text-sm font-semibold
                                                  text-slate-700">

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
                                                    strtotime(
                                                        $nilai['tanggal']
                                                    )
                                                );
                                                ?>

                                            </p>

                                        </div>


                                        <div class="text-right">

                                            <p class="text-lg font-bold
                                                  text-emerald-600">

                                                <?php
                                                echo $nilai['nilai'];
                                                ?>

                                            </p>

                                            <?php if ($nilai['nilai'] >= 80): ?>

                                                <span class="text-[10px]
                                                         text-emerald-600">

                                                    Sangat Baik

                                                </span>

                                            <?php elseif ($nilai['nilai'] >= 70): ?>

                                                <span class="text-[10px]
                                                         text-teal-600">

                                                    Baik

                                                </span>

                                            <?php else: ?>

                                                <span class="text-[10px]
                                                         text-amber-600">

                                                    Perlu Ditingkatkan

                                                </span>

                                            <?php endif; ?>

                                        </div>

                                    </div>

                                <?php endwhile; ?>

                            </div>

                        <?php else: ?>

                            <div class="text-center py-6
                                    text-slate-400 text-xs">

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


                    <div class="p-5 border-b border-slate-100">

                        <h3 class="font-bold text-slate-800">
                            Pengumuman
                        </h3>

                    </div>


                    <div class="p-5">


                        <?php if (
                            $result_pengumuman &&
                            mysqli_num_rows($result_pengumuman) > 0
                        ): ?>

                            <div class="space-y-4">

                                <?php while (
                                    $pengumuman =
                                    mysqli_fetch_assoc($result_pengumuman)
                                ): ?>

                                    <div class="flex items-start space-x-3">

                                        <div class="w-9 h-9 rounded-xl
                                                bg-amber-50 text-amber-600
                                                flex items-center justify-center
                                                flex-shrink-0">

                                            <i class="fa-solid fa-bullhorn"></i>

                                        </div>


                                        <div>

                                            <h4 class="text-sm font-semibold
                                                   text-slate-700">

                                                <?php
                                                echo htmlspecialchars(
                                                    $pengumuman['judul']
                                                );
                                                ?>

                                            </h4>


                                            <p class="text-xs text-slate-500
                                                  mt-1 line-clamp-2">

                                                <?php
                                                echo htmlspecialchars(
                                                    $pengumuman['isi']
                                                );
                                                ?>

                                            </p>


                                            <p class="text-[10px]
                                                  text-slate-400 mt-1">

                                                <?php
                                                echo date(
                                                    'd M Y H:i',
                                                    strtotime(
                                                        $pengumuman['tanggal']
                                                    )
                                                );
                                                ?>

                                            </p>

                                        </div>

                                    </div>

                                <?php endwhile; ?>

                            </div>

                        <?php else: ?>

                            <div class="text-center py-6
                                    text-slate-400 text-xs">

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



    <!-- =========================================================
     JAVASCRIPT
========================================================= -->

    <script>
        /*
    |--------------------------------------------------------------------------
    | AUTO HIDE ALERT / ANIMASI
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
    </script>


</body>

</html>