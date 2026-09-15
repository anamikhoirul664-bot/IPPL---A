<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');
$nama_user = getUserNama();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_pengumuman'])) {
    $judul    = trim($_POST['judul']);
    $isi      = trim($_POST['isi']);
    $tanggal  = date('Y-m-d H:i:s');

    if (!empty($judul) && !empty($isi)) {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO pengumuman (judul, isi, tanggal) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $judul, $isi, $tanggal);
        if (mysqli_stmt_execute($stmt)) {
            $msg = 'posted';
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $p_id = intval($_GET['id']);
    mysqli_query($koneksi, "DELETE FROM pengumuman WHERE id = $p_id");
    header("Location: pengumuman.php?msg=deleted");
    exit();
}

$pengumuman_list = mysqli_query($koneksi, "SELECT * FROM pengumuman ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengumuman - E-Hafalan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex">

    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col min-h-screen sticky top-0 z-30">
        <div class="p-5 border-b border-slate-800 flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white text-xl font-bold">
                <i class="fa-solid fa-quran"></i>
            </div>
            <div>
                <h1 class="font-bold text-white text-lg leading-tight">E-Hafalan</h1>
                <span class="text-xs text-emerald-400 font-medium">Panel Super Admin</span>
            </div>
        </div>
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto text-sm">
            <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-solid fa-chart-pie text-slate-400 w-5"></i><span>Dashboard</span></a>
            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase text-slate-500">Master Data</div>
            <a href="santri.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-solid fa-user-graduate text-slate-400 w-5"></i><span>Data Santri</span></a>
            <a href="wali.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-solid fa-users text-slate-400 w-5"></i><span>Data Wali Santri</span></a>
            <a href="pengasuh.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-solid fa-user-tie text-slate-400 w-5"></i><span>Data Pengasuh</span></a>
            <a href="users.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-solid fa-user-gear text-slate-400 w-5"></i><span>Kelola User</span></a>
            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase text-slate-500">Akademik & Hafalan</div>
            <a href="jadwal.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-regular fa-calendar-alt text-slate-400 w-5"></i><span>Jadwal Halaqah</span></a>
            <a href="setoran.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-solid fa-book-bookmark text-slate-400 w-5"></i><span>Setoran Hafalan</span></a>
            <a href="penilaian.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-solid fa-star text-slate-400 w-5"></i><span>Penilaian & Nilai</span></a>
            <div class="pt-4 pb-1 px-4 text-[11px] font-bold uppercase text-slate-500">Laporan & Info</div>
            <a href="statistik.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-solid fa-chart-line text-slate-400 w-5"></i><span>Statistik Hafalan</span></a>
            <a href="laporan.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-solid fa-file-invoice text-slate-400 w-5"></i><span>Laporan Hafalan</span></a>
            <a href="pengumuman.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-emerald-600 text-white font-medium shadow-lg shadow-emerald-600/30"><i class="fa-solid fa-bullhorn text-lg w-5"></i><span>Pengumuman</span></a>
            <a href="setting.php" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl hover:bg-slate-800 transition-all"><i class="fa-solid fa-sliders text-slate-400 w-5"></i><span>Pengaturan Sistem</span></a>
        </nav>
        <div class="p-4 border-t border-slate-800 flex items-center justify-between">
            <span class="text-xs text-white truncate font-semibold"><?php echo htmlspecialchars($nama_user); ?></span>
            <a href="../../logout.php" class="text-slate-400 hover:text-red-400"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </aside>

    <main class="flex-1 flex flex-col min-w-0">
        <header class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-20">
            <h2 class="text-xl font-bold text-slate-800">Kelola Pengumuman</h2>
            <p class="text-xs text-slate-500">Buat berita dan pemberitahuan penting untuk pengguna</p>
        </header>

        <div class="p-6 space-y-6">
            <?php if ($msg == 'posted' || (isset($_GET['msg']) && $_GET['msg'] == 'deleted')): ?>
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
                    <i class="fa-solid fa-circle-check mr-2"></i> Operasi pengumuman berhasil dilaksanakan!
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <h3 class="font-bold text-slate-800 text-base mb-4">Buat Pengumuman Baru</h3>
                <form action="pengumuman.php" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Pengumuman *</label>
                        <input type="text" name="judul" required placeholder="Judul informasi..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Isi Pengumuman *</label>
                        <textarea name="isi" rows="4" required placeholder="Tuliskan detail pengumuman di sini..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500"></textarea>
                    </div>
                    <div class="text-right">
                        <button type="submit" name="post_pengumuman" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs rounded-xl shadow-md transition-all">
                            <i class="fa-solid fa-paper-plane mr-1"></i> Terbitkan Pengumuman
                        </button>
                    </div>
                </form>
            </div>

            <div class="space-y-4">
                <h3 class="font-bold text-slate-800 text-base">Riwayat Pengumuman</h3>
                <?php while ($p = mysqli_fetch_assoc($pengumuman_list)): ?>
                    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm relative">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-bold text-slate-800 text-base"><?php echo htmlspecialchars($p['judul']); ?></h4>
                            <span class="text-[11px] text-slate-400"><?php echo date('d M Y, H:i', strtotime($p['tanggal'])); ?></span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed mb-4"><?php echo nl2br(htmlspecialchars($p['isi'])); ?></p>
                        <div class="text-right">
                            <a href="pengumuman.php?action=delete&id=<?php echo $p['id']; ?>" onclick="return confirm('Hapus pengumuman ini?');" class="text-xs text-red-600 hover:underline">
                                <i class="fa-solid fa-trash mr-1"></i> Hapus
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>
</body>
</html>