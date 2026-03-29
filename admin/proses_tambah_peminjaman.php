<?php
include '../config/database.php';
include '../helpers/logger.php';

logAktivitas($conn, "Menambahkan data peminjaman", $id_kendaraan);

$id_user = $_POST['id_user'];
$id_kendaraan = $_POST['id_kendaraan'];
$tgl_pinjam = $_POST['tanggal_pinjam'];
$tgl_kembali = $_POST['tanggal_rencana_kembali'];

mysqli_query($conn, "
    INSERT INTO peminjaman 
    (id_user, id_kendaraan, tanggal_pinjam, tanggal_rencana_kembali)
    VALUES 
    ('$id_user', '$id_kendaraan', '$tgl_pinjam', '$tgl_kembali')
");

header("Location: peminjaman.php");