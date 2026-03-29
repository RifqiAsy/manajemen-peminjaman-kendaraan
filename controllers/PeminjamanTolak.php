<?php
include "../config/database.php";
require '../helpers/logger.php';

$id_peminjaman = $_GET['id'];

mysqli_query($conn, "
    UPDATE peminjaman 
    SET status='ditolak' 
    WHERE id_peminjaman='$id_peminjaman'
");

logAktivitas(   
    $conn,
    $_SESSION['id_user'],
    "Menolak peminjaman ID $id_peminjaman"
);

header("Location: ../dashboard/approval.php");
