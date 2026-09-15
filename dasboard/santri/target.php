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
| TOTAL HAFALAN
|--------------------------------------------------------------------------
*/

$total_hafalan = $data_santri['total_hafalan'] ?? 0;
$total_halaman = $data_santri['total_hafalan_halaman'] ?? 0;


/*
|--------------------------------------------------------------------------
| TARGET HAFALAN
|--------------------------------------------------------------------------
*/

// Mengambil semua target milik santri
$query_target = "
    SELECT *
    FROM target_hafalan
    WHERE santri_id = ?
    ORDER BY 
        CASE 
            WHEN status = 'Berjalan' THEN 1
            WHEN status = 'Tercapai' THEN 2
            WHEN status = 'Gagal' THEN 3
            ELSE 4
        END,
        tgl_tenggat ASC
";

$stmt_target = mysqli_prepare($koneksi, $query_target);
mysqli_stmt_bind_param($stmt_target, "i", $santri_id);
mysqli_stmt_execute($stmt_target);

$result_target = mysqli_stmt_get_result($stmt_target);


/*
|--------------------------------------------------------------------------
| TARGET YANG SEDANG BERJALAN
|--------------------------------------------------------------------------
*/

$query_target_aktif = "
    SELECT *
    FROM target_hafalan
    WHERE santri_id = ?
    AND status = 'Berjalan'
    ORDER BY tgl_tenggat ASC
    LIMIT 1
";

$stmt_aktif = mysqli_prepare($koneksi, $query_target_aktif);
mysqli_stmt_bind_param($stmt_aktif, "i", $santri_id);
mysqli_stmt_execute($stmt_aktif);

$result_aktif = mysqli_stmt_get_result($stmt_aktif);
$target_aktif = mysqli_fetch_assoc($result_aktif);


/*
|--------------------------------------------------------------------------
| HITUNG PROGRESS TARGET AKTIF
|--------------------------------------------------------------------------
*/

$persentase_target = 0;

if ($target_aktif) {

    $target_juz_aktif = $target_aktif['target_juz'];

    if ($target_juz_aktif > 0) {

        $persentase_target = round(
            ($total_hafalan / $target_juz_aktif) * 100
        );

        if ($persentase_target > 100) {
            $persentase_target = 100;
        }
    }
} else {

    $target_juz_aktif = 0;
}


/*
|--------------------------------------------------------------------------
| JUMLAH STATUS TARGET
|--------------------------------------------------------------------------
*/

// Target berjalan
$query_berjalan = "
    SELECT COUNT(*) AS total
    FROM target_hafalan
    WHERE santri_id = ?
    AND status = 'Berjalan'
";

$stmt = mysqli_prepare($koneksi, $query_berjalan);
mysqli_stmt_bind_param($stmt, "i", $santri_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$total_berjalan = mysqli_fetch_assoc($result)['total'] ?? 0;


// Target tercapai
$query_tercapai = "
    SELECT COUNT(*) AS total
    FROM target_hafalan
    WHERE santri_id = ?
    AND status = 'Tercapai'
";

$stmt = mysqli_prepare($koneksi, $query_tercapai);
mysqli_stmt_bind_param($stmt, "i", $santri_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$total_tercapai = mysqli_fetch_assoc($result)['total'] ?? 0;


// Target gagal
$query_gagal = "
    SELECT COUNT(*) AS total
    FROM target_hafalan
    WHERE santri_id = ?
    AND status = 'Gagal'
";

$stmt = mysqli_prepare($koneksi, $query_gagal);
mysqli_stmt_bind_param($stmt, "i", $santri_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$total_gagal = mysqli_fetch_assoc($result)['total'] ?? 0;

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Target Hafalan - E-Hafalan</title>

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


            <!-- Hafalan Saya -->
            <a href="hafalan.php"
                class="flex items-center space-x-3 px-4 py-2.5 rounded-xl
                hover:bg-slate-800 hover:text-white transition-all">

                <i class="fa-solid fa-book-quran text-slate-400 w-5"></i>

                <span>Hafalan Saya</span>

            </a>


            <!-- Target Hafalan AKTIF -->
            <a href="target.php"
                class="flex items-center space-x-3 px-4 py-3 rounded-xl
                bg-emerald-600 text-white font-medium
                shadow-lg shadow-emerald-600/30">

                <i class="fa-solid fa-bullseye text-lg w-5"></i>

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
                    Target Hafalan
                </h2>

                <p class="text-xs text-slate-500">

                    Pantau target hafalan Al-Qur'an Anda

                </p>

            </div>


            <div class="flex items-center space-x-3">

                <a href="hafalan.php"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700
                    text-white rounded-xl text-xs font-medium
                    shadow-md shadow-emerald-600/20 transition-all
                    flex items-center space-x-2">

                    <i class="fa-solid fa-book-quran"></i>

                    <span>Lihat Hafalan</span>

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
                            Target Hafalan
                        </p>

                        <h1 class="text-2xl font-bold mt-1">

                            <?php echo htmlspecialchars($nama_user); ?>

                        </h1>

                        <p class="text-emerald-100 text-xs mt-2">

                            Tetap semangat dan konsisten dalam mencapai
                            target hafalan Al-Qur'an.

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

                            <i class="fa-solid fa-bullseye text-2xl"></i>

                        </div>

                    </div>

                </div>

            </div>



            <!-- =================================================
                 STATISTIC CARDS
            ================================================== -->

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">


                <!-- Target Berjalan -->
                <div class="bg-white p-5 rounded-2xl
                        border border-slate-200/80 shadow-sm
                        flex items-center justify-between">

                    <div>

                        <p class="text-xs text-slate-500 font-medium">
                            Target Berjalan
                        </p>

                        <h3 class="text-2xl font-bold text-slate-800 mt-1">

                            <?php echo $total_berjalan; ?>

                        </h3>

                    </div>


                    <div class="w-12 h-12 rounded-2xl bg-emerald-50
                            text-emerald-600 flex items-center
                            justify-center text-xl">

                        <i class="fa-solid fa-bullseye"></i>

                    </div>

                </div>



                <!-- Target Tercapai -->
                <div class="bg-white p-5 rounded-2xl
                        border border-slate-200/80 shadow-sm
                        flex items-center justify-between">

                    <div>

                        <p class="text-xs text-slate-500 font-medium">
                            Target Tercapai
                        </p>

                        <h3 class="text-2xl font-bold text-slate-800 mt-1">

                            <?php echo $total_tercapai; ?>

                        </h3>

                    </div>


                    <div class="w-12 h-12 rounded-2xl bg-teal-50
                            text-teal-600 flex items-center
                            justify-center text-xl">

                        <i class="fa-solid fa-circle-check"></i>

                    </div>

                </div>



                <!-- Target Gagal -->
                <div class="bg-white p-5 rounded-2xl
                        border border-slate-200/80 shadow-sm
                        flex items-center justify-between">

                    <div>

                        <p class="text-xs text-slate-500 font-medium">
                            Target Gagal
                        </p>

                        <h3 class="text-2xl font-bold text-slate-800 mt-1">

                            <?php echo $total_gagal; ?>

                        </h3>

                    </div>


                    <div class="w-12 h-12 rounded-2xl bg-amber-50
                            text-amber-600 flex items-center
                            justify-center text-xl">

                        <i class="fa-solid fa-circle-xmark"></i>

                    </div>

                </div>

            </div>



            <!-- =================================================
                 TARGET AKTIF
            ================================================== -->

            <div class="bg-white rounded-2xl
                    border border-slate-200/80 shadow-sm overflow-hidden">


                <div class="p-5 border-b border-slate-100
                        flex items-center justify-between">

                    <div>

                        <h3 class="font-bold text-slate-800">
                            Target Yang Sedang Berjalan
                        </h3>

                        <p class="text-xs text-slate-400 mt-1">
                            Progress target hafalan Anda saat ini
                        </p>

                    </div>


                    <?php if ($target_aktif): ?>

                        <span class="px-3 py-1 rounded-full
                                bg-emerald-100 text-emerald-700
                                text-xs font-semibold">

                            Berjalan

                        </span>

                    <?php endif; ?>

                </div>


                <div class="p-5">

                    <?php if ($target_aktif): ?>


                        <!-- Target -->
                        <div class="flex items-center justify-between mb-3">

                            <div>

                                <p class="text-sm font-semibold text-slate-700">

                                    Target
                                    <?php echo $target_juz_aktif; ?>
                                    Juz

                                </p>

                                <p class="text-xs text-slate-400 mt-1">

                                    Mulai:

                                    <?php
                                    echo date(
                                        'd M Y',
                                        strtotime(
                                            $target_aktif['tgl_mulai']
                                        )
                                    );
                                    ?>

                                    &nbsp;•&nbsp;

                                    Tenggat:

                                    <?php
                                    echo date(
                                        'd M Y',
                                        strtotime(
                                            $target_aktif['tgl_tenggat']
                                        )
                                    );
                                    ?>

                                </p>

                            </div>


                            <div class="text-right">

                                <p class="text-2xl font-bold text-emerald-600">

                                    <?php echo $persentase_target; ?>%

                                </p>

                                <p class="text-[10px] text-slate-400">
                                    Progress
                                </p>

                            </div>

                        </div>


                        <!-- Progress Bar -->
                        <div class="w-full bg-slate-100 rounded-full h-3">

                            <div
                                class="bg-emerald-500 h-3 rounded-full transition-all"
                                style="width: <?php echo $persentase_target; ?>%;">

                            </div>

                        </div>


                        <!-- Progress Text -->
                        <div class="flex justify-between mt-2">

                            <span class="text-xs text-slate-400">

                                <?php echo $total_hafalan; ?> Juz

                            </span>

                            <span class="text-xs text-slate-400">

                                <?php echo $target_juz_aktif; ?> Juz

                            </span>

                        </div>


                    <?php else: ?>


                        <!-- Tidak Ada Target -->
                        <div class="text-center py-8">

                            <div class="w-12 h-12 mx-auto
                                    rounded-full bg-slate-100
                                    flex items-center justify-center
                                    text-slate-400">

                                <i class="fa-solid fa-bullseye"></i>

                            </div>

                            <p class="text-sm text-slate-500 mt-3">

                                Belum ada target hafalan yang sedang berjalan.

                            </p>

                            <p class="text-xs text-slate-400 mt-1">

                                Silakan hubungi ustadz pembimbing untuk
                                mendapatkan target hafalan.

                            </p>

                        </div>


                    <?php endif; ?>

                </div>

            </div>



            <!-- =================================================
                 SEMUA TARGET
            ================================================== -->

            <div class="bg-white rounded-2xl
                    border border-slate-200/80 shadow-sm overflow-hidden">


                <div class="p-5 border-b border-slate-100">

                    <h3 class="font-bold text-slate-800">
                        Riwayat Target Hafalan
                    </h3>

                    <p class="text-xs text-slate-400 mt-1">
                        Daftar target hafalan yang pernah diberikan
                    </p>

                </div>



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
                                    Target
                                </th>

                                <th class="py-3 px-5 font-semibold">
                                    Tanggal Mulai
                                </th>

                                <th class="py-3 px-5 font-semibold">
                                    Tenggat
                                </th>

                                <th class="py-3 px-5 font-semibold">
                                    Progress
                                </th>

                                <th class="py-3 px-5 font-semibold">
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-slate-100
                                text-slate-700">


                            <?php if (mysqli_num_rows($result_target) > 0): ?>

                                <?php
                                $no = 1;

                                while ($row = mysqli_fetch_assoc($result_target)):

                                    $target_juz = $row['target_juz'];

                                    $progress = 0;

                                    if ($target_juz > 0) {

                                        $progress = round(
                                            ($total_hafalan / $target_juz) * 100
                                        );

                                        if ($progress > 100) {
                                            $progress = 100;
                                        }
                                    }
                                ?>

                                    <tr class="hover:bg-slate-50/80 transition-colors">


                                        <!-- No -->
                                        <td class="py-3 px-5 text-xs text-slate-500">

                                            <?php echo $no++; ?>

                                        </td>


                                        <!-- Target -->
                                        <td class="py-3 px-5">

                                            <p class="font-semibold text-slate-700">

                                                <?php
                                                echo $target_juz;
                                                ?>

                                                Juz

                                            </p>

                                        </td>


                                        <!-- Tanggal Mulai -->
                                        <td class="py-3 px-5 text-xs text-slate-500">

                                            <?php
                                            echo date(
                                                'd M Y',
                                                strtotime(
                                                    $row['tgl_mulai']
                                                )
                                            );
                                            ?>

                                        </td>


                                        <!-- Tenggat -->
                                        <td class="py-3 px-5 text-xs text-slate-500">

                                            <?php
                                            echo date(
                                                'd M Y',
                                                strtotime(
                                                    $row['tgl_tenggat']
                                                )
                                            );
                                            ?>

                                        </td>


                                        <!-- Progress -->
                                        <td class="py-3 px-5 min-w-[180px]">

                                            <div class="flex items-center space-x-3">

                                                <div class="w-24 bg-slate-100
                                                        rounded-full h-2">

                                                    <div
                                                        class="bg-emerald-500 h-2 rounded-full"
                                                        style="width: <?php echo $progress; ?>%;">

                                                    </div>

                                                </div>


                                                <span class="text-xs font-semibold
                                                        text-emerald-600">

                                                    <?php echo $progress; ?>%

                                                </span>

                                            </div>

                                        </td>


                                        <!-- Status -->
                                        <td class="py-3 px-5">

                                            <?php if ($row['status'] == 'Berjalan'): ?>

                                                <span class="px-2.5 py-1 rounded-full
                                                        text-xs font-semibold
                                                        bg-emerald-100
                                                        text-emerald-700">

                                                    Berjalan

                                                </span>


                                            <?php elseif ($row['status'] == 'Tercapai'): ?>

                                                <span class="px-2.5 py-1 rounded-full
                                                        text-xs font-semibold
                                                        bg-teal-100
                                                        text-teal-700">

                                                    Tercapai

                                                </span>


                                            <?php elseif ($row['status'] == 'Gagal'): ?>

                                                <span class="px-2.5 py-1 rounded-full
                                                        text-xs font-semibold
                                                        bg-amber-100
                                                        text-amber-700">

                                                    Gagal

                                                </span>


                                            <?php else: ?>

                                                <span class="px-2.5 py-1 rounded-full
                                                        text-xs font-semibold
                                                        bg-slate-100
                                                        text-slate-600">

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $row['status']
                                                    );
                                                    ?>

                                                </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>


                            <?php else: ?>

                                <tr>

                                    <td colspan="6"
                                        class="py-10 text-center
                                        text-slate-400 text-xs">

                                        <div class="mb-2">

                                            <i class="fa-solid fa-bullseye
                                                text-2xl"></i>

                                        </div>

                                        Belum ada target hafalan.

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
                                Informasi Target
                            </h3>

                            <p class="text-xs text-slate-500 mt-1">

                                Target hafalan diberikan oleh ustadz
                                pembimbing. Progress dihitung berdasarkan
                                jumlah hafalan yang telah dicapai.

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