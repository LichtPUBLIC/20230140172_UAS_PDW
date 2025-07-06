<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Cari Praktikum';
$activePage = 'courses';
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['praktikum_id'])) {
    $praktikum_id = intval($_POST['praktikum_id']);
    $user_id = $_SESSION['user_id'];

    $check = $conn->prepare("SELECT * FROM praktikum_mahasiswa WHERE user_id = ? AND praktikum_id = ?");
    $check->bind_param("ii", $user_id, $praktikum_id);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        $insert = $conn->prepare("INSERT INTO praktikum_mahasiswa (user_id, praktikum_id) VALUES (?, ?)");
        $insert->bind_param("ii", $user_id, $praktikum_id);
        if ($insert->execute()) {
            $message = "Berhasil mendaftar ke praktikum!";
            $message_type = "success";
        } else {
            $message = "Gagal mendaftar.";
            $message_type = "error";
        }
        $insert->close();
    } else {
        $message = "Anda sudah terdaftar pada praktikum ini.";
        $message_type = "info";
    }
    $check->close();
}

$sql = "SELECT p.id, p.nama, p.semester, p.tahun, (SELECT COUNT(*) FROM praktikum_mahasiswa pm WHERE pm.praktikum_id = p.id AND pm.user_id = ?) as terdaftar FROM praktikum p";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
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
                        <a href="my_courses.php" class="text-slate-300 hover:bg-slate-700 px-3 py-2 rounded-md text-sm font-medium">Praktikum Saya</a>
                        <a href="courses.php" class="text-white bg-slate-800 px-3 py-2 rounded-md text-sm font-medium">Cari Praktikum</a>
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
            <h1 class="text-4xl font-extrabold text-white">Katalog Praktikum</h1>
            <p class="text-lg text-slate-400 mt-1">Jelajahi dan daftar ke praktikum yang tersedia.</p>
        </header>

        <?php if (!empty($message)): ?>
            <div class="p-4 rounded-md mb-6 text-center border <?= 
                $message_type === 'success' ? 'bg-green-500/10 text-green-300 border-green-500/30' : 
               ($message_type === 'error' ? 'bg-red-500/10 text-red-300 border-red-500/30' : 
               'bg-blue-500/10 text-blue-300 border-blue-500/30') ?>">
                <p><?= htmlspecialchars($message) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="bg-slate-800 rounded-xl shadow-lg border border-slate-700 p-6 flex flex-col justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-white mb-1"><?= htmlspecialchars($row['nama']) ?></h2>
                            <p class="text-sm text-slate-400 mb-4">Semester: <?= $row['semester'] ?> - Tahun: <?= $row['tahun'] ?></p>
                        </div>
                        <form action="courses.php" method="post">
                            <input type="hidden" name="praktikum_id" value="<?= $row['id'] ?>">
                            <?php if ($row['terdaftar']): ?>
                                <button disabled class="w-full px-5 py-2.5 text-sm font-medium rounded-md bg-slate-700 text-slate-400 cursor-not-allowed">
                                    Sudah Terdaftar
                                </button>
                            <?php else: ?>
                                <button type="submit" class="w-full px-5 py-2.5 text-sm font-medium rounded-md text-white bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 transition-all transform hover:scale-[1.03]">
                                    Daftar Sekarang
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16">
                <p class="text-xl text-slate-400 font-semibold mb-2">Belum ada praktikum yang tersedia.</p>
                <p class="text-slate-500">Silakan hubungi administrator untuk informasi lebih lanjut.</p>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>