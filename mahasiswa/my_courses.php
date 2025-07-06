<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Praktikum Saya';
$activePage = 'my_courses';
$user_id = $_SESSION['user_id'];

$sql = "
    SELECT p.id, p.nama, p.semester, p.tahun 
    FROM praktikum_mahasiswa pm
    JOIN praktikum p ON p.id = pm.praktikum_id
    WHERE pm.user_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Mahasiswa - <?= htmlspecialchars($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; background-color: #0F172A; } </style>
</head>
<body class="min-h-screen text-white">
    <nav class="bg-slate-900/80 backdrop-blur-lg border-b border-slate-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="dashboard.php" class="text-white text-2xl font-extrabold tracking-wide">SIMPRAK</a>
                    <div class="hidden md:block ml-10 flex items-baseline space-x-4">
                        <a href="dashboard.php" class="text-slate-300 hover:bg-slate-700 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                        <a href="my_courses.php" class="text-white bg-slate-800 px-3 py-2 rounded-md text-sm font-medium">Praktikum Saya</a>
                        <a href="courses.php" class="text-slate-300 hover:bg-slate-700 px-3 py-2 rounded-md text-sm font-medium">Cari Praktikum</a>
                    </div>
                </div>
                <div class="hidden md:block">
                    <a href="../logout.php" class="bg-red-500/10 hover:bg-red-500/20 text-red-300 font-semibold py-2 px-4 rounded-lg text-sm">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-8 md:px-6 lg:px-8">
        <header class="mb-10">
            <h1 class="text-4xl font-extrabold text-white">Praktikum Saya</h1>
            <p class="text-lg text-slate-400 mt-1">Daftar praktikum yang sedang atau pernah Anda ikuti.</p>
        </header>

        <?php if ($result->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="bg-slate-800 rounded-xl shadow-lg border border-slate-700 p-6 flex flex-col justify-between hover:border-purple-500 transition-all duration-300">
                        <div>
                            <h2 class="text-xl font-bold text-white mb-1"><?= htmlspecialchars($row['nama']) ?></h2>
                            <p class="text-sm text-slate-400 mb-6">Semester: <?= $row['semester'] ?> - Tahun: <?= $row['tahun'] ?></p>
                        </div>
                        <a href="detail_praktikum.php?id=<?= $row['id'] ?>" class="text-center w-full px-5 py-2.5 text-sm font-medium rounded-md text-white bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 transition-all transform hover:scale-[1.03]">
                            Lihat Detail &rarr;
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16 bg-slate-800 rounded-xl border border-slate-700">
                <p class="text-xl text-slate-400 font-semibold mb-2">Anda belum terdaftar di praktikum manapun.</p>
                <p class="text-slate-500 mb-6">Jelajahi katalog untuk menemukan praktikum yang menarik!</p>
                <a href="courses.php" class="inline-flex items-center px-6 py-2.5 border border-transparent text-base font-medium rounded-lg shadow-lg text-white bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700">
                    Cari Praktikum
                </a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>