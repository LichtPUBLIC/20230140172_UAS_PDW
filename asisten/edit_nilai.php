<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Edit Nilai';

// Ambil ID dari URL
$laporan_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($laporan_id <= 0) {
    echo "ID laporan tidak valid.";
    exit();
}

// Ambil data laporan
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
    echo "Laporan tidak ditemukan.";
    exit();
}

// Proses form edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nilai'])) {
    $nilai_baru = intval($_POST['nilai']);

    $update = $conn->prepare("UPDATE laporan SET nilai = ? WHERE id = ?");
    $update->bind_param("ii", $nilai_baru, $laporan_id);
    $update->execute();
    $update->close();

    header("Location: laporan.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-lg">
        <h2 class="text-2xl font-bold mb-4"><?= $pageTitle ?></h2>

        <div class="mb-4">
            <p><strong>Nama Mahasiswa:</strong> <?= htmlspecialchars($data['nama_mahasiswa']) ?></p>
            <p><strong>Praktikum:</strong> <?= htmlspecialchars($data['praktikum']) ?></p>
            <p><strong>Pertemuan ke-<?= $data['pertemuan_ke'] ?>:</strong> <?= htmlspecialchars($data['judul_modul']) ?></p>
            <p><strong>File Laporan:</strong> 
                <a href="../db/laporan/<?= htmlspecialchars($data['file_laporan']) ?>" target="_blank" class="text-blue-600 underline">
                    Lihat
                </a>
            </p>
        </div>

        <form method="post" class="space-y-4">
            <div>
                <label class="block font-medium mb-1">Nilai Baru</label>
                <input type="number" name="nilai" min="0" max="100" value="<?= $data['nilai'] ?>" required class="w-full border px-3 py-2 rounded">
            </div>

            <div class="flex justify-between">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded">
                    Simpan Perubahan
                </button>
                <a href="laporan.php" class="text-gray-600 hover:underline px-4 py-2">Batal</a>
            </div>
        </form>
    </div>
</body>
</html>
