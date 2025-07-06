<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Tambah Praktikum';
$form_message = '';
$form_message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $jumlah_sks = intval($_POST['jumlah_sks']);

    if (empty($nama) || $jumlah_sks <= 0) {
        $form_message = 'Nama praktikum dan jumlah SKS harus valid.';
        $form_message_type = 'error';
    } else {
        $check_stmt = $conn->prepare("SELECT id FROM praktikum WHERE nama = ?");
        $check_stmt->bind_param("s", $nama);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $form_message = 'Praktikum dengan nama tersebut sudah ada.';
            $form_message_type = 'error';
        } else {
            $stmt = $conn->prepare("INSERT INTO praktikum (nama, jumlah_sks) VALUES (?, ?)");
            $stmt->bind_param("si", $nama, $jumlah_sks);
            if ($stmt->execute()) {
                $_SESSION['last_action_message'] = 'Praktikum "' . htmlspecialchars($nama) . '" berhasil ditambahkan!';
                $_SESSION['last_action_message_type'] = 'success';
                header("Location: praktikum.php");
                exit();
            } else {
                $form_message = 'Gagal menambah praktikum.';
                $form_message_type = 'error';
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}
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
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0F172A; /* slate-900 */
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-[#1E293B] p-8 rounded-xl shadow-2xl w-full max-w-lg border border-slate-700">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-white"><?= htmlspecialchars($pageTitle) ?></h2>
            <p class="text-slate-400 mt-2">Buat data praktikum baru.</p>
        </div>

        <?php if (!empty($form_message)): ?>
            <div class="bg-red-500/10 text-red-300 px-4 py-3 rounded-md mb-6 text-center border border-red-500/30">
                <p><?= htmlspecialchars($form_message) ?></p>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-6">
            <div>
                <label for="nama" class="block text-sm font-medium text-slate-300 mb-1">Nama Praktikum</label>
                <input type="text" name="nama" id="nama" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                    class="mt-1 block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-md shadow-sm placeholder-slate-400 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm transition duration-200"
                    placeholder="Contoh: Pemrograman Berorientasi Objek">
            </div>
            <div>
                <label for="jumlah_sks" class="block text-sm font-medium text-slate-300 mb-1">Jumlah SKS</label>
                <input type="number" name="jumlah_sks" id="jumlah_sks" min="1" required value="<?= htmlspecialchars($_POST['jumlah_sks'] ?? '') ?>"
                    class="mt-1 block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-md shadow-sm placeholder-slate-400 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm transition duration-200"
                    placeholder="Contoh: 3">
            </div>
            <div class="flex justify-between items-center pt-4">
                <a href="praktikum.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors duration-200">
                    &larr; Kembali
                </a>
                <button type="submit" class="inline-flex items-center px-6 py-2.5 border border-transparent text-base font-medium rounded-lg shadow-lg text-white bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-800 focus:ring-purple-500 transition-all duration-300 transform hover:scale-105">
                    Simpan Praktikum
                </button>
            </div>
        </form>
    </div>
</body>
</html>