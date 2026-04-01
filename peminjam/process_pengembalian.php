<?php
include '../middleware/auth.php';
cekLogin();
cekRole('peminjam');

include '../config/database.php';
include '../helpers/logger.php';

$id_user = $_SESSION['id_user'];

if (!isset($_POST['id_peminjaman'])) {
    die("Invalid request");
}

$id_peminjaman = (int)$_POST['id_peminjaman'];

mysqli_begin_transaction($conn);

try {

    $update = mysqli_query($conn, "
        UPDATE peminjaman
        SET status='menunggu_kembali'
        WHERE id_peminjaman='$id_peminjaman'
        AND id_user='$id_user'
        AND status='disetujui'
    ");

    if (mysqli_affected_rows($conn) === 0) {
        throw new Exception("Gagal mengajukan pengembalian.");
    }

    logAktivitas(
        $conn,
        $id_user,
        "Mengajukan pengembalian kendaraan"
    );

    mysqli_commit($conn);

    header("Location: peminjaman.php?success=1");
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = $e->getMessage();
    header("Location: peminjaman.php");
}