<?php
include "../config/database.php";
require '../helpers/logger.php';

$id_peminjaman = $_GET['id'];

$data = mysqli_fetch_assoc(
    mysqli_query($conn, "
        SELECT id_kendaraan 
        FROM peminjaman 
        WHERE id_peminjaman='$id_peminjaman'
    ")
);

$id_kendaraan = $data['id_kendaraan'];

mysqli_query($conn, "
    UPDATE peminjaman 
    SET status='disetujui' 
    WHERE id_peminjaman='$id_peminjaman'
");

mysqli_query($conn, "
    UPDATE kendaraan 
    SET status='dipinjam' 
    WHERE id_kendaraan='$id_kendaraan'
");

logAktivitas(   
    $conn,
    $_SESSION['id_user'],
    "Menyetujui peminjaman kendaraan ID $id_kendaraan"
);

header("Location: ../dashboard/approval.php");
