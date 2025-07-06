<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$praktikum_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($praktikum_id <= 0) {
    header("Location: my_courses.php");
    exit();
}

// Cek kepesertaan
$cek_ikut = $conn->prepare("SELECT id FROM praktikum_mahasiswa WHERE praktikum_id = ? AND user_id = ?");
$cek_ikut->bind_param("ii", $praktikum_id, $user_id);
$cek_ikut->execute();
if ($cek_ikut->get_result()->num_rows === 0) {
    header("Location: my_courses.php");
    exit();
}
$cek_ikut->close();

// Info praktikum
$stmt_prak = $conn->prepare("SELECT * FROM praktikum WHERE id = ?");
$stmt_prak->bind_param("i", $praktikum_id);
$stmt_prak->execute();
$praktikum = $stmt_prak->get_result()->fetch_assoc();
$stmt_prak->close();
if (!$praktikum) {
    header("Location: my_courses.php");
    exit();
}
$pageTitle = "Detail: " . htmlspecialchars($praktikum['nama']);

// Handle upload laporan
$upload_message = '';
$upload_message_type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_laporan'])) {
    $modul_id = intval($_POST['modul_id']);
    $file_ext = strtolower(pathinfo($_FILES['file_laporan']['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, ['pdf', 'doc', 'docx'])) {
        $_SESSION['upload_feedback'] = ['message' => 'Format file tidak didukung.', 'type' => 'error'];
    } else {
        $check = $conn->prepare("SELECT id FROM laporan WHERE modul_id = ? AND user_id = ?");
        $check->bind_param("ii", $modul_id, $user_id);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            $upload_dir = '../db/laporan/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $filename = 'laporan_' . $user_id . '_' . $modul_id . '_' . time() . '.' . $file_ext;
            if (move_uploaded_file($_FILES['file_laporan']['tmp_name'], $upload_dir . $filename)) {
                $stmt_upload = $conn->prepare("INSERT INTO laporan (modul_id, user_id, file_laporan) VALUES (?, ?, ?)");
                $stmt_upload->bind_param("iis", $modul_id, $user_id, $filename);
                $_SESSION['upload_feedback'] = $stmt_upload->execute() ? 
                    ['message' => 'Laporan berhasil diunggah.', 'type' => 'success'] : 
                    ['message' => 'Gagal menyimpan data laporan.', 'type' => 'error'];
                $stmt_upload->close();
            } else {
                $_SESSION['upload_feedback'] = ['message' => 'Gagal mengunggah file.', 'type' => 'error'];
            }
        } else {
            $_SESSION['upload_feedback'] = ['message' => 'Anda sudah mengunggah laporan untuk modul ini.', 'type' => 'info'];
        }
        $check->close();
    }
    header("Location: detail_praktikum.php?id=" . $praktikum_id);
    exit();
}

if (isset($_SESSION['upload_feedback'])) {
    $upload_message = $_SESSION['upload_feedback']['message'];
    $upload_message_type = $_SESSION['upload_feedback']['type'];
    unset($_SESSION['upload_feedback']);
}

// Ambil modul
$stmt_modul = $conn->prepare("SELECT m.id, m.judul, m.file_materi, m.pertemuan_ke, l.file_laporan, l.nilai FROM modul m LEFT JOIN laporan l ON m.id = l.modul_id AND l.user_id = ? WHERE m.praktikum_id = ? ORDER BY m.pertemuan_ke ASC");
$stmt_modul->bind_param("ii", $user_id, $praktikum_id);
$stmt_modul->execute();
$modul_result = $stmt_modul->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; background-color: #0F172A; } </style>
</head>
<body class="min-h-screen text-white">
    <nav class="bg-slate-900/80 backdrop-blur-lg border-b border-slate-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="dashboard.php" class="text-white text-2xl font-extrabold tracking-wide">SIMPRAK</a>
                <a href="my_courses.php" class="inline-flex items-center text-slate-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                    &larr; Kembali ke Praktikum Saya
                </a>
            </div>
        </div>
    </nav>
    <main class="container mx-auto px-4 py-8 md:px-6 lg:px-8">
        <header class="mb-10">
            <h1 class="text-4xl font-extrabold text-white"><?= htmlspecialchars($praktikum['nama']) ?></h1>
            <p class="text-lg text-slate-400 mt-1">Semester: <?= htmlspecialchars($praktikum['semester']) ?> - Tahun: <?= htmlspecialchars($praktikum['tahun']) ?></p>
        </header>

        <?php if (!empty($upload_message)): ?>
            <div class="p-4 rounded-md mb-6 text-center border <?= 
                $upload_message_type === 'success' ? 'bg-green-500/10 text-green-300 border-green-500/30' : 
               ($upload_message_type === 'error' ? 'bg-red-500/10 text-red-300 border-red-500/30' : 
               'bg-blue-500/10 text-blue-300 border-blue-500/30') ?>">
                <p><?= htmlspecialchars($upload_message) ?></p>
            </div>
        <?php endif; ?>

        <div class="space-y-6">
            <?php if ($modul_result->num_rows > 0): ?>
                <?php while ($modul = $modul_result->fetch_assoc()): ?>
                    <div class="bg-slate-800 rounded-xl shadow-lg border border-slate-700 p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-2xl font-bold text-white">Pertemuan <?= $modul['pertemuan_ke'] ?>: <?= htmlspecialchars($modul['judul']) ?></h2>
                            </div>
                            <div class="text-right">
                                <p class="text-slate-400 text-sm">Status Nilai</p>
                                <?php if ($modul['nilai'] !== null): ?>
                                    <p class="text-2xl font-bold text-green-400"><?= htmlspecialchars($modul['nilai']) ?></p>
                                <?php else: ?>
                                    <p class="text-lg font-semibold text-yellow-400">Belum Dinilai</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="border-t border-slate-700 mt-4 pt-4">
                            <?php if ($modul['file_laporan']): ?>
                                <div class="bg-slate-700/50 p-4 rounded-lg">
                                    <p class="text-sm text-green-300 font-semibold flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                        Laporan Sudah Diunggah: 
                                        <a href="../db/laporan/<?= htmlspecialchars($modul['file_laporan']) ?>" class="underline ml-2" target="_blank">Lihat Laporan</a>
                                    </p>
                                </div>
                            <?php else: ?>
                                <form action="detail_praktikum.php?id=<?= $praktikum_id ?>" method="post" enctype="multipart/form-data" class="space-y-3">
                                    <input type="hidden" name="modul_id" value="<?= $modul['id'] ?>">
                                    <div>
                                        <label for="file_laporan_<?= $modul['id'] ?>" class="text-sm font-medium text-slate-300">Upload Laporan Anda:</label>
                                        <input type="file" name="file_laporan" id="file_laporan_<?= $modul['id'] ?>" accept=".pdf,.doc,.docx" required class="mt-1 block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-violet-500/20 file:text-violet-300 hover:file:bg-violet-500/30">
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700">Upload</button>
                                        <?php if ($modul['file_materi']): ?>
                                            <a href="../db/materi/<?= htmlspecialchars($modul['file_materi']) ?>" target="_blank" class="text-sm text-slate-300 hover:text-white underline">Unduh Materi</a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-16 bg-slate-800 rounded-xl border border-slate-700">
                    <p class="text-xl text-slate-400 font-semibold mb-2">Belum ada modul untuk praktikum ini.</p>
                    <p class="text-slate-500">Silakan cek kembali nanti.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>