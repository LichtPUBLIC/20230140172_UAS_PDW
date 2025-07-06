<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}
$pageTitle = 'Manajemen Praktikum';
$activePage = 'praktikum';
$sql = "SELECT id, nama, jumlah_sks FROM praktikum ORDER BY nama";
$result = $conn->query($sql);
$form_message = $_SESSION['last_action_message'] ?? '';
unset($_SESSION['last_action_message']);
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
        .table-hover tbody tr:hover { background-color: #334155; }
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
        <header class="mb-8 flex items-center justify-between">
            <h1 class="text-4xl font-extrabold text-white">
                <?= htmlspecialchars($pageTitle) ?>
            </h1>
            <a href="praktikum_tambah.php" class="inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-lg text-white bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Tambah Praktikum
            </a>
        </header>

        <?php if (!empty($form_message)): ?>
            <div class="bg-green-500/10 text-green-300 px-4 py-3 rounded-md mb-6 border border-green-500/30">
                <p><?= htmlspecialchars($form_message) ?></p>
            </div>
        <?php endif; ?>
        
        <div class="bg-[#1E293B] rounded-xl shadow-2xl overflow-hidden border border-slate-700">
            <table class="min-w-full divide-y divide-slate-700">
                <thead class="bg-slate-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Nama Praktikum</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Jumlah SKS</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    <?php if ($result->num_rows > 0): while ($row = $result->fetch_assoc()): ?>
                        <tr class="table-hover">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-white"><?= htmlspecialchars($row['nama']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-slate-700 text-slate-200">
                                    <?= htmlspecialchars($row['jumlah_sks']) ?> SKS
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div class="inline-flex items-center space-x-4">
                                    <a href="praktikum_edit.php?id=<?= $row['id'] ?>" class="text-blue-400 hover:text-blue-300 font-semibold">Edit</a>
                                    <a href="praktikum_hapus.php?id=<?= $row['id'] ?>" class="text-red-400 hover:text-red-300 font-semibold" onclick="return confirm('Yakin ingin menghapus praktikum ini?')">Hapus</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="3" class="text-center py-12 text-slate-400">Belum ada praktikum.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>