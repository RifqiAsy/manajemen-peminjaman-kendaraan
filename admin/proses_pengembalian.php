<?php
include '../config/database.php';
include '../helpers/logger.php';
session_start();

// Validasi ID
if (!isset($_GET['id'])) {
    die("ID tidak ditemukan");
}

$id = (int) $_GET['id'];
$tanggal = date('Y-m-d');

// Ambil user login
$id_user = $_SESSION['id_user'] ?? 0;

// Update peminjaman
mysqli_query($conn, "
    UPDATE peminjaman 
    SET 
        status = 'dikembalikan',
        tanggal_pengembalian = '$tanggal'
    WHERE id_peminjaman = $id
");

// Logging (pakai user, bukan id peminjaman)
logAktivitas($conn, $id_user, "Mengembalikan kendaraan ID $id");

// Redirect
header("Location: pengembalian.php");
exit;