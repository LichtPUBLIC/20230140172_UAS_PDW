<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}
$pageTitle = 'Dashboard Asisten';
$activePage = 'dashboard';
$res1 = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'mahasiswa'");
$jumlah_mahasiswa = $res1->fetch_assoc()['total'];
$res2 = $conn->query("SELECT COUNT(*) AS total FROM modul");
$jumlah_modul = $res2->fetch_assoc()['total'];
$res3 = $conn->query("SELECT COUNT(*) AS total FROM laporan");
$jumlah_laporan = $res3->fetch_assoc()['total'];
$res4 = $conn->query("SELECT COUNT(*) AS total FROM laporan WHERE nilai IS NULL");
$jumlah_belum_dinilai = $res4->fetch_assoc()['total'];
$avg_result = $conn->query("SELECT AVG(nilai) AS rata2 FROM laporan WHERE nilai IS NOT NULL");
$rata2_nilai = round($avg_result->fetch_assoc()['rata2'] ?? 0, 2);
$top_result = $conn->query("SELECT u.nama, AVG(l.nilai) AS nilai_avg FROM laporan l JOIN users u ON l.user_id = u.id WHERE l.nilai IS NOT NULL GROUP BY l.user_id ORDER BY nilai_avg DESC LIMIT 1");
$top_user = $top_result->fetch_assoc();
$low_result = $conn->query("SELECT u.nama, AVG(l.nilai) AS nilai_avg FROM laporan l JOIN users u ON l.user_id = u.id WHERE l.nilai IS NOT NULL GROUP BY l.user_id ORDER BY nilai_avg ASC LIMIT 1");
$low_user = $low_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Asisten - <?= htmlspecialchars($pageTitle) ?></title>
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
<body class="min-h-screen flex text-white">
    <aside class="w-64 bg-slate-900 flex-shrink-0 flex flex-col border-r border-slate-700">
        <div class="p-6 text-center border-b border-slate-700">
            <h3 class="text-2xl font-extrabold text-white">SIMPRAK</h3>
            <p class="text-md font-medium text-slate-400 mt-1">Panel Asisten</p>
        </div>
        <nav class="flex-grow py-4">
            <?php include '_sidebar.php'; ?>
        </nav>
        <div class="p-4 border-t border-slate-700">
            <a href="../logout.php" class="flex items-center justify-center bg-red-800/80 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 w-full">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H5a3 3 0 01-3-3V5a3 3 0 013-3h5a3 3 0 013 3v1"></path></svg>
                Logout
            </a>
        </div>
    </aside>

    <main class="flex-1 p-6 lg:p-10 overflow-auto">
        <header class="mb-8">
            <h1 class="text-4xl font-extrabold text-white">
                Halo, <?= htmlspecialchars($_SESSION['nama']) ?> 👋
            </h1>
            <p class="text-lg text-slate-400 mt-1">Selamat datang di Panel Asisten Praktikum.</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="stat-card rounded-xl p-6">
                <p class="text-slate-400 text-sm mb-1">Total Mahasiswa</p>
                <h2 class="text-4xl font-extrabold text-white"><?= $jumlah_mahasiswa ?></h2>
            </div>
            <div class="stat-card rounded-xl p-6">
                <p class="text-slate-400 text-sm mb-1">Total Modul</p>
                <h2 class="text-4xl font-extrabold text-white"><?= $jumlah_modul ?></h2>
            </div>
            <div class="stat-card rounded-xl p-6">
                <p class="text-slate-400 text-sm mb-1">Laporan Masuk</p>
                <h2 class="text-4xl font-extrabold text-white"><?= $jumlah_laporan ?></h2>
            </div>
            <div class="stat-card rounded-xl p-6 bg-yellow-500/10 border-yellow-500/30">
                <p class="text-yellow-300 text-sm mb-1">Laporan Belum Dinilai</p>
                <h2 class="text-4xl font-extrabold text-yellow-300"><?= $jumlah_belum_dinilai ?></h2>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="stat-card rounded-xl p-6">
                <p class="text-slate-400 text-sm mb-1">Rata-rata Nilai</p>
                <h2 class="text-4xl font-extrabold text-white"><?= $rata2_nilai ?></h2>
            </div>
            <div class="stat-card rounded-xl p-6">
                <p class="text-slate-400 text-sm mb-1">Nilai Tertinggi</p>
                <h2 class="text-xl font-bold text-white truncate"><?= htmlspecialchars($top_user['nama'] ?? '-') ?></h2>
                <p class="text-3xl font-extrabold text-green-400"><?= round($top_user['nilai_avg'] ?? 0) ?></p>
            </div>
            <div class="stat-card rounded-xl p-6">
                <p class="text-slate-400 text-sm mb-1">Nilai Terendah</p>
                <h2 class="text-xl font-bold text-white truncate"><?= htmlspecialchars($low_user['nama'] ?? '-') ?></h2>
                <p class="text-3xl font-extrabold text-red-400"><?= round($low_user['nilai_avg'] ?? 0) ?></p>
            </div>
        </div>
    </main>
</body>
</html>