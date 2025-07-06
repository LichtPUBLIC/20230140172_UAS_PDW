<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Tidak boleh menghapus diri sendiri
if ($id <= 0 || $id == $_SESSION['user_id']) {
    header("Location: users.php");
    exit();
}

// Cek apakah user ada
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    header("Location: users.php");
    exit();
}
$stmt->close();

// Hapus user
$delete = $conn->prepare("DELETE FROM users WHERE id = ?");
$delete->bind_param("i", $id);
$delete->execute();
$delete->close();

// Redirect kembali
header("Location: users.php");
exit();
