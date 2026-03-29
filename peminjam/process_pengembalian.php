<?php
include '../middleware/auth.php';
cekLogin();
cekRole('peminjam');
include '../config/database.php';
include '../helpers/logger.php';

$id_user = $_SESSION['id_user'];

// CEK DENDA BELUM DIBAYAR
$cekDenda = mysqli_query($conn, "
    SELECT d.id_denda
    FROM denda d
    JOIN pengembalian pg ON d.id_pengembalian = pg.id_pengembalian
    JOIN peminjaman p ON pg.id_peminjaman = p.id_peminjaman
    WHERE p.id_user = '$id_user'
    AND d.status = 'belum_dibayar'
");

if (mysqli_num_rows($cekDenda) > 0) {
    $_SESSION['error'] = "Anda masih memiliki denda yang belum dibayar. Silakan lunasi terlebih dahulu.";
    header("Location: peminjaman.php");
    exit;
}


$id_peminjaman = (int)$_POST['id_peminjaman'];
$kondisi = $_POST['kondisi'];
$keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

$denda = 0;
if ($kondisi === 'rusak') {
    $denda = 50000; // silakan ubah sesuai aturan
}

mysqli_begin_transaction($conn);

try {

    mysqli_query($conn, "
        INSERT INTO pengembalian 
        (id_peminjaman, tanggal_pengembalian, kondisi, keterangan, denda)
        VALUES (
            '$id_peminjaman',
            CURDATE(),
            '$kondisi',
            '$keterangan',
            '$denda'
        )
    ");

    mysqli_query($conn, "
        UPDATE peminjaman
        SET status='menunggu_kembali'
        WHERE id_peminjaman='$id_peminjaman'
        AND id_user='$id_user'
        AND status='disetujui'
    ");

    logAktivitas(
        $conn,
        $id_user,
        "Mengajukan pengembalian kendaraan. Denda: Rp $denda"
    );

    mysqli_commit($conn);

    header("Location: pengembalian.php?success=1");
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    die("Gagal proses pengembalian");
}
