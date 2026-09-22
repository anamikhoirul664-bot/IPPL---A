<?php

require_once '../../config/koneksi.php';
require_once '../../config/session.php';
require_once '../../config/auth.php';

// Pastikan hanya santri yang bisa mengakses halaman ini
checkRole('santri');

// Ambil ID user yang sedang login
$user_id = getUserId();

// Ambil nama user
$nama_user = getUserNama();

// Ambil data profil santri
$query = "
    SELECT 
        s.id AS santri_id,
        s.nis,
        s.kelas_kelompok,
        s.target_juz,
        s.total_hafalan,
        s.total_hafalan_halaman,

        u.nama,
        u.username,
        u.email,
        u.telepon,
        u.status,

        h.nama_halaqah,
        h.ruangan,

        ust.id AS ustadz_id,
        uu.nama AS nama_ustadz,
        ust.gelar,
        ust.spesialisasi,

        ws.hubungan,
        ws.alamat AS alamat_wali,
        wu.nama AS nama_wali,
        wu.telepon AS telepon_wali

    FROM santri s

    INNER JOIN users u 
        ON s.user_id = u.id

    LEFT JOIN halaqah h 
        ON s.halaqah_id = h.id

    LEFT JOIN ustadz ust 
        ON s.ustadz_id = ust.id

    LEFT JOIN users uu 
        ON ust.user_id = uu.id

    LEFT JOIN wali_santri ws 
        ON s.wali_id = ws.id

    LEFT JOIN users wu 
        ON ws.user_id = wu.id

    WHERE s.user_id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

// Jika data santri tidak ditemukan
if (!$data) {
    die("Data profil santri tidak ditemukan.");
}

// Fungsi sederhana untuk menampilkan tanda jika data kosong
function tampilData($data)
{
    if ($data === null || $data === '') {
        return '-';
    }

    return htmlspecialchars($data);
}

// Nama lengkap ustadz
$nama_ustadz = '-';

if (!empty($data['nama_ustadz'])) {
    $nama_ustadz = $data['nama_ustadz'];

    if (!empty($data['gelar'])) {
        $nama_ustadz .= ', ' . $data['gelar'];
    }
}

// Status akun
$status_akun = $data['status'] ?? 'Aktif';

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Profil Saya - E-Hafalan</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

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

        /* Responsive Sidebar */
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
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen text-slate-800 flex">

    <!-- OVERLAY SIDEBAR MOBILE -->
    <div
        id="sidebarOverlay"
        class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"
        onclick="tutupSidebar()">
    </div>

    <!-- ================= SIDEBAR ================= -->
    <aside
        id="sidebar"
        class="w-64 bg-slate-900 text-slate-300 flex flex-col min-h-screen sticky top-0 z-30">

        <!-- Tombol Tutup Mobile -->
        <div class="flex justify-end p-3 md:hidden">
            <button
                type="button"
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


        <!-- Menu -->
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto text-sm">

            <!-- Dashboard -->
            <a href="dasboard.php"
                class="flex items-center space-x-3 px-4 py-3 rounded-xl
                hover:bg-slate-800 hover:text-white transition-all">

                <i class="fa-solid fa-chart-pie text-slate-400 w-5"></i>

                <span>Dashboard</span>

            </a>


            <!-- Section Hafalan -->
            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase
                tracking-wider text-slate-500">

                Hafalan Saya

            </div>


            <!-- Hafalan -->
            <a href="hafalan.php"
                class="flex items-center space-x-3 px-4 py-2.5 rounded-xl
                hover:bg-slate-800 hover:text-white transition-all">

                <i class="fa-solid fa-book-quran text-slate-400 w-5"></i>

                <span>Hafalan Saya</span>

            </a>


            <!-- Target -->
            <a href="target.php"
                class="flex items-center space-x-3 px-4 py-2.5 rounded-xl
                hover:bg-slate-800 hover:text-white transition-all">

                <i class="fa-solid fa-bullseye text-slate-400 w-5"></i>

                <span>Target Hafalan</span>

            </a>


            <!-- Section Akun -->
            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase
                tracking-wider text-slate-500">

                Akun Saya

            </div>


            <!-- Profil Aktif -->
            <a href="profil.php"
                class="flex items-center space-x-3 px-4 py-2.5 rounded-xl
                bg-emerald-600 text-white font-medium
                shadow-lg shadow-emerald-600/20">

                <i class="fa-solid fa-user text-white w-5"></i>

                <span>Profil Saya</span>

            </a>

        </nav>


        <!-- User bawah -->
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
                            <?php echo htmlspecialchars($nama_user); ?>
                        </p>

                        <p class="text-[10px] text-slate-400 uppercase">
                            Santri
                        </p>

                    </div>

                </div>


                <!-- Logout -->
                <a href="../../logout.php"
                    onclick="return confirm('Apakah Anda yakin ingin logout?')"
                    class="text-slate-400 hover:text-red-400 p-2
                    rounded-lg transition-colors"
                    title="Logout">

                    <i class="fa-solid fa-right-from-bracket text-lg"></i>

                </a>

            </div>

        </div>

    </aside>


    <!-- ================= MAIN ================= -->
    <main class="flex-1 flex flex-col min-w-0 overflow-x-hidden">


        <!-- ================= HEADER ================= -->
        <header class="bg-white border-b border-slate-200 px-4 md:px-6 py-4
            flex items-center justify-between sticky top-0 z-20">

            <div class="flex items-center gap-3">

                <!-- Tombol Menu Mobile -->
                <button
                    type="button"
                    onclick="bukaSidebar()"
                    class="md:hidden text-slate-600 hover:text-emerald-600 text-xl"
                    aria-label="Buka menu">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <div>
                    <h2 class="text-xl font-bold text-slate-800">
                        Profil Saya
                    </h2>

                    <p class="text-xs text-slate-500 mt-1">
                        Informasi akun dan data pribadi santri
                    </p>
                </div>

            </div>


            <!-- Tombol dashboard -->
            <a href="dasboard.php"
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700
                text-white rounded-xl text-xs font-medium
                shadow-md shadow-emerald-600/20 transition-all
                flex items-center space-x-2">

                <i class="fa-solid fa-house"></i>

                <span>Dashboard</span>

            </a>

        </header>


        <!-- ================= CONTENT ================= -->
        <div class="p-4 md:p-6 space-y-6 fade-in">


            <!-- ================= PROFILE HEADER ================= -->
            <div class="bg-gradient-to-r from-emerald-600 to-teal-500
                rounded-2xl p-5 md:p-6 text-white shadow-lg">

                <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-5">


                    <!-- Avatar -->
                    <div class="w-20 h-20 rounded-full bg-white/20
                        border-4 border-white/30
                        flex items-center justify-center
                        text-white text-3xl font-bold shadow-lg">

                        <?php
                        echo strtoupper(
                            substr(
                                htmlspecialchars($data['nama']),
                                0,
                                1
                            )
                        );
                        ?>

                    </div>


                    <!-- Nama -->
                    <div class="flex-1">

                        <p class="text-emerald-100 text-sm mb-1">
                            Profil Santri
                        </p>

                        <h1 class="text-2xl font-bold">
                            <?php echo htmlspecialchars($data['nama']); ?>
                        </h1>

                        <p class="text-emerald-100 text-sm mt-1">
                            NIS: <?php echo htmlspecialchars($data['nis']); ?>
                        </p>

                    </div>


                    <!-- Status -->
                    <div class="sm:ml-auto">

                        <?php if (strtolower($status_akun) === 'aktif') : ?>

                            <span class="inline-flex items-center gap-2
                                px-3 py-1.5 rounded-full
                                bg-white/20 text-white
                                text-xs font-medium">

                                <span class="w-2 h-2 bg-green-300 rounded-full"></span>

                                Akun Aktif

                            </span>

                        <?php else : ?>

                            <span class="inline-flex items-center gap-2
                                px-3 py-1.5 rounded-full
                                bg-red-500/30 text-white
                                text-xs font-medium">

                                <span class="w-2 h-2 bg-red-300 rounded-full"></span>

                                <?php echo htmlspecialchars($status_akun); ?>

                            </span>

                        <?php endif; ?>

                    </div>

                </div>

            </div>


            <!-- ================= DATA AKUN ================= -->
            <div class="bg-white rounded-2xl border border-slate-200/80
                shadow-sm overflow-hidden">

                <!-- Header -->
                <div class="p-5 border-b border-slate-100">

                    <div class="flex items-center space-x-3">

                        <div class="w-10 h-10 rounded-xl
                            bg-indigo-50 text-indigo-600
                            flex items-center justify-center">

                            <i class="fa-solid fa-user-shield"></i>

                        </div>

                        <div>

                            <h3 class="font-semibold text-slate-800">
                                Informasi Akun
                            </h3>

                            <p class="text-xs text-slate-500">
                                Data akun yang digunakan untuk login
                            </p>

                        </div>

                    </div>

                </div>


                <!-- Isi -->
                <div class="p-5">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


                        <!-- Nama -->
                        <div>

                            <label class="text-xs font-medium text-slate-500">
                                Nama Lengkap
                            </label>

                            <div class="mt-2 flex items-center gap-3
                                px-4 py-3 bg-slate-50
                                border border-slate-200 rounded-xl">

                                <i class="fa-solid fa-user text-emerald-600 w-5"></i>

                                <span class="text-sm text-slate-700">
                                    <?php echo tampilData($data['nama']); ?>
                                </span>

                            </div>

                        </div>


                        <!-- Username -->
                        <div>

                            <label class="text-xs font-medium text-slate-500">
                                Username
                            </label>

                            <div class="mt-2 flex items-center gap-3
                                px-4 py-3 bg-slate-50
                                border border-slate-200 rounded-xl">

                                <i class="fa-solid fa-at text-emerald-600 w-5"></i>

                                <span class="text-sm text-slate-700">
                                    <?php echo tampilData($data['username']); ?>
                                </span>

                            </div>

                        </div>


                        <!-- Email -->
                        <div>

                            <label class="text-xs font-medium text-slate-500">
                                Email
                            </label>

                            <div class="mt-2 flex items-center gap-3
                                px-4 py-3 bg-slate-50
                                border border-slate-200 rounded-xl">

                                <i class="fa-solid fa-envelope text-emerald-600 w-5"></i>

                                <span class="text-sm text-slate-700 break-all">
                                    <?php echo tampilData($data['email']); ?>
                                </span>

                            </div>

                        </div>


                        <!-- Telepon -->
                        <div>

                            <label class="text-xs font-medium text-slate-500">
                                Nomor Telepon
                            </label>

                            <div class="mt-2 flex items-center gap-3
                                px-4 py-3 bg-slate-50
                                border border-slate-200 rounded-xl">

                                <i class="fa-solid fa-phone text-emerald-600 w-5"></i>

                                <span class="text-sm text-slate-700">
                                    <?php echo tampilData($data['telepon']); ?>
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ================= DATA SANTRI ================= -->
            <div class="bg-white rounded-2xl border border-slate-200/80
                shadow-sm overflow-hidden">

                <!-- Header -->
                <div class="p-5 border-b border-slate-100">

                    <div class="flex items-center space-x-3">

                        <div class="w-10 h-10 rounded-xl
                            bg-emerald-50 text-emerald-600
                            flex items-center justify-center">

                            <i class="fa-solid fa-graduation-cap"></i>

                        </div>

                        <div>

                            <h3 class="font-semibold text-slate-800">
                                Data Santri
                            </h3>

                            <p class="text-xs text-slate-500">
                                Informasi akademik dan hafalan
                            </p>

                        </div>

                    </div>

                </div>


                <!-- Isi -->
                <div class="p-5">

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">


                        <!-- NIS -->
                        <div>

                            <label class="text-xs font-medium text-slate-500">
                                NIS
                            </label>

                            <div class="mt-2 px-4 py-3 bg-slate-50
                                border border-slate-200 rounded-xl
                                text-sm text-slate-700">

                                <?php echo tampilData($data['nis']); ?>

                            </div>

                        </div>


                        <!-- Kelas -->
                        <div>

                            <label class="text-xs font-medium text-slate-500">
                                Kelas / Kelompok
                            </label>

                            <div class="mt-2 px-4 py-3 bg-slate-50
                                border border-slate-200 rounded-xl
                                text-sm text-slate-700">

                                <?php echo tampilData($data['kelas_kelompok']); ?>

                            </div>

                        </div>


                        <!-- Halaqah -->
                        <div>

                            <label class="text-xs font-medium text-slate-500">
                                Halaqah
                            </label>

                            <div class="mt-2 px-4 py-3 bg-slate-50
                                border border-slate-200 rounded-xl
                                text-sm text-slate-700">

                                <?php echo tampilData($data['nama_halaqah']); ?>

                            </div>

                        </div>


                        <!-- Ruangan -->
                        <div>

                            <label class="text-xs font-medium text-slate-500">
                                Ruangan Halaqah
                            </label>

                            <div class="mt-2 px-4 py-3 bg-slate-50
                                border border-slate-200 rounded-xl
                                text-sm text-slate-700">

                                <?php echo tampilData($data['ruangan']); ?>

                            </div>

                        </div>


                        <!-- Ustadz -->
                        <div>

                            <label class="text-xs font-medium text-slate-500">
                                Ustadz Pembimbing
                            </label>

                            <div class="mt-2 px-4 py-3 bg-slate-50
                                border border-slate-200 rounded-xl
                                text-sm text-slate-700">

                                <?php echo htmlspecialchars($nama_ustadz); ?>

                            </div>

                        </div>


                        <!-- Spesialisasi -->
                        <div>

                            <label class="text-xs font-medium text-slate-500">
                                Spesialisasi Ustadz
                            </label>

                            <div class="mt-2 px-4 py-3 bg-slate-50
                                border border-slate-200 rounded-xl
                                text-sm text-slate-700">

                                <?php echo tampilData($data['spesialisasi']); ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ================= STATISTIK HAFALAN ================= -->
            <div class="bg-white rounded-2xl border border-slate-200/80
                shadow-sm overflow-hidden">

                <!-- Header -->
                <div class="p-5 border-b border-slate-100">

                    <div class="flex items-center space-x-3">

                        <div class="w-10 h-10 rounded-xl
                            bg-amber-50 text-amber-600
                            flex items-center justify-center">

                            <i class="fa-solid fa-book-quran"></i>

                        </div>

                        <div>

                            <h3 class="font-semibold text-slate-800">
                                Ringkasan Hafalan
                            </h3>

                            <p class="text-xs text-slate-500">
                                Perkembangan hafalan yang tercatat
                            </p>

                        </div>

                    </div>

                </div>


                <!-- Statistik -->
                <div class="p-5">

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">


                        <!-- Target -->
                        <div class="bg-slate-50 border border-slate-200
                            rounded-2xl p-5">

                            <div class="flex items-center justify-between">

                                <div>

                                    <p class="text-xs text-slate-500">
                                        Target Hafalan
                                    </p>

                                    <p class="text-2xl font-bold text-slate-800 mt-1">
                                        <?php echo (int)$data['target_juz']; ?>
                                        <span class="text-sm font-medium text-slate-500">
                                            Juz
                                        </span>
                                    </p>

                                </div>

                                <div class="w-11 h-11 rounded-xl
                                    bg-emerald-50 text-emerald-600
                                    flex items-center justify-center">

                                    <i class="fa-solid fa-bullseye"></i>

                                </div>

                            </div>

                        </div>


                        <!-- Total Hafalan -->
                        <div class="bg-slate-50 border border-slate-200
                            rounded-2xl p-5">

                            <div class="flex items-center justify-between">

                                <div>

                                    <p class="text-xs text-slate-500">
                                        Total Hafalan
                                    </p>

                                    <p class="text-2xl font-bold text-slate-800 mt-1">
                                        <?php echo (int)$data['total_hafalan']; ?>
                                        <span class="text-sm font-medium text-slate-500">
                                            Juz
                                        </span>
                                    </p>

                                </div>

                                <div class="w-11 h-11 rounded-xl
                                    bg-teal-50 text-teal-600
                                    flex items-center justify-center">

                                    <i class="fa-solid fa-book-open"></i>

                                </div>

                            </div>

                        </div>


                        <!-- Total Halaman -->
                        <div class="bg-slate-50 border border-slate-200
                            rounded-2xl p-5">

                            <div class="flex items-center justify-between">

                                <div>

                                    <p class="text-xs text-slate-500">
                                        Total Halaman
                                    </p>

                                    <p class="text-2xl font-bold text-slate-800 mt-1">
                                        <?php echo (int)$data['total_hafalan_halaman']; ?>
                                    </p>

                                </div>

                                <div class="w-11 h-11 rounded-xl
                                    bg-indigo-50 text-indigo-600
                                    flex items-center justify-center">

                                    <i class="fa-solid fa-file-lines"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ================= DATA WALI ================= -->
            <div class="bg-white rounded-2xl border border-slate-200/80
                shadow-sm overflow-hidden">

                <!-- Header -->
                <div class="p-5 border-b border-slate-100">

                    <div class="flex items-center space-x-3">

                        <div class="w-10 h-10 rounded-xl
                            bg-purple-50 text-purple-600
                            flex items-center justify-center">

                            <i class="fa-solid fa-users"></i>

                        </div>

                        <div>

                            <h3 class="font-semibold text-slate-800">
                                Data Wali
                            </h3>

                            <p class="text-xs text-slate-500">
                                Informasi wali santri
                            </p>

                        </div>

                    </div>

                </div>


                <!-- Isi -->
                <div class="p-5">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


                        <!-- Nama Wali -->
                        <div>

                            <label class="text-xs font-medium text-slate-500">
                                Nama Wali
                            </label>

                            <div class="mt-2 flex items-center gap-3
                                px-4 py-3 bg-slate-50
                                border border-slate-200 rounded-xl">

                                <i class="fa-solid fa-user text-purple-600 w-5"></i>

                                <span class="text-sm text-slate-700">

                                    <?php
                                    echo tampilData($data['nama_wali']);
                                    ?>

                                </span>

                            </div>

                        </div>


                        <!-- Hubungan -->
                        <div>

                            <label class="text-xs font-medium text-slate-500">
                                Hubungan
                            </label>

                            <div class="mt-2 flex items-center gap-3
                                px-4 py-3 bg-slate-50
                                border border-slate-200 rounded-xl">

                                <i class="fa-solid fa-user-group text-purple-600 w-5"></i>

                                <span class="text-sm text-slate-700">

                                    <?php
                                    echo tampilData($data['hubungan']);
                                    ?>

                                </span>

                            </div>

                        </div>


                        <!-- Telepon Wali -->
                        <div>

                            <label class="text-xs font-medium text-slate-500">
                                Telepon Wali
                            </label>

                            <div class="mt-2 flex items-center gap-3
                                px-4 py-3 bg-slate-50
                                border border-slate-200 rounded-xl">

                                <i class="fa-solid fa-phone text-purple-600 w-5"></i>

                                <span class="text-sm text-slate-700">

                                    <?php
                                    echo tampilData($data['telepon_wali']);
                                    ?>

                                </span>

                            </div>

                        </div>


                        <!-- Alamat Wali -->
                        <div>

                            <label class="text-xs font-medium text-slate-500">
                                Alamat Wali
                            </label>

                            <div class="mt-2 flex items-start gap-3
                                px-4 py-3 bg-slate-50
                                border border-slate-200 rounded-xl
                                min-h-[48px]">

                                <i class="fa-solid fa-location-dot text-purple-600 w-5 mt-0.5"></i>

                                <span class="text-sm text-slate-700">

                                    <?php
                                    echo tampilData($data['alamat_wali']);
                                    ?>

                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ================= CATATAN ================= -->
            <div class="bg-emerald-50 border border-emerald-100
                rounded-2xl p-5">

                <div class="flex items-start gap-4">

                    <div class="w-10 h-10 rounded-xl
                        bg-emerald-100 text-emerald-600
                        flex items-center justify-center flex-shrink-0">

                        <i class="fa-solid fa-circle-info"></i>

                    </div>

                    <div>

                        <h3 class="font-semibold text-emerald-800">
                            Informasi Profil
                        </h3>

                        <p class="text-sm text-emerald-700 mt-1 leading-relaxed">

                            Data yang ditampilkan pada halaman ini berasal dari
                            data akun dan data santri yang tersimpan di dalam sistem.
                            Jika terdapat data yang tidak sesuai, silakan hubungi
                            ustadz atau pengasuh untuk melakukan perubahan data.

                        </p>

                    </div>

                </div>

            </div>


        </div>

    </main>

    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay =
            document.getElementById('sidebarOverlay');

        function bukaSidebar() {
            sidebar.classList.add('active');
            sidebarOverlay.classList.add('active');
        }

        function tutupSidebar() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        }

        document
            .querySelectorAll('#sidebar a')
            .forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 767) {
                        tutupSidebar();
                    }
                });
            });

        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            }
        });
    </script>

</body>

</html>