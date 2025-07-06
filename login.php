<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';

// Jika sudah login, redirect ke halaman yang sesuai
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'asisten') {
        header("Location: asisten/dashboard.php");
    } elseif ($_SESSION['role'] == 'mahasiswa') {
        header("Location: mahasiswa/dashboard.php");
    }
    exit();
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $message = "Email dan password harus diisi!";
    } else {
        $sql = "SELECT id, nama, email, password, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] == 'asisten') {
                    header("Location: asisten/dashboard.php");
                } elseif ($user['role'] == 'mahasiswa') {
                    header("Location: mahasiswa/dashboard.php");
                } else {
                    $message = "Peran pengguna tidak valid.";
                }
                exit();
            } else {
                $message = "Password yang Anda masukkan salah.";
            }
        } else {
            $message = "Akun dengan email tersebut tidak ditemukan.";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SIMPRAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0F172A; /* slate-900 */
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-[#1E293B] p-8 rounded-xl shadow-2xl w-full max-w-md border border-slate-700">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-white">
                Selamat Datang
            </h2>
            <p class="text-slate-400 mt-2">Login untuk mengakses dashboard Anda</p>
        </div>

        <?php 
            if (isset($_GET['status']) && $_GET['status'] == 'registered') {
                echo '<p class="bg-green-500/10 text-green-300 px-4 py-3 rounded-md mb-6 text-center border border-green-500/30">Registrasi berhasil! Silakan login.</p>';
            }
            if (!empty($message)) {
                echo '<p class="bg-red-500/10 text-red-300 px-4 py-3 rounded-md mb-6 text-center border border-red-500/30">' . htmlspecialchars($message) . '</p>';
            }
        ?>

        <form action="login.php" method="post" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-slate-300 mb-1">Email</label>
                <input type="email" id="email" name="email" required
                    class="mt-1 block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-md shadow-sm placeholder-slate-400 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm transition duration-200"
                    placeholder="nama@contoh.com">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-slate-300 mb-1">Password</label>
                <input type="password" id="password" name="password" required
                    class="mt-1 block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-md shadow-sm placeholder-slate-400 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm transition duration-200"
                    placeholder="••••••••">
            </div>
            <button type="submit"
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-lg text-lg font-semibold text-white bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-800 focus:ring-purple-500 transition-all duration-300 transform hover:scale-[1.02]">
                Login
            </button>
        </form>

        <div class="mt-8 text-center text-sm">
            <p class="text-slate-400">Belum punya akun?
                <a href="register.php" class="font-medium text-blue-400 hover:text-blue-300 transition duration-200">
                    Daftar di sini
                </a>
            </p>
        </div>
    </div>
</body>
</html>