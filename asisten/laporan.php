<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}
$pageTitle = 'Laporan Masuk';
$activePage = 'laporan';
$filter_modul = $_GET['modul_id'] ?? '';
$filter_user = $_GET['user_id'] ?? '';
$filter_status = $_GET['status'] ?? '';
$modul_q = $conn->query("SELECT m.id, p.nama AS praktikum_nama, m.pertemuan_ke, m.judul FROM modul m JOIN praktikum p ON m.praktikum_id = p.id ORDER BY p.nama, m.pertemuan_ke");
$mahasiswa_q = $conn->query("SELECT id, nama FROM users WHERE role = 'mahasiswa' ORDER BY nama");
$sql = "SELECT l.id AS laporan_id, u.nama AS mahasiswa_nama, p.nama AS praktikum_nama, m.pertemuan_ke, m.judul, l.file_laporan, l.nilai FROM laporan l JOIN modul m ON l.modul_id = m.id JOIN praktikum p ON m.praktikum_id = p.id JOIN users u ON l.user_id = u.id WHERE 1=1";
if ($filter_modul !== '') $sql .= " AND l.modul_id = " . intval($filter_modul);
if ($filter_user !== '') $sql .= " AND l.user_id = " . intval($filter_user);
if ($filter_status === 'belum') $sql .= " AND l.nilai IS NULL";
elseif ($filter_status === 'sudah') $sql .= " AND l.nilai IS NOT NULL";
$sql .= " ORDER BY l.id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Asisten - <?= htmlspecialchars($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; background-color: #0F172A; } </style>
</head>
<body class="min-h-screen flex">
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
        <div class="bg-[#1E293B] p-6 rounded-xl shadow-lg mb-8 border border-slate-700">
            <h1 class="text-3xl font-extrabold text-white"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="text-slate-400 mt-1">Kelola dan nilai laporan praktikum yang masuk.</p>
        </div>

        <div class="bg-[#1E293B] p-6 rounded-xl shadow-lg mb-8 border border-slate-700">
            <h3 class="text-xl font-semibold text-white mb-4">Filter Laporan</h3>
            <form method="get" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Modul:</label>
                    <select name="modul_id" class="block w-full bg-slate-700/50 border-slate-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                        <option value="">Semua Modul</option>
                        <?php while ($modul = $modul_q->fetch_assoc()): ?>
                            <option value="<?= $modul['id'] ?>" <?= ($filter_modul == $modul['id']) ? 'selected' : '' ?>>P.<?= $modul['pertemuan_ke'] ?>: <?= htmlspecialchars($modul['judul']) ?></option>
                        <?php endwhile; $modul_q->data_seek(0); ?>
                    </select>
                </div>
                 <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Mahasiswa:</label>
                    <select name="user_id" class="block w-full bg-slate-700/50 border-slate-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                        <option value="">Semua Mahasiswa</option>
                        <?php while ($mhs = $mahasiswa_q->fetch_assoc()): ?>
                            <option value="<?= $mhs['id'] ?>" <?= ($filter_user == $mhs['id']) ? 'selected' : '' ?>><?= htmlspecialchars($mhs['nama']) ?></option>
                        <?php endwhile; $mahasiswa_q->data_seek(0); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Status Penilaian:</label>
                    <select name="status" class="block w-full bg-slate-700/50 border-slate-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                        <option value="">Semua Status</option>
                        <option value="belum" <?= ($filter_status === 'belum') ? 'selected' : '' ?>>Belum Dinilai</option>
                        <option value="sudah" <?= ($filter_status === 'sudah') ? 'selected' : '' ?>>Sudah Dinilai</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md text-white bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700">Terapkan Filter</button>
                    <a href="laporan.php" class="inline-flex items-center px-4 py-2 border border-slate-600 text-sm font-medium rounded-md text-slate-300 bg-slate-800 hover:bg-slate-700">Reset</a>
                </div>
            </form>
        </div>

        <div class="bg-[#1E293B] rounded-xl shadow-lg overflow-hidden border border-slate-700">
            <table class="min-w-full"><thead class="bg-slate-800"><tr><th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Mahasiswa</th><th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Modul</th><th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Laporan</th><th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Nilai</th><th class="px-6 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider">Aksi</th></tr></thead><tbody class="divide-y divide-slate-700">
                <?php if ($result->num_rows > 0): while ($row = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-slate-800">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white"><?= htmlspecialchars($row['mahasiswa_nama']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">P.<?= $row['pertemuan_ke'] ?>: <?= htmlspecialchars($row['judul']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><a href="../db/laporan/<?= htmlspecialchars($row['file_laporan']) ?>" target="_blank" class="text-blue-400 hover:text-blue-300 font-semibold">Lihat</a></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"><?php if ($row['nilai'] !== null): ?><span class="px-2.5 py-1 inline-flex text-xs font-bold rounded-full bg-green-500/20 text-green-300"><?= htmlspecialchars($row['nilai']) ?></span><?php else: ?><span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full bg-yellow-500/20 text-yellow-300">Belum dinilai</span><?php endif; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"><a href="edit_nilai.php?id=<?= $row['laporan_id'] ?>" class="text-blue-400 hover:text-blue-300 font-semibold"><?= $row['nilai'] === null ? 'Beri Nilai' : 'Edit' ?></a></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="5" class="text-center py-10 text-slate-400">Tidak ada laporan.</td></tr>
                <?php endif; ?>
            </tbody></table>
        </div>
    </main>
</body>
</html>