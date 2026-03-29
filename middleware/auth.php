<?php
session_start();

function cekLogin() {
    if (!isset($_SESSION['id_user'])) {
        header("Location: /manajemen-peminjaman-kendaraan/auth/login.php");
        exit;
    }
}

function cekRole($role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] != $role) {
        header("Location: /manajemen-peminjaman-kendaraan/forbidden.php");
        exit;
    }
}
