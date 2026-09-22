<?php
session_start();
require_once '../../config/koneksi.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'pengasuh') {
    header("Location: ../../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Statistik Hafalan - E-Hafalan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-slate-50 min-h-screen flex">

    <aside class="w-64 bg-slate-900 text-white min-h-screen p-5 flex flex-col justify-between hidden md:flex">
        <div>
            <div class="flex items-center space-x-3 mb-8 px-2">
                <i class="fa-solid fa-quran text-2xl text-amber-400"></i>
                <span class="text-xl font-bold">E-Hafalan</span>
            </div>
            <nav class="space-y-2">
                <a href="dasboard.php" class="flex items-center space-x-3 text-slate-300 hover:bg-slate-800 p-3 rounded-xl font-medium transition-colors">
                    <i class="fa-solid fa-chart-pie w-5"></i><span>Dashboard</span>
                </a>
                <a href="monitoring.php" class="flex items-center space-x-3 text-slate-300 hover:bg-slate-800 p-3 rounded-xl font-medium transition-colors">
                    <i class="fa-solid fa-eye w-5"></i><span>Monitoring Setoran</span>
                </a>
                <a href="statistik.php" class="flex items-center space-x-3 bg-amber-600 text-white p-3 rounded-xl font-medium">
                    <i class="fa-solid fa-chart-line w-5"></i><span>Statistik Hafalan</span>
                </a>
                <a href="laporan.php" class="flex items-center space-x-3 text-slate-300 hover:bg-slate-800 p-3 rounded-xl font-medium transition-colors">
                    <i class="fa-solid fa-file-lines w-5"></i><span>Laporan</span>
                </a>
            </nav>
        </div>
        <a href="../../logout.php" class="flex items-center space-x-3 bg-red-600 hover:bg-red-700 text-white p-3 rounded-xl font-medium transition-colors">
            <i class="fa-solid fa-right-from-bracket w-5"></i><span>Keluar</span>
        </a>
    </aside>

    <main class="flex-1 p-6 sm:p-10">
        <header class="mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Statistik Capaian Hafalan</h1>
            <p class="text-sm text-slate-500">Analisis perkembangan hafalan santri secara kolektif</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <h3 class="font-bold text-slate-800 mb-4">Capaian per Kategori Hafalan</h3>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between text-xs font-semibold mb-1">
                            <span>Juz 30 (Juz Amma)</span>
                            <span class="text-emerald-600">80% Santri</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-3">
                            <div class="bg-emerald-500 h-3 rounded-full" style="width: 80%;"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs font-semibold mb-1">
                            <span>Juz 29 (Tabarak)</span>
                            <span class="text-blue-600">45% Santri</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-3">
                            <div class="bg-blue-500 h-3 rounded-full" style="width: 45%;"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs font-semibold mb-1">
                            <span>Juz 1 - 5</span>
                            <span class="text-amber-600">25% Santri</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-3">
                            <div class="bg-amber-500 h-3 rounded-full" style="width: 25%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-center items-center text-center">
                <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center text-2xl font-bold mb-3">
                    <i class="fa-solid fa-trophy"></i>
                </div>
                <h3 class="font-bold text-slate-800">Santri Teraktif Minggu Ini</h3>
                <p class="text-xs text-slate-500 max-w-xs mt-1">Santri dengan kelulusan setoran terbanyak dalam periode 7 hari terakhir.</p>
            </div>
        </div>
    </main>
</body>
</html>