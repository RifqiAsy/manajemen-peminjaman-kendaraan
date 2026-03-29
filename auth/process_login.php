<?php
session_start();
include '../config/database.php';
require '../helpers/logger.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// ambil user (PAKAI PREPARED STATEMENT)
$stmt = mysqli_prepare($conn, "
    SELECT id_user, nama, role, password
    FROM users
    WHERE username = ?
");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);

    if (password_verify($password, $user['password'])) {

        // =============================
        // SET SESSION
        // =============================
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama']    = $user['nama'];
        $_SESSION['role']    = $user['role'];

        // =============================
        // LOG AKTIVITAS LOGIN
        // =============================
        $id_user = $user['id_user'];
        mysqli_query($conn, "
            INSERT INTO log_aktivitas (id_user, aktivitas)
            VALUES ('$id_user', 'Login ke sistem')
        ");

        // =============================
        // REDIRECT SESUAI ROLE
        // =============================
        if ($user['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } elseif ($user['role'] === 'petugas') {
            header("Location: ../petugas/dashboard.php");
        } else {
            header("Location: ../peminjam/dashboard.php");
        }
        exit;
    }
}

// =============================
// LOGIN GAGAL
// =============================
$_SESSION['login_error'] = "Username atau password salah";
header("Location: login.php");
exit;
