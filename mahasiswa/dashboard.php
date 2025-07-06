<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Dashboard Mahasiswa';
$activePage = 'dashboard';
$user_id = $_SESSION['user_id'];

// Ambil statistik
$q1 = $conn->query("SELECT COUNT(*) AS total FROM praktikum_mahasiswa WHERE user_id = $user_id");
$jumlah_praktikum = $q1->fetch_assoc()['total'];
$q2 = $conn->query("SELECT COUNT(*) AS total FROM laporan WHERE user_id = $user_id");
$jumlah_laporan = $q2->fetch_assoc()['total'];
$q3 = $conn->query("SELECT COUNT(*) AS total FROM laporan WHERE user_id = $user_id AND nilai IS NOT NULL");
$jumlah_dinilai = $q3->fetch_assoc()['total'];

// --- PERUBAHAN DIMULAI DI SINI ---
// Query baru untuk mengambil notifikasi laporan yang sudah dinilai
$stmt_notif = $conn->prepare(
    "SELECT m.judul AS judul_modul, p.nama AS nama_praktikum, l.nilai 
     FROM laporan l 
     JOIN modul m ON l.modul_id = m.id 
     JOIN praktikum p ON m.praktikum_id = p.id 
     WHERE l.user_id = ? AND l.nilai IS NOT NULL 
     ORDER BY l.id DESC 
     LIMIT 5"
);
$stmt_notif->bind_param("i", $user_id);
$stmt_notif->execute();
$notif_result = $stmt_notif->get_result();
// --- PERUBAHAN SELESAI DI SINI ---

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Mahasiswa - <?= htmlspecialchars($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0F172A; }
        .stat-card {
            background-color: #1E293B; border: 1px solid #334155;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
    </style>
</head>
<body class="min-h-screen text-white">
    <nav class="bg-slate-900/80 backdrop-blur-lg border-b border-slate-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="dashboard.php" class="text-white text-2xl font-extrabold tracking-wide">SIMPRAK</a>
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <a href="dashboard.php" class="text-white bg-slate-800 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                            <a href="my_courses.php" class="text-slate-300 hover:bg-slate-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Praktikum Saya</a>
                            <a href="courses.php" class="text-slate-300 hover:bg-slate-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Cari Praktikum</a>
                        </div>
                    </div>
                </div>
                <div class="hidden md:block">
                    <a href="../logout.php" class="bg-red-500/10 hover:bg-red-500/20 text-red-300 font-semibold py-2 px-4 rounded-lg text-sm transition-all duration-200">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-8 md:px-6 lg:px-8">
        <header class="mb-10">
            <h1 class="text-4xl font-extrabold text-white">
                Halo, <?= htmlspecialchars($_SESSION['nama']) ?> 👋
            </h1>
            <p class="text-lg text-slate-400 mt-1">Selamat datang kembali di dasbor praktikum Anda.</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="stat-card rounded-xl p-6">
                <p class="text-slate-400 text-sm mb-1">Praktikum Diikuti</p>
                <h2 class="text-4xl font-extrabold text-white"><?= $jumlah_praktikum ?></h2>
            </div>
            <div class="stat-card rounded-xl p-6">
                <p class="text-slate-400 text-sm mb-1">Laporan Dikirim</p>
                <h2 class="text-4xl font-extrabold text-white"><?= $jumlah_laporan ?></h2>
            </div>
            <div class="stat-card rounded-xl p-6">
                <p class="text-slate-400 text-sm mb-1">Laporan Dinilai</p>
                <h2 class="text-4xl font-extrabold text-white"><?= $jumlah_dinilai ?></h2>
            </div>
        </div>
        
        <section>
            <h2 class="text-2xl font-bold text-white mb-5">Pengumuman Terbaru</h2>
            <div class="space-y-4">
                <?php
                // --- PERUBAHAN DIMULAI DI SINI ---
                // Menampilkan notifikasi nilai dari database
                if ($notif_result->num_rows > 0) {
                    while ($notif = $notif_result->fetch_assoc()) {
                        echo '<div class="bg-slate-800 border border-green-500/30 rounded-lg p-5">';
                        echo '<h3 class="text-lg font-semibold text-green-400 mb-1">Laporan Telah Dinilai!</h3>';
                        echo '<p class="text-slate-300 leading-relaxed">Laporan Anda untuk <b>' . htmlspecialchars($notif['nama_praktikum']) . ' - ' . htmlspecialchars($notif['judul_modul']) . '</b> telah dinilai. Anda mendapatkan nilai: <b class="text-2xl text-white">' . htmlspecialchars($notif['nilai']) . '</b></p>';
                        echo '</div>';
                    }
                }

                // Menampilkan pengumuman statis yang sudah ada
                $pengumuman = [
                    ["judul" => "Pengumpulan Modul 1", "isi" => "Harap mengunggah laporan Modul 1 sebelum hari Jumat pukul 23:59 WIB. Pastikan format file sesuai panduan."],
                    ["judul" => "Libur Nasional", "isi" => "Tanggal 17 Agustus tidak ada kegiatan praktikum. Jadwal praktikum akan disesuaikan."]
                ];
                foreach ($pengumuman as $p):
                ?>
                <div class="bg-slate-800 border border-slate-700 rounded-lg p-5">
                    <h3 class="text-lg font-semibold text-blue-400 mb-1"><?= htmlspecialchars($p['judul']) ?></h3>
                    <p class="text-slate-300 leading-relaxed"><?= nl2br(htmlspecialchars($p['isi'])) ?></p>
                </div>
                <?php endforeach; 
                // --- PERUBAHAN SELESAI DI SINI ---
                ?>
            </div>
        </section>
    </main>
</body>
</html>