<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Edit Nilai';

$laporan_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($laporan_id <= 0) {
    // Sebaiknya redirect dengan pesan error, tapi untuk sementara kita redirect ke laporan
    header("Location: laporan.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT 
        l.id, l.nilai, l.file_laporan,
        u.nama AS nama_mahasiswa,
        m.judul AS judul_modul,
        m.pertemuan_ke,
        p.nama AS praktikum
    FROM laporan l
    JOIN modul m ON l.modul_id = m.id
    JOIN praktikum p ON m.praktikum_id = p.id
    JOIN users u ON l.user_id = u.id
    WHERE l.id = ?
");
$stmt->bind_param("i", $laporan_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    header("Location: laporan.php?error=notfound");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nilai'])) {
    $nilai_baru = intval($_POST['nilai']);

    if (is_numeric($nilai_baru) && $nilai_baru >= 0 && $nilai_baru <= 100) {
        $update = $conn->prepare("UPDATE laporan SET nilai = ? WHERE id = ?");
        $update->bind_param("ii", $nilai_baru, $laporan_id);
        $update->execute();
        $update->close();
    }

    header("Location: laporan.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Asisten - <?= htmlspecialchars($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Inter', sans-serif; background-color: #0F172A; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 text-white">
    <div class="bg-[#1E293B] p-8 rounded-xl shadow-2xl w-full max-w-lg border border-slate-700">
        <div class="mb-6">
            <h2 class="text-3xl font-extrabold text-white"><?= $pageTitle ?></h2>
            <p class="text-slate-400 mt-1">Berikan atau perbarui nilai untuk laporan mahasiswa.</p>
        </div>

        <div class="mb-6 space-y-2 border-l-4 border-slate-600 pl-4 text-sm">
            <p><strong class="text-slate-400">Nama Mahasiswa:</strong> <span class="font-semibold"><?= htmlspecialchars($data['nama_mahasiswa']) ?></span></p>
            <p><strong class="text-slate-400">Praktikum:</strong> <span class="font-semibold"><?= htmlspecialchars($data['praktikum']) ?></span></p>
            <p><strong class="text-slate-400">Modul:</strong> <span class="font-semibold">P.<?= $data['pertemuan_ke'] ?> - <?= htmlspecialchars($data['judul_modul']) ?></span></p>
            <p><strong class="text-slate-400">File Laporan:</strong> 
                <a href="../db/laporan/<?= htmlspecialchars($data['file_laporan']) ?>" target="_blank" class="text-blue-400 hover:text-blue-300 underline font-semibold">
                    Lihat Laporan
                </a>
            </p>
        </div>

        <form method="post" class="space-y-4">
            <div>
                <label for="nilai" class="block font-medium text-slate-300 mb-1">Nilai Baru (0-100)</label>
                <input type="number" id="nilai" name="nilai" min="0" max="100" value="<?= htmlspecialchars($data['nilai'] ?? '') ?>" required 
                       class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <div class="flex justify-end items-center space-x-4 pt-4">
                <a href="laporan.php" class="px-6 py-2 text-sm font-medium text-slate-300 bg-slate-800 hover:bg-slate-700 rounded-lg transition-all duration-200">Batal</a>
                <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 rounded-lg shadow-lg">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</body>
</html>