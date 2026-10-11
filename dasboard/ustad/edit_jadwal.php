
<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');

$nama_user = getUserNama();
$error = '';
$wali_halaqah_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($wali_halaqah_id <= 0) {
    header("Location: jadwal.php");
    exit();
}

// Ambil daftar pengampu dari tabel users
$query_pengampu = "SELECT id, nama FROM users
                   WHERE role IN ('ustad', 'pengasuh')
                   ORDER BY nama ASC";
$result_pengampu = mysqli_query($koneksi, $query_pengampu);

$daftar_pengampu = [];

if ($result_pengampu) {
    while ($row = mysqli_fetch_assoc($result_pengampu)) {
        $daftar_pengampu[] = $row;
    }
}

// Ambil data halaqah berdasarkan ID
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM halaqah WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $wali_halaqah_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: jadwal.php");
    exit();
}

// Proses penyimpanan perubahan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_halaqah = trim($_POST['nama_halaqah'] ?? '');
    $ustad_id = (int) ($_POST['ustad_id'] ?? 0);
    $hari = trim($_POST['hari'] ?? '');
    $jam_mulai = trim($_POST['jam_mulai'] ?? '');
    $jam_selesai = trim($_POST['jam_selesai'] ?? '');
    $ruangan = trim($_POST['ruangan'] ?? '');

    $daftar_hari = [
        'Senin', 'Selasa', 'Rabu', 'Kamis',
        'Jumat', 'Sabtu', 'Minggu'
    ];

    if (
        $nama_halaqah === '' ||
        $ustad_id <= 0 ||
        !in_array($hari, $daftar_hari, true) ||
        $jam_mulai === '' ||
        $jam_selesai === '' ||
        $ruangan === ''
    ) {
        $error = "Semua kolom wajib diisi.";
    } elseif ($jam_mulai >= $jam_selesai) {
        $error = "Jam selesai harus lebih akhir daripada jam mulai.";
    } else {
        // Pastikan pengampu benar-benar tersedia
        $stmt_cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM users
             WHERE id = ? AND role IN ('ustad', 'pengasuh')"
        );

        mysqli_stmt_bind_param($stmt_cek, "i", $ustad_id);
        mysqli_stmt_execute($stmt_cek);

        $result_cek = mysqli_stmt_get_result($stmt_cek);
        $pengampu_valid = mysqli_fetch_assoc($result_cek);

        if (!$pengampu_valid) {
            $error = "Pengampu yang dipilih tidak valid.";
        } else {
            $stmt_update = mysqli_prepare(
                $koneksi,
                "UPDATE halaqah
                 SET nama_halaqah = ?,
                     ustad_id = ?,
                     hari = ?,
                     jam_mulai = ?,
                     jam_selesai = ?,
                     ruangan = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt_update,
                "sissssi",
                $nama_halaqah,
                $ustad_id,
                $hari,
                $jam_mulai,
                $jam_selesai,
                $ruangan,
                $wali_halaqah_id
            );

            if (mysqli_stmt_execute($stmt_update)) {
                header("Location: jadwal.php?msg=updated");
                exit();
            } else {
                $error = "Data halaqah gagal diperbarui. Silakan coba lagi.";
            }
        }
    }

    // Tampilkan kembali nilai yang dikirim jika validasi gagal
    $data['nama_halaqah'] = $nama_halaqah;
    $data['ustad_id'] = $ustad_id;
    $data['hari'] = $hari;
    $data['jam_mulai'] = $jam_mulai;
    $data['jam_selesai'] = $jam_selesai;
    $data['ruangan'] = $ruangan;
}

function e($value) {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jadwal Halaqah - E-Hafalan</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen text-slate-800">

    <header class="bg-white border-b border-slate-200 px-4 sm:px-6 py-4">
        <div class="max-w-4xl mx-auto flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white
                            flex items-center justify-center shadow-md">
                    <i class="fa-solid fa-quran text-xl"></i>
                </div>

                <div>
                    <h1 class="font-bold text-lg text-slate-800">E-Hafalan</h1>
                    <p class="text-xs text-slate-500">Panel Admin Ustadz</p>
                </div>
            </div>

            <a href="jadwal.php"
               class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200
                      text-slate-700 text-xs sm:text-sm font-semibold transition">
                <i class="fa-solid fa-arrow-left mr-1"></i>
                Kembali
            </a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">

        <div class="mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-slate-800">
                Edit Jadwal Halaqah
            </h2>

            <p class="text-sm text-slate-500 mt-1">
                Perbarui informasi kelompok halaqah di bawah ini.
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-7">

            <?php if ($error !== ''): ?>
                <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200
                            text-rose-700 text-sm">
                    <i class="fa-solid fa-circle-exclamation mr-2"></i>
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="edit_jadwal.php?id=<?php echo $wali_halaqah_id; ?>"
                  class="space-y-5">

                <div>
                    <label for="nama_halaqah"
                           class="block text-sm font-semibold text-slate-700 mb-2">
                        Nama Halaqah <span class="text-rose-500">*</span>
                    </label>

                    <input type="text"
                           id="nama_halaqah"
                           name="nama_halaqah"
                           value="<?php echo e($data['nama_halaqah']); ?>"
                           required
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200
                                  rounded-xl text-sm focus:bg-white focus:outline-none
                                  focus:ring-2 focus:ring-emerald-500 transition">
                </div>

                <div>
                    <label for="ustad_id"
                           class="block text-sm font-semibold text-slate-700 mb-2">
                        Pengampu <span class="text-rose-500">*</span>
                    </label>

                    <select id="ustad_id" name="ustad_id" required
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200
                                   rounded-xl text-sm focus:bg-white focus:outline-none
                                   focus:ring-2 focus:ring-emerald-500 transition">

                        <option value="">-- Pilih Pengampu --</option>

                        <?php foreach ($daftar_pengampu as $pengampu): ?>
                            <option value="<?php echo (int) $pengampu['id']; ?>"
                                <?php echo ((int) $data['ustad_id'] === (int) $pengampu['id'])
                                    ? 'selected' : ''; ?>>
                                <?php echo e($pengampu['nama']); ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div>
                    <label for="hari"
                           class="block text-sm font-semibold text-slate-700 mb-2">
                        Hari <span class="text-rose-500">*</span>
                    </label>

                    <select id="hari" name="hari" required
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200
                                   rounded-xl text-sm focus:bg-white focus:outline-none
                                   focus:ring-2 focus:ring-emerald-500 transition">

                        <?php
                        $daftar_hari = [
                            'Senin', 'Selasa', 'Rabu', 'Kamis',
                            'Jumat', 'Sabtu', 'Minggu'
                        ];
                        ?>

                        <?php foreach ($daftar_hari as $hari): ?>
                            <option value="<?php echo e($hari); ?>"
                                <?php echo ($data['hari'] === $hari) ? 'selected' : ''; ?>>
                                <?php echo e($hari); ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="jam_mulai"
                               class="block text-sm font-semibold text-slate-700 mb-2">
                            Jam Mulai <span class="text-rose-500">*</span>
                        </label>

                        <input type="time"
                               id="jam_mulai"
                               name="jam_mulai"
                               value="<?php echo e(substr($data['jam_mulai'], 0, 5)); ?>"
                               required
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200
                                      rounded-xl text-sm focus:bg-white focus:outline-none
                                      focus:ring-2 focus:ring-emerald-500 transition">
                    </div>

                    <div>
                        <label for="jam_selesai"
                               class="block text-sm font-semibold text-slate-700 mb-2">
                            Jam Selesai <span class="text-rose-500">*</span>
                        </label>

                        <input type="time"
                               id="jam_selesai"
                               name="jam_selesai"
                               value="<?php echo e(substr($data['jam_selesai'], 0, 5)); ?>"
                               required
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200
                                      rounded-xl text-sm focus:bg-white focus:outline-none
                                      focus:ring-2 focus:ring-emerald-500 transition">
                    </div>
                </div>

                <div>
                    <label for="ruangan"
                           class="block text-sm font-semibold text-slate-700 mb-2">
                        Ruangan / Lokasi <span class="text-rose-500">*</span>
                    </label>

                    <input type="text"
                           id="ruangan"
                           name="ruangan"
                           value="<?php echo e($data['ruangan']); ?>"
                           required
                           placeholder="Contoh: Masjid Utama"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200
                                  rounded-xl text-sm focus:bg-white focus:outline-none
                                  focus:ring-2 focus:ring-emerald-500 transition">
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4
                            border-t border-slate-100">

                    <a href="jadwal.php"
                       class="px-5 py-3 rounded-xl bg-slate-100 hover:bg-slate-200
                              text-slate-700 text-sm font-semibold text-center transition">
                        Batal
                    </a>

                    <button type="submit"
                            class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700
                                   text-white text-sm font-semibold shadow-md
                                   shadow-emerald-600/20 transition">
                        <i class="fa-solid fa-floppy-disk mr-2"></i>
                        Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>

    </main>

</body>
</html>
