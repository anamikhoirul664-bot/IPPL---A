<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');

$error = '';

// Ambil data Wali Santri untuk pilihan dropdown
$wali_query = mysqli_query($koneksi, "SELECT ws.id, u.nama FROM wali_santri ws JOIN users u ON ws.user_id = u.id");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama           = trim($_POST['nama']);
    $username       = trim($_POST['username']);
    $email          = trim($_POST['email']);
    $telepon        = trim($_POST['telepon']);
    $password       = $_POST['password'];
    $nis            = trim($_POST['nis']);
    $kelas          = trim($_POST['kelas_kelompok']);
    $wali_id        = !empty($_POST['wali_id']) ? intval($_POST['wali_id']) : NULL;
    $target_juz     = intval($_POST['target_juz']);

    if (empty($nama) || empty($username) || empty($email) || empty($password) || empty($nis)) {
        $error = "Kolom yang bertanda bintang (*) wajib diisi.";
    } else {
        // Cek username/email/nis unik
        $check = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username' OR email = '$email'");
        $check_nis = mysqli_query($koneksi, "SELECT id FROM santri WHERE nis = '$nis'");

        if (mysqli_num_rows($check) > 0) {
            $error = "Username atau Email sudah terdaftar.";
        } elseif (mysqli_num_rows($check_nis) > 0) {
            $error = "NIS santri sudah terdaftar.";
        } else {
            $hashed_pass = password_hash($password, PASSWORD_BCRYPT);

            // Insert ke tabel users
            $stmt1 = mysqli_prepare($koneksi, "INSERT INTO users (nama, username, email, password, role, telepon) VALUES (?, ?, ?, ?, 'santri', ?)");
            mysqli_stmt_bind_param($stmt1, "sssss", $nama, $username, $email, $hashed_pass, $telepon);

            if (mysqli_stmt_execute($stmt1)) {
                $user_id = mysqli_insert_id($koneksi);
                
                // Ambil ustadz_id penguji saat ini (ustadz yang sedang login)
                $cur_user_id = getUserId();
                $get_ust = mysqli_query($koneksi, "SELECT id FROM ustadz WHERE user_id = $cur_user_id");
                $ustadz_data = mysqli_fetch_assoc($get_ust);
                $ustadz_id = $ustadz_data['id'] ?? NULL;

                // Insert ke tabel santri
                $stmt2 = mysqli_prepare($koneksi, "INSERT INTO santri (user_id, nis, kelas_kelompok, ustadz_id, wali_id, target_juz) VALUES (?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt2, "issiii", $user_id, $nis, $kelas, $ustadz_id, $wali_id, $target_juz);
                mysqli_stmt_execute($stmt2);

                header("Location: santri.php?msg=success");
                exit();
            } else {
                $error = "Gagal menambah santri baru.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Santri Baru - E-Hafalan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex">

    <main class="flex-1 p-6 max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Tambah Santri Baru</h2>
                <p class="text-xs text-slate-500">Buat data dan akun login santri baru</p>
            </div>
            <a href="santri.php" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-xl text-xs font-medium hover:bg-slate-300 transition-colors">
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
            <form action="tambah_santri.php" method="POST" class="space-y-5">
                
                <h3 class="text-sm font-bold text-emerald-600 uppercase tracking-wider border-b border-slate-100 pb-2">Informasi Akun Login</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap Santri *</label>
                        <input type="text" name="nama" required placeholder="Ahmad Hanif" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">NIS (Nomor Induk Santri) *</label>
                        <input type="text" name="nis" required placeholder="SNT-2026-001" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Username *</label>
                        <input type="text" name="username" required placeholder="santri_hanif" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email *</label>
                        <input type="email" name="email" required placeholder="hanif@gmail.com" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Password *</label>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Telepon / WA</label>
                        <input type="text" name="telepon" placeholder="081234567890" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <h3 class="text-sm font-bold text-emerald-600 uppercase tracking-wider border-b border-slate-100 pb-2 pt-4">Informasi Akademik & Hafalan</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kelas / Kelompok</label>
                        <input type="text" name="kelas_kelompok" value="Kelas Tahfidz 1" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Wali Santri</label>
                        <select name="wali_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                            <option value="">-- Pilih Wali Santri --</option>
                            <?php while($wali = mysqli_fetch_assoc($wali_query)): ?>
                                <option value="<?php echo $wali['id']; ?>"><?php echo htmlspecialchars($wali['nama']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Target Hafalan (Juz)</label>
                        <input type="number" name="target_juz" value="30" min="1" max="30" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <a href="santri.php" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 text-sm font-medium transition-colors">Batal</a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-all shadow-md shadow-emerald-600/20">
                        <i class="fa-solid fa-save mr-1"></i> Simpan Data Santri
                    </button>
                </div>

            </form>
        </div>
    </main>

</body>
</html>