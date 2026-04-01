<?php
include '../config/database.php';
include '../helpers/logger.php';

$id_user = $_POST['id_user'];
$id_kendaraan = $_POST['id_kendaraan'];
$tgl_pinjam = $_POST['tanggal_pinjam'];
$tgl_kembali = $_POST['tanggal_rencana_kembali'];

// VALIDASI
if (!$id_user || !$id_kendaraan || !$tgl_pinjam || !$tgl_kembali) {
    die("Data tidak lengkap");
}

if ($tgl_kembali < $tgl_pinjam) {
    die("Tanggal kembali tidak valid");
}

// INSERT dengan status langsung disetujui
$insert = mysqli_query($conn, "
    INSERT INTO peminjaman 
    (id_user, id_kendaraan, tanggal_pinjam, tanggal_rencana_kembali, status)
    VALUES 
    ('$id_user', '$id_kendaraan', '$tgl_pinjam', '$tgl_kembali', 'disetujui')
");

if ($insert) {
    mysqli_query($conn, "
        UPDATE kendaraan 
        SET status = 'dipinjam' 
        WHERE id_kendaraan = '$id_kendaraan'
    ");
} else {
    die("Gagal menambahkan peminjaman: " . mysqli_error($conn));
}

// LOG setelah data valid
logAktivitas($conn, "Admin menambahkan peminjaman (auto approve)", $id_kendaraan);

header("Location: peminjaman.php");