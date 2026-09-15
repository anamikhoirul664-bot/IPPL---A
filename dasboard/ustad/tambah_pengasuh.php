<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama         = trim($_POST['nama']);
    $username     = trim($_POST['username']);
    $email        = trim($_POST['email']);
    $telepon      = trim($_POST['telepon']);
    $password     = $_POST['password'];
    $nip          = trim($_POST['nip']);
    $spesialisasi = trim($_POST['spesialisasi']);
    $alamat       = trim($_POST['alamat']);

    if (empty($nama) || empty($username) || empty($email) || empty($password)) {
        $error = "Kolom bertanda bintang (*) wajib diisi.";
    } else {
        // Cek username/email unik
        $check = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username' OR email = '$email'");

        if (mysqli_num_rows($check) > 0) {
            $error = "Username atau Email sudah terdaftar.";
        } else {
            $hashed_pass = password_hash($password, PASSWORD_BCRYPT);

            // Insert ke tabel users dengan role 'ustad'
            $stmt1 = mysqli_prepare($koneksi, "INSERT INTO users (nama, username, email, password, role, telepon) VALUES (?, ?, ?, ?, 'ustad', ?)");
            mysqli_stmt_bind_param($stmt1, "sssss", $nama, $username, $email, $hashed_pass, $telepon);

            if (mysqli_stmt_execute($stmt1)) {
                $user_id = mysqli_insert_id($koneksi);

                // Insert ke tabel pengasuh
                $stmt2 = mysqli_prepare($koneksi, "INSERT INTO pengasuh (user_id, nip, spesialisasi, alamat) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt2, "isss", $user_id, $nip, $spesialisasi, $alamat);
                mysqli_stmt_execute($stmt2);

                header("Location: pengasuh.php?msg=success");
                exit();
            } else {
                $error = "Gagal menambah data pengasuh.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pengasuh Baru - E-Hafalan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex">

    <main class="flex-1 p-6 max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Tambah Pengasuh Baru</h2>
                <p class="text-xs text-slate-500">Buat akun Ustadz / Ustadzah pengampu halaqah</p>
            </div>
            <a href="pengasuh.php" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-xl text-xs font-medium hover:bg-slate-300 transition-colors">
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
            <form action="tambah_pengasuh.php" method="POST" class="space-y-5">
                
                <h3 class="text-sm font-bold text-emerald-600 uppercase tracking-wider border-b border-slate-100 pb-2">Informasi Akun Login</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap & Gelar *</label>
                        <input type="text" name="nama" required placeholder="Ust. Ahmad Fauzi, S.Pd.I" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Username *</label>
                        <input type="text" name="username" required placeholder="ust_fauzi" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email *</label>
                        <input type="email" name="email" required placeholder="fauzi@pesantren.com" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Password *</label>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <h3 class="text-sm font-bold text-emerald-600 uppercase tracking-wider border-b border-slate-100 pb-2 pt-4">Data Kepegawaian & Kontak</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">NIP / Kode Pengasuh</label>
                        <input type="text" name="nip" placeholder="UST-2026-001" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Telepon / WA</label>
                        <input type="text" name="telepon" placeholder="081298765432" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Spesialisasi / Keahlian</label>
                        <input type="text" name="spesialisasi" placeholder="Tahfizh / Tajwid / Mutqin" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Lengkap</label>
                        <textarea name="alamat" rows="2" placeholder="Komplek Pesantren..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500"></textarea>
                    </div>
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <a href="pengasuh.php" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 text-sm font-medium transition-colors">Batal</a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-all shadow-md shadow-emerald-600/20">
                        <i class="fa-solid fa-save mr-1"></i> Simpan Data Pengasuh
                    </button>
                </div>

            </form>
        </div>
    </main>

</body>
</html>