<?php
session_start();
include "../config/database.php";
require '../helpers/logger.php';

// ============================
// VALIDASI LOGIN
// ============================
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// ============================
// PROSES AJUKAN PEMINJAMAN
// ============================
if (isset($_POST['ajukan'])) {

    $tgl_pinjam   = $_POST['tanggal_pinjam'];
    $tgl_kembali  = $_POST['tanggal_kembali'];

    // Validasi dasar
    if (empty($_POST['pilih'])) {
        die("Pilih minimal 1 kendaraan");
    }

    // 1. INSERT KE TABEL PEMINJAMAN
    mysqli_query($conn, "
        INSERT INTO peminjaman (
            id_user,
            tanggal_pinjam,
            tanggal_jatuh_tempo,
            status
        ) VALUES (
            '$id_user',
            '$tgl_pinjam',
            '$tgl_kembali',
            'menunggu'
        )
    ");

    $id_peminjaman = mysqli_insert_id($conn);

    // 2. INSERT DETAIL PEMINJAMAN (MULTI)
    foreach ($_POST['pilih'] as $id_kendaraan) {

        $jumlah = $_POST['jumlah'][$id_kendaraan];

        if ($jumlah > 0) {

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

    header("Location: ../peminjam/dashboard.php");
    exit;
}


// ============================
// APPROVE PEMINJAMAN (PETUGAS)
// ============================
if (isset($_GET['approve'])) {

    $id_peminjaman = $_GET['approve'];

    // Ambil semua kendaraan dari detail
    $q = mysqli_query($conn, "
        SELECT id_kendaraan, jumlah 
        FROM detail_peminjaman
        WHERE id_peminjaman = '$id_peminjaman'
    ");

    while ($d = mysqli_fetch_assoc($q)) {

        $id_kendaraan = $d['id_kendaraan'];
        $jumlah       = $d['jumlah'];

        // Kurangi stok kendaraan
        mysqli_query($conn, "
            UPDATE kendaraan
            SET stok = stok - $jumlah
            WHERE id_kendaraan = '$id_kendaraan'
        ");
    }

    // Update status peminjaman
    mysqli_query($conn, "
        UPDATE peminjaman 
        SET status='disetujui',
            approved_by='$id_user'
        WHERE id_peminjaman='$id_peminjaman'
    ");

    // LOG
    logAktivitas($conn, $id_user, "Menyetujui peminjaman ID $id_peminjaman");

    header("Location: ../petugas/approval.php");
    exit;
}


// ============================
// TOLAK PEMINJAMAN
// ============================
if (isset($_GET['tolak'])) {

    $id_peminjaman = $_GET['tolak'];

    mysqli_query($conn, "
        UPDATE peminjaman 
        SET status='ditolak'
        WHERE id_peminjaman='$id_peminjaman'
    ");

    logAktivitas($conn, $id_user, "Menolak peminjaman ID $id_peminjaman");

    header("Location: ../petugas/approval.php");
    exit;
}