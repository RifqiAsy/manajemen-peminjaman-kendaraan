<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: auth/login.php");
    exit;
}

switch ($_SESSION['role']) {
    case 'admin':
        header("Location: admin/dashboard.php");
        break;
    case 'petugas':
        header("Location: petugas/dashboard.php");
        break;
    case 'peminjam':
        header("Location: peminjam/dashboard.php");
        break;
}
