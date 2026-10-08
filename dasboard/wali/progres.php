<?php
session_start();
require_once '../../config/koneksi.php';

// Proteksi Halaman: Hanya Role Wali yang bisa akses
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'wali') {
    header("Location: ../../login.php");
    exit();
}

$wali_id = $_SESSION['user_id'];
$nama_wali = $_SESSION['nama'] ?? $_SESSION['nama_user'] ?? 'Wali Santri';

// Ambil semua santri yang terhubung dengan wali
$query_santri = mysqli_query($koneksi, "
    SELECT 
        s.*,
        u.nama AS nama_santri
    FROM santri s
    JOIN wali_santri w ON s.wali_id = w.id
    JOIN users u ON s.user_id = u.id
    WHERE w.user_id = '$wali_id'
    ORDER BY s.id ASC
");

$daftar_santri = [];

while ($row = mysqli_fetch_assoc($query_santri)) {
    $daftar_santri[] = $row;
}

// Tentukan santri yang dipilih
$santri_id = 0;

if (isset($_GET['santri_id'])) {
    $id_pilihan = (int) $_GET['santri_id'];

    foreach ($daftar_santri as $item) {
        if ((int) $item['id'] === $id_pilihan) {
            $santri_id = $id_pilihan;
            break;
        }
    }
}

// Jika belum memilih, gunakan santri pertama
if ($santri_id === 0 && !empty($daftar_santri)) {
    $santri_id = (int) $daftar_santri[0]['id'];
}

// Ambil data santri yang sedang dipilih
$santri = [];

foreach ($daftar_santri as $item) {
    if ((int) $item['id'] === $santri_id) {
        $santri = $item;
        break;
    }
}

$nama_santri = $santri['nama_santri'] ?? 'Santri';

// ==========================================
// STATISTIK PROGRES SANTRI
// ==========================================

$total_setoran = 0;
$total_juz = 0;
$total_juz_tuntas = 0;
$nilai_rata_rata = 0;

if ($santri_id > 0) {

    // Total seluruh setoran
    $q_total = mysqli_query($koneksi, "
        SELECT COUNT(*) AS total
        FROM setoran
        WHERE santri_id = '$santri_id'
    ");

    $r_total = mysqli_fetch_assoc($q_total);
    $total_setoran = (int) ($r_total['total'] ?? 0);

    // Total Juz yang sudah dipelajari
    $q_juz = mysqli_query($koneksi, "
        SELECT COUNT(DISTINCT juz) AS total
        FROM setoran
        WHERE santri_id = '$santri_id'
        AND juz IS NOT NULL
        AND juz > 0
    ");

    $r_juz = mysqli_fetch_assoc($q_juz);
    $total_juz = (int) ($r_juz['total'] ?? 0);

    // Total juz yang sudah tuntas
    $q_juz_tuntas = mysqli_query($koneksi, "
        SELECT juz
        FROM setoran
        WHERE santri_id = '$santri_id'
        AND juz IS NOT NULL
        AND juz > 0
        AND nilai_angka IS NOT NULL
        GROUP BY juz
        HAVING AVG(nilai_angka) >= 75
    ");

    $total_juz_tuntas = mysqli_num_rows($q_juz_tuntas);

    // Nilai rata-rata
    $q_nilai = mysqli_query($koneksi, "
        SELECT AVG(nilai_angka) AS rata_nilai
        FROM setoran
        WHERE santri_id = '$santri_id'
        AND nilai_angka IS NOT NULL
    ");

    $r_nilai = mysqli_fetch_assoc($q_nilai);
    $nilai_rata_rata = round($r_nilai['rata_nilai'] ?? 0);
}

// Target hafalan adalah 30 Juz
$target_juz = 30;

// Persentase berdasarkan Juz yang sudah tuntas
$persentase = $target_juz > 0
    ? round(($total_juz_tuntas / $target_juz) * 100)
    : 0;

if ($persentase > 100) {
    $persentase = 100;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progres Hafalan - E-Hafalan</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        app: {
                            sidebar: '#062319',
                            active: '#059669',
                            activeHover: '#047857',
                            bg: '#F8FAFC',
                            card: '#FFFFFF',
                            textNav: '#A7F3D0',
                        }
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': {
                                opacity: '0',
                                transform: 'translateY(16px)'
                            },
                            '100%': {
                                opacity: '1',
                                transform: 'translateY(0)'
                            },
                        }
                    },
                    animation: {
                        'fade-in': 'fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F8FAFC;
        }

        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: #062319;
        }

        ::-webkit-scrollbar-thumb {
            background: #047857;
            border-radius: 10px;
        }
    </style>
</head>

<body
    class="bg-app-bg text-slate-800 min-h-screen flex flex-col md:flex-row antialiased overflow-x-hidden"
    x-data="{ sidebarOpen: false, logoutModalOpen: false }"
>

    <!-- OVERLAY MOBILE SIDEBAR -->
    <div
        x-show="sidebarOpen"
        x-cloak
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-40 md:hidden"
    ></div>

    <!-- SIDEBAR NAVIGATION -->
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed md:static inset-y-0 left-0 z-50 w-64 bg-app-sidebar text-slate-300 min-h-screen p-4 flex flex-col justify-between transition-transform duration-300 ease-in-out md:translate-x-0 border-r border-emerald-900/40 shadow-2xl md:shadow-none"
    >

        <div class="overflow-y-auto max-h-[calc(100vh-90px)] pr-1">

            <!-- Header Brand -->
            <div class="flex items-center justify-between mb-8 px-2 pt-2">
                <div class="flex items-center space-x-3">

                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white flex items-center justify-center font-bold text-lg shadow-lg shadow-emerald-950/50">
                        <i class="fa-solid fa-quran"></i>
                    </div>

                    <div>
                        <span class="text-base font-extrabold text-white tracking-wide block leading-tight">
                            E-Hafalan
                        </span>

                        <span class="text-[10px] font-bold text-emerald-400 tracking-wider">
                            PANEL WALI SANTRI
                        </span>
                    </div>

                </div>

                <button
                    @click="sidebarOpen = false"
                    class="md:hidden text-slate-400 hover:text-white p-1"
                >
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Navigation -->
            <div class="text-[10px] font-bold text-emerald-500/80 uppercase tracking-wider mb-2 px-3">
                MENU UTAMA
            </div>

            <nav class="space-y-1.5">

                <a
                    href="dasboard.php"
                    class="flex items-center space-x-3 text-emerald-100/70 hover:text-white hover:bg-emerald-900/50 px-4 py-2.5 rounded-xl font-medium text-xs transition-all duration-200 group"
                >
                    <i class="fa-solid fa-chart-pie w-5 text-center group-hover:text-emerald-400 transition-colors"></i>
                    <span>Dashboard</span>
                </a>

                <!-- ACTIVE MENU -->
                <a
                    href="progres.php"
                    class="flex items-center space-x-3 bg-app-active text-white px-4 py-3 rounded-xl font-semibold text-xs shadow-md shadow-emerald-900/30 transition-all duration-200"
                >
                    <i class="fa-solid fa-bars-progress w-5 text-center text-sm"></i>
                    <span>Progres Hafalan</span>
                </a>

                <a
                    href="nilai.php"
                    class="flex items-center space-x-3 text-emerald-100/70 hover:text-white hover:bg-emerald-900/50 px-4 py-2.5 rounded-xl font-medium text-xs transition-all duration-200 group"
                >
                    <i class="fa-solid fa-star w-5 text-center group-hover:text-emerald-400 transition-colors"></i>
                    <span>Nilai & Penilaian</span>
                </a>

                <a
                    href="riwayat.php"
                    class="flex items-center space-x-3 text-emerald-100/70 hover:text-white hover:bg-emerald-900/50 px-4 py-2.5 rounded-xl font-medium text-xs transition-all duration-200 group"
                >
                    <i class="fa-solid fa-clock-rotate-left w-5 text-center group-hover:text-emerald-400 transition-colors"></i>
                    <span>Riwayat Setoran</span>
                </a>

            </nav>
        </div>

        <!-- User Profile -->
        <div class="pt-3 border-t border-emerald-900/60 flex items-center justify-between px-2">

            <div class="flex items-center space-x-3 overflow-hidden">

                <div class="w-9 h-9 rounded-xl bg-emerald-700/40 border border-emerald-500/30 text-emerald-300 font-bold flex items-center justify-center text-xs shadow-inner">
                    <?php echo strtoupper(substr($nama_wali, 0, 1)); ?>
                </div>

                <div class="overflow-hidden">

                    <p class="text-xs font-semibold text-white truncate max-w-[110px]">
                        <?php echo htmlspecialchars($nama_wali); ?>
                    </p>

                    <p class="text-[9px] text-emerald-400 font-bold uppercase tracking-wider">
                        Wali Santri
                    </p>

                </div>

            </div>

            <button
                @click="logoutModalOpen = true"
                title="Keluar Sistem"
                class="w-8 h-8 rounded-lg bg-emerald-950/80 hover:bg-rose-600 text-emerald-300 hover:text-white flex items-center justify-center transition-all duration-200 text-xs shadow-sm cursor-pointer"
            >
                <i class="fa-solid fa-right-from-bracket"></i>
            </button>

        </div>

    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 min-w-0 flex flex-col min-h-screen">

        <!-- TOPBAR MOBILE -->
        <div class="md:hidden bg-app-sidebar text-white p-4 flex justify-between items-center border-b border-emerald-900 shadow-md">

            <div class="flex items-center space-x-3">

                <div class="w-8 h-8 rounded-lg bg-app-active flex items-center justify-center text-white font-bold">
                    <i class="fa-solid fa-quran text-sm"></i>
                </div>

                <span class="font-bold text-sm tracking-wide">
                    E-Hafalan
                </span>

            </div>

            <button
                @click="sidebarOpen = true"
                class="p-2 bg-emerald-900/80 text-emerald-200 rounded-lg hover:bg-emerald-800 transition-colors"
            >
                <i class="fa-solid fa-bars text-lg"></i>
            </button>

        </div>

        <div class="p-4 sm:p-8 lg:p-8 flex-1 max-w-7xl w-full mx-auto animate-fade-in space-y-6">

            <!-- HEADER -->
            <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">

                <div>

                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                        Progres Hafalan Santri
                    </h1>

                    <p class="text-xs text-slate-500 mt-1">
                        Perkembangan capaian target hafalan untuk santri
                        <span class="font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200/60">
                            <?php echo htmlspecialchars($nama_santri); ?>
                        </span>
                    </p>

                    <?php if (!empty($daftar_santri)): ?>

                        <div class="mt-4 max-w-md">

                            <label class="block text-xs font-bold text-slate-600 mb-2">
                                Pilih Santri
                            </label>

                            <select
                                onchange="window.location.href='progres.php?santri_id=' + this.value"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >

                                <?php foreach ($daftar_santri as $item): ?>

                                    <option
                                        value="<?php echo $item['id']; ?>"
                                        <?php echo ((int)$item['id'] === $santri_id) ? 'selected' : ''; ?>
                                    >
                                        <?php echo htmlspecialchars($item['nama_santri']); ?>
                                        - <?php echo htmlspecialchars($item['nis']); ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    <?php endif; ?>

                </div>

                <div class="flex items-center space-x-3">

                    <a
                        href="riwayat.php"
                        class="inline-flex items-center space-x-2 bg-app-active hover:bg-app-activeHover text-white px-4 py-2.5 rounded-xl text-xs font-semibold shadow-md shadow-emerald-600/20 transition-all duration-150 transform hover:-translate-y-0.5"
                    >
                        <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                        <span>Lihat Riwayat Setoran</span>
                    </a>

                </div>

            </header>

            <!-- PROGRESS SUMMARY CARD -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200/80 relative overflow-hidden">

                <div class="absolute -right-10 -top-10 w-32 h-32 bg-emerald-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

                <div class="relative z-10">

                    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-6 gap-3">

                        <div>

                            <span class="inline-block px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-bold uppercase tracking-wider mb-2">
                                Statistik Utama
                            </span>

                            <h3 class="font-extrabold text-slate-900 text-xl sm:text-2xl">
                                Target Capaian <span class="text-emerald-600">30 Juz</span>
                            </h3>

                            <p class="text-xs text-slate-500 mt-1 flex items-center gap-2">

                                <i class="fa-solid fa-bullseye text-amber-500"></i>

                                Telah menuntaskan

                                <strong class="text-slate-800">
                                    <?php echo $total_juz_tuntas; ?>
                                </strong>

                                dari

                                <strong class="text-slate-800">
                                    <?php echo $target_juz; ?>
                                </strong>

                                Juz

                            </p>

                        </div>

                        <div class="flex items-baseline gap-1 bg-emerald-50 px-4 py-2 rounded-xl border border-emerald-100 self-start md:self-auto">

                            <span class="text-3xl sm:text-4xl font-extrabold text-emerald-600">
                                <?php echo $persentase; ?>
                            </span>

                            <span class="text-emerald-700 font-bold text-lg">
                                %
                            </span>

                        </div>

                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-slate-100 rounded-full h-4 sm:h-5 overflow-hidden mb-6 border border-slate-200/60 shadow-inner">

                        <div
                            class="bg-gradient-to-r from-emerald-500 to-teal-400 h-full rounded-full transition-all duration-1000 shadow-[0_0_10px_rgba(16,185,129,0.4)]"
                            style="width: <?php echo $persentase; ?>%;"
                        ></div>

                    </div>

                    <!-- Grid Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-6 border-t border-slate-100">

                        <!-- Total Setoran -->
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100/50 hover:bg-slate-100 transition-colors">

                            <div class="flex items-center gap-2 mb-1">

                                <i class="fa-solid fa-list-check text-slate-400"></i>

                                <span class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">
                                    Total Setoran
                                </span>

                            </div>

                            <span class="text-xl font-bold text-slate-800">
                                <?php echo $total_setoran; ?>
                                <span class="text-sm font-medium text-slate-500">Kali</span>
                            </span>

                        </div>

                        <!-- Juz Dipelajari -->
                        <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100/50 hover:bg-emerald-100/80 transition-colors">

                            <div class="flex items-center gap-2 mb-1">

                                <i class="fa-solid fa-book-open text-emerald-500"></i>

                                <span class="text-[11px] text-emerald-700 font-bold uppercase tracking-wider">
                                    Juz Dipelajari
                                </span>

                            </div>

                            <span class="text-xl font-bold text-emerald-800">
                                <?php echo $total_juz; ?>
                                <span class="text-sm font-medium text-emerald-600/70">Juz</span>
                            </span>

                        </div>

                        <!-- Juz Tuntas -->
                        <div class="p-4 bg-amber-50 rounded-xl border border-amber-100/50 hover:bg-amber-100/80 transition-colors">

                            <div class="flex items-center gap-2 mb-1">

                                <i class="fa-solid fa-circle-check text-amber-500"></i>

                                <span class="text-[11px] text-amber-700 font-bold uppercase tracking-wider">
                                    Juz Tuntas
                                </span>

                            </div>

                            <span class="text-xl font-bold text-amber-800">
                                <?php echo $total_juz_tuntas; ?>
                                <span class="text-sm font-medium text-amber-600/70">Juz</span>
                            </span>

                        </div>

                        <!-- Rata-rata Nilai -->
                        <div class="p-4 bg-blue-50 rounded-xl border border-blue-100/50 hover:bg-blue-100/80 transition-colors">

                            <div class="flex items-center gap-2 mb-1">

                                <i class="fa-solid fa-star text-blue-500"></i>

                                <span class="text-[11px] text-blue-700 font-bold uppercase tracking-wider">
                                    Rata-rata Nilai
                                </span>

                            </div>

                            <span class="text-xl font-bold text-blue-800">
                                <?php echo $nilai_rata_rata; ?>
                            </span>

                        </div>

                    </div>

                </div>

            </div>

            <!-- DETAIL RINCIAN SURAH TABLE -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden flex flex-col">

                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50/50">

                    <div class="flex items-center gap-3">

                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base shadow-inner">
                            <i class="fa-solid fa-list-ol"></i>
                        </div>

                        <div>

                            <h3 class="text-sm font-bold text-slate-800">
                                Daftar Status Hafalan
                            </h3>

                            <p class="text-[11px] text-slate-400">
                                Rincian status per surah yang disetorkan
                            </p>

                        </div>

                    </div>

                    <span class="text-[11px] bg-white border border-slate-200/80 text-slate-600 font-semibold px-3 py-1.5 rounded-lg flex items-center gap-2 shadow-sm">
                        <i class="fa-solid fa-rotate text-emerald-500"></i>
                        Terintegrasi Realtime
                    </span>

                </div>

                <div class="overflow-x-auto w-full">

                    <table class="w-full text-left text-sm text-slate-600 min-w-[600px]">

                        <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold border-b border-slate-100">

                            <tr>
                                <th class="p-4 pl-6 w-16">No</th>
                                <th class="p-4">Nama Surah</th>
                                <th class="p-4">Terakhir Ayat</th>
                                <th class="p-4">Status Setoran</th>
                                <th class="p-4 pr-6">Tanggal Update</th>
                            </tr>

                        </thead>

                        <tbody class="divide-y divide-slate-100 text-xs">

                            <?php
                            if ($santri_id > 0) {

                                // Query status progres per surah
                                $q_progres = mysqli_query($koneksi, "
                                    SELECT 
                                        s.nama_surah AS surah,
                                        MAX(st.ayat_selesai) AS ayat_terakhir,
                                        MAX(st.tanggal_setor) AS tgl_terakhir,
                                        MAX(st.nilai_angka) AS nilai_angka
                                    FROM setoran st
                                    JOIN surah s ON st.surah_id = s.id
                                    WHERE st.santri_id = '$santri_id'
                                    GROUP BY st.surah_id, s.nama_surah
                                    ORDER BY tgl_terakhir DESC
                                ");

                                if ($q_progres && mysqli_num_rows($q_progres) > 0) {

                                    $no = 1;

                                    while ($row = mysqli_fetch_assoc($q_progres)) {

                                        $is_lulus = floatval($row['nilai_angka'] ?? 0) >= 75;
                            ?>

                                        <tr class="hover:bg-slate-50/80 transition-colors duration-200">

                                            <td class="p-4 pl-6 font-medium text-slate-400">
                                                <?php echo str_pad($no++, 2, '0', STR_PAD_LEFT); ?>
                                            </td>

                                            <td class="p-4 font-bold text-slate-800 text-sm">
                                                <?php echo htmlspecialchars($row['surah']); ?>
                                            </td>

                                            <td class="p-4 font-medium text-slate-600">

                                                <span class="bg-slate-100 px-2.5 py-1 rounded-md border border-slate-200">
                                                    Ayat <?php echo htmlspecialchars($row['ayat_terakhir'] ?? '-'); ?>
                                                </span>

                                            </td>

                                            <td class="p-4">

                                                <?php if ($is_lulus): ?>

                                                    <span class="inline-flex items-center px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg font-bold text-[11px]">
                                                        <i class="fa-solid fa-circle-check mr-1.5 text-emerald-500"></i>
                                                        TUNTAS / LULUS
                                                    </span>

                                                <?php else: ?>

                                                    <span class="inline-flex items-center px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg font-bold text-[11px]">
                                                        <i class="fa-solid fa-rotate-right mr-1.5 text-amber-500"></i>
                                                        MENGULANG
                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                            <td class="p-4 pr-6 text-slate-500 font-medium">

                                                <i class="fa-regular fa-calendar-check mr-1.5 text-slate-400"></i>

                                                <?php echo date('d M Y', strtotime($row['tgl_terakhir'])); ?>

                                            </td>

                                        </tr>

                            <?php
                                    }

                                } else {
                            ?>

                                    <tr>

                                        <td colspan="5" class="p-12 text-center">

                                            <div class="flex flex-col items-center justify-center text-slate-400">

                                                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mb-3 text-slate-400 shadow-inner">
                                                    <i class="fa-solid fa-folder-open text-2xl"></i>
                                                </div>

                                                <p class="text-sm font-bold text-slate-700">
                                                    Belum Ada Progres Hafalan
                                                </p>

                                                <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto">
                                                    Data hafalan akan muncul otomatis setelah Ustadz menginput setoran.
                                                </p>

                                            </div>

                                        </td>

                                    </tr>

                            <?php
                                }
                            }
                            ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </main>

    <!-- MODAL CONFIRMATION LOGOUT -->
    <div
        x-show="logoutModalOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click.self="logoutModalOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/75 backdrop-blur-sm p-4"
    >

        <div
            x-show="logoutModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="bg-slate-900 border border-emerald-900/60 rounded-2xl p-6 w-full max-w-sm shadow-2xl text-center relative overflow-hidden"
        >

            <div class="absolute -top-12 -left-12 w-28 h-28 bg-rose-500/20 rounded-full blur-xl pointer-events-none"></div>

            <div class="w-14 h-14 bg-rose-500/10 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-rose-500/20 shadow-inner">
                <i class="fa-solid fa-right-from-bracket text-2xl"></i>
            </div>

            <h3 class="text-lg font-bold text-white mb-1">
                Konfirmasi Logout
            </h3>

            <p class="text-xs text-slate-400 mb-6 leading-relaxed">
                Apakah Anda yakin ingin keluar dari sistem E-Hafalan Wali Santri?
            </p>

            <div class="flex items-center space-x-3">

                <button
                    type="button"
                    @click="logoutModalOpen = false"
                    class="flex-1 px-4 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:bg-slate-800 font-semibold text-xs transition-all cursor-pointer"
                >
                    Batal
                </button>

                <a
                    href="../../logout.php"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-semibold text-xs transition-all shadow-lg shadow-rose-600/30 text-center"
                >
                    Ya, Keluar
                </a>

            </div>

        </div>

    </div>

</body>
</html>