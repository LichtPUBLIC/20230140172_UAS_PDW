<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Manajemen Pengguna';
$activePage = 'users';

$sql = "SELECT id, nama, email, role FROM users ORDER BY role, nama";
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0F172A; }
        .table-striped tbody tr:nth-child(odd) { background-color: #1E293B; }
        .table-hover tbody tr:hover { background-color: #334155; }
    </style>
</head>
<body class="min-h-screen flex">
    <aside class="w-64 bg-slate-900 text-white flex-shrink-0 flex flex-col border-r border-slate-700">
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
            <a href="user_tambah.php" class="inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-lg text-white bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-purple-500 transition-all duration-300 transform hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                Tambah Pengguna
            </a>
        </header>

        <?php if (!empty($form_message)): ?>
            <div class="bg-green-500/10 text-green-300 px-4 py-3 rounded-md mb-6 text-center border border-green-500/30">
                <p><?= htmlspecialchars($form_message) ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-[#1E293B] rounded-xl shadow-2xl overflow-hidden border border-slate-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-700">
                    <thead class="bg-slate-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-slate-300 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="table-hover">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-white"><?= htmlspecialchars($row['nama']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300"><?= htmlspecialchars($row['email']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?= $row['role'] === 'asisten' ? 'bg-purple-500/20 text-purple-300' : 'bg-blue-500/20 text-blue-300' ?>">
                                            <?= ucfirst(htmlspecialchars($row['role'])) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="inline-flex items-center space-x-4">
                                            <a href="user_edit.php?id=<?= $row['id'] ?>" class="text-blue-400 hover:text-blue-300 transition-colors duration-200 font-semibold">Edit</a>
                                            <?php if ($_SESSION['user_id'] != $row['id']): ?>
                                                <a href="user_hapus.php?id=<?= $row['id'] ?>" class="text-red-400 hover:text-red-300 transition-colors duration-200 font-semibold" onclick="return confirm('Yakin ingin menghapus pengguna ini?')">Hapus</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-12 text-slate-400">
                                    <p class="text-xl font-semibold mb-2">Belum ada pengguna terdaftar.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>