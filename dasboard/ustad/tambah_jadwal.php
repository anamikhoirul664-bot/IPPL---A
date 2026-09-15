<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_halaqah = trim($_POST['nama_halaqah']);
    $pengasuh_id  = !empty($_POST['pengasuh_id']) ? intval($_POST['pengasuh_id']) : NULL;
    $hari         = trim($_POST['hari']);
    $jam_mulai    = trim($_POST['jam_mulai']);
    $jam_selesai  = trim($_POST['jam_selesai']);
    $ruangan      = trim($_POST['ruangan']);

    if (empty($nama_halaqah) || empty($hari) || empty($jam_mulai)) {
        $error = "Nama halaqah, hari, dan jam mulai wajib diisi.";
    } else {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO halaqah (nama_halaqah, pengasuh_id, hari, jam_mulai, jam_selesai, ruangan) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sissss", $nama_halaqah, $pengasuh_id, $hari, $jam_mulai, $jam_selesai, $ruangan);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: jadwal.php?msg=success");
            exit();
        } else {
            $error = "Gagal membuat jadwal halaqah.";
        }
    }
}

// Fetch list pengasuh untuk dropdown
$pengasuh_list = mysqli_query($koneksi, "SELECT p.id, u.nama FROM pengasuh p JOIN users u ON p.user_id = u.id");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Halaqah Baru - E-Hafalan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex">

    <main class="flex-1 p-6 max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Buat Halaqah / Jadwal Baru</h2>
                <p class="text-xs text-slate-500">Tentukan nama kelompok, pengasuh, dan jadwal pertemuan</p>
            </div>
            <a href="jadwal.php" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-xl text-xs font-medium hover:bg-slate-300 transition-colors">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-600 text-sm flex items-center space-x-3">
                <i class="fa-solid fa-circle-exclamation text-lg"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
            <form action="tambah_jadwal.php" method="POST" class="space-y-4">
                
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Kelompok Halaqah *</label>
                    <input type="text" name="nama_halaqah" required placeholder="Contoh: Halaqah Ali Bin Abi Thalib" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pengasuh / Ustadz Pembimbing</label>
                    <select name="pengasuh_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                        <option value="">-- Pilih Pengasuh --</option>
                        <?php while ($p = mysqli_fetch_assoc($pengasuh_list)): ?>
                            <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nama']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Hari Rutin *</label>
                        <select name="hari" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                            <option value="Setiap Hari">Setiap Hari</option>
                            <option value="Senin - Jumat">Senin - Jumat</option>
                            <option value="Sabtu & Minggu">Sabtu & Minggu</option>
                            <option value="Subuh">Subuh (Setiap Hari)</option>
                            <option value="Maghrib">Maghrib (Setiap Hari)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Mulai *</label>
                        <input type="time" name="jam_mulai" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Selesai</label>
                        <input type="time" name="jam_selesai" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Lokasi / Ruangan</label>
                    <input type="text" name="ruangan" placeholder="Contoh: Masjid Lantai 2 / Gazebo A" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <a href="jadwal.php" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 text-sm font-medium transition-colors">Batal</a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-all shadow-md shadow-emerald-600/20">
                        <i class="fa-solid fa-save mr-1"></i> Simpan Halaqah
                    </button>
                </div>

            </form>
        </div>
    </main>

</body>
</html>