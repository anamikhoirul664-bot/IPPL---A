<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Hafalan | Sistem Monitoring Hafalan Al-Qur'an Modern</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome Icons CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Konfigurasi Tailwind Custom Warna & Font -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        poppins: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        },
                        tealBrand: '#0d9488',
                    }
                }
            }
        }
    </script>

    <!-- Custom CSS untuk Glassmorphism & Custom Animations -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        /* Glassmorphism Effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .glass-nav {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .glass-dark {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Floating Animation */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
            100% { transform: translateY(0px); }
        }

        @keyframes float-slow {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-8px) rotate(1deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }

        .animate-float {
            animation: float 4s ease-in-out infinite;
        }

        .animate-float-slow {
            animation: float-slow 6s ease-in-out infinite;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #10b981;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #059669;
        }

        /* Scroll Reveal Initial Styles */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.5, 0, 0, 1);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased overflow-x-hidden">

    <!-- ==========================================
         1. NAVBAR SECTION
    =========================================== -->
    <header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="#beranda" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-emerald-500/30 group-hover:scale-105 transition-transform duration-300">
                        <i class="fa-solid fa-quran"></i>
                    </div>
                    <span class="text-xl font-bold bg-gradient-to-r from-emerald-700 via-emerald-600 to-teal-600 bg-clip-text text-transparent">
                        E-Hafalan
                    </span>
                </a>

                <!-- Desktop Menu -->
                <nav class="hidden md:flex items-center space-x-1 lg:space-x-2">
                    <a href="#beranda" class="px-3 py-2 text-sm font-medium text-slate-700 hover:text-emerald-600 transition-colors">Beranda</a>
                    <a href="#fitur" class="px-3 py-2 text-sm font-medium text-slate-700 hover:text-emerald-600 transition-colors">Fitur</a>
                    <a href="#statistik" class="px-3 py-2 text-sm font-medium text-slate-700 hover:text-emerald-600 transition-colors">Statistik</a>
                    <a href="#alur" class="px-3 py-2 text-sm font-medium text-slate-700 hover:text-emerald-600 transition-colors">Alur Sistem</a>
                    <a href="#dashboard-preview" class="px-3 py-2 text-sm font-medium text-slate-700 hover:text-emerald-600 transition-colors">Dashboard</a>
                    <a href="#testimoni" class="px-3 py-2 text-sm font-medium text-slate-700 hover:text-emerald-600 transition-colors">Testimoni</a>
                    <a href="#tentang" class="px-3 py-2 text-sm font-medium text-slate-700 hover:text-emerald-600 transition-colors">Tentang</a>
                </nav>

                <!-- Auth Buttons Desktop -->
                <div class="hidden md:flex items-center space-x-3">
                    <a href="login.php" class="px-5 py-2.5 text-sm font-semibold text-emerald-700 hover:text-emerald-800 transition-colors">
                        Masuk
                    </a>
                    <a href="register.php" class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl shadow-md shadow-emerald-500/20 hover:shadow-lg hover:shadow-emerald-500/30 hover:-translate-y-0.5 transition-all duration-300">
                        Daftar Akun
                    </a>
                </div>

                <!-- Mobile Hamburger Button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-btn" class="text-slate-700 hover:text-emerald-600 focus:outline-none p-2">
                        <i class="fa-solid fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Dropdown Menu -->
        <div id="mobile-menu" class="hidden md:hidden glass-nav border-b border-slate-200/60 px-4 pt-2 pb-6 space-y-3">
            <a href="#beranda" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-600">Beranda</a>
            <a href="#fitur" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-600">Fitur</a>
            <a href="#statistik" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-600">Statistik</a>
            <a href="#alur" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-600">Alur Sistem</a>
            <a href="#dashboard-preview" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-600">Dashboard</a>
            <a href="#testimoni" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-600">Testimoni</a>
            <a href="#tentang" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-600">Tentang</a>
            <div class="pt-4 border-t border-slate-200 flex flex-col space-y-2">
                <a href="login.php" class="w-full text-center px-4 py-2.5 text-sm font-semibold text-emerald-700 border border-emerald-600/30 rounded-xl hover:bg-emerald-50">Masuk</a>
                <a href="register.php" class="w-full text-center px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 rounded-xl shadow-md">Daftar Akun</a>
            </div>
        </div>
    </header>


    <!-- ==========================================
         2. HERO SECTION
    =========================================== -->
    <section id="beranda" class="relative pt-32 pb-20 lg:pt-40 lg:pb-28 overflow-hidden bg-gradient-to-b from-emerald-50/60 via-slate-50 to-slate-50">
        <!-- Background Decorative Orbs -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-tr from-emerald-300/30 to-teal-200/30 rounded-full blur-3xl -z-10 pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                <!-- Left Text Content -->
                <div class="lg:col-span-7 text-center lg:text-left reveal">
                    <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-emerald-100/80 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-medium mb-6">
                        <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-ping"></span>
                        <span>Platform Edutech Pesantren Masa Kini</span>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 leading-tight sm:leading-tight lg:leading-tight mb-6">
                        Monitoring Hafalan Al-Qur'an Secara <span class="bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-500 bg-clip-text text-transparent">Digital & Modern</span>
                    </h1>

                    <p class="text-slate-600 text-base sm:text-lg mb-8 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                        Hubungkan Santri, Ustadz, Pengasuh, dan Wali Santri dalam satu ekosistem digital yang terintegrasi, akurat, dan dapat diakses secara *real-time* kapan saja.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start space-y-3 sm:space-y-0 sm:space-x-4">
                        <a href="login.php" class="w-full sm:w-auto px-8 py-4 text-base font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-2xl shadow-xl shadow-emerald-600/25 hover:shadow-emerald-600/35 hover:-translate-y-1 transition-all duration-300 text-center">
                            <i class="fa-solid fa-right-to-bracket mr-2"></i> Masuk Ke Dashboard
                        </a>
                        <a href="#fitur" class="w-full sm:w-auto px-8 py-4 text-base font-semibold text-slate-700 hover:text-emerald-700 bg-white hover:bg-slate-100/80 border border-slate-200 rounded-2xl shadow-sm hover:shadow transition-all duration-300 text-center">
                            Lihat Fitur Lengkap
                        </a>
                    </div>

                    <!-- Trust Badge -->
                    <div class="mt-10 pt-8 border-t border-slate-200/80 flex items-center justify-center lg:justify-start space-x-6 text-slate-500 text-xs sm:text-sm">
                        <div class="flex items-center space-x-2">
                            <i class="fa-solid fa-shield-halved text-emerald-600 text-lg"></i>
                            <span>Data Aman & Terorganisir</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i class="fa-solid fa-bolt text-teal-600 text-lg"></i>
                            <span>Laporan Real-Time</span>
                        </div>
                    </div>
                </div>

                <!-- Right Hero Illustration & Floating Cards -->
                <div class="lg:col-span-5 relative flex justify-center reveal">
                    <div class="relative w-full max-w-md lg:max-w-none">
                        
                        <!-- Main Card / Mockup Holder -->
                        <div class="glass-card rounded-3xl p-6 shadow-2xl shadow-emerald-900/10 border border-white/60 relative z-10 animate-float-slow">
                            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold">
                                        AH
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-slate-800">Ahmad Hanif</h4>
                                        <p class="text-xs text-slate-500">Santri Kelas 12 - Takhassus</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 rounded-full border border-emerald-200">
                                    Lulus Ziayadah
                                </span>
                            </div>

                            <!-- Progress Widget -->
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between text-xs font-medium mb-1">
                                        <span class="text-slate-600">Target Hafalan (30 Juz)</span>
                                        <span class="text-emerald-600 font-bold">24 Juz (80%)</span>
                                    </div>
                                    <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden p-0.5 border border-slate-200/50">
                                        <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full" style="width: 80%"></div>
                                    </div>
                                </div>

                                <div class="bg-slate-50/80 rounded-xl p-3 border border-slate-100 space-y-2">
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-slate-500">Setoran Terakhir:</span>
                                        <span class="font-medium text-slate-700">Surah An-Naba' (1-40)</span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-slate-500">Nilai Mumtaz:</span>
                                        <span class="font-bold text-emerald-600">98 / 100</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Floating Card 1 (Top Right) -->
                        <div class="absolute -top-6 -right-4 sm:-right-6 glass-card p-4 rounded-2xl shadow-lg border border-white/80 z-20 animate-float hidden sm:flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-teal-500 text-white flex items-center justify-center text-lg shadow-md">
                                <i class="fa-solid fa-bell"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-800">Notifikasi Wali</p>
                                <p class="text-[11px] text-slate-500">Setoran baru saja dinilai</p>
                            </div>
                        </div>

                        <!-- Floating Card 2 (Bottom Left) -->
                        <div class="absolute -bottom-6 -left-4 sm:-left-6 glass-card p-4 rounded-2xl shadow-lg border border-white/80 z-20 animate-float style-delay-2000 hidden sm:flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-lg shadow-md">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-800">Statistik Bulanan</p>
                                <p class="text-[11px] text-emerald-600 font-semibold">+15% Peningkatan</p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>


    <!-- ==========================================
         3. STATISTIK SECTION
    =========================================== -->
    <section id="statistik" class="py-16 bg-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                
                <!-- Stat Card 1 -->
                <div class="glass-card rounded-2xl p-6 text-center border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 reveal">
                    <div class="w-12 h-12 mx-auto mb-4 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <h3 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-1">
                        <span class="counter" data-target="1250">0</span>+
                    </h3>
                    <p class="text-xs sm:text-sm font-medium text-slate-500">Total Santri</p>
                </div>

                <!-- Stat Card 2 -->
                <div class="glass-card rounded-2xl p-6 text-center border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 reveal">
                    <div class="w-12 h-12 mx-auto mb-4 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <h3 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-1">
                        <span class="counter" data-target="48">0</span>
                    </h3>
                    <p class="text-xs sm:text-sm font-medium text-slate-500">Ustadz & Penguji</p>
                </div>

                <!-- Stat Card 3 -->
                <div class="glass-card rounded-2xl p-6 text-center border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 reveal">
                    <div class="w-12 h-12 mx-auto mb-4 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-book-bookmark"></i>
                    </div>
                    <h3 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-1">
                        <span class="counter" data-target="34200">0</span>+
                    </h3>
                    <p class="text-xs sm:text-sm font-medium text-slate-500">Total Setoran</p>
                </div>

                <!-- Stat Card 4 -->
                <div class="glass-card rounded-2xl p-6 text-center border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 reveal">
                    <div class="w-12 h-12 mx-auto mb-4 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                    <h3 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-1">
                        <span class="counter" data-target="94">0</span>%
                    </h3>
                    <p class="text-xs sm:text-sm font-medium text-slate-500">Target Tercapai</p>
                </div>

            </div>
        </div>
    </section>


    <!-- ==========================================
         4. FITUR UTAMA SECTION (Multi-Role)
    =========================================== -->
    <section id="fitur" class="py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                <h2 class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-3">Fitur Unggulan</h2>
                <p class="text-3xl sm:text-4xl font-bold text-slate-900">Dirancang Khusus untuk 4 Peran Utama</p>
                <p class="text-slate-600 mt-4 text-sm sm:text-base">Setiap pengguna memiliki portal dashboard yang disesuaikan dengan kebutuhan dan wewenang masing-masing.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                
                <!-- Role Card: Santri -->
                <div class="glass-card rounded-3xl p-6 border border-slate-200/80 hover:shadow-2xl hover:border-emerald-300 transition-all duration-300 flex flex-col justify-between group reveal">
                    <div>
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white flex items-center justify-center text-2xl shadow-lg shadow-emerald-500/20 mb-6 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-user-graduate"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-2">Portal Santri</h3>
                        <p class="text-slate-600 text-sm mb-6">Memudahkan santri dalam mengontrol target hafalan pribadi secara terstruktur.</p>
                        
                        <ul class="space-y-3 text-sm text-slate-700">
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Lihat Progress Hafalan</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Target Ziyadah & Muraaja'ah</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Riwayat Setoran Lengkap</span>
                            </li>
                        </ul>
                    </div>
                    <div class="mt-8 pt-4 border-t border-slate-100">
                        <a href="login.php" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800 flex items-center justify-between">
                            <span>Akses Santri</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Role Card: Ustadz -->
                <div class="glass-card rounded-3xl p-6 border border-slate-200/80 hover:shadow-2xl hover:border-emerald-300 transition-all duration-300 flex flex-col justify-between group reveal">
                    <div>
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-teal-500 to-emerald-400 text-white flex items-center justify-center text-2xl shadow-lg shadow-teal-500/20 mb-6 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-2">Portal Ustadz</h3>
                        <p class="text-slate-600 text-sm mb-6">Alat bantu penguji untuk menginput nilai dan catatan kualitas bacaan santri.</p>
                        
                        <ul class="space-y-3 text-sm text-slate-700">
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Input Setoran Cepat</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Penilaian Tajwid & Fashohah</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Laporan Harian Kelompok</span>
                            </li>
                        </ul>
                    </div>
                    <div class="mt-8 pt-4 border-t border-slate-100">
                        <a href="login.php" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800 flex items-center justify-between">
                            <span>Akses Ustadz</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Role Card: Pengasuh -->
                <div class="glass-card rounded-3xl p-6 border border-slate-200/80 hover:shadow-2xl hover:border-emerald-300 transition-all duration-300 flex flex-col justify-between group reveal">
                    <div>
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-600 text-white flex items-center justify-center text-2xl shadow-lg shadow-emerald-600/20 mb-6 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-crown"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-2">Portal Pengasuh</h3>
                        <p class="text-slate-600 text-sm mb-6">Kendali penuh untuk pimpinan lembaga dalam memantau performa keseluruhan.</p>
                        
                        <ul class="space-y-3 text-sm text-slate-700">
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Monitoring Global Pesantren</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Statistik Lanjutan & Grafik</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Ranking Santri Berprestasi</span>
                            </li>
                        </ul>
                    </div>
                    <div class="mt-8 pt-4 border-t border-slate-100">
                        <a href="login.php" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800 flex items-center justify-between">
                            <span>Akses Pengasuh</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Role Card: Wali Santri -->
                <div class="glass-card rounded-3xl p-6 border border-slate-200/80 hover:shadow-2xl hover:border-emerald-300 transition-all duration-300 flex flex-col justify-between group reveal">
                    <div>
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-teal-600 to-emerald-500 text-white flex items-center justify-center text-2xl shadow-lg shadow-teal-600/20 mb-6 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-people-roof"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-2">Portal Wali Santri</h3>
                        <p class="text-slate-600 text-sm mb-6">Memberikan ketenangan bagi orang tua untuk memantau anak dari jarak jauh.</p>
                        
                        <ul class="space-y-3 text-sm text-slate-700">
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Pantau Anak Real-Time</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Rincian Nilai & Catatan Ustadz</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                <span>Notifikasi Perkembangan</span>
                            </li>
                        </ul>
                    </div>
                    <div class="mt-8 pt-4 border-t border-slate-100">
                        <a href="login.php" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800 flex items-center justify-between">
                            <span>Akses Wali</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <!-- ==========================================
         5. ALUR SISTEM SECTION
    =========================================== -->
    <section id="alur" class="py-20 bg-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                <h2 class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-3">Alur Kerja</h2>
                <p class="text-3xl sm:text-4xl font-bold text-slate-900">Bagaimana Alur Sistem Bekerja?</p>
                <p class="text-slate-600 mt-4 text-sm sm:text-base">Proses sederhana dari setoran hafalan offline hingga pelaporan digital secara otomatis.</p>
            </div>

            <!-- Workflow Visual Steps -->
            <div class="relative">
                <!-- Connecting Line (Desktop) -->
                <div class="hidden lg:block absolute top-1/2 left-0 right-0 h-1 bg-gradient-to-r from-emerald-200 via-teal-300 to-emerald-200 -translate-y-1/2 z-0"></div>

                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 relative z-10">
                    
                    <!-- Step 1 -->
                    <div class="glass-card rounded-2xl p-5 text-center border border-slate-100 hover:border-emerald-300 transition-all reveal">
                        <div class="w-12 h-12 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center mx-auto mb-4 text-lg shadow-md shadow-emerald-500/30">
                            1
                        </div>
                        <h4 class="font-bold text-slate-800 text-sm mb-1">Santri</h4>
                        <p class="text-xs text-slate-500">Menyiapkan hafalan ziyadah atau muraaja'ah.</p>
                    </div>

                    <!-- Step 2 -->
                    <div class="glass-card rounded-2xl p-5 text-center border border-slate-100 hover:border-emerald-300 transition-all reveal">
                        <div class="w-12 h-12 rounded-full bg-teal-600 text-white font-bold flex items-center justify-center mx-auto mb-4 text-lg shadow-md shadow-teal-500/30">
                            2
                        </div>
                        <h4 class="font-bold text-slate-800 text-sm mb-1">Setor Offline</h4>
                        <p class="text-xs text-slate-500">Setor hafalan tatap muka secara langsung kepada Ustadz.</p>
                    </div>

                    <!-- Step 3 -->
                    <div class="glass-card rounded-2xl p-5 text-center border border-slate-100 hover:border-emerald-300 transition-all reveal">
                        <div class="w-12 h-12 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center mx-auto mb-4 text-lg shadow-md shadow-emerald-500/30">
                            3
                        </div>
                        <h4 class="font-bold text-slate-800 text-sm mb-1">Ustadz Menilai</h4>
                        <p class="text-xs text-slate-500">Ustadz menguji & menginput nilai ke aplikasi E-Hafalan.</p>
                    </div>

                    <!-- Step 4 -->
                    <div class="glass-card rounded-2xl p-5 text-center border border-slate-100 hover:border-emerald-300 transition-all reveal">
                        <div class="w-12 h-12 rounded-full bg-teal-600 text-white font-bold flex items-center justify-center mx-auto mb-4 text-lg shadow-md shadow-teal-500/30">
                            4
                        </div>
                        <h4 class="font-bold text-slate-800 text-sm mb-1">Pengasuh Monitor</h4>
                        <p class="text-xs text-slate-500">Sistem memproses grafik & rekapitulasi data santri.</p>
                    </div>

                    <!-- Step 5 -->
                    <div class="glass-card rounded-2xl p-5 text-center border border-slate-100 hover:border-emerald-300 transition-all reveal">
                        <div class="w-12 h-12 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center mx-auto mb-4 text-lg shadow-md shadow-emerald-500/30">
                            5
                        </div>
                        <h4 class="font-bold text-slate-800 text-sm mb-1">Wali Pantau</h4>
                        <p class="text-xs text-slate-500">Orang tua menerima laporan perkembangan via portal.</p>
                    </div>

                </div>
            </div>

        </div>
    </section>


    <!-- ==========================================
         6. MOCKUP PREVIEW DASHBOARD SECTION
    =========================================== -->
    <section id="dashboard-preview" class="py-20 bg-slate-900 text-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                <h2 class="text-xs font-bold text-emerald-400 uppercase tracking-widest mb-3">Preview Antarmuka</h2>
                <p class="text-3xl sm:text-4xl font-bold text-white">Tampilan Dashboard SaaS yang Ergonomis</p>
                <p class="text-slate-400 mt-4 text-sm sm:text-base">Didesain dengan antarmuka yang bersih, intuitif, dan responsif di berbagai perangkat.</p>
            </div>

            <!-- Dashboard Mockup Frame -->
            <div class="glass-dark rounded-3xl p-4 sm:p-6 border border-slate-700/80 shadow-2xl reveal">
                
                <!-- Top Window Bar -->
                <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-800">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    </div>
                    <div class="text-xs text-slate-400 font-mono">https://e-hafalan.pesantren.id/dashboard</div>
                    <div class="w-12"></div>
                </div>

                <!-- Mockup Content Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    
                    <!-- Mini Sidebar -->
                    <div class="hidden lg:block lg:col-span-3 space-y-2 border-r border-slate-800 pr-4">
                        <div class="flex items-center space-x-3 px-3 py-2.5 bg-emerald-600/20 text-emerald-400 rounded-xl font-medium text-sm">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span>Dashboard Utama</span>
                        </div>
                        <div class="flex items-center space-x-3 px-3 py-2.5 text-slate-400 hover:bg-slate-800 rounded-xl font-medium text-sm transition-colors">
                            <i class="fa-solid fa-users"></i>
                            <span>Data Santri</span>
                        </div>
                        <div class="flex items-center space-x-3 px-3 py-2.5 text-slate-400 hover:bg-slate-800 rounded-xl font-medium text-sm transition-colors">
                            <i class="fa-solid fa-book-open"></i>
                            <span>Setoran Hafalan</span>
                        </div>
                        <div class="flex items-center space-x-3 px-3 py-2.5 text-slate-400 hover:bg-slate-800 rounded-xl font-medium text-sm transition-colors">
                            <i class="fa-solid fa-file-invoice"></i>
                            <span>Laporan PDF/Excel</span>
                        </div>
                    </div>

                    <!-- Mini Content Area -->
                    <div class="lg:col-span-9 space-y-6">
                        
                        <!-- Top Stat Cards Mockup -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="bg-slate-800/80 p-4 rounded-2xl border border-slate-700/50">
                                <p class="text-xs text-slate-400 mb-1">Target Bulan Ini</p>
                                <p class="text-xl font-bold text-emerald-400">120 Juz</p>
                                <div class="w-full bg-slate-700 h-1.5 rounded-full mt-3 overflow-hidden">
                                    <div class="bg-emerald-500 h-full" style="width: 75%"></div>
                                </div>
                            </div>
                            <div class="bg-slate-800/80 p-4 rounded-2xl border border-slate-700/50">
                                <p class="text-xs text-slate-400 mb-1">Setoran Hari Ini</p>
                                <p class="text-xl font-bold text-teal-400">45 Santri</p>
                                <div class="w-full bg-slate-700 h-1.5 rounded-full mt-3 overflow-hidden">
                                    <div class="bg-teal-500 h-full" style="width: 90%"></div>
                                </div>
                            </div>
                            <div class="bg-slate-800/80 p-4 rounded-2xl border border-slate-700/50">
                                <p class="text-xs text-slate-400 mb-1">Predikat Rata-Rata</p>
                                <p class="text-xl font-bold text-yellow-400">Jayyid Jiddan</p>
                                <div class="w-full bg-slate-700 h-1.5 rounded-full mt-3 overflow-hidden">
                                    <div class="bg-yellow-500 h-full" style="width: 85%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Simulated Chart Bars -->
                        <div class="bg-slate-800/50 p-5 rounded-2xl border border-slate-700/50">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-sm font-semibold text-slate-300">Grafik Capaian Hafalan Santri</h4>
                                <span class="text-xs text-slate-500">Tahun 2026</span>
                            </div>
                            <div class="h-40 flex items-end justify-between gap-2 pt-4">
                                <div class="w-full bg-emerald-500/20 hover:bg-emerald-500/40 rounded-t-lg transition-all h-[40%]"></div>
                                <div class="w-full bg-emerald-500/20 hover:bg-emerald-500/40 rounded-t-lg transition-all h-[55%]"></div>
                                <div class="w-full bg-emerald-500/20 hover:bg-emerald-500/40 rounded-t-lg transition-all h-[75%]"></div>
                                <div class="w-full bg-emerald-500 hover:bg-emerald-400 rounded-t-lg transition-all h-[90%]"></div>
                                <div class="w-full bg-emerald-500/20 hover:bg-emerald-500/40 rounded-t-lg transition-all h-[60%]"></div>
                                <div class="w-full bg-emerald-500/20 hover:bg-emerald-500/40 rounded-t-lg transition-all h-[80%]"></div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>
    </section>


    <!-- ==========================================
         7. TESTIMONI SECTION
    =========================================== -->
    <section id="testimoni" class="py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                <h2 class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-3">Testimoni Pengguna</h2>
                <p class="text-3xl sm:text-4xl font-bold text-slate-900">Apa Kata Mereka Tentang E-Hafalan?</p>
                <p class="text-slate-600 mt-4 text-sm sm:text-base">Pengalaman nyata dari stakeholder pesantren yang telah terbantu oleh sistem kami.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Testimonial 1 -->
                <div class="glass-card rounded-3xl p-6 border border-slate-200/80 flex flex-col justify-between hover:shadow-xl transition-all duration-300 reveal">
                    <div>
                        <div class="flex text-amber-400 text-sm mb-4 space-x-1">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="text-slate-600 text-sm italic mb-6">
                            "Sangat membantu saya sebagai orang tua. Sekarang bisa memantau perkembangan hafalan anak setiap hari dari luar kota tanpa harus sering menelepon pengasuh."
                        </p>
                    </div>
                    <div class="flex items-center space-x-3 pt-4 border-t border-slate-100">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center text-sm">
                            H
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">H. Abdullah Mansur</h4>
                            <p class="text-xs text-slate-500">Wali Santri</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="glass-card rounded-3xl p-6 border border-slate-200/80 flex flex-col justify-between hover:shadow-xl transition-all duration-300 reveal">
                    <div>
                        <div class="flex text-amber-400 text-sm mb-4 space-x-1">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="text-slate-600 text-sm italic mb-6">
                            "Proses penilaian setoran santri jadi jauh lebih efisien. Tidak ada lagi buku setoran fisik yang terselip atau rusak, semua tersimpan rapi di database."
                        </p>
                    </div>
                    <div class="flex items-center space-x-3 pt-4 border-t border-slate-100">
                        <div class="w-10 h-10 rounded-full bg-teal-100 text-teal-700 font-bold flex items-center justify-center text-sm">
                            U
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">Ust. Ahmad Fauzi, S.Pd.I</h4>
                            <p class="text-xs text-slate-500">Penguji Tahfizh</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="glass-card rounded-3xl p-6 border border-slate-200/80 flex flex-col justify-between hover:shadow-xl transition-all duration-300 reveal">
                    <div>
                        <div class="flex text-amber-400 text-sm mb-4 space-x-1">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="text-slate-600 text-sm italic mb-6">
                            "E-Hafalan memberikan gambaran analisis data capaian santri secara transparan. Sangat merekomendasikan sistem ini untuk era pesantren digital."
                        </p>
                    </div>
                    <div class="flex items-center space-x-3 pt-4 border-t border-slate-100">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center text-sm">
                            K
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">KH. M. Zainuddin</h4>
                            <p class="text-xs text-slate-500">Pengasuh Pesantren</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <!-- ==========================================
         8. TENTANG & CTA SECTION
    =========================================== -->
    <section id="tentang" class="py-20 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-r from-emerald-700 via-emerald-600 to-teal-600 rounded-3xl p-8 sm:p-12 lg:p-16 text-white shadow-2xl relative overflow-hidden reveal">
                
                <!-- Background Decorative Shape -->
                <div class="absolute top-0 right-0 -mt-12 -mr-12 w-96 h-96 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative z-10 max-w-3xl mx-auto text-center space-y-6">
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight">
                        Siap Bertransformasi ke Pesantren Digital?
                    </h2>
                    <p class="text-emerald-100 text-base sm:text-lg leading-relaxed">
                        Mulai digitalisasi pengelolaan hafalan Al-Qur'an pesantren Anda hari ini. Cepat, akurat, dan mudah digunakan oleh seluruh elemen lembaga.
                    </p>
                    <div class="pt-4 flex flex-col sm:flex-row justify-center items-center space-y-3 sm:space-y-0 sm:space-x-4">
                        <a href="register.php" class="w-full sm:w-auto px-8 py-4 text-base font-bold text-emerald-800 bg-white hover:bg-slate-100 rounded-2xl shadow-xl transition-all duration-300">
                            Daftarkan Pesantren Sekarang
                        </a>
                        <a href="login.php" class="w-full sm:w-auto px-8 py-4 text-base font-semibold text-white border border-white/40 hover:bg-white/10 rounded-2xl transition-all duration-300">
                            Masuk ke Aplikasi
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <!-- ==========================================
         9. FOOTER SECTION
    =========================================== -->
    <footer class="bg-slate-900 text-slate-400 pt-16 pb-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 pb-12 border-b border-slate-800">
                
                <!-- Brand Info -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-white font-bold text-xl">
                            <i class="fa-solid fa-quran"></i>
                        </div>
                        <span class="text-xl font-bold text-white">E-Hafalan</span>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed max-w-sm">
                        Platform Sistem Monitoring Hafalan Al-Qur'an berbasis web yang membantu pesantren mencetak generasi huffazh berkualitas dan terorganisir secara modern.
                    </p>
                </div>

                <!-- Quick Links 1 -->
                <div>
                    <h4 class="text-white text-sm font-semibold mb-4">Navigasi Utama</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="#beranda" class="hover:text-emerald-400 transition-colors">Beranda</a></li>
                        <li><a href="#fitur" class="hover:text-emerald-400 transition-colors">Fitur Utama</a></li>
                        <li><a href="#statistik" class="hover:text-emerald-400 transition-colors">Statistik Capaian</a></li>
                        <li><a href="#alur" class="hover:text-emerald-400 transition-colors">Alur Sistem</a></li>
                    </ul>
                </div>

                <!-- Quick Links 2 -->
                <div>
                    <h4 class="text-white text-sm font-semibold mb-4">Portal Akses</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="login.php" class="hover:text-emerald-400 transition-colors">Dashboard Santri</a></li>
                        <li><a href="login.php" class="hover:text-emerald-400 transition-colors">Dashboard Ustadz</a></li>
                        <li><a href="login.php" class="hover:text-emerald-400 transition-colors">Dashboard Pengasuh</a></li>
                        <li><a href="login.php" class="hover:text-emerald-400 transition-colors">Dashboard Wali Santri</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h4 class="text-white text-sm font-semibold mb-4">Kontak Kami</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li class="flex items-center space-x-2">
                            <i class="fa-solid fa-location-dot text-emerald-500"></i>
                            <span>Jl. Pesantren No. 123, Indonesia</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fa-solid fa-envelope text-emerald-500"></i>
                            <span>info@e-hafalan.id</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fa-solid fa-phone text-emerald-500"></i>
                            <span>+62 812-3456-7890</span>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Bottom Copyright & Social Links -->
            <div class="pt-8 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 text-xs text-slate-500">
                <p>&copy; <?php echo date('Y'); ?> E-Hafalan. All Rights Reserved. Dikembangkan untuk Startup Edutech Pesantren.</p>
                <div class="flex space-x-4 text-base text-slate-400">
                    <a href="#" class="hover:text-emerald-400 transition-colors"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#" class="hover:text-emerald-400 transition-colors"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="hover:text-emerald-400 transition-colors"><i class="fa-brands fa-youtube"></i></a>
                    <a href="#" class="hover:text-emerald-400 transition-colors"><i class="fa-brands fa-github"></i></a>
                </div>
            </div>
        </div>
    </footer>


    <!-- ==========================================
         10. VANILLA JAVASCRIPT LOGIC
    =========================================== -->
    <script>
        // 1. Sticky Navbar Glass Effect on Scroll
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) {
                navbar.classList.add('glass-nav', 'shadow-sm', 'border-b', 'border-slate-200/50');
            } else {
                navbar.classList.remove('glass-nav', 'shadow-sm', 'border-b', 'border-slate-200/50');
            }
        });

        // 2. Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Close Mobile Menu when link clicked
        const mobileLinks = mobileMenu.querySelectorAll('a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
            });
        });

        // 3. Counter Animation for Statistics
        const counters = document.querySelectorAll('.counter');
        let counterAnimated = false;

        const animateCounters = () => {
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                const speed = 200; // Lower is faster
                const count = +counter.innerText;
                const inc = target / speed;

                const updateCount = () => {
                    const current = +counter.innerText;
                    if (current < target) {
                        counter.innerText = Math.ceil(current + inc);
                        setTimeout(updateCount, 15);
                    } else {
                        counter.innerText = target.toLocaleString('id-ID');
                    }
                };

                updateCount();
            });
        };

        // 4. Scroll Reveal Animation Trigger
        const revealElements = document.querySelectorAll('.reveal');

        const revealOnScroll = () => {
            const windowHeight = window.innerHeight;
            const elementVisible = 100;

            revealElements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                if (elementTop < windowHeight - elementVisible) {
                    element.classList.add('active');
                }
            });

            // Trigger counter when statistic section is visible
            const statSection = document.getElementById('statistik');
            if (statSection) {
                const statTop = statSection.getBoundingClientRect().top;
                if (statTop < windowHeight - elementVisible && !counterAnimated) {
                    animateCounters();
                    counterAnimated = true;
                }
            }
        };

        window.addEventListener('scroll', revealOnScroll);
        // Initial Check
        revealOnScroll();
    </script>
</body>

</html>