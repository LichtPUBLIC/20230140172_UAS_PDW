<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Tambah Pengguna';
$form_message = '';
$form_message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    if (empty($nama) || empty($email) || empty($password) || empty($role)) {
        $form_message = 'Semua field harus diisi.';
        $form_message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, ['mahasiswa', 'asisten'])) {
        $form_message = 'Data yang dimasukkan tidak valid.';
        $form_message_type = 'error';
    } else {
        $cek = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $cek->bind_param("s", $email);
        $cek->execute();
        $cek->store_result();
        if ($cek->num_rows > 0) {
            $form_message = 'Email sudah digunakan.';
            $form_message_type = 'error';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
            $insert->bind_param("ssss", $nama, $email, $hashed_password, $role);
            if ($insert->execute()) {
                $_SESSION['last_action_message'] = 'Pengguna ' . htmlspecialchars($nama) . ' berhasil ditambahkan!';
                header("Location: users.php");
                exit();
            } else {
                $form_message = 'Gagal menambah pengguna.';
                $form_message_type = 'error';
            }
            $insert->close();
        }
        $cek->close();
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
        body { font-family: 'Inter', sans-serif; background-color: #0F172A; }
        select {
            -webkit-appearance: none; -moz-appearance: none; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 0.5rem center; background-size: 1.5em 1.5em; padding-right: 2.5rem;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-[#1E293B] p-8 rounded-xl shadow-2xl w-full max-w-lg border border-slate-700">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-white"><?= htmlspecialchars($pageTitle) ?></h2>
            <p class="text-slate-400 mt-2">Buat akun baru untuk sistem.</p>
        </div>

        <?php if (!empty($form_message)): ?>
            <div class="bg-red-500/10 text-red-300 px-4 py-3 rounded-md mb-6 text-center border border-red-500/30">
                <p><?= htmlspecialchars($form_message) ?></p>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-6">
            <div>
                <label for="nama" class="block text-sm font-medium text-slate-300 mb-1">Nama Lengkap</label>
                <input type="text" name="nama" id="nama" required class="mt-1 block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Contoh: Budi Sanjaya">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-slate-300 mb-1">Email</label>
                <input type="email" name="email" id="email" required class="mt-1 block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="contoh@email.com">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-slate-300 mb-1">Password</label>
                <input type="password" name="password" id="password" required class="mt-1 block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Buat password">
            </div>
            <div>
                <label for="role" class="block text-sm font-medium text-slate-300 mb-1">Role</label>
                <select name="role" id="role" required class="block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="" disabled selected>-- Pilih Role --</option>
                    <option value="mahasiswa">Mahasiswa</option>
                    <option value="asisten">Asisten</option>
                </select>
            </div>
            <div class="flex justify-between items-center pt-4">
                <a href="users.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors duration-200">&larr; Kembali</a>
                <button type="submit" class="inline-flex items-center px-6 py-2.5 border border-transparent text-base font-medium rounded-lg shadow-lg text-white bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 transition-all duration-300 transform hover:scale-105">Simpan Pengguna</button>
            </div>
        </form>
    </div>
</body>
</html>