<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo "ID tidak valid.";
    exit();
}

// Hapus file jika ada
$stmt = $conn->prepare("SELECT file_materi FROM modul WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($modul = $result->fetch_assoc()) {
    if (!empty($modul['file_materi']) && file_exists("../db/" . $modul['file_materi'])) {
        unlink("../db/" . $modul['file_materi']);
    }
}
$stmt->close();

// Hapus dari database
$stmt = $conn->prepare("DELETE FROM modul WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: modul.php");
exit();
