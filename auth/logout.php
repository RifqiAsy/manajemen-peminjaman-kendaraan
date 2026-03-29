<?php
session_start();
session_destroy();
header("Location: login.php");
require '../helpers/logger.php';

logAktivitas(
    $conn,
    $_SESSION['id_user'],
    "Logout dari sistem"
);