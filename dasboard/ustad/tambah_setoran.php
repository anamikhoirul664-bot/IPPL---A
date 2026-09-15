<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');

$error = '';

// Data Santri
$santri_query = mysqli_query($koneksi, "SELECT s.id, u.nama, s.nis FROM santri s JOIN users u ON s.user_id = u.id ORDER BY u.nama ASC");

// Ambil ID Ustadz penguji saat ini
$cur_user_id = getUserId();
$get_ust = mysqli_query($koneksi, "SELECT id FROM ustadz WHERE user_id = $cur_user_id");
$ustadz_data = mysqli_fetch_assoc($get_ust);
$ustadz_id = $ustadz_data['id'] ?? NULL;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $santri_id    = intval($_POST['santri_id']);
    $tanggal      = $_POST['tanggal'];
    $surah        = trim($_POST['surah']);
    $ayat_mulai   = intval($_POST['ayat_mulai']);
    $ayat_selesai = intval($_POST['ayat_selesai']);
    $juz          = intval($_POST['juz']);
    $kelancaran   = $_POST['kelancaran'];
    $tajwid       = $_POST['tajwid'];
    $catatan      = trim($_POST['catatan']);

    if (empty($santri_id) || empty($surah) || empty($juz) || empty($tanggal)) {
        $error = "Pilih Santri, Surah, Juz, dan Tanggal setoran terlebih dahulu.";
    } else {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO setoran_hafalan (santri_id, ustadz_id, tanggal, surah, ayat_mulai, ayat_selesai, juz, kelancaran, tajwid, catatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iisssiisss", $santri_id, $ustadz_id, $tanggal, $surah, $ayat_mulai, $ayat_selesai, $juz, $kelancaran, $tajwid, $catatan);

        if (mysqli_stmt_execute($stmt)) {
            
            // Hitung total juz yang sudah pernah disetorkan santri (update ke tabel santri)
            $count_query = mysqli_query($koneksi, "SELECT COUNT(DISTINCT juz) AS total_juz FROM setoran_hafalan WHERE santri_id = $santri_id AND kelancaran IN ('Lancar', 'Cukup Lancar')");
            $count_data = mysqli_fetch_assoc($count_query);
            $total_juz = $count_data['total_juz'] ?? 0;

            mysqli_query($koneksi, "UPDATE santri SET total_hafalan_juz = $total_juz WHERE id = $santri_id");

            header("Location: setoran.php?msg=success");
            exit();
        } else {
            $error = "Gagal mencatat setoran hafalan.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Catat Setoran Hafalan - E-Hafalan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex">

    <main class="flex-1 p-6 max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Catat Setoran Hafalan Baru</h2>
                <p class="text-xs text-slate-500">Input hasil simaan hafalan santri</p>
            </div>
            <a href="setoran.php" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-xl text-xs font-medium hover:bg-slate-300 transition-colors">
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
            <form action="tambah_setoran.php" method="POST" class="space-y-5">
                
                <h3 class="text-sm font-bold text-emerald-600 uppercase tracking-wider border-b border-slate-100 pb-2">Identitas Santri & Tanggal</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Santri *</label>
                        <select name="santri_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                            <option value="">-- Pilih Santri --</option>
                            <?php while($s = mysqli_fetch_assoc($santri_query)): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['nama']) . " (" . htmlspecialchars($s['nis']) . ")"; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Setoran *</label>
                        <input type="date" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <h3 class="text-sm font-bold text-emerald-600 uppercase tracking-wider border-b border-slate-100 pb-2 pt-4">Materi Hafalan</h3>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Surah *</label>
                        <input type="text" name="surah" required placeholder="Contoh: Al-Baqarah" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Juz Ke- *</label>
                        <input type="number" name="juz" min="1" max="30" value="1" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div class="flex space-x-2">
                        <div class="w-1/2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Ayat Mulai</label>
                            <input type="number" name="ayat_mulai" value="1" min="1" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                        </div>
                        <div class="w-1/2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Ayat Selesai</label>
                            <input type="number" name="ayat_selesai" value="10" min="1" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                        </div>
                    </div>
                </div>

                <h3 class="text-sm font-bold text-emerald-600 uppercase tracking-wider border-b border-slate-100 pb-2 pt-4">Penilaian & Catatan Penguji</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tingkat Kelancaran *</label>
                        <select name="kelancaran" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                            <option value="Lancar">Lancar (Sangat Baik)</option>
                            <option value="Cukup Lancar">Cukup Lancar (Ada 1-3 Kali Lupa)</option>
                            <option value="Kurang Lancar">Kurang Lancar (Perlu Mengulang)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kualitas Tajwid & Makhraj *</label>
                        <select name="tajwid" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                            <option value="Sangat Baik">Sangat Baik</option>
                            <option value="Baik">Baik</option>
                            <option value="Cukup">Cukup</option>
                            <option value="Perlu Perbaikan">Perlu Perbaikan</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan / Evaluasi Ustadz</label>
                        <textarea name="catatan" rows="3" placeholder="Contoh: Perhatikan makhraj huruf 'Ain dan perpanjang mad jaiz muttashil." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500"></textarea>
                    </div>
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <a href="setoran.php" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 text-sm font-medium transition-colors">Batal</a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-all shadow-md shadow-emerald-600/20">
                        <i class="fa-solid fa-save mr-1"></i> Simpan Setoran Hafalan
                    </button>
                </div>

            </form>
        </div>
    </main>

</body>
</html>