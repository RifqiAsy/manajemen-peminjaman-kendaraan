<?php
include '../config/database.php';
include '../helpers/logger.php';

logAktivitas($conn, "Mengembalikan kendaraan", $id);

$id = $_GET['id'];
$tanggal = date('Y-m-d');

mysqli_query($conn, "
    UPDATE peminjaman 
    SET 
        status = 'dikembalikan',
        tanggal_pengembalian = '$tanggal'
    WHERE id_peminjaman = '$id'
");

header("Location: pengembalian.php");