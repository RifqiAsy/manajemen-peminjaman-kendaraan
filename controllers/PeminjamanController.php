<?php
include "../config/database.php";
require '../helpers/logger.php';
session_start();

$id_user = $_SESSION['id_user'];
$tgl_pinjam = date("Y-m-d");
$tgl_kembali = $_POST['tanggal_kembali'];

// 1. INSERT PEMINJAMAN (STATUS MENUNGGU)
mysqli_query($conn, "
    INSERT INTO peminjaman (
        id_user,
        tanggal_pinjam,
        tanggal_rencana_kembali,
        status
    ) VALUES (
        '$id_user',
        '$tgl_pinjam',
        '$tgl_kembali',
        'menunggu'
    )
");

$id_peminjaman = mysqli_insert_id($conn);

// 2. INSERT DETAIL (MULTI KENDARAAN)
foreach($_POST['kendaraan'] as $id_kendaraan => $jumlah){

    if($jumlah > 0){
        mysqli_query($conn, "
            INSERT INTO detail_peminjaman (
                id_peminjaman,
                id_kendaraan,
                jumlah
            ) VALUES (
                '$id_peminjaman',
                '$id_kendaraan',
                '$jumlah'
            )
        ");
    }
}

// LOG
logAktivitas($conn, $id_user, "Mengajukan peminjaman ID $id_peminjaman");

// REDIRECT
header("Location: ../peminjam/dashboard.php");
exit;