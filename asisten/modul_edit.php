<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Edit Modul';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: modul.php");
    exit();
}

// Ambil data modul
$stmt = $conn->prepare("SELECT * FROM modul WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$modul = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$modul) {
    header("Location: modul.php");
    exit();
}

$current_file_materi = $modul['file_materi'];
$form_message = '';
$form_message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $praktikum_id = intval($_POST['praktikum_id']);
    $judul = trim($_POST['judul']);
    $pertemuan = intval($_POST['pertemuan_ke']);
    $file_materi_to_save = $current_file_materi;

    if (empty($judul) || $praktikum_id <= 0 || $pertemuan <= 0) {
        $form_message = "Semua field harus diisi dengan benar.";
        $form_message_type = "error";
    } else {
        if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['pdf', 'doc', 'docx'];
            $ext = strtolower(pathinfo($_FILES['file_materi']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $form_message = "Format file tidak valid.";
                $form_message_type = "error";
            } else {
                $upload_dir = '../db/materi/';
                if ($current_file_materi && file_exists($upload_dir . $current_file_materi)) {
                    unlink($upload_dir . $current_file_materi);
                }
                $new_filename = 'materi_' . time() . '_' . basename($_FILES['file_materi']['name']);
                if (move_uploaded_file($_FILES['file_materi']['tmp_name'], $upload_dir . $new_filename)) {
                    $file_materi_to_save = $new_filename;
                } else {
                    $form_message = "Gagal mengunggah file baru.";
                    $form_message_type = "error";
                }
            }
        }
        
        if (empty($form_message)) {
            $stmt_update = $conn->prepare("UPDATE modul SET praktikum_id = ?, judul = ?, file_materi = ?, pertemuan_ke = ? WHERE id = ?");
            $stmt_update->bind_param("issii", $praktikum_id, $judul, $file_materi_to_save, $pertemuan, $id);
            if ($stmt_update->execute()) {
                $_SESSION['last_action_message'] = 'Modul "' . htmlspecialchars($judul) . '" berhasil diperbarui!';
                header("Location: modul.php");
                exit();
            } else {
                $form_message = "Gagal menyimpan perubahan.";
                $form_message_type = "error";
            }
            $stmt_update->close();
        }
    }
}
$praktikum_result = $conn->query("SELECT id, nama FROM praktikum ORDER BY nama");
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
        select {
            -webkit-appearance: none; -moz-appearance: none; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 0.5rem center; background-size: 1.5em 1.5em; padding-right: 2.5rem;
        }
        input[type="file"]::file-selector-button {
            background: #334155; color: #cbd5e1; border: none; padding: 0.5rem 1rem; border-radius: 0.375rem;
            margin-right: 1rem; cursor: pointer; transition: background-color 0.2s;
        }
        input[type="file"]::file-selector-button:hover { background: #475569; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-[#1E293B] p-8 rounded-xl shadow-2xl w-full max-w-lg border border-slate-700">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-white"><?= htmlspecialchars($pageTitle) ?></h2>
            <p class="text-slate-400 mt-2">Perbarui detail modul praktikum.</p>
        </div>

        <?php if (!empty($form_message)): ?>
            <div class="bg-red-500/10 text-red-300 px-4 py-3 rounded-md mb-6 text-center border border-red-500/30">
                <p><?= htmlspecialchars($form_message) ?></p>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label for="praktikum_id" class="block text-sm font-medium text-slate-300 mb-1">Pilih Praktikum</label>
                <select name="praktikum_id" id="praktikum_id" required class="block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-md shadow-sm text-white focus:outline-none focus:ring-2 focus:ring-purple-500 sm:text-sm">
                    <?php while ($row = $praktikum_result->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>" <?= ($modul['praktikum_id'] == $row['id']) ? 'selected' : '' ?>><?= htmlspecialchars($row['nama']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label for="judul" class="block text-sm font-medium text-slate-300 mb-1">Judul Modul</label>
                <input type="text" name="judul" id="judul" required value="<?= htmlspecialchars($modul['judul']) ?>" class="mt-1 block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-md shadow-sm text-white focus:outline-none focus:ring-2 focus:ring-purple-500 sm:text-sm">
            </div>
            <div>
                <label for="pertemuan_ke" class="block text-sm font-medium text-slate-300 mb-1">Pertemuan Ke-</label>
                <input type="number" name="pertemuan_ke" id="pertemuan_ke" min="1" required value="<?= htmlspecialchars($modul['pertemuan_ke']) ?>" class="mt-1 block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-md shadow-sm text-white focus:outline-none focus:ring-2 focus:ring-purple-500 sm:text-sm">
            </div>
            <div>
                <label for="file_materi" class="block text-sm font-medium text-slate-300 mb-1">Upload File Materi Baru (Opsional)</label>
                <input type="file" name="file_materi" id="file_materi" class="block w-full text-sm text-slate-400 border border-slate-600 rounded-lg cursor-pointer bg-slate-700/50 focus:outline-none">
                <?php if ($current_file_materi): ?>
                    <p class="text-sm mt-2 text-slate-400">File saat ini: 
                        <a href="../db/materi/<?= htmlspecialchars($current_file_materi) ?>" class="text-blue-400 hover:underline" target="_blank"><?= htmlspecialchars($current_file_materi) ?></a>
                    </p>
                <?php endif; ?>
            </div>
            <div class="flex justify-between items-center pt-4">
                <a href="modul.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors duration-200">&larr; Kembali</a>
                <button type="submit" class="inline-flex items-center px-6 py-2.5 border border-transparent text-base font-medium rounded-lg shadow-lg text-white bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-800 focus:ring-purple-500 transition-all duration-300 transform hover:scale-105">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</body>
</html>