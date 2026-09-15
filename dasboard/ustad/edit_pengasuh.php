<?php
require_once '../../config/auth.php';
require_once '../../config/koneksi.php';

checkRole('ustad');

$error = '';
$pengasuh_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data pengasuh saat ini
$query_curr = "SELECT p.*, u.nama, u.username, u.email, u.telepon, u.status 
               FROM pengasuh p JOIN users u ON p.user_id = u.id WHERE p.id = $pengasuh_id";
$res_curr = mysqli_query($koneksi, $query_curr);
$data = mysqli_fetch_assoc($res_curr);

if (!$data) {
    header("Location: pengasuh.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama         = trim($_POST['nama']);
    $username     = trim($_POST['username']);
    $email        = trim($_POST['email']);
    $telepon      = trim($_POST['telepon']);
    $status       = $_POST['status'];
    $nip          = trim($_POST['nip']);
    $spesialisasi = trim($_POST['spesialisasi']);
    $alamat       = trim($_POST['alamat']);
    $password     = $_POST['password'];

    $user_id = $data['user_id'];

    if (empty($nama) || empty($username) || empty($email)) {
        $error = "Kolom bertanda (*) tidak boleh kosong.";
    } else {
        // Update user
        if (!empty($password)) {
            $hashed_pass = password_hash($password, PASSWORD_BCRYPT);
            $stmt1 = mysqli_prepare($koneksi, "UPDATE users SET nama=?, username=?, email=?, telepon=?, status=?, password=? WHERE id=?");
            mysqli_stmt_bind_param($stmt1, "ssssssi", $nama, $username, $email, $telepon, $status, $hashed_pass, $user_id);
        } else {
            $stmt1 = mysqli_prepare($koneksi, "UPDATE users SET nama=?, username=?, email=?, telepon=?, status=? WHERE id=?");
            mysqli_stmt_bind_param($stmt1, "sssssi", $nama, $username, $email, $telepon, $status, $user_id);
        }
        mysqli_stmt_execute($stmt1);

        // Update detail pengasuh
        $stmt2 = mysqli_prepare($koneksi, "UPDATE pengasuh SET nip=?, spesialisasi=?, alamat=? WHERE id=?");
        mysqli_stmt_bind_param($stmt2, "sssi", $nip, $spesialisasi, $alamat, $pengasuh_id);
        mysqli_stmt_execute($stmt2);

        header("Location: pengasuh.php?msg=updated");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Data Pengasuh - E-Hafalan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex">

    <main class="flex-1 p-6 max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Edit Data Pengasuh</h2>
                <p class="text-xs text-slate-500">Perbarui informasi pengasuh halaqah</p>
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
            <form action="edit_pengasuh.php?id=<?php echo $pengasuh_id; ?>" method="POST" class="space-y-5">
                
                <h3 class="text-sm font-bold text-emerald-600 uppercase tracking-wider border-b border-slate-100 pb-2">Informasi Akun Login</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap & Gelar *</label>
                        <input type="text" name="nama" value="<?php echo htmlspecialchars($data['nama']); ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Username *</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($data['username']); ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email *</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($data['email']); ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Password Baru (Biarkan kosong jika tidak diganti)</label>
                        <input type="password" name="password" placeholder="••••••••" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <h3 class="text-sm font-bold text-emerald-600 uppercase tracking-wider border-b border-slate-100 pb-2 pt-4">Data Kepegawaian & Status</h3>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">NIP / Kode</label>
                        <input type="text" name="nip" value="<?php echo htmlspecialchars($data['nip']); ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Telepon / WA</label>
                        <input type="text" name="telepon" value="<?php echo htmlspecialchars($data['telepon']); ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Spesialisasi</label>
                        <input type="text" name="spesialisasi" value="<?php echo htmlspecialchars($data['spesialisasi']); ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status Akun</label>
                        <select name="status" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                            <option value="aktif" <?php echo $data['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="nonaktif" <?php echo $data['status'] == 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                        </select>
                    </div>
                    <div class="sm:col-span-4">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Lengkap</label>
                        <textarea name="alamat" rows="2" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500"><?php echo htmlspecialchars($data['alamat']); ?></textarea>
                    </div>
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <a href="pengasuh.php" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 text-sm font-medium transition-colors">Batal</a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-all shadow-md shadow-emerald-600/20">
                        <i class="fa-solid fa-save mr-1"></i> Perbarui Data Pengasuh
                    </button>
                </div>

            </form>
        </div>
    </main>

</body>
</html>