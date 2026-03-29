<?php
session_start();
include "../config/database.php";
require '../helpers/logger.php';

$id_user      = $_SESSION['id_user'];
$id_kendaraan = $_POST['id_kendaraan'];
$tgl_pinjam   = date("Y-m-d");

mysqli_query($conn, "
    INSERT INTO peminjaman 
    VALUES (
        NULL,
        '$id_user',
        '$id_kendaraan',
        '$tgl_pinjam',
        NULL,
        'menunggu'
    )
");

logAktivitas(
    $conn,
    $_SESSION['id_user'],
    "Mengajukan peminjaman kendaraan ID $id_kendaraan"
);
header("Location: ../dashboard/index.php");
