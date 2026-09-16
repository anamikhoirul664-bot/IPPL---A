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
    </style>

</head>


<body class="bg-slate-100 min-h-screen text-slate-800 flex">


    <!-- =========================================================
         SIDEBAR
    ========================================================== -->

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

            <div>

                <h2 class="text-xl font-bold text-slate-800">
                    Hafalan Saya
                </h2>

                <p class="text-xs text-slate-500">

                    Riwayat hafalan dan setoran Al-Qur'an Anda

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

                            <i class="fa-solid fa-book-quran text-2xl"></i>

                        </div>

                    </div>

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
                <div class="overflow-x-auto">

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
    </script>


</body>

</html>